<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit;
}

$pdo  = db();
$stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
$stmt->execute([$payload['sub']]);
$me   = $stmt->fetch();

if (!$me || $me['role'] !== 's') {
    http_response_code(403); echo json_encode(['error' => '슈퍼 권한이 필요합니다.']); exit;
}

$_body = json_decode(file_get_contents('php://input'), true) ?? [];

// GET 또는 POST(action 없음): 회원 목록
if ($_SERVER['REQUEST_METHOD'] === 'GET' || (($_SERVER['REQUEST_METHOD'] === 'POST') && !isset($_body['action']))) {
    $page   = max(1, (int)($_body['page'] ?? $_GET['page'] ?? 1));
    $limit  = 20;
    $offset = ($page - 1) * $limit;
    $q      = trim($_body['q'] ?? $_GET['q'] ?? '');

    $cols = 'id, email, role, name, phone, company, created_at, last_login_at, last_login_ip, withdrawn_at,
        view_spec, view_parts, view_cost, view_price, view_leadtime, view_shipping, view_desc,
        company_name, company_biz_no, company_biz_type, company_biz_category, company_ceo, company_phone,
        company_zipcode, company_address, company_address_detail,
        (SELECT COUNT(*) FROM drawings WHERE user_id = users.id) AS drawing_count, (SELECT COUNT(*) FROM drawing_export_logs WHERE user_id = users.id) AS export_count';

    if ($q) {
        $like    = '%' . $q . '%';
        $stmt    = $pdo->prepare("SELECT $cols FROM users WHERE email LIKE ? OR name LIKE ? OR company_name LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?");
        $stmt->execute([$like, $like, $like, $limit, $offset]);
        $cntStmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email LIKE ? OR name LIKE ? OR company_name LIKE ?');
        $cntStmt->execute([$like, $like, $like]);
    } else {
        $stmt    = $pdo->prepare("SELECT $cols FROM users ORDER BY id DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        $cntStmt = $pdo->query('SELECT COUNT(*) FROM users');
    }

    echo json_encode([
        'users' => $stmt->fetchAll(),
        'total' => (int) $cntStmt->fetchColumn(),
        'page'  => $page,
        'limit' => $limit,
    ]);
    exit;
}

// PUT: 회원 정보 수정
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $body      = json_decode(file_get_contents('php://input'), true) ?? [];
    $userId    = (int)($body['id'] ?? 0);
    $role      = $body['role']  ?? null;
    $name      = array_key_exists('name',      $body) ? trim($body['name'])  : null;
    $phone     = array_key_exists('phone',     $body) ? trim($body['phone']) : null;
    $withdrawn = array_key_exists('withdrawn', $body) ? (bool)$body['withdrawn'] : null;
    $viewSpec     = array_key_exists('view_spec',     $body) ? (bool)$body['view_spec']     : null;
    $viewParts    = array_key_exists('view_parts',    $body) ? (bool)$body['view_parts']    : null;
    $viewCost     = array_key_exists('view_cost',     $body) ? (bool)$body['view_cost']     : null;
    $viewPrice    = array_key_exists('view_price',    $body) ? (bool)$body['view_price']    : null;
    $viewLeadtime = array_key_exists('view_leadtime', $body) ? (bool)$body['view_leadtime'] : null;
    $viewShipping = array_key_exists('view_shipping', $body) ? (bool)$body['view_shipping'] : null;
    $viewDesc     = array_key_exists('view_desc',     $body) ? (bool)$body['view_desc']     : null;

    $companyFields = [
        'company_name', 'company_biz_no', 'company_biz_type', 'company_biz_category',
        'company_ceo', 'company_phone', 'company_zipcode', 'company_address', 'company_address_detail',
    ];

    if (!$userId) {
        http_response_code(422); echo json_encode(['error' => '대상 사용자가 없습니다.']); exit;
    }
    if ($role !== null && !in_array($role, ['s', 'm', 'a', 'u'], true)) {
        http_response_code(422); echo json_encode(['error' => '잘못된 권한 값입니다.']); exit;
    }

    $sets = []; $binds = [];
    if ($role      !== null) { $sets[] = 'role = ?';          $binds[] = $role; }
    if ($name      !== null) { $sets[] = 'name = ?';          $binds[] = $name  ?: null; }
    if ($phone     !== null) { $sets[] = 'phone = ?';         $binds[] = $phone ?: null; }
    if ($withdrawn !== null) { $sets[] = 'withdrawn_at = ?';  $binds[] = $withdrawn ? date('Y-m-d H:i:s') : null; }
    if ($viewSpec     !== null) { $sets[] = 'view_spec = ?';     $binds[] = $viewSpec     ? 1 : 0; }
    if ($viewParts    !== null) { $sets[] = 'view_parts = ?';    $binds[] = $viewParts    ? 1 : 0; }
    if ($viewCost     !== null) { $sets[] = 'view_cost = ?';     $binds[] = $viewCost     ? 1 : 0; }
    if ($viewPrice    !== null) { $sets[] = 'view_price = ?';    $binds[] = $viewPrice    ? 1 : 0; }
    if ($viewLeadtime !== null) { $sets[] = 'view_leadtime = ?'; $binds[] = $viewLeadtime ? 1 : 0; }
    if ($viewShipping !== null) { $sets[] = 'view_shipping = ?'; $binds[] = $viewShipping ? 1 : 0; }
    if ($viewDesc     !== null) { $sets[] = 'view_desc = ?';     $binds[] = $viewDesc     ? 1 : 0; }
    foreach ($companyFields as $f) {
        if (array_key_exists($f, $body)) {
            $v = trim((string) $body[$f]);
            if (mb_strlen($v) > 255) {
                http_response_code(422); echo json_encode(['error' => '입력값이 너무 깁니다.']); exit;
            }
            $sets[] = "$f = ?"; $binds[] = $v ?: null;
        }
    }

    if (!$sets) {
        http_response_code(422); echo json_encode(['error' => '변경할 내용이 없습니다.']); exit;
    }

    $binds[] = $userId;
    $pdo->prepare('UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($binds);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
