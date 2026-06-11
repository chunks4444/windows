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

// GET: 회원 목록
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $page   = max(1, (int)($_GET['page'] ?? 1));
    $limit  = 20;
    $offset = ($page - 1) * $limit;
    $q      = trim($_GET['q'] ?? '');

    if ($q) {
        $like    = '%' . $q . '%';
        $stmt    = $pdo->prepare('SELECT id, email, role, name, phone, company, created_at, last_login_at, last_login_ip, withdrawn_at FROM users WHERE email LIKE ? OR name LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?');
        $stmt->execute([$like, $like, $limit, $offset]);
        $cntStmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email LIKE ? OR name LIKE ?');
        $cntStmt->execute([$like, $like]);
    } else {
        $stmt    = $pdo->prepare('SELECT id, email, role, name, phone, company, created_at, last_login_at, last_login_ip, withdrawn_at FROM users ORDER BY id DESC LIMIT ? OFFSET ?');
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
