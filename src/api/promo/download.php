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
    // 관리자 '포트폴리오 PDF' 페이지에서 올린 파일은 홍보 캠페인·템플릿에 안 걸려 있을 수 있어, 거기 기록된 파일명을 쓴다
    if (!$found) {
        $cfg = db()->query("SELECT value FROM site_config WHERE key_name = 'portfolio_pdf'")->fetchColumn();
        $cj = $cfg ? json_decode((string)$cfg, true) : null;
        if ($cj && ($cj['path'] ?? '') === $path && !empty($cj['name'])) $name = $cj['name'];
    }
} catch (Throwable $e) {}

$ascii = preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);
if ($ascii === '' || $ascii === '.pdf') $ascii = 'portfolio.pdf';

// ── 세 가지 응답 ──────────────────────────────────────────────────────────────
//   ?f=…            안내 화면: 다운로드가 자동으로 시작되고, 안 되면 큰 버튼을 누른다 (메일 속 링크는 이 주소)
//   ?f=…&dl=1       파일로 내려받기 (attachment)
//   ?f=…&view=1     브라우저 안에서 바로 열기 (inline)
// 메일 링크가 곧바로 attachment를 돌려주면, 크롬 등은 화면 없이 조용히 받아 새 탭이 빈 화면으로 남고, 메일 앱 안의 간이
// 브라우저는 아무 반응이 없다 (2026-11-09 "다운로드 안됨·빈 화면" 신고). 그래서 먼저 눈에 보이는 안내 화면을 보여준다.
if (empty($_GET['dl']) && empty($_GET['view'])) {
    $h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    $base = '?f=' . rawurlencode($f);
    $dlUrl = $base . '&dl=1';
    $viewUrl = $base . '&view=1';
    $mb = number_format(filesize($file) / 1048576, 1);
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-store');
    header('X-Robots-Tag: noindex, nofollow');
    ?><!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>포트폴리오 다운로드 | 워크그룹평목</title>
<style>
    body { margin:0; background:#F6F6F6; font-family:'Apple SD Gothic Neo','Malgun Gothic',sans-serif; color:#111; }
    main { max-width:440px; margin:0 auto; padding:56px 24px; text-align:center; }
    .brand { font-size:13px; letter-spacing:1px; font-weight:800; }
    h1 { margin:28px 0 8px; font-size:24px; line-height:32px; }
    .file { margin:0 0 20px; font-size:14px; color:#666; word-break:break-all; }
    .msg { margin:0 0 28px; font-size:15px; line-height:24px; color:#333; }
    .btn { display:block; padding:18px 20px; background:#000; color:#fff; font-size:17px; font-weight:700; text-decoration:none; }
    .sub { display:inline-block; margin-top:18px; font-size:14px; color:#111; }
    .hint { margin-top:36px; padding:14px 16px; background:#fff; border:1px solid #E2E2E2; font-size:13px; line-height:21px; color:#555; text-align:left; }
</style>
</head>
<body>
<main>
    <div class="brand">WORKGROUP PYEONGMOK</div>
    <h1>포트폴리오 PDF</h1>
    <p class="file"><?= $h($name) ?> · <?= $mb ?>MB</p>
    <p class="msg">다운로드가 곧 시작됩니다.<br>시작되지 않으면 아래 버튼을 눌러 주세요.</p>
    <a class="btn" href="<?= $h($dlUrl) ?>" download>PDF 다운로드</a>
    <a class="sub" href="<?= $h($viewUrl) ?>">브라우저에서 바로 보기</a>
    <div class="hint">
        메일 앱 안에서 이 화면을 열었는데 받아지지 않으면, 오른쪽 위 메뉴(⋮ 또는 공유 아이콘)에서
        <b>"브라우저로 열기"</b>를 선택한 뒤 다시 눌러 주세요.
    </div>
</main>
<script>
    // 버튼을 누르기 전에도 곧바로 받기 시작한다 (이 화면은 그대로 남는다)
    setTimeout(function () { window.location.href = <?= json_encode($dlUrl, JSON_UNESCAPED_SLASHES) ?>; }, 700);
</script>
</body>
</html>
<?php
    exit;
}

$disposition = !empty($_GET['dl']) ? 'attachment' : 'inline';
header('Content-Type: application/pdf');
header('Content-Disposition: ' . $disposition . '; filename="' . $ascii . '"; filename*=UTF-8\'\'' . rawurlencode($name));
header('Content-Length: ' . filesize($file));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=3600');
readfile($file);
