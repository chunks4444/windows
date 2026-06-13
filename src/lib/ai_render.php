<?php
/**
 * OpenAI gpt-image-1 이미지 편집 공용 함수
 * 모든 엔진의 렌더링에서 공통으로 사용
 */

function ai_get_openai_key(): string {
    if (defined('OPENAI_API_KEY') && OPENAI_API_KEY) return OPENAI_API_KEY;
    return getenv('OPENAI_API_KEY') ?: '';
}

function ai_get_render_quality(): string {
    static $cached = null;
    if ($cached !== null) return $cached;
    $allowed = ['low', 'medium', 'high'];
    try {
        require_once __DIR__ . '/db.php';
        $row = db()->query("SELECT value FROM site_config WHERE key_name = 'render_quality'")->fetch();
        $v = $row ? $row['value'] : 'low';
        $cached = in_array($v, $allowed) ? $v : 'low';
    } catch (Throwable) {
        $cached = 'low';
    }
    return $cached;
}

/**
 * 한국어 → 영어 자동 번역
 */
function ai_translate_ko_en(string $text): string {
    $url = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=ko&tl=en&dt=t&q=' . urlencode($text);
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_HTTPHEADER => ['User-Agent: Mozilla/5.0']]);
    $res = curl_exec($ch);
    curl_close($ch);
    if (!$res) return $text;
    $data = json_decode($res, true);
    $out = '';
    if (!empty($data[0]) && is_array($data[0])) {
        foreach ($data[0] as $part) { if (!empty($part[0])) $out .= $part[0]; }
    }
    return trim($out) ?: $text;
}

/**
 * 이미지 → 1024×1024 PNG 임시 파일 생성
 * 반환: 임시 파일 경로 또는 null(실패)
 */
function ai_prepare_image(string $imageBase64): ?string {
    if (preg_match('/^data:image\/\w+;base64,/', $imageBase64)) {
        $imageBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $imageBase64);
    }
    $src = @imagecreatefromstring(base64_decode($imageBase64));
    if (!$src) return null;

    $dst = imagecreatetruecolor(1024, 1024);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, 1024, 1024, imagesx($src), imagesy($src));
    imagedestroy($src);

    ob_start(); imagepng($dst); $pngBytes = ob_get_clean();
    imagedestroy($dst);

    $tmpPath = tempnam(sys_get_temp_dir(), 'pm_img_') . '.png';
    file_put_contents($tmpPath, $pngBytes);
    return $tmpPath;
}

/**
 * OpenAI gpt-image-1 이미지 편집 실행
 *
 * @param string $imageBase64  base64 또는 data URI
 * @param string $prompt       한국어 또는 영어 프롬프트
 * @return array ['image' => 'data:image/png;base64,...'] 또는 ['error' => '...']
 */
function ai_render_openai(string $imageBase64, string $prompt): array {
    $apiKey = ai_get_openai_key();
    if (!$apiKey) return ['error' => 'OpenAI API 키가 설정되지 않았습니다.'];

    if (preg_match('/[\x{AC00}-\x{D7A3}]/u', $prompt)) {
        $prompt = ai_translate_ko_en($prompt);
    }

    $tmpImg = ai_prepare_image($imageBase64);
    if (!$tmpImg) return ['error' => '이미지 디코딩 실패'];

    $fullPrompt = 'Keep the exact same window structure, grid pattern, proportions, and composition unchanged. '
        . 'Apply only surface texture and material: ' . $prompt
        . '. Do not alter the shape, lines, or layout of the window frame and lattice.';

    $ch = curl_init('https://api.openai.com/v1/images/edits');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 300,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey],
        CURLOPT_POSTFIELDS     => [
            'model'   => 'gpt-image-1',
            'prompt'  => $fullPrompt,
            'quality' => ai_get_render_quality(),
            'n'       => '1',
            'size'    => '1024x1024',
            'image'   => new CURLFile($tmpImg, 'image/png', 'input.png'),
        ],
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);
    unlink($tmpImg);

    if ($curlErr) return ['error' => "네트워크 오류: {$curlErr}"];

    $result = json_decode($response, true);
    if ($httpCode !== 200) {
        return ['error' => $result['error']['message'] ?? "API 오류 (HTTP {$httpCode})"];
    }

    if (!empty($result['data'][0]['b64_json'])) {
        return ['image' => 'data:image/png;base64,' . $result['data'][0]['b64_json']];
    }
    if (!empty($result['data'][0]['url'])) {
        return ['image' => 'data:image/png;base64,' . base64_encode(file_get_contents($result['data'][0]['url']))];
    }
    return ['error' => '렌더링 결과 없음'];
}
