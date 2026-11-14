const ROLE_MAP = { s:'슈퍼', m:'관리자', a:'작가', u:'회원' };
let chart = null;
let currentMonths = 6;
let anonPage = 1;
let topPagesPage = 1;
let topUsersPage = 1;

// '이 브라우저는 방문 기록에서 제외' — pm_nolog 쿠키. 서버(lib/logger.php pm_is_excluded)가 이 쿠키를 보면 로그 파일·접속 통계·JS 비콘 모두 건너뛴다.
// 사이트가 pyeongmok.com과 studio.pyeongmok.com 두 주소로 열리므로 상위 도메인에 심어 둘 다 적용한다.
function hasNoLogCookie() { return /(?:^|;\s*)pm_nolog=1(?:;|$)/.test(document.cookie); }
function toggleNoLog(on) {
    const dom = /(^|\.)pyeongmok\.com$/.test(location.hostname) ? '; domain=.pyeongmok.com' : '';
    const secure = location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = `pm_nolog=${on ? '1' : ''}; max-age=${on ? 31536000 : 0}; path=/${dom}; SameSite=Lax${secure}`;
    // 실제로 저장됐는지 다시 읽어 체크 상태를 맞춘다 (브라우저가 쿠키를 막은 경우를 드러낸다)
    const saved = hasNoLogCookie();
    document.getElementById('chkNoLog').checked = saved;
    if (on && !saved) alert('이 브라우저가 쿠키 저장을 막고 있어 설정하지 못했습니다.');
}

async function init() {
    const user = authGetUser();
    if (!user || user.role !== 's') { location.href = '/'; return; }
    document.getElementById('statsPage').style.display = '';
    document.getElementById('chkNoLog').checked = hasNoLogCookie();
    let savedTab = 'overview';
    try { savedTab = sessionStorage.getItem('statsTab') || 'overview'; } catch (e) {}
    if (document.querySelector('#stMainTabs [data-tab="' + savedTab + '"]')) switchStatsTab(savedTab);
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
        body: JSON.stringify({ months, exclude_admin: document.getElementById('chkExAdmin')?.checked !== false }),
    });
    const data = await res.json();
    if (!res.ok) return;

    renderSummary(data.summary);
    document.getElementById('sumShared').textContent = (+data.sharedCount || 0).toLocaleString();
    renderDailyChart(data.daily, !!data.classified);
    renderClassification(data);
    renderHumans(data.humans || []);
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

// 화면 위의 탭 줄: 접속 통계 / 방문 구분 / 인기 페이지 / 회원접속 / 비로그인 방문 / 내보내기 기록 / 화이트리스트
const STATS_LIST_TABS = ['topPages', 'topUsers', 'anonVisits', 'exportLogs', 'allowlist'];   // 한 상자(#stOldPanel) 안에 들어 있는 탭들
function switchStatsTab(tab) {
    document.querySelectorAll('.st-tab-btn').forEach(b => b.classList.toggle('active', b.dataset.tab === tab));
    document.querySelectorAll('.st-tab-panel').forEach(p => p.style.display = (p.id === 'tab-' + tab) ? '' : 'none');
    document.getElementById('stOldPanel').style.display = STATS_LIST_TABS.includes(tab) ? '' : 'none';
    try { sessionStorage.setItem('statsTab', tab); } catch (e) {}   // 새로고침해도 보던 탭 유지
}

function renderSummary(s) {
    document.getElementById('sumPv').textContent     = (+s.total_pv).toLocaleString();
    document.getElementById('sumUv').textContent     = (+s.total_uv).toLocaleString();
    document.getElementById('sumMember').textContent = (+s.member_pv).toLocaleString();
    const mobilePct = s.total_pv > 0 ? Math.round(s.mobile_pv / s.total_pv * 100) : 0;
    document.getElementById('sumMobile').textContent = (+s.mobile_pv).toLocaleString();
    document.querySelector('#sumMobile + .st-card-sub').textContent = `전체의 ${mobilePct}%`;
}

function renderDailyChart(daily, classified) {
    const labels = daily.map(r => r.date.slice(5)); // MM-DD
    const uvData = daily.map(r => +r.uv);

    // 분류가 시작된 뒤에는 PV를 사람/봇/분류 전으로 쌓아서 보여준다. 분류 이전 방문은 사람/봇을 알 수 없다.
    const datasets = classified ? [
        { label: '사람 PV',  data: daily.map(r => +r.human_pv || 0), backgroundColor: 'rgba(58,140,130,0.8)',  borderRadius: 2, stack: 'pv', order: 2 },
        { label: '봇 PV',    data: daily.map(r => +r.bot_pv   || 0), backgroundColor: 'rgba(150,150,150,0.7)', borderRadius: 2, stack: 'pv', order: 2 },
        { label: '분류 전 PV', data: daily.map(r => +r.unk_pv || 0), backgroundColor: 'rgba(58,140,130,0.25)', borderRadius: 2, stack: 'pv', order: 2 },
        { label: 'UV(전체)', data: uvData, type: 'line', borderColor: '#F59E0B', backgroundColor: 'transparent', pointRadius: 2, tension: 0.3, order: 1 },
        { label: 'JS 실행 확인 UV', data: daily.map(r => +r.js_uv || 0), type: 'line', borderColor: '#2f6b3a', backgroundColor: 'transparent', pointRadius: 2, tension: 0.3, order: 1 },
    ] : [
        { label: 'PV', data: daily.map(r => +r.pv), backgroundColor: 'rgba(58,140,130,0.7)', borderRadius: 3, order: 2 },
        { label: 'UV', data: uvData, type: 'line', borderColor: '#F59E0B', backgroundColor: 'transparent', pointRadius: 2, tension: 0.3, order: 1 },
    ];

    if (chart) chart.destroy();
    chart = new Chart(document.getElementById('dailyChart'), {
        type: 'bar',
        data: { labels, datasets },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { labels: { font: { family: 'Pretendard', size: 11 } } } },
            scales: {
                x: { stacked: classified, ticks: { font: { size: 10 }, maxTicksLimit: 20 }, grid: { display: false } },
                y: { stacked: classified, ticks: { font: { size: 10 } }, beginAtZero: true },
            }
        }
    });
}

function renderSources(sources, n) {
    document.getElementById('sourcesTbody').innerHTML = sources.map(s => `
        <tr><td>${esc(SOURCE_LABEL[s.source] || s.source)}</td>
            <td class="st-num">${n(s.pv)}${+s.prior_pv ? `<span style="color:var(--text-3);font-size:10px;font-weight:400;"> (분류 전 ${n(s.prior_pv)})</span>` : ''}</td>
            <td style="color:var(--text-3);">${n(s.uv)}</td>
            <td style="color:var(--text-3);">${n(s.js_uv)}</td></tr>`).join('')
        || '<tr><td colspan="4" style="padding:16px;text-align:center;color:var(--text-3);">데이터 없음</td></tr>';
}

// ── 사람/봇 구분, 유입 경로, 봇 종류 ─────────────────────────────────
const VISITOR_TYPE_LABEL = { human: '사람', search_bot: '검색엔진 봇', ai_bot: 'AI 봇', preview_bot: '링크 미리보기', other_bot: '기타 봇' };
const SOURCE_LABEL = { google: '구글', naver: '네이버', daum: '다음', bing: '빙', other_search: '기타 검색', sns: 'SNS·커뮤니티', internal: '사이트 내 이동', direct: '직접 접속', other: '기타 사이트' };

function renderClassification(data) {
    const on = !!data.classified;
    const hasSources = (data.sources || []).length > 0;
    ['cardHuman', 'cardBot', 'cardJs'].forEach(id => document.getElementById(id).style.display = on ? '' : 'none');
    document.getElementById('panelClass').style.display = '';
    const note = document.getElementById('classNote');
    note.style.display = on ? '' : 'none';
    const n = v => (+v || 0).toLocaleString();

    // 유입 경로 표는 분류가 시작되지 않았어도(과거 로그에서 채운 값만 있어도) 보여준다
    renderSources(data.sources || [], n);
    if (!on) { document.getElementById('botsTbody').innerHTML = '<tr><td colspan="5" style="padding:16px;text-align:center;color:var(--text-3);">아직 잡힌 봇이 없습니다.</td></tr>'; return; }

    const types = {}; (data.types || []).forEach(t => types[t.type] = t);
    const human = types.human || { pv: 0, uv: 0, js_uv: 0 };
    const botTypes = ['search_bot', 'ai_bot', 'preview_bot', 'other_bot'];
    const botPv = botTypes.reduce((a, k) => a + (+(types[k]?.pv) || 0), 0);
    const unk = +(types.unknown?.pv) || 0;

    document.getElementById('sumHuman').textContent = n(human.pv);
    document.getElementById('sumHumanSub').textContent = `UV ${n(human.uv)} · 위장 크롤러 포함 가능`;
    document.getElementById('sumBot').textContent = n(botPv);
    document.getElementById('sumBotSub').textContent = botTypes.filter(k => types[k]).map(k => `${VISITOR_TYPE_LABEL[k]} ${n(types[k].pv)}`).join(' · ') || '—';
    document.getElementById('sumJs').textContent = n(human.js_uv);

    const since = (data.classSince || '').slice(0, 10);
    note.textContent = `사람/봇 분류는 ${since} 이후 방문부터 적용됩니다.` + (unk ? ` 그 이전 ${n(unk)}건은 "분류 전"으로 남습니다.` : '')
        + ' User-Agent를 밝히는 봇만 구분되며, 사람처럼 위장한 크롤러는 "JS 실행 확인 UV"(브라우저에서 스크립트가 실제로 실행된 방문자)로 걸러서 보세요.';

    document.getElementById('botsTbody').innerHTML = (data.bots || []).map(b => `
        <tr><td style="white-space:nowrap;">${esc(VISITOR_TYPE_LABEL[b.type] || b.type)}</td>
            <td>${esc(b.name)}</td>
            <td class="st-num">${n(b.pv)}</td>
            <td style="color:var(--text-3);">${n(b.uv)}</td>
            <td style="color:var(--text-3);font-size:11px;white-space:nowrap;">${b.last_at ? esc(b.last_at.slice(5, 16)) : '—'}</td></tr>`).join('')
        || '<tr><td colspan="5" style="padding:16px;text-align:center;color:var(--text-3);">아직 잡힌 봇이 없습니다.</td></tr>';
}

function renderHumans(humans) {
    const tb = document.getElementById('humansTbody');
    if (!tb) return;
    tb.innerHTML = humans.map(h => {
        const ip = esc(h.ip);
        let page = h.first_page || '';
        try { page = decodeURIComponent(page); } catch (e) {}
        return `<tr>
            <td style="font-size:11px;color:var(--text-3);white-space:nowrap;">${esc((h.last_at || '').slice(5, 16))}</td>
            <td style="font-size:12px;font-family:monospace;cursor:pointer;text-decoration:underline;" onclick="openIpVisits('${ip}')">${ip}<span class="ip-geo" data-ip="${ip}" style="color:var(--text-3);font-size:10px;margin-left:4px;"></span>${h.user_id ? ' <span style="color:var(--teal);font-size:10px;">회원</span>' : ''}</td>
            <td style="white-space:nowrap;">${+h.mobile ? '모바일' : 'PC'}</td>
            <td style="white-space:nowrap;">${esc(SOURCE_LABEL[h.ref_source] || h.ref_source || '—')}</td>
            <td style="font-size:12px;word-break:break-all;">${esc(page)}</td>
            <td class="st-num">${(+h.pv).toLocaleString()}</td>
        </tr>`;
    }).join('') || '<tr><td colspan="6" style="padding:20px;text-align:center;color:var(--text-3);">아직 확인된 사람 방문자가 없습니다.</td></tr>';
    fillIpGeo(tb);
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
            <td style="color:var(--text-3);font-size:11px;font-family:monospace;cursor:pointer;text-decoration:underline;" onclick="openUserVisits(${u.id},'${esc(u.email)}')">${u.last_ip ? esc(u.last_ip) + '<span class="ip-geo" data-ip="${esc(u.last_ip)}" style="color:var(--text-3);font-size:10px;margin-left:4px;"></span>' : '—'}</td>
        </tr>
    `;
    }).join('') || '<tr><td colspan="6" style="padding:20px;text-align:center;color:var(--text-3);">데이터 없음</td></tr>';
    fillIpGeo(document.getElementById('topUsersTbody'));
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
            <td style="font-size:12px;font-family:monospace;cursor:pointer;text-decoration:underline;" onclick="openIpVisits('${ipEsc}')">${ipEsc}<span class="ip-geo" data-ip="${ipEsc}" style="color:var(--text-3);font-size:10px;margin-left:4px;"></span>${blocked ? ' <span style="color:#e05218;font-size:10px;">차단됨</span>' : ''}</td>
            <td class="st-num">${total.toLocaleString()}</td>
            <td style="font-size:11px;white-space:nowrap;">${device}</td>
            <td style="color:var(--text-3);font-size:11px;">${v.last_visit ? v.last_visit.slice(11,19) : '—'}</td>
            <td>${blocked
                ? `<button class="st-tab-btn" onclick="toggleBlockIp('${ipEsc}', false)">해제</button>`
                : `<button class="st-tab-btn" onclick="toggleBlockIp('${ipEsc}', true)">차단</button>`}</td>
        </tr>
    `;
    }).join('') || '<tr><td colspan="6" style="padding:20px;text-align:center;color:var(--text-3);">데이터 없음</td></tr>';
    fillIpGeo(document.getElementById('anonVisitsTbody'));
}

// IP 옆 빈칸(.ip-geo)에 국가·도시를 채운다. 화면에 보이는 IP만 서버에 물어보고
// (서버가 DB에 없는 IP만 외부 조회 후 저장), 실패하면 그냥 빈칸으로 둔다.
async function fillIpGeo(container) {
    const spans = [...container.querySelectorAll('.ip-geo[data-ip]')];
    const ips = [...new Set(spans.map(s => s.dataset.ip))];
    if (!ips.length) return;
    try {
        const res = await fetch('/src/api/admin/ip_geo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() },
            body: JSON.stringify({ ips }),
        });
        if (!res.ok) return;
        const geo = (await res.json()).geo || {};
        spans.forEach(s => {
            const g = geo[s.dataset.ip];
            if (g) s.textContent = '(' + [g.country, g.city].filter(Boolean).join(' · ') + ')';
        });
    } catch (e) {}
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
                    <span style="font-family:monospace;white-space:nowrap;">${esc(v.ip)}<span class="ip-geo" data-ip="${esc(v.ip)}" style="color:var(--text-3);font-size:10px;margin-left:4px;"></span></span>
                    <span style="color:var(--text-3);">${v.is_mobile ? 'Mobile' : 'PC'}</span>
                    <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${esc(v.page)}</span>
                </div>
            `).join('')}
        </div>
    `).join('');
    fillIpGeo(body);
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

document.querySelectorAll('.st-month-btn').forEach(b => {
    b.addEventListener('click', () => loadStats(+b.dataset.m));
});

document.addEventListener('DOMContentLoaded', init);
window.addEventListener('pmokAuthChanged', init);
