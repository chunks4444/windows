<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';
require_once __DIR__ . '/../../lib/i18n.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401);
    echo json_encode(['error' => t('auth_err_need_auth')]);
    exit;
}

$userId = (int) $payload['sub'];
$pdo    = db();

// GET: 프로필 조회
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare('SELECT id, email, role, name, phone, company, zipcode, address, address_detail, created_at FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => t('auth_err_user_not_found')]);
        exit;
    }
    echo json_encode(['user' => $user]);
    exit;
}

// PUT: 프로필 수정
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $body           = json_decode(file_get_contents('php://input'), true) ?? [];
    $name           = trim($body['name']           ?? '');
    $phone          = trim($body['phone']          ?? '');
    $company        = trim($body['company']        ?? '');
    $zipcode        = trim($body['zipcode']        ?? '');
    $address        = trim($body['address']        ?? '');
    $address_detail = trim($body['address_detail'] ?? '');

    if (mb_strlen($name) > 100 || mb_strlen($phone) > 30 || mb_strlen($company) > 100 ||
        mb_strlen($zipcode) > 10 || mb_strlen($address) > 255 || mb_strlen($address_detail) > 100) {
        http_response_code(422);
        echo json_encode(['error' => t('auth_err_too_long')]);
        exit;
    }

    $stmt = $pdo->prepare(
        'UPDATE users SET name=?, phone=?, company=?, zipcode=?, address=?, address_detail=? WHERE id=?'
    );
    $stmt->execute([
        $name ?: null, $phone ?: null, $company ?: null,
        $zipcode ?: null, $address ?: null, $address_detail ?: null,
        $userId,
    ]);

    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
