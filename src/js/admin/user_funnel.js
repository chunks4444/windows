// 어드민 '가입자 활동' — api/admin/user_funnel.php
const UF_API = '/src/api/admin/user_funnel.php';
let ufMonths = 6;
let ufMembers = [];

function ufPct(n, d) { return d > 0 ? Math.round(n / d * 100) : 0; }
function ufDay(dt) { return dt ? String(dt).slice(0, 10) : ''; }
function ufN(v) { return (+v || 0).toLocaleString(); }

async function ufInit() {
    const user = authGetUser();
    if (!user || user.role !== 's') { location.href = '/'; return; }
    document.getElementById('ufPage').style.display = '';
    document.querySelectorAll('.st-month-btn').forEach(b => b.addEventListener('click', () => { ufMonths = +b.dataset.m; ufLoad(); }));
    await ufLoad();
}

async function ufLoad() {
    document.querySelectorAll('.st-month-btn').forEach(b => b.classList.toggle('active', +b.dataset.m === ufMonths));
    const res  = await fetch(`${UF_API}?months=${ufMonths}`, { headers: { 'Authorization': 'Bearer ' + token() } });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) { alert(data.error || '불러오기 실패'); return; }
    document.getElementById('ufNoTable').style.display = data.no_table ? '' : 'none';
    if (data.no_table) return;

    ufRenderCards(data.total || {});
    ufRenderCohorts(data.cohorts || []);
    ufMembers = data.members || [];
    renderMembers();
    document.getElementById('ufNote').textContent = data.pvSince
        ? `스튜디오 열기는 로그인 방문 기록(${ufDay(data.pvSince)}부터 보관)이 근거라, 그 이전 가입자의 스튜디오 열람은 알 수 없습니다.` : '';
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
    const rows = ufMembers.filter(m =>
        (!onlyNoDrawing || !m.first_drawing_at) &&
        (!q || (m.email || '').toLowerCase().includes(q) || (m.name || '').toLowerCase().includes(q)));
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
        </tr>`).join('') || '<tr><td colspan="8" style="padding:20px;text-align:center;color:var(--text-3);">해당하는 회원이 없습니다.</td></tr>';
}

document.addEventListener('DOMContentLoaded', () => {
    if (authGetToken()) ufInit();
    window.addEventListener('pmokAuthChanged', ufInit);
});
