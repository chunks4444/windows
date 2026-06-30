<?php header('Content-Type: text/html; charset=UTF-8'); ?>
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
body { background: #f4f6f5; }
.as-wrap { max-width: 900px; margin: 0 auto; padding: 32px 20px 60px; }
.as-breadcrumb { font-size: 12px; color: #999; margin-bottom: 20px; }
.as-breadcrumb a { color: #666; text-decoration: none; }
.as-breadcrumb a:hover { color: #2A7B70; }
.as-header { display: flex; align-items: center; gap: 12px; margin-bottom: 28px; }
.as-header-icon { width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg,#1a6b62,#2A7B70); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 20px; flex-shrink: 0; }
.as-title { font-size: 22px; font-weight: 700; color: #111; margin: 0; }
.as-subtitle { font-size: 13px; color: #888; margin: 2px 0 0; }

.as-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
.as-card { background: #fff; border-radius: 14px; border: 1px solid #e8ece9; overflow: hidden; }
.as-card-head { padding: 16px 20px 12px; border-bottom: 1px solid #f0f2f0; font-size: 13px; font-weight: 700; color: #333; display: flex; align-items: center; gap: 8px; }
.as-card-head i { color: #2A7B70; }
.as-card-body { padding: 16px 20px; }

.as-engine-bar { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
.as-engine-bar:last-child { margin-bottom: 0; }
.as-engine-name { font-size: 12px; color: #555; width: 68px; flex-shrink: 0; }
.as-bar-wrap { flex: 1; background: #f0f2f0; border-radius: 4px; height: 8px; overflow: hidden; }
.as-bar-fill { height: 8px; border-radius: 4px; background: linear-gradient(90deg,#2A7B70,#4db8ac); transition: width .4s; }
.as-bar-count { font-size: 12px; color: #999; width: 36px; text-align: right; flex-shrink: 0; }

.as-stat-num { font-size: 32px; font-weight: 800; color: #111; line-height: 1; }
.as-stat-label { font-size: 12px; color: #999; margin-top: 4px; }
.as-stat-row { display: flex; gap: 28px; }
.as-stat-item { }

.as-log { background: #fff; border-radius: 14px; border: 1px solid #e8ece9; overflow: hidden; }
.as-log-head { padding: 16px 20px 12px; border-bottom: 1px solid #f0f2f0; font-size: 13px; font-weight: 700; color: #333; display: flex; align-items: center; gap: 8px; }
.as-log table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.as-log th { padding: 10px 16px; background: #f8faf9; color: #999; font-weight: 600; font-size: 11px; text-align: left; border-bottom: 1px solid #f0f2f0; }
.as-log td { padding: 10px 16px; border-bottom: 1px solid #f5f7f5; color: #333; vertical-align: top; }
.as-log tr:last-child td { border-bottom: none; }
.as-engine-tag { display: inline-block; font-size: 10px; font-weight: 700; background: #111; color: #fff; border-radius: 4px; padding: 2px 6px; font-family: monospace; }
.as-msg { max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #555; }
.as-reply { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #888; }
.as-time { color: #bbb; white-space: nowrap; }
</style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div id="authWall" style="display:none;padding:60px 24px;text-align:center;color:#999;">슈퍼 권한이 필요합니다.</div>

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
    if (!res.ok) { document.getElementById('authWall').style.display = ''; return; }
    const d = await res.json();

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
        <tr>
            <td class="as-time">${r.created_at.slice(5,16)}</td>
            <td><span class="as-engine-tag">${r.engine}</span></td>
            <td class="as-msg" title="${r.message}">${r.message}</td>
            <td class="as-reply" title="${r.reply||''}">${r.reply||'-'}</td>
        </tr>
    `).join('');

    document.getElementById('mainPage').style.display = '';
}
window.addEventListener('load', load);
</script>
</body>
</html>
