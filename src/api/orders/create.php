<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/mailer.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit;
}
$userId = (int) $payload['sub'];

$validEngines = ['square', 'classic', 'cross', 'diamond', 'triangle', 'hexagon'];

$body      = json_decode(file_get_contents('php://input'), true) ?? [];
$engine    = $body['engine'] ?? '';
$title     = trim($body['title'] ?? '');
$memo      = trim($body['memo'] ?? '');
$drawingId = isset($body['drawing_id']) ? (int) $body['drawing_id'] : null;
$spec      = $body['spec'] ?? null;

if (!in_array($engine, $validEngines, true)) {
    http_response_code(422); echo json_encode(['error' => '엔진 종류가 올바르지 않습니다.']); exit;
}
if (mb_strlen($title) > 100 || mb_strlen($memo) > 1000) {
    http_response_code(422); echo json_encode(['error' => '입력값이 너무 깁니다.']); exit;
}

$pdo = db();

// 주문자 정보는 클라이언트가 아니라 서버에서 직접 조회 (스냅샷 무결성)
$stmt = $pdo->prepare('SELECT name, phone, email, company_name FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user) {
    http_response_code(404); echo json_encode(['error' => '사용자를 찾을 수 없습니다.']); exit;
}
if (!$user['name'] || !$user['phone']) {
    http_response_code(422); echo json_encode(['error' => '프로필에 이름과 연락처를 먼저 입력해주세요.']); exit;
}

// drawing_id가 있으면 실제로 이 유저 소유인지 확인
if ($drawingId) {
    $chk = $pdo->prepare('SELECT id FROM drawings WHERE id = ? AND user_id = ?');
    $chk->execute([$drawingId, $userId]);
    if (!$chk->fetch()) $drawingId = null;
}

$stmt = $pdo->prepare(
    'INSERT INTO orders (user_id, drawing_id, engine, title, customer_name, customer_phone, company_name, memo, spec_json, status, created_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, \'pending\', NOW())'
);
$stmt->execute([
    $userId, $drawingId, $engine, $title,
    $user['name'], $user['phone'], $user['company_name'] ?: null,
    $memo ?: null,
    $spec !== null ? json_encode($spec, JSON_UNESCAPED_UNICODE) : null,
]);
$orderId = (int) $pdo->lastInsertId();

send_mail(
    'pyeongmok@gmail.com',
    '[주문] ' . $user['name'] . ' - ' . ($title ?: $engine),
    'order',
    [
        'orderId'  => $orderId,
        'engine'   => $engine,
        'title'    => $title,
        'name'     => $user['name'],
        'phone'    => $user['phone'],
        'email'    => $user['email'],
        'company'  => $user['company_name'],
        'memo'     => $memo,
    ]
);

echo json_encode(['ok' => true, 'order_id' => $orderId]);
