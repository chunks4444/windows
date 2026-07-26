<?php
/**
 * 1회성 마이그레이션: 기존 blog_posts.thumbnail_url 이미지들을 폭 1200px로 업스케일
 * (구글 검색결과 큰 이미지 미리보기 조건 충족용, src/api/admin/blog.php의 saveBlogImage()와 동일 기준)
 * 실행: 브라우저에서 /src/admin/migrate_blog_thumbnails_1200.php 접근 (슈퍼 권한 필요)
 * 완료 후 이 파일 삭제할 것.
 */
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403);
    echo '권한이 없습니다.';
    exit;
}

$pdo = db();
$log = [];
$targetW = 1200;

$rows = $pdo->query("SELECT id, slug, thumbnail_url FROM blog_posts WHERE thumbnail_url IS NOT NULL AND thumbnail_url != ''")->fetchAll();

foreach ($rows as $row) {
    $url = $row['thumbnail_url'];
    if (strpos($url, '/uploads/blog/') !== 0) {
        $log[] = "⏭️  #{$row['id']} {$row['slug']} — 외부 URL이라 건너뜀 ($url)";
        continue;
    }
    $path = __DIR__ . '/../..' . $url;
    if (!is_file($path)) {
        $log[] = "⚠️  #{$row['id']} {$row['slug']} — 파일 없음 ($path)";
        continue;
    }
    $img = @imagecreatefromstring(file_get_contents($path));
    if (!$img) {
        $log[] = "⚠️  #{$row['id']} {$row['slug']} — 이미지 디코딩 실패";
        continue;
    }
    $w = imagesx($img); $h = imagesy($img);
    if ($w === $targetW) {
        $log[] = "✅ #{$row['id']} {$row['slug']} — 이미 {$targetW}px, 건너뜀";
        imagedestroy($img);
        continue;
    }
    $scale = $targetW / $w;
    $nw = $targetW; $nh = (int)round($h * $scale);
    $resized = imagecreatetruecolor($nw, $nh);
    imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
    imagedestroy($img);
    imagejpeg($resized, $path, 92);
    imagedestroy($resized);
    $log[] = "✅ #{$row['id']} {$row['slug']} — {$w}x{$h} → {$nw}x{$nh}";
}

header('Content-Type: text/plain; charset=UTF-8');
echo implode("\n", $log) . "\n\n완료. 이 파일을 삭제하세요: src/admin/migrate_blog_thumbnails_1200.php\n";
