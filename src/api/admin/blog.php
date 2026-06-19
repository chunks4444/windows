<?php
header('Content-Type: application/json; charset=UTF-8');
set_exception_handler(function(Throwable $e) {
    if (!headers_sent()) http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
});
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403);
    echo json_encode(['error' => '권한이 없습니다.']);
    exit;
}

$pdo    = db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $rows = $pdo->query('SELECT * FROM blog_posts ORDER BY sort_order, id')->fetchAll();
    echo json_encode(['posts' => $rows]);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? '';

function saveBlogImage(string $dataUrl): ?string {
    if (!preg_match('/^data:image\/(jpeg|png|webp);base64,/', $dataUrl)) return null;
    $base64 = substr($dataUrl, strpos($dataUrl, ',') + 1);
    $binary = base64_decode($base64, true);
    if ($binary === false || strlen($binary) > 20 * 1024 * 1024) return null;
    $img = @imagecreatefromstring($binary);
    if (!$img) return null;
    $w = imagesx($img); $h = imagesy($img);
    if ($w > 2400 || $h > 2400) {
        $scale = min(2400 / $w, 2400 / $h);
        $nw = (int)($w * $scale); $nh = (int)($h * $scale);
        $resized = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img); $img = $resized;
    }
    $dir = __DIR__ . '/../../../uploads/blog';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $fname = time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
    imagejpeg($img, $dir . '/' . $fname, 92);
    imagedestroy($img);
    return '/uploads/blog/' . $fname;
}

if ($action === 'save') {
    $id            = (int)($body['id'] ?? 0);
    $title         = trim($body['title'] ?? '');
    $summary       = trim($body['summary'] ?? '');
    $content       = trim($body['content'] ?? '');
    $thumbnail_url = trim($body['thumbnail_url'] ?? '');

    if (!empty($body['thumbnail_data'])) {
        $saved = saveBlogImage($body['thumbnail_data']);
        if ($saved) $thumbnail_url = $saved;
        else { echo json_encode(['error' => '이미지 저장 실패']); exit; }
    }

    if ($id) {
        $pdo->prepare('UPDATE blog_posts SET title=?, summary=?, content=?, thumbnail_url=? WHERE id=?')
            ->execute([$title, $summary, $content, $thumbnail_url, $id]);
    } else {
        $maxOrder = (int)$pdo->query('SELECT COALESCE(MAX(sort_order),0) FROM blog_posts')->fetchColumn();
        $pdo->prepare('INSERT INTO blog_posts (title, summary, content, thumbnail_url, sort_order) VALUES (?,?,?,?,?)')
            ->execute([$title, $summary, $content, $thumbnail_url, $maxOrder + 1]);
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
    $pdo->prepare('UPDATE blog_posts SET is_active = 1 - is_active WHERE id=?')->execute([(int)($body['id'] ?? 0)]);
    echo json_encode(['ok' => true]);
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
