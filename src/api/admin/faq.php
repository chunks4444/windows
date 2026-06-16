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

$method = $_SERVER['REQUEST_METHOD'];
$pdo    = db();

if ($method === 'GET') {
    $rows = $pdo->query('SELECT * FROM faqs ORDER BY sort_order, id')->fetchAll();
    echo json_encode(['faqs' => $rows]);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? '';

if ($action === 'save') {
    $id       = (int)($body['id'] ?? 0);
    $question = trim($body['question'] ?? '');
    $answer   = trim($body['answer'] ?? '');

    if ($question === '' || $answer === '') {
        echo json_encode(['error' => '질문과 답변을 모두 입력하세요.']);
        exit;
    }

    if ($id) {
        $pdo->prepare('UPDATE faqs SET question=?, answer=? WHERE id=?')
            ->execute([$question, $answer, $id]);
    } else {
        $maxOrder = (int)$pdo->query('SELECT COALESCE(MAX(sort_order),0) FROM faqs')->fetchColumn();
        $pdo->prepare('INSERT INTO faqs (question, answer, sort_order) VALUES (?,?,?)')
            ->execute([$question, $answer, $maxOrder + 1]);
        $id = (int)$pdo->lastInsertId();
    }

    $stmt = $pdo->prepare('SELECT * FROM faqs WHERE id=?');
    $stmt->execute([$id]);
    echo json_encode(['ok' => true, 'faq' => $stmt->fetch()]);
    exit;
}

if ($action === 'delete') {
    $pdo->prepare('DELETE FROM faqs WHERE id=?')->execute([(int)($body['id'] ?? 0)]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'toggle') {
    $id = (int)($body['id'] ?? 0);
    $pdo->prepare('UPDATE faqs SET is_active = 1 - is_active WHERE id=?')->execute([$id]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'reorder') {
    $ids  = $body['ids'] ?? [];
    $stmt = $pdo->prepare('UPDATE faqs SET sort_order=? WHERE id=?');
    foreach ($ids as $i => $id) {
        $stmt->execute([$i, (int)$id]);
    }
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action']);
