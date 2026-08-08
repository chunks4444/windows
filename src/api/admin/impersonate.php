<?php
// 관리자가 회원 계정에 강제 로그인(대리 로그인)한다. 지원 목적으로 회원이 보는 화면을
// 그대로 확인해야 할 때 사용. 슈퍼(s) 권한만 가능하며, 다른 슈퍼 계정은 대상에서 제외한다.
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

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit;
}

$pdo  = db();
$stmt = $pdo->prepare('SELECT id, role FROM users WHERE id = ?');
$stmt->execute([$payload['sub']]);
$admin = $stmt->fetch();

if (!$admin || $admin['role'] !== 's') {
    http_response_code(403); echo json_encode(['error' => '슈퍼 권한이 필요합니다.']); exit;
}

$body      = json_decode(file_get_contents('php://input'), true) ?? [];
$targetId  = (int)($body['user_id'] ?? 0);
if (!$targetId) {
    http_response_code(422); echo json_encode(['error' => '대상 사용자가 없습니다.']); exit;
}

$stmt = $pdo->prepare('SELECT id, email, role, withdrawn_at FROM users WHERE id = ?');
$stmt->execute([$targetId]);
$target = $stmt->fetch();

if (!$target) {
    http_response_code(404); echo json_encode(['error' => '사용자를 찾을 수 없습니다.']); exit;
}
if ($target['role'] === 's') {
    http_response_code(403); echo json_encode(['error' => '다른 슈퍼 관리자 계정으로는 대리 로그인할 수 없습니다.']); exit;
}
if ($target['withdrawn_at']) {
    http_response_code(403); echo json_encode(['error' => '탈퇴한 계정입니다.']); exit;
}

$token = jwt_encode([
    'sub'   => $target['id'],
    'email' => $target['email'],
    'role'  => $target['role'],
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

$pdo->prepare('INSERT INTO admin_impersonation_log (admin_id, target_user_id, ip) VALUES (?, ?, ?)')
    ->execute([$admin['id'], $target['id'], pm_get_ip()]);

echo json_encode([
    'token' => $token,
    'user'  => ['id' => $target['id'], 'email' => $target['email'], 'role' => $target['role']],
]);
