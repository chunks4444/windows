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
.as-wrap { max-width: 900px; margin: 0 auto; padding: 32px 20px 60px; }
.as-breadcrumb { font-size: 12px; color: var(--text); margin-bottom: 20px; }
.as-breadcrumb a { color: var(--text); text-decoration: none; }
.as-breadcrumb a:hover { color: var(--accent); }
.as-header { display: flex; align-items: center; gap: 12px; margin-bottom: 28px; }
.as-header-icon { width: 44px; height: 44px; border-radius: 12px; background: var(--accent); display: flex; align-items: center; justify-content: center; color: var(--bg); font-size: 20px; flex-shrink: 0; }
.as-title { font-size: 22px; font-weight: 700; color: var(--text); margin: 0; }
.as-subtitle { font-size: 13px; color: var(--text); margin: 2px 0 0; }

.as-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
.as-card { background: var(--bg); border-radius: 14px; border: 1px solid var(--accent-tint); overflow: hidden; }
.as-card-head { padding: 16px 20px 12px; border-bottom: 1px solid var(--bg); font-size: 13px; font-weight: 700; color: var(--text); display: flex; align-items: center; gap: 8px; }
.as-card-head i { color: var(--accent); }
.as-card-body { padding: 16px 20px; }

.as-engine-bar { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
.as-engine-bar:last-child { margin-bottom: 0; }
.as-engine-name { font-size: 12px; color: var(--accent-hover); width: 68px; flex-shrink: 0; }
.as-bar-wrap { flex: 1; background: var(--bg); border-radius: 4px; height: 8px; overflow: hidden; }
.as-bar-fill { height: 8px; border-radius: 4px; background: var(--accent); transition: width .4s; }
.as-bar-count { font-size: 12px; color: var(--text); width: 36px; text-align: right; flex-shrink: 0; }

.as-stat-num { font-size: 32px; font-weight: 800; color: var(--text); line-height: 1; }
.as-stat-label { font-size: 12px; color: var(--text); margin-top: 4px; }
.as-stat-row { display: flex; gap: 28px; }
.as-stat-item { }

.as-log { background: var(--bg); border-radius: 14px; border: 1px solid var(--accent-tint); overflow: hidden; }
.as-log-head { padding: 16px 20px 12px; border-bottom: 1px solid var(--bg); font-size: 13px; font-weight: 700; color: var(--text); display: flex; align-items: center; gap: 8px; }
.as-log table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.as-log th { padding: 10px 16px; background: var(--bg); color: var(--text); font-weight: 600; font-size: 11px; text-align: left; border-bottom: 1px solid var(--bg); }
.as-log td { padding: 10px 16px; border-bottom: 1px solid var(--bg); color: var(--text); vertical-align: top; }
.as-log tr:last-child td { border-bottom: none; }
.as-engine-tag { display: inline-block; font-size: 10px; font-weight: 700; background: var(--text); color: var(--bg); border-radius: 4px; padding: 2px 6px; font-family: monospace; }
.as-msg { max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--accent-hover); }
.as-reply { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text); }
.as-time { color: var(--border); white-space: nowrap; }
.as-log tr.expanded .as-msg,
.as-log tr.expanded .as-reply { white-space: normal; overflow: visible; text-overflow: unset; max-width: none; }
.as-log tbody tr { cursor: pointer; }
.as-log tbody tr:hover td { background: var(--bg); }
</style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div id="authWall" style="display:none;padding:60px 24px;text-align:center;color: var(--text);">슈퍼 권한이 필요합니다.</div>

<div class="as-wrap" id="mainPage" style="display:none;">
    <div class="as-breadcrumb"><a href="/src/admin/">어드민</a> / <a href="/src/admin/ai_tuning.php">AI 튜닝</a> / 사용 통계</div>

    <div class="as-header">
        <div class="as-header-icon"><i class="bi bi-bar-chart-line"></i></div>
        <div>
            <div class="as-title">AI 설계 도우미 통계</div>
            <div class="as-subtitle">엔진별 사용 현황 및 대화 히스토리</div>
        </div>
    </div>

    <div class="as-grid">
        <div class="as-card">
            <div class="as-card-head"><i class="bi bi-lightning-charge"></i> 전체 요약</div>
            <div class="as-card-body" id="summaryBox">
                <div class="as-stat-row" id="statRow"></div>
            </div>
        </div>
        <div class="as-card">
            <div class="as-card-head"><i class="bi bi-diagram-3"></i> 엔진별 사용 횟수</div>
            <div class="as-card-body" id="engineBox"></div>
        </div>
    </div>

    <div class="as-log">
        <div class="as-log-head"><i class="bi bi-chat-left-text"></i> 최근 대화 50건</div>
        <table>
            <thead><tr>
                <th>시각</th><th>엔진</th><th>입력</th><th>AI 답변</th>
            </tr></thead>
            <tbody id="logBody"></tbody>
        </table>
    </div>
</div>

<script>
function _h() { return { 'Authorization': 'Bearer ' + localStorage.getItem('pmok_auth_token') }; }

async function load() {
    const res = await fetch('/src/api/admin/ai_stats.php', { headers: _h() });
    if (res.status === 403) { document.getElementById('authWall').style.display = ''; return; }
    const d = await res.json();
    if (d.error === 'db_error') {
        document.getElementById('mainPage').innerHTML = `<div style="padding:40px;color:var(--danger);font-size:13px;">DB 오류: ${d.message}<br><br>ai_chat_history 테이블을 생성해주세요 (schema.sql 참고)</div>`;
        document.getElementById('mainPage').style.display = '';
        return;
    }

    // 요약
    document.getElementById('statRow').innerHTML = `
        <div class="as-stat-item"><div class="as-stat-num">${d.total}</div><div class="as-stat-label">전체 대화</div></div>
        <div class="as-stat-item"><div class="as-stat-num">${d.today}</div><div class="as-stat-label">오늘</div></div>
        <div class="as-stat-item"><div class="as-stat-num">${d.users}</div><div class="as-stat-label">로그인 사용자</div></div>
    `;

    // 엔진별 바
    const max = Math.max(...d.engines.map(e => e.cnt), 1);
    document.getElementById('engineBox').innerHTML = d.engines.map(e => `
        <div class="as-engine-bar">
            <span class="as-engine-name">${e.engine}</span>
            <div class="as-bar-wrap"><div class="as-bar-fill" style="width:${Math.round(e.cnt/max*100)}%"></div></div>
            <span class="as-bar-count">${e.cnt}</span>
        </div>
    `).join('');

    // 로그
    document.getElementById('logBody').innerHTML = d.logs.map(r => `
        <tr onclick="this.classList.toggle('expanded')">
            <td class="as-time">${r.created_at.slice(5,16)}</td>
            <td><span class="as-engine-tag">${r.engine}</span></td>
            <td class="as-msg">${r.message}</td>
            <td class="as-reply">${r.reply||'-'}</td>
        </tr>
    `).join('');

    document.getElementById('mainPage').style.display = '';
}
window.addEventListener('load', load);
</script>
</body>
</html>
