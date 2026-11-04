<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/order_status.php';
require_once __DIR__ . '/../../lib/engine_settings.php';
require_once __DIR__ . '/../../lib/order_estimate.php';

// 목재 사용량(재 수) — 관리자 전용. 엔진 응답(고객에게도 내려감)에는 재 수를 싣지 않으므로,
// 주문 시점 견적의 목재비(price_breakdown.door/muntol)를 현재 원가표의 그 목재 단가×가중치로 나눠 되짚는다.
// compute_price_estimate()가 목재비 = 재 수 × 가중치 × 단가로 계산하므로, 주문 뒤 단가·가중치를 안 바꿨다면 정확히 같은 값.
function order_wood_usage(?string $specJson, ?string $breakdownJson): ?array {
    $p = $specJson ? json_decode($specJson, true) : null;
    $b = $breakdownJson ? json_decode($breakdownJson, true) : null;
    if (!is_array($p) || !is_array($b) || empty($p['wood'])) return null;
    $item = get_cost_table_item('wood', $p['wood']);
    $per  = $item ? (float) $item['unit_price'] * (float) $item['weight'] : 0;
    if ($per <= 0) return null;

    $JAE_M3 = 30.3 * 30.3 * 3636 / 1e9; // 1재 부피(㎥) — geometry.php의 $_JAE와 같은 기준
    $door   = (float) ($b['door'] ?? 0) / $per;
    $muntol = (float) ($b['muntol'] ?? 0) / $per;
    return [
        'wood'     => $p['wood'],
        'doorJae'  => round($door, 2),
        'muntolJae'=> round($muntol, 2),
        'totalJae' => round($door + $muntol, 2),
        'totalM3'  => round(($door + $muntol) * $JAE_M3, 4),
    ];
}

// 주문 시점 도면 사양(spec_json = 엔진 getParams() 스냅샷)을 관리자 상세 화면용 요약으로 바꾼다.
// 문 1짝 크기는 스냅샷에 없어서 7엔진 geometry.php와 같은 공식(문틀 치수 − 문틀두께·갭 → 짝수로 나눔)으로 다시 계산한다.
// 문틀두께·갭·울거미 단계표는 "지금" 엔진설정 값이라, 주문 뒤에 설정을 바꿨다면 주문 당시와 몇 mm 다를 수 있다.
function order_spec_summary(string $engine, ?string $specJson): ?array {
    $p = $specJson ? json_decode($specJson, true) : null;
    if (!is_array($p) || empty($p['W']) || empty($p['H'])) return null;

    $cfg        = get_engine_settings($engine);
    $frameThick = (int) ($cfg['frameThick'] ?? 30);
    $frameGap   = (int) ($cfg['frameGap'] ?? 2);
    $doorType   = ($p['doorType'] ?? '') === 'slide' ? 'slide' : 'swing';
    $doorCount  = max(1, (int) ($p['doorCount'] ?? 1));
    $openingW   = (int) $p['W'];
    $openingH   = (int) $p['H'];
    $frameW     = (int) ($p['frame'] ?? 0);
    $frameH     = (int) ($p['frameH'] ?? 0);

    $outerH = max(100, $openingH - 2 * $frameThick - 2 * $frameGap);
    if ($step = get_frame_size_step($doorType, (int) round($outerH))) {
        $frameW = max($frameW, (int) $step['frame_width']);
        $frameH = max($frameH, (int) $step['frame_width']);
    }
    $base   = $openingW - 2 * $frameThick - 2 * $frameGap;
    $outerW = $doorType === 'slide'
        ? ($base + $frameW * ($doorCount - 1)) / $doorCount
        : ($base - $frameGap * ($doorCount - 1)) / $doorCount;
    $outerW = max(100, $outerW);

    // 색은 hex로만 저장되므로 컬러 팔레트(color_swatches)에서 업체 코드·이름을 찾아 붙인다
    $swatches = [];
    try {
        foreach (db()->query('SELECT brand, code, name, hex FROM color_swatches ORDER BY is_active DESC, id') as $s) {
            $swatches[strtolower($s['hex'])] ??= $s;
        }
    } catch (Throwable $e) {}
    $color = function ($hex) use ($swatches) {
        if (!is_string($hex) || $hex === '') return null;
        $s = $swatches[strtolower($hex)] ?? null;
        return [
            'hex'   => $hex,
            'label' => $s ? trim(($s['brand'] ? $s['brand'] . ' ' : '') . $s['code'] . ' ' . $s['name']) : '',
        ];
    };

    // 면 재질 — 문 전체 배지(배접지·백지·일반유리) 1종 + 칸별 재질(색한지·칼라유리)을 재질·색별 칸 수로 묶음
    $subLabels = ['saek' => '색한지', 'baejeop' => '배접지', 'ilban' => '일반한지(백지)', 'plain' => '일반유리', 'color' => '칼라유리'];
    $faceLabel = function ($fm) use ($subLabels) {
        $label = ($fm['material'] ?? '') === 'glass' ? '유리' : '한지';
        if (!empty($fm['sub'])) $label .= ' · ' . ($subLabels[$fm['sub']] ?? $fm['sub']);
        $code = trim(($fm['brand'] ?? '') . ' ' . ($fm['code'] ?? ''));
        if ($code !== '' || !empty($fm['name'])) $label .= ' (' . trim($code . ' ' . ($fm['name'] ?? '')) . ')';
        return $label;
    };
    $faceCells = [];
    foreach ((array) ($p['faceMaterialMap'] ?? []) as $fm) {
        if (!is_array($fm) || empty($fm['material'])) continue;
        $key = $faceLabel($fm);
        $faceCells[$key] ??= ['label' => $key, 'hex' => $fm['hex'] ?? '', 'cells' => 0];
        $faceCells[$key]['cells']++;
    }
    $backing = is_array($p['faceBacking'] ?? null) && !empty($p['faceBacking']['material'])
        ? ['label' => $faceLabel($p['faceBacking']), 'hex' => $p['faceBacking']['hex'] ?? '']
        : null;

    return [
        'size' => [
            'openingW'    => $openingW,
            'openingH'    => $openingH,
            'doorType'    => $doorType,
            'doorCount'   => $doorCount,
            'doorW'       => round($outerW, 1),
            'doorH'       => $outerH,
            'frameW'      => $frameW,
            'frameH'      => $frameH,
            'slat'        => isset($p['slat']) ? (float) $p['slat'] : null,
            'cols'        => isset($p['cols']) ? (int) $p['cols'] : null,
            'rows'        => !empty($p['rowsManual']) ? (int) ($p['rows'] ?? 0) : null,
            'vRatio'      => isset($p['vRatio']) ? (float) $p['vRatio'] : null,
            'pungpan'     => !empty($p['pungpanOn']) ? (int) ($p['pungpan'] ?? 0) : null,
            'rotate'      => !empty($p['rotate']),
            'showMuntol'  => !empty($p['showMuntol']),
            'frameThick'  => $frameThick,
            'frameGap'    => $frameGap,
        ],
        'finish' => [
            'wood'        => (string) ($p['wood'] ?? ''),
            'finish'      => (string) ($p['finish'] ?? ''),
            'hardware'    => (string) ($p['hardware'] ?? ''),
            'frameColor'  => $color($p['frameColor'] ?? null),
            'slatColor'   => $color($p['slatColor'] ?? null),
            'muntolColor' => !empty($p['showMuntol']) ? $color($p['muntolColor'] ?? null) : null,
            'backing'     => $backing,
            'faceCells'   => array_values($faceCells),
        ],
    ];
}

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403); echo json_encode(['error' => '슈퍼 권한이 필요합니다.']); exit;
}
$adminId = (int) $payload['sub'];

$pdo = db();

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

        // 같은 도면(drawing_id)의 과거 주문 이력 — 취소/수정요청으로 재접수된 경우 이전 협의 내용을 관리자가 참고할 수 있도록
        $history = [];
        if ($order['drawing_id']) {
            $histStmt = $pdo->prepare(
                'SELECT id, status, revision_note, price_note, memo, created_at, title, version_label, thumbnail FROM orders
                 WHERE drawing_id = ? AND id != ? ORDER BY created_at DESC'
            );
            $histStmt->execute([$order['drawing_id'], $id]);
            $history = $histStmt->fetchAll();
        }

        // 견적 스냅샷이 없는 주문(서버 산출 도입 전, 원가 열람권한 없는 회원이 접수해 빈 값으로 들어온 것)은
        // 관리자가 처음 열 때 한 번만 지금 원가표로 산출해 주문에 고정 저장한다 — 이후엔 원가표·도면이 바뀌어도 그대로(computedAt으로 시점 표시).
        if (!$order['price_breakdown'] && ($est = order_compute_estimate($order['engine'], $order['spec_json'], 'admin_backfill'))) {
            $order['price_breakdown'] = json_encode($est['breakdown'], JSON_UNESCAPED_UNICODE);
            $order['estimated_price'] = $order['estimated_price'] ?: $est['breakdown']['total'];
            $pdo->prepare('UPDATE orders SET price_breakdown = ?, estimated_price = ? WHERE id = ? AND price_breakdown IS NULL')
                ->execute([$order['price_breakdown'], $order['estimated_price'], $id]);
        }

        // 도면 최신 저장본의 예상견적 — 엔진 getParams()가 저장 시 화면의 예상가를 estimatedPrice로 함께 남긴다.
        // 주문 뒤 고객이 도면을 고쳐 다시 저장했다면 주문 시점 예상가(orders.estimated_price)와 달라질 수 있어 둘 다 보여준다.
        $drawingEstimate = null;
        if ($order['drawing_id']) {
            $verStmt = $pdo->prepare(
                "SELECT JSON_EXTRACT(params, '$.estimatedPrice') AS price, saved_at FROM drawing_versions
                 WHERE drawing_id = ? ORDER BY saved_at DESC, id DESC LIMIT 1"
            );
            $verStmt->execute([$order['drawing_id']]);
            if ($ver = $verStmt->fetch()) {
                $drawingEstimate = ['price' => $ver['price'] !== null ? (float) $ver['price'] : null, 'saved_at' => $ver['saved_at']];
            }
        }

        echo json_encode([
            'order'   => $order,
            'drawingEstimate' => $drawingEstimate,
            'history' => $history,
            'spec'    => order_spec_summary($order['engine'], $order['spec_json']),
            'wood'    => order_wood_usage($order['spec_json'], $order['price_breakdown']),
        ]);
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
    if ($finalPrice !== null && $finalPrice !== '' && !is_numeric($finalPrice)) {
        http_response_code(422); echo json_encode(['error' => '확정 가격은 숫자로 입력해주세요.']); exit;
    }
    // 자동 가격공식이 확정되기 전까지는 견적확정 단계에서 협의된 확정가격을 수기로 입력받는다
    $effectiveFinalPrice = $finalPrice !== null && $finalPrice !== '' ? $finalPrice : $current['final_price'];
    if ($status === 'quote_finalized' && (float) $effectiveFinalPrice <= 0) {
        http_response_code(422); echo json_encode(['error' => '확정 가격을 입력해주세요.']); exit;
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
    if ($finalPrice !== null) {
        $sets[] = 'final_price = ?'; $binds[] = $finalPrice !== '' ? $finalPrice : null;
        // 자동계산 공식이 아직 없어 수기로 입력한 가격임을 표시 — 나중에 공식이 붙으면 이 값으로 구분한다
        $sets[] = 'price_formula_version = ?'; $binds[] = $finalPrice !== '' ? 'manual' : null;
    }
    if ($priceNote  !== null) { $sets[] = 'price_note = ?';       $binds[] = $priceNote ?: null; }

    if (!$sets) { http_response_code(422); echo json_encode(['error' => '변경할 내용이 없습니다.']); exit; }

    $binds[] = $id;
    $pdo->prepare('UPDATE orders SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($binds);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
