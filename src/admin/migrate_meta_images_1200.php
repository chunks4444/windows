<?php
/**
 * 1회성 마이그레이션: 기존 page_meta.og_image 이미지들을 가로·세로 각각 최소 1200px로 업스케일
 * (구글 검색결과 큰 이미지 미리보기 조건 충족용, src/lib/image_resize.php의 resize_image_min_size()와 동일 기준)
 * 실행: 브라우저에서 /src/admin/migrate_meta_images_1200.php 접근 (슈퍼 권한 필요)
 * 완료 후 이 파일 삭제할 것.
 */
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/jwt.php';
require_once __DIR__ . '/../lib/image_resize.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403);
    echo '권한이 없습니다.';
    exit;
}

$pdo = db();
$log = [];
$minSize = 1200;

$rows = $pdo->query("SELECT id, path, og_image FROM page_meta WHERE og_image IS NOT NULL AND og_image != ''")->fetchAll();

foreach ($rows as $row) {
    $url = $row['og_image'];
    $marker = '/uploads/meta/';
    $pos = strpos($url, $marker);
    if ($pos === false) {
        $log[] = "⏭️  #{$row['id']} {$row['path']} — 외부 URL이라 건너뜀 ($url)";
        continue;
    }
    $path = __DIR__ . '/../..' . substr($url, $pos);
    if (!is_file($path)) {
        $log[] = "⚠️  #{$row['id']} {$row['path']} — 파일 없음 ($path)";
        continue;
    }
    $img = @imagecreatefromstring(file_get_contents($path));
    if (!$img) {
        $log[] = "⚠️  #{$row['id']} {$row['path']} — 이미지 디코딩 실패";
        continue;
    }
    $w = imagesx($img); $h = imagesy($img);
    if ($w >= $minSize && $h >= $minSize) {
        $log[] = "✅ #{$row['id']} {$row['path']} — 이미 {$w}x{$h}, 건너뜀";
        imagedestroy($img);
        continue;
    }
    $resized = resize_image_min_size($img, $minSize);
    $nw = imagesx($resized); $nh = imagesy($resized);
    imagejpeg($resized, $path, 90);
    imagedestroy($resized);
    $log[] = "✅ #{$row['id']} {$row['path']} — {$w}x{$h} → {$nw}x{$nh}";
}

header('Content-Type: text/plain; charset=UTF-8');
echo implode("\n", $log) . "\n\n완료. 이 파일을 삭제하세요: src/admin/migrate_meta_images_1200.php\n";
