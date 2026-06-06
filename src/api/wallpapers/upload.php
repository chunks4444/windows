<?php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/db.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit;
}

$body      = json_decode(file_get_contents('php://input'), true);
$dataUrl   = $body['image']      ?? '';
$filename  = $body['filename']   ?? '';
$engine    = $body['engine']     ?? '';
$drawingId = (int)($body['drawing_id'] ?? 0) ?: null;

if (!$dataUrl || !preg_match('/^data:image\/(jpeg|png|webp);base64,/', $dataUrl, $m)) {
    http_response_code(422); echo json_encode(['error' => '유효하지 않은 이미지입니다.']); exit;
}

// base64 디코딩 및 크기 검증 (원본 바이너리 5MB 이내)
$base64 = substr($dataUrl, strpos($dataUrl, ',') + 1);
$binary = base64_decode($base64, true);
if ($binary === false || strlen($binary) > 5 * 1024 * 1024) {
    http_response_code(422); echo json_encode(['error' => '이미지가 너무 큽니다 (최대 5MB).']); exit;
}

// GD로 1600px 초과 여부 확인 (클라이언트 우회 방어)
$img = @imagecreatefromstring($binary);
if (!$img) {
    http_response_code(422); echo json_encode(['error' => '이미지를 처리할 수 없습니다.']); exit;
}
$w = imagesx($img); $h = imagesy($img);
if ($w > 1600 || $h > 1600) {
    // 서버에서도 리사이즈
    $scale  = min(1600 / $w, 1600 / $h);
    $nw = (int)($w * $scale); $nh = (int)($h * $scale);
    $resized = imagecreatetruecolor($nw, $nh);
    imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
    imagedestroy($img);
    $img = $resized;
}

$userId = (int)$payload['sub'];
$dir    = __DIR__ . '/../../../uploads/wallpapers/' . $userId;
if (!is_dir($dir)) mkdir($dir, 0755, true);

$fname = time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
$path  = $dir . '/' . $fname;
imagejpeg($img, $path, 85);
imagedestroy($img);

$webPath = '/uploads/wallpapers/' . $userId . '/' . $fname;

$pdo  = db();
$stmt = $pdo->prepare('INSERT INTO wallpapers (user_id, engine, drawing_id, filename, filepath) VALUES (?, ?, ?, ?, ?)');
$stmt->execute([$userId, $engine, $drawingId, $filename, $webPath]);
echo json_encode(['ok' => true, 'id' => (int)$pdo->lastInsertId(), 'url' => $webPath]);
