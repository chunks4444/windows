<?php
header('Content-Type: application/json; charset=UTF-8');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/db.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403); echo json_encode(['error' => '권한이 없습니다.']); exit;
}

$pdo    = db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query(
        'SELECT id, group_name, sort_order, code, name, hex, is_active
         FROM color_swatches ORDER BY group_name, sort_order, id'
    );
    echo json_encode(['colors' => $stmt->fetchAll()]); exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];

if ($method === 'DELETE') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) { http_response_code(422); echo json_encode(['error' => 'id required']); exit; }
    $pdo->prepare('DELETE FROM color_swatches WHERE id = ?')->execute([$id]);
    echo json_encode(['ok' => true]); exit;
}

if ($method === 'POST') {
    // 활성 토글
    if (($body['_action'] ?? '') === 'toggle') {
        $id = (int)($body['id'] ?? 0);
        $active = (int)($body['is_active'] ?? 1);
        $pdo->prepare('UPDATE color_swatches SET is_active = ? WHERE id = ?')->execute([$active, $id]);
        echo json_encode(['ok' => true]); exit;
    }

    // 추가 / 수정
    $id    = (int)($body['id'] ?? 0);
    $group = trim($body['group_name'] ?? '');
    $order = (int)($body['sort_order'] ?? 0);
    $code  = trim($body['code'] ?? '');
    $name  = trim($body['name'] ?? '');
    $hex   = trim($body['hex'] ?? '');

    if (!$group || !$code || !$name || !preg_match('/^#[0-9a-fA-F]{6}$/', $hex)) {
        http_response_code(422); echo json_encode(['error' => '입력값을 확인하세요.']); exit;
    }

    try {
        if ($id) {
            $pdo->prepare('UPDATE color_swatches SET group_name=?, sort_order=?, code=?, name=?, hex=? WHERE id=?')
                ->execute([$group, $order, $code, $name, $hex, $id]);
        } else {
            $pdo->prepare('INSERT INTO color_swatches (group_name, sort_order, code, name, hex) VALUES (?, ?, ?, ?, ?)')
                ->execute([$group, $order, $code, $name, $hex]);
        }
    } catch (\PDOException $e) {
        if ($e->getCode() === '23000') {
            http_response_code(422); echo json_encode(['error' => "코드 '{$code}'가 이미 존재합니다."]); exit;
        }
        throw $e;
    }
    echo json_encode(['ok' => true]); exit;
}

http_response_code(405); echo json_encode(['error' => 'Method not allowed']);
