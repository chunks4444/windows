<?php
header('Content-Type: application/json; charset=UTF-8');
set_exception_handler(function(Throwable $e) {
    if (!headers_sent()) http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
});
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/promo_mail.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403);
    echo json_encode(['error' => '권한이 없습니다.']);
    exit;
}

$pdo = db();
promo_ensure_tables($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // 발송 횟수·마지막 발송 시각은 발송 이력(promo_logs)에서 성공('sent') 건만 센다
    $rows = $pdo->query(
        "SELECT c.*, COALESCE(s.sent_count, 0) AS sent_count, s.last_sent_at
         FROM promo_companies c
         LEFT JOIN (SELECT company_id, COUNT(*) AS sent_count, MAX(sent_at) AS last_sent_at
                    FROM promo_logs WHERE status = 'sent' GROUP BY company_id) s ON s.company_id = c.id
         ORDER BY c.id DESC"
    )->fetchAll();
    echo json_encode(['companies' => $rows, 'industries' => PROMO_INDUSTRIES]);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? '';

if ($action === 'save') {
    $id       = (int)($body['id'] ?? 0);
    $name     = trim($body['name'] ?? '');
    $email    = strtolower(trim($body['email'] ?? ''));
    $phone    = trim($body['phone'] ?? '');
    $address  = trim($body['address'] ?? '');
    $industry = trim($body['industry'] ?? '');
    $website  = trim($body['website'] ?? '');
    $optOut   = !empty($body['opt_out']) ? 1 : 0;
    // 홈페이지: 스킴 없이 입력해도 되도록 https://를 붙여 저장 (http/https만 허용)
    if ($website !== '') {
        if (!preg_match('#^https?://#i', $website)) $website = 'https://' . $website;
        if (!filter_var($website, FILTER_VALIDATE_URL) || strlen($website) > 255) { http_response_code(400); echo json_encode(['error' => '홈페이지 주소 형식이 올바르지 않습니다.']); exit; }
    }

    if ($name === '') { http_response_code(400); echo json_encode(['error' => '회사명을 입력하세요.']); exit; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { http_response_code(400); echo json_encode(['error' => '이메일 형식이 올바르지 않습니다.']); exit; }
    if (!in_array($industry, PROMO_INDUSTRIES, true)) { http_response_code(400); echo json_encode(['error' => '업종을 선택하세요.']); exit; }

    $dup = $pdo->prepare('SELECT id FROM promo_companies WHERE email = ? AND id <> ?');
    $dup->execute([$email, $id]);
    if ($dup->fetchColumn()) { http_response_code(409); echo json_encode(['error' => '이미 등록된 이메일입니다.']); exit; }

    if ($id) {
        $pdo->prepare('UPDATE promo_companies SET name=?, email=?, phone=?, address=?, website=?, industry=?, opt_out=? WHERE id=?')
            ->execute([$name, $email, $phone, $address, $website, $industry, $optOut, $id]);
    } else {
        $pdo->prepare('INSERT INTO promo_companies (name, email, phone, address, website, industry, opt_out) VALUES (?,?,?,?,?,?,?)')
            ->execute([$name, $email, $phone, $address, $website, $industry, $optOut]);
        $id = (int)$pdo->lastInsertId();
    }
    $stmt = $pdo->prepare('SELECT * FROM promo_companies WHERE id = ?');
    $stmt->execute([$id]);
    echo json_encode(['ok' => true, 'company' => $stmt->fetch()]);
    exit;
}

if ($action === 'toggle_opt_out') {
    $id = (int)($body['id'] ?? 0);
    $pdo->prepare('UPDATE promo_companies SET opt_out = 1 - opt_out WHERE id = ?')->execute([$id]);
    $stmt = $pdo->prepare('SELECT opt_out FROM promo_companies WHERE id = ?');
    $stmt->execute([$id]);
    echo json_encode(['ok' => true, 'opt_out' => (int)$stmt->fetchColumn()]);
    exit;
}

if ($action === 'delete') {
    $pdo->prepare('DELETE FROM promo_companies WHERE id = ?')->execute([(int)($body['id'] ?? 0)]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action']);
