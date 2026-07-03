<?php
header('Content-Type: application/json; charset=UTF-8');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/db.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$id   = (int)($body['id'] ?? 0);
if (!$id) {
    http_response_code(422); echo json_encode(['error' => 'id 필드가 필요합니다.']); exit;
}

$uid  = (int)$payload['sub'];
$pdo  = db();
$stmt = $pdo->prepare('SELECT filepath FROM renders WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $uid]);
$row  = $stmt->fetch();
if (!$row) {
    http_response_code(404); echo json_encode(['error' => '항목을 찾을 수 없습니다.']); exit;
}

$file = __DIR__ . '/../../../' . ltrim($row['filepath'], '/');
if (is_file($file)) @unlink($file);

$pdo->prepare('DELETE FROM renders WHERE id = ? AND user_id = ?')->execute([$id, $uid]);

echo json_encode(['ok' => true]);
