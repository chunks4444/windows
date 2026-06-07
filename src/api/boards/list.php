<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/db.php';

$payload = jwt_from_request();
if (!$payload) { http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit; }

$pdo  = db();
$stmt = $pdo->prepare('
    SELECT b.id, b.name, b.created_at, COUNT(bi.id) AS item_count
    FROM boards b
    LEFT JOIN board_items bi ON bi.board_id = b.id
    WHERE b.user_id = ?
    GROUP BY b.id
    ORDER BY b.created_at DESC
');
$stmt->execute([(int)$payload['sub']]);
echo json_encode(['boards' => $stmt->fetchAll()]);
