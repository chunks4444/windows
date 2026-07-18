<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit;
}

$pdo  = db();
$stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
$stmt->execute([$payload['sub']]);
$me   = $stmt->fetch();
if (!$me || $me['role'] !== 's') {
    http_response_code(403); echo json_encode(['error' => '슈퍼 권한이 필요합니다.']); exit;
}

$months     = max(1, min(12, (int)($_GET['months'] ?? 6)));
$page       = max(1, (int)($_GET['page'] ?? 1));
$ipQuery    = trim($_GET['ip'] ?? '');
$blockedOnly = !empty($_GET['blocked_only']);
$limit      = 20;
$offset     = ($page - 1) * $limit;

$ipCond   = $ipQuery !== '' ? 'AND pv.ip LIKE ?' : '';
$ipParams = $ipQuery !== '' ? ['%' . $ipQuery . '%'] : [];
// 기본값은 이미 차단된 IP를 목록에서 제외(처리 끝난 항목이라 굳이 계속 노출할 필요 없음),
// "차단된 IP만" 체크 시에는 반대로 그것만 보여준다.
$blockedCond = $blockedOnly
    ? 'AND pv.ip IN (SELECT ip FROM blocked_ips)'
    : 'AND pv.ip NOT IN (SELECT ip FROM blocked_ips)';

// 전체 개수 (IP+날짜 그룹 기준)
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM (
        SELECT 1
        FROM page_views pv
        WHERE pv.user_id IS NULL AND pv.visited_at >= DATE_SUB(NOW(), INTERVAL ? MONTH) $ipCond $blockedCond
        GROUP BY pv.ip, DATE(pv.visited_at)
    ) t
");
$stmt->execute(array_merge([$months], $ipParams));
$total = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT pv.ip,
           DATE(pv.visited_at)  AS visit_date,
           COUNT(*)             AS visit_count,
           SUM(pv.is_mobile)    AS mobile_count,
           MAX(pv.visited_at)   AS last_visit,
           MAX(bi.ip IS NOT NULL) AS blocked
    FROM page_views pv
    LEFT JOIN blocked_ips bi ON bi.ip = pv.ip
    WHERE pv.user_id IS NULL AND pv.visited_at >= DATE_SUB(NOW(), INTERVAL ? MONTH) $ipCond $blockedCond
    GROUP BY pv.ip, DATE(pv.visited_at)
    ORDER BY visit_date DESC, last_visit DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute(array_merge([$months], $ipParams));
$visits = $stmt->fetchAll();

echo json_encode([
    'visits'      => $visits,
    'total'       => $total,
    'page'        => $page,
    'pages_count' => (int) ceil($total / $limit),
]);
