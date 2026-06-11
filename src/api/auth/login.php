<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/logger.php';
require_once __DIR__ . '/../../lib/rate_limit.php';

$ip = pm_get_ip();
if (!rate_limit_check('login:' . $ip, 10, 900)) {
    http_response_code(429);
    echo json_encode(['error' => '로그인 시도가 너무 많습니다. 15분 후 다시 시도해주세요.']);
    exit;
}

$body     = json_decode(file_get_contents('php://input'), true);
$email    = trim($body['email'] ?? '');
$password = $body['password'] ?? '';

if (!$email || !$password) {
    http_response_code(422);
    echo json_encode(['error' => '이메일과 비밀번호를 입력해주세요.']);
    exit;
}

try {
    $pdo  = db();
    $stmt = $pdo->prepare('SELECT id, email, role, password_hash, withdrawn_at FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['error' => '이메일 또는 비밀번호가 올바르지 않습니다.']);
        exit;
    }

    if ($user['withdrawn_at']) {
        http_response_code(403);
        echo json_encode(['error' => '탈퇴한 계정입니다.']);
        exit;
    }

    $pdo->prepare('UPDATE users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?')->execute([$ip, $user['id']]);

    $token = jwt_encode([
        'sub'   => $user['id'],
        'email' => $user['email'],
        'role'  => $user['role'],
        'iat'   => time(),
        'exp'   => time() + JWT_EXPIRE,
    ]);

    setcookie('pmok_auth', $token, [
        'expires'  => time() + JWT_EXPIRE,
        'path'     => '/',
        'httponly' => true,
        'secure'   => true,
        'samesite' => 'Lax',
    ]);

    echo json_encode(['token' => $token, 'user' => ['id' => $user['id'], 'email' => $user['email'], 'role' => $user['role']]]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => '서버 오류가 발생했습니다. 잠시 후 다시 시도해주세요.']);
}
