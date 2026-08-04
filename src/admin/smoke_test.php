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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../lib/meta.php'; meta_tags(); ?>
    <?php css_tag('/src/css/dashboard.css'); ?>
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>
<style>
.st-wrap { max-width: 760px; margin: 0 auto; padding: 32px 20px 60px; }
.st-breadcrumb { font-size: 12px; color: var(--text); margin-bottom: 20px; }
.st-breadcrumb a { color: var(--text); text-decoration: none; }
.st-breadcrumb a:hover { color: var(--accent); }
.st-header { display: flex; align-items: center; gap: 12px; margin-bottom: 8px; }
.st-header-icon { width: 44px; height: 44px; border-radius: 12px; background: var(--accent); display: flex; align-items: center; justify-content: center; color: var(--bg); font-size: 20px; flex-shrink: 0; }
.st-title { font-size: 22px; font-weight: 700; color: var(--text); margin: 0; }
.st-subtitle { font-size: 13px; color: var(--text); margin: 2px 0 0; }
.st-run-row { display: flex; align-items: center; gap: 12px; margin: 24px 0; }
.st-run-btn {
    height: 44px; padding: 0 22px; border-radius: 10px; border: none; background: var(--accent); color: var(--bg);
    font-size: 14px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px;
}
.st-run-btn:disabled { opacity: .6; cursor: not-allowed; }
.st-meta { font-size: 12px; color: var(--text); opacity: .7; }
.st-summary { font-size: 15px; font-weight: 700; margin-bottom: 16px; }
.st-summary.st-ok  { color: var(--accent); }
.st-summary.st-bad { color: var(--danger); }
.st-list { display: flex; flex-direction: column; gap: 6px; }
.st-row {
    display: flex; align-items: center; gap: 10px; padding: 10px 14px;
    border: 1px solid var(--border); border-radius: 8px; font-size: 13px;
}
.st-row-icon { flex-shrink: 0; font-size: 15px; }
.st-row.st-pass .st-row-icon { color: var(--accent); }
.st-row.st-fail { border-color: var(--danger); background: var(--danger-tint); }
.st-row.st-fail .st-row-icon { color: var(--danger); }
.st-row-name { flex: 1; color: var(--text); font-weight: 600; }
.st-row-detail { color: var(--text); opacity: .65; font-size: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 260px; }
.st-row-ms { color: var(--text); opacity: .5; font-size: 11px; flex-shrink: 0; width: 50px; text-align: right; }
</style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>
<?php include __DIR__ . '/../components/admin_sidenav.php'; ?>

<div id="authWall" style="display:none;padding:60px 24px;text-align:center;color:var(--text);">슈퍼 권한이 필요합니다.</div>

<div class="st-wrap" id="mainPage" style="display:none;">
    <div class="st-breadcrumb"><a href="/src/admin/">어드민</a> / 스모크 테스트</div>

    <div class="st-header">
        <div class="st-header-icon"><i class="bi bi-activity"></i></div>
        <div>
            <div class="st-title">배포 스모크 테스트</div>
            <div class="st-subtitle">git pull 직후 핵심 기능이 살아있는지 클릭 한 번으로 확인</div>
        </div>
    </div>

    <div class="st-run-row">
        <button class="st-run-btn" id="runBtn" onclick="runSmokeTest()">
            <i class="bi bi-play-fill"></i> <span id="runBtnText">테스트 실행</span>
        </button>
        <span class="st-meta" id="runMeta"></span>
    </div>

    <div id="reportBox"></div>
</div>

<script>
function _h() { return { 'Authorization': 'Bearer ' + localStorage.getItem('pmok_auth_token') }; }

async function runSmokeTest() {
    const btn = document.getElementById('runBtn');
    const btnText = document.getElementById('runBtnText');
    const reportBox = document.getElementById('reportBox');
    btn.disabled = true; btnText.textContent = '실행 중…';
    reportBox.innerHTML = '';

    try {
        const res = await fetch('/src/api/admin/smoke_test.php', { headers: _h() });
        const d = await res.json();
        if (!res.ok) { reportBox.innerHTML = `<div class="st-summary st-bad">오류: ${d.error || '실행 실패'}</div>`; return; }

        document.getElementById('runMeta').textContent = `${d.ran_at} · ${d.base_url}`;

        const summaryClass = d.all_pass ? 'st-ok' : 'st-bad';
        const summaryIcon  = d.all_pass ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
        let html = `<div class="st-summary ${summaryClass}"><i class="bi ${summaryIcon} me-1"></i>${d.pass} / ${d.total} 통과${d.fail ? ` — ${d.fail}건 실패` : ''}</div>`;

        html += '<div class="st-list">' + d.checks.map(c => `
            <div class="st-row ${c.pass ? 'st-pass' : 'st-fail'}">
                <i class="bi ${c.pass ? 'bi-check-circle-fill' : 'bi-x-circle-fill'} st-row-icon"></i>
                <span class="st-row-name">${esc(c.name)}</span>
                <span class="st-row-detail" title="${esc(c.detail)}">${esc(c.detail)}</span>
                <span class="st-row-ms">${c.ms}ms</span>
            </div>
        `).join('') + '</div>';

        reportBox.innerHTML = html;
    } catch (err) {
        reportBox.innerHTML = `<div class="st-summary st-bad">요청 실패: ${esc(err.message)}</div>`;
    } finally {
        btn.disabled = false; btnText.textContent = '다시 실행';
    }
}

function esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

document.addEventListener('DOMContentLoaded', () => {
    const user = JSON.parse(localStorage.getItem('pmok_auth_user') || 'null');
    if (!user || user.role !== 's') { document.getElementById('authWall').style.display = ''; return; }
    document.getElementById('mainPage').style.display = '';
});
</script>
</body>
</html>
