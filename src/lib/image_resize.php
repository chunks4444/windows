<?php
/**
 * 구글 검색결과 큰 이미지 미리보기 조건(가로·세로 각각 최소 1200px)을 맞추기 위해
 * 원본이 그보다 작으면 업스케일한다. 이미 두 변 모두 충분히 크면 손대지 않는다(다운스케일 안 함).
 * 메타데이터(og:image)와 블로그 글 타이틀 이미지 저장 시 공통으로 사용.
 */
function resize_image_min_size($img, int $min = 1200) {
    $w = imagesx($img);
    $h = imagesy($img);
    if ($w >= $min && $h >= $min) return $img;

    $scale = $min / min($w, $h);
    $nw = (int)ceil($w * $scale);
    $nh = (int)ceil($h * $scale);

    $resized = imagecreatetruecolor($nw, $nh);
    imagealphablending($resized, false);
    imagesavealpha($resized, true);
    imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
    imagedestroy($img);
    return $resized;
}
