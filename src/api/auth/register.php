<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$body     = json_decode(file_get_contents('php://input'), true);
$email    = trim($body['email'] ?? '');
$password = $body['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422); echo json_encode(['error' => '유효하지 않은 이메일입니다.']); exit;
}
if (strlen($password) < 6) {
    http_response_code(422); echo json_encode(['error' => '비밀번호는 6자 이상이어야 합니다.']); exit;
}

try {
    $pdo  = db();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(409); echo json_encode(['error' => '이미 사용 중인 이메일입니다.']); exit;
    }
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash) VALUES (?, ?)');
    $stmt->execute([$email, $hash]);
    $userId = (int) $pdo->lastInsertId();
    $token  = jwt_encode(['sub' => $userId, 'email' => $email, 'iat' => time(), 'exp' => time() + JWT_EXPIRE]);
    http_response_code(201);
    echo json_encode(['token' => $token, 'user' => ['id' => $userId, 'email' => $email]]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => '서버 오류가 발생했습니다. 잠시 후 다시 시도해주세요.']);
}
