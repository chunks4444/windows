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

$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM (
        SELECT 1 FROM page_views
        WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
        GROUP BY page
    ) t
");
$stmt->execute([$months]);
$total = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT page,
           COUNT(*)                AS pv,
           COUNT(DISTINCT ip_hash) AS uv
    FROM page_views
    WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
    GROUP BY page
    ORDER BY pv DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute([$months]);
$rows = $stmt->fetchAll();

echo json_encode([
    'rows'        => $rows,
    'total'       => $total,
    'page'        => $page,
    'pages_count' => (int) ceil($total / $limit),
]);
