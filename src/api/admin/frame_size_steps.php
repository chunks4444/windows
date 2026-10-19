<?php
header('Content-Type: application/json; charset=UTF-8');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/db.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403); echo json_encode(['error' => '권한이 없습니다.']); exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$pdo    = db();

if ($method === 'GET') {
    $rows = $pdo->query(
        'SELECT id, door_type, stage_order, height_max, frame_thickness, frame_width, is_active
         FROM frame_size_steps ORDER BY door_type, stage_order'
    )->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['rows' => $rows]);
    exit;
}

// 행 자체(door_type·stage_order 구성)는 코드가 전제하는 고정 구조라 추가/삭제는 지원하지 않고,
// 기존 행의 치수(height_max·frame_thickness·frame_width)만 고친다 — 치수는 나중에 바뀔 걸 전제로 한 설계.
if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $rows = $body['rows'] ?? [];
    if (!is_array($rows)) {
        http_response_code(400); echo json_encode(['error' => '잘못된 요청입니다.']); exit;
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            'UPDATE frame_size_steps SET height_max = ?, frame_thickness = ?, frame_width = ? WHERE id = ?'
        );
        foreach ($rows as $r) {
            $id = (int)($r['id'] ?? 0);
            if ($id <= 0) continue;
            $heightMax = ($r['height_max'] ?? '') === '' ? null : max(0, (int)$r['height_max']);
            $thickness = max(1, (int)($r['frame_thickness'] ?? 0));
            $width     = max(1, (int)($r['frame_width'] ?? 0));
            $stmt->execute([$heightMax, $thickness, $width, $id]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        http_response_code(500); echo json_encode(['error' => '저장에 실패했습니다.']); exit;
    }

    $rows = $pdo->query(
        'SELECT id, door_type, stage_order, height_max, frame_thickness, frame_width, is_active
         FROM frame_size_steps ORDER BY door_type, stage_order'
    )->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['ok' => true, 'rows' => $rows]);
    exit;
}

http_response_code(405);
