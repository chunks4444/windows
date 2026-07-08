<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403); echo json_encode(['error' => '슈퍼 권한이 필요합니다.']); exit;
}
$adminId = (int) $payload['sub'];

$pdo = db();

const ORDER_STATUSES = [
    'pending_review', 'revision_requested', 'approved', 'quote_finalized',
    'deposit_paid', 'in_production', 'production_done', 'shipped', 'delivered', 'cancelled',
];

$_body = json_decode(file_get_contents('php://input'), true) ?? [];

// GET 또는 POST(action 없음): 목록 또는 단건 상세
if ($_SERVER['REQUEST_METHOD'] === 'GET' || ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_body['action']))) {
    // 단건 상세 (?id= 또는 body.id)
    $id = (int) ($_body['id'] ?? $_GET['id'] ?? 0);
    if ($id) {
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        if (!$order) { http_response_code(404); echo json_encode(['error' => '주문을 찾을 수 없습니다.']); exit; }
        echo json_encode(['order' => $order]);
        exit;
    }

    $page   = max(1, (int) ($_body['page'] ?? $_GET['page'] ?? 1));
    $limit  = 20;
    $offset = ($page - 1) * $limit;
    $q      = trim($_body['q'] ?? $_GET['q'] ?? '');
    $status = trim($_body['status'] ?? $_GET['status'] ?? '');

    $where  = [];
    $params = [];
    if ($q !== '') {
        $like     = '%' . $q . '%';
        $where[]  = '(customer_name LIKE ? OR customer_phone LIKE ? OR company_name LIKE ? OR title LIKE ?)';
        array_push($params, $like, $like, $like, $like);
    }
    if ($status !== '' && in_array($status, ORDER_STATUSES, true)) {
        $where[]  = 'status = ?';
        $params[] = $status;
    }
    $whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $cols = 'id, user_id, drawing_id, engine, title, version_label, customer_name, customer_phone,
        company_name, due_date, status, tracking_carrier, tracking_number, created_at, reviewed_at, shipped_at, delivered_at';

    $stmt = $pdo->prepare("SELECT $cols FROM orders $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([...$params, $limit, $offset]);

    $cntStmt = $pdo->prepare("SELECT COUNT(*) FROM orders $whereClause");
    $cntStmt->execute($params);

    echo json_encode([
        'orders' => $stmt->fetchAll(),
        'total'  => (int) $cntStmt->fetchColumn(),
        'page'   => $page,
        'limit'  => $limit,
    ]);
    exit;
}

// PUT: 상태/운송장/수정요청 사유 변경
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $body   = json_decode(file_get_contents('php://input'), true) ?? [];
    $id     = (int) ($body['id'] ?? 0);
    $status = array_key_exists('status', $body) ? trim($body['status']) : null;
    $carrier    = array_key_exists('tracking_carrier', $body) ? trim($body['tracking_carrier']) : null;
    $trackNo    = array_key_exists('tracking_number',  $body) ? trim($body['tracking_number'])  : null;
    $note       = array_key_exists('revision_note',    $body) ? trim($body['revision_note'])    : null;
    $finalPrice = array_key_exists('final_price',      $body) ? trim((string) $body['final_price']) : null;
    $priceNote  = array_key_exists('price_note',       $body) ? trim($body['price_note'])       : null;

    if (!$id) { http_response_code(422); echo json_encode(['error' => '대상 주문이 없습니다.']); exit; }

    $stmt = $pdo->prepare('SELECT status, shipped_at, delivered_at, final_price FROM orders WHERE id = ?');
    $stmt->execute([$id]);
    $current = $stmt->fetch();
    if (!$current) { http_response_code(404); echo json_encode(['error' => '주문을 찾을 수 없습니다.']); exit; }

    if ($status !== null && !in_array($status, ORDER_STATUSES, true)) {
        http_response_code(422); echo json_encode(['error' => '잘못된 상태 값입니다.']); exit;
    }
    if ($status === 'revision_requested' && ($note === null || $note === '')) {
        http_response_code(422); echo json_encode(['error' => '수정요청 사유를 입력해주세요.']); exit;
    }
    if ($status === 'shipped' && (!$carrier && !$trackNo)) {
        http_response_code(422); echo json_encode(['error' => '택배사와 운송장번호를 입력해주세요.']); exit;
    }
    // 자동 가격공식이 확정되기 전까지는 견적확정 단계에서 협의된 확정가격을 수기로 입력받는다
    $effectiveFinalPrice = $finalPrice !== null && $finalPrice !== '' ? $finalPrice : $current['final_price'];
    if ($status === 'quote_finalized' && (!is_numeric($effectiveFinalPrice) || (float) $effectiveFinalPrice <= 0)) {
        http_response_code(422); echo json_encode(['error' => '확정 가격을 입력해주세요.']); exit;
    }
    if ($finalPrice !== null && $finalPrice !== '' && !is_numeric($finalPrice)) {
        http_response_code(422); echo json_encode(['error' => '확정 가격은 숫자로 입력해주세요.']); exit;
    }
    if (mb_strlen((string) $note) > 2000 || mb_strlen((string) $carrier) > 50 || mb_strlen((string) $trackNo) > 50 || mb_strlen((string) $priceNote) > 2000) {
        http_response_code(422); echo json_encode(['error' => '입력값이 너무 깁니다.']); exit;
    }

    $sets = []; $binds = [];
    if ($status !== null) {
        $sets[] = 'status = ?';      $binds[] = $status;
        $sets[] = 'reviewed_by = ?'; $binds[] = $adminId;
        $sets[] = 'reviewed_at = ?'; $binds[] = date('Y-m-d H:i:s');
        if ($status === 'shipped' && !$current['shipped_at']) {
            $sets[] = 'shipped_at = ?'; $binds[] = date('Y-m-d H:i:s');
        }
        if ($status === 'delivered' && !$current['delivered_at']) {
            $sets[] = 'delivered_at = ?'; $binds[] = date('Y-m-d H:i:s');
        }
    }
    if ($carrier    !== null) { $sets[] = 'tracking_carrier = ?'; $binds[] = $carrier ?: null; }
    if ($trackNo    !== null) { $sets[] = 'tracking_number = ?';  $binds[] = $trackNo ?: null; }
    if ($note       !== null) { $sets[] = 'revision_note = ?';    $binds[] = $note ?: null; }
    if ($finalPrice !== null) { $sets[] = 'final_price = ?';      $binds[] = $finalPrice !== '' ? $finalPrice : null; }
    if ($priceNote  !== null) { $sets[] = 'price_note = ?';       $binds[] = $priceNote ?: null; }

    if (!$sets) { http_response_code(422); echo json_encode(['error' => '변경할 내용이 없습니다.']); exit; }

    $binds[] = $id;
    $pdo->prepare('UPDATE orders SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($binds);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
