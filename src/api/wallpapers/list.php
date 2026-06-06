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
$engine    = trim($_GET['engine']      ?? '');

if (!$engine) {
    echo json_encode(['wallpapers' => []]); exit;
}

$pdo = db();
$uid = (int)$payload['sub'];

if ($drawingId) {
    // 해당 도면 소속 OR 아직 도면 미연결(NULL) 배경 모두 반환
    $stmt = $pdo->prepare(
        'SELECT id, filename, filepath AS url FROM wallpapers
         WHERE user_id = ? AND engine = ? AND (drawing_id = ? OR drawing_id IS NULL)
         ORDER BY id ASC'
    );
    $stmt->execute([$uid, $engine, $drawingId]);
} else {
    // 미저장 도면: 엔진 기준으로 모두 반환
    $stmt = $pdo->prepare(
        'SELECT id, filename, filepath AS url FROM wallpapers
         WHERE user_id = ? AND engine = ?
         ORDER BY id ASC'
    );
    $stmt->execute([$uid, $engine]);
}

echo json_encode(['wallpapers' => $stmt->fetchAll()]);
