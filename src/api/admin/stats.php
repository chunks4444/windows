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

echo json_encode([
    'daily'       => $daily,
    'summary'     => $summary,
    'sharedCount' => $sharedCount,
]);
