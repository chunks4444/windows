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
    $rows = $pdo->query("SELECT id, label, prompt_text, sort_order, is_active FROM render_presets ORDER BY sort_order, id")->fetchAll();
    echo json_encode(['presets' => $rows]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $label       = trim($body['label'] ?? '');
    $prompt_text = trim($body['prompt_text'] ?? '');
    if (!$label || !$prompt_text) { http_response_code(422); echo json_encode(['error' => 'label, prompt_text 필수']); exit; }
    $maxOrder = (int)$pdo->query('SELECT COALESCE(MAX(sort_order),0) FROM render_presets')->fetchColumn();
    $pdo->prepare("INSERT INTO render_presets (label, prompt_text, sort_order) VALUES (?,?,?)")
        ->execute([$label, $prompt_text, $maxOrder + 1]);
    echo json_encode(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $id          = (int)($body['id'] ?? 0);
    $label       = trim($body['label'] ?? '');
    $prompt_text = trim($body['prompt_text'] ?? '');
    if (!$id || !$label || !$prompt_text) { http_response_code(422); echo json_encode(['error' => 'id, label, prompt_text 필수']); exit; }
    $pdo->prepare("UPDATE render_presets SET label=?, prompt_text=?, sort_order=?, is_active=? WHERE id=?")
        ->execute([$label, $prompt_text, (int)($body['sort_order'] ?? 0), isset($body['is_active']) ? (int)$body['is_active'] : 1, $id]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) { http_response_code(422); echo json_encode(['error' => 'id 필수']); exit; }
    $pdo->prepare("DELETE FROM render_presets WHERE id=?")->execute([$id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405); echo json_encode(['error' => 'Method not allowed']);
