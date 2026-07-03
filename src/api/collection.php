<?php
header('Content-Type: application/json; charset=UTF-8');
set_exception_handler(function(Throwable $e) {
    if (!headers_sent()) http_response_code(500);
    echo json_encode(['error' => '서버 오류가 발생했습니다.']);
    exit;
});
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/jwt.php';

$_input   = json_decode(file_get_contents('php://input'), true) ?? [];
$q        = trim($_input['q'] ?? $_GET['q'] ?? '');
$page     = max(1, (int)($_input['page'] ?? 1));
$category = trim($_input['category'] ?? '');   // 패턴 카테고리 ID ('') = 전체
$liked    = !empty($_input['liked']);          // 좋아요 필터 (로그인 필요)
$limit    = 20;
$offset   = ($page - 1) * $limit;
$pdo      = db();

$payload     = jwt_from_request();
$uid         = $payload ? (int)$payload['sub'] : 0;
$likedNoAuth = $liked && !$uid;   // 비로그인 상태로 좋아요 필터 요청 — 전체 목록이 아니라 빈 결과를 줘야 함
if ($likedNoAuth) $liked = false;

$editorMap = [
    'classic'  => '/src/engine/classic/classic.php',
    'square'   => '/src/engine/square/square.php',
    'diamond'  => '/src/engine/diamond/diamond.php',
    'cross'    => '/src/engine/cross/cross.php',
    'triangle' => '/src/engine/triangle/triangle.php',
    'hexagon'  => '/src/engine/hexagon/hexagon.php',
];

// WHERE 조건 — 검색어/모양/좋아요는 서로 결합하지 않고 각각 개별 검색으로 동작.
// (프런트가 필터 하나를 켜면 나머지를 비우지만, 혹시 여러 개가 함께 오더라도
//  모양 > 검색어(공간 포함) > 좋아요 우선순위로 하나만 적용한다.)
$baseWhere = 'p.is_active = 1';
$params    = [];

if ($category !== '') {
    $baseWhere .= ' AND d.pattern_category = :category';
    $params[':category'] = $category;
} elseif ($q !== '') {
    $like = '%' . $q . '%';
    $baseWhere .= ' AND (p.name_ko LIKE :q OR p.id IN (SELECT pattern_id FROM library_keywords WHERE keyword LIKE :q2))';
    $params[':q']  = $like;
    $params[':q2'] = $like;
} elseif ($liked) {
    $baseWhere .= ' AND p.id IN (SELECT pattern_id FROM library_likes WHERE user_id = :uid)';
    $params[':uid'] = $uid;
} elseif ($likedNoAuth) {
    $baseWhere .= ' AND 0';   // 로그인 안 한 사용자의 좋아요 필터 — 전체 노출 대신 결과 없음
}

// 필터 버튼용 전체 키워드 (page 1에서만)
$keywords = [];
if ($page === 1) {
    $kstmt    = $pdo->query('SELECT DISTINCT k.keyword FROM library_keywords k JOIN library_patterns p ON p.id = k.pattern_id WHERE p.is_active = 1 ORDER BY k.keyword');
    $keywords = $kstmt->fetchAll(PDO::FETCH_COLUMN);
}

// 페이징된 패턴
$stmt = $pdo->prepare(
    "SELECT p.id, p.name_ko, p.drawing_id, p.image_path, d.type AS engine,
            GROUP_CONCAT(k.keyword ORDER BY k.id SEPARATOR ',') AS keywords
     FROM library_patterns p
     LEFT JOIN drawings d ON d.id = p.drawing_id
     LEFT JOIN library_keywords k ON k.pattern_id = p.id
     WHERE $baseWhere
     GROUP BY p.id
     ORDER BY p.sort_order, p.id
     LIMIT :lim OFFSET :off"
);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':lim',  $limit + 1, PDO::PARAM_INT);
$stmt->bindValue(':off',  $offset,    PDO::PARAM_INT);
$stmt->execute();

$rows     = $stmt->fetchAll();
$has_more = count($rows) > $limit;
if ($has_more) array_pop($rows);

foreach ($rows as &$r) {
    $r['keywords']   = $r['keywords'] ? explode(',', $r['keywords']) : [];
    $engineKey       = strtolower($r['engine'] ?? '');
    $r['editor_url'] = $editorMap[$engineKey] ?? null;
}

$out = ['patterns' => $rows, 'has_more' => $has_more];
if ($page === 1) $out['keywords'] = $keywords;

// 총 개수 (첫 페이지에서만 계산 — 필터/검색어 바뀔 때마다 갱신됨)
if ($page === 1) {
    $cstmt = $pdo->prepare(
        "SELECT COUNT(DISTINCT p.id) FROM library_patterns p
         LEFT JOIN drawings d ON d.id = p.drawing_id
         LEFT JOIN library_keywords k ON k.pattern_id = p.id
         WHERE $baseWhere"
    );
    foreach ($params as $k => $v) $cstmt->bindValue($k, $v);
    $cstmt->execute();
    $out['total'] = (int)$cstmt->fetchColumn();
}

echo json_encode($out);
