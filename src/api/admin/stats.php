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

$months = max(1, min(6, (int)((json_decode(file_get_contents('php://input'),true)['months'] ?? 6))));

// 1. 일별 PV / UV
$stmt = $pdo->prepare("
    SELECT DATE(visited_at) AS date,
           COUNT(*)                  AS pv,
           COUNT(DISTINCT ip_hash)   AS uv
    FROM page_views
    WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
    GROUP BY DATE(visited_at)
    ORDER BY date
");
$stmt->execute([$months]);
$daily = $stmt->fetchAll();

// 2. 인기 페이지 TOP 10
$stmt = $pdo->prepare("
    SELECT page,
           COUNT(*)                AS pv,
           COUNT(DISTINCT ip_hash) AS uv
    FROM page_views
    WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
    GROUP BY page
    ORDER BY pv DESC
    LIMIT 10
");
$stmt->execute([$months]);
$topPages = $stmt->fetchAll();

// 3. 회원별 접속 기록 (날짜별) — 같은 회원이 여러 날 접속했으면 날짜마다 별도 행으로 표시
$stmt = $pdo->prepare("
    SELECT u.id, u.email, u.role,
           DATE(pv.visited_at)                           AS visit_date,
           COUNT(pv.id)                                  AS visit_count,
           SUM(pv.is_mobile)                             AS mobile_count,
           MAX(pv.visited_at)                            AS last_visit,
           SUBSTRING_INDEX(GROUP_CONCAT(pv.ip ORDER BY pv.visited_at DESC SEPARATOR ','), ',', 1) AS last_ip
    FROM page_views pv
    JOIN users u ON pv.user_id = u.id
    WHERE pv.visited_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
    GROUP BY u.id, DATE(pv.visited_at)
    ORDER BY visit_date DESC, last_visit DESC
    LIMIT 20
");
$stmt->execute([$months]);
$topUsers = $stmt->fetchAll();

// 4. 요약
$stmt = $pdo->prepare("
    SELECT COUNT(*)                AS total_pv,
           COUNT(DISTINCT ip_hash) AS total_uv,
           COUNT(pv.user_id)       AS member_pv,
           SUM(pv.is_mobile)       AS mobile_pv
    FROM page_views pv
    WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
");
$stmt->execute([$months]);
$summary = $stmt->fetch();

// 5. 현재 공유 중인 도면 수 (기간 필터와 무관한 현재 시점 스냅샷)
$sharedCount = (int) $pdo->query('SELECT COUNT(*) FROM drawings WHERE is_shared = 1')->fetchColumn();

echo json_encode([
    'daily'       => $daily,
    'topPages'    => $topPages,
    'topUsers'    => $topUsers,
    'summary'     => $summary,
    'sharedCount' => $sharedCount,
]);
