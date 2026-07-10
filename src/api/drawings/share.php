<?php
// 도면 공유 링크 on/off 토글. 소유자 본인만 가능.
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../lib/cors.php';
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/meta.php';

$payload = jwt_from_request();
if (!$payload) { http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit; }

$body      = json_decode(file_get_contents('php://input'), true) ?? [];
$drawingId = (int)($body['id'] ?? 0);
$shared    = !empty($body['shared']) ? 1 : 0;

if (!$drawingId) { http_response_code(422); echo json_encode(['error' => 'id 필수']); exit; }

$pdo  = db();
$uid  = (int)$payload['sub'];

$stmt = $pdo->prepare('SELECT type, user_id FROM drawings WHERE id = ?');
$stmt->execute([$drawingId]);
$row = $stmt->fetch();

if (!$row) { http_response_code(404); echo json_encode(['error' => '도면을 찾을 수 없습니다.']); exit; }
if ((int)$row['user_id'] !== $uid) { http_response_code(403); echo json_encode(['error' => '본인 도면만 공유할 수 있습니다.']); exit; }

$pdo->prepare('UPDATE drawings SET is_shared = ? WHERE id = ?')->execute([$shared, $drawingId]);

$shareUrl = SITE_URL . '/src/engine/' . $row['type'] . '/' . $row['type'] . '.php?drawing_id=' . $drawingId;

echo json_encode(['ok' => true, 'is_shared' => (bool)$shared, 'share_url' => $shareUrl]);
