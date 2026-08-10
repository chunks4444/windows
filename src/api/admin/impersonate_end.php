<?php
// 대리 로그인 종료: 클라이언트가 보관해둔(원래) 관리자 토큰을 다시 인증 쿠키로 세팅한다.
// 새로 발급하지 않고 기존 토큰을 그대로 재사용 — 로그인 시 이미 서명 검증된 값이므로 충분하다.
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

// 주의: 지금 인증 쿠키는 대리 로그인 중인 "대상 회원"의 것이므로 jwt_from_request()
// (쿠키 우선)로 권한을 확인하면 안 된다. 복귀시키려는 원래 관리자 토큰 자체를
// Authorization 헤더로 받아 그 토큰이 유효한 슈퍼 계정 것인지 직접 검증한다.
$header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
if (!$header && function_exists('getallheaders')) {
    $all    = getallheaders();
    $header = $all['Authorization'] ?? $all['authorization'] ?? '';
}
if (!preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
    http_response_code(422); echo json_encode(['error' => '토큰이 없습니다.']); exit;
}
$token   = $m[1];
$payload = jwt_decode($token);
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '유효하지 않거나 만료된 토큰입니다.']); exit;
}

$pdo  = db();
$stmt = $pdo->prepare('SELECT id, role FROM users WHERE id = ?');
$stmt->execute([$payload['sub']]);
$admin = $stmt->fetch();

if (!$admin || $admin['role'] !== 's') {
    http_response_code(403); echo json_encode(['error' => '슈퍼 권한이 필요합니다.']); exit;
}

setcookie('pmok_auth', $token, [
    'expires'  => time() + JWT_EXPIRE,
    'path'     => '/',
    'httponly' => true,
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'samesite' => 'Lax',
]);

echo json_encode(['ok' => true]);
