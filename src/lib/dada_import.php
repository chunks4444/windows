<?php
require_once __DIR__ . '/dada_db.php';

const DADA_COLS = [
    'A' => 'ns', 'B' => 'created_at', 'C' => 'book_date', 'D' => 'acct', 'E' => 'ota_no',
    'F' => 'room_no', 'G' => 'room_type', 'H' => 'rooms', 'I' => 'checkin', 'J' => 'nights',
    'K' => 'checkout', 'L' => 'adults', 'M' => 'children', 'O' => 'channel', 'S' => 'dc_pct',
    'T' => 'avg_rate', 'U' => 'daily_rate', 'V' => 'total_amt', 'W' => 'extra_amt', 'X' => 'sum_amt',
    'Y' => 'status', 'Z' => 'memo', 'AA' => 'group_memo', 'AB' => 'amenity', 'AC' => 'trace',
    'AD' => 'modified_raw',
];

const DADA_EXPECTED_HEADERS = ['A' => 'NS', 'D' => 'ACCT#', 'I' => '입실일', 'V' => '총 금액', 'AC' => 'Trace'];

function dada_read_xlsx(string $path): array {
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        throw new RuntimeException('엑셀 파일을 열 수 없습니다.');
    }
    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    $sstXml   = $zip->getFromName('xl/sharedStrings.xml');
    $zip->close();
    if ($sheetXml === false) {
        throw new RuntimeException('첫 시트를 찾을 수 없습니다.');
    }

    $shared = [];
    if ($sstXml) {
        foreach (simplexml_load_string($sstXml)->si as $si) {
            $t = '';
            foreach ($si->xpath('.//*[local-name()="t"]') as $p) $t .= (string)$p;
            $shared[] = $t;
        }
    }

    $rows = [];
    foreach (simplexml_load_string($sheetXml)->sheetData->row as $row) {
        $out = [];
        foreach ($row->c as $c) {
            $col = preg_replace('/\d+/', '', (string)$c['r']);
            $type = (string)$c['t'];
            if ($type === 'inlineStr') $v = (string)$c->is->t;
            elseif ($type === 's')     $v = $shared[(int)$c->v] ?? '';
            else                       $v = (string)$c->v;
            $out[$col] = trim($v);
        }
        $rows[] = $out;
    }
    return $rows;
}

function dada_nullable(?string $v): ?string {
    return ($v === null || $v === '') ? null : $v;
}

function dada_import_xlsx(string $path): array {
    $rows = dada_read_xlsx($path);
    $header = array_shift($rows) ?? [];
    foreach (DADA_EXPECTED_HEADERS as $col => $name) {
        if (($header[$col] ?? '') !== $name) {
            throw new RuntimeException("예약 조회 엑셀 형식이 아닙니다. ({$col}1 = {$name} 이어야 함)");
        }
    }

    $pdo = dada_db();
    dada_ensure_schema($pdo);

    $fields = array_merge(['row_key'], array_values(DADA_COLS));
    $sql = 'INSERT INTO raw_bookings (' . implode(',', $fields) . ') VALUES (' . implode(',', array_fill(0, count($fields), '?')) . ')'
         . ' ON DUPLICATE KEY UPDATE ' . implode(',', array_map(fn($f) => "$f=VALUES($f)", array_filter($fields, fn($f) => $f !== 'row_key')))
         . ', imported_at=CURRENT_TIMESTAMP';
    $stmt = $pdo->prepare($sql);

    $total = 0;
    $skipped = 0;
    $seq = [];
    $pdo->beginTransaction();
    try {
        foreach ($rows as $r) {
            if (!preg_match('/^\d{12}$/', $r['D'] ?? '')) { $skipped++; continue; }
            $base = $r['D'] . '|' . ($r['F'] ?? '') . '|' . ($r['I'] ?? '');
            $seq[$base] = ($seq[$base] ?? 0) + 1;
            $vals = [$base . '|' . $seq[$base]];
            foreach (DADA_COLS as $col => $f) {
                $v = dada_nullable($r[$col] ?? null);
                if ($v !== null && in_array($f, ['rooms', 'nights', 'adults', 'children'], true)) $v = (int)round((float)$v);
                $vals[] = $v;
            }
            $stmt->execute($vals);
            $total++;
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    dada_refine($pdo);
    return ['loaded' => $total, 'skipped' => $skipped];
}

function dada_refine(PDO $pdo): void {
    $pdo->exec("INSERT INTO bookings
        (row_key, acct, ota_no, created_at, book_date, room_no, room_type, checkin, checkout, nights, adults, children,
         channel, daily_rate, total_amt, status, lead_days, is_over, is_zero_amt, is_zero_night, excluded)
        SELECT row_key, acct, ota_no, created_at, book_date, room_no, room_type, checkin, checkout, nights, adults, children,
               channel, daily_rate, total_amt, status,
               DATEDIFF(checkin, COALESCE(book_date, DATE(created_at))),
               IF(room_no IN ('8888','9999'), 1, 0),
               IF(COALESCE(total_amt,0) = 0, 1, 0),
               IF(COALESCE(nights,0) = 0, 1, 0),
               IF(room_no IN ('8888','9999') OR COALESCE(total_amt,0) = 0 OR COALESCE(nights,0) = 0, 1, 0)
        FROM raw_bookings
        ON DUPLICATE KEY UPDATE
            acct=VALUES(acct), ota_no=VALUES(ota_no), created_at=VALUES(created_at), book_date=VALUES(book_date), room_no=VALUES(room_no),
            room_type=VALUES(room_type), checkin=VALUES(checkin), checkout=VALUES(checkout), nights=VALUES(nights),
            adults=VALUES(adults), children=VALUES(children), channel=VALUES(channel), daily_rate=VALUES(daily_rate),
            total_amt=VALUES(total_amt), status=VALUES(status), lead_days=VALUES(lead_days), is_over=VALUES(is_over),
            is_zero_amt=VALUES(is_zero_amt), is_zero_night=VALUES(is_zero_night), excluded=VALUES(excluded)");

    $pdo->exec('UPDATE bookings SET is_overlap = 0');
    $pdo->exec("UPDATE bookings a
        JOIN (
            SELECT DISTINCT x.row_key FROM bookings x
            JOIN bookings y ON x.room_no = y.room_no AND x.row_key <> y.row_key
                AND x.checkin < y.checkout AND y.checkin < x.checkout
            WHERE x.excluded = 0 AND y.excluded = 0 AND x.room_no IS NOT NULL AND x.room_no <> ''
        ) o ON o.row_key = a.row_key
        SET a.is_overlap = 1");
}

function dada_stats(PDO $pdo): array {
    return [
        'raw'      => (int)$pdo->query('SELECT COUNT(*) FROM raw_bookings')->fetchColumn(),
        'bookings' => (int)$pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn(),
        'over'     => (int)$pdo->query('SELECT COUNT(*) FROM bookings WHERE is_over = 1')->fetchColumn(),
        'zero_amt' => (int)$pdo->query('SELECT COUNT(*) FROM bookings WHERE is_zero_amt = 1')->fetchColumn(),
        'zero_night' => (int)$pdo->query('SELECT COUNT(*) FROM bookings WHERE is_zero_night = 1')->fetchColumn(),
        'overlap'  => (int)$pdo->query('SELECT COUNT(*) FROM bookings WHERE is_overlap = 1')->fetchColumn(),
        'usable'   => (int)$pdo->query('SELECT COUNT(*) FROM bookings WHERE excluded = 0')->fetchColumn(),
        'range'    => $pdo->query('SELECT MIN(checkin) a, MAX(checkin) b FROM bookings')->fetch(),
        'status'   => $pdo->query('SELECT status, COUNT(*) n FROM bookings GROUP BY status ORDER BY n DESC')->fetchAll(),
    ];
}
