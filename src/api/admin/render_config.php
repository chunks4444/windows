<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../lib/cors.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403); echo json_encode(['error' => '권한이 없습니다.']); exit;
}

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rows = $pdo->query("SELECT key_name, value FROM site_config WHERE key_name IN ('render_quality','openai_api_key')")->fetchAll();
    $cfg  = [];
    foreach ($rows as $r) $cfg[$r['key_name']] = $r['value'];
    echo json_encode([
        'render_quality' => $cfg['render_quality']  ?? 'low',
        'openai_api_key' => $cfg['openai_api_key']  ?? '',
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $body    = json_decode(file_get_contents('php://input'), true) ?? [];
    $stmt    = $pdo->prepare("INSERT INTO site_config (key_name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)");

    $allowed = ['low', 'medium', 'high'];
    $quality = in_array($body['render_quality'] ?? '', $allowed) ? $body['render_quality'] : 'low';
    $stmt->execute(['render_quality', $quality]);

    if (array_key_exists('openai_api_key', $body)) {
        $stmt->execute(['openai_api_key', trim($body['openai_api_key'])]);
    }

    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405); echo json_encode(['error' => 'Method not allowed']);
