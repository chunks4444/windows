// 어드민 '가입자 활동' — api/admin/user_funnel.php
const UF_API = '/src/api/admin/user_funnel.php';
let ufMonths = 6;
let ufMembers = [];
let ufOnlineTimer = null;

function ufPct(n, d) { return d > 0 ? Math.round(n / d * 100) : 0; }
function ufDay(dt) { return dt ? String(dt).slice(0, 10) : ''; }
function ufN(v) { return (+v || 0).toLocaleString(); }

async function ufInit() {
    const user = authGetUser();
    if (!user || user.role !== 's') { location.href = '/'; return; }
    document.getElementById('ufPage').style.display = '';
    document.querySelectorAll('.st-month-btn').forEach(b => b.addEventListener('click', () => { ufMonths = +b.dataset.m; ufLoad(); }));
    await ufLoad();
    clearInterval(ufOnlineTimer);
    ufOnlineTimer = setInterval(ufRefreshOnline, 60000);
}

// 현재 접속만 1분마다 가볍게 새로 불러온다 (탭이 가려져 있으면 쉰다)
async function ufRefreshOnline() {
    if (document.hidden) return;
    try {
        const res  = await fetch(`${UF_API}?online_only=1`, { headers: { 'Authorization': 'Bearer ' + token() } });
        const data = await res.json();
        if (res.ok && data.online) ufRenderOnline(data.online);
    } catch (e) {}
}

async function ufLoad() {
    document.querySelectorAll('.st-month-btn').forEach(b => b.classList.toggle('active', +b.dataset.m === ufMonths));
    const res  = await fetch(`${UF_API}?months=${ufMonths}`, { headers: { 'Authorization': 'Bearer ' + token() } });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) { alert(data.error || '불러오기 실패'); return; }
    document.getElementById('ufNoTable').style.display = data.no_table ? '' : 'none';
    if (data.no_table) return;

    ufRenderOnline(data.online || []);
    ufRenderActive(data.active || {}, data.daily || {});
    ufRenderCards(data.total || {});
    ufRenderCohorts(data.cohorts || []);
    ufMembers = data.members || [];
    renderMembers();
    document.getElementById('ufNote').textContent = data.pvSince
        ? `스튜디오 열기는 로그인 방문 기록(${ufDay(data.pvSince)}부터 보관)이 근거라, 그 이전 가입자의 스튜디오 열람은 알 수 없습니다.` : '';
}

const UF_ROLE = { s: '슈퍼 관리자', m: '관리자', a: '작가', u: '회원' };

// 현재 접속 — 최근 10분 안에 페이지를 연 회원(관리자 포함)
function ufRenderOnline(rows) {
    document.getElementById('ufOnlineCount').textContent = `(${rows.length}명)`;
    document.getElementById('ufOnline').innerHTML = rows.map(r => {
        const admin = r.role === 's' || r.role === 'm';
        const mins = +r.minutes_ago || 0;
        const ago = mins < 1 ? '방금' : `${mins}분 전`;
        return `<div style="display:flex;align-items:center;gap:8px;padding:6px 12px;border:1px solid var(--border);border-radius:999px;font-size:12px;${admin ? 'background:rgba(var(--text-rgb),.05);' : ''}">
            <span style="width:8px;height:8px;border-radius:50%;background:${mins < 3 ? '#2f6b3a' : '#b0b0b0'};flex-shrink:0;"></span>
            <span>${esc(r.name || r.email)}</span>
            ${admin ? `<span style="font-size:10px;color:#8a3a22;background:#fbe9e4;padding:1px 6px;border-radius:999px;">${esc(UF_ROLE[r.role])}</span>` : ''}
            <span style="color:var(--text-3);">${esc(ago)}</span>
        </div>`;
    }).join('') || '<span style="font-size:13px;color:var(--text-3);">지금 접속 중인 회원이 없습니다.</span>';
}

// 접속 회원 수(오늘·7일·30일)와 최근 30일 일별 추이
function ufRenderActive(a, daily) {
    document.getElementById('ufActiveCards').innerHTML = [['오늘 접속', a.today], ['최근 7일 접속', a.d7], ['최근 30일 접속', a.d30]].map(([label, n]) => `
        <div class="st-card"><div class="st-card-label">${esc(label)}</div><div class="st-card-value">${ufN(n)}</div><div class="st-card-sub">회원 수(중복 제외)</div></div>`).join('');
    // 최근 30일을 하루도 빠짐없이 (접속 없는 날은 0)
    const days = [], today = new Date();
    for (let i = 29; i >= 0; i--) { const d = new Date(today); d.setDate(today.getDate() - i); days.push(ufLocalDay(d)); }
    const vals = days.map(d => +daily[d] || 0), max = Math.max(1, ...vals);
    document.getElementById('ufDaily').innerHTML = vals.map((v, i) =>
        `<div title="${days[i]} · ${v}명" style="flex:1;min-width:0;height:${Math.max(2, Math.round(v / max * 100))}%;background:${v ? 'rgba(58,140,130,.75)' : 'rgba(150,150,150,.25)'};border-radius:2px;"></div>`).join('');
    document.getElementById('ufDailyAxis').innerHTML = days.map((d, i) =>
        `<div style="flex:1;min-width:0;text-align:center;overflow:hidden;">${(i % 5 === 0 || i === 29) ? d.slice(5) : ''}</div>`).join('');
}

// toISOString은 UTC라 한국 시간 새벽에 하루가 어긋난다 — 브라우저 로컬 날짜로 만든다
function ufLocalDay(d) {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

function ufRenderCards(t) {
    const total = +t.signups || 0;
    const steps = [
        ['가입', total, '일반 회원·작가'],
        ['스튜디오 열기', +t.studio || 0, null],
        ['도면 저장', +t.drawing || 0, null],
        ['내보내기', +t.export || 0, null],
        ['AI 렌더링', +t.render || 0, null],
        ['견적요청', +t.ordered || 0, null],
    ];
    document.getElementById('ufCards').innerHTML = steps.map(([label, n, sub]) => `
        <div class="st-card">
            <div class="st-card-label">${esc(label)}</div>
            <div class="st-card-value">${ufN(n)}</div>
            <div class="st-card-sub">${sub ? esc(sub) : `가입자의 ${ufPct(n, total)}%`}</div>
        </div>`).join('');
}

function ufRenderCohorts(rows) {
    const cell = (n, total) => `<td class="st-num">${ufN(n)} <span style="color:var(--text-3);font-size:11px;font-weight:400;">(${ufPct(+n, total)}%)</span></td>`;
    document.getElementById('ufCohorts').innerHTML = rows.map(r => {
        const total = +r.signups || 0;
        return `<tr><td>${esc(r.ym)}</td><td class="st-num">${ufN(total)}</td>
            ${cell(r.studio, total)}${cell(r.drawing, total)}${cell(r.export, total)}${cell(r.render, total)}${cell(r.ordered, total)}</tr>`;
    }).join('') || '<tr><td colspan="7" style="padding:20px;text-align:center;color:var(--text-3);">가입자가 없습니다.</td></tr>';
}

function renderMembers() {
    const q = (document.getElementById('ufSearch')?.value || '').trim().toLowerCase();
    const onlyNoDrawing = !!document.getElementById('ufNoDrawing')?.checked;
    const sort = document.getElementById('ufSort')?.value || 'created';
    const rows = ufMembers.filter(m =>
        (!onlyNoDrawing || !m.first_drawing_at) &&
        (!q || (m.email || '').toLowerCase().includes(q) || (m.name || '').toLowerCase().includes(q)));
    const seenCmp = (a, b) => String(b.last_seen_at || '').localeCompare(String(a.last_seen_at || ''));
    if (sort === 'seen') rows.sort(seenCmp);
    else if (sort === 'days') rows.sort((a, b) => (+b.days_30d || 0) - (+a.days_30d || 0) || seenCmp(a, b));
    const step = dt => dt ? `<span style="color:#2f6b3a;">${esc(ufDay(dt))}</span>` : '<span style="color:var(--text-3);">—</span>';
    document.getElementById('ufMembers').innerHTML = rows.map(m => `
        <tr${m.withdrawn_at ? ' style="opacity:.5;"' : ''}>
            <td style="font-size:12px;white-space:nowrap;">${esc(ufDay(m.created_at))}</td>
            <td style="font-size:12px;">${esc(m.email)}${m.name ? ` <span style="color:var(--text-3);">${esc(m.name)}</span>` : ''}${m.withdrawn_at ? ' <span style="color:#a02020;">(탈퇴)</span>' : ''}</td>
            <td style="font-size:12px;">${step(m.first_studio_at)}</td>
            <td style="font-size:12px;">${step(m.first_drawing_at)}</td>
            <td style="font-size:12px;">${step(m.first_export_at)}</td>
            <td style="font-size:12px;">${step(m.first_render_at)}</td>
            <td style="font-size:12px;">${step(m.first_order_at)}</td>
            <td style="font-size:12px;color:var(--text-3);white-space:nowrap;">${esc(ufDay(m.last_seen_at)) || '—'}</td>
            <td style="font-size:12px;white-space:nowrap;">${+m.days_30d ? `${ufN(m.days_30d)}일 <span style="color:var(--text-3);">· ${ufN(m.pv_30d)}회</span>` : '<span style="color:var(--text-3);">—</span>'}</td>
        </tr>`).join('') || '<tr><td colspan="9" style="padding:20px;text-align:center;color:var(--text-3);">해당하는 회원이 없습니다.</td></tr>';
}

document.addEventListener('DOMContentLoaded', () => {
    if (authGetToken()) ufInit();
    window.addEventListener('pmokAuthChanged', ufInit);
});
