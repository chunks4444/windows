<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit; }

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/db.php';

$payload = jwt_from_request();
if (!$payload) { http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit; }

$body       = json_decode(file_get_contents('php://input'), true);
$boardId    = (int)($body['board_id']   ?? 0);
$patternId  = (int)($body['pattern_id'] ?? 0);
if (!$boardId || !$patternId) { http_response_code(422); echo json_encode(['error' => '필드가 부족합니다.']); exit; }

$pdo  = db();
// 보드가 본인 소유인지 확인
$stmt = $pdo->prepare('SELECT id FROM boards WHERE id = ? AND user_id = ?');
$stmt->execute([$boardId, (int)$payload['sub']]);
if (!$stmt->fetch()) { http_response_code(403); echo json_encode(['error' => '권한이 없습니다.']); exit; }

$pdo->prepare('INSERT IGNORE INTO board_items (board_id, pattern_id) VALUES (?, ?)')->execute([$boardId, $patternId]);
echo json_encode(['ok' => true]);
