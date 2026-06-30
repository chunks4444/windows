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

$pdo  = db();
$body = json_decode(file_get_contents('php://input'), true) ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rows = $pdo->query("SELECT id, name, sort_order, is_active FROM pattern_categories ORDER BY sort_order, id")->fetchAll();
    echo json_encode(['categories' => $rows]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($body['name'] ?? '');
    if (!$name) { http_response_code(422); echo json_encode(['error' => 'name 필수']); exit; }
    $pdo->prepare("INSERT INTO pattern_categories (name, sort_order) VALUES (?,?)")
        ->execute([$name, (int)($body['sort_order'] ?? 0)]);
    echo json_encode(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $id   = (int)($body['id'] ?? 0);
    $name = trim($body['name'] ?? '');
    if (!$id || !$name) { http_response_code(422); echo json_encode(['error' => 'id, name 필수']); exit; }
    $pdo->prepare("UPDATE pattern_categories SET name=?, sort_order=?, is_active=? WHERE id=?")
        ->execute([$name, (int)($body['sort_order'] ?? 0), isset($body['is_active']) ? (int)$body['is_active'] : 1, $id]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) { http_response_code(422); echo json_encode(['error' => 'id 필수']); exit; }
    $pdo->prepare("DELETE FROM pattern_categories WHERE id=?")->execute([$id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405); echo json_encode(['error' => 'Method not allowed']);
