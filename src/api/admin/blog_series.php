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
    $rows = $pdo->query("SELECT id, name, tagline, sort_order, show_on_home, is_completed FROM blog_series ORDER BY sort_order, id")->fetchAll();
    echo json_encode(['series' => $rows]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($body['name'] ?? '');
    if (!$name) { http_response_code(422); echo json_encode(['error' => '이름 필수']); exit; }
    $pdo->prepare("INSERT INTO blog_series (name, tagline, sort_order, show_on_home, is_completed) VALUES (?,?,?,?,?)")
        ->execute([$name, trim($body['tagline'] ?? ''), (int)($body['sort_order'] ?? 0), !empty($body['show_on_home']) ? 1 : 0, !empty($body['is_completed']) ? 1 : 0]);
    echo json_encode(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $id   = (int)($body['id'] ?? 0);
    $name = trim($body['name'] ?? '');
    if (!$id || !$name) { http_response_code(422); echo json_encode(['error' => 'id, 이름 필수']); exit; }
    $pdo->prepare("UPDATE blog_series SET name=?, tagline=?, sort_order=?, show_on_home=?, is_completed=? WHERE id=?")
        ->execute([$name, trim($body['tagline'] ?? ''), (int)($body['sort_order'] ?? 0), !empty($body['show_on_home']) ? 1 : 0, !empty($body['is_completed']) ? 1 : 0, $id]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) { http_response_code(422); echo json_encode(['error' => 'id 필수']); exit; }
    // 이 시리즈를 참조하는 글이 있으면 series_id를 비워 고아 참조를 막는다
    $pdo->prepare("UPDATE blog_posts SET series_id=NULL, series_order=0 WHERE series_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM blog_series WHERE id=?")->execute([$id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405); echo json_encode(['error' => 'Method not allowed']);
