<?php
header('Content-Type: application/json; charset=UTF-8');
set_exception_handler(function(Throwable $e) {
    if (!headers_sent()) http_response_code(500);
    echo json_encode(['error' => '서버 오류가 발생했습니다.']);
    exit;
});
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401);
    echo json_encode(['error' => '로그인이 필요합니다.']);
    exit;
}

$uid    = (int)$payload['sub'];
$method = $_SERVER['REQUEST_METHOD'];
$pdo    = db();

// GET: 내 좋아요 패턴 ID 목록 (?full=1 이면 카드 렌더용 전체 정보 포함, 필터 무관 전체)
if ($method === 'GET') {
    $rows = $pdo->prepare('SELECT pattern_id FROM library_likes WHERE user_id = ?');
    $rows->execute([$uid]);
    $ids = $rows->fetchAll(PDO::FETCH_COLUMN);

    $out = ['likes' => $ids];

    if (!empty($_GET['full']) && $ids) {
        $editorMap = [
            'classic'  => '/src/engine/classic/classic.php',
            'square'   => '/src/engine/square/square.php',
            'diamond'  => '/src/engine/diamond/diamond.php',
            'cross'    => '/src/engine/cross/cross.php',
            'triangle' => '/src/engine/triangle/triangle.php',
            'hexagon'  => '/src/engine/hexagon/hexagon.php',
        ];

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare(
            "SELECT p.id, p.name_ko, p.drawing_id, p.image_path, d.type AS engine,
                    GROUP_CONCAT(k.keyword ORDER BY k.id SEPARATOR ',') AS keywords
             FROM library_patterns p
             LEFT JOIN drawings d ON d.id = p.drawing_id
             LEFT JOIN library_keywords k ON k.pattern_id = p.id
             WHERE p.id IN ($placeholders) AND p.is_active = 1
             GROUP BY p.id
             ORDER BY p.sort_order, p.id"
        );
        $stmt->execute(array_values($ids));
        $patterns = $stmt->fetchAll();
        foreach ($patterns as &$r) {
            $r['keywords']   = $r['keywords'] ? explode(',', $r['keywords']) : [];
            $engineKey       = strtolower($r['engine'] ?? '');
            $r['editor_url'] = $editorMap[$engineKey] ?? null;
        }
        $out['patterns'] = $patterns;
    } elseif (!empty($_GET['full'])) {
        $out['patterns'] = [];
    }

    echo json_encode($out);
    exit;
}

// POST: 토글 (있으면 삭제, 없으면 추가)
if ($method === 'POST') {
    $body      = json_decode(file_get_contents('php://input'), true) ?? [];
    $patternId = (int)($body['pattern_id'] ?? 0);
    if (!$patternId) {
        http_response_code(400);
        echo json_encode(['error' => 'pattern_id 필요']);
        exit;
    }

    $check = $pdo->prepare('SELECT id FROM library_likes WHERE user_id = ? AND pattern_id = ?');
    $check->execute([$uid, $patternId]);

    if ($check->fetch()) {
        $pdo->prepare('DELETE FROM library_likes WHERE user_id = ? AND pattern_id = ?')
            ->execute([$uid, $patternId]);
        echo json_encode(['liked' => false]);
    } else {
        $pdo->prepare('INSERT INTO library_likes (user_id, pattern_id) VALUES (?,?)')
            ->execute([$uid, $patternId]);
        echo json_encode(['liked' => true]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
