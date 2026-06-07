<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit; }

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/db.php';

$payload = jwt_from_request();
if (!$payload) { http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit; }

$body    = json_decode(file_get_contents('php://input'), true);
$boardId = (int)($body['board_id'] ?? 0);
if (!$boardId) { http_response_code(422); echo json_encode(['error' => 'board_id가 필요합니다.']); exit; }

$pdo  = db();
$stmt = $pdo->prepare('DELETE FROM boards WHERE id = ? AND user_id = ?');
$stmt->execute([$boardId, (int)$payload['sub']]);
echo json_encode(['ok' => $stmt->rowCount() > 0]);
