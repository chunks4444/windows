<?php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/mailer.php';

$body    = json_decode(file_get_contents('php://input'), true);
$name    = trim($body['name']    ?? '');
$email   = trim($body['email']   ?? '');
$subject = trim($body['subject'] ?? '');
$message = trim($body['message'] ?? '');

if (!$name || !$email || !$subject || !$message) {
    http_response_code(422); echo json_encode(['error' => '모든 항목을 입력해주세요.']); exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422); echo json_encode(['error' => '이메일 형식이 올바르지 않습니다.']); exit;
}
if (mb_strlen($message) > 2000) {
    http_response_code(422); echo json_encode(['error' => '내용은 2000자 이내로 입력해주세요.']); exit;
}

// IP 기반 간단 rate limit (1시간에 5회)
$pdo    = db();
$ip     = (function () {
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_REAL_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) return trim(explode(',', $_SERVER[$k])[0]);
    }
    return '-';
})();
$ipHash = substr(md5($ip), 0, 8);

$cnt = $pdo->prepare("SELECT COUNT(*) FROM contact_log WHERE ip_hash = ? AND sent_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
$cnt->execute([$ipHash]);
if ((int)$cnt->fetchColumn() >= 5) {
    http_response_code(429); echo json_encode(['error' => '잠시 후 다시 시도해주세요.']); exit;
}

$sent = send_mail(
    mail_address('sales'),
    '[문의] ' . $subject,
    'contact',
    compact('name', 'email', 'subject', 'message'),
    'Reply-To: ' . $email,
    'sales'
);

if (!$sent) {
    http_response_code(500); echo json_encode(['error' => '메일 전송에 실패했습니다. 직접 이메일로 문의해주세요.']); exit;
}

$pdo->prepare("INSERT INTO contact_log (ip_hash, name, email, subject, sent_at) VALUES (?, ?, ?, ?, NOW())")
    ->execute([$ipHash, mb_substr($name, 0, 50), mb_substr($email, 0, 100), mb_substr($subject, 0, 100)]);

echo json_encode(['ok' => true]);
