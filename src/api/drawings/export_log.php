<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';

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

$body       = json_decode(file_get_contents('php://input'), true);
$drawingId  = isset($body['drawing_id']) && $body['drawing_id'] ? (int)$body['drawing_id'] : null;
$engine     = preg_replace('/[^a-z]/', '', strtolower($body['engine'] ?? ''));
$format     = in_array($body['format'] ?? '', ['png', 'pdf']) ? $body['format'] : null;
$drawingName = substr(trim($body['drawing_name'] ?? ''), 0, 100);
$version     = substr(trim($body['version']      ?? ''), 0, 10);

if (!$engine || !$format) {
    http_response_code(422); echo json_encode(['error' => 'engine, format 필드가 필요합니다.']); exit;
}

$pdo = db();
$pdo->prepare('INSERT INTO drawing_export_logs (user_id, drawing_id, engine, format, drawing_name, version) VALUES (?,?,?,?,?,?)')
    ->execute([(int)$payload['sub'], $drawingId, $engine, $format, $drawingName, $version]);

echo json_encode(['ok' => true]);
