<?php
header('Content-Type: application/json');
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

$body = json_decode(file_get_contents('php://input'), true);
$id   = (int)($body['id'] ?? 0);
if (!$id) {
    http_response_code(422); echo json_encode(['error' => 'id 필드가 필요합니다.']); exit;
}

$pdo  = db();
$stmt = $pdo->prepare('SELECT filepath FROM wallpapers WHERE id = ? AND user_id = ?');
$stmt->execute([$id, (int)$payload['sub']]);
$row  = $stmt->fetch();
if (!$row) {
    http_response_code(404); echo json_encode(['error' => '이미지를 찾을 수 없습니다.']); exit;
}

// 파일 삭제
$file = __DIR__ . '/../../../../' . ltrim($row['filepath'], '/');
if (is_file($file)) @unlink($file);

$pdo->prepare('DELETE FROM wallpapers WHERE id = ? AND user_id = ?')
    ->execute([$id, (int)$payload['sub']]);

echo json_encode(['ok' => true]);
