<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/admin_guard.php';
require_admin_role('s');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../lib/meta.php'; ?>
    <?php meta_tags(); ?>
    <?php css_tag('/src/css/dashboard.css'); ?>
    <?php css_tag('/src/css/users.css'); ?>
    <?php css_tag('/src/css/admin/oauth.css'); ?>
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>
<?php include __DIR__ . '/../components/admin_sidenav.php'; ?>

<div class="db-page" id="noticeAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="noticePage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>공지 배너</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-megaphone me-2"></i>공지 배너</h1>
    </div>

    <div style="max-width:600px;">
        <div class="oauth-card">
            <div class="oauth-card-header">
                <div class="oauth-card-logo" style="background:var(--text);color:var(--bg);font-size:14px;"><i class="bi bi-megaphone-fill"></i></div>
                <span class="oauth-card-title">배너 설정</span>
            </div>

            <div class="oauth-field" style="display:flex;align-items:center;gap:12px;">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;font-weight:600;margin:0;">
                    <input type="checkbox" id="noticeVisible" class="form-check-input" style="width:36px;height:20px;margin:0;">
                    배너 노출
                </label>
                <span id="noticeStatusBadge" class="badge" style="font-size:12px;"></span>
            </div>

            <div class="oauth-field">
                <label>공지 문구</label>
                <input type="text" id="noticeText" placeholder="7월 15일까지 접수분만 8월 납품 가능합니다." maxlength="120">
                <div style="font-size:11px;color: var(--text);margin-top:4px;">최대 120자. 모바일에서는 말줄임 처리됩니다.</div>
            </div>

            <div class="oauth-field">
                <label>링크 URL <span style="color: var(--text);font-weight:400;">(선택)</span></label>
                <input type="text" id="noticeLink" placeholder="/guide/order">
                <div style="font-size:11px;color: var(--text);margin-top:4px;">입력하면 문구 클릭 시 해당 페이지로 이동합니다.</div>
            </div>

            <div class="oauth-field">
                <label>배경 색상</label>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <label class="notice-bg-opt" data-val="dark">
                        <input type="radio" name="noticeBg" value="dark" hidden>
                        <span class="notice-bg-swatch" style="background:var(--text);"></span>
                        <span>Dark</span>
                    </label>
                    <label class="notice-bg-opt" data-val="teal">
                        <input type="radio" name="noticeBg" value="teal" hidden>
                        <span class="notice-bg-swatch" style="background:var(--accent);"></span>
                        <span>Teal</span>
                    </label>
                    <label class="notice-bg-opt" data-val="red">
                        <input type="radio" name="noticeBg" value="red" hidden>
                        <span class="notice-bg-swatch" style="background:var(--danger);"></span>
                        <span>Red</span>
                    </label>
                </div>
            </div>

            <div class="oauth-field">
                <label>미리보기</label>
                <div id="noticePreview" style="height:40px;border-radius:6px;display:flex;align-items:center;justify-content:center;padding:0 48px;position:relative;font-size:13px;font-weight:600;">
                    <span id="noticePreviewText"></span>
                    <span style="position:absolute;right:14px;opacity:.6;font-size:14px;">✕</span>
                </div>
            </div>

            <div id="noticeSaveMsg" style="display:none;font-size:13px;color:var(--accent);margin-bottom:12px;font-weight:600;">저장되었습니다.</div>
            <button class="oauth-save" onclick="saveNotice()">저장</button>
        </div>
    </div>
</div>

<style>
.notice-bg-opt {
    display: flex; align-items: center; gap: 6px;
    padding: 7px 14px; border: 1.5px solid var(--bg);
    border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600;
    transition: border-color .15s; user-select: none;
}
.notice-bg-opt:has(input:checked) { border-color: var(--accent); background: var(--bg); }
.notice-bg-swatch { width: 16px; height: 16px; border-radius: 3px; flex-shrink: 0; }
</style>

<script>
const BG_COLORS = { dark: 'var(--text)', teal: 'var(--accent)', red: 'var(--danger)' };
function _h(json) {
    const h = { 'Authorization': 'Bearer ' + localStorage.getItem('pmok_auth_token') };
    if (json) h['Content-Type'] = 'application/json';
    return h;
}

function updateBadge(on) {
    const b = document.getElementById('noticeStatusBadge');
    b.textContent = on ? '노출 중' : '숨김';
    b.className = 'badge ' + (on ? 'bg-success' : 'bg-secondary');
}

function updatePreview() {
    const text = document.getElementById('noticeText').value || '공지 문구를 입력하세요.';
    const bg   = document.querySelector('input[name="noticeBg"]:checked')?.value || 'dark';
    const prev = document.getElementById('noticePreview');
    prev.style.background = BG_COLORS[bg] || 'var(--text)';
    prev.style.color = 'var(--bg)';
    document.getElementById('noticePreviewText').textContent = text;
}

document.getElementById('noticeText').addEventListener('input', updatePreview);
document.querySelectorAll('input[name="noticeBg"]').forEach(r => r.addEventListener('change', updatePreview));
document.getElementById('noticeVisible').addEventListener('change', function() {
    updateBadge(this.checked);
});

async function saveNotice() {
    const body = {
        notice_visible: document.getElementById('noticeVisible').checked ? '1' : '0',
        notice_text:    document.getElementById('noticeText').value.trim(),
        notice_link:    document.getElementById('noticeLink').value.trim(),
        notice_bg:      document.querySelector('input[name="noticeBg"]:checked')?.value || 'dark',
    };
    const res = await fetch('/src/api/admin/notice.php', {
        method: 'POST',
        headers: _h(true),
        body: JSON.stringify(body),
    });
    if (res.ok) {
        const msg = document.getElementById('noticeSaveMsg');
        msg.style.display = '';
        setTimeout(() => msg.style.display = 'none', 2500);
    }
}

async function loadNotice() {
    const res  = await fetch('/src/api/admin/notice.php', {
        headers: { 'Authorization': 'Bearer ' + localStorage.getItem('pmok_auth_token') }
    });
    if (!res.ok) { document.getElementById('noticeAuthWall').style.display = ''; return; }
    const data = await res.json();

    document.getElementById('noticeVisible').checked = data.notice_visible === '1';
    document.getElementById('noticeText').value  = data.notice_text  || '';
    document.getElementById('noticeLink').value  = data.notice_link  || '';

    const bg = data.notice_bg || 'dark';
    const radio = document.querySelector(`input[name="noticeBg"][value="${bg}"]`);
    if (radio) radio.checked = true;

    updateBadge(data.notice_visible === '1');
    updatePreview();
    document.getElementById('noticePage').style.display = '';
}

window.addEventListener('load', loadNotice);
</script>
</body>
</html>
