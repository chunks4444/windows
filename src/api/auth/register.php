<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';
require_once __DIR__ . '/../../lib/i18n.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/mailer.php';
require_once __DIR__ . '/../../lib/logger.php';
require_once __DIR__ . '/../../lib/rate_limit.php';

$ip = pm_get_ip();
if (IS_PROD && !rate_limit_check('register:' . $ip, 5, 3600)) {
    http_response_code(429); echo json_encode(['error' => t('auth_err_retry_later')]); exit;
}

$body     = json_decode(file_get_contents('php://input'), true);
$email    = trim($body['email'] ?? '');
$password = $body['password'] ?? '';
$agree    = !empty($body['agree']);

// 허니팟: auth_modal.php의 숨김 칸(website)은 사람은 못 보고 봇만 채움.
// 성공한 척 응답해서 봇이 우회를 시도하지 않게 하고, 실제 가입·메일 발송은 하지 않음
if (trim($body['website'] ?? '') !== '') {
    error_log("register honeypot: ip=$ip email=$email");
    http_response_code(201); echo json_encode(['ok' => true]); exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422); echo json_encode(['error' => t('auth_err_email_invalid')]); exit;
}
if (strlen($password) < 6) {
    http_response_code(422); echo json_encode(['error' => t('auth_err_pw_min')]); exit;
}
if (!$agree) {
    http_response_code(422); echo json_encode(['error' => t('auth_msg_agree')]); exit;
}

try {
    $pdo  = db();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(409); echo json_encode(['error' => t('auth_err_email_taken')]); exit;
    }
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, terms_agreed_at) VALUES (?, ?, NOW())');
    $stmt->execute([$email, $hash]);
    $userId = (int) $pdo->lastInsertId();
    $token  = jwt_encode(['sub' => $userId, 'email' => $email, 'role' => 'u', 'iat' => time(), 'exp' => time() + JWT_EXPIRE]);

    send_mail($email, '가입을 환영합니다!', 'welcome', ['email' => $email], '', 'member', mail_address('sales'));

    setcookie('pmok_auth', $token, [
        'expires'  => time() + JWT_EXPIRE,
        'path'     => '/',
        'httponly' => true,
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'samesite' => 'Lax',
    ]);

    http_response_code(201);
    echo json_encode(['token' => $token, 'user' => ['id' => $userId, 'email' => $email, 'role' => 'u']]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => t('auth_err_server_retry')]);
}
