<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/drawing.php';
require_once __DIR__ . '/../../lib/mailer.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit;
}
$userId = (int) $payload['sub'];

$validEngines = ['square', 'classic', 'cross', 'diamond', 'triangle', 'hexagon'];

$body         = json_decode(file_get_contents('php://input'), true) ?? [];
$engine       = $body['engine'] ?? '';
$title        = trim($body['title'] ?? '');
$versionLabel = trim($body['version_label'] ?? '');
$thumbnail    = $body['thumbnail'] ?? null;
$memo         = trim($body['memo'] ?? '');
$drawingId    = isset($body['drawing_id']) ? (int) $body['drawing_id'] : null;
$spec         = $body['spec'] ?? null;
$dueDate      = trim($body['due_date'] ?? '');
$shipZip      = trim($body['ship_zipcode'] ?? '');
$shipAddr     = trim($body['ship_address'] ?? '');
$shipAddr2    = trim($body['ship_address_detail'] ?? '');
$shipPhone    = trim($body['ship_phone'] ?? '');

if (!in_array($engine, $validEngines, true)) {
    http_response_code(422); echo json_encode(['error' => '엔진 종류가 올바르지 않습니다.']); exit;
}
if (mb_strlen($title) > 100 || mb_strlen($memo) > 1000 || mb_strlen($versionLabel) > 50) {
    http_response_code(422); echo json_encode(['error' => '입력값이 너무 깁니다.']); exit;
}
if (!$dueDate || !DateTime::createFromFormat('Y-m-d', $dueDate)) {
    http_response_code(422); echo json_encode(['error' => '납기 희망일을 올바르게 입력해주세요.']); exit;
}
if (!$shipAddr || !$shipPhone) {
    http_response_code(422); echo json_encode(['error' => '배송지 주소와 연락처를 입력해주세요.']); exit;
}
if (mb_strlen($shipZip) > 10 || mb_strlen($shipAddr) > 255 || mb_strlen($shipAddr2) > 100 || mb_strlen($shipPhone) > 30) {
    http_response_code(422); echo json_encode(['error' => '배송지 입력값이 너무 깁니다.']); exit;
}
if ($thumbnail !== null && (!is_string($thumbnail) || strpos($thumbnail, 'data:image') !== 0)) {
    $thumbnail = null;
}

$pdo = db();

// 요청자 정보는 클라이언트가 아니라 서버에서 직접 조회 (스냅샷 무결성)
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
    'INSERT INTO orders (
        user_id, drawing_id, engine, title, version_label, thumbnail,
        customer_name, customer_phone, company_name,
        due_date, ship_zipcode, ship_address, ship_address_detail, ship_phone,
        memo, spec_json, status, created_at
     ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \'pending\', NOW())'
);
$stmt->execute([
    $userId, $drawingId, $engine, $title, $versionLabel ?: null, $thumbnail,
    $user['name'], $user['phone'], $user['company_name'] ?: null,
    $dueDate, $shipZip ?: null, $shipAddr, $shipAddr2 ?: null, $shipPhone,
    $memo ?: null,
    $spec !== null ? json_encode($spec, JSON_UNESCAPED_UNICODE) : null,
]);
$orderId = (int) $pdo->lastInsertId();

// 견적요청이 접수된 도면은 편집/삭제 불가하도록 잠금
if ($drawingId) {
    Drawing::lock($drawingId, $userId);
}

// 관리자(슈퍼/관리자 권한) 계정의 이메일로 발송 — 고정 주소 대신 실제 등록된 관리자에게 전달
$adminEmails = $pdo->query("SELECT email FROM users WHERE role IN ('s','m') AND withdrawn_at IS NULL")->fetchAll(PDO::FETCH_COLUMN);

foreach ($adminEmails as $adminEmail) {
    send_mail(
        $adminEmail,
        '[견적요청] ' . $user['name'] . ' - ' . ($title ?: $engine),
        'order',
        [
            'orderId'   => $orderId,
            'orderDate' => date('Y-m-d'),
            'engine'    => $engine,
            'title'     => $title,
            'version'   => $versionLabel,
            'thumbnail' => $thumbnail,
            'name'      => $user['name'],
            'phone'     => $user['phone'],
            'email'     => $user['email'],
            'company'   => $user['company_name'],
            'dueDate'   => $dueDate,
            'shipZip'   => $shipZip,
            'shipAddr'  => trim($shipAddr . ' ' . $shipAddr2),
            'shipPhone' => $shipPhone,
            'memo'      => $memo,
        ]
    );
}

echo json_encode(['ok' => true, 'order_id' => $orderId]);
