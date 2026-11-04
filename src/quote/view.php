<?php
// 견적서 보기·검토·발신·인쇄 (슈퍼 관리자 전용) — 어드민 '견적서' 메뉴에서 연다(발행 직후 자동으로 이 화면).
// 발행은 저장만 하고, 메일은 여기서 관리자가 검토한 뒤 '고객에게 발신'을 눌러야 나간다. 인쇄로 PDF 저장.
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/admin_guard.php';
require_admin_role('s');
require_once __DIR__ . '/../lib/quote.php';

try { quote_ensure_table(); } catch (Throwable $e) { error_log('quote table ensure failed: ' . $e->getMessage()); }
$quote = quote_by_id((int) ($_GET['id'] ?? 0));
if (!$quote) { http_response_code(404); echo '견적서를 찾을 수 없습니다.'; exit; }
// ?pdf=1 — 발신 메일에 첨부되는 것과 같은 PDF를 내려받기
if (!empty($_GET['pdf'])) {
    $pdf = quote_pdf($quote);
    $fn  = quote_pdf_filename($quote);
    header('Content-Type: application/pdf');
    header("Content-Disposition: inline; filename=\"quote-{$quote['quote_no']}.pdf\"; filename*=UTF-8''" . rawurlencode($fn));
    header('Content-Length: ' . strlen($pdf));
    echo $pdf;
    exit;
}
$superseded = (int) $quote['revision'] < (int) $quote['latest_revision'];
$sendTo = (string) (json_decode($quote['customer_json'], true)['email'] ?? '');
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
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
    .qv-send { max-width: 760px; margin: 16px auto 0; padding: 12px 14px; background: #fff; border: 1px solid #C9C6BF; border-radius: 6px; font-size: 13px; display: flex; flex-wrap: wrap; gap: 8px 12px; align-items: center; }
    .qv-send .st { flex: 1; min-width: 200px; }
    .qv-send .st b { color: #23262A; }
    .qv-send button { height: 34px; padding: 0 16px; border-radius: 6px; border: none; background: #23262A; color: #fff; font-size: 13px; font-weight: 600; cursor: pointer; }
    .qv-send button:disabled { opacity: .5; cursor: default; }
    .qv-send .intro { width: 100%; display: flex; flex-direction: column; gap: 4px; font-size: 12px; font-weight: 600; }
    .qv-send .intro small { font-weight: 400; color: #6B6A66; }
    .qv-send .intro textarea { width: 100%; box-sizing: border-box; border: 1px solid #C9C6BF; border-radius: 6px; padding: 8px; font: inherit; font-size: 13px; font-weight: 400; line-height: 1.6; resize: vertical; }
    .qv-send .msg { width: 100%; font-size: 12px; }
    .qv-send .msg:empty { display: none; }
    .qv-send .msg.err { color: #8A2A2A; }
    .qv-send .msg.ok { color: #2F6B3A; }
    .qv-warn { max-width: 760px; margin: 16px auto 0; padding: 10px 14px; background: #FBEAEA; color: #8A2A2A; border-radius: 6px; font-size: 13px; }
    .qv-page { max-width: 760px; margin: 16px auto 40px; background: #fff; padding: 40px 44px; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
    @media (max-width: 640px) { .qv-page { padding: 20px 16px; margin: 0; } }
    @page { size: A4; margin: 14mm; }
    @media print {
        body { background: #fff; }
        .qv-bar, .qv-warn, .qv-send { display: none; }
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
    <a href="?id=<?= (int) $quote['id'] ?>&amp;pdf=1" target="_blank" rel="noopener">PDF 받기 (메일 첨부본)</a>
    <button class="primary" onclick="window.print()">인쇄</button>
</div>
<?php if ($superseded): ?>
<div class="qv-warn">이 견적서(<?= (int) $quote['revision'] ?>차)는 이후 <?= (int) $quote['latest_revision'] ?>차로 재발행되어 효력이 없습니다. 인쇄하면 이 안내는 빠집니다.</div>
<?php else: ?>
<div class="qv-send">
    <div class="st" id="qvSendStatus">
        <?php if ($quote['emailed_at']): ?>
        <b>발신됨</b> · <?= $h(substr($quote['emailed_at'], 0, 16)) ?> → <?= $h($quote['emailed_to']) ?>
        <?php else: ?>
        <b>아직 고객에게 보내지 않았습니다.</b> 아래 내용을 검토한 뒤 발신하세요. 받는 주소: <?= $sendTo !== '' ? $h($sendTo) : '<span style="color:#8A2A2A">(수신자 이메일 없음 — 이메일을 넣어 재발행하세요)</span>' ?>
        <?php endif; ?>
    </div>
    <a href="/src/admin/quotes.php?<?= $quote['order_id'] ? 'order_id=' . (int) $quote['order_id'] : 'base_no=' . rawurlencode($quote['base_no']) ?>" style="font-size:13px;color:#23262A;">고칠 곳이 있으면 재발행</a>
    <button id="qvSendBtn" onclick="sendQuote()" <?= $sendTo === '' ? 'disabled' : '' ?>><?= $quote['emailed_at'] ? '다시 발신' : '고객에게 발신' ?></button>
    <label class="intro">
        <span>메일 인사말 <small>— 메일 맨 위, 견적서 위에 들어가는 문구. 견본이니 고쳐서 보내세요 (비우면 견본 그대로)</small></span>
        <textarea id="qvIntro" rows="4" maxlength="2000"><?= $h(QUOTE_MAIL_INTRO_DEFAULT) ?></textarea>
    </label>
    <div class="msg" id="qvSendMsg"></div>
</div>
<?php endif; ?>
<div class="qv-page">
    <?php include __DIR__ . '/../components/quote_document.php'; ?>
</div>
<?php if (!$superseded): ?>
<script>
async function sendQuote() {
    const to = <?= json_encode($sendTo, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const again = <?= $quote['emailed_at'] ? 'true' : 'false' ?>;
    if (!confirm((again ? '이미 보낸 견적서입니다. 다시 ' : '') + '견적서 <?= $h($quote['quote_no']) ?> 를\n' + to + '\n로 보낼까요?')) return;
    const btn = document.getElementById('qvSendBtn'), msg = document.getElementById('qvSendMsg');
    btn.disabled = true; btn.textContent = '보내는 중…'; msg.className = 'msg'; msg.textContent = '';
    try {
        const tk = (() => { try { return localStorage.getItem('pmok_auth_token'); } catch (e) { return null; } })();
        const res = await fetch('/src/api/admin/quotes.php', {
            method: 'POST', credentials: 'same-origin',
            headers: Object.assign({ 'Content-Type': 'application/json' }, tk ? { Authorization: 'Bearer ' + tk } : {}),
            body: JSON.stringify({ action: 'send', quote_id: <?= (int) $quote['id'] ?>, intro: document.getElementById('qvIntro').value }),
        });
        const data = await res.json().catch(() => ({ error: '서버 응답을 읽지 못했습니다.' }));
        if (!res.ok) throw new Error(data.error || '발신에 실패했습니다.');
        const now = new Date(), p = n => String(n).padStart(2, '0');
        const st = document.getElementById('qvSendStatus');
        st.innerHTML = '<b>발신됨</b> · ';
        st.appendChild(document.createTextNode(now.getFullYear() + '-' + p(now.getMonth() + 1) + '-' + p(now.getDate()) + ' ' + p(now.getHours()) + ':' + p(now.getMinutes()) + ' → ' + to));
        msg.className = 'msg ok'; msg.textContent = '보냈습니다(PDF 첨부). 관리자 계정들에게 숨은참조 사본이 갑니다.';
        btn.textContent = '다시 발신';
    } catch (err) {
        msg.className = 'msg err'; msg.textContent = err.message;
        btn.textContent = again ? '다시 발신' : '고객에게 발신';
    } finally {
        btn.disabled = false;
    }
}
</script>
<?php endif; ?>
</body>
</html>
