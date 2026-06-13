<?php
ini_set('display_errors', 0);
error_reporting(0);
set_time_limit(120);
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../../../lib/jwt.php';
if (!jwt_from_request()) { http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit; }

$body   = json_decode(file_get_contents('php://input'), true);
$image  = $body['image']  ?? '';
$prompt = trim($body['prompt'] ?? '');

if (!$image || !$prompt) {
    echo json_encode(['error' => '이미지 또는 프롬프트가 없습니다.']);
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../../../lib/ai_render.php';

echo json_encode(ai_render_openai($image, $prompt));
