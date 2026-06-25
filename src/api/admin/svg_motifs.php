<?php
header('Content-Type: application/json; charset=UTF-8');
set_exception_handler(function(Throwable $e) {
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
$pdo->exec("
CREATE TABLE IF NOT EXISTS svg_motifs (
    id         INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    name       VARCHAR(100)      NOT NULL DEFAULT '',
    svg_url    VARCHAR(500)      NOT NULL DEFAULT '',
    category   VARCHAR(50)       NOT NULL DEFAULT '',
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active  TINYINT(1)        NOT NULL DEFAULT 1,
    created_at DATETIME          NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    KEY idx_svg_motifs_sort (sort_order, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $rows = $pdo->query('SELECT * FROM svg_motifs ORDER BY sort_order, id')->fetchAll();
    echo json_encode(['motifs' => $rows]);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? '';

// SVG 텍스트를 검증해 파일로 저장하고 공개 URL을 반환
function saveSvgMotifFile(string $svgInput): ?string {
    $svg = $svgInput;
    if (preg_match('/^data:[^,]*;base64,/i', $svg, $m)) {
        $svg = base64_decode(substr($svg, strlen($m[0])), true);
        if ($svg === false) return null;
    }
    $svg = trim($svg);
    if ($svg === '' || strlen($svg) > 2 * 1024 * 1024) return null;
    if (stripos($svg, '<svg') === false) return null;
    // 기본적인 악성 스크립트 방지
    if (preg_match('/<script[\s>]|[\s"\']on[a-z]+\s*=\s*["\']|javascript:/i', $svg)) return null;

    $dir = __DIR__ . '/../../../uploads/svg_motifs';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $fname = time() . '_' . bin2hex(random_bytes(4)) . '.svg';
    file_put_contents($dir . '/' . $fname, $svg);
    return '/uploads/svg_motifs/' . $fname;
}

if ($action === 'save') {
    $id       = (int)($body['id'] ?? 0);
    $name     = trim($body['name'] ?? '');
    $category = trim($body['category'] ?? '');
    $svg_url  = trim($body['svg_url'] ?? '');

    if (!empty($body['svg_data'])) {
        $saved = saveSvgMotifFile($body['svg_data']);
        if ($saved) $svg_url = $saved;
        else { echo json_encode(['error' => 'SVG 저장 실패 (형식을 확인해주세요)']); exit; }
    }
    if (!$svg_url) { echo json_encode(['error' => 'SVG 파일이 필요합니다.']); exit; }

    if ($id) {
        $pdo->prepare('UPDATE svg_motifs SET name=?, svg_url=?, category=? WHERE id=?')
            ->execute([$name, $svg_url, $category, $id]);
    } else {
        $maxOrder = (int)$pdo->query('SELECT COALESCE(MAX(sort_order),0) FROM svg_motifs')->fetchColumn();
        $pdo->prepare('INSERT INTO svg_motifs (name, svg_url, category, sort_order) VALUES (?,?,?,?)')
            ->execute([$name, $svg_url, $category, $maxOrder + 1]);
        $id = (int)$pdo->lastInsertId();
    }
    $stmt = $pdo->prepare('SELECT * FROM svg_motifs WHERE id=?');
    $stmt->execute([$id]);
    echo json_encode(['ok' => true, 'motif' => $stmt->fetch()]);
    exit;
}

if ($action === 'delete') {
    $pdo->prepare('DELETE FROM svg_motifs WHERE id=?')->execute([(int)($body['id'] ?? 0)]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'toggle') {
    $pdo->prepare('UPDATE svg_motifs SET is_active = 1 - is_active WHERE id=?')->execute([(int)($body['id'] ?? 0)]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'reorder') {
    $stmt = $pdo->prepare('UPDATE svg_motifs SET sort_order=? WHERE id=?');
    foreach (($body['ids'] ?? []) as $i => $id) $stmt->execute([$i, (int)$id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action']);
