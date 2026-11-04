<?php
// 홍보메일 수신거부 — 메일 속 링크(GET) 또는 메일 클라이언트의 원클릭 수신거부(POST)로 호출된다. 로그인 불필요, 서명된 토큰으로만 처리.
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../lib/promo_mail.php';

$id = promo_unsub_verify((string)($_GET['t'] ?? ''));
$ok = false;
if ($id) {
    $pdo = db();
    promo_ensure_tables($pdo);
    $pdo->prepare('UPDATE promo_companies SET opt_out = 1 WHERE id = ?')->execute([$id]);
    $ok = true;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') { http_response_code($ok ? 200 : 400); exit; }
http_response_code($ok ? 200 : 400);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>수신거부</title>
</head>
<body style="margin:0;font-family:'Apple SD Gothic Neo','Malgun Gothic',sans-serif;background:#fff;color:#222;">
<div style="max-width:420px;margin:15vh auto;padding:0 24px;text-align:center;">
<?php if ($ok): ?>
    <h1 style="font-size:20px;">수신거부가 처리되었습니다.</h1>
    <p style="font-size:14px;color:#666;line-height:1.7;">앞으로 <?= htmlspecialchars(SITE_NAME) ?>의 홍보 메일을 보내지 않습니다.</p>
<?php else: ?>
    <h1 style="font-size:20px;">유효하지 않은 링크입니다.</h1>
    <p style="font-size:14px;color:#666;line-height:1.7;">수신거부는 <?= htmlspecialchars(mail_address('sales')) ?>로 문의해 주세요.</p>
<?php endif; ?>
</div>
</body>
</html>
