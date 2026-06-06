<?php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/db.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit;
}

$drawingId = (int)($_GET['drawing_id'] ?? 0);
if (!$drawingId) {
    echo json_encode(['wallpapers' => []]); exit;
}

$pdo  = db();
$stmt = $pdo->prepare('SELECT id, filename, filepath AS url FROM wallpapers WHERE user_id = ? AND drawing_id = ? ORDER BY id ASC');
$stmt->execute([(int)$payload['sub'], $drawingId]);

echo json_encode(['wallpapers' => $stmt->fetchAll()]);
