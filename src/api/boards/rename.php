<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/db.php';

$payload = jwt_from_request();
if (!$payload) { http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit; }

$body    = json_decode(file_get_contents('php://input'), true);
$boardId = (int)($body['board_id'] ?? 0);
$name    = trim($body['name'] ?? '');

if (!$boardId || $name === '') { http_response_code(422); echo json_encode(['error' => 'board_id, name이 필요합니다.']); exit; }

$pdo  = db();
$stmt = $pdo->prepare('UPDATE boards SET name = ? WHERE id = ? AND user_id = ?');
$stmt->execute([$name, $boardId, (int)$payload['sub']]);

if ($stmt->rowCount() === 0) { http_response_code(403); echo json_encode(['error' => '권한이 없거나 보드가 없습니다.']); exit; }

echo json_encode(['ok' => true]);
