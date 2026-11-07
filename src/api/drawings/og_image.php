<?php
// 도면 공유 링크 미리보기용 가로형(1200×630) 이미지. 공개 주소 — 썸네일 파일 이름(무작위)을 아는 경우에만 열린다.
// 처음 요청 때 만들어 uploads/og_drawing/에 저장해 두고 이후엔 파일을 그대로 내어 준다.
require_once __DIR__ . '/../../lib/og_image.php';

$t = (string)($_GET['t'] ?? '');
if (!preg_match('/^\d+_[0-9a-f]{8}$/', $t)) { http_response_code(404); exit; }

$root = __DIR__ . '/../../../uploads';
$src  = $root . '/drawing_thumbs/' . $t . '.png';
if (!is_file($src)) { http_response_code(404); exit; }

$dir = $root . '/og_drawing';
if (!is_dir($dir)) @mkdir($dir, 0755, true);
$dst = $dir . '/' . $t . '.jpg';

if (!is_file($dst)) {
    // 동시에 여러 번 요청돼도 반쯤 쓰인 파일이 나가지 않도록 임시 파일에 만들고 이름을 바꾼다
    $tmp = $dst . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if (og_drawing_render($src, $tmp) && @rename($tmp, $dst)) {
        @chmod($dst, 0644);
    } else {
        @unlink($tmp);
        // 이미지를 만들지 못하면(GD 없음 등) 원본 썸네일이라도 내어 준다
        header('Content-Type: image/png');
        header('Content-Length: ' . filesize($src));
        readfile($src);
        exit;
    }
}

header('Content-Type: image/jpeg');
header('Content-Length: ' . filesize($dst));
header('Cache-Control: public, max-age=31536000, immutable');
readfile($dst);
