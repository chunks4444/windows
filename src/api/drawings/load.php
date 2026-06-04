<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/drawing.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit;
}

$type = $_GET['type'] ?? '';
if (!$type) {
    http_response_code(422); echo json_encode(['error' => 'type 파라미터가 필요합니다.']); exit;
}

$result = Drawing::load((int) $payload['sub'], $type);

echo json_encode($result ?? ['drawing' => null, 'versions' => []]);
