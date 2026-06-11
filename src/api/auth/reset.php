<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}

require_once __DIR__ . '/../../lib/db.php';

$body     = json_decode(file_get_contents('php://input'), true) ?? [];
$token    = $body['token']    ?? '';
$password = $body['password'] ?? '';

if (strlen($token) !== 64) {
    http_response_code(400); echo json_encode(['error' => '유효하지 않은 링크입니다.']); exit;
}
if (strlen($password) < 6) {
    http_response_code(422); echo json_encode(['error' => '비밀번호는 6자 이상이어야 합니다.']); exit;
}

$pdo  = db();
$stmt = $pdo->prepare('SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW()');
$stmt->execute([$token]);
$row = $stmt->fetch();

if (!$row) {
    http_response_code(400); echo json_encode(['error' => '유효하지 않거나 만료된 링크입니다. 다시 요청해 주세요.']); exit;
}

$pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
    ->execute([password_hash($password, PASSWORD_BCRYPT), $row['user_id']]);
$pdo->prepare('DELETE FROM password_resets WHERE token = ?')->execute([$token]);

echo json_encode(['ok' => true]);
