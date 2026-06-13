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
    $row = $pdo->query("SELECT value FROM site_config WHERE key_name = 'render_quality'")->fetch();
    echo json_encode(['render_quality' => $row ? $row['value'] : 'low']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $body    = json_decode(file_get_contents('php://input'), true) ?? [];
    $allowed = ['low', 'medium', 'high'];
    $quality = in_array($body['render_quality'] ?? '', $allowed) ? $body['render_quality'] : 'low';
    $pdo->prepare("INSERT INTO site_config (key_name, value) VALUES ('render_quality', ?) ON DUPLICATE KEY UPDATE value = VALUES(value)")
        ->execute([$quality]);
    echo json_encode(['ok' => true, 'render_quality' => $quality]);
    exit;
}

http_response_code(405); echo json_encode(['error' => 'Method not allowed']);
