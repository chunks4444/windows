<?php
header('Content-Type: application/json; charset=UTF-8');
set_exception_handler(function(Throwable $e) {
    if (!headers_sent()) http_response_code(500);
    echo json_encode(['error' => '서버 오류가 발생했습니다.']);
    exit;
});
require_once __DIR__ . '/../lib/db.php';

$_input   = json_decode(file_get_contents('php://input'), true) ?? [];
$q        = trim($_input['q'] ?? $_GET['q'] ?? '');
$page     = max(1, (int)($_input['page'] ?? 1));
$category = trim($_input['category'] ?? '');   // 패턴 카테고리 ID ('') = 전체
$limit    = 20;
$offset   = ($page - 1) * $limit;
$pdo      = db();

$editorMap = [
    'classic'  => '/src/engine/classic/classic.php',
    'square'   => '/src/engine/square/square.php',
    'diamond'  => '/src/engine/diamond/diamond.php',
    'cross'    => '/src/engine/cross/cross.php',
    'triangle' => '/src/engine/triangle/triangle.php',
    'hexagon'  => '/src/engine/hexagon/hexagon.php',
];

// WHERE 조건
$baseWhere = 'p.is_active = 1';
$params    = [];
if ($q !== '') {
    $like = '%' . $q . '%';
    $baseWhere .= ' AND (p.name_ko LIKE :q OR p.id IN (SELECT pattern_id FROM library_keywords WHERE keyword LIKE :q2))';
    $params = [':q' => $like, ':q2' => $like];
}
if ($category !== '') {
    $baseWhere .= ' AND d.pattern_category = :category';
    $params[':category'] = $category;
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
echo json_encode($out);
