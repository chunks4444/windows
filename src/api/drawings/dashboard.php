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
// type: 정자살·클래식 같은 엔진 하나로 좁힐 때만 보낸다(툴 레일 '내 도면'의 엔진 필터). 없으면 전체.
$type     = preg_match('/^[a-z]{1,20}$/', (string)($_GET['type'] ?? '')) ? (string)$_GET['type'] : '';
$drawings = Drawing::list_all((int)$payload['sub'], $page, $limit + 1, $q, $type);
$has_more = count($drawings) > $limit;
if ($has_more) array_pop($drawings);

$out = ['drawings' => $drawings, 'has_more' => $has_more];
if ($page === 1) {
    $sql    = 'SELECT COUNT(*) FROM drawings WHERE user_id = ?';
    $params = [(int)$payload['sub']];
    if ($q !== '')    { $sql .= ' AND title LIKE ?'; $params[] = '%' . $q . '%'; }
    if ($type !== '') { $sql .= ' AND type = ?';     $params[] = $type; }
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $out['total'] = (int)$stmt->fetchColumn();
}

echo json_encode($out);
