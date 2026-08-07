<?php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/mailer.php';

// multipart/form-data로 받는다 (파일 첨부 지원을 위해 JSON 대신 $_POST/$_FILES 사용)
$name     = trim($_POST['name']    ?? '');
$email    = trim($_POST['email']   ?? '');
$subject  = trim($_POST['subject'] ?? '');
$message  = trim($_POST['message'] ?? '');
$website  = trim($_POST['website'] ?? '');   // 허니팟 — 사람 눈엔 안 보이는 필드, 채워져 있으면 봇
$openedAt = (float)($_POST['opened_at'] ?? 0);

// 봇 방어: 허니팟이 채워졌거나, 폼이 뜬 지 2초도 안 돼 제출되면 봇으로 간주하고
// 실제로는 아무것도 하지 않되 정상 응답처럼 보이게 해서 봇이 적응하지 못하게 함
if ($website !== '') {
    echo json_encode(['ok' => true]); exit;
}
if ($openedAt > 0) {
    $elapsedMs = (microtime(true) * 1000) - $openedAt;
    if ($elapsedMs >= 0 && $elapsedMs < 2000) {
        echo json_encode(['ok' => true]); exit;
    }
}

if (!$name || !$email || !$subject || !$message) {
    http_response_code(422); echo json_encode(['error' => '모든 항목을 입력해주세요.']); exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422); echo json_encode(['error' => '이메일 형식이 올바르지 않습니다.']); exit;
}
if (mb_strlen($message) > 2000) {
    http_response_code(422); echo json_encode(['error' => '내용은 2000자 이내로 입력해주세요.']); exit;
}

// 첨부파일 (선택) — 10MB 이하, 허용 확장자만
$attachments = [];
if (!empty($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(422); echo json_encode(['error' => '파일 업로드에 실패했습니다.']); exit;
    }
    if ($file['size'] > 10 * 1024 * 1024) {
        http_response_code(422); echo json_encode(['error' => '첨부파일은 10MB 이하만 가능합니다.']); exit;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExt = ['jpg','jpeg','png','gif','webp','pdf','zip','dwg','dxf','doc','docx','xls','xlsx','hwp'];
    if (!in_array($ext, $allowedExt, true)) {
        http_response_code(422); echo json_encode(['error' => '지원하지 않는 파일 형식입니다.']); exit;
    }
    $attachments[] = [
        'name'     => basename($file['name']),
        'tmp_name' => $file['tmp_name'],
        'type'     => $file['type'] ?: 'application/octet-stream',
    ];
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

$attachmentName = $attachments ? $attachments[0]['name'] : '';
$sent = send_mail(
    mail_address('sales'),
    '[문의] ' . $subject,
    'contact',
    compact('name', 'email', 'subject', 'message', 'attachmentName'),
    'Reply-To: ' . $email,
    'sales',
    '',
    $attachments
);

if (!$sent) {
    http_response_code(500); echo json_encode(['error' => '메일 전송에 실패했습니다. 직접 이메일로 문의해주세요.']); exit;
}

$pdo->prepare("INSERT INTO contact_log (ip_hash, name, email, subject, sent_at) VALUES (?, ?, ?, ?, NOW())")
    ->execute([$ipHash, mb_substr($name, 0, 50), mb_substr($email, 0, 100), mb_substr($subject, 0, 100)]);

echo json_encode(['ok' => true]);
