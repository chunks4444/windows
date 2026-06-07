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

$method = $_SERVER['REQUEST_METHOD'];
$pdo    = db();

if ($method === 'GET') {
    $rows = $pdo->query('SELECT * FROM hero_slides ORDER BY sort_order, id')->fetchAll();
    echo json_encode(['slides' => $rows]);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? '';

function saveSlideImage(string $dataUrl): ?string {
    if (!preg_match('/^data:image\/(jpeg|png|webp);base64,/', $dataUrl)) return null;
    $base64 = substr($dataUrl, strpos($dataUrl, ',') + 1);
    $binary = base64_decode($base64, true);
    if ($binary === false || strlen($binary) > 15 * 1024 * 1024) return null;
    $img = @imagecreatefromstring($binary);
    if (!$img) return null;
    $w = imagesx($img); $h = imagesy($img);
    if ($w > 1920 || $h > 1080) {
        $scale = min(1920 / $w, 1080 / $h);
        $nw = (int)($w * $scale); $nh = (int)($h * $scale);
        $resized = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img);
        $img = $resized;
    }
    $dir = __DIR__ . '/../../../uploads/hero_slides';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $fname = time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
    imagejpeg($img, $dir . '/' . $fname, 90);
    imagedestroy($img);
    return '/uploads/hero_slides/' . $fname;
}

if ($action === 'save') {
    $id        = (int)($body['id'] ?? 0);
    $title     = trim($body['title'] ?? '');
    $subtitle  = trim($body['subtitle'] ?? '');
    $image_url = trim($body['image_url'] ?? '');
    $is_active = (int)($body['is_active'] ?? 1);

    if (!empty($body['image_data'])) {
        $saved = saveSlideImage($body['image_data']);
        if ($saved) {
            $image_url = $saved;
        } else {
            echo json_encode(['error' => '이미지 저장 실패 (지원 형식: jpg/png/webp, 최대 15MB)']);
            exit;
        }
    }

    if ($id) {
        $pdo->prepare('UPDATE hero_slides SET title=?, subtitle=?, image_url=?, is_active=? WHERE id=?')
            ->execute([$title, $subtitle, $image_url, $is_active, $id]);
    } else {
        $maxOrder = (int)$pdo->query('SELECT COALESCE(MAX(sort_order),0) FROM hero_slides')->fetchColumn();
        $pdo->prepare('INSERT INTO hero_slides (title, subtitle, image_url, sort_order, is_active) VALUES (?,?,?,?,?)')
            ->execute([$title, $subtitle, $image_url, $maxOrder + 1, $is_active]);
        $id = (int)$pdo->lastInsertId();
    }

    $stmt = $pdo->prepare('SELECT * FROM hero_slides WHERE id=?');
    $stmt->execute([$id]);
    echo json_encode(['ok' => true, 'slide' => $stmt->fetch()]);
    exit;
}

if ($action === 'delete') {
    $pdo->prepare('DELETE FROM hero_slides WHERE id=?')->execute([(int)($body['id'] ?? 0)]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'toggle') {
    $id = (int)($body['id'] ?? 0);
    $pdo->prepare('UPDATE hero_slides SET is_active = 1 - is_active WHERE id=?')->execute([$id]);
    $active = $pdo->prepare('SELECT is_active FROM hero_slides WHERE id=?');
    $active->execute([$id]);
    echo json_encode(['ok' => true, 'is_active' => (int)$active->fetchColumn()]);
    exit;
}

if ($action === 'reorder') {
    $ids  = $body['ids'] ?? [];
    $stmt = $pdo->prepare('UPDATE hero_slides SET sort_order=? WHERE id=?');
    foreach ($ids as $i => $id) {
        $stmt->execute([$i, (int)$id]);
    }
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action']);
