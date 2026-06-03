<?php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json; charset=UTF-8');

$body   = json_decode(file_get_contents('php://input'), true);
$image  = $body['image']  ?? '';
$prompt = trim($body['prompt'] ?? '');

if (!$image || !$prompt) {
    echo json_encode(['error' => '이미지 또는 프롬프트가 없습니다.']);
    exit;
}

require_once __DIR__ . '/config.php';
$apiKey = STABILITY_API_KEY;

// base64 추출
if (preg_match('/^data:image\/\w+;base64,/', $image)) {
    $image = preg_replace('/^data:image\/\w+;base64,/', '', $image);
}
$imageBytes = base64_decode($image);

// GD로 1024×1024 리사이즈 (SDXL 요구 사양)
if (!function_exists('imagecreatefromstring')) {
    echo json_encode(['error' => 'GD 라이브러리가 필요합니다.']);
    exit;
}

$src = imagecreatefromstring($imageBytes);
if (!$src) {
    echo json_encode(['error' => '이미지 디코딩 실패']);
    exit;
}

$srcW = imagesx($src);
$srcH = imagesy($src);

// 비율 유지하며 가장 가까운 SDXL 지원 크기로 맞춤
$sdxlSizes = [
    [1024, 1024], [1152, 896], [1216, 832],
    [1344, 768],  [1536, 640], [896, 1152],
    [832, 1216],  [768, 1344], [640, 1536],
];
$srcRatio = $srcW / max($srcH, 1);
$best = [1024, 1024];
$minDiff = PHP_FLOAT_MAX;
foreach ($sdxlSizes as [$w, $h]) {
    $diff = abs($srcRatio - $w / $h);
    if ($diff < $minDiff) { $minDiff = $diff; $best = [$w, $h]; }
}
[$dstW, $dstH] = $best;

$dst = imagecreatetruecolor($dstW, $dstH);
imagecopyresampled($dst, $src, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
imagedestroy($src);

ob_start();
imagejpeg($dst, null, 92);
$resizedBytes = ob_get_clean();
imagedestroy($dst);

// ── Stability AI img2img 요청 ─────────────────
$engineId = 'stable-diffusion-xl-1024-v1-0';
$apiUrl   = "https://api.stability.ai/v1/generation/{$engineId}/image-to-image";

$boundary = 'pmBoundary' . bin2hex(random_bytes(8));

$parts = '';
$field = function($name, $value) use (&$parts, $boundary) {
    $parts .= "--{$boundary}\r\n";
    $parts .= "Content-Disposition: form-data; name=\"{$name}\"\r\n\r\n";
    $parts .= "{$value}\r\n";
};

$field('text_prompts[0][text]', $prompt);
$field('text_prompts[0][weight]', '1');
$field('init_image_mode',        'IMAGE_STRENGTH');
$field('image_strength',         '0.35');
$field('cfg_scale',              '7');
$field('samples',                '1');
$field('steps',                  '30');
$field('style_preset',           'photographic');

// 이미지 파트
$parts .= "--{$boundary}\r\n";
$parts .= "Content-Disposition: form-data; name=\"init_image\"; filename=\"input.jpg\"\r\n";
$parts .= "Content-Type: image/jpeg\r\n\r\n";
$parts .= $resizedBytes . "\r\n";
$parts .= "--{$boundary}--\r\n";

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 120,
    CURLOPT_HTTPHEADER     => [
        "Authorization: Bearer {$apiKey}",
        "Accept: application/json",
        "Content-Type: multipart/form-data; boundary={$boundary}",
        'Content-Length: ' . strlen($parts),
    ],
    CURLOPT_POSTFIELDS => $parts,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    echo json_encode(['error' => "네트워크 오류: {$curlErr}"]);
    exit;
}

if ($httpCode !== 200) {
    $err = json_decode($response, true);
    echo json_encode(['error' => $err['message'] ?? "API 오류 (HTTP {$httpCode})"]);
    exit;
}

$result = json_decode($response, true);
if (empty($result['artifacts'][0]['base64'])) {
    echo json_encode(['error' => '렌더링 결과가 없습니다.']);
    exit;
}

echo json_encode(['image' => 'data:image/png;base64,' . $result['artifacts'][0]['base64']]);
