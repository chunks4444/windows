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

$pdo = db();

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT id, type, title, thumbnail FROM drawings WHERE id=?');
    $stmt->execute([(int)$_GET['id']]);
    $row = $stmt->fetch();
    echo json_encode(['drawing' => $row ?: null]);
    exit;
}

$rows = $pdo->query(
    'SELECT id, type, title FROM drawings ORDER BY type, title'
)->fetchAll();

echo json_encode(['drawings' => $rows]);
