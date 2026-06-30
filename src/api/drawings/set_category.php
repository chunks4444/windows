<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../lib/cors.php';
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/db.php';

$payload = jwt_from_request();
if (!$payload) { http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit; }

$body       = json_decode(file_get_contents('php://input'), true) ?? [];
$drawingId  = (int)($body['drawing_id'] ?? 0);
$category   = isset($body['pattern_category']) ? trim((string)$body['pattern_category']) : null;
if ($category === '') $category = null;

if (!$drawingId) { http_response_code(422); echo json_encode(['error' => 'drawing_id 필수']); exit; }

$pdo  = db();
$role = $payload['role'] ?? '';
$uid  = (int)$payload['sub'];

// 어드민(s)은 모든 도면 수정 가능, 일반 사용자는 본인 도면만
if ($role === 's') {
    $stmt = $pdo->prepare('UPDATE drawings SET pattern_category = ? WHERE id = ?');
    $stmt->execute([$category, $drawingId]);
} else {
    $stmt = $pdo->prepare('UPDATE drawings SET pattern_category = ? WHERE id = ? AND user_id = ?');
    $stmt->execute([$category, $drawingId, $uid]);
}

if ($stmt->rowCount() === 0) {
    http_response_code(404); echo json_encode(['error' => '도면을 찾을 수 없습니다.']); exit;
}

echo json_encode(['ok' => true]);
