<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/drawing.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit;
}

$limit    = 20;
$page     = max(1, (int)($_GET['page'] ?? 1));
$q        = trim($_GET['q'] ?? '');
$drawings = Drawing::list_all((int)$payload['sub'], $page, $limit + 1, $q);
$has_more = count($drawings) > $limit;
if ($has_more) array_pop($drawings);

$out = ['drawings' => $drawings, 'has_more' => $has_more];
if ($page === 1) {
    if ($q !== '') {
        $stmt = db()->prepare('SELECT COUNT(*) FROM drawings WHERE user_id = ? AND title LIKE ?');
        $stmt->execute([(int)$payload['sub'], '%' . $q . '%']);
    } else {
        $stmt = db()->prepare('SELECT COUNT(*) FROM drawings WHERE user_id = ?');
        $stmt->execute([(int)$payload['sub']]);
    }
    $out['total'] = (int)$stmt->fetchColumn();
}

echo json_encode($out);
