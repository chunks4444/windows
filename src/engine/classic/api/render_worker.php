<?php
// 백그라운드에서 실행: php render_worker.php <jobId>
set_time_limit(0);
ini_set('memory_limit', '256M');
ini_set('display_errors', 0);
error_reporting(0);

$jobId  = $argv[1] ?? '';
if (!$jobId) exit(1);

$jobDir = sys_get_temp_dir() . '/pmok_render';
$input  = json_decode(file_get_contents("{$jobDir}/{$jobId}.input.json"), true);
if (!$input) exit(1);

$image  = $input['image'];
$prompt = $input['prompt'];

require_once __DIR__ . '/config.php';

// ── 한국어 자동 번역 ─────────────────────────────
if (preg_match('/[\x{AC00}-\x{D7A3}]/u', $prompt)) {
    $url = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=ko&tl=en&dt=t&q=' . urlencode($prompt);
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_HTTPHEADER => ['User-Agent: Mozilla/5.0']]);
    $res = curl_exec($ch); curl_close($ch);
    if ($res) {
        $data = json_decode($res, true);
        $out = '';
        if (!empty($data[0]) && is_array($data[0])) {
            foreach ($data[0] as $part) { if (!empty($part[0])) $out .= $part[0]; }
        }
        if (trim($out)) $prompt = trim($out);
    }
}

// ── 이미지 → 1024×1024 PNG ────────────────────────
if (preg_match('/^data:image\/\w+;base64,/', $image)) {
    $image = preg_replace('/^data:image\/\w+;base64,/', '', $image);
}
$src = @imagecreatefromstring(base64_decode($image));
if (!$src) {
    file_put_contents("{$jobDir}/{$jobId}.result.json", json_encode(['error' => '이미지 디코딩 실패']));
    exit(1);
}

$dst = imagecreatetruecolor(256, 256);
imagealphablending($dst, false);
imagesavealpha($dst, true);
imagecopyresampled($dst, $src, 0, 0, 0, 0, 256, 256, imagesx($src), imagesy($src));
imagedestroy($src);

ob_start(); imagepng($dst); $pngBytes = ob_get_clean();
imagedestroy($dst);

$tmpImg = tempnam(sys_get_temp_dir(), 'pm_img_') . '.png';
file_put_contents($tmpImg, $pngBytes);

// ── OpenAI gpt-image-1-mini edits (테스트용 최저가) ──
$fullPrompt = 'Keep the exact same window structure, grid pattern, proportions, and composition unchanged. Apply only surface texture and material: ' . $prompt . '. Do not alter the shape, lines, or layout of the window frame and lattice.';

$ch = curl_init('https://api.openai.com/v1/images/edits');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 300,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . OPENAI_API_KEY],
    CURLOPT_POSTFIELDS     => [
        'model'  => 'gpt-image-1-mini',
        'prompt' => $fullPrompt,
        'n'      => '1',
        'size'   => '256x256',
        'image'  => new CURLFile($tmpImg, 'image/png', 'input.png'),
    ],
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);
unlink($tmpImg);

if ($curlErr) {
    file_put_contents("{$jobDir}/{$jobId}.result.json", json_encode(['error' => "네트워크 오류: {$curlErr}"]));
    exit(1);
}

$result = json_decode($response, true);

if ($httpCode !== 200) {
    $msg = $result['error']['message'] ?? "API 오류 (HTTP {$httpCode})";
    file_put_contents("{$jobDir}/{$jobId}.result.json", json_encode(['error' => $msg]));
    exit(1);
}

if (!empty($result['data'][0]['b64_json'])) {
    $imageData = 'data:image/png;base64,' . $result['data'][0]['b64_json'];
} elseif (!empty($result['data'][0]['url'])) {
    $imageData = 'data:image/png;base64,' . base64_encode(file_get_contents($result['data'][0]['url']));
} else {
    file_put_contents("{$jobDir}/{$jobId}.result.json", json_encode(['error' => '렌더링 결과 없음']));
    exit(1);
}

file_put_contents("{$jobDir}/{$jobId}.result.json", json_encode(['image' => $imageData]));
unlink("{$jobDir}/{$jobId}.input.json");
