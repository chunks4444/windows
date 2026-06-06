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
    $rows = db()->query('SELECT id, slug, name_ko, sort_order, is_active FROM library_categories ORDER BY sort_order, id')->fetchAll();
    echo json_encode(['categories' => $rows]);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];

if ($method === 'POST') {
    $slug    = trim($body['slug']    ?? '');
    $name_ko = trim($body['name_ko'] ?? '');
    $order   = (int)($body['sort_order'] ?? 0);

    if (!$slug || !$name_ko) {
        http_response_code(400);
        echo json_encode(['error' => 'slug와 이름은 필수입니다.']);
        exit;
    }
    if (!preg_match('/^[a-z0-9_-]+$/', $slug)) {
        http_response_code(400);
        echo json_encode(['error' => 'slug는 영문 소문자·숫자·_·- 만 가능합니다.']);
        exit;
    }

    try {
        $stmt = db()->prepare('INSERT INTO library_categories (slug, name_ko, sort_order) VALUES (?,?,?)');
        $stmt->execute([$slug, $name_ko, $order]);
        $id = db()->lastInsertId();
        echo json_encode(['ok' => true, 'id' => $id]);
    } catch (PDOException $e) {
        http_response_code(409);
        echo json_encode(['error' => 'slug가 이미 존재합니다.']);
    }
    exit;
}

if ($method === 'PUT') {
    $id      = (int)($body['id']         ?? 0);
    $name_ko = trim($body['name_ko']     ?? '');
    $order   = (int)($body['sort_order'] ?? 0);
    $active  = isset($body['is_active']) ? (int)(bool)$body['is_active'] : null;

    if (!$id || !$name_ko) {
        http_response_code(400);
        echo json_encode(['error' => 'id와 이름은 필수입니다.']);
        exit;
    }

    if ($active !== null) {
        db()->prepare('UPDATE library_categories SET name_ko=?, sort_order=?, is_active=? WHERE id=?')
             ->execute([$name_ko, $order, $active, $id]);
    } else {
        db()->prepare('UPDATE library_categories SET name_ko=?, sort_order=? WHERE id=?')
             ->execute([$name_ko, $order, $id]);
    }
    echo json_encode(['ok' => true]);
    exit;
}

if ($method === 'DELETE') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) { http_response_code(400); echo json_encode(['error' => 'id 필요']); exit; }
    db()->prepare('DELETE FROM library_categories WHERE id=?')->execute([$id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
