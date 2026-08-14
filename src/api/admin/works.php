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
require_once __DIR__ . '/../../lib/svg_sanitize.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403);
    echo json_encode(['error' => '권한이 없습니다.']);
    exit;
}

$pdo    = db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $rows = $pdo->query('SELECT * FROM works ORDER BY sort_order, id')->fetchAll();
    echo json_encode(['works' => $rows]);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? '';

function saveWorkImage(string $dataUrl): ?string {
    if (!preg_match('/^data:image\/(jpeg|png|webp);base64,/', $dataUrl, $m)) return null;
    $srcType = $m[1];
    $base64 = substr($dataUrl, strpos($dataUrl, ',') + 1);
    $binary = base64_decode($base64, true);
    if ($binary === false || strlen($binary) > 20 * 1024 * 1024) return null;
    $img = @imagecreatefromstring($binary);
    if (!$img) return null;

    // PNG는 투명 배경을 유지한다 — 무조건 JPG로 저장하면 투명 영역이 검은색으로 뭉개짐
    $isPng = ($srcType === 'png');
    if ($isPng) { imagealphablending($img, false); imagesavealpha($img, true); }

    $w = imagesx($img); $h = imagesy($img);
    if ($w > 2400 || $h > 2400) {
        $scale = min(2400 / $w, 2400 / $h);
        $nw = (int)($w * $scale); $nh = (int)($h * $scale);
        $resized = imagecreatetruecolor($nw, $nh);
        if ($isPng) { imagealphablending($resized, false); imagesavealpha($resized, true); }
        imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img); $img = $resized;
    }
    $dir = __DIR__ . '/../../../uploads/works';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $fname = time() . '_' . bin2hex(random_bytes(4)) . ($isPng ? '.png' : '.jpg');
    if ($isPng) {
        imagepng($img, $dir . '/' . $fname, 6);
    } else {
        imagejpeg($img, $dir . '/' . $fname, 92);
    }
    imagedestroy($img);
    return '/uploads/works/' . $fname;
}

if ($action === 'save') {
    $id          = (int)($body['id'] ?? 0);
    $title       = trim($body['title'] ?? '');
    $description = trim($body['description'] ?? '');
    $image_url   = trim($body['image_url'] ?? '');

    if (!empty($body['image_data'])) {
        $saved = saveWorkImage($body['image_data']);
        if ($saved) $image_url = $saved;
        else { echo json_encode(['error' => '이미지 저장 실패']); exit; }
    }

    $panel_bg    = preg_match('/^#[0-9a-fA-F]{3,6}$/', $body['panel_bg']    ?? '') ? $body['panel_bg']    : '#111111';
    $title_color = preg_match('/^#[0-9a-fA-F]{3,6}$/', $body['title_color'] ?? '') ? $body['title_color'] : '#ffffff';
    $desc_color  = preg_match('/^#[0-9a-fA-F]{3,6}$/', $body['desc_color']  ?? '') ? $body['desc_color']  : '#888888';
    $validEngines = ['classic', 'square', 'cross', 'diamond', 'triangle', 'hexagon'];
    $engine_key   = in_array($body['engine_key'] ?? '', $validEngines, true) ? $body['engine_key'] : null;

    // 카드에 사진 대신 표시할 커스텀 아이콘 SVG — 그대로 페이지에 심어지므로(inline) 반드시 살균
    $icon_svg_raw = trim($body['icon_svg'] ?? '');
    $icon_svg     = null;
    if ($icon_svg_raw !== '') {
        $clean = svg_sanitize_inline($icon_svg_raw);
        if ($clean === '') { echo json_encode(['error' => 'SVG 파일을 읽을 수 없습니다.']); exit; }
        if (strlen($clean) > 200000) { echo json_encode(['error' => 'SVG 파일이 너무 큽니다.']); exit; }
        $icon_svg = $clean;
    }

    if ($id) {
        $pdo->prepare('UPDATE works SET title=?, description=?, image_url=?, panel_bg=?, title_color=?, desc_color=?, engine_key=?, icon_svg=? WHERE id=?')
            ->execute([$title, $description, $image_url, $panel_bg, $title_color, $desc_color, $engine_key, $icon_svg, $id]);
    } else {
        $slug     = make_unique_slug($pdo, 'works', $title);
        $maxOrder = (int)$pdo->query('SELECT COALESCE(MAX(sort_order),0) FROM works')->fetchColumn();
        $pdo->prepare('INSERT INTO works (title, slug, description, image_url, sort_order, panel_bg, title_color, desc_color, engine_key, icon_svg) VALUES (?,?,?,?,?,?,?,?,?,?)')
            ->execute([$title, $slug, $description, $image_url, $maxOrder + 1, $panel_bg, $title_color, $desc_color, $engine_key, $icon_svg]);
        $id = (int)$pdo->lastInsertId();
    }
    $stmt = $pdo->prepare('SELECT * FROM works WHERE id=?');
    $stmt->execute([$id]);
    echo json_encode(['ok' => true, 'work' => $stmt->fetch()]);
    exit;
}

if ($action === 'delete') {
    $pdo->prepare('DELETE FROM works WHERE id=?')->execute([(int)($body['id'] ?? 0)]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'toggle') {
    $pdo->prepare('UPDATE works SET is_active = 1 - is_active WHERE id=?')->execute([(int)($body['id'] ?? 0)]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'reorder') {
    $stmt = $pdo->prepare('UPDATE works SET sort_order=? WHERE id=?');
    foreach (($body['ids'] ?? []) as $i => $id) $stmt->execute([$i, (int)$id]);
    echo json_encode(['ok' => true]);
    exit;
}

/* ── work_images 관리 ── */
if ($action === 'get_images') {
    $stmt = $pdo->prepare('SELECT id, image_url, sort_order FROM work_images WHERE work_id=? ORDER BY sort_order, id');
    $stmt->execute([(int)($body['work_id'] ?? 0)]);
    echo json_encode(['images' => $stmt->fetchAll()]);
    exit;
}

if ($action === 'add_image') {
    $work_id   = (int)($body['work_id'] ?? 0);
    $image_url = trim($body['image_url'] ?? '');
    if (!empty($body['image_data'])) {
        $saved = saveWorkImage($body['image_data']);
        if ($saved) $image_url = $saved;
    }
    if (!$work_id || !$image_url) { echo json_encode(['error' => '필수값 누락']); exit; }
    $maxOrder = (int)$pdo->prepare('SELECT COALESCE(MAX(sort_order),0) FROM work_images WHERE work_id=?')->execute([$work_id]) ? $pdo->query("SELECT COALESCE(MAX(sort_order),0) FROM work_images WHERE work_id=$work_id")->fetchColumn() : 0;
    $pdo->prepare('INSERT INTO work_images (work_id, image_url, sort_order) VALUES (?,?,?)')->execute([$work_id, $image_url, $maxOrder + 1]);
    echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId(), 'image_url' => $image_url]);
    exit;
}

if ($action === 'delete_image') {
    $pdo->prepare('DELETE FROM work_images WHERE id=?')->execute([(int)($body['id'] ?? 0)]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'reorder_images') {
    $stmt = $pdo->prepare('UPDATE work_images SET sort_order=? WHERE id=?');
    foreach (($body['ids'] ?? []) as $i => $id) $stmt->execute([$i, (int)$id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action']);
