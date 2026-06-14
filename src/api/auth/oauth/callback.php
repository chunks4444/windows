<?php
require_once __DIR__ . '/../../../lib/oauth.php';
require_once __DIR__ . '/../../../lib/db.php';
require_once __DIR__ . '/../../../lib/jwt.php';

function oauth_fail(string $msg): never {
    header('Location: /?oauth_error=' . urlencode($msg));
    exit;
}

$provider = $_GET['provider'] ?? '';
if (!in_array($provider, ['google', 'kakao', 'naver'], true)) {
    oauth_fail('잘못된 요청입니다.');
}

$code  = $_GET['code']  ?? '';
$state = $_GET['state'] ?? '';

if (!$code || !oauth_verify_state($state, $provider)) {
    oauth_fail('인증 요청이 만료되었습니다. 다시 시도해 주세요.');
}

$cfg          = oauth_config($provider);
$access_token = oauth_get_token($cfg, $code, $provider);
if (!$access_token) {
    oauth_fail('인증 토큰을 가져오지 못했습니다.');
}

$email = oauth_get_email($provider, $cfg, $access_token);
if (!$email) {
    oauth_fail('이메일 정보를 가져오지 못했습니다. 이메일 제공에 동의해 주세요.');
}

// ── 유저 조회/생성 ─────────────────────────────────────────────
$pdo  = db();
$stmt = $pdo->prepare('SELECT id, role, withdrawn_at FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && $user['withdrawn_at']) {
    oauth_fail('탈퇴한 계정입니다.');
}

if (!$user) {
    $pdo->prepare('INSERT INTO users (email, password_hash) VALUES (?, ?)')
        ->execute([$email, password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT)]);
    $userId = (int) $pdo->lastInsertId();
    $role   = 'u';
} else {
    $userId = (int) $user['id'];
    $role   = $user['role'];
    $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$userId]);
}

// oauth 연결 기록
$pdo->prepare('INSERT IGNORE INTO user_oauth (user_id, provider) VALUES (?, ?)')
    ->execute([$userId, $provider]);

// ── JWT 발급 ───────────────────────────────────────────────────
$token = jwt_encode([
    'sub'   => $userId,
    'email' => $email,
    'role'  => $role,
    'iat'   => time(),
    'exp'   => time() + JWT_EXPIRE,
]);

setcookie('pmok_auth', $token, [
    'expires'  => time() + JWT_EXPIRE,
    'path'     => '/',
    'httponly' => true,
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'samesite' => 'Lax',
]);

// 토큰을 URL fragment로 전달 (JS에서 localStorage 저장)
header('Location: /?oauth_token=' . urlencode($token));
exit;
