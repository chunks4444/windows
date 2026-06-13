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

$logFile = sys_get_temp_dir() . '/pmok_render_debug.log';

// exec() 실제 동작 여부 테스트
$canExec = false;
if (function_exists('exec') && !in_array('exec', array_map('trim', explode(',', ini_get('disable_functions'))))) {
    $out = []; $code = -1;
    @exec('echo pmok_test 2>&1', $out, $code);
    $canExec = ($code === 0 && ($out[0] ?? '') === 'pmok_test');
}

file_put_contents($logFile, date('Y-m-d H:i:s') . " canExec={$canExec} PHP=" . PHP_BINARY . "\n", FILE_APPEND);

if ($canExec) {
    $jobId  = bin2hex(random_bytes(16));
    $jobDir = sys_get_temp_dir() . '/pmok_render';
    if (!is_dir($jobDir)) mkdir($jobDir, 0777, true);
    file_put_contents("{$jobDir}/{$jobId}.input.json", json_encode(['image' => $image, 'prompt' => $prompt]));
    $workerPath = __DIR__ . '/render_worker.php';
    $cmd = PHP_BINARY . ' ' . escapeshellarg($workerPath) . ' ' . escapeshellarg($jobId) . ' >> ' . escapeshellarg($logFile) . ' 2>&1 &';
    exec($cmd);
    file_put_contents($logFile, date('Y-m-d H:i:s') . " job={$jobId} launched\n", FILE_APPEND);
    echo json_encode(['job' => $jobId]);
} else {
    // 동기 폴백
    file_put_contents($logFile, date('Y-m-d H:i:s') . " sync fallback\n", FILE_APPEND);
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/../../../lib/ai_render.php';
    set_time_limit(120);
    $result = ai_render_openai($image, $prompt);
    file_put_contents($logFile, date('Y-m-d H:i:s') . " sync done: " . (isset($result['error']) ? 'ERR='.$result['error'] : 'OK') . "\n", FILE_APPEND);
    echo json_encode($result);
}
