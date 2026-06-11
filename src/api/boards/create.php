<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit; }

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/db.php';

$payload = jwt_from_request();
if (!$payload) { http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit; }

$body = json_decode(file_get_contents('php://input'), true);
$name = trim($body['name'] ?? '');
if (!$name) { http_response_code(422); echo json_encode(['error' => '보드 이름을 입력해주세요.']); exit; }

$pdo  = db();
$stmt = $pdo->prepare('INSERT INTO boards (user_id, name) VALUES (?, ?)');
$stmt->execute([(int)$payload['sub'], $name]);
echo json_encode(['board_id' => (int)$pdo->lastInsertId(), 'name' => $name]);
