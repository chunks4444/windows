<?php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/db.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit;
}

$engine = $_GET['engine'] ?? '';

$pdo  = db();
$stmt = $pdo->prepare('SELECT id, filename, filepath AS url FROM wallpapers WHERE user_id = ? AND engine = ? ORDER BY id ASC');
$stmt->execute([(int)$payload['sub'], $engine]);

echo json_encode(['wallpapers' => $stmt->fetchAll()]);
