<?php
header('Content-Type: application/json; charset=UTF-8');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/render_storage.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit;
}

$engine = trim($_GET['engine'] ?? '');
$uid    = (int)$payload['sub'];

if ($engine !== '') {
    $stmt = db()->prepare('SELECT id, engine, filepath, created_at FROM renders WHERE user_id = ? AND engine = ? ORDER BY created_at DESC');
    $stmt->execute([$uid, $engine]);
} else {
    $stmt = db()->prepare('SELECT id, engine, filepath, created_at FROM renders WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$uid]);
}

echo json_encode(['renders' => $stmt->fetchAll(), 'limit' => RENDER_LIMIT_PER_USER]);
