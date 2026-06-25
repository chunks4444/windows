<?php
header('Content-Type: application/json; charset=UTF-8');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}

require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$svg  = $body['svg_data'] ?? '';

if (preg_match('/^data:[^,]*;base64,/i', $svg, $m)) {
    $svg = base64_decode(substr($svg, strlen($m[0])), true);
}
$svg = is_string($svg) ? trim($svg) : '';

if ($svg === '' || strlen($svg) > 2 * 1024 * 1024) {
    http_response_code(422); echo json_encode(['error' => 'SVG 파일이 너무 크거나 비어 있습니다 (최대 2MB).']); exit;
}
if (stripos($svg, '<svg') === false) {
    http_response_code(422); echo json_encode(['error' => '유효한 SVG 파일이 아닙니다.']); exit;
}
if (preg_match('/<script[\s>]|[\s"\']on[a-z]+\s*=\s*["\']|javascript:/i', $svg)) {
    http_response_code(422); echo json_encode(['error' => '허용되지 않는 내용이 포함된 SVG입니다.']); exit;
}

$userId = (int)$payload['sub'];
$dir    = __DIR__ . '/../../../uploads/svg_insert/' . $userId;
if (!is_dir($dir)) mkdir($dir, 0755, true);

$fname = time() . '_' . bin2hex(random_bytes(4)) . '.svg';
file_put_contents($dir . '/' . $fname, $svg);

echo json_encode(['ok' => true, 'url' => '/uploads/svg_insert/' . $userId . '/' . $fname]);
