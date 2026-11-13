<?php
// 포트폴리오 PDF 관리 (슈퍼 관리자 전용): GET = 현재 PDF 조회, POST(multipart: pdf, apply_template) = 새 PDF 업로드·교체.
header('Content-Type: application/json; charset=UTF-8');
set_exception_handler(function (Throwable $e) {
    if (!headers_sent()) http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
});
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/promo_mail.php';
require_once __DIR__ . '/../../lib/portfolio_pdf.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403);
    echo json_encode(['error' => '권한이 없습니다.']);
    exit;
}

$pdo = db();
promo_ensure_tables($pdo);

function fail(int $code, string $msg): never {
    http_response_code($code);
    echo json_encode(['error' => $msg]);
    exit;
}

const PORTFOLIO_PDF_MAX = 30 * 1024 * 1024;

// 옛 파일 정리: 홍보 캠페인·템플릿과 현재 포트폴리오 설정 어디에서도 쓰지 않을 때만 지운다 (DB를 먼저 바꾼 다음 호출)
function portfolio_release(PDO $pdo, string $path, string $keepPath): void {
    if ($path === '' || $path === $keepPath) return;
    $n = $pdo->prepare('SELECT (SELECT COUNT(*) FROM promo_campaigns WHERE attachment_path = ?) + (SELECT COUNT(*) FROM promo_templates WHERE attachment_path = ?)');
    $n->execute([$path, $path]);
    if ((int)$n->fetchColumn() > 0) return;
    $root = realpath(__DIR__ . '/../../../uploads/promo');
    $file = realpath(__DIR__ . '/../../..' . $path);
    if ($root && $file && str_starts_with($file, $root . DIRECTORY_SEPARATOR)) @unlink($file);
}

function current_payload(PDO $pdo): array {
    $cur = portfolio_pdf_current($pdo);
    $tpl = $pdo->query('SELECT attachment_name, attachment_path FROM promo_templates WHERE id = 1')->fetch() ?: [];
    return [
        'current'      => $cur,
        'template_pdf' => (string)($tpl['attachment_name'] ?? ''),
        'template_same' => $cur && (string)($tpl['attachment_path'] ?? '') === $cur['path'],
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(['ok' => true] + current_payload($pdo), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail(405, '허용되지 않는 요청입니다.');

$f = $_FILES['pdf'] ?? null;
if (!$f || $f['error'] !== UPLOAD_ERR_OK) fail(400, 'PDF 업로드에 실패했습니다. (파일이 너무 크거나 전송이 끊겼을 수 있습니다)');
if ($f['size'] > PORTFOLIO_PDF_MAX) fail(400, 'PDF는 30MB 이하여야 합니다.');
$head = (string)@file_get_contents($f['tmp_name'], false, null, 0, 5);
if ($head !== '%PDF-') fail(400, 'PDF 파일만 올릴 수 있습니다.');

$dir = __DIR__ . '/../../../uploads/promo';
if (!is_dir($dir)) mkdir($dir, 0755, true);
$stored = bin2hex(random_bytes(16)) . '.pdf';
if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $stored)) fail(500, '파일을 저장하지 못했습니다.');
chmod($dir . '/' . $stored, 0644);   // 업로드 임시 파일은 0600 — 웹·발송 워커가 읽을 수 있게 연다

// 받는 사람에게 보일 파일명 — 경로·제어문자 제거, 확장자 .pdf 보장
$name = preg_replace('/[\x00-\x1F\x7F\\\\\/:*?"<>|]/u', '', basename((string)$f['name']));
$name = mb_substr(preg_replace('/\.pdf$/i', '', $name), 0, 100) . '.pdf';
$newPath = '/uploads/promo/' . $stored;

$oldCfg = portfolio_pdf_current($pdo);   // 교체 전에 쓰던 파일
$oldCfgPath = $oldCfg ? $oldCfg['path'] : '';

$json = json_encode(['path' => $newPath, 'name' => $name, 'size' => (int)$f['size'], 'uploaded_at' => date('Y-m-d H:i:s')], JSON_UNESCAPED_UNICODE);
$pdo->prepare('INSERT INTO site_config (key_name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)')->execute([PORTFOLIO_PDF_KEY, $json]);

// 선택: 홍보메일 템플릿(#1)의 첨부도 같은 PDF로 바꾼다 (이미 만들어진 캠페인은 각자 첨부를 그대로 유지)
$oldTplPath = '';
if (!empty($_POST['apply_template'])) {
    $oldTplPath = (string)$pdo->query('SELECT attachment_path FROM promo_templates WHERE id = 1')->fetchColumn();
    $pdo->prepare('UPDATE promo_templates SET attachment_path = ?, attachment_name = ?, attachment_size = ? WHERE id = 1')
        ->execute([$newPath, $name, (int)$f['size']]);
}

portfolio_release($pdo, $oldCfgPath, $newPath);
portfolio_release($pdo, $oldTplPath, $newPath);

echo json_encode(['ok' => true, 'applied_template' => !empty($_POST['apply_template'])] + current_payload($pdo), JSON_UNESCAPED_UNICODE);
