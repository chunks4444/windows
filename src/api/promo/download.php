<?php
// 홍보메일 첨부 PDF 다운로드 — 메일 속 "포트폴리오 다운로드" 버튼이 가리키는 공개 주소.
// 저장 파일명(32자리 무작위 16진수 + .pdf)을 아는 사람만 받을 수 있고, 그 외 경로는 열지 않는다.
require_once __DIR__ . '/../../lib/db.php';

$f = (string)($_GET['f'] ?? '');
if (!preg_match('/^[0-9a-f]{32}\.pdf$/', $f)) { http_response_code(404); exit; }

$file = __DIR__ . '/../../../uploads/promo/' . $f;
if (!is_file($file)) { http_response_code(404); header('Content-Type: text/plain; charset=UTF-8'); echo '파일을 찾을 수 없습니다.'; exit; }

// 받는 사람에게 보일 파일명: 이 파일을 첨부로 쓰는 캠페인·템플릿에서 찾는다
$name = 'portfolio.pdf';
try {
    $path = '/uploads/promo/' . $f;
    $stmt = db()->prepare("SELECT attachment_name FROM promo_campaigns WHERE attachment_path = ? AND attachment_name <> '' UNION SELECT attachment_name FROM promo_templates WHERE attachment_path = ? AND attachment_name <> '' LIMIT 1");
    $stmt->execute([$path, $path]);
    $found = $stmt->fetchColumn();
    if ($found) $name = $found;
} catch (Throwable $e) {}

$ascii = preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);
if ($ascii === '' || $ascii === '.pdf') $ascii = 'portfolio.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $ascii . '"; filename*=UTF-8\'\'' . rawurlencode($name));
header('Content-Length: ' . filesize($file));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=3600');
readfile($file);
