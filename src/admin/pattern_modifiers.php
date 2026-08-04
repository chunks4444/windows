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
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>
<?php include __DIR__ . '/../components/admin_sidenav.php'; ?>

<div class="db-page" id="pcPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>수식어 관리</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-tag me-2"></i>수식어 관리</h1>
    </div>

    <p style="font-size:13px;color: var(--text);margin:-8px 0 16px;">평목 컬렉션 코드 체계 v1.0의 수식어(계열 안에서 세부 구분, 예: JEO-SE-001)입니다. 컬렉션 아이템 생성 시 이 목록 중에서만 고를 수 있습니다.<br>
    <code>PM</code>=새살, <code>JS</code>=쇼지, <code>JK</code>=쿠미꼬는 "자체 창작"(PYM) 계열 안에서 컬렉션 페이지의 우리살/새살/일본살 필터를 구분하는 데 쓰입니다 — 이 수식어를 붙여 아이템을 만들면 자동으로 새살/일본살 필터에 노출됩니다.</p>
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

<script>
const TOKEN   = () => localStorage.getItem('pmok_auth_token');
const MOD_API = '/src/api/admin/pattern_modifiers.php';
let _mods = [];

function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

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

document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('pcPage');
    if (authGetToken()) { page.style.display=''; loadMods(); }
    window.addEventListener('pmokAuthChanged', () => { page.style.display=''; loadMods(); });
    authUpdateNav();
});
</script>
</body>
</html>
