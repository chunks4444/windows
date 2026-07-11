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
    $rows = $pdo->query("SELECT id, label, query, sort_order, is_active FROM collection_space_filters ORDER BY sort_order, id")->fetchAll();
    echo json_encode(['filters' => $rows]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $label = trim($body['label'] ?? '');
    $query = trim($body['query'] ?? '');
    if (!$label || !$query) { http_response_code(422); echo json_encode(['error' => 'label, query 필수']); exit; }
    $pdo->prepare("INSERT INTO collection_space_filters (label, query, sort_order) VALUES (?,?,?)")
        ->execute([$label, $query, (int)($body['sort_order'] ?? 0)]);
    echo json_encode(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $id    = (int)($body['id'] ?? 0);
    $label = trim($body['label'] ?? '');
    $query = trim($body['query'] ?? '');
    if (!$id || !$label || !$query) { http_response_code(422); echo json_encode(['error' => 'id, label, query 필수']); exit; }
    $pdo->prepare("UPDATE collection_space_filters SET label=?, query=?, sort_order=?, is_active=? WHERE id=?")
        ->execute([$label, $query, (int)($body['sort_order'] ?? 0), isset($body['is_active']) ? (int)$body['is_active'] : 1, $id]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $ids  = $body['ids'] ?? [];
    $stmt = $pdo->prepare("UPDATE collection_space_filters SET sort_order=? WHERE id=?");
    foreach ($ids as $i => $id) {
        $stmt->execute([$i, (int)$id]);
    }
    echo json_encode(['ok' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) { http_response_code(422); echo json_encode(['error' => 'id 필수']); exit; }
    $pdo->prepare("DELETE FROM collection_space_filters WHERE id=?")->execute([$id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405); echo json_encode(['error' => 'Method not allowed']);
