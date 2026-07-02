<?php
/**
 * OpenAI gpt-image-1 이미지 편집 공용 함수
 * 모든 엔진의 렌더링에서 공통으로 사용
 */

function ai_get_openai_key(): string {
    if (defined('OPENAI_API_KEY') && OPENAI_API_KEY) return OPENAI_API_KEY;
    $env = getenv('OPENAI_API_KEY');
    if ($env) return $env;
    try {
        require_once __DIR__ . '/db.php';
        $row = db()->query("SELECT value FROM site_config WHERE key_name = 'openai_api_key'")->fetch();
        return ($row && $row['value']) ? $row['value'] : '';
    } catch (Throwable $e) {
        return '';
    }
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
    } catch (Throwable $e) {
        $cached = 'low';
    }
    return $cached;
}

// AI 렌더링 기본 지시문 템플릿 — {{prompt}} 자리에 사용자가 고른/입력한 프롬프트가 삽입됨
function ai_default_base_prompt_template(): string {
    return 'ABSOLUTE CRITICAL RULE — HIGHEST PRIORITY, OVERRIDES EVERYTHING ELSE: '
        . 'The window frame (문틀) and every lattice/muntin bar (문살) must remain EXACTLY, PIXEL-FOR-PIXEL identical to the input image. '
        . 'Do NOT change their shape, count, spacing, thickness, position, angle, or proportions by even a single pixel. '
        . 'Do NOT shift, resize, add, remove, merge, redraw, reinterpret, or "improve" any structural line, bar, or frame edge — treat the entire structure as a fixed, immutable mask that must be copied through unchanged. '
        . 'You are ONLY allowed to change surface material, texture, color, and lighting on top of this untouched geometry. '
        . 'Render this scene as: {{prompt}}. '
        . 'Final check before responding: overlay the output on the input — the frame and lattice geometry must match with zero deviation, down to the pixel.';
}

function ai_get_base_prompt_template(): string {
    static $cached = null;
    if ($cached !== null) return $cached;
    try {
        require_once __DIR__ . '/db.php';
        $row = db()->query("SELECT value FROM site_config WHERE key_name = 'render_base_prompt'")->fetch();
        $cached = ($row && trim($row['value']) !== '') ? $row['value'] : ai_default_base_prompt_template();
    } catch (Throwable $e) {
        $cached = ai_default_base_prompt_template();
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
 * GD 이미지를 비율 유지한 채 1024×1024 안에 맞추고 나머지는 흰색 패딩
 * 반환: ['path'=>임시PNG경로, 'scale'=>축소비율, 'offX'=>패딩X, 'offY'=>패딩Y, 'dstW'=>실제내용가로, 'dstH'=>실제내용세로]
 */
function ai_pad_to_1024($src, int $srcW, int $srcH): array {
    $scale = min(1024 / $srcW, 1024 / $srcH);
    $dstW  = (int)round($srcW * $scale);
    $dstH  = (int)round($srcH * $scale);
    $offX  = (int)round((1024 - $dstW) / 2);
    $offY  = (int)round((1024 - $dstH) / 2);

    $dst = imagecreatetruecolor(1024, 1024);
    imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
    imagecopyresampled($dst, $src, $offX, $offY, 0, 0, $dstW, $dstH, $srcW, $srcH);

    ob_start(); imagepng($dst); $pngBytes = ob_get_clean();
    imagedestroy($dst);

    $tmpPath = tempnam(sys_get_temp_dir(), 'pm_img_') . '.png';
    file_put_contents($tmpPath, $pngBytes);

    return ['path' => $tmpPath, 'scale' => $scale, 'offX' => $offX, 'offY' => $offY, 'dstW' => $dstW, 'dstH' => $dstH];
}

/**
 * OpenAI gpt-image-1 이미지 편집 실행
 *
 * 배경 사진 전체를 보내면 도면(문살) 영역이 1024×1024 중 일부에 불과해 유효 해상도가 낮아지고,
 * gpt-image-1이 가는 살 패턴을 인식하지 못해 완전히 다른(더 성긴) 격자를 그려버리는 문제가 있었다.
 * 그래서 편집 대상 영역(rect)만 원본 해상도 그대로 잘라 1024×1024로 확대해 전송하고,
 * 결과를 다시 축소해 원본 배경 사진 위의 정확한 위치에 그대로 합성한다 — 배경은 항상 원본 그대로 유지된다.
 *
 * @param string     $imageBase64  base64 또는 data URI (배경+도면 전체 스냅샷)
 * @param string     $prompt       한국어 또는 영어 프롬프트
 * @param array|null $rect         편집을 허용할 영역(원본 이미지 좌표계) ['x','y','w','h']. null이면 이미지 전체 편집.
 * @return array ['image' => 'data:image/png;base64,...'] 또는 ['error' => '...']
 */
function ai_render_openai(string $imageBase64, string $prompt, ?array $rect = null): array {
    $apiKey = ai_get_openai_key();
    if (!$apiKey) return ['error' => 'OpenAI API 키가 설정되지 않았습니다.'];

    if (preg_match('/[\x{AC00}-\x{D7A3}]/u', $prompt)) {
        $prompt = ai_translate_ko_en($prompt);
    }

    if (preg_match('/^data:image\/\w+;base64,/', $imageBase64)) {
        $imageBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $imageBase64);
    }
    $full = @imagecreatefromstring(base64_decode($imageBase64));
    if (!$full) return ['error' => '이미지 디코딩 실패'];
    $fullW = imagesx($full);
    $fullH = imagesy($full);

    // 편집 대상 영역 (없으면 전체 이미지)
    $rx = max(0, min($fullW - 1, (int)round($rect['x'] ?? 0)));
    $ry = max(0, min($fullH - 1, (int)round($rect['y'] ?? 0)));
    $rw = max(1, min($fullW - $rx, (int)round($rect['w'] ?? $fullW)));
    $rh = max(1, min($fullH - $ry, (int)round($rect['h'] ?? $fullH)));

    // 편집 영역만 원본 해상도로 잘라내기
    $crop = imagecreatetruecolor($rw, $rh);
    imagecopy($crop, $full, 0, 0, $rx, $ry, $rw, $rh);

    // 잘라낸 영역을 1024×1024로 확대(패딩) — 문살이 이미지 대부분을 차지해 유효 해상도가 높아짐
    $padded = ai_pad_to_1024($crop, $rw, $rh);
    imagedestroy($crop);
    $tmpImg = $padded['path'];

    $fullPrompt = str_replace('{{prompt}}', $prompt, ai_get_base_prompt_template());

    $fields = [
        'model'          => 'gpt-image-1',
        'prompt'         => $fullPrompt,
        'quality'        => ai_get_render_quality(),
        'input_fidelity' => 'high',
        'n'              => '1',
        'size'           => '1024x1024',
        'image'          => new CURLFile($tmpImg, 'image/png', 'input.png'),
    ];

    $ch = curl_init('https://api.openai.com/v1/images/edits');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 300,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey],
        CURLOPT_POSTFIELDS     => $fields,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);
    unlink($tmpImg);

    if ($curlErr) { imagedestroy($full); return ['error' => "네트워크 오류: {$curlErr}"]; }

    $result = json_decode($response, true);
    if ($httpCode !== 200) {
        imagedestroy($full);
        return ['error' => $result['error']['message'] ?? "API 오류 (HTTP {$httpCode})"];
    }

    $outBytes = null;
    if (!empty($result['data'][0]['b64_json'])) {
        $outBytes = base64_decode($result['data'][0]['b64_json']);
    } elseif (!empty($result['data'][0]['url'])) {
        $outBytes = file_get_contents($result['data'][0]['url']);
    }
    if ($outBytes === null) { imagedestroy($full); return ['error' => '렌더링 결과 없음']; }

    $aiImg = @imagecreatefromstring($outBytes);
    if (!$aiImg) { imagedestroy($full); return ['error' => 'AI 결과 디코딩 실패']; }

    // 1024 패딩을 역산해 편집 영역만큼만 잘라 원래 크기(rw×rh)로 축소
    $renderedCrop = imagecreatetruecolor($rw, $rh);
    imagecopyresampled($renderedCrop, $aiImg, 0, 0, $padded['offX'], $padded['offY'], $rw, $rh, $padded['dstW'], $padded['dstH']);
    imagedestroy($aiImg);

    // 원본 배경 사진(전체 해상도) 위의 정확한 위치에 렌더링 결과만 합성 — 배경은 100% 원본 그대로
    imagecopy($full, $renderedCrop, $rx, $ry, 0, 0, $rw, $rh);
    imagedestroy($renderedCrop);

    ob_start(); imagepng($full); $finalBytes = ob_get_clean();
    imagedestroy($full);

    return ['image' => 'data:image/png;base64,' . base64_encode($finalBytes)];
}
