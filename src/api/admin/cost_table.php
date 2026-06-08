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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rows = $pdo->query('SELECT * FROM cost_table ORDER BY sort_order, id')->fetchAll();
    echo json_encode(['items' => $rows]);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? '';

if ($action === 'save') {
    $id         = (int)($body['id'] ?? 0);
    $category   = trim($body['category'] ?? '');
    $name       = trim($body['name'] ?? '');
    $unit_price = (float)($body['unit_price'] ?? 0);
    $unit       = trim($body['unit'] ?? '');
    $unit_name  = trim($body['unit_name'] ?? '');
    $weight     = (float)($body['weight'] ?? 1);
    $notes      = trim($body['notes'] ?? '');
    $is_active  = (int)($body['is_active'] ?? 1);

    if (!$name) { echo json_encode(['error' => '항목을 입력하세요.']); exit; }

    if ($id) {
        $pdo->prepare('UPDATE cost_table SET category=?, name=?, unit_price=?, unit=?, unit_name=?, weight=?, notes=?, is_active=? WHERE id=?')
            ->execute([$category, $name, $unit_price, $unit, $unit_name, $weight, $notes, $is_active, $id]);
    } else {
        $maxOrder = (int)$pdo->query('SELECT COALESCE(MAX(sort_order),0) FROM cost_table')->fetchColumn();
        $pdo->prepare('INSERT INTO cost_table (category, name, unit_price, unit, unit_name, weight, notes, sort_order, is_active) VALUES (?,?,?,?,?,?,?,?,?)')
            ->execute([$category, $name, $unit_price, $unit, $unit_name, $weight, $notes, $maxOrder + 1, $is_active]);
        $id = (int)$pdo->lastInsertId();
    }

    $stmt = $pdo->prepare('SELECT * FROM cost_table WHERE id=?');
    $stmt->execute([$id]);
    echo json_encode(['ok' => true, 'item' => $stmt->fetch()]);
    exit;
}

if ($action === 'delete') {
    $pdo->prepare('DELETE FROM cost_table WHERE id=?')->execute([(int)($body['id'] ?? 0)]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'toggle') {
    $id = (int)($body['id'] ?? 0);
    $pdo->prepare('UPDATE cost_table SET is_active = 1 - is_active WHERE id=?')->execute([$id]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'reorder') {
    $stmt = $pdo->prepare('UPDATE cost_table SET sort_order=? WHERE id=?');
    foreach (($body['ids'] ?? []) as $i => $id) $stmt->execute([$i, (int)$id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action']);
