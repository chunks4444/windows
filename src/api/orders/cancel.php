<?php
header('Content-Type: application/json; charset=UTF-8');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/db.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit;
}
$uid = (int) $payload['sub'];

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$id   = (int) ($body['id'] ?? 0);
if (!$id) { http_response_code(422); echo json_encode(['error' => '대상 주문이 없습니다.']); exit; }

$pdo  = db();
$stmt = $pdo->prepare('SELECT status FROM orders WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $uid]);
$order = $stmt->fetch();
if (!$order) { http_response_code(404); echo json_encode(['error' => '주문을 찾을 수 없습니다.']); exit; }

// 관리자가 검토를 시작하기 전(pending_review) 또는 수정요청 상태에서만 고객이 직접 취소 가능.
// 그 이후(승인/입금/제작 등)엔 이미 처리가 진행 중이므로 담당자에게 문의하도록 한다.
if (!in_array($order['status'], ['pending_review', 'revision_requested'], true)) {
    http_response_code(409); echo json_encode(['error' => '이미 처리가 진행된 주문은 직접 취소할 수 없습니다. 담당자에게 문의해주세요.']); exit;
}

$pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ?")->execute([$id, $uid]);
echo json_encode(['ok' => true]);
