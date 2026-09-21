<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';
require_once __DIR__ . '/../../lib/i18n.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401);
    echo json_encode(['error' => t('auth_err_need_auth')]);
    exit;
}

$body     = json_decode(file_get_contents('php://input'), true) ?? [];
$current  = $body['current']  ?? '';
$password = $body['password'] ?? '';

if (strlen($password) < 6) {
    http_response_code(422);
    echo json_encode(['error' => t('auth_err_new_pw_min')]);
    exit;
}

$pdo  = db();
$stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
$stmt->execute([$payload['sub']]);
$user = $stmt->fetch();

if (!$user || !password_verify($current, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['error' => t('auth_err_current_pw')]);
    exit;
}

$stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
$stmt->execute([password_hash($password, PASSWORD_BCRYPT), $payload['sub']]);

echo json_encode(['ok' => true]);
