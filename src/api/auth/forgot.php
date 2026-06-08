<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/mailer.php';

$body  = json_decode(file_get_contents('php://input'), true) ?? [];
$email = trim($body['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422); echo json_encode(['error' => '유효하지 않은 이메일입니다.']); exit;
}

$pdo  = db();
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND withdrawn_at IS NULL');
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    $token = bin2hex(random_bytes(32));
    $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$user['id']]);
    $pdo->prepare('INSERT INTO password_resets (token, user_id, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))')
        ->execute([$token, $user['id']]);
    send_mail($email, '비밀번호 재설정', 'reset', ['token' => $token, 'email' => $email]);
}

// 이메일 존재 여부를 노출하지 않음
echo json_encode(['ok' => true]);
