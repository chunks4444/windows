// 어드민 '블로그 검색 관리' (src/admin/blog_search.php) — API: src/api/admin/blog_search.php
// token()/esc()는 admin_sidenav가 불러오는 js/admin/common.js 것을 쓴다(재선언 금지)
const BSR_API = '/src/api/admin/blog_search.php';
function bsrHeaders() { return { 'Authorization': 'Bearer ' + token(), 'Content-Type': 'application/json' }; }

async function bsrLoad() {
    const res = await fetch(BSR_API, { headers: bsrHeaders() });
    const data = await res.json();
    if (data.error) {
        document.getElementById('bsrAliasBody').innerHTML = `<tr><td colspan="2" class="bsr-empty">${esc(data.error)}</td></tr>`;
        return;
    }
    bsrRenderAliases(data.aliases || []);
    bsrRenderZero(data.zero || []);
    document.getElementById('bsrRecent').textContent = `최근 30일 검색 ${Number(data.recent_count || 0).toLocaleString()}회.`;
}

function bsrRenderAliases(rows) {
    const groups = {};
    rows.forEach(r => { (groups[r.canonical] ||= []).push(r); });
    const keys = Object.keys(groups);
    document.getElementById('bsrAliasBody').innerHTML = keys.length ? keys.map(c => `
        <tr class="bsr-group">
            <td>${esc(c)}</td>
            <td>${groups[c].map(r => `<span class="bsr-chip">${esc(r.alias)}<button type="button" title="삭제" data-alias="${esc(r.alias)}" onclick="bsrDeleteAlias(${r.id}, this.dataset.alias)"><i class="bi bi-x"></i></button></span>`).join('')}</td>
        </tr>`).join('') : '<tr><td colspan="2" class="bsr-empty">등록된 이표기가 없습니다.</td></tr>';
}

function bsrRenderZero(rows) {
    document.getElementById('bsrZeroBody').innerHTML = rows.length ? rows.map(r => `
        <tr>
            <td>${esc(r.query)}</td>
            <td class="bsr-num">${Number(r.cnt).toLocaleString()}</td>
            <td>${esc(String(r.last_at).slice(0, 16))}</td>
            <td><button class="bsr-btn bsr-btn-sm" data-q="${esc(r.query)}" onclick="bsrUseAsAlias(this.dataset.q)">이표기로 넣기</button></td>
        </tr>`).join('') : '<tr><td colspan="4" class="bsr-empty">아직 결과 0건 검색어가 없습니다.</td></tr>';
}

function bsrUseAsAlias(q) {
    document.getElementById('bsrAlias').value = q;
    const c = document.getElementById('bsrCanonical');
    c.value = '';
    c.focus();
    c.scrollIntoView({ block: 'center', behavior: 'smooth' });
}

async function bsrAddAlias() {
    const alias = document.getElementById('bsrAlias').value.trim();
    const canonical = document.getElementById('bsrCanonical').value.trim();
    const st = document.getElementById('bsrAddStatus');
    const data = await (await fetch(BSR_API, { method: 'POST', headers: bsrHeaders(),
        body: JSON.stringify({ action: 'add_alias', alias, canonical }) })).json();
    if (data.ok) {
        document.getElementById('bsrAlias').value = '';
        document.getElementById('bsrCanonical').value = '';
        st.className = 'bsr-status ok'; st.textContent = '추가됨';
        setTimeout(() => st.textContent = '', 2000);
        bsrLoad();
    } else {
        st.className = 'bsr-status err'; st.textContent = data.error || '오류';
    }
}

async function bsrDeleteAlias(id, alias) {
    if (!confirm(`'${alias}'을(를) 이표기 묶음에서 뺄까요?`)) return;
    await fetch(BSR_API, { method: 'POST', headers: bsrHeaders(), body: JSON.stringify({ action: 'delete_alias', id }) });
    bsrLoad();
}

document.addEventListener('DOMContentLoaded', () => {
    const user = JSON.parse(localStorage.getItem('pmok_auth_user') || 'null');
    if (!user || user.role !== 's') { document.getElementById('bsrAuthWall').style.display = ''; return; }
    document.getElementById('bsrPage').style.display = '';
    ['bsrAlias', 'bsrCanonical'].forEach(id => document.getElementById(id).addEventListener('keydown', e => { if (e.key === 'Enter') bsrAddAlias(); }));
    bsrLoad();
});
