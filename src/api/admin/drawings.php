<?php
header('Content-Type: application/json; charset=UTF-8');
set_exception_handler(function(Throwable $e) {
    if (!headers_sent()) http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
});
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/drawing.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403);
    echo json_encode(['error' => '권한이 없습니다.']);
    exit;
}

$pdo = db();

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT id, type, title, thumbnail FROM drawings WHERE id=?');
    $stmt->execute([(int)$_GET['id']]);
    $row = $stmt->fetch();
    echo json_encode(['drawing' => $row ?: null]);
    exit;
}

// 필터/페이징 모드: ?page, ?category, ?type 파라미터 사용
if (isset($_GET['page']) || isset($_GET['category'])) {
    $limit    = 30;
    $page     = max(1, (int)($_GET['page']     ?? 1));
    $filter   = $_GET['category'] ?? '';   // '' = 전체, '0' = 미분류, 숫자 = 해당 카테고리 ID
    $typeF    = $_GET['type']     ?? '';
    $q        = trim($_GET['q']   ?? '');
    $offset   = ($page - 1) * $limit;

    $where  = [];
    $params = [];

    if ($filter === '0') {
        $where[] = '(d.pattern_category IS NULL OR d.pattern_category = "")';
    } elseif ($filter !== '') {
        $where[] = 'd.pattern_category = ?';
        $params[] = $filter;
    }
    if ($typeF !== '') {
        $where[] = 'd.type = ?';
        $params[] = $typeF;
    }
    if ($q !== '') {
        $where[] = '(d.title LIKE ? OR u.email LIKE ?)';
        $params[] = '%' . $q . '%';
        $params[] = '%' . $q . '%';
    }

    $whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $lockedAtExpr = Drawing::lockedAtExpr('d');
    $stmt = $pdo->prepare("
        SELECT d.id, d.type, d.title, d.pattern_category,
               pc.name AS pattern_category_name,
               u.email AS user_email,
               d.updated_at, {$lockedAtExpr},
               (SELECT COUNT(*) FROM drawing_versions WHERE drawing_id = d.id) AS version_count
        FROM drawings d
        LEFT JOIN pattern_categories pc ON pc.id = CAST(d.pattern_category AS UNSIGNED)
        LEFT JOIN users u ON u.id = d.user_id
        $whereClause
        ORDER BY d.updated_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([...$params, $limit + 1, $offset]);
    $drawings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $has_more = count($drawings) > $limit;
    if ($has_more) array_pop($drawings);
    echo json_encode(['drawings' => $drawings, 'has_more' => $has_more]);
    exit;
}

$rows = $pdo->query(
    'SELECT id, type, title, thumbnail FROM drawings ORDER BY type, title'
)->fetchAll();

echo json_encode(['drawings' => $rows]);
