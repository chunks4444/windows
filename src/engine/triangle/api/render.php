<?php
ini_set('display_errors', 0);
error_reporting(0);
set_time_limit(120);
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../../../lib/jwt.php';
$payload = jwt_from_request();
if (!$payload) { http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit; }
$userId = (int)$payload['sub'];

require_once __DIR__ . '/../../../lib/render_storage.php';
if (render_count_for_user($userId) >= RENDER_LIMIT_PER_USER) {
    http_response_code(429);
    echo json_encode(['error' => '저장된 렌더링이 ' . RENDER_LIMIT_PER_USER . '장을 초과했습니다. 마이페이지 > 렌더링 탭에서 오래된 항목을 삭제한 후 다시 시도해주세요.']);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true);
$image  = $body['image']  ?? '';
$prompt = trim($body['prompt'] ?? '');
$rect   = is_array($body['rect'] ?? null) ? $body['rect'] : null;

if (!$image || !$prompt) {
    echo json_encode(['error' => '이미지 또는 프롬프트가 없습니다.']);
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../../../lib/ai_render.php';

$result = ai_render_openai($image, $prompt, $rect);

if (!empty($result['image']) && preg_match('/^data:image\/png;base64,(.+)$/', $result['image'], $m)) {
    render_save($userId, basename(dirname(__DIR__)), base64_decode($m[1]));
}

echo json_encode($result);
