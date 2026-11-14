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

$in     = json_decode(file_get_contents('php://input'), true) ?: [];
$months = max(1, min(6, (int)($in['months'] ?? 6)));
// 관리자(s·m) 계정으로 로그인한 방문은 방문자 통계가 아니라서 기본으로 집계에서 뺀다 (화면의 '관리자 방문 제외' 체크). 기록은 지우지 않는다.
$exAdmin = !array_key_exists('exclude_admin', $in) || !empty($in['exclude_admin']);
$adm = $exAdmin ? " AND (user_id IS NULL OR user_id NOT IN (SELECT id FROM users WHERE role IN ('s','m')))" : '';

// 1. 일별 PV / UV
$stmt = $pdo->prepare("
    SELECT DATE(visited_at) AS date,
           COUNT(*)                  AS pv,
           COUNT(DISTINCT ip_hash)   AS uv
    FROM page_views
    WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? MONTH) $adm
    GROUP BY DATE(visited_at)
    ORDER BY date
");
$stmt->execute([$months]);
$daily = $stmt->fetchAll();

// 4. 요약
$stmt = $pdo->prepare("
    SELECT COUNT(*)                AS total_pv,
           COUNT(DISTINCT ip_hash) AS total_uv,
           COUNT(pv.user_id)       AS member_pv,
           SUM(pv.is_mobile)       AS mobile_pv
    FROM page_views pv
    WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? MONTH) $adm
");
$stmt->execute([$months]);
$summary = $stmt->fetch();

// 5. 현재 공유 중인 도면 수 (기간 필터와 무관한 현재 시점 스냅샷)
$sharedCount = (int) $pdo->query('SELECT COUNT(*) FROM drawings WHERE is_shared = 1')->fetchColumn();

// 6. 사람/봇 구분, 검색엔진·유입 경로 — page_views의 분류 칸(visitor_type 등)이 있을 때만.
// 분류는 칸을 추가한 뒤의 방문부터 붙으므로 그 이전 방문은 visitor_type이 NULL('분류 전')이다.
$classified = false;
$types = $bots = $sources = $humans = [];
$classSince = null;
try {
    $from = "FROM page_views WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? MONTH) $adm";

    // 일별: 종류별 PV와 사람 UV, JS 실행 확인 UV
    $stmt = $pdo->prepare("
        SELECT DATE(visited_at) AS date,
               SUM(visitor_type = 'human')                              AS human_pv,
               SUM(visitor_type IS NOT NULL AND visitor_type <> 'human') AS bot_pv,
               SUM(visitor_type IS NULL)                                AS unk_pv,
               COUNT(DISTINCT CASE WHEN visitor_type = 'human' THEN ip_hash END) AS human_uv,
               COUNT(DISTINCT CASE WHEN js_ok = 1 THEN ip_hash END)     AS js_uv
        $from GROUP BY DATE(visited_at) ORDER BY date
    ");
    $stmt->execute([$months]);
    $byDate = [];
    foreach ($stmt->fetchAll() as $r) $byDate[$r['date']] = $r;
    foreach ($daily as &$d) {
        $x = $byDate[$d['date']] ?? null;
        $d['human_pv'] = (int)($x['human_pv'] ?? 0);
        $d['bot_pv']   = (int)($x['bot_pv']   ?? 0);
        $d['unk_pv']   = (int)($x['unk_pv']   ?? 0);
        $d['human_uv'] = (int)($x['human_uv'] ?? 0);
        $d['js_uv']    = (int)($x['js_uv']    ?? 0);
    }
    unset($d);

    // 기간 전체: 종류별 PV/UV
    $stmt = $pdo->prepare("SELECT COALESCE(visitor_type, 'unknown') AS type, COUNT(*) AS pv, COUNT(DISTINCT ip_hash) AS uv, SUM(js_ok) AS js_pv,
                                  COUNT(DISTINCT CASE WHEN js_ok = 1 THEN ip_hash END) AS js_uv
                           $from GROUP BY COALESCE(visitor_type, 'unknown') ORDER BY pv DESC");
    $stmt->execute([$months]);
    $types = $stmt->fetchAll();

    // 봇 종류별 (검색엔진·AI·미리보기·기타 — 이름 단위)
    $stmt = $pdo->prepare("SELECT visitor_type AS type, COALESCE(bot_name, '-') AS name, COUNT(*) AS pv, COUNT(DISTINCT ip_hash) AS uv, MAX(visited_at) AS last_at
                           $from AND visitor_type IS NOT NULL AND visitor_type <> 'human'
                           GROUP BY visitor_type, bot_name ORDER BY pv DESC LIMIT 40");
    $stmt->execute([$months]);
    $bots = $stmt->fetchAll();

    // 유입 경로 (구글·네이버·다음·직접 …). 분류 후에는 사람으로 판정된 방문만, 분류 전 방문은 봇이 섞여 있어도
    // (로그에서 채운 Referer가 있으면) 같이 센다 — 그래서 사람 확실도는 "JS 확인 UV"로 따로 보여준다.
    $stmt = $pdo->prepare("SELECT ref_source AS source, COUNT(*) AS pv, COUNT(DISTINCT ip_hash) AS uv,
                                  COUNT(DISTINCT CASE WHEN js_ok = 1 THEN ip_hash END) AS js_uv,
                                  SUM(visitor_type IS NULL) AS prior_pv
                           $from AND ref_source IS NOT NULL AND (visitor_type = 'human' OR visitor_type IS NULL)
                           GROUP BY ref_source ORDER BY pv DESC");
    $stmt->execute([$months]);
    $sources = $stmt->fetchAll();

    // 사람 추정 방문자: 사람으로 분류되고 브라우저에서 스크립트 실행(js_ok)까지 확인된 IP. 최근 순 150명.
    // 유입 경로·본 페이지는 그 IP의 방문 중 시간순으로 첫 값 (GROUP_CONCAT 기본 길이 제한 안에서 첫 값은 항상 온전하다).
    $stmt = $pdo->prepare("SELECT ip, MAX(visited_at) AS last_at, COUNT(*) AS pv, MAX(is_mobile) AS mobile, MAX(user_id) AS user_id,
                                  SUBSTRING_INDEX(GROUP_CONCAT(ref_source ORDER BY visited_at SEPARATOR '\t'), '\t', 1) AS ref_source,
                                  SUBSTRING_INDEX(GROUP_CONCAT(page ORDER BY visited_at SEPARATOR '\t'), '\t', 1) AS first_page
                           $from AND visitor_type = 'human' AND ip IS NOT NULL
                           GROUP BY ip HAVING SUM(js_ok = 1) > 0
                           ORDER BY last_at DESC LIMIT 150");
    $stmt->execute([$months]);
    $humans = $stmt->fetchAll();

    $classSince = $pdo->query("SELECT MIN(visited_at) FROM page_views WHERE visitor_type IS NOT NULL")->fetchColumn() ?: null;
    $classified = $classSince !== null;
} catch (Throwable $e) {
    // 분류 칸이 아직 없는 DB — 기존 응답만 내보낸다
}

echo json_encode([
    'excludeAdmin' => $exAdmin,
    'daily'       => $daily,
    'summary'     => $summary,
    'sharedCount' => $sharedCount,
    'classified'  => $classified,
    'classSince'  => $classSince,
    'types'       => $types,
    'bots'        => $bots,
    'sources'     => $sources,
    'humans'      => $humans,
]);
