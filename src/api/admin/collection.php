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
    $rows = $pdo->query(
        'SELECT p.id, p.name_ko, p.drawing_id, p.pattern_category, p.image_path, p.sort_order, p.is_active,
                GROUP_CONCAT(k.keyword ORDER BY k.id SEPARATOR ",") AS keywords
         FROM library_patterns p
         LEFT JOIN library_keywords k ON k.pattern_id = p.id
         GROUP BY p.id
         ORDER BY p.sort_order, p.id'
    )->fetchAll();
    foreach ($rows as &$r) {
        $r['keywords'] = $r['keywords'] ? explode(',', $r['keywords']) : [];
    }
    echo json_encode(['patterns' => $rows]);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];

function fixImageOrientation($img, string $binary) {
    if (!function_exists('exif_read_data')) return $img;
    $tmp = tmpfile();
    if (!$tmp) return $img;
    fwrite($tmp, $binary);
    $meta = stream_get_meta_data($tmp);
    $exif = @exif_read_data($meta['uri']);
    fclose($tmp);
    $orientation = $exif['Orientation'] ?? 1;
    switch ((int)$orientation) {
        case 3: return imagerotate($img, 180, 0);
        case 6: return imagerotate($img, -90, 0);
        case 8: return imagerotate($img, 90, 0);
        default: return $img;
    }
}

function saveLibraryImage(string $input): ?string {
    if (preg_match('/^data:image\/(jpeg|png|webp);base64,/', $input)) {
        $base64 = substr($input, strpos($input, ',') + 1);
        $binary = base64_decode($base64, true);
    } elseif (strpos($input, '/uploads/') === 0) {
        // 도면 썸네일이 base64가 아니라 파일 경로로 저장된 경우 (Drawing::persistThumbnail 참고)
        // 확장자가 없는 경로일 수 있어 .png를 붙여서도 확인한다 (.htaccess rewrite와 동일한 규칙)
        $path = __DIR__ . '/../../../' . ltrim($input, '/');
        if (is_file($path)) {
            $binary = file_get_contents($path);
        } elseif (is_file($path . '.png')) {
            $binary = file_get_contents($path . '.png');
        } else {
            $binary = false;
        }
    } else {
        return null;
    }
    if ($binary === false || strlen($binary) > 10 * 1024 * 1024) return null;

    $img = @imagecreatefromstring($binary);
    if (!$img) return null;

    $img = fixImageOrientation($img, $binary);

    $w = imagesx($img); $h = imagesy($img);
    $nw = $w; $nh = $h;
    if ($w > 1024 || $h > 1024) {
        $scale = min(1024 / $w, 1024 / $h);
        $nw = (int)($w * $scale); $nh = (int)($h * $scale);
    }

    // PNG로 저장해 투명 배경을 그대로 유지한다 (JPEG 변환 시 알파 채널이 검게 뭉개지는
    // 문제도 없고, 선 위주 패턴 이미지라 JPEG 압축 아티팩트도 피할 수 있다).
    $canvas = imagecreatetruecolor($nw, $nh);
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
    imagefill($canvas, 0, 0, $transparent);
    imagealphablending($canvas, true);
    imagecopyresampled($canvas, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
    imagedestroy($img);

    $dir = __DIR__ . '/../../../uploads/library';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $fname = time() . '_' . bin2hex(random_bytes(4)) . '.png';
    imagepng($canvas, $dir . '/' . $fname, 6);
    imagedestroy($canvas);
    return '/uploads/library/' . $fname;
}

function drawingThumbnailImage(PDO $pdo, int $drawingId): ?string {
    $stmt = $pdo->prepare('SELECT thumbnail FROM drawings WHERE id=?');
    $stmt->execute([$drawingId]);
    $thumb = $stmt->fetchColumn();
    return $thumb ? saveLibraryImage($thumb) : null;
}

if ($method === 'POST') {
    $name_ko    = trim($body['name_ko'] ?? '');
    $drawing_id = (int)($body['drawing_id'] ?? 0) ?: null;
    $pattern_category = (int)($body['pattern_category'] ?? 0) ?: null;
    $order      = (int)($body['sort_order'] ?? 0);
    $keywords   = array_values(array_unique(array_filter(array_map('trim', (array)($body['keywords'] ?? [])))));

    if (!$name_ko) {
        http_response_code(400);
        echo json_encode(['error' => '이름은 필수입니다.']);
        exit;
    }

    $image_path = '';
    if (!empty($body['image'])) {
        $image_path = saveLibraryImage($body['image']) ?? '';
    } elseif ($drawing_id) {
        $image_path = drawingThumbnailImage($pdo, $drawing_id) ?? '';
    }

    $slug = bin2hex(random_bytes(6));

    $pdo->prepare('INSERT INTO library_patterns (slug, name_ko, drawing_id, pattern_category, image_path, sort_order) VALUES (?,?,?,?,?,?)')
        ->execute([$slug, $name_ko, $drawing_id, $pattern_category, $image_path, $order]);
    $id = (int)$pdo->lastInsertId();

    $stmt = $pdo->prepare('INSERT INTO library_keywords (pattern_id, keyword) VALUES (?,?)');
    foreach ($keywords as $kw) {
        $stmt->execute([$id, $kw]);
    }

    echo json_encode(['ok' => true, 'id' => $id]);
    exit;
}

if ($method === 'PUT') {
    $id      = (int)($body['id'] ?? 0);
    $name_ko = trim($body['name_ko'] ?? '');
    $order   = (int)($body['sort_order'] ?? 0);
    $active  = isset($body['is_active']) ? (int)(bool)$body['is_active'] : 1;
    $keywords = array_key_exists('keywords', $body)
        ? array_values(array_unique(array_filter(array_map('trim', (array)$body['keywords']))))
        : null;
    $hasDrawingId = array_key_exists('drawing_id', $body);
    $drawing_id   = $hasDrawingId ? ((int)($body['drawing_id'] ?? 0) ?: null) : null;
    $hasCategory  = array_key_exists('pattern_category', $body);
    $pattern_category = $hasCategory ? ((int)($body['pattern_category'] ?? 0) ?: null) : null;

    if (!$id || !$name_ko) {
        http_response_code(400);
        echo json_encode(['error' => 'id와 이름은 필수입니다.']);
        exit;
    }

    $image_path = null;
    if (!empty($body['image'])) {
        $image_path = saveLibraryImage($body['image']);
    } elseif ($hasDrawingId && $drawing_id) {
        $image_path = drawingThumbnailImage($pdo, $drawing_id);
    }

    if ($image_path !== null) {
        $old = $pdo->prepare('SELECT image_path FROM library_patterns WHERE id=?');
        $old->execute([$id]);
        $oldRow = $old->fetch();
        if ($oldRow && $oldRow['image_path']) {
            $f = __DIR__ . '/../../../' . ltrim($oldRow['image_path'], '/');
            if (file_exists($f)) unlink($f);
        }
    }

    $setParts = ['name_ko=?', 'sort_order=?', 'is_active=?'];
    $setArgs  = [$name_ko, $order, $active];
    if ($hasDrawingId)         { $setParts[] = 'drawing_id=?';       $setArgs[] = $drawing_id; }
    if ($hasCategory)         { $setParts[] = 'pattern_category=?'; $setArgs[] = $pattern_category; }
    if ($image_path !== null) { $setParts[] = 'image_path=?';       $setArgs[] = $image_path; }
    $setArgs[] = $id;

    $pdo->prepare('UPDATE library_patterns SET ' . implode(', ', $setParts) . ' WHERE id=?')
        ->execute($setArgs);

    if ($keywords !== null) {
        $pdo->prepare('DELETE FROM library_keywords WHERE pattern_id=?')->execute([$id]);
        $stmt = $pdo->prepare('INSERT INTO library_keywords (pattern_id, keyword) VALUES (?,?)');
        foreach ($keywords as $kw) {
            $stmt->execute([$id, $kw]);
        }
    }

    echo json_encode(['ok' => true]);
    exit;
}

if ($method === 'DELETE') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) { http_response_code(400); echo json_encode(['error' => 'id 필요']); exit; }

    $row = $pdo->prepare('SELECT image_path FROM library_patterns WHERE id=?');
    $row->execute([$id]);
    $r = $row->fetch();
    if ($r && $r['image_path']) {
        $f = __DIR__ . '/../../../' . ltrim($r['image_path'], '/');
        if (file_exists($f)) unlink($f);
    }

    $pdo->prepare('DELETE FROM library_patterns WHERE id=?')->execute([$id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
