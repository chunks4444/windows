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
    $rows = $pdo->query('SELECT * FROM cost_params ORDER BY category, sort_order, id')->fetchAll();
    echo json_encode(['params' => $rows]);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? '';

if ($action === 'save') {
    $id               = (int)($body['id'] ?? 0);
    $category         = in_array($body['category'] ?? '', ['wood','overhead','difficulty']) ? $body['category'] : 'wood';
    $name             = trim($body['name'] ?? '');
    $unit_price       = (float)($body['unit_price'] ?? 0);
    $consumption_rate = (float)($body['consumption_rate'] ?? 0);
    $unit             = trim($body['unit'] ?? '');
    $notes            = trim($body['notes'] ?? '');
    $is_active        = (int)($body['is_active'] ?? 1);

    if ($id) {
        $pdo->prepare('UPDATE cost_params SET category=?,name=?,unit_price=?,consumption_rate=?,unit=?,notes=?,is_active=? WHERE id=?')
            ->execute([$category, $name, $unit_price, $consumption_rate, $unit, $notes, $is_active, $id]);
    } else {
        $maxOrder = (int)$pdo->prepare('SELECT COALESCE(MAX(sort_order),0) FROM cost_params WHERE category=?')
            ->execute([$category]) ? $pdo->query("SELECT COALESCE(MAX(sort_order),0) FROM cost_params WHERE category='$category'")->fetchColumn() : 0;
        $pdo->prepare('INSERT INTO cost_params (category,name,unit_price,consumption_rate,unit,notes,sort_order,is_active) VALUES (?,?,?,?,?,?,?,?)')
            ->execute([$category, $name, $unit_price, $consumption_rate, $unit, $notes, $maxOrder + 1, $is_active]);
        $id = (int)$pdo->lastInsertId();
    }

    $stmt = $pdo->prepare('SELECT * FROM cost_params WHERE id=?');
    $stmt->execute([$id]);
    echo json_encode(['ok' => true, 'param' => $stmt->fetch()]);
    exit;
}

if ($action === 'delete') {
    $pdo->prepare('DELETE FROM cost_params WHERE id=?')->execute([(int)($body['id'] ?? 0)]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'toggle') {
    $id = (int)($body['id'] ?? 0);
    $pdo->prepare('UPDATE cost_params SET is_active = 1 - is_active WHERE id=?')->execute([$id]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'reorder') {
    $ids  = $body['ids'] ?? [];
    $stmt = $pdo->prepare('UPDATE cost_params SET sort_order=? WHERE id=?');
    foreach ($ids as $i => $id) $stmt->execute([$i, (int)$id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action']);
