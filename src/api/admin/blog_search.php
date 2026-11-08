<?php
// 어드민 '블로그 검색 관리' API (src/admin/blog_search.php) — 슈퍼 권한 전용
//   GET                       → 이표기 표 + 결과 0건 검색어(횟수순) + 최근 30일 검색 수
//   POST action=add_alias     → {alias, canonical} 추가
//   POST action=delete_alias  → {id} 삭제
header('Content-Type: application/json; charset=UTF-8');
set_exception_handler(function (Throwable $e) {
    if (!headers_sent()) http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
});
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403);
    echo json_encode(['error' => '권한이 없습니다.']);
    exit;
}
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $aliases = $pdo->query('SELECT id, alias, canonical, created_at FROM term_aliases ORDER BY canonical, alias')->fetchAll();
    $zero = $pdo->query('
        SELECT query, COUNT(*) AS cnt, MAX(created_at) AS last_at
        FROM search_logs
        WHERE result_count = 0
        GROUP BY query
        ORDER BY cnt DESC, last_at DESC
        LIMIT 200
    ')->fetchAll();
    $recent = (int)$pdo->query('SELECT COUNT(*) FROM search_logs WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();
    echo json_encode(['aliases' => $aliases, 'zero' => $zero, 'recent_count' => $recent]);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? '';

if ($action === 'add_alias') {
    $alias     = trim((string)($body['alias'] ?? ''));
    $canonical = trim((string)($body['canonical'] ?? ''));
    if ($alias === '' || $canonical === '') { echo json_encode(['error' => '두 칸 모두 입력해 주세요.']); exit; }
    if (mb_strlen($alias) > 50 || mb_strlen($canonical) > 50) { echo json_encode(['error' => '50자 이내로 입력해 주세요.']); exit; }
    if ($alias === $canonical) { echo json_encode(['error' => '다른 표기와 대표 표기가 같습니다.']); exit; }

    // 대표 표기로 적은 말이 이미 다른 묶음의 '다른 표기'라면 그 묶음의 대표 표기로 붙인다(묶음이 갈라지지 않게)
    $st = $pdo->prepare('SELECT canonical FROM term_aliases WHERE alias = ?');
    $st->execute([$canonical]);
    if ($c = $st->fetchColumn()) $canonical = $c;
    if ($alias === $canonical) { echo json_encode(['error' => '이미 같은 묶음에 들어 있습니다.']); exit; }

    // 다른 표기로 적은 말이 이미 어떤 묶음의 대표 표기라면 거절 — 묶음 둘을 합치는 건 수동으로
    $st = $pdo->prepare('SELECT COUNT(*) FROM term_aliases WHERE canonical = ?');
    $st->execute([$alias]);
    if ((int)$st->fetchColumn() > 0) {
        echo json_encode(['error' => "'{$alias}'은(는) 이미 다른 묶음의 대표 표기입니다. 그 묶음을 먼저 정리해 주세요."]);
        exit;
    }
    $st = $pdo->prepare('SELECT canonical FROM term_aliases WHERE alias = ?');
    $st->execute([$alias]);
    if ($c = $st->fetchColumn()) { echo json_encode(['error' => "'{$alias}'은(는) 이미 '{$c}' 묶음에 있습니다."]); exit; }

    $pdo->prepare('INSERT INTO term_aliases (alias, canonical) VALUES (?, ?)')->execute([$alias, $canonical]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'delete_alias') {
    $pdo->prepare('DELETE FROM term_aliases WHERE id = ?')->execute([(int)($body['id'] ?? 0)]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action']);
