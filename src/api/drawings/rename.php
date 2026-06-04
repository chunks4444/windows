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

$body      = json_decode(file_get_contents('php://input'), true);
$type      = $body['type']      ?? '';
$old_title = $body['old_title'] ?? '';
$new_title = $body['new_title'] ?? '';

if (!$type || !$old_title || !$new_title) {
    http_response_code(422); echo json_encode(['error' => 'type, old_title, new_title 필드가 필요합니다.']); exit;
}

$ok = Drawing::rename((int) $payload['sub'], $type, $old_title, $new_title);

if (!$ok) {
    http_response_code(404); echo json_encode(['error' => '도면을 찾을 수 없습니다.']); exit;
}

echo json_encode(['ok' => true]);
