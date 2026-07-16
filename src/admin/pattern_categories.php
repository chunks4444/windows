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
    <?php require_once __DIR__ . '/../lib/meta.php'; meta_tags(); ?>
    <?php css_tag('/src/css/dashboard.css'); ?>
    <?php css_tag('/src/css/users.css'); ?>
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>
    <style>
        .pc-tabs { display:flex; gap:0; border-bottom:2px solid var(--border); margin-bottom:24px; }
        .pc-tab  { padding:8px 22px; font-size:13px; font-weight:600; color: var(--text); border:none; background:none; cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-2px; }
        .pc-tab.active { color:var(--accent); border-bottom-color:var(--accent); }

        .pc-table { width:100%; border-collapse:collapse; font-size:13px; max-width:560px; }
        .pc-table th { background:var(--bg); padding:8px 12px; text-align:left; font-weight:600; color: var(--text); border-bottom:2px solid var(--border); }
        .pc-table td { padding:8px 12px; border-bottom:1px solid var(--border); vertical-align:middle; }
        .pc-table tr:hover td { background:var(--bg); }
        .pc-id { font-family:monospace; font-size:11px; color: var(--text); background:var(--bg); padding:2px 6px; border-radius:4px; }
        .pc-name-input { border:1px solid var(--border); border-radius:5px; padding:4px 8px; font-size:13px; width:150px; }
        .pc-code-input { border:1px solid var(--border); border-radius:5px; padding:4px 8px; font-size:13px; width:56px; text-align:center; text-transform:uppercase; font-family:monospace; }
        .pc-sort-input { border:1px solid var(--border); border-radius:5px; padding:4px 6px; font-size:13px; width:52px; text-align:center; }
        .pc-btn { border:none; border-radius:5px; padding:4px 10px; font-size:12px; font-weight:600; cursor:pointer; }
        .pc-btn-save { background:var(--accent); color:var(--bg); } .pc-btn-save:hover { opacity:.85; }
        .pc-btn-del  { background:var(--bg); color:var(--danger); }    .pc-btn-del:hover  { background:var(--danger-tint); }
        .pc-inactive td { opacity:.4; }
        .pc-status { font-size:12px; margin-left:6px; }
        .pc-status.ok { color:var(--accent); } .pc-status.err { color:var(--danger); }
        .pc-add-row { display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin-top:16px; }
        .pc-add-row input { border:1px solid var(--border); border-radius:6px; padding:6px 10px; font-size:13px; }
        .pc-add-btn { background:var(--accent); color:var(--bg); border:none; border-radius:6px; padding:6px 18px; font-size:13px; font-weight:600; cursor:pointer; }

        .pc-dl-filters { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px; align-items:center; }
        .pc-dl-filter-btn { border:1px solid var(--border); background:var(--bg); border-radius:6px; padding:4px 12px; font-size:12px; font-weight:600; cursor:pointer; color: var(--text); }
        .pc-dl-filter-btn.active { background:var(--accent); color:var(--bg); border-color:var(--accent); }
        .pc-dl-table { width:100%; border-collapse:collapse; font-size:13px; }
        .pc-dl-table th { background:var(--bg); padding:7px 10px; text-align:left; font-weight:600; color: var(--text); border-bottom:2px solid var(--border); }
        .pc-dl-table td { padding:7px 10px; border-bottom:1px solid var(--border); vertical-align:middle; }
        .pc-dl-table tr:hover td { background:var(--bg); }
        .pc-dl-cat-select { border:1px solid var(--border); border-radius:5px; padding:3px 7px; font-size:12px; font-weight:600; color:var(--accent); cursor:pointer; }
        .pc-dl-type-badge { font-size:10px; font-weight:700; background:var(--bg); color: var(--text); border-radius:4px; padding:2px 6px; }
        .pc-dl-load-more { text-align:center; padding:12px 0; }
        .pc-dl-load-more button { border:1px solid var(--border); background:var(--bg); border-radius:6px; padding:6px 20px; font-size:13px; cursor:pointer; }
        .pc-dl-search { border:1px solid var(--border); border-radius:6px; padding:6px 10px; font-size:13px; width:260px; margin-bottom:12px; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="pcPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>패턴 카테고리</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-tags me-2"></i>패턴 카테고리</h1>
    </div>

    <div class="pc-tabs">
        <button class="pc-tab active" onclick="switchTab('cats', this)">카테고리 관리</button>
        <button class="pc-tab"       onclick="switchTab('mods', this)">수식어 관리</button>
        <button class="pc-tab"       onclick="switchTab('drawings', this)">도면 분류</button>
    </div>

    <!-- 탭 1: 카테고리 관리 -->
    <div id="tabCats">
        <p style="font-size:13px;color: var(--text);margin:-8px 0 16px;">이름만 수정하면 모든 엔진·도면 목록에 즉시 반영됩니다. 코드는 평목 컬렉션 코드 체계 v1.0의 계열 코드(영문 3자, 예: JEO)로, 컬렉션 아이템 슬러그 생성에 쓰입니다. 비워두면 코드 없는 카테고리로 유지됩니다.</p>
        <div style="overflow-x:auto;">
            <table class="pc-table" id="pcTable">
                <thead><tr><th>ID</th><th>이름</th><th>코드</th><th>정렬</th><th>활성</th><th></th></tr></thead>
                <tbody id="pcBody"></tbody>
            </table>
        </div>
        <div class="pc-add-row">
            <input id="addName" placeholder="새 카테고리 이름" style="width:160px;">
            <input id="addCode" placeholder="코드(3자)" maxlength="3" style="width:80px;text-transform:uppercase;">
            <input id="addSort" type="number" value="0" placeholder="정렬" style="width:64px;">
            <button class="pc-add-btn" onclick="addCategory()">추가</button>
            <span class="pc-status" id="addStatus"></span>
        </div>
    </div>

    <!-- 탭 2: 수식어 관리 -->
    <div id="tabMods" style="display:none;">
        <p style="font-size:13px;color: var(--text);margin:-8px 0 16px;">평목 컬렉션 코드 체계 v1.0의 수식어(계열 안에서 세부 구분, 예: JEO-SE-001)입니다. 컬렉션 아이템 생성 시 이 목록 중에서만 고를 수 있습니다.</p>
        <div style="overflow-x:auto;">
            <table class="pc-table" id="modTable">
                <thead><tr><th>ID</th><th>이름</th><th>코드</th><th>정렬</th><th>활성</th><th></th></tr></thead>
                <tbody id="modBody"></tbody>
            </table>
        </div>
        <div class="pc-add-row">
            <input id="modAddName" placeholder="새 수식어 이름" style="width:160px;">
            <input id="modAddCode" placeholder="코드(2자)" maxlength="2" style="width:80px;text-transform:uppercase;">
            <input id="modAddSort" type="number" value="0" placeholder="정렬" style="width:64px;">
            <button class="pc-add-btn" onclick="addModifier()">추가</button>
            <span class="pc-status" id="modAddStatus"></span>
        </div>
    </div>

    <!-- 탭 3: 도면 분류 -->
    <div id="tabDrawings" style="display:none;">
        <input type="text" class="pc-dl-search" id="dlSearch" placeholder="도면명·사용자 이메일로 검색…" oninput="onDlSearch()">
        <div class="pc-dl-filters" id="dlFilters">
            <button class="pc-dl-filter-btn active" data-cat="" onclick="setFilter(this,'')">전체</button>
            <button class="pc-dl-filter-btn" data-cat="0" onclick="setFilter(this,'0')">미분류</button>
        </div>
        <div style="overflow-x:auto;margin-bottom:8px;">
            <table class="pc-dl-table">
                <thead><tr><th>도면명</th><th>엔진</th><th>사용자</th><th>수정일</th><th>카테고리</th></tr></thead>
                <tbody id="dlBody"></tbody>
            </table>
        </div>
        <div class="pc-dl-load-more" id="dlLoadMore" style="display:none;">
            <button onclick="loadDrawings()">더 보기</button>
        </div>
    </div>
</div>

<script>
const TOKEN     = () => localStorage.getItem('pmok_auth_token');
const API       = '/src/api/admin/pattern_categories.php';
const MOD_API   = '/src/api/admin/pattern_modifiers.php';
let _cats   = [];
let _mods   = [];
let _dlInited  = false;
let _modInited = false;

function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function switchTab(name, btn) {
    document.querySelectorAll('.pc-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tabCats').style.display     = name === 'cats'     ? '' : 'none';
    document.getElementById('tabMods').style.display     = name === 'mods'     ? '' : 'none';
    document.getElementById('tabDrawings').style.display = name === 'drawings' ? '' : 'none';
    if (name === 'mods' && !_modInited) { _modInited = true; loadMods(); }
    if (name === 'drawings' && !_dlInited) { _dlInited = true; initDrawingsTab(); }
}

/* ── 카테고리 관리 ── */
async function load() {
    const res = await fetch(API, { headers:{ Authorization:'Bearer '+TOKEN() } });
    _cats = (await res.json()).categories || [];
    render();
}

function render() {
    document.getElementById('pcBody').innerHTML = _cats.map(c => `
        <tr class="${c.is_active=='1'?'':'pc-inactive'}" id="row-${c.id}">
            <td><span class="pc-id">${c.id}</span></td>
            <td><input class="pc-name-input" value="${esc(c.name)}" id="name-${c.id}"></td>
            <td><input class="pc-code-input" value="${esc(c.code||'')}" maxlength="3" id="code-${c.id}"></td>
            <td><input class="pc-sort-input" type="number" value="${c.sort_order}" id="sort-${c.id}"></td>
            <td><input type="checkbox" ${c.is_active=='1'?'checked':''} onchange="toggleActive(${c.id},this.checked)"></td>
            <td style="display:flex;gap:6px;align-items:center;">
                <button class="pc-btn pc-btn-save" onclick="save(${c.id})">저장</button>
                <button class="pc-btn pc-btn-del"  onclick="del(${c.id},'${esc(c.name)}')">삭제</button>
                <span class="pc-status" id="st-${c.id}"></span>
            </td>
        </tr>`).join('');
}

async function save(id) {
    const name = document.getElementById(`name-${id}`).value.trim();
    const code = document.getElementById(`code-${id}`).value.trim();
    const sort = parseInt(document.getElementById(`sort-${id}`).value) || 0;
    const act  = document.querySelector(`#row-${id} input[type=checkbox]`).checked ? 1 : 0;
    const st   = document.getElementById(`st-${id}`);
    if (!name) { st.className='pc-status err'; st.textContent='이름 필수'; return; }
    const data = await (await fetch(API, { method:'PUT',
        headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN()},
        body: JSON.stringify({ id, name, code, sort_order:sort, is_active:act }) })).json();
    st.className = data.ok ? 'pc-status ok' : 'pc-status err';
    st.textContent = data.ok ? '저장됨' : (data.error||'오류');
    if (data.ok) { const c=_cats.find(x=>x.id==id); if(c){c.name=name;c.code=code;c.sort_order=sort;c.is_active=act;} setTimeout(()=>st.textContent='',2500); }
}

async function toggleActive(id, checked) {
    const name = document.getElementById(`name-${id}`).value.trim();
    const sort = parseInt(document.getElementById(`sort-${id}`).value) || 0;
    await fetch(API, { method:'PUT', headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN()},
        body: JSON.stringify({ id, name, sort_order:sort, is_active:checked?1:0 }) });
    document.getElementById(`row-${id}`).className = checked ? '' : 'pc-inactive';
}

async function del(id, name) {
    if (!confirm(`"${name}" 카테고리를 삭제하시겠습니까?`)) return;
    const data = await (await fetch(API, { method:'DELETE',
        headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN()},
        body: JSON.stringify({ id }) })).json();
    if (data.ok) { _cats = _cats.filter(c=>c.id!=id); render(); }
}

async function addCategory() {
    const st   = document.getElementById('addStatus');
    const name = document.getElementById('addName').value.trim();
    const code = document.getElementById('addCode').value.trim();
    const sort = parseInt(document.getElementById('addSort').value) || 0;
    if (!name) { st.className='pc-status err'; st.textContent='이름을 입력하세요'; return; }
    const data = await (await fetch(API, { method:'POST',
        headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN()},
        body: JSON.stringify({ name, code, sort_order:sort }) })).json();
    if (data.ok) {
        st.className='pc-status ok'; st.textContent='추가됨';
        document.getElementById('addName').value = '';
        document.getElementById('addCode').value = '';
        _cats.push({ id:data.id, name, code:code.toUpperCase(), sort_order:sort, is_active:'1' });
        render();
        setTimeout(()=>st.textContent='',2500);
    } else {
        st.className='pc-status err'; st.textContent=data.error||'오류';
    }
}

/* ── 수식어 관리 ── */
async function loadMods() {
    const res = await fetch(MOD_API, { headers:{ Authorization:'Bearer '+TOKEN() } });
    _mods = (await res.json()).modifiers || [];
    renderMods();
}

function renderMods() {
    document.getElementById('modBody').innerHTML = _mods.map(m => `
        <tr class="${m.is_active=='1'?'':'pc-inactive'}" id="modRow-${m.id}">
            <td><span class="pc-id">${m.id}</span></td>
            <td><input class="pc-name-input" value="${esc(m.name)}" id="modName-${m.id}"></td>
            <td><input class="pc-code-input" value="${esc(m.code||'')}" maxlength="2" id="modCode-${m.id}"></td>
            <td><input class="pc-sort-input" type="number" value="${m.sort_order}" id="modSort-${m.id}"></td>
            <td><input type="checkbox" ${m.is_active=='1'?'checked':''} onchange="toggleModActive(${m.id},this.checked)"></td>
            <td style="display:flex;gap:6px;align-items:center;">
                <button class="pc-btn pc-btn-save" onclick="saveMod(${m.id})">저장</button>
                <button class="pc-btn pc-btn-del"  onclick="delMod(${m.id},'${esc(m.name)}')">삭제</button>
                <span class="pc-status" id="modSt-${m.id}"></span>
            </td>
        </tr>`).join('');
}

async function saveMod(id) {
    const name = document.getElementById(`modName-${id}`).value.trim();
    const code = document.getElementById(`modCode-${id}`).value.trim();
    const sort = parseInt(document.getElementById(`modSort-${id}`).value) || 0;
    const act  = document.querySelector(`#modRow-${id} input[type=checkbox]`).checked ? 1 : 0;
    const st   = document.getElementById(`modSt-${id}`);
    if (!name) { st.className='pc-status err'; st.textContent='이름 필수'; return; }
    const data = await (await fetch(MOD_API, { method:'PUT',
        headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN()},
        body: JSON.stringify({ id, name, code, sort_order:sort, is_active:act }) })).json();
    st.className = data.ok ? 'pc-status ok' : 'pc-status err';
    st.textContent = data.ok ? '저장됨' : (data.error||'오류');
    if (data.ok) { const m=_mods.find(x=>x.id==id); if(m){m.name=name;m.code=code;m.sort_order=sort;m.is_active=act;} setTimeout(()=>st.textContent='',2500); }
}

async function toggleModActive(id, checked) {
    const name = document.getElementById(`modName-${id}`).value.trim();
    const code = document.getElementById(`modCode-${id}`).value.trim();
    const sort = parseInt(document.getElementById(`modSort-${id}`).value) || 0;
    await fetch(MOD_API, { method:'PUT', headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN()},
        body: JSON.stringify({ id, name, code, sort_order:sort, is_active:checked?1:0 }) });
    document.getElementById(`modRow-${id}`).className = checked ? '' : 'pc-inactive';
}

async function delMod(id, name) {
    if (!confirm(`"${name}" 수식어를 삭제하시겠습니까?`)) return;
    const data = await (await fetch(MOD_API, { method:'DELETE',
        headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN()},
        body: JSON.stringify({ id }) })).json();
    if (data.ok) { _mods = _mods.filter(m=>m.id!=id); renderMods(); }
}

async function addModifier() {
    const st   = document.getElementById('modAddStatus');
    const name = document.getElementById('modAddName').value.trim();
    const code = document.getElementById('modAddCode').value.trim();
    const sort = parseInt(document.getElementById('modAddSort').value) || 0;
    if (!name) { st.className='pc-status err'; st.textContent='이름을 입력하세요'; return; }
    const data = await (await fetch(MOD_API, { method:'POST',
        headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN()},
        body: JSON.stringify({ name, code, sort_order:sort }) })).json();
    if (data.ok) {
        st.className='pc-status ok'; st.textContent='추가됨';
        document.getElementById('modAddName').value = '';
        document.getElementById('modAddCode').value = '';
        _mods.push({ id:data.id, name, code:code.toUpperCase(), sort_order:sort, is_active:'1' });
        renderMods();
        setTimeout(()=>st.textContent='',2500);
    } else {
        st.className='pc-status err'; st.textContent=data.error||'오류';
    }
}

/* ── 도면 분류 탭 ── */
let _dlPage = 1, _dlFilter = '', _dlHasMore = false, _dlQuery = '', _dlSearchTimer = null;

function onDlSearch() {
    clearTimeout(_dlSearchTimer);
    _dlSearchTimer = setTimeout(() => {
        _dlQuery = document.getElementById('dlSearch').value.trim();
        _dlPage  = 1;
        document.getElementById('dlBody').innerHTML = '';
        loadDrawings();
    }, 300);
}

function initDrawingsTab() {
    const filtersEl = document.getElementById('dlFilters');
    _cats.forEach(c => {
        const btn = document.createElement('button');
        btn.className    = 'pc-dl-filter-btn';
        btn.dataset.cat  = c.id;
        btn.textContent  = c.name;
        btn.onclick      = () => setFilter(btn, String(c.id));
        filtersEl.appendChild(btn);
    });
    loadDrawings();
}

function setFilter(btn, cat) {
    document.querySelectorAll('.pc-dl-filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    _dlFilter = cat;
    _dlPage   = 1;
    document.getElementById('dlBody').innerHTML = '';
    loadDrawings();
}

function fmtDate(s) {
    const d = new Date(s); const pad = n => String(n).padStart(2,'0');
    return `${d.getFullYear()}.${pad(d.getMonth()+1)}.${pad(d.getDate())}`;
}

async function loadDrawings() {
    const params = new URLSearchParams({ page: _dlPage });
    if (_dlFilter !== '') params.set('category', _dlFilter);
    if (_dlQuery  !== '') params.set('q', _dlQuery);
    const res  = await fetch(`/src/api/admin/drawings.php?${params}`, { headers:{ Authorization:'Bearer '+TOKEN() } });
    const data = await res.json();
    const rows = data.drawings || [];
    _dlHasMore = !!data.has_more;

    document.getElementById('dlBody').insertAdjacentHTML('beforeend', rows.map(d => {
        const catOpts = `<option value="">분류 없음</option>` +
            _cats.map(c => `<option value="${c.id}"${d.pattern_category == c.id ? ' selected' : ''}>${esc(c.name)}</option>`).join('');
        return `<tr>
            <td>${esc(d.title)}</td>
            <td><span class="pc-dl-type-badge">${esc(d.type)}</span></td>
            <td style="font-size:11px;color: var(--text);">${esc(d.user_email||'')}</td>
            <td style="font-size:11px;color: var(--text);">${fmtDate(d.updated_at)}</td>
            <td><select class="pc-dl-cat-select" onchange="saveDlCat(${d.id},this.value)">${catOpts}</select></td>
        </tr>`;
    }).join(''));

    _dlPage++;
    document.getElementById('dlLoadMore').style.display = _dlHasMore ? '' : 'none';
}

async function saveDlCat(drawingId, val) {
    await fetch('/src/api/drawings/set_category.php', {
        method:'POST',
        headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN()},
        body: JSON.stringify({ drawing_id:drawingId, pattern_category: val || null }),
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('pcPage');
    if (authGetToken()) { page.style.display=''; load(); }
    window.addEventListener('pmokAuthChanged', () => { page.style.display=''; load(); });
    authUpdateNav();
});
</script>
</body>
</html>
