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

// GET: 내 좋아요 패턴 ID 목록
if ($method === 'GET') {
    $rows = $pdo->prepare('SELECT pattern_id FROM library_likes WHERE user_id = ?');
    $rows->execute([$uid]);
    echo json_encode(['likes' => $rows->fetchAll(PDO::FETCH_COLUMN)]);
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
