<?php
header('Content-Type: application/json; charset=UTF-8');
set_exception_handler(function(Throwable $e) {
    if (!headers_sent()) http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
});
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/slug.php';
require_once __DIR__ . '/../../lib/image_resize.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403);
    echo json_encode(['error' => '권한이 없습니다.']);
    exit;
}

$pdo    = db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $rows = $pdo->query('
        SELECT p.*, s.name AS series_name
        FROM blog_posts p
        LEFT JOIN blog_series s ON s.id = p.series_id
        ORDER BY p.sort_order, p.id
    ')->fetchAll();
    echo json_encode(['posts' => $rows]);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? '';

function saveBlogImage(string $dataUrl): ?string {
    if (!preg_match('/^data:image\/(jpeg|png|webp);base64,/', $dataUrl, $m)) return null;
    $format = $m[1];
    $base64 = substr($dataUrl, strpos($dataUrl, ',') + 1);
    $binary = base64_decode($base64, true);
    if ($binary === false || strlen($binary) > 20 * 1024 * 1024) return null;
    $img = @imagecreatefromstring($binary);
    if (!$img) return null;
    // 구글 검색결과 큰 이미지 미리보기 조건(가로·세로 각각 최소 1200px)을 항상 만족시키기 위해 업스케일
    $img = resize_image_min_size($img, 1200);
    $dir = __DIR__ . '/../../../uploads/blog';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $ext = $format === 'jpeg' ? 'jpg' : $format;
    $fname = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if ($format === 'png') {
        imagesavealpha($img, true);
        imagepng($img, $dir . '/' . $fname, 6);
    } elseif ($format === 'webp') {
        imagewebp($img, $dir . '/' . $fname, 90);
    } else {
        imagejpeg($img, $dir . '/' . $fname, 92);
    }
    imagedestroy($img);
    return '/uploads/blog/' . $fname;
}

if ($action === 'save') {
    $id             = (int)($body['id'] ?? 0);
    $title          = trim($body['title'] ?? '');
    $summary        = trim($body['summary'] ?? '');
    $cta_text       = trim($body['cta_text'] ?? '');
    $source_text    = trim($body['source_text'] ?? '') ?: null;
    $content        = trim($body['content'] ?? '');
    $thumbnail_url  = trim($body['thumbnail_url'] ?? '');
    $is_featured    = !empty($body['is_featured']) ? 1 : 0;
    $series_id      = (int)($body['series_id'] ?? 0) ?: null;
    $series_order   = (int)($body['series_order'] ?? 0);
    $related_engine = trim($body['related_engine'] ?? '') ?: null;
    $related_drawing_id = (int)($body['related_drawing_id'] ?? 0) ?: null;
    $question       = trim($body['question'] ?? '');

    // 검색엔진 연산자로 오인될 수 있는 문자는 제목에 못 들어가게 막는다 (- — " ' * :)
    if (preg_match('/[-—"\'*:]/u', $title)) {
        http_response_code(422);
        echo json_encode(['error' => '제목에는 - — " \' * : 문자를 쓸 수 없습니다.']);
        exit;
    }

    if (!empty($body['thumbnail_data'])) {
        $saved = saveBlogImage($body['thumbnail_data']);
        if ($saved) $thumbnail_url = $saved;
        else { echo json_encode(['error' => '이미지 저장 실패']); exit; }
    }

    if ($id) {
        // is_featured는 목록의 별표 토글(action=toggle_featured) 전용 — 일반 저장에서는 건드리지 않는다
        $pdo->prepare('UPDATE blog_posts SET title=?, summary=?, cta_text=?, source_text=?, content=?, thumbnail_url=?,
                series_id=?, series_order=?, related_engine=?, related_drawing_id=?, question=? WHERE id=?')
            ->execute([$title, $summary, $cta_text, $source_text, $content, $thumbnail_url,
                $series_id, $series_order, $related_engine, $related_drawing_id, $question, $id]);
    } else {
        // 새 글은 항상 비공개(is_active=0)로 시작하고 slug는 NULL — 관리자가 검토 후
        // "표시"(공개 전환)를 눌러야 그 시점에 slug가 생성된다 (draft 상태에는 URL이 없어야 함)
        // 새 글은 목록 맨 앞(sort_order=0)에 놓는다 — 블로그 인덱스가 최신 글을 앞에 기대하므로
        // 기존 글들을 전부 한 칸씩 뒤로 미룸(MAX+1로 맨 뒤에 붙이면 관리자가 매번 수동으로 끌어올려야 했음)
        $pdo->exec('UPDATE blog_posts SET sort_order = sort_order + 1');
        $pdo->prepare('INSERT INTO blog_posts (title, slug, summary, cta_text, source_text, content, thumbnail_url, is_featured, sort_order,
                series_id, series_order, related_engine, related_drawing_id, question, is_active) VALUES (?,NULL,?,?,?,?,?,?,0,?,?,?,?,?,0)')
            ->execute([$title, $summary, $cta_text, $source_text, $content, $thumbnail_url, $is_featured,
                $series_id, $series_order, $related_engine, $related_drawing_id, $question]);
        $id = (int)$pdo->lastInsertId();
    }
    $stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE id=?');
    $stmt->execute([$id]);
    echo json_encode(['ok' => true, 'post' => $stmt->fetch()]);
    exit;
}

if ($action === 'upload_content_image') {
    $saved = saveBlogImage($body['image_data'] ?? '');
    if (!$saved) { echo json_encode(['error' => '이미지 저장 실패']); exit; }
    echo json_encode(['ok' => true, 'url' => $saved]);
    exit;
}

if ($action === 'delete') {
    $pdo->prepare('DELETE FROM blog_posts WHERE id=?')->execute([(int)($body['id'] ?? 0)]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'toggle') {
    $id = (int)($body['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT title, slug, is_active FROM blog_posts WHERE id=?');
    $stmt->execute([$id]);
    $post = $stmt->fetch();
    if (!$post) { http_response_code(404); echo json_encode(['error' => '글을 찾을 수 없습니다.']); exit; }

    $newActive = $post['is_active'] ? 0 : 1;
    if ($newActive && !$post['slug']) {
        // 비공개 → 공개로 전환되는 순간(=검토 완료, "표시" 클릭 시점)에 slug를 확정한다
        $slug = make_unique_slug($pdo, 'blog_posts', $post['title'], $id);
        $pdo->prepare('UPDATE blog_posts SET is_active=?, slug=? WHERE id=?')->execute([$newActive, $slug, $id]);
    } else {
        $pdo->prepare('UPDATE blog_posts SET is_active=? WHERE id=?')->execute([$newActive, $id]);
    }
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'toggle_featured') {
    $id = (int)($body['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT is_featured FROM blog_posts WHERE id=?');
    $stmt->execute([$id]);
    $post = $stmt->fetch();
    if (!$post) { http_response_code(404); echo json_encode(['error' => '글을 찾을 수 없습니다.']); exit; }

    $newFeatured = $post['is_featured'] ? 0 : 1;
    $pdo->prepare('UPDATE blog_posts SET is_featured=? WHERE id=?')->execute([$newFeatured, $id]);
    echo json_encode(['ok' => true, 'is_featured' => $newFeatured]);
    exit;
}

if ($action === 'reorder') {
    $stmt = $pdo->prepare('UPDATE blog_posts SET sort_order=? WHERE id=?');
    foreach (($body['ids'] ?? []) as $i => $id) $stmt->execute([$i, (int)$id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action']);
