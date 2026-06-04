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

$type  = $_GET['type']  ?? '';
$title = $_GET['title'] ?? '';

if (!$type) {
    http_response_code(422); echo json_encode(['error' => 'type 파라미터가 필요합니다.']); exit;
}

// title 없으면 목록 반환, 있으면 특정 도면 로드
if ($title === '') {
    echo json_encode(['drawings' => Drawing::list((int) $payload['sub'], $type)]);
} else {
    $result = Drawing::load((int) $payload['sub'], $type, $title);
    echo json_encode($result ?? ['drawing' => null, 'versions' => []]);
}
