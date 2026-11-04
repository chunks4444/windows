<?php
// 견적서 보기·인쇄 (슈퍼 관리자 전용) — 어드민 '견적서' 메뉴에서 연다. 브라우저 인쇄로 PDF 저장.
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/admin_guard.php';
require_admin_role('s');
require_once __DIR__ . '/../lib/quote.php';

$quote = quote_by_id((int) ($_GET['id'] ?? 0));
if (!$quote) { http_response_code(404); echo '견적서를 찾을 수 없습니다.'; exit; }
$superseded = (int) $quote['revision'] < (int) $quote['latest_revision'];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>견적서 <?= htmlspecialchars($quote['quote_no']) ?></title>
<style>
    body { margin: 0; background: #E9E6E0; font-family: 'Apple SD Gothic Neo', 'Malgun Gothic', sans-serif; color: #23262A; }
    .qv-bar { position: sticky; top: 0; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; padding: 10px 16px; background: #23262A; color: #fff; font-size: 13px; z-index: 1; }
    .qv-bar a, .qv-bar button { color: #fff; background: transparent; border: 1px solid rgba(255,255,255,.4); border-radius: 6px; padding: 6px 12px; font-size: 13px; cursor: pointer; text-decoration: none; }
    .qv-bar button.primary { background: #fff; color: #23262A; border-color: #fff; font-weight: 600; }
    .qv-bar .sp { flex: 1; }
    .qv-warn { max-width: 760px; margin: 16px auto 0; padding: 10px 14px; background: #FBEAEA; color: #8A2A2A; border-radius: 6px; font-size: 13px; }
    .qv-page { max-width: 760px; margin: 16px auto 40px; background: #fff; padding: 40px 44px; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
    @media (max-width: 640px) { .qv-page { padding: 20px 16px; margin: 0; } }
    @page { size: A4; margin: 14mm; }
    @media print {
        body { background: #fff; }
        .qv-bar, .qv-warn { display: none; }
        .qv-page { box-shadow: none; margin: 0; padding: 0; max-width: none; }
        * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
</head>
<body>
<div class="qv-bar">
    <a href="/src/admin/quotes.php">← 견적서 목록</a>
    <span><?= htmlspecialchars($quote['quote_no']) ?><?= $quote['title'] ? ' · ' . htmlspecialchars($quote['title']) : '' ?></span>
    <span class="sp"></span>
    <button class="primary" onclick="window.print()">인쇄 / PDF 저장</button>
</div>
<?php if ($superseded): ?>
<div class="qv-warn">이 견적서(<?= (int) $quote['revision'] ?>차)는 이후 <?= (int) $quote['latest_revision'] ?>차로 재발행되어 효력이 없습니다. 인쇄하면 이 안내는 빠집니다.</div>
<?php endif; ?>
<div class="qv-page">
    <?php $quoteShowThumb = true; include __DIR__ . '/../components/quote_document.php'; ?>
</div>
</body>
</html>
