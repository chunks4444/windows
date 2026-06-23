<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/meta.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403);
    echo json_encode(['error' => '권한이 없습니다.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $rows = db()->query('SELECT id, path, title, description, keywords, og_image FROM page_meta ORDER BY path')->fetchAll();
    echo json_encode(['pages' => $rows]);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];

function saveMetaOgImage(string $dataUrl): ?string {
    if (!preg_match('/^data:image\/(jpeg|png|webp);base64,/', $dataUrl)) return null;
    $base64 = substr($dataUrl, strpos($dataUrl, ',') + 1);
    $binary = base64_decode($base64, true);
    if ($binary === false || strlen($binary) > 15 * 1024 * 1024) return null;
    $img = @imagecreatefromstring($binary);
    if (!$img) return null;
    $w = imagesx($img); $h = imagesy($img);
    if ($w > 1200 || $h > 630) {
        $scale = min(1200 / $w, 630 / $h);
        $nw = (int)($w * $scale); $nh = (int)($h * $scale);
        $resized = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img);
        $img = $resized;
    }
    $dir = __DIR__ . '/../../../uploads/meta';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $fname = time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
    imagejpeg($img, $dir . '/' . $fname, 90);
    imagedestroy($img);
    return SITE_URL . '/uploads/meta/' . $fname;
}

if ($method === 'POST') {
    $path = trim($body['path'] ?? '');
    if (!$path) { http_response_code(400); echo json_encode(['error' => 'path 필요']); exit; }
    $ogImage = trim($body['og_image'] ?? '');
    if (!empty($body['og_image_data'])) {
        $saved = saveMetaOgImage($body['og_image_data']);
        if (!$saved) { http_response_code(422); echo json_encode(['error' => '이미지 저장 실패 (지원 형식: jpg/png/webp, 최대 15MB)']); exit; }
        $ogImage = $saved;
    }
    try {
        db()->prepare('INSERT INTO page_meta (path, title, description, keywords, og_image) VALUES (?,?,?,?,?)')
             ->execute([$path, $body['title'] ?? '', $body['description'] ?? '', $body['keywords'] ?? '', $ogImage]);
        echo json_encode(['ok' => true, 'id' => db()->lastInsertId()]);
    } catch (PDOException $e) {
        http_response_code(409); echo json_encode(['error' => '경로가 이미 존재합니다.']);
    }
    exit;
}

if ($method === 'PUT') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) { http_response_code(400); echo json_encode(['error' => 'id 필요']); exit; }
    $ogImage = trim($body['og_image'] ?? '');
    if (!empty($body['og_image_data'])) {
        $saved = saveMetaOgImage($body['og_image_data']);
        if (!$saved) { http_response_code(422); echo json_encode(['error' => '이미지 저장 실패 (지원 형식: jpg/png/webp, 최대 15MB)']); exit; }
        $ogImage = $saved;
    }
    db()->prepare('UPDATE page_meta SET title=?, description=?, keywords=?, og_image=? WHERE id=?')
         ->execute([$body['title'] ?? '', $body['description'] ?? '', $body['keywords'] ?? '', $ogImage, $id]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($method === 'DELETE') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) { http_response_code(400); echo json_encode(['error' => 'id 필요']); exit; }
    db()->prepare('DELETE FROM page_meta WHERE id=?')->execute([$id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
