<?php
// 포트폴리오 PDF 기록 중, 클라우드 IP인데 사람으로 분류돼 있는 행을 다시 분류한다 (lib/visitor_class.php의 pm_classify_visit 규칙).
// 기본은 확인만 하고 아무것도 바꾸지 않는다. 반영하려면: php ops/reclassify_portfolio_dl.php --apply
// 회원(user_id가 있는 행)은 VPN·회사망일 수 있어 건드리지 않는다.
if (PHP_SAPI !== 'cli') exit(1);

require_once __DIR__ . '/../src/lib/db.php';
require_once __DIR__ . '/../src/lib/visitor_class.php';

$apply = in_array('--apply', $argv, true);
$pdo = db();

$rows = $pdo->query("SELECT id, ip, user_agent, mode, created_at FROM portfolio_downloads
                     WHERE user_id IS NULL AND ip IS NOT NULL AND (visitor_type IS NULL OR visitor_type = 'human')")->fetchAll();

$ids = [];
$byRange = [];
foreach ($rows as $r) {
    $name = pm_classify_ip((string)$r['ip']);
    if ($name === null) continue;
    $ids[] = (int)$r['id'];
    $byRange[$name][$r['mode']] = ($byRange[$name][$r['mode']] ?? 0) + 1;
}

echo "검사한 행(비회원·사람 분류): " . count($rows) . "건\n";
echo "클라우드 IP로 다시 분류할 행: " . count($ids) . "건\n";
foreach ($byRange as $name => $modes) {
    $parts = [];
    foreach ($modes as $m => $n) $parts[] = "$m $n";
    echo "  $name: " . implode(', ', $parts) . "\n";
}

if (!$ids) exit(0);
if (!$apply) {
    echo "(확인만 했습니다. 반영하려면 --apply)\n";
    exit(0);
}

foreach (array_chunk($ids, 500) as $chunk) {
    $in = implode(',', array_fill(0, count($chunk), '?'));
    $pdo->prepare("UPDATE portfolio_downloads SET visitor_type = 'other_bot' WHERE id IN ($in)")->execute($chunk);
}
echo "반영했습니다: " . count($ids) . "건 → other_bot\n";
