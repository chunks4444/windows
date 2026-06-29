<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/drawing.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$ids  = array_values(array_unique(array_filter(array_map('intval', $body['ids'] ?? []), fn($id) => $id > 0)));

echo json_encode(Drawing::thumbnails((int) $payload['sub'], $ids));
