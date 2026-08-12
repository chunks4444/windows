<?php
header('Content-Type: application/json; charset=UTF-8');
set_exception_handler(function(Throwable $e) {
    if (!headers_sent()) http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
});
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403);
    echo json_encode(['error' => '권한이 없습니다.']);
    exit;
}

$pdo    = db();
$method = $_SERVER['REQUEST_METHOD'];

// company_page_content에 값이 없는 키는 src/company/index.php의 하드코딩 기본값이 그대로 쓰인다.
$allowedKeys = [
    'hero_label', 'hero_title', 'hero_desc',
    'phil_heading', 'phil_text',
    'phil_item1_title', 'phil_item1_desc',
    'phil_item2_title', 'phil_item2_desc',
    'phil_item3_title', 'phil_item3_desc',
    'studio_label', 'studio_title', 'studio_body',
    'contact_label', 'contact_title', 'contact_body',
];

if ($method === 'GET') {
    $rows = $pdo->query('SELECT content_key, content_value FROM company_page_content')->fetchAll();
    $map = [];
    foreach ($rows as $r) $map[$r['content_key']] = $r['content_value'];
    echo json_encode(['content' => $map]);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? '';

if ($action === 'save') {
    $values = $body['values'] ?? [];
    $stmt = $pdo->prepare('INSERT INTO company_page_content (content_key, content_value) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)');
    $deleteStmt = $pdo->prepare('DELETE FROM company_page_content WHERE content_key = ?');
    foreach ($allowedKeys as $key) {
        if (!array_key_exists($key, $values)) continue;
        $value = trim((string)$values[$key]);
        if ($value === '') {
            // 빈 값으로 저장하면 company_content()가 기본값을 못 쓰게 되므로, 행 자체를 지워 기본값으로 되돌린다.
            $deleteStmt->execute([$key]);
        } else {
            $stmt->execute([$key, $value]);
        }
    }
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action']);
