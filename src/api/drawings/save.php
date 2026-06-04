<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/drawing.php';

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

$drawingId = Drawing::save(
    (int) $payload['sub'],
    $type,
    $title,
    $body['created_at']   ?? null,
    $body['versions']     ?? [],
    $body['thumbnail']    ?? null,
    (int) ($body['work_time_sec'] ?? 0)
);

echo json_encode(['ok' => true, 'drawing_id' => $drawingId]);
