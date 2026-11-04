<?php
// 견적서 관리 API (슈퍼 관리자 전용) — 화면: /src/admin/quotes.php, 공용 로직: src/lib/quote.php
//
// POST {action:'list', page, q}            발행된 견적서 목록(전 차수)
// POST {action:'orders', q}                견적서를 쓸 주문 찾기(최근 주문 + 최신 견적 차수)
// POST {action:'draft', order_id}          작성 창 기본값 — 이전 차수가 있으면 그 내용을, 없으면 주문 정보로 1줄 채움
// POST {action:'issue', order_id, items, valid_until, lead_time, note, send_email, set_status, customer}
//                                          발행(새 차수 저장) → 확정가 갱신 → (선택) 상태 '견적확정' → (선택) 메일 발송
// POST {action:'resend', quote_id}         같은 견적서 메일 다시 보내기 (견적서 내용 전체가 메일 본문)
// POST {action:'supplier', supplier}       공급자(평목) 정보 저장
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../lib/cors.php';
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/quote.php';
require_once __DIR__ . '/../../lib/mailer.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403); echo json_encode(['error' => '슈퍼 권한이 필요합니다.']); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}
$adminId = (int) $payload['sub'];
$pdo  = db();
$body = json_decode(file_get_contents('php://input'), true) ?? [];
$action = (string) ($body['action'] ?? 'list');

function fail(string $msg, int $code = 422): never {
    http_response_code($code); echo json_encode(['error' => $msg], JSON_UNESCAPED_UNICODE); exit;
}
function out(array $data): never {
    echo json_encode($data, JSON_UNESCAPED_UNICODE); exit;
}

function load_order(PDO $pdo, int $id): array {
    $st = $pdo->prepare('SELECT o.*, u.email AS user_email, u.company_biz_no, u.company_ceo, u.company_phone,
                                u.company_zipcode, u.company_address, u.company_address_detail
                         FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: fail('주문을 찾을 수 없습니다.', 404);
}

// 수신자(고객) 정보 — 주문 시점 이름·연락처·회사명 스냅샷 + 지금 회원정보의 사업자 정보
function default_customer(array $o): array {
    return [
        'name'    => (string) $o['customer_name'],
        'company' => (string) ($o['company_name'] ?? ''),
        'biz_no'  => (string) ($o['company_biz_no'] ?? ''),
        'ceo'     => (string) ($o['company_ceo'] ?? ''),
        'phone'   => (string) ($o['customer_phone'] ?: ($o['company_phone'] ?? '')),
        'email'   => (string) ($o['user_email'] ?? ''),
        'address' => trim(($o['company_address'] ?? '') . ' ' . ($o['company_address_detail'] ?? '')),
    ];
}

// 첫 발행 기본 품목 1줄 — 품명: "{패턴명} 창호 제작", 규격: 도면명·문틀 크기·문 종류·목재·마감
function default_items(array $o): array {
    $p = json_decode((string) $o['spec_json'], true) ?: [];
    $spec = [];
    if ($o['title'] !== '') $spec[] = $o['title'] . ($o['version_label'] ? ' (' . $o['version_label'] . ')' : '');
    if (!empty($p['W']) && !empty($p['H'])) $spec[] = '문틀 ' . (int) $p['W'] . '×' . (int) $p['H'] . 'mm';
    if (!empty($p['doorCount'])) $spec[] = (($p['doorType'] ?? '') === 'slide' ? '미서기 ' : '여닫이 ') . (int) $p['doorCount'] . '짝';
    if (!empty($p['wood']))   $spec[] = (string) $p['wood'];
    if (!empty($p['finish'])) $spec[] = (string) $p['finish'];
    // 단가: 확정가가 있으면 그것, 없으면 주문 시점 예상가를 천 원 단위로 반올림 (부가세 별도 공급가로 취급)
    $price = (float) ($o['final_price'] ?: $o['estimated_price'] ?: 0);
    $unit  = $o['final_price'] ? (int) round($price) : (int) (round($price / 1000) * 1000);
    return [[
        'name'       => (QUOTE_ENGINE_NAME[$o['engine']] ?? $o['engine']) . ' 창호 제작',
        'spec'       => implode(' · ', $spec),
        'qty'        => 1,
        'unit_price' => $unit,
    ]];
}

// 메일 본문 = 견적서 문서 그 자체(components/quote_document.php, 인라인 스타일). 고객용 웹 열람 화면이 없으므로 링크 대신 내용 전체를 싣는다.
function send_quote_mail(array $q): bool {
    $customer = json_decode($q['customer_json'], true) ?: [];
    $to = $customer['email'] ?? '';
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) return false;
    return send_mail(
        $to,
        '[워크그룹 평목] 견적서 ' . $q['quote_no'] . ($q['title'] ? ' — ' . $q['title'] : ''),
        'quote',
        ['quote' => $q],
        '', 'sales', mail_address('sales')
    );
}

switch ($action) {

case 'list': {
    $page = max(1, (int) ($body['page'] ?? 1));
    $limit = 20; $offset = ($page - 1) * $limit;
    $q = trim((string) ($body['q'] ?? ''));
    $where = ''; $params = [];
    if ($q !== '') {
        $like = '%' . $q . '%';
        $where = 'WHERE (q.quote_no LIKE ? OR o.customer_name LIKE ? OR o.company_name LIKE ? OR o.title LIKE ? OR o.id = ?)';
        $params = [$like, $like, $like, $like, (int) preg_replace('/\D/', '', $q)];
    }
    try {
        $st = $pdo->prepare("SELECT q.id, q.order_id, q.revision, q.quote_no, q.issued_at, q.valid_until,
                                    q.supply_amount, q.vat_amount, q.total_amount, q.emailed_to, q.emailed_at,
                                    o.engine, o.title, o.customer_name, o.company_name, o.status AS order_status,
                                    (SELECT MAX(revision) FROM quotes q2 WHERE q2.order_id = q.order_id) AS latest_revision
                             FROM quotes q JOIN orders o ON o.id = q.order_id $where
                             ORDER BY q.issued_at DESC, q.id DESC LIMIT $limit OFFSET $offset");
        $st->execute($params);
        $rows = $st->fetchAll();
        $cnt = $pdo->prepare("SELECT COUNT(*) FROM quotes q JOIN orders o ON o.id = q.order_id $where");
        $cnt->execute($params);
        $total = (int) $cnt->fetchColumn();
    } catch (PDOException $e) {
        $rows = []; $total = 0; // 첫 발행 전이라 테이블이 아직 없음
    }
    out(['quotes' => $rows, 'total' => $total, 'page' => $page, 'limit' => $limit, 'supplier' => quote_supplier()]);
}

case 'orders': {
    $q = trim((string) ($body['q'] ?? ''));
    $where = "WHERE o.status != 'cancelled'"; $params = [];
    if ($q !== '') {
        $like = '%' . $q . '%';
        $where .= ' AND (o.customer_name LIKE ? OR o.company_name LIKE ? OR o.title LIKE ? OR o.id = ?)';
        $params = [$like, $like, $like, (int) preg_replace('/\D/', '', $q)];
    }
    $st = $pdo->prepare("SELECT o.id, o.engine, o.title, o.customer_name, o.company_name, o.status, o.created_at,
                                o.estimated_price, o.final_price
                         FROM orders o $where ORDER BY o.created_at DESC LIMIT 30");
    $st->execute($params);
    $orders = $st->fetchAll();
    $latest = [];
    if ($orders) {
        try {
            $ids = array_map('intval', array_column($orders, 'id'));
            $rs = $pdo->query('SELECT order_id, MAX(revision) AS rev FROM quotes WHERE order_id IN (' . implode(',', $ids) . ') GROUP BY order_id');
            foreach ($rs as $r) $latest[(int) $r['order_id']] = (int) $r['rev'];
        } catch (PDOException $e) {}
    }
    foreach ($orders as &$o) $o['latest_revision'] = $latest[(int) $o['id']] ?? 0;
    unset($o);
    out(['orders' => $orders]);
}

case 'draft': {
    $o = load_order($pdo, (int) ($body['order_id'] ?? 0));
    $history = quote_list_for_order((int) $o['id']);
    $prev = null;
    if ($history) {
        $st = $pdo->prepare('SELECT items_json, note, lead_time, valid_until, issued_at, customer_json FROM quotes WHERE id = ?');
        $st->execute([$history[0]['id']]);
        $prev = $st->fetch();
    }
    $bd = json_decode((string) $o['price_breakdown'], true) ?: [];
    $leadDays = isset($bd['leadTimeDays']) ? (int) $bd['leadTimeDays'] : null;
    $validDays = 30;
    if ($prev && $prev['valid_until']) {
        $validDays = max(1, (int) round((strtotime($prev['valid_until']) - strtotime(substr($prev['issued_at'], 0, 10))) / 86400));
    }
    out([
        'order' => [
            'id' => (int) $o['id'], 'code' => quote_order_code($o['engine'], (int) $o['id']),
            'engine' => $o['engine'], 'title' => $o['title'], 'version_label' => $o['version_label'],
            'thumbnail' => $o['thumbnail'], 'status' => $o['status'], 'created_at' => $o['created_at'],
            'customer_name' => $o['customer_name'], 'company_name' => $o['company_name'],
            'estimated_price' => $o['estimated_price'], 'final_price' => $o['final_price'],
            'price_note' => $o['price_note'], 'memo' => $o['memo'], 'due_date' => $o['due_date'],
        ],
        'next_revision' => $history ? (int) $history[0]['revision'] + 1 : 1,
        'history'  => $history,
        'items'    => $prev ? json_decode($prev['items_json'], true) : default_items($o),
        'note'     => $prev ? (string) $prev['note'] : '',
        'lead_time'=> $prev ? (string) $prev['lead_time'] : ($leadDays ? "주문 확정 후 약 {$leadDays}일" : ''),
        'valid_until' => date('Y-m-d', strtotime("+{$validDays} days")),
        // 수신자 정보는 매번 지금 회원정보로 새로 채움(이전 차수 뒤 회원이 사업자정보를 고쳤을 수 있음)
        'customer' => default_customer($o),
        'supplier' => quote_supplier(),
    ]);
}

case 'issue': {
    $o = load_order($pdo, (int) ($body['order_id'] ?? 0));
    if ($o['status'] === 'cancelled') fail('취소된 주문에는 견적서를 발행할 수 없습니다.');

    $calc = quote_normalize_items($body['items'] ?? null);
    if (isset($calc['error'])) fail($calc['error']);

    $validUntil = trim((string) ($body['valid_until'] ?? ''));
    if ($validUntil !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $validUntil)) fail('유효기한 날짜 형식을 확인해주세요.');
    $leadTime = mb_substr(trim((string) ($body['lead_time'] ?? '')), 0, 100);
    $note = trim((string) ($body['note'] ?? ''));
    if (mb_strlen($note) > 3000) fail('비고가 너무 깁니다.');

    $customer = default_customer($o);
    foreach ($customer as $k => $_) {
        if (isset($body['customer'][$k])) $customer[$k] = mb_substr(trim((string) $body['customer'][$k]), 0, 200);
    }
    if ($customer['name'] === '' && $customer['company'] === '') fail('수신자(고객) 이름이나 회사명을 입력해주세요.');
    $sendEmail = !empty($body['send_email']);
    if ($sendEmail && !filter_var($customer['email'], FILTER_VALIDATE_EMAIL)) fail('메일을 보내려면 수신자 이메일을 확인해주세요.');

    quote_ensure_table();
    $pdo->beginTransaction();
    try {
        // 같은 주문 동시 발행 대비 — 주문 행을 잠그고 다음 차수를 정한다
        $pdo->prepare('SELECT id FROM orders WHERE id = ? FOR UPDATE')->execute([$o['id']]);
        $st = $pdo->prepare('SELECT COALESCE(MAX(revision), 0) + 1 FROM quotes WHERE order_id = ?');
        $st->execute([$o['id']]);
        $rev = (int) $st->fetchColumn();
        $quoteNo = quote_order_code($o['engine'], (int) $o['id']) . '-Q' . $rev;
        $now = date('Y-m-d H:i:s');

        $pdo->prepare('INSERT INTO quotes (order_id, revision, quote_no, issued_at, issued_by, valid_until, lead_time,
                                           items_json, supply_amount, vat_amount, total_amount, note, customer_json, supplier_json)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([
                $o['id'], $rev, $quoteNo, $now, $adminId, $validUntil ?: null, $leadTime ?: null,
                json_encode($calc['items'], JSON_UNESCAPED_UNICODE), $calc['supply'], $calc['vat'], $calc['total'],
                $note ?: null, json_encode($customer, JSON_UNESCAPED_UNICODE), json_encode(quote_supplier(), JSON_UNESCAPED_UNICODE),
            ]);
        $quoteId = (int) $pdo->lastInsertId();

        // 주문의 확정 가격 = 견적서 공급가액(부가세 제외). 상태는 체크했고 아직 견적확정 전 단계일 때만 '견적확정'으로.
        $sets = ['final_price = ?', "price_formula_version = 'quote'"]; $binds = [$calc['supply']];
        if (!empty($body['set_status']) && in_array($o['status'], ['pending_review', 'revision_requested', 'approved'], true)) {
            array_push($sets, "status = 'quote_finalized'", 'reviewed_by = ?', 'reviewed_at = ?');
            array_push($binds, $adminId, $now);
        }
        $binds[] = $o['id'];
        $pdo->prepare('UPDATE orders SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($binds);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('quote issue failed: ' . $e->getMessage());
        fail('견적서 저장에 실패했습니다.', 500);
    }

    $mailed = null;
    if ($sendEmail) {
        $mailed = send_quote_mail(quote_by_id($quoteId));
        if ($mailed) $pdo->prepare('UPDATE quotes SET emailed_to = ?, emailed_at = NOW() WHERE id = ?')->execute([$customer['email'], $quoteId]);
    }
    out(['ok' => true, 'quote_id' => $quoteId, 'quote_no' => $quoteNo, 'mailed' => $mailed]);
}

case 'resend': {
    $q = quote_by_id((int) ($body['quote_id'] ?? 0)) ?: fail('견적서를 찾을 수 없습니다.', 404);
    $to = json_decode($q['customer_json'], true)['email'] ?? '';
    if (!send_quote_mail($q)) fail('메일 발송에 실패했습니다. 수신자 이메일(' . $to . ')과 메일 발송 설정을 확인해주세요.', 500);
    $pdo->prepare('UPDATE quotes SET emailed_to = ?, emailed_at = NOW() WHERE id = ?')->execute([$to, $q['id']]);
    out(['ok' => true, 'to' => $to]);
}

case 'supplier': {
    out(['ok' => true, 'supplier' => quote_save_supplier((array) ($body['supplier'] ?? []))]);
}

default:
    fail('알 수 없는 요청입니다.');
}
