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
    $rows = $pdo->query("SELECT id, korean, english, category, sort_order FROM i18n_terms ORDER BY category, sort_order, id")->fetchAll();
    echo json_encode(['terms' => $rows], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $korean  = trim($body['korean'] ?? '');
    $english = trim($body['english'] ?? '');
    if (!$korean || !$english) { http_response_code(422); echo json_encode(['error' => 'korean, english 필수']); exit; }
    $category = trim($body['category'] ?? '') ?: 'general';
    try {
        $pdo->prepare("INSERT INTO i18n_terms (korean, english, category, sort_order) VALUES (?,?,?,?)")
            ->execute([$korean, $english, $category, (int)($body['sort_order'] ?? 0)]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') { http_response_code(422); echo json_encode(['error' => '이미 등록된 한글 용어입니다.']); exit; }
        throw $e;
    }
    echo json_encode(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $id      = (int)($body['id'] ?? 0);
    $korean  = trim($body['korean'] ?? '');
    $english = trim($body['english'] ?? '');
    if (!$id || !$korean || !$english) { http_response_code(422); echo json_encode(['error' => 'id, korean, english 필수']); exit; }
    $category = trim($body['category'] ?? '') ?: 'general';
    try {
        $pdo->prepare("UPDATE i18n_terms SET korean=?, english=?, category=?, sort_order=? WHERE id=?")
            ->execute([$korean, $english, $category, (int)($body['sort_order'] ?? 0), $id]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') { http_response_code(422); echo json_encode(['error' => '이미 등록된 한글 용어입니다.']); exit; }
        throw $e;
    }
    echo json_encode(['ok' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) { http_response_code(422); echo json_encode(['error' => 'id 필수']); exit; }
    $pdo->prepare("DELETE FROM i18n_terms WHERE id=?")->execute([$id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405); echo json_encode(['error' => 'Method not allowed']);
