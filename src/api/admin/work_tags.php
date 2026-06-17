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
$pdo->exec("
CREATE TABLE IF NOT EXISTS work_tags (
    id         INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    name       VARCHAR(50)       NOT NULL DEFAULT '',
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active  TINYINT(1)        NOT NULL DEFAULT 1,
    created_at DATETIME          NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    KEY idx_wt_sort (sort_order, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(['tags' => $pdo->query('SELECT * FROM work_tags ORDER BY sort_order, id')->fetchAll()]);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? '';

if ($action === 'save') {
    $id   = (int)($body['id'] ?? 0);
    $name = trim($body['name'] ?? '');
    if (!$name) { echo json_encode(['error' => '이름 필수']); exit; }
    if ($id) {
        $pdo->prepare('UPDATE work_tags SET name=? WHERE id=?')->execute([$name, $id]);
    } else {
        $max = (int)$pdo->query('SELECT COALESCE(MAX(sort_order),0) FROM work_tags')->fetchColumn();
        $pdo->prepare('INSERT INTO work_tags (name, sort_order) VALUES (?,?)')->execute([$name, $max + 1]);
        $id = (int)$pdo->lastInsertId();
    }
    echo json_encode(['ok' => true, 'tag' => $pdo->prepare('SELECT * FROM work_tags WHERE id=?')->execute([$id]) ? $pdo->query("SELECT * FROM work_tags WHERE id=$id")->fetch() : null]);
    exit;
}

if ($action === 'delete') {
    $pdo->prepare('DELETE FROM work_tags WHERE id=?')->execute([(int)($body['id'] ?? 0)]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'toggle') {
    $pdo->prepare('UPDATE work_tags SET is_active = 1 - is_active WHERE id=?')->execute([(int)($body['id'] ?? 0)]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'reorder') {
    $stmt = $pdo->prepare('UPDATE work_tags SET sort_order=? WHERE id=?');
    foreach (($body['ids'] ?? []) as $i => $id) $stmt->execute([$i, (int)$id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action']);
