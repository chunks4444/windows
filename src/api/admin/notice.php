<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403);
    echo json_encode(['error' => '권한이 없습니다.']);
    exit;
}

$pdo = db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $keys = ['notice_visible', 'notice_text', 'notice_link', 'notice_bg'];
    $result = [];
    foreach ($keys as $k) {
        $v = $pdo->query("SELECT value FROM site_config WHERE key_name=" . $pdo->quote($k))->fetchColumn();
        $result[$k] = $v === false ? '' : $v;
    }
    if (!isset($result['notice_visible']) || $result['notice_visible'] === '') $result['notice_visible'] = '0';
    if (!isset($result['notice_bg'])      || $result['notice_bg'] === '')      $result['notice_bg']      = 'dark';
    echo json_encode($result);
    exit;
}

if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $allowed = ['notice_visible', 'notice_text', 'notice_link', 'notice_bg'];
    $stmt = $pdo->prepare(
        "INSERT INTO site_config (key_name, value) VALUES (:k, :v)
         ON DUPLICATE KEY UPDATE value=VALUES(value)"
    );
    foreach ($allowed as $k) {
        if (!array_key_exists($k, $body)) continue;
        $stmt->execute([':k' => $k, ':v' => (string)$body[$k]]);
    }
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
