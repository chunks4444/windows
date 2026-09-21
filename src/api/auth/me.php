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

echo json_encode(['user' => $user]);
