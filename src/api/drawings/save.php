<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/drawing.php';
require_once __DIR__ . '/../../lib/db.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$type = $body['type'] ?? '';

if (!$type) {
    http_response_code(422); echo json_encode(['error' => 'type 필드가 필요합니다.']); exit;
}

$title = $body['title'] ?? '';
if (!$title) {
    http_response_code(422); echo json_encode(['error' => 'title 필드가 필요합니다.']); exit;
}

if (Drawing::is_locked((int) $payload['sub'], $type, $title)) {
    http_response_code(423); echo json_encode(['error' => '이 도면은 견적요청 중이라 편집할 수 없습니다.']); exit;
}

$patternCategory = isset($body['pattern_category']) ? trim($body['pattern_category']) : null;
if ($patternCategory === '') $patternCategory = null;

$drawingId = Drawing::save(
    (int) $payload['sub'],
    $type,
    $title,
    $body['created_at']   ?? null,
    $body['versions']     ?? [],
    $body['thumbnail']    ?? null,
    (int) ($body['work_time_sec'] ?? 0),
    $patternCategory
);

// 도면 저장 직후, drawing_id가 없는(NULL) 배경 이미지를 이 도면에 연결
$pdo = db();
$pdo->prepare('UPDATE wallpapers SET drawing_id = ? WHERE user_id = ? AND engine = ? AND drawing_id IS NULL')
    ->execute([$drawingId, (int)$payload['sub'], strtolower($type)]);

echo json_encode(['ok' => true, 'drawing_id' => $drawingId]);
