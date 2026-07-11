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
        .cf-table { width:100%; border-collapse:collapse; font-size:13px; max-width:640px; }
        .cf-table th { background:var(--bg); padding:8px 12px; text-align:left; font-weight:600; color: var(--text); border-bottom:2px solid var(--border); }
        .cf-table td { padding:8px 12px; border-bottom:1px solid var(--border); vertical-align:middle; }
        .cf-table tr:hover td { background:var(--bg); }
        .cf-id { font-family:monospace; font-size:11px; color: var(--text); background:var(--bg); padding:2px 6px; border-radius:4px; }
        .cf-name-input { border:1px solid var(--border); border-radius:5px; padding:4px 8px; font-size:13px; width:130px; }
        .cf-sort-input { border:1px solid var(--border); border-radius:5px; padding:4px 6px; font-size:13px; width:52px; text-align:center; }
        .cf-btn { border:none; border-radius:5px; padding:4px 10px; font-size:12px; font-weight:600; cursor:pointer; }
        .cf-btn-save { background:var(--accent); color:var(--bg); } .cf-btn-save:hover { opacity:.85; }
        .cf-btn-del  { background:var(--bg); color:var(--danger); }    .cf-btn-del:hover  { background:var(--danger-tint); }
        .cf-inactive td { opacity:.4; }
        .cf-status { font-size:12px; margin-left:6px; }
        .cf-status.ok { color:var(--accent); } .cf-status.err { color:var(--danger); }
        .cf-add-row { display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin-top:16px; }
        .cf-add-row input { border:1px solid var(--border); border-radius:6px; padding:6px 10px; font-size:13px; }
        .cf-add-btn { background:var(--accent); color:var(--bg); border:none; border-radius:6px; padding:6px 18px; font-size:13px; font-weight:600; cursor:pointer; }
        .cf-drag-handle { cursor:grab; color:var(--text-muted); }
        .cf-drag-handle:active { cursor:grabbing; }
        #cfBody tr.dragging { opacity:.4; }
        #cfBody tr.drag-over td { background:var(--accent-tint); }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="cfPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>컬렉션 필터 키워드</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-funnel me-2"></i>컬렉션 필터 키워드</h1>
    </div>
    <p style="font-size:13px;color: var(--text);margin:-8px 0 16px;">컬렉션 페이지 "공간" 드롭다운 전용 키워드입니다. 메인 페이지 큐레이션 카드(공간 카드 관리)와는 별개로 관리됩니다. 행을 드래그해 순서를 바꿀 수 있습니다.</p>

    <div style="overflow-x:auto;">
        <table class="cf-table" id="cfTable">
            <thead><tr><th style="width:28px;"></th><th>ID</th><th>라벨</th><th>검색어</th><th>정렬</th><th>활성</th><th></th></tr></thead>
            <tbody id="cfBody"></tbody>
        </table>
    </div>
    <div class="cf-add-row">
        <input id="addLabel" placeholder="라벨 (예: 중문)" style="width:130px;">
        <input id="addQuery" placeholder="검색어 (예: 중문)" style="width:130px;">
        <input id="addSort" type="number" value="0" placeholder="정렬" style="width:64px;">
        <button class="cf-add-btn" onclick="addFilter()">추가</button>
        <span class="cf-status" id="addStatus"></span>
    </div>
</div>

<script>
const TOKEN = () => localStorage.getItem('pmok_auth_token');
const API   = '/src/api/admin/collection_filters.php';
let _filters = [];

function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

async function load() {
    const res = await fetch(API, { headers:{ Authorization:'Bearer '+TOKEN() } });
    _filters = (await res.json()).filters || [];
    render();
}

function render() {
    document.getElementById('cfBody').innerHTML = _filters.map(f => `
        <tr class="${f.is_active=='1'?'':'cf-inactive'}" id="row-${f.id}" data-id="${f.id}" draggable="true">
            <td style="text-align:center;"><span class="cf-drag-handle"><i class="bi bi-grip-vertical"></i></span></td>
            <td><span class="cf-id">${f.id}</span></td>
            <td><input class="cf-name-input" value="${esc(f.label)}" id="label-${f.id}"></td>
            <td><input class="cf-name-input" value="${esc(f.query)}" id="query-${f.id}"></td>
            <td><input class="cf-sort-input" type="number" value="${f.sort_order}" id="sort-${f.id}"></td>
            <td><input type="checkbox" ${f.is_active=='1'?'checked':''} onchange="toggleActive(${f.id},this.checked)"></td>
            <td style="display:flex;gap:6px;align-items:center;">
                <button class="cf-btn cf-btn-save" onclick="save(${f.id})">저장</button>
                <button class="cf-btn cf-btn-del"  onclick="del(${f.id},'${esc(f.label)}')">삭제</button>
                <span class="cf-status" id="st-${f.id}"></span>
            </td>
        </tr>`).join('');
    bindDrag();
}

let dragSrc;
function bindDrag() {
    document.querySelectorAll('#cfBody tr').forEach(tr => {
        tr.addEventListener('dragstart', () => { dragSrc = tr; tr.classList.add('dragging'); });
        tr.addEventListener('dragend',   () => tr.classList.remove('dragging'));
        tr.addEventListener('dragover',  e => { e.preventDefault(); tr.classList.add('drag-over'); });
        tr.addEventListener('dragleave', () => tr.classList.remove('drag-over'));
        tr.addEventListener('drop', async e => {
            e.preventDefault();
            tr.classList.remove('drag-over');
            if (dragSrc === tr) return;
            tr.parentNode.insertBefore(dragSrc, tr.nextSibling);
            const ids = [...tr.parentNode.querySelectorAll('tr')].map(r => +r.dataset.id);
            await fetch(API, { method:'PATCH', headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN()},
                body: JSON.stringify({ ids }) });
            await load();
        });
    });
}

async function save(id) {
    const label = document.getElementById(`label-${id}`).value.trim();
    const query = document.getElementById(`query-${id}`).value.trim();
    const sort  = parseInt(document.getElementById(`sort-${id}`).value) || 0;
    const act   = document.querySelector(`#row-${id} input[type=checkbox]`).checked ? 1 : 0;
    const st    = document.getElementById(`st-${id}`);
    if (!label || !query) { st.className='cf-status err'; st.textContent='라벨/검색어 필수'; return; }
    const data = await (await fetch(API, { method:'PUT',
        headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN()},
        body: JSON.stringify({ id, label, query, sort_order:sort, is_active:act }) })).json();
    st.className = data.ok ? 'cf-status ok' : 'cf-status err';
    st.textContent = data.ok ? '저장됨' : (data.error||'오류');
    if (data.ok) { const f=_filters.find(x=>x.id==id); if(f){f.label=label;f.query=query;f.sort_order=sort;f.is_active=act;} setTimeout(()=>st.textContent='',2500); }
}

async function toggleActive(id, checked) {
    const label = document.getElementById(`label-${id}`).value.trim();
    const query = document.getElementById(`query-${id}`).value.trim();
    const sort  = parseInt(document.getElementById(`sort-${id}`).value) || 0;
    await fetch(API, { method:'PUT', headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN()},
        body: JSON.stringify({ id, label, query, sort_order:sort, is_active:checked?1:0 }) });
    document.getElementById(`row-${id}`).className = checked ? '' : 'cf-inactive';
}

async function del(id, label) {
    if (!confirm(`"${label}" 필터를 삭제하시겠습니까?`)) return;
    const data = await (await fetch(API, { method:'DELETE',
        headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN()},
        body: JSON.stringify({ id }) })).json();
    if (data.ok) { _filters = _filters.filter(f=>f.id!=id); render(); }
}

async function addFilter() {
    const st    = document.getElementById('addStatus');
    const label = document.getElementById('addLabel').value.trim();
    const query = document.getElementById('addQuery').value.trim();
    const sort  = parseInt(document.getElementById('addSort').value) || 0;
    if (!label || !query) { st.className='cf-status err'; st.textContent='라벨/검색어를 입력하세요'; return; }
    const data = await (await fetch(API, { method:'POST',
        headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN()},
        body: JSON.stringify({ label, query, sort_order:sort }) })).json();
    if (data.ok) {
        st.className='cf-status ok'; st.textContent='추가됨';
        document.getElementById('addLabel').value = '';
        document.getElementById('addQuery').value = '';
        _filters.push({ id:data.id, label, query, sort_order:sort, is_active:'1' });
        render();
        setTimeout(()=>st.textContent='',2500);
    } else {
        st.className='cf-status err'; st.textContent=data.error||'오류';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('cfPage');
    if (authGetToken()) { page.style.display=''; load(); }
    window.addEventListener('pmokAuthChanged', () => { page.style.display=''; load(); });
    authUpdateNav();
});
</script>
</body>
</html>
