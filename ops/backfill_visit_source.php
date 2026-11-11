<?php
// 과거 방문 기록(page_views)에 유입 경로(ref_source)와 JS 실행 확인(js_ok)을 로그 파일에서 채운다. (2026-11-08)
// 방문자 분류 기능(visitor_type 등)을 켜기 전의 방문은 칸이 비어 있다. 사이트 로그(logs/access_*.log)의 [S] 줄에는 Referer 호스트가,
// [C] 줄(브라우저가 자바스크립트를 실행했을 때만 남는 비콘)에는 실행 흔적이 남아 있어서, 같은 방문을 찾아 값을 옮긴다.
//
//   php ops/backfill_visit_source.php            # 미리보기: 몇 건이 맞는지만 센다 (page_views는 바꾸지 않는다)
//   php ops/backfill_visit_source.php --apply    # 실제로 채운다
//
// - 대상은 visitor_type이 NULL인 옛 방문뿐이다 (분류된 방문은 건드리지 않는다). 여러 번 돌려도 같은 결과다.
// - 방문 매칭: IP + 방문 시각(초) + 페이지가 같은 [S] 줄.
// - JS 확인: 같은 IP·페이지에서 그 방문 직후 90초 안에 [C] 비콘이 있으면 확인된 것으로 본다. 이 계산은 DB 조인이 아니라
//   로그를 읽으며 PHP에서 한다 (공용 IP 하나가 수만 줄을 남기면 조인이 끝나지 않는다).
// - 옛 방문에는 User-Agent가 없어 사람/봇은 가를 수 없다. 그래서 옛 방문의 "사람" 수는 JS 실행 확인으로 가늠한다.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

require_once __DIR__ . '/../src/lib/db.php';
require_once __DIR__ . '/../src/lib/visitor_class.php';

$apply = in_array('--apply', $argv ?? [], true);
$pdo   = db();
$files = glob(__DIR__ . '/../logs/access_*.log') ?: [];
sort($files);
echo "로그 파일 " . count($files) . "개, 모드: " . ($apply ? 'APPLY(실제 반영)' : 'DRY-RUN(미리보기)') . "\n";
flush();

$pdo->exec('DROP TABLE IF EXISTS _pv_backfill');
$pdo->exec("CREATE TABLE _pv_backfill (
    ip VARCHAR(45) NOT NULL, ts DATETIME NOT NULL, page VARCHAR(120) NOT NULL, src VARCHAR(12) NOT NULL, js TINYINT NOT NULL DEFAULT 0,
    KEY k (ip, ts)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pending = [];
$flush = function () use (&$pending, $pdo) {
    if (!$pending) return;
    $ph = implode(',', array_fill(0, count($pending), '(?,?,?,?,?)'));
    $pdo->prepare("INSERT INTO _pv_backfill (ip, ts, page, src, js) VALUES $ph")->execute(array_merge(...$pending));
    $pending = [];
};

// $list의 오름차순 정수 배열에서 $x 이상인 첫 값 (없으면 null)
$firstAtLeast = function (array $list, int $x): ?int {
    $lo = 0; $hi = count($list);
    while ($lo < $hi) { $mid = ($lo + $hi) >> 1; if ($list[$mid] < $x) $lo = $mid + 1; else $hi = $mid; }
    return $lo < count($list) ? $list[$lo] : null;
};

$nS = $nC = $nJs = 0;
foreach ($files as $f) {
    $fh = fopen($f, 'r');
    if (!$fh) continue;
    $S = [];      // 방문: key => [ts, ip, page, src, epoch, js]
    $C = [];      // 비콘: "ip|page" => [epoch...]
    while (($line = fgets($fh)) !== false) {
        if (strpos($line, '] [S] ') !== false
            && preg_match('/^\[([\d\- :]{19})\] \[S\] IP:(\S+)\s.*?Ref:(.*?)\s+User:\S*\s+Page:(\S+)/u', $line, $m)) {
            $page = substr($m[4], 0, 120);
            $k = $m[2] . '|' . $m[1] . '|' . $page;
            if (!isset($S[$k])) $S[$k] = [$m[1], $m[2], $page, pm_ref_source(trim($m[3])), strtotime($m[1]), 0];
            $nS++;
        } elseif (strpos($line, '] [C] ') !== false
            && preg_match('/^\[([\d\- :]{19})\] \[C\] IP:(\S+)\s.*Page:(\S+)\s*$/u', $line, $m)) {
            $C[$m[2] . '|' . substr($m[3], 0, 120)][] = strtotime($m[1]);
            $nC++;
        }
    }
    fclose($fh);
    foreach ($C as &$l) sort($l);
    unset($l);
    foreach ($S as $row) {
        $js = 0;
        $list = $C[$row[1] . '|' . $row[2]] ?? null;
        if ($list) { $t = $firstAtLeast($list, $row[4]); if ($t !== null && $t - $row[4] <= 90) $js = 1; }
        $nJs += $js;
        $pending[] = [$row[1], $row[0], $row[2], $row[3], $js];
        if (count($pending) >= 1000) $flush();
    }
}
$flush();
echo "읽은 줄: 방문 [S] " . number_format($nS) . "건, 자바스크립트 비콘 [C] " . number_format($nC) . "건 → JS 실행이 확인된 방문 " . number_format($nJs) . "건\n";
flush();

$join = "FROM page_views pv JOIN _pv_backfill b ON b.ip = pv.ip AND b.ts = pv.visited_at AND b.page = pv.page
         WHERE pv.visitor_type IS NULL AND pv.ref_source IS NULL";
echo "채울 수 있는 옛 방문: " . number_format((int)$pdo->query("SELECT COUNT(DISTINCT pv.id) $join")->fetchColumn())
   . "건 (그중 JS 확인 " . number_format((int)$pdo->query("SELECT COUNT(DISTINCT pv.id) $join AND b.js = 1")->fetchColumn()) . "건)\n";
echo "page_views의 분류 전 방문 전체: " . number_format((int)$pdo->query("SELECT COUNT(*) FROM page_views WHERE visitor_type IS NULL")->fetchColumn()) . "건\n";
flush();

if ($apply) {
    $n = $pdo->exec("UPDATE page_views pv JOIN _pv_backfill b ON b.ip = pv.ip AND b.ts = pv.visited_at AND b.page = pv.page
                     SET pv.ref_source = b.src, pv.js_ok = GREATEST(pv.js_ok, b.js)
                     WHERE pv.visitor_type IS NULL AND pv.ref_source IS NULL");
    echo "반영 완료: " . number_format((int)$n) . "행\n";
}

echo "\n분류 전 방문의 유입 경로 분포" . ($apply ? '' : ' (현재 DB 기준 — 미리보기에서는 아직 비어 있음)') . ":\n";
foreach ($pdo->query("SELECT COALESCE(ref_source,'(미확인)') s, COUNT(*) n, SUM(js_ok) js FROM page_views WHERE visitor_type IS NULL GROUP BY ref_source ORDER BY n DESC") as $r) {
    printf("  %-14s PV %7s   JS확인 %6s\n", $r['s'], number_format($r['n']), number_format((int)$r['js']));
}
if (!$apply) {
    echo "\n(미리보기) 반영하면 채워질 유입 경로 분포 — 로그 기준:\n";
    foreach ($pdo->query("SELECT b.src s, COUNT(DISTINCT pv.id) n, COUNT(DISTINCT CASE WHEN b.js = 1 THEN pv.id END) js
                          $join GROUP BY b.src ORDER BY n DESC") as $r) {
        printf("  %-14s PV %7s   JS확인 %6s\n", $r['s'], number_format($r['n']), number_format((int)$r['js']));
    }
}
$pdo->exec('DROP TABLE IF EXISTS _pv_backfill');
echo "\n끝\n";
