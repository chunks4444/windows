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

// src/guide/_head.php의 $guide_nav와 동일한 순서 — 목록을 실제 사이트 메뉴 순서로 보여주기 위함
$slugOrder = [
    'intro', 'getting-started', 'studio-classic', 'studio-square', 'studio-cross',
    'studio-diamond', 'studio-triangle', 'studio-hexagon', 'canvas-toolbar', 'drawing',
    'export', 'render', 'collection', 'account', 'order', 'delivery', 'faq',
];

if ($method === 'GET') {
    $rows = $pdo->query('SELECT id, slug, title, body_html, updated_at FROM guide_articles')->fetchAll();
    $bySlug = [];
    foreach ($rows as $r) $bySlug[$r['slug']] = $r;
    $ordered = [];
    foreach ($slugOrder as $slug) {
        if (isset($bySlug[$slug])) $ordered[] = $bySlug[$slug];
    }
    echo json_encode(['articles' => $ordered]);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? '';

function saveGuideImage(string $dataUrl): ?string {
    if (!preg_match('/^data:image\/(jpeg|png|webp);base64,/', $dataUrl, $m)) return null;
    $format = $m[1];
    $base64 = substr($dataUrl, strpos($dataUrl, ',') + 1);
    $binary = base64_decode($base64, true);
    if ($binary === false || strlen($binary) > 20 * 1024 * 1024) return null;
    $dir = __DIR__ . '/../../../uploads/guide';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $ext   = $format === 'jpeg' ? 'jpg' : $format;
    $fname = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    file_put_contents($dir . '/' . $fname, $binary);
    return '/uploads/guide/' . $fname;
}

if ($action === 'save') {
    $slug     = trim($body['slug'] ?? '');
    $title    = trim($body['title'] ?? '');
    $bodyHtml = $body['body_html'] ?? '';

    if (!in_array($slug, $slugOrder, true) || $title === '') {
        http_response_code(422);
        echo json_encode(['error' => 'slug/제목이 올바르지 않습니다.']);
        exit;
    }

    $pdo->prepare('INSERT INTO guide_articles (slug, title, body_html) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE title = VALUES(title), body_html = VALUES(body_html)')
        ->execute([$slug, $title, $bodyHtml]);

    $stmt = $pdo->prepare('SELECT id, slug, title, body_html, updated_at FROM guide_articles WHERE slug=?');
    $stmt->execute([$slug]);
    echo json_encode(['ok' => true, 'article' => $stmt->fetch()]);
    exit;
}

if ($action === 'upload_content_image') {
    $saved = saveGuideImage($body['image_data'] ?? '');
    if (!$saved) { echo json_encode(['error' => '이미지 저장 실패']); exit; }
    echo json_encode(['ok' => true, 'url' => $saved]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action']);
