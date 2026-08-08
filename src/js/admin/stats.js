const ROLE_MAP = { s:'슈퍼', m:'관리자', a:'작가', u:'회원' };
let chart = null;
let currentMonths = 6;
let anonPage = 1;
let topPagesPage = 1;
let topUsersPage = 1;

function token() { return localStorage.getItem('pmok_auth_token'); }

async function init() {
    const user = authGetUser();
    if (!user || user.role !== 's') { location.href = '/'; return; }
    document.getElementById('statsPage').style.display = '';
    await Promise.all([loadStats(6), loadExportLogs(), loadAllowlist()]);
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
    loadTopPages(1);
    loadTopUsers(1);
    loadAnonVisits(1);
}

async function loadTopPages(page) {
    if (page < 1) return;
    const res = await fetch(`/src/api/admin/top_pages.php?months=${currentMonths}&page=${page}`, {
        headers: { 'Authorization': 'Bearer ' + token() },
    });
    const data = await res.json();
    if (!res.ok) return;

    topPagesPage = data.page || 1;
    renderTopPages(data.rows || []);

    const pagesCount = data.pages_count || 1;
    document.getElementById('pagesPageLabel').textContent = `${topPagesPage} / ${pagesCount} (총 ${(+data.total || 0).toLocaleString()}건)`;
    document.getElementById('pagesPrevBtn').disabled = topPagesPage <= 1;
    document.getElementById('pagesNextBtn').disabled = topPagesPage >= pagesCount;
}

async function loadTopUsers(page) {
    if (page < 1) return;
    const res = await fetch(`/src/api/admin/top_users.php?months=${currentMonths}&page=${page}`, {
        headers: { 'Authorization': 'Bearer ' + token() },
    });
    const data = await res.json();
    if (!res.ok) return;

    topUsersPage = data.page || 1;
    renderTopUsers(data.users || []);

    const pagesCount = data.pages_count || 1;
    document.getElementById('usersPageLabel').textContent = `${topUsersPage} / ${pagesCount} (총 ${(+data.total || 0).toLocaleString()}건)`;
    document.getElementById('usersPrevBtn').disabled = topUsersPage <= 1;
    document.getElementById('usersNextBtn').disabled = topUsersPage >= pagesCount;
}

async function loadAnonVisits(page) {
    if (page < 1) return;
    const ip = document.getElementById('anonIpFilter')?.value.trim() || '';
    const blockedOnly = document.getElementById('anonBlockedOnly')?.checked ? '1' : '';
    const res = await fetch(`/src/api/admin/anon_visits.php?months=${currentMonths}&page=${page}&ip=${encodeURIComponent(ip)}&blocked_only=${blockedOnly}`, {
        headers: { 'Authorization': 'Bearer ' + token() },
    });
    const data = await res.json();
    if (!res.ok) return;

    anonPage = data.page || 1;
    renderAnonVisits(data.visits || []);

    const pagesCount = data.pages_count || 1;
    document.getElementById('anonPageLabel').textContent = `${anonPage} / ${pagesCount} (총 ${(+data.total || 0).toLocaleString()}건)`;
    document.getElementById('anonPrevBtn').disabled = anonPage <= 1;
    document.getElementById('anonNextBtn').disabled = anonPage >= pagesCount;
}

function switchStatsTab(tab) {
    document.querySelectorAll('.st-tab-btn').forEach(b => b.classList.toggle('active', b.dataset.tab === tab));
    document.querySelectorAll('.st-tab-panel').forEach(p => p.style.display = (p.id === 'tab-' + tab) ? '' : 'none');
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
            <td class="st-rank">${(topPagesPage - 1) * 20 + i + 1}</td>
            <td class="st-page">${esc(p.page)}</td>
            <td class="st-num">${(+p.pv).toLocaleString()}</td>
            <td style="color:var(--text-3);">${(+p.uv).toLocaleString()}</td>
        </tr>
    `).join('') || '<tr><td colspan="4" style="padding:20px;text-align:center;color:var(--text-3);">데이터 없음</td></tr>';
}

function renderTopUsers(users) {
    document.getElementById('topUsersTbody').innerHTML = users.map(u => {
        const mobile = +u.mobile_count || 0;
        const total  = +u.visit_count || 0;
        const desktop = total - mobile;
        const device = !total ? '—' : (mobile && desktop) ? `M:${mobile} P:${desktop}` : (mobile ? `M:${mobile}` : `P:${desktop}`);
        return `
        <tr>
            <td style="color:var(--text-3);font-size:11px;">${u.last_visit ? u.last_visit.slice(0,10) : '—'}</td>
            <td style="font-size:12px;cursor:pointer;text-decoration:underline;" onclick="openUserVisits(${u.id},'${esc(u.email)}')">${esc(u.email)}</td>
            <td class="st-num">${total.toLocaleString()}</td>
            <td style="font-size:11px;white-space:nowrap;">${device}</td>
            <td style="color:var(--text-3);font-size:11px;">${u.last_visit ? u.last_visit.slice(11,19) : '—'}</td>
            <td style="color:var(--text-3);font-size:11px;font-family:monospace;cursor:pointer;text-decoration:underline;" onclick="openUserVisits(${u.id},'${esc(u.email)}')">${u.last_ip ? esc(u.last_ip) : '—'}</td>
        </tr>
    `;
    }).join('') || '<tr><td colspan="6" style="padding:20px;text-align:center;color:var(--text-3);">데이터 없음</td></tr>';
}

function renderAnonVisits(visits) {
    document.getElementById('anonVisitsTbody').innerHTML = visits.map(v => {
        const mobile = +v.mobile_count || 0;
        const total  = +v.visit_count || 0;
        const desktop = total - mobile;
        const device = !total ? '—' : (mobile && desktop) ? `M:${mobile} P:${desktop}` : (mobile ? `M:${mobile}` : `P:${desktop}`);
        const blocked = !!(+v.blocked);
        const ipEsc = esc(v.ip);
        return `
        <tr>
            <td style="color:var(--text-3);font-size:11px;">${v.last_visit ? v.last_visit.slice(0,10) : '—'}</td>
            <td style="font-size:12px;font-family:monospace;cursor:pointer;text-decoration:underline;" onclick="openIpVisits('${ipEsc}')">${ipEsc}${blocked ? ' <span style="color:#e05218;font-size:10px;">차단됨</span>' : ''}</td>
            <td class="st-num">${total.toLocaleString()}</td>
            <td style="font-size:11px;white-space:nowrap;">${device}</td>
            <td style="color:var(--text-3);font-size:11px;">${v.last_visit ? v.last_visit.slice(11,19) : '—'}</td>
            <td>${blocked
                ? `<button class="st-tab-btn" onclick="toggleBlockIp('${ipEsc}', false)">해제</button>`
                : `<button class="st-tab-btn" onclick="toggleBlockIp('${ipEsc}', true)">차단</button>`}</td>
        </tr>
    `;
    }).join('') || '<tr><td colspan="6" style="padding:20px;text-align:center;color:var(--text-3);">데이터 없음</td></tr>';
}

async function toggleBlockIp(ip, block) {
    if (block && !confirm(`${ip} 를 차단할까요? 이 IP는 사이트 전체에 접근할 수 없게 됩니다.`)) return;
    if (!block && !confirm(`${ip} 차단을 해제할까요?`)) return;

    const res = await fetch('/src/api/admin/blocked_ips.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() },
        body: JSON.stringify({ action: block ? 'block' : 'unblock', ip }),
    });
    if (!res.ok) { alert('처리 실패'); return; }
    loadAnonVisits(anonPage);
}

async function loadAllowlist() {
    const res = await fetch('/src/api/admin/allowed_ips.php', {
        headers: { 'Authorization': 'Bearer ' + token() },
    });
    const data = await res.json();
    if (!res.ok) return;
    renderAllowlist(data.allowed || []);
}

function renderAllowlist(rows) {
    document.getElementById('allowlistTbody').innerHTML = rows.map(r => `
        <tr>
            <td style="font-size:12px;font-family:monospace;">${esc(r.ip)}</td>
            <td style="font-size:12px;">${esc(r.note) || '—'}</td>
            <td style="color:var(--text-3);font-size:11px;">${r.added_at ? r.added_at.slice(0,16).replace('T',' ') : '—'}</td>
            <td><button class="st-tab-btn" onclick="removeAllowedIp('${esc(r.ip)}')">해제</button></td>
        </tr>
    `).join('') || '<tr><td colspan="4" style="padding:20px;text-align:center;color:var(--text-3);">등록된 IP 없음</td></tr>';
}

async function addAllowedIp() {
    const ip   = document.getElementById('allowIpInput').value.trim();
    const note = document.getElementById('allowNoteInput').value.trim();
    if (!ip) { alert('IP를 입력하세요.'); return; }

    const res = await fetch('/src/api/admin/allowed_ips.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() },
        body: JSON.stringify({ action: 'add', ip, note }),
    });
    if (!res.ok) { alert('처리 실패'); return; }
    document.getElementById('allowIpInput').value = '';
    document.getElementById('allowNoteInput').value = '';
    loadAllowlist();
}

async function removeAllowedIp(ip) {
    if (!confirm(`${ip} 를 화이트리스트에서 해제할까요?`)) return;
    const res = await fetch('/src/api/admin/allowed_ips.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() },
        body: JSON.stringify({ action: 'remove', ip }),
    });
    if (!res.ok) { alert('처리 실패'); return; }
    loadAllowlist();
}

async function openIpVisits(ip) {
    document.getElementById('userVisitsTitle').textContent = ip + ' (비로그인)';
    document.getElementById('userVisitsBody').innerHTML = '<p style="color:var(--text-3);">불러오는 중…</p>';
    document.getElementById('userVisitsOverlay').style.display = 'flex';

    const res = await fetch(`/src/api/admin/ip_visits.php?ip=${encodeURIComponent(ip)}&months=${currentMonths}`, {
        headers: { 'Authorization': 'Bearer ' + token() },
    });
    const data = await res.json();
    if (!res.ok) {
        document.getElementById('userVisitsBody').innerHTML = '<p style="color:var(--text-3);">불러오기 실패</p>';
        return;
    }
    renderUserVisits(data.visits || []);
}

async function openUserVisits(userId, email) {
    document.getElementById('userVisitsTitle').textContent = email;
    document.getElementById('userVisitsBody').innerHTML = '<p style="color:var(--text-3);">불러오는 중…</p>';
    document.getElementById('userVisitsOverlay').style.display = 'flex';

    const res = await fetch(`/src/api/admin/user_visits.php?user_id=${userId}&months=${currentMonths}`, {
        headers: { 'Authorization': 'Bearer ' + token() },
    });
    const data = await res.json();
    if (!res.ok) {
        document.getElementById('userVisitsBody').innerHTML = '<p style="color:var(--text-3);">불러오기 실패</p>';
        return;
    }
    renderUserVisits(data.visits || []);
}

function renderUserVisits(visits) {
    const body = document.getElementById('userVisitsBody');
    if (!visits.length) {
        body.innerHTML = '<p style="color:var(--text-3);">방문 기록이 없습니다.</p>';
        return;
    }
    // 날짜별로 묶기 (visits는 이미 최신순으로 정렬돼서 옴)
    const groups = [];
    let lastDate = null;
    for (const v of visits) {
        const date = v.visited_at.slice(0, 10);
        if (date !== lastDate) { groups.push({ date, items: [] }); lastDate = date; }
        groups[groups.length - 1].items.push(v);
    }
    body.innerHTML = groups.map(g => `
        <div style="margin-bottom:14px;">
            <div style="font-weight:700;margin-bottom:6px;">${g.date}</div>
            ${g.items.map(v => `
                <div style="display:flex;gap:10px;padding:4px 0;border-bottom:1px solid var(--border);">
                    <span style="color:var(--text-3);white-space:nowrap;">${esc(v.visited_at.slice(0, 10))}</span>
                    <span style="color:var(--text-3);white-space:nowrap;">${v.visited_at.slice(11, 19)}</span>
                    <span style="font-family:monospace;white-space:nowrap;">${esc(v.ip)}</span>
                    <span style="color:var(--text-3);">${v.is_mobile ? 'Mobile' : 'PC'}</span>
                    <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${esc(v.page)}</span>
                </div>
            `).join('')}
        </div>
    `).join('');
}

function closeUserVisits() {
    document.getElementById('userVisitsOverlay').style.display = 'none';
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
