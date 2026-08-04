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
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>패턴 카테고리</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-tags me-2"></i>패턴 카테고리</h1>
    </div>

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

<script>
const TOKEN = () => localStorage.getItem('pmok_auth_token');
const API   = '/src/api/admin/pattern_categories.php';
let _cats = [];

function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

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

document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('pcPage');
    if (authGetToken()) { page.style.display=''; load(); }
    window.addEventListener('pmokAuthChanged', () => { page.style.display=''; load(); });
    authUpdateNav();
});
</script>
</body>
</html>
