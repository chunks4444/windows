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

// 평목 컬렉션 코드 체계 v1.0 수식어 코드 — 한글 첫 음절 로마자 2자, 항상 필수(카테고리 코드와 달리 NULL 없음)
function normalizeModifierCode(string $raw) {
    $code = strtoupper(trim($raw));
    if (!preg_match('/^[A-Z]{2}$/', $code)) return false;
    return $code;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rows = $pdo->query("SELECT id, name, code, sort_order, is_active FROM pattern_modifiers ORDER BY sort_order, id")->fetchAll();
    echo json_encode(['modifiers' => $rows]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($body['name'] ?? '');
    if (!$name) { http_response_code(422); echo json_encode(['error' => 'name 필수']); exit; }
    $code = normalizeModifierCode((string)($body['code'] ?? ''));
    if ($code === false) { http_response_code(422); echo json_encode(['error' => '코드는 영문 2자입니다.']); exit; }
    try {
        $pdo->prepare("INSERT INTO pattern_modifiers (name, code, sort_order) VALUES (?,?,?)")
            ->execute([$name, $code, (int)($body['sort_order'] ?? 0)]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') { http_response_code(422); echo json_encode(['error' => '이미 사용 중인 코드입니다.']); exit; }
        throw $e;
    }
    echo json_encode(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $id   = (int)($body['id'] ?? 0);
    $name = trim($body['name'] ?? '');
    if (!$id || !$name) { http_response_code(422); echo json_encode(['error' => 'id, name 필수']); exit; }
    $code = normalizeModifierCode((string)($body['code'] ?? ''));
    if ($code === false) { http_response_code(422); echo json_encode(['error' => '코드는 영문 2자입니다.']); exit; }
    try {
        $pdo->prepare("UPDATE pattern_modifiers SET name=?, code=?, sort_order=?, is_active=? WHERE id=?")
            ->execute([$name, $code, (int)($body['sort_order'] ?? 0), isset($body['is_active']) ? (int)$body['is_active'] : 1, $id]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') { http_response_code(422); echo json_encode(['error' => '이미 사용 중인 코드입니다.']); exit; }
        throw $e;
    }
    echo json_encode(['ok' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) { http_response_code(422); echo json_encode(['error' => 'id 필수']); exit; }
    $pdo->prepare("DELETE FROM pattern_modifiers WHERE id=?")->execute([$id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405); echo json_encode(['error' => 'Method not allowed']);
