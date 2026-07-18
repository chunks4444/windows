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

$months = max(1, min(12, (int)($_GET['months'] ?? 6)));
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 20;
$offset = ($page - 1) * $limit;

// 전체 개수 (IP+날짜 그룹 기준)
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM (
        SELECT 1
        FROM page_views
        WHERE user_id IS NULL AND visited_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
        GROUP BY ip, DATE(visited_at)
    ) t
");
$stmt->execute([$months]);
$total = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT ip,
           DATE(visited_at)     AS visit_date,
           COUNT(*)             AS visit_count,
           SUM(is_mobile)       AS mobile_count,
           MAX(visited_at)      AS last_visit
    FROM page_views
    WHERE user_id IS NULL AND visited_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
    GROUP BY ip, DATE(visited_at)
    ORDER BY visit_date DESC, last_visit DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute([$months]);
$visits = $stmt->fetchAll();

echo json_encode([
    'visits' => $visits,
    'total'  => $total,
    'page'   => $page,
    'pages'  => (int) ceil($total / $limit),
]);
