<?php
// 견적서 발행 모듈 공용 함수 (2026-11-04)
//
// 흐름: 관리자가 어드민 '견적서' 메뉴(/src/admin/quotes.php, 주문 상세의 '견적서 작성' 버튼으로도 진입)에서 작성·발행(저장)
//       → 견적서 화면에서 검토 → '고객에게 발신'(견적서 전문 메일 본문 + PDF 첨부).
//       견적요청(주문) 없이 '직접 작성'도 가능 — 수신자·품목을 빈칸부터 입력, order_id 없음.
// 견적서 화면(/src/quote/view.php?id=)과 목록은 슈퍼 관리자만 볼 수 있다 — 고객용 웹 열람 화면·링크는 없음(메일 본문이 전달 수단).
// 재발행하면 차수(revision)를 올려 새 행으로 저장하고 이전 차수는 그대로 보관한다(화면에 "재발행되어 효력 없음" 표시).
// 금액 규칙: 품목 금액 = 수량 × 단가(원 단위 반올림), 공급가액 = 합, 세액 = 공급가액의 10%(원 미만 절사), 합계 = 공급가액 + 세액.

require_once __DIR__ . '/db.php';

const QUOTE_VAT_RATE   = 0.10;
const QUOTE_MAX_ITEMS  = 30;
const QUOTE_ENGINE_CODE = ['classic' => 'CL', 'square' => 'SQ', 'cross' => 'CR', 'diamond' => 'DM', 'triangle' => 'TR', 'hexagon' => 'HX', 'mondrian' => 'MD'];
const QUOTE_ENGINE_NAME = ['square' => '정자살', 'classic' => '완자살', 'cross' => '아자살', 'diamond' => '완자문살', 'triangle' => '세모솟을살', 'hexagon' => '육모살', 'mondrian' => '몬드리안'];

// 테이블을 지금 구조로 맞춘다 — schema.sql의 quotes 정의와 같아야 한다. 견적서 API·화면 요청마다 1번(가벼운 확인 쿼리).
// 2026-11-04 오후: 견적요청(주문) 없이 직접 작성하는 견적서 추가 — order_id NULL 허용, 차수는 base_no(견적 묶음 번호)별로 센다.
// 그 전에 만들어진 테이블이면 여기서 ALTER로 맞춘다(운영 DB에 먼저 생겼을 수 있어서).
function quote_ensure_table(): void {
    static $done = false;
    if ($done) return;
    $done = true;
    $pdo = db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS quotes (
        id              INT UNSIGNED  NOT NULL AUTO_INCREMENT COMMENT '견적서 고유 ID',
        order_id        INT UNSIGNED  NULL COMMENT '어느 주문의 견적서인지 (orders.id) — 견적요청 없이 직접 작성한 견적서는 NULL',
        base_no         VARCHAR(40)   NOT NULL COMMENT '견적 묶음 번호 — 주문 견적서는 주문코드(SQ-1024), 직접 작성은 PM-yymmdd-NN. 차수는 이 번호별로 센다',
        revision        SMALLINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '차수 — 같은 묶음에 재발행할 때마다 1씩 올라감, 이전 차수는 보관',
        quote_no        VARCHAR(40)   NOT NULL COMMENT '견적번호 = base_no + -Q차수 (예: SQ-1024-Q2, PM-261104-01-Q1)',
        title           VARCHAR(100)  NULL COMMENT '건명 (예: 거실 중문) — 견적서 본문·메일 제목에 표시, 주문 견적서는 기본값이 도면명',
        issued_at       DATETIME      NOT NULL COMMENT '발행일시',
        issued_by       INT UNSIGNED  NULL COMMENT '발행한 관리자 user_id',
        valid_until     DATE          NULL COMMENT '견적 유효기한',
        lead_time       VARCHAR(100)  NULL COMMENT '납기 안내 문구 (예: 주문 확정 후 약 21일)',
        items_json      TEXT          NOT NULL COMMENT '품목 행 [{name, spec, qty, unit_price, amount}] — 관리자가 발행 때 확정한 값',
        supply_amount   BIGINT        NOT NULL COMMENT '공급가액 (원, 부가세 제외)',
        vat_amount      BIGINT        NOT NULL COMMENT '세액 (원, 공급가액의 10% 절사)',
        total_amount    BIGINT        NOT NULL COMMENT '합계금액 (원, 공급가액+세액)',
        note            TEXT          NULL COMMENT '비고 (고객에게 보이는 문구 — 결제조건·별도 항목 등)',
        customer_json   TEXT          NOT NULL COMMENT '수신자(고객) 정보 스냅샷 — 이름·회사·사업자번호·주소 등, 발행 뒤 회원정보가 바뀌어도 그대로',
        supplier_json   TEXT          NOT NULL COMMENT '공급자(평목) 정보 스냅샷 — 상호·사업자번호·대표자·주소 등',
        emailed_to      VARCHAR(255)  NULL COMMENT '견적서 메일을 보낸 주소 (안 보냈으면 NULL)',
        emailed_at      DATETIME      NULL COMMENT '마지막 메일 발송 시각',
        PRIMARY KEY (id),
        UNIQUE KEY uk_quotes_base_rev (base_no, revision),
        KEY idx_quotes_order (order_id),
        CONSTRAINT fk_quotes_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='발행된 견적서 (견적 묶음별 차수 보관)'");

    // 예전 구조(주문 전용, base_no·title 없음)로 이미 만들어진 테이블 맞추기
    $hasBase = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS
                                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quotes' AND COLUMN_NAME = 'base_no'")->fetchColumn();
    if (!$hasBase) {
        $pdo->exec("ALTER TABLE quotes
            ADD COLUMN base_no VARCHAR(40) NOT NULL DEFAULT '' COMMENT '견적 묶음 번호 — 주문 견적서는 주문코드(SQ-1024), 직접 작성은 PM-yymmdd-NN. 차수는 이 번호별로 센다' AFTER order_id,
            ADD COLUMN title VARCHAR(100) NULL COMMENT '건명 (예: 거실 중문) — 견적서 본문·메일 제목에 표시, 주문 견적서는 기본값이 도면명' AFTER quote_no,
            MODIFY COLUMN order_id INT UNSIGNED NULL COMMENT '어느 주문의 견적서인지 (orders.id) — 견적요청 없이 직접 작성한 견적서는 NULL'");
        $pdo->exec("UPDATE quotes SET base_no = REGEXP_REPLACE(quote_no, '-Q[0-9]+$', '') WHERE base_no = ''");
        $pdo->exec("ALTER TABLE quotes ALTER COLUMN base_no DROP DEFAULT, ADD UNIQUE KEY uk_quotes_base_rev (base_no, revision)");
    }
}

// 공급자(평목) 기본 정보 — site_config 'quote_supplier'(JSON)에 저장된 값이 있으면 그걸 쓴다.
// 관리자 견적서 작성 창의 '공급자 정보'에서 고치면 저장되고, 이후 발행분부터 반영(이미 발행된 견적서는 스냅샷이라 그대로).
function quote_supplier_defaults(): array {
    return [
        'name'      => '워크그룹 평목(루나미디어)',
        'biz_no'    => '135-32-53949',
        'ceo'       => '전경수',
        'address'   => '경기도 양평군 양서면 도곡리 107-2',
        'biz_type'  => '',
        'biz_item'  => '',
        'phone'     => '070-5124-4568',
        'email'     => '',
        'bank'      => '',
    ];
}

function quote_supplier(): array {
    $s = quote_supplier_defaults();
    try {
        $st = db()->prepare("SELECT value FROM site_config WHERE key_name = 'quote_supplier'");
        $st->execute();
        $saved = json_decode((string) $st->fetchColumn(), true);
        if (is_array($saved)) {
            foreach ($s as $k => $_) if (isset($saved[$k])) $s[$k] = (string) $saved[$k];
        }
    } catch (Throwable $e) {}
    if ($s['email'] === '') {
        require_once __DIR__ . '/mailer.php';
        $s['email'] = mail_address('sales');
    }
    return $s;
}

function quote_save_supplier(array $in): array {
    $s = quote_supplier_defaults();
    foreach ($s as $k => $_) $s[$k] = mb_substr(trim((string) ($in[$k] ?? '')), 0, 200);
    db()->prepare("INSERT INTO site_config (key_name, value) VALUES ('quote_supplier', ?) ON DUPLICATE KEY UPDATE value = VALUES(value)")
        ->execute([json_encode($s, JSON_UNESCAPED_UNICODE)]);
    return quote_supplier();
}

function quote_order_code(string $engine, int $orderId): string {
    return (QUOTE_ENGINE_CODE[$engine] ?? 'PM') . '-' . $orderId;
}

// 품목 행 검증·정규화 + 금액 계산. 잘못되면 ['error' => 메시지].
function quote_normalize_items($items): array {
    if (!is_array($items) || !$items) return ['error' => '품목을 한 줄 이상 입력해주세요.'];
    if (count($items) > QUOTE_MAX_ITEMS) return ['error' => '품목은 ' . QUOTE_MAX_ITEMS . '줄까지 입력할 수 있습니다.'];
    $out = []; $supply = 0;
    foreach ($items as $i => $it) {
        $name = trim((string) ($it['name'] ?? ''));
        $spec = trim((string) ($it['spec'] ?? ''));
        $qty  = $it['qty'] ?? '';
        $unit = $it['unit_price'] ?? '';
        if ($name === '' && $spec === '' && ($unit === '' || (float) $unit == 0)) continue; // 빈 줄은 건너뜀
        $n = $i + 1;
        if ($name === '') return ['error' => "{$n}번째 줄 품명을 입력해주세요."];
        if (mb_strlen($name) > 100 || mb_strlen($spec) > 300) return ['error' => "{$n}번째 줄 내용이 너무 깁니다."];
        if (!is_numeric($qty) || (float) $qty <= 0 || (float) $qty > 99999) return ['error' => "{$n}번째 줄 수량을 확인해주세요."];
        if (!is_numeric($unit) || abs((float) $unit) > 1e10) return ['error' => "{$n}번째 줄 단가를 확인해주세요. (할인은 음수로 입력)"];
        $qty = round((float) $qty, 2);
        $unit = (int) round((float) $unit);
        $amount = (int) round($qty * $unit);
        $supply += $amount;
        $out[] = ['name' => $name, 'spec' => $spec, 'qty' => $qty, 'unit_price' => $unit, 'amount' => $amount];
    }
    if (!$out) return ['error' => '품목을 한 줄 이상 입력해주세요.'];
    if ($supply <= 0) return ['error' => '공급가액 합계가 0원 이하입니다.'];
    $vat = (int) floor($supply * QUOTE_VAT_RATE);
    return ['items' => $out, 'supply' => $supply, 'vat' => $vat, 'total' => $supply + $vat];
}

// 금액을 한글로 — 견적서 상단 "일금 일백육십오만원정" 표기용
function quote_amount_korean(int $n): string {
    if ($n === 0) return '영';
    $digits = ['', '일', '이', '삼', '사', '오', '육', '칠', '팔', '구'];
    $small  = ['', '십', '백', '천'];
    $big    = ['', '만', '억', '조'];
    $out = '';
    $neg = $n < 0; $n = abs($n);
    for ($g = 0; $n > 0; $g++, $n = intdiv($n, 10000)) {
        $chunk = $n % 10000;
        if (!$chunk) continue;
        $s = '';
        for ($p = 3; $p >= 0; $p--) {
            $d = intdiv($chunk, 10 ** $p) % 10;
            if ($d) $s .= $digits[$d] . $small[$p];
        }
        $out = $s . $big[$g] . $out;
    }
    return ($neg ? '마이너스 ' : '') . $out;
}

// 주문 하나의 발행 이력 (최신 차수 먼저). 테이블이 아직 없으면 빈 배열.
function quote_list_for_order(int $orderId): array {
    try {
        $st = db()->prepare('SELECT id, revision, quote_no, issued_at, valid_until, supply_amount, vat_amount, total_amount,
                                    emailed_to, emailed_at
                             FROM quotes WHERE order_id = ? ORDER BY revision DESC');
        $st->execute([$orderId]);
        return $st->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// 견적 묶음 하나(직접 작성 견적서의 재발행 이력 등)의 발행 이력, 최신 차수 먼저
function quote_list_for_base(string $baseNo): array {
    try {
        $st = db()->prepare('SELECT id, revision, quote_no, issued_at, valid_until, supply_amount, vat_amount, total_amount,
                                    emailed_to, emailed_at
                             FROM quotes WHERE base_no = ? ORDER BY revision DESC');
        $st->execute([$baseNo]);
        return $st->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// 직접 작성(주문 없는) 견적서의 새 묶음 번호 — PM-yymmdd-NN (그날 몇 번째인지). 발행 잠금(GET_LOCK) 안에서 호출할 것.
function quote_next_standalone_base(): string {
    $prefix = 'PM-' . date('ymd') . '-';
    $st = db()->prepare('SELECT COUNT(DISTINCT base_no) FROM quotes WHERE base_no LIKE ?');
    $st->execute([$prefix . '%']);
    return $prefix . sprintf('%02d', (int) $st->fetchColumn() + 1);
}

// 견적서 한 장 + (있으면) 주문 정보 (관리자 화면·메일·PDF 렌더링용). title은 건명, 없으면(구버전 행) 주문 도면명.
function quote_by_id(int $id): ?array {
    try {
        $st = db()->prepare('SELECT q.*, COALESCE(NULLIF(q.title, \'\'), o.title, \'\') AS title,
                                    o.engine, o.version_label, o.thumbnail, o.status AS order_status,
                                    (SELECT MAX(revision) FROM quotes q2 WHERE q2.base_no = q.base_no) AS latest_revision
                             FROM quotes q LEFT JOIN orders o ON o.id = q.order_id WHERE q.id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

// ── PDF (발신 메일 첨부 · 견적서 화면 'PDF 받기') ─────────────────────────────
// mPDF(순수 PHP, src/lib/vendor — 운영서버에 composer·크롬이 없어 저장소에 포함) + 나눔고딕(src/lib/pdf_fonts, OFL 라이선스).
// 화면·메일과 같은 components/quote_document.php HTML을 그대로 PDF로 그린다.
// mPDF 갱신: 저장소 밖에서 composer require mpdf/mpdf 후 vendor 통째로 교체(ttfonts는 DejaVuSansCondensed 2개만 남기고 삭제 — 87MB라서).
function quote_pdf(array $quote): string {
    require_once __DIR__ . '/vendor/autoload.php';
    $tmp = sys_get_temp_dir() . '/pmok_mpdf';
    if (!is_dir($tmp)) @mkdir($tmp, 0700, true);

    $defaults     = (new \Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDefaults = (new \Mpdf\Config\FontVariables())->getDefaults();
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8', 'format' => 'A4',
        'margin_left' => 14, 'margin_right' => 14, 'margin_top' => 14, 'margin_bottom' => 14,
        'tempDir'  => $tmp,
        'fontDir'  => array_merge($defaults['fontDir'], [__DIR__ . '/pdf_fonts']),
        'fontdata' => $fontDefaults['fontdata'] + [
            'nanumgothic' => ['R' => 'NanumGothic-Regular.ttf', 'B' => 'NanumGothic-Bold.ttf'],
        ],
        'default_font' => 'nanumgothic',
    ]);
    $mpdf->SetTitle('견적서 ' . $quote['quote_no']);
    $mpdf->SetAuthor(json_decode($quote['supplier_json'], true)['name'] ?? '워크그룹 평목');

    ob_start();
    include __DIR__ . '/../components/quote_document.php';
    $html = ob_get_clean();
    // 화면용 글꼴 이름(맥·윈도 시스템 폰트)은 PDF에 없으므로 나눔고딕으로 바꾼다
    $html = preg_replace('/font-family:[^;"]+/', 'font-family:nanumgothic', $html);
    // mPDF는 숫자 굵기(600~900)를 굵게로 인식하지 않음 — 나눔고딕은 보통/굵게 2종뿐이라 bold로 통일
    $html = preg_replace('/font-weight:\s*[6-9]00/', 'font-weight:bold', $html);
    $mpdf->WriteHTML($html);
    return $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
}

function quote_pdf_filename(array $quote): string {
    return '견적서_' . $quote['quote_no'] . '.pdf';
}
