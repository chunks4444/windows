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
    $rows = $pdo->query('SELECT * FROM space_cards ORDER BY sort_order, id')->fetchAll();

    $countStmt = $pdo->prepare(
        "SELECT COUNT(DISTINCT p.id) FROM library_patterns p
         LEFT JOIN library_keywords k ON k.pattern_id = p.id
         WHERE p.is_active = 1 AND (p.name_ko LIKE :q OR p.id IN (SELECT pattern_id FROM library_keywords WHERE keyword LIKE :q2))"
    );
    foreach ($rows as &$row) {
        $like = '%' . $row['collection_query'] . '%';
        $countStmt->execute([':q' => $like, ':q2' => $like]);
        $row['match_count'] = (int)$countStmt->fetchColumn();
    }
    unset($row);

    $keywords = $pdo->query(
        "SELECT DISTINCT k.keyword FROM library_keywords k JOIN library_patterns p ON p.id = k.pattern_id WHERE p.is_active = 1 ORDER BY k.keyword"
    )->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(['cards' => $rows, 'keywords' => $keywords]);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? '';

function saveSpaceCardImage(string $dataUrl): ?string {
    if (!preg_match('/^data:image\/(jpeg|png|webp);base64,/', $dataUrl)) return null;
    $base64 = substr($dataUrl, strpos($dataUrl, ',') + 1);
    $binary = base64_decode($base64, true);
    if ($binary === false || strlen($binary) > 10 * 1024 * 1024) return null;
    $img = @imagecreatefromstring($binary);
    if (!$img) return null;
    $w = imagesx($img); $h = imagesy($img);
    if ($w > 1200 || $h > 800) {
        $scale = min(1200 / $w, 800 / $h);
        $nw = (int)($w * $scale); $nh = (int)($h * $scale);
        $resized = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img);
        $img = $resized;
    }
    $dir = __DIR__ . '/../../../uploads/space_cards';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $fname = time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
    imagejpeg($img, $dir . '/' . $fname, 88);
    imagedestroy($img);
    return '/uploads/space_cards/' . $fname;
}

if ($action === 'save') {
    $id               = (int)($body['id'] ?? 0);
    $label            = trim($body['label'] ?? '');
    $image_url        = trim($body['image_url'] ?? '');
    $collection_query = trim($body['collection_query'] ?? '');
    $is_active        = (int)($body['is_active'] ?? 1);

    if (!empty($body['image_data'])) {
        $saved = saveSpaceCardImage($body['image_data']);
        if ($saved) $image_url = $saved;
    }

    if ($id) {
        $pdo->prepare('UPDATE space_cards SET label=?, image_url=?, collection_query=?, is_active=? WHERE id=?')
            ->execute([$label, $image_url, $collection_query, $is_active, $id]);
    } else {
        $maxOrder = (int)$pdo->query('SELECT COALESCE(MAX(sort_order),0) FROM space_cards')->fetchColumn();
        $pdo->prepare('INSERT INTO space_cards (label, image_url, collection_query, sort_order, is_active) VALUES (?,?,?,?,?)')
            ->execute([$label, $image_url, $collection_query, $maxOrder + 1, $is_active]);
        $id = (int)$pdo->lastInsertId();
    }

    $stmt = $pdo->prepare('SELECT * FROM space_cards WHERE id=?');
    $stmt->execute([$id]);
    echo json_encode(['ok' => true, 'card' => $stmt->fetch()]);
    exit;
}

if ($action === 'delete') {
    $pdo->prepare('DELETE FROM space_cards WHERE id=?')->execute([(int)($body['id'] ?? 0)]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'toggle') {
    $id = (int)($body['id'] ?? 0);
    $pdo->prepare('UPDATE space_cards SET is_active = 1 - is_active WHERE id=?')->execute([$id]);
    $active = $pdo->prepare('SELECT is_active FROM space_cards WHERE id=?');
    $active->execute([$id]);
    echo json_encode(['ok' => true, 'is_active' => (int)$active->fetchColumn()]);
    exit;
}

if ($action === 'reorder') {
    $ids  = $body['ids'] ?? [];
    $stmt = $pdo->prepare('UPDATE space_cards SET sort_order=? WHERE id=?');
    foreach ($ids as $i => $id) {
        $stmt->execute([$i, (int)$id]);
    }
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action']);
