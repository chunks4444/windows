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

$days = max(7, min(365, (int)($_GET['days'] ?? 90)));

$stmt = $pdo->prepare("
    SELECT snapshot_date, total_views
    FROM blog_view_snapshots
    WHERE snapshot_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
    ORDER BY snapshot_date
");
$stmt->execute([$days]);
$rows = $stmt->fetchAll();

echo json_encode(['rows' => $rows]);
