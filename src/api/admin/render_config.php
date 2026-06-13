<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403); echo json_encode(['error' => '권한이 없습니다.']); exit;
}

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $row = $pdo->query("SELECT value FROM site_config WHERE key_name = 'render_size'")->fetch();
    echo json_encode(['render_size' => $row ? $row['value'] : '512x512']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $allowed = ['256x256', '512x512', '1024x1024'];
    $size = in_array($body['render_size'] ?? '', $allowed) ? $body['render_size'] : '512x512';
    $pdo->prepare("INSERT INTO site_config (key_name, value) VALUES ('render_size', ?) ON DUPLICATE KEY UPDATE value = VALUES(value)")
        ->execute([$size]);
    echo json_encode(['ok' => true, 'render_size' => $size]);
    exit;
}

http_response_code(405); echo json_encode(['error' => 'Method not allowed']);
