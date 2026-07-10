const ROLE_MAP = { s:'슈퍼', m:'관리자', a:'작가', u:'회원' };
let chart = null;
let currentMonths = 6;

function token() { return localStorage.getItem('pmok_auth_token'); }

async function init() {
    const user = authGetUser();
    if (!user || user.role !== 's') { location.href = '/'; return; }
    document.getElementById('statsPage').style.display = '';
    await Promise.all([loadStats(6), loadExportLogs()]);
}

async function loadStats(months) {
    currentMonths = months;
    document.querySelectorAll('.st-month-btn').forEach(b => {
        b.classList.toggle('active', +b.dataset.m === months);
    });

    const res  = await fetch('/src/api/admin/stats.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() },
        body: JSON.stringify({ months }),
    });
    const data = await res.json();
    if (!res.ok) return;

    renderSummary(data.summary);
    document.getElementById('sumShared').textContent = (+data.sharedCount || 0).toLocaleString();
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

async function loadExportLogs() {
    const res  = await fetch('/src/api/admin/export_logs.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() },
        body: JSON.stringify({ limit: 100 }),
    });
    if (!res.ok) return;
    const data = await res.json();
    renderExportSummary(data.summary || []);
    renderExportLogs(data.logs || []);
}

function renderExportSummary(summary) {
    const el = document.getElementById('exportSummaryBadges');
    if (!el) return;
    const totals = {};
    summary.forEach(r => {
        const key = r.engine + ' ' + r.format.toUpperCase();
        totals[key] = (totals[key] || 0) + (+r.cnt);
    });
    el.innerHTML = Object.entries(totals).map(([k, v]) =>
        `<span style="font-size:11px;padding:2px 8px;border-radius:10px;background:var(--accent-bg);color:var(--text-2);">${esc(k)} <strong>${v}</strong></span>`
    ).join('');
}

function renderExportLogs(logs) {
    document.getElementById('exportLogsTbody').innerHTML = logs.map(r => `
        <tr>
            <td style="font-size:11px;color:var(--text-3);white-space:nowrap;">${r.created_at.slice(0,16)}</td>
            <td style="font-size:12px;">${esc(r.email || '—')}</td>
            <td><span style="font-size:11px;padding:1px 6px;border-radius:8px;background:var(--accent-bg);color:var(--text-2);">${esc(r.engine)}</span></td>
            <td><span style="font-size:11px;font-weight:600;color:${r.format==='pdf'?'#e05218':'#3a8c82'};">${r.format.toUpperCase()}</span></td>
            <td style="font-size:12px;color:var(--text-2);">${esc(r.drawing_name || '—')}</td>
            <td style="font-size:11px;color:var(--text-3);font-family:monospace;">${esc(r.version || '—')}</td>
        </tr>
    `).join('') || '<tr><td colspan="6" style="padding:20px;text-align:center;color:var(--text-3);">기록 없음</td></tr>';
}

function esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

document.querySelectorAll('.st-month-btn').forEach(b => {
    b.addEventListener('click', () => loadStats(+b.dataset.m));
});

document.addEventListener('DOMContentLoaded', init);
window.addEventListener('pmokAuthChanged', init);
