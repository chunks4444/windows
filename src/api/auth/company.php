<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401);
    echo json_encode(['error' => '인증이 필요합니다.']);
    exit;
}

$userId = (int) $payload['sub'];
$pdo    = db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare('SELECT company_name, company_biz_no, company_biz_type, company_biz_category,
                                  company_ceo, company_phone,
                                  company_zipcode, company_address, company_address_detail
                           FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    echo json_encode(['company' => $row ?: []]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $body    = json_decode(file_get_contents('php://input'), true) ?? [];
    $name     = trim($body['company_name']           ?? '');
    $bizNo    = trim($body['company_biz_no']         ?? '');
    $bizType  = trim($body['company_biz_type']       ?? '');
    $bizCat   = trim($body['company_biz_category']   ?? '');
    $ceo      = trim($body['company_ceo']            ?? '');
    $phone    = trim($body['company_phone']          ?? '');
    $zip      = trim($body['company_zipcode']        ?? '');
    $addr     = trim($body['company_address']        ?? '');
    $detail   = trim($body['company_address_detail'] ?? '');

    if (mb_strlen($name) > 100 || mb_strlen($bizNo) > 20 ||
        mb_strlen($bizType) > 100 || mb_strlen($bizCat) > 100 ||
        mb_strlen($ceo) > 100 || mb_strlen($phone) > 30 ||
        mb_strlen($zip) > 10 || mb_strlen($addr) > 255 || mb_strlen($detail) > 100) {
        http_response_code(422);
        echo json_encode(['error' => '입력값이 너무 깁니다.']);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE users SET
        company_name=?, company_biz_no=?, company_biz_type=?, company_biz_category=?,
        company_ceo=?, company_phone=?,
        company_zipcode=?, company_address=?, company_address_detail=?
        WHERE id=?');
    $stmt->execute([
        $name ?: null, $bizNo ?: null, $bizType ?: null, $bizCat ?: null,
        $ceo  ?: null, $phone ?: null,
        $zip  ?: null, $addr  ?: null, $detail ?: null,
        $userId,
    ]);

    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
