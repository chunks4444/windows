<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';
require_once __DIR__ . '/../../lib/i18n.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401);
    echo json_encode(['error' => t('auth_err_need_auth')]);
    exit;
}

$pdo  = db();
$stmt = $pdo->prepare('SELECT id, email, role, created_at FROM users WHERE id = ?');
$stmt->execute([$payload['sub']]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => t('auth_err_user_not_found')]);
    exit;
}

// 쿠키로 로그인돼 있는데 브라우저 저장소(localStorage)가 비어 있는 경우(http→https 이동, www 유무 등
// 출처가 바뀐 경우) 화면 쪽 로그인 정보를 다시 채울 수 있도록, 쿠키로 들어온 요청이면 그 토큰도 돌려준다.
$out = ['user' => $user];
if (!empty($_COOKIE['pmok_auth']) && jwt_decode($_COOKIE['pmok_auth'])) $out['token'] = $_COOKIE['pmok_auth'];
echo json_encode($out);
