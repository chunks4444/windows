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

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $rows = db()->query('SELECT id, path, title, description, keywords, og_image FROM page_meta ORDER BY path')->fetchAll();
    echo json_encode(['pages' => $rows]);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];

if ($method === 'POST') {
    $path = trim($body['path'] ?? '');
    if (!$path) { http_response_code(400); echo json_encode(['error' => 'path 필요']); exit; }
    try {
        db()->prepare('INSERT INTO page_meta (path, title, description, keywords, og_image) VALUES (?,?,?,?,?)')
             ->execute([$path, $body['title'] ?? '', $body['description'] ?? '', $body['keywords'] ?? '', $body['og_image'] ?? '']);
        echo json_encode(['ok' => true, 'id' => db()->lastInsertId()]);
    } catch (PDOException $e) {
        http_response_code(409); echo json_encode(['error' => '경로가 이미 존재합니다.']);
    }
    exit;
}

if ($method === 'PUT') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) { http_response_code(400); echo json_encode(['error' => 'id 필요']); exit; }
    db()->prepare('UPDATE page_meta SET title=?, description=?, keywords=?, og_image=? WHERE id=?')
         ->execute([$body['title'] ?? '', $body['description'] ?? '', $body['keywords'] ?? '', $body['og_image'] ?? '', $id]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($method === 'DELETE') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) { http_response_code(400); echo json_encode(['error' => 'id 필요']); exit; }
    db()->prepare('DELETE FROM page_meta WHERE id=?')->execute([$id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
