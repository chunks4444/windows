<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/slug.php';

$payload = jwt_from_request();
if (!$payload) { http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit; }

$_body = json_decode(file_get_contents('php://input'),true) ?? [];
$boardId = (int)($_body['board_id'] ?? 0);
if (!$boardId) { http_response_code(422); echo json_encode(['error' => 'board_id가 필요합니다.']); exit; }

$pdo  = db();
// 보드 소유 확인
$stmt = $pdo->prepare('SELECT id, name FROM boards WHERE id = ? AND user_id = ?');
$stmt->execute([$boardId, (int)$payload['sub']]);
$board = $stmt->fetch();
if (!$board) { http_response_code(403); echo json_encode(['error' => '권한이 없습니다.']); exit; }

$stmt = $pdo->prepare('
    SELECT p.id, p.slug, p.name_ko, p.image_path, p.drawing_id, d.type AS engine
    FROM board_items bi
    JOIN library_patterns p ON p.id = bi.pattern_id
    LEFT JOIN drawings d ON d.id = p.drawing_id
    WHERE bi.board_id = ?
    ORDER BY bi.created_at DESC
');
$stmt->execute([$boardId]);
$items = $stmt->fetchAll();
foreach ($items as &$it) {
    $it['display_name'] = library_pattern_display_name($it['slug'], $it['name_ko']);
}
echo json_encode(['board' => $board, 'items' => $items]);
