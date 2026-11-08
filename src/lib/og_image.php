<?php
// 공유 링크(카카오톡·페이스북 등) 미리보기 이미지.
// 도면 썸네일은 정사각형(1024×1024)인데, 카카오톡 링크 카드는 가로로 긴 비율(약 1.9:1)이라 정사각형을 그대로 쓰면
// 가운데만 잘려 도면의 일부 띠만 확대돼 보인다. 그래서 1200×630 가로 캔버스에 도면 전체가 들어가도록 맞춰 다시 만든다.

const OG_W = 1200;
const OG_H = 630;

// 공개 썸네일 경로(/uploads/drawing_thumbs/<이름>, 확장자 있든 없든) → 미리보기 이미지 주소. 썸네일이 없으면 기본 이미지.
// 썸네일 파일 이름이 바뀌면(도면을 다시 저장하면) 주소도 바뀌므로, 카카오 등이 캐시한 옛 이미지가 따라오지 않는다.
function drawing_og_image_url(?string $thumbnail): string {
    if ($thumbnail && preg_match('#^/uploads/drawing_thumbs/(\d+_[0-9a-f]{8})(?:\.png)?$#', $thumbnail, $m)) {
        return SITE_URL . '/src/api/drawings/og_image.php?t=' . $m[1];
    }
    // 다른 경로의 업로드 이미지는 예전처럼 그대로 쓴다
    return ($thumbnail && strpos($thumbnail, '/uploads/') === 0) ? SITE_URL . $thumbnail : SITE_DEFAULT_IMAGE;
}

// 썸네일 PNG를 가로형 JPEG로 만든다. 여백(흰색·투명)은 잘라내고 도면 전체를 가운데에 맞춰 넣는다.
function og_drawing_render(string $srcPng, string $dstJpg): bool {
    if (!function_exists('imagecreatefrompng')) return false;
    $src = @imagecreatefrompng($srcPng);
    if (!$src) return false;

    // 도면 둘레의 빈 여백 제거 (투명 → 흰색 순으로 시도, 실패하면 원본 그대로)
    foreach ([IMG_CROP_TRANSPARENT, IMG_CROP_WHITE] as $mode) {
        $c = @imagecropauto($src, $mode);
        if ($c !== false && imagesx($c) > 40 && imagesy($c) > 40 && (imagesx($c) < imagesx($src) || imagesy($c) < imagesy($src))) {
            $src = $c; break;
        }
    }

    $canvas = imagecreatetruecolor(OG_W, OG_H);
    imagefill($canvas, 0, 0, imagecolorallocate($canvas, 244, 241, 234)); // 따뜻한 밝은 배경

    // 도면이 캔버스 안에 통째로 들어오게(contain) 맞춘다. 사방 여백 44px
    $pad = 44;
    $sw = imagesx($src); $sh = imagesy($src);
    $scale = min((OG_W - 2 * $pad) / $sw, (OG_H - 2 * $pad) / $sh);
    $dw = max(1, (int)round($sw * $scale)); $dh = max(1, (int)round($sh * $scale));
    imagealphablending($canvas, true);
    imagecopyresampled($canvas, $src, (int)((OG_W - $dw) / 2), (int)((OG_H - $dh) / 2), 0, 0, $dw, $dh, $sw, $sh);

    $ok = imagejpeg($canvas, $dstJpg, 88);

    return $ok;
}
