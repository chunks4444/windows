<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit;
}

$pdo  = db();
$stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
$stmt->execute([$payload['sub']]);
$me   = $stmt->fetch();
if (!$me || $me['role'] !== 's') {
    http_response_code(403); echo json_encode(['error' => '슈퍼 권한이 필요합니다.']); exit;
}

$months = max(1, min(6, (int)((json_decode(file_get_contents('php://input'),true)['months'] ?? 6))));

// 1. 일별 PV / UV
$stmt = $pdo->prepare("
    SELECT DATE(visited_at) AS date,
           COUNT(*)                  AS pv,
           COUNT(DISTINCT ip_hash)   AS uv
    FROM page_views
    WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
    GROUP BY DATE(visited_at)
    ORDER BY date
");
$stmt->execute([$months]);
$daily = $stmt->fetchAll();

// 4. 요약
$stmt = $pdo->prepare("
    SELECT COUNT(*)                AS total_pv,
           COUNT(DISTINCT ip_hash) AS total_uv,
           COUNT(pv.user_id)       AS member_pv,
           SUM(pv.is_mobile)       AS mobile_pv
    FROM page_views pv
    WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
");
$stmt->execute([$months]);
$summary = $stmt->fetch();

// 5. 현재 공유 중인 도면 수 (기간 필터와 무관한 현재 시점 스냅샷)
$sharedCount = (int) $pdo->query('SELECT COUNT(*) FROM drawings WHERE is_shared = 1')->fetchColumn();

// 6. 사람/봇 구분, 검색엔진·유입 경로 — page_views의 분류 칸(visitor_type 등)이 있을 때만.
// 분류는 칸을 추가한 뒤의 방문부터 붙으므로 그 이전 방문은 visitor_type이 NULL('분류 전')이다.
$classified = false;
$types = $bots = $sources = [];
$classSince = null;
try {
    $from = 'FROM page_views WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)';

    // 일별: 종류별 PV와 사람 UV, JS 실행 확인 UV
    $stmt = $pdo->prepare("
        SELECT DATE(visited_at) AS date,
               SUM(visitor_type = 'human')                              AS human_pv,
               SUM(visitor_type IS NOT NULL AND visitor_type <> 'human') AS bot_pv,
               SUM(visitor_type IS NULL)                                AS unk_pv,
               COUNT(DISTINCT CASE WHEN visitor_type = 'human' THEN ip_hash END) AS human_uv,
               COUNT(DISTINCT CASE WHEN js_ok = 1 THEN ip_hash END)     AS js_uv
        $from GROUP BY DATE(visited_at) ORDER BY date
    ");
    $stmt->execute([$months]);
    $byDate = [];
    foreach ($stmt->fetchAll() as $r) $byDate[$r['date']] = $r;
    foreach ($daily as &$d) {
        $x = $byDate[$d['date']] ?? null;
        $d['human_pv'] = (int)($x['human_pv'] ?? 0);
        $d['bot_pv']   = (int)($x['bot_pv']   ?? 0);
        $d['unk_pv']   = (int)($x['unk_pv']   ?? 0);
        $d['human_uv'] = (int)($x['human_uv'] ?? 0);
        $d['js_uv']    = (int)($x['js_uv']    ?? 0);
    }
    unset($d);

    // 기간 전체: 종류별 PV/UV
    $stmt = $pdo->prepare("SELECT COALESCE(visitor_type, 'unknown') AS type, COUNT(*) AS pv, COUNT(DISTINCT ip_hash) AS uv, SUM(js_ok) AS js_pv,
                                  COUNT(DISTINCT CASE WHEN js_ok = 1 THEN ip_hash END) AS js_uv
                           $from GROUP BY COALESCE(visitor_type, 'unknown') ORDER BY pv DESC");
    $stmt->execute([$months]);
    $types = $stmt->fetchAll();

    // 봇 종류별 (검색엔진·AI·미리보기·기타 — 이름 단위)
    $stmt = $pdo->prepare("SELECT visitor_type AS type, COALESCE(bot_name, '-') AS name, COUNT(*) AS pv, COUNT(DISTINCT ip_hash) AS uv, MAX(visited_at) AS last_at
                           $from AND visitor_type IS NOT NULL AND visitor_type <> 'human'
                           GROUP BY visitor_type, bot_name ORDER BY pv DESC LIMIT 40");
    $stmt->execute([$months]);
    $bots = $stmt->fetchAll();

    // 사람의 유입 경로 (구글·네이버·다음·직접 …)
    $stmt = $pdo->prepare("SELECT ref_source AS source, COUNT(*) AS pv, COUNT(DISTINCT ip_hash) AS uv, COUNT(DISTINCT CASE WHEN js_ok = 1 THEN ip_hash END) AS js_uv
                           $from AND visitor_type = 'human' AND ref_source IS NOT NULL
                           GROUP BY ref_source ORDER BY pv DESC");
    $stmt->execute([$months]);
    $sources = $stmt->fetchAll();

    $classSince = $pdo->query("SELECT MIN(visited_at) FROM page_views WHERE visitor_type IS NOT NULL")->fetchColumn() ?: null;
    $classified = $classSince !== null;
} catch (Throwable $e) {
    // 분류 칸이 아직 없는 DB — 기존 응답만 내보낸다
}

echo json_encode([
    'daily'       => $daily,
    'summary'     => $summary,
    'sharedCount' => $sharedCount,
    'classified'  => $classified,
    'classSince'  => $classSince,
    'types'       => $types,
    'bots'        => $bots,
    'sources'     => $sources,
]);
