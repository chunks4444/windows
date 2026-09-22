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
        .it-table { width:100%; border-collapse:collapse; font-size:13px; max-width:900px; }
        .it-table th { background:var(--bg); padding:8px 12px; text-align:left; font-weight:600; color: var(--text); border-bottom:2px solid var(--border); }
        .it-table td { padding:8px 12px; border-bottom:1px solid var(--border); vertical-align:middle; }
        .it-table tr:hover td { background:var(--bg); }
        .it-id { font-family:monospace; font-size:11px; color: var(--text); background:var(--bg); padding:2px 6px; border-radius:4px; }
        .it-kr-input { border:1px solid var(--border); border-radius:5px; padding:4px 8px; font-size:13px; width:120px; }
        .it-en-input { border:1px solid var(--border); border-radius:5px; padding:4px 8px; font-size:13px; width:280px; }
        .it-cat-input { border:1px solid var(--border); border-radius:5px; padding:4px 8px; font-size:13px; width:90px; }
        .it-sort-input { border:1px solid var(--border); border-radius:5px; padding:4px 6px; font-size:13px; width:52px; text-align:center; }
        .it-btn { border:none; border-radius:5px; padding:4px 10px; font-size:12px; font-weight:600; cursor:pointer; }
        .it-btn-save { background:var(--accent); color:var(--bg); } .it-btn-save:hover { opacity:.85; }
        .it-btn-del  { background:var(--bg); color:var(--danger); }    .it-btn-del:hover  { background:var(--danger-tint); }
        .it-status { font-size:12px; margin-left:6px; }
        .it-status.ok { color:var(--accent); } .it-status.err { color:var(--danger); }
        .it-add-row { display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin-top:16px; }
        .it-add-row input { border:1px solid var(--border); border-radius:6px; padding:6px 10px; font-size:13px; }
        .it-add-btn { background:var(--accent); color:var(--bg); border:none; border-radius:6px; padding:6px 18px; font-size:13px; font-weight:600; cursor:pointer; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>
<?php include __DIR__ . '/../components/admin_sidenav.php'; ?>

<div class="db-page" id="itPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>영문 용어집</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-translate me-2"></i>영문 용어집</h1>
    </div>

    <p style="font-size:13px;color: var(--text);margin:-8px 0 16px;">영문(/en/) 사이트에서 한글 창호 용어를 영문으로 치환할 때 쓰는 표입니다. 한글 항목을 여기서 추가/수정하면 코드 배포 없이 사이트 전체(엔진·블로그·포트폴리오·가이드·컬렉션 필터 등)에 바로 반영됩니다. 분류(category)는 어드민 화면 정리용일 뿐 동작에는 영향 없습니다.</p>
    <div style="overflow-x:auto;">
        <table class="it-table" id="itTable">
            <thead><tr><th>ID</th><th>한글</th><th>영문</th><th>분류</th><th>정렬</th><th></th></tr></thead>
            <tbody id="itBody"></tbody>
        </table>
    </div>
    <div class="it-add-row">
        <input id="addKr" placeholder="한글 (예: 정자살)" class="it-kr-input">
        <input id="addEn" placeholder="영문 (예: Jeongja-sal (Grid Lattice))" class="it-en-input">
        <input id="addCat" placeholder="분류 (예: lattice)" class="it-cat-input">
        <input id="addSort" type="number" value="0" placeholder="정렬" class="it-sort-input">
        <button class="it-add-btn" onclick="addTerm()">추가</button>
        <span class="it-status" id="addStatus"></span>
    </div>
</div>

<script>
const TOKEN = () => localStorage.getItem('pmok_auth_token');
const API   = '/src/api/admin/i18n_terms.php';
let _terms = [];

function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

async function load() {
    const res = await fetch(API, { headers:{ Authorization:'Bearer '+TOKEN() } });
    _terms = (await res.json()).terms || [];
    render();
}

function render() {
    document.getElementById('itBody').innerHTML = _terms.map(t => `
        <tr id="row-${t.id}">
            <td><span class="it-id">${t.id}</span></td>
            <td><input class="it-kr-input" value="${esc(t.korean)}" id="kr-${t.id}"></td>
            <td><input class="it-en-input" value="${esc(t.english)}" id="en-${t.id}"></td>
            <td><input class="it-cat-input" value="${esc(t.category)}" id="cat-${t.id}"></td>
            <td><input class="it-sort-input" type="number" value="${t.sort_order}" id="sort-${t.id}"></td>
            <td style="display:flex;gap:6px;align-items:center;">
                <button class="it-btn it-btn-save" onclick="save(${t.id})">저장</button>
                <button class="it-btn it-btn-del"  onclick="del(${t.id},'${esc(t.korean)}')">삭제</button>
                <span class="it-status" id="st-${t.id}"></span>
            </td>
        </tr>`).join('');
}

async function save(id) {
    const korean  = document.getElementById(`kr-${id}`).value.trim();
    const english = document.getElementById(`en-${id}`).value.trim();
    const category = document.getElementById(`cat-${id}`).value.trim() || 'general';
    const sort = parseInt(document.getElementById(`sort-${id}`).value) || 0;
    const st   = document.getElementById(`st-${id}`);
    if (!korean || !english) { st.className='it-status err'; st.textContent='한글/영문 필수'; return; }
    const data = await (await fetch(API, { method:'PUT',
        headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN()},
        body: JSON.stringify({ id, korean, english, category, sort_order:sort }) })).json();
    st.className = data.ok ? 'it-status ok' : 'it-status err';
    st.textContent = data.ok ? '저장됨' : (data.error||'오류');
    if (data.ok) { const t=_terms.find(x=>x.id==id); if(t){t.korean=korean;t.english=english;t.category=category;t.sort_order=sort;} setTimeout(()=>st.textContent='',2500); }
}

async function del(id, korean) {
    if (!confirm(`"${korean}" 용어를 삭제하시겠습니까?`)) return;
    const data = await (await fetch(API, { method:'DELETE',
        headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN()},
        body: JSON.stringify({ id }) })).json();
    if (data.ok) { _terms = _terms.filter(t=>t.id!=id); render(); }
}

async function addTerm() {
    const st   = document.getElementById('addStatus');
    const korean  = document.getElementById('addKr').value.trim();
    const english = document.getElementById('addEn').value.trim();
    const category = document.getElementById('addCat').value.trim() || 'general';
    const sort = parseInt(document.getElementById('addSort').value) || 0;
    if (!korean || !english) { st.className='it-status err'; st.textContent='한글/영문을 입력하세요'; return; }
    const data = await (await fetch(API, { method:'POST',
        headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN()},
        body: JSON.stringify({ korean, english, category, sort_order:sort }) })).json();
    if (data.ok) {
        st.className='it-status ok'; st.textContent='추가됨';
        document.getElementById('addKr').value = '';
        document.getElementById('addEn').value = '';
        document.getElementById('addCat').value = '';
        _terms.push({ id:data.id, korean, english, category, sort_order:sort });
        render();
        setTimeout(()=>st.textContent='',2500);
    } else {
        st.className='it-status err'; st.textContent=data.error||'오류';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('itPage');
    if (authGetToken()) { page.style.display=''; load(); }
    window.addEventListener('pmokAuthChanged', () => { page.style.display=''; load(); });
    authUpdateNav();
});
</script>
</body>
</html>
