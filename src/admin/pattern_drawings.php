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
        .pc-dl-view-link { font-size:12px; font-weight:600; color:var(--accent); text-decoration:none; }
        .pc-dl-view-link:hover { text-decoration:underline; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>
<?php include __DIR__ . '/../components/admin_sidenav.php'; ?>

<div class="db-page" id="pcPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>도면 분류</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-diagram-3 me-2"></i>도면 분류</h1>
    </div>

    <input type="text" class="pc-dl-search" id="dlSearch" placeholder="도면명·사용자 이메일로 검색…" oninput="onDlSearch()">
    <div class="pc-dl-filters" id="dlFilters">
        <button class="pc-dl-filter-btn active" data-cat="" onclick="setFilter(this,'')">전체</button>
        <button class="pc-dl-filter-btn" data-cat="0" onclick="setFilter(this,'0')">미분류</button>
    </div>
    <div style="overflow-x:auto;margin-bottom:8px;">
        <table class="pc-dl-table">
            <thead><tr><th>도면명</th><th>엔진</th><th>사용자</th><th>수정일</th><th>카테고리</th><th>보기</th></tr></thead>
            <tbody id="dlBody"></tbody>
        </table>
    </div>
    <div class="pc-dl-load-more" id="dlLoadMore" style="display:none;">
        <button onclick="loadDrawings()">더 보기</button>
    </div>
</div>

<script>
const TOKEN = () => localStorage.getItem('pmok_auth_token');
const CAT_API = '/src/api/admin/pattern_categories.php';
let _cats = [];

function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

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

async function initDrawingsTab() {
    const res = await fetch(CAT_API, { headers:{ Authorization:'Bearer '+TOKEN() } });
    _cats = (await res.json()).categories || [];
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
            <td><a href="/src/engine/${encodeURIComponent(d.type)}/${encodeURIComponent(d.type)}.php?drawing_id=${d.id}&admin_view=1" target="_blank" rel="noopener" class="pc-dl-view-link">보기</a></td>
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
    if (authGetToken()) { page.style.display=''; initDrawingsTab(); }
    window.addEventListener('pmokAuthChanged', () => { page.style.display=''; initDrawingsTab(); });
    authUpdateNav();
});
</script>
</body>
</html>
