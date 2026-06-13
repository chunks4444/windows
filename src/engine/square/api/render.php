<?php
ini_set('display_errors', 0);
error_reporting(0);
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

// exec() 사용 가능하면 백그라운드 워커, 불가하면 동기 처리
if (function_exists('exec') && !in_array('exec', array_map('trim', explode(',', ini_get('disable_functions'))))) {
    $jobId  = bin2hex(random_bytes(16));
    $jobDir = sys_get_temp_dir() . '/pmok_render';
    if (!is_dir($jobDir)) mkdir($jobDir, 0777, true);
    file_put_contents("{$jobDir}/{$jobId}.input.json", json_encode(['image' => $image, 'prompt' => $prompt]));
    $workerPath = __DIR__ . '/render_worker.php';
    exec(PHP_BINARY . ' ' . escapeshellarg($workerPath) . ' ' . escapeshellarg($jobId) . ' > /dev/null 2>&1 &');
    echo json_encode(['job' => $jobId]);
} else {
    // 동기 폴백
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/../../../lib/ai_render.php';
    set_time_limit(300);
    echo json_encode(ai_render_openai($image, $prompt));
}
