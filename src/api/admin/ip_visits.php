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

$ip     = trim($_GET['ip'] ?? '');
$months = max(1, min(6, (int)($_GET['months'] ?? 6)));
if (!$ip) {
    http_response_code(422); echo json_encode(['error' => 'ip가 필요합니다.']); exit;
}

$stmt = $pdo->prepare('
    SELECT visited_at, page, is_mobile, ip
    FROM page_views
    WHERE ip = ? AND user_id IS NULL AND visited_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
    ORDER BY visited_at DESC
');
$stmt->execute([$ip, $months]);
$visits = $stmt->fetchAll();

echo json_encode(['visits' => $visits]);
