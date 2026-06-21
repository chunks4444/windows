<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403); echo json_encode(['error' => '권한이 없습니다.']); exit;
}

$keys = ['mail_smtp_user', 'mail_smtp_pass', 'mail_sales', 'mail_member'];
$pdo  = db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rows = $pdo->query("SELECT key_name, value FROM site_config WHERE key_name LIKE 'mail_%'")->fetchAll();
    $cfg  = [];
    foreach ($rows as $r) $cfg[$r['key_name']] = $r['value'];
    echo json_encode([
        'mail_smtp_user' => $cfg['mail_smtp_user'] ?? 'pyeongmok@gmail.com',
        'mail_smtp_pass' => $cfg['mail_smtp_pass'] ?? '',
        'mail_sales'     => $cfg['mail_sales']     ?? 'pyeongmok@gmail.com',
        'mail_member'    => $cfg['mail_member']    ?? 'pyeongmok@gmail.com',
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $stmt = $pdo->prepare('INSERT INTO site_config (key_name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)');
    foreach ($keys as $k) {
        if (array_key_exists($k, $body)) {
            $stmt->execute([$k, trim($body[$k])]);
        }
    }
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405); echo json_encode(['error' => 'Method not allowed']);
