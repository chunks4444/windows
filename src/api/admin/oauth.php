<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, PUT, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403); echo json_encode(['error' => '권한이 없습니다.']); exit;
}

$keys = [
    'oauth_google_client_id', 'oauth_google_client_secret',
    'oauth_kakao_client_id',  'oauth_kakao_client_secret',
    'oauth_naver_client_id',  'oauth_naver_client_secret',
];
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rows = $pdo->query("SELECT key_name, value FROM site_config WHERE key_name LIKE 'oauth_%'")->fetchAll();
    $data = [];
    foreach ($rows as $r) $data[$r['key_name']] = $r['value'];
    echo json_encode(['config' => $data]);
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
