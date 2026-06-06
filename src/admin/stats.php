<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/src/css/dashboard.css">
    <link rel="stylesheet" href="/src/css/stats.css">
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="statsAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="statsPage" style="display:none;">
    <div class="db-header">
        <h1 class="db-title">접속 통계</h1>
        <div class="st-month-btns">
            <button class="st-month-btn" data-m="1">1개월</button>
            <button class="st-month-btn" data-m="3">3개월</button>
            <button class="st-month-btn active" data-m="6">6개월</button>
        </div>
    </div>

    <!-- 요약 카드 -->
    <div class="st-cards">
        <div class="st-card">
            <div class="st-card-label">총 PV</div>
            <div class="st-card-value" id="sumPv">—</div>
            <div class="st-card-sub">페이지뷰</div>
        </div>
        <div class="st-card">
            <div class="st-card-label">총 UV</div>
            <div class="st-card-value" id="sumUv">—</div>
            <div class="st-card-sub">유니크 방문자</div>
        </div>
        <div class="st-card">
            <div class="st-card-label">회원 PV</div>
            <div class="st-card-value" id="sumMember">—</div>
            <div class="st-card-sub">로그인 상태 방문</div>
        </div>
        <div class="st-card">
            <div class="st-card-label">모바일</div>
            <div class="st-card-value" id="sumMobile">—</div>
            <div class="st-card-sub">모바일 PV</div>
        </div>
    </div>

    <!-- 일별 차트 -->
    <div class="st-panel">
        <div class="st-panel-head">
            <span class="st-panel-title">일별 PV / UV</span>
        </div>
        <div class="st-panel-body">
            <div class="st-chart-wrap"><canvas id="dailyChart"></canvas></div>
        </div>
    </div>

    <div class="st-grid-2">
        <!-- 인기 페이지 -->
        <div class="st-panel">
            <div class="st-panel-head"><span class="st-panel-title">인기 페이지 TOP 10</span></div>
            <table>
                <thead><tr><th>#</th><th>페이지</th><th>PV</th><th>UV</th></tr></thead>
                <tbody id="topPagesTbody"></tbody>
            </table>
        </div>

        <!-- 회원별 접속 -->
        <div class="st-panel">
            <div class="st-panel-head"><span class="st-panel-title">회원 접속 TOP 20</span></div>
            <table>
                <thead><tr><th>#</th><th>이메일</th><th>권한</th><th>방문</th><th>최근</th><th>IP</th></tr></thead>
                <tbody id="topUsersTbody"></tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
const ROLE_MAP = { s:'슈퍼', m:'관리자', a:'작가', u:'회원' };
let chart = null;
let currentMonths = 6;

function token() { return localStorage.getItem('pmok_auth_token'); }

async function init() {
    const user = authGetUser();
    if (!user || user.role !== 's') { location.href = '/'; return; }
    document.getElementById('statsPage').style.display = '';
    await loadStats(6);
}

async function loadStats(months) {
    currentMonths = months;
    document.querySelectorAll('.st-month-btn').forEach(b => {
        b.classList.toggle('active', +b.dataset.m === months);
    });

    const res  = await fetch('/src/api/admin/stats.php?months=' + months, {
        headers: { 'Authorization': 'Bearer ' + token() }
    });
    const data = await res.json();
    if (!res.ok) return;

    renderSummary(data.summary);
    renderDailyChart(data.daily);
    renderTopPages(data.topPages);
    renderTopUsers(data.topUsers);
}

function renderSummary(s) {
    document.getElementById('sumPv').textContent     = (+s.total_pv).toLocaleString();
    document.getElementById('sumUv').textContent     = (+s.total_uv).toLocaleString();
    document.getElementById('sumMember').textContent = (+s.member_pv).toLocaleString();
    const mobilePct = s.total_pv > 0 ? Math.round(s.mobile_pv / s.total_pv * 100) : 0;
    document.getElementById('sumMobile').textContent = (+s.mobile_pv).toLocaleString();
    document.querySelector('#sumMobile + .st-card-sub').textContent = `전체의 ${mobilePct}%`;
}

function renderDailyChart(daily) {
    const labels = daily.map(r => r.date.slice(5)); // MM-DD
    const pvData = daily.map(r => +r.pv);
    const uvData = daily.map(r => +r.uv);

    if (chart) chart.destroy();
    chart = new Chart(document.getElementById('dailyChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'PV', data: pvData, backgroundColor: 'rgba(58,140,130,0.7)', borderRadius: 3, order: 2 },
                { label: 'UV', data: uvData, type: 'line', borderColor: '#F59E0B', backgroundColor: 'transparent', pointRadius: 2, tension: 0.3, order: 1 },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { labels: { font: { family: 'Noto Sans KR', size: 11 } } } },
            scales: {
                x: { ticks: { font: { size: 10 }, maxTicksLimit: 20 }, grid: { display: false } },
                y: { ticks: { font: { size: 10 } }, beginAtZero: true },
            }
        }
    });
}

function renderTopPages(pages) {
    document.getElementById('topPagesTbody').innerHTML = pages.map((p, i) => `
        <tr>
            <td class="st-rank">${i + 1}</td>
            <td class="st-page">${esc(p.page)}</td>
            <td class="st-num">${(+p.pv).toLocaleString()}</td>
            <td style="color:var(--text-3);">${(+p.uv).toLocaleString()}</td>
        </tr>
    `).join('') || '<tr><td colspan="4" style="padding:20px;text-align:center;color:var(--text-3);">데이터 없음</td></tr>';
}

function renderTopUsers(users) {
    document.getElementById('topUsersTbody').innerHTML = users.map((u, i) => `
        <tr>
            <td class="st-rank">${i + 1}</td>
            <td style="font-size:12px;">${esc(u.email)}</td>
            <td><span class="role-badge" data-role="${esc(u.role)}">${ROLE_MAP[u.role] || u.role}</span></td>
            <td class="st-num">${(+u.visit_count).toLocaleString()}</td>
            <td style="color:var(--text-3);font-size:11px;">${u.last_visit ? u.last_visit.slice(0,10) : '—'}</td>
            <td style="color:var(--text-3);font-size:11px;font-family:monospace;">${u.ips ? esc(u.ips) : '—'}</td>
        </tr>
    `).join('') || '<tr><td colspan="5" style="padding:20px;text-align:center;color:var(--text-3);">데이터 없음</td></tr>';
}

function esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

document.querySelectorAll('.st-month-btn').forEach(b => {
    b.addEventListener('click', () => loadStats(+b.dataset.m));
});

document.addEventListener('DOMContentLoaded', init);
window.addEventListener('pmokAuthChanged', init);
</script>
</body>
</html>
