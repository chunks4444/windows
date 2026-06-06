<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/src/css/dashboard.css">
    <link rel="stylesheet" href="/src/css/users.css">
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>
    <style>
        .lib-thumb { width:56px; height:56px; object-fit:cover; border-radius:6px; background:var(--bg-2); }
        .lib-thumb-empty { width:56px; height:56px; border-radius:6px; background:var(--bg-2); display:flex; align-items:center; justify-content:center; color:var(--text-3); font-size:18px; }
        .kw-list { display:flex; flex-wrap:wrap; gap:4px; }
        .kw-badge { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; background:var(--bg-2); border-radius:20px; font-size:12px; color:var(--text-2); }
        .kw-badge button { background:none; border:none; padding:0; line-height:1; cursor:pointer; color:var(--text-3); font-size:13px; }
        .kw-badge button:hover { color:#c00; }
        .kw-input-row { display:flex; gap:6px; margin-top:6px; }
        .kw-input-row input { flex:1; }
        .lib-img-preview { width:100%; max-height:200px; object-fit:contain; border-radius:8px; background:var(--bg-2); display:none; margin-bottom:8px; }
        .lib-img-preview.show { display:block; }
        .lib-upload-label { display:block; padding:10px; border:1.5px dashed var(--border); border-radius:8px; text-align:center; cursor:pointer; color:var(--text-3); font-size:13px; }
        .lib-upload-label:hover { border-color:var(--accent); color:var(--accent); }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="libAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="libPage" style="display:none;">
    <div class="db-header">
        <h1 class="db-title">라이브러리 패턴</h1>
        <div style="display:flex;gap:8px;align-items:center;">
            <div style="display:flex;gap:4px;">
                <button class="adm-edit-btn lib-status-btn" data-status="all"     style="height:32px;padding:0 12px;" onclick="setStatusFilter('all')">전체</button>
                <button class="adm-edit-btn lib-status-btn" data-status="active"   style="height:32px;padding:0 12px;" onclick="setStatusFilter('active')">활성</button>
                <button class="adm-edit-btn lib-status-btn" data-status="inactive" style="height:32px;padding:0 12px;" onclick="setStatusFilter('inactive')">비활성</button>
            </div>
            <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="openAddModal()">
                <i class="bi bi-plus-lg"></i> 추가
            </button>
        </div>
    </div>

    <div class="adm-table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width:64px;"></th>
                    <th>이름</th>
                    <th>키워드</th>
                    <th style="width:80px;">도면 ID</th>
                    <th style="width:56px;">순서</th>
                    <th style="width:72px;">상태</th>
                    <th style="width:120px;"></th>
                </tr>
            </thead>
            <tbody id="libTbody"></tbody>
        </table>
    </div>
</div>

<!-- 추가/수정 모달 -->
<div class="adm-modal-overlay" id="libModalOverlay">
    <div class="adm-modal" style="max-width:520px;">
        <div class="adm-modal-head">
            <h3 id="libModalTitle">패턴 추가</h3>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <div id="libModalAlert" style="display:none;"></div>

            <div class="adm-mfield">
                <label>이름</label>
                <input type="text" id="lpName" placeholder="예: 정자살" maxlength="80">
            </div>
            <div class="adm-mfield">
                <label>도면 <small style="color:var(--text-3);font-weight:400;">(선택)</small></label>
                <select id="lpDrawingId" style="width:100%;padding:8px 10px;border:1.5px solid var(--border);border-radius:8px;background:var(--bg-1);color:var(--text-1);font-size:14px;">
                    <option value="">— 연결 안함 —</option>
                </select>
            </div>
            <div class="adm-mfield">
                <label>대표 이미지</label>
                <img id="lpImgPreview" class="lib-img-preview" src="" alt="">
                <label class="lib-upload-label" for="lpImgFile">
                    <i class="bi bi-upload"></i> 이미지 선택 (최대 10MB · 1024px)
                </label>
                <input type="file" id="lpImgFile" accept="image/*" style="display:none;">
            </div>
            <div class="adm-mfield">
                <label>정렬 순서</label>
                <input type="number" id="lpOrder" value="0" min="0" max="9999">
            </div>
            <div class="adm-mfield" style="margin-bottom:0;">
                <label>키워드</label>
                <div class="kw-list" id="kwList"></div>
                <div class="kw-input-row">
                    <input type="text" id="kwInput" placeholder="키워드 입력 후 Enter" maxlength="60">
                    <button class="adm-edit-btn" style="height:36px;padding:0 12px;" onclick="addKeyword()">추가</button>
                </div>
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" id="lpSaveBtn" onclick="savePattern()">저장</button>
        </div>
    </div>
</div>

<script>
let editingId   = null;
let keywords    = [];
let pendingImg  = null;
let allDrawings = [];
let allPatterns = [];
let statusFilter = 'all';

function token()   { return localStorage.getItem('pmok_auth_token'); }
function headers() { return { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() }; }

async function init() {
    const user = authGetUser();
    if (!user || user.role !== 's') {
        document.getElementById('libAuthWall').style.display = '';
        return;
    }
    document.getElementById('libPage').style.display = '';
    setStatusFilter('all');
    await Promise.all([loadDrawings(), loadPatterns()]);
}

async function loadDrawings() {
    const res  = await fetch('/src/api/admin/drawings.php', { headers: headers() });
    const data = await res.json();
    if (!res.ok) return;
    allDrawings = data.drawings || [];
    const sel = document.getElementById('lpDrawingId');
    sel.innerHTML = '<option value="">— 연결 안함 —</option>' +
        allDrawings.map(d => `<option value="${d.id}">[${esc(d.type)}] ${esc(d.title)} #${d.id}</option>`).join('');
}

async function loadPatterns() {
    const res  = await fetch('/src/api/admin/library.php', { headers: headers() });
    const data = await res.json();
    if (!res.ok) return;
    allPatterns = data.patterns || [];
    applyFilter();
}

function setStatusFilter(status) {
    statusFilter = status;
    document.querySelectorAll('.lib-status-btn').forEach(b => {
        const on = b.dataset.status === status;
        b.style.background  = on ? 'var(--teal)' : '';
        b.style.color       = on ? '#fff' : '';
        b.style.fontWeight  = on ? '700' : '';
        b.style.borderColor = on ? 'var(--teal)' : '';
    });
    applyFilter();
}

function applyFilter() {
    let list = allPatterns;
    if (statusFilter === 'active')   list = allPatterns.filter(p => p.is_active == 1);
    if (statusFilter === 'inactive') list = allPatterns.filter(p => p.is_active != 1);
    renderTable(list);
}

function renderTable(patterns) {
    document.getElementById('libTbody').innerHTML = patterns.map(p => `
        <tr>
            <td>${p.image_path
                ? `<img class="lib-thumb" src="${esc(p.image_path)}" loading="lazy">`
                : `<div class="lib-thumb-empty"><i class="bi bi-image"></i></div>`}</td>
            <td style="font-weight:600;">${esc(p.name_ko)}</td>
            <td><div class="kw-list">${(p.keywords||[]).map(k =>
                `<span class="kw-badge">${esc(k)}</span>`).join('')}</div></td>
            <td style="color:var(--text-3);font-size:12px;">${p.drawing_id || '—'}</td>
            <td class="st-num" style="color:var(--text-3);">${p.sort_order}</td>
            <td>${p.is_active == 1
                ? '<span class="adm-active-badge">활성</span>'
                : '<span class="adm-withdrawn-badge">비활성</span>'}</td>
            <td><div class="adm-action-cell">
                <button class="adm-edit-btn" onclick='openEditModal(${JSON.stringify(p)})'>수정</button>
                <button class="adm-withdraw-btn" onclick="toggleActive(${p.id}, ${p.is_active == 1 ? 0 : 1})">
                    ${p.is_active == 1 ? '숨김' : '표시'}
                </button>
                <button class="adm-withdraw-btn" style="background:#c00;color:#fff;" onclick="deletePattern(${p.id}, '${esc(p.name_ko)}')">삭제</button>
            </div></td>
        </tr>
    `).join('') || '<tr><td colspan="7" style="padding:40px;text-align:center;color:var(--text-3);">패턴이 없습니다.</td></tr>';
}

function openAddModal() {
    editingId = null; keywords = []; pendingImg = null;
    document.getElementById('libModalTitle').textContent = '패턴 추가';
    document.getElementById('lpName').value      = '';
    document.getElementById('lpDrawingId').value = '';
    document.getElementById('lpOrder').value     = '0';
    document.getElementById('lpImgFile').value   = '';
    document.getElementById('lpImgPreview').src  = '';
    document.getElementById('lpImgPreview').classList.remove('show');
    document.getElementById('libModalAlert').style.display = 'none';
    renderKeywords();
    document.getElementById('libModalOverlay').classList.add('open');
    document.getElementById('lpName').focus();
}

function openEditModal(p) {
    editingId = p.id; keywords = [...(p.keywords || [])]; pendingImg = null;
    document.getElementById('libModalTitle').textContent = '패턴 수정';
    document.getElementById('lpName').value      = p.name_ko;
    document.getElementById('lpDrawingId').value = p.drawing_id || '';
    // select가 아직 없는 값이면 빈 값 유지 (로딩 타이밍)
    document.getElementById('lpOrder').value     = p.sort_order;
    document.getElementById('lpImgFile').value   = '';
    if (p.image_path) {
        document.getElementById('lpImgPreview').src = p.image_path;
        document.getElementById('lpImgPreview').classList.add('show');
    } else {
        document.getElementById('lpImgPreview').src = '';
        document.getElementById('lpImgPreview').classList.remove('show');
    }
    document.getElementById('libModalAlert').style.display = 'none';
    renderKeywords();
    document.getElementById('libModalOverlay').classList.add('open');
    document.getElementById('lpName').focus();
}

function closeModal() {
    document.getElementById('libModalOverlay').classList.remove('open');
    editingId = null; keywords = []; pendingImg = null;
}

/* ── 키워드 ── */
function renderKeywords() {
    document.getElementById('kwList').innerHTML = keywords.map((k, i) => `
        <span class="kw-badge">${esc(k)}<button onclick="removeKeyword(${i})" title="삭제">×</button></span>
    `).join('');
}

function addKeyword() {
    const inp = document.getElementById('kwInput');
    const kw  = inp.value.trim();
    if (!kw || keywords.includes(kw)) { inp.value = ''; return; }
    keywords.push(kw);
    inp.value = '';
    renderKeywords();
}

function removeKeyword(i) {
    keywords.splice(i, 1);
    renderKeywords();
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('kwInput').addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.isComposing) { e.preventDefault(); addKeyword(); }
    });

    document.getElementById('lpImgFile').addEventListener('change', e => {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = ev => {
            pendingImg = ev.target.result;
            document.getElementById('lpImgPreview').src = pendingImg;
            document.getElementById('lpImgPreview').classList.add('show');
        };
        reader.readAsDataURL(file);
    });
});

/* ── 저장 ── */
async function savePattern() {
    const btn  = document.getElementById('lpSaveBtn');
    const name = document.getElementById('lpName').value.trim();
    if (!name) { showAlert('이름을 입력하세요.'); return; }

    const body = {
        name_ko:    name,
        drawing_id: parseInt(document.getElementById('lpDrawingId').value) || 0,
        sort_order: parseInt(document.getElementById('lpOrder').value) || 0,
        keywords:   keywords,
    };
    if (pendingImg) body.image = pendingImg;
    if (editingId !== null) body.id = editingId;
    if (editingId !== null) body.is_active = 1;

    btn.disabled = true; btn.textContent = '저장 중…';
    try {
        const res  = await fetch('/src/api/admin/library.php', {
            method:  editingId !== null ? 'PUT' : 'POST',
            headers: headers(),
            body:    JSON.stringify(body),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || '오류가 발생했습니다.');
        closeModal();
        await loadPatterns();
    } catch (err) {
        showAlert(err.message);
    } finally {
        btn.disabled = false; btn.textContent = '저장';
    }
}

async function toggleActive(id, active) {
    const row  = document.querySelector(`[onclick*="toggleActive(${id},"]`).closest('tr');
    const name = row.querySelector('td:nth-child(2)').textContent.trim();
    const res  = await fetch('/src/api/admin/library.php', {
        method:  'PUT',
        headers: headers(),
        body:    JSON.stringify({ id, name_ko: name, is_active: active }),
    });
    if (res.ok) await loadPatterns();
}

async function deletePattern(id, name) {
    if (!confirm(`"${name}" 패턴을 삭제하시겠습니까?`)) return;
    const res = await fetch('/src/api/admin/library.php', {
        method:  'DELETE',
        headers: headers(),
        body:    JSON.stringify({ id }),
    });
    if (res.ok) await loadPatterns();
    else { const d = await res.json(); alert(d.error || '삭제 실패'); }
}

function showAlert(msg) {
    const el = document.getElementById('libModalAlert');
    el.textContent   = msg;
    el.className     = 'adm-alert adm-alert-error';
    el.style.display = '';
}

function esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

document.getElementById('libModalOverlay').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeModal();
});

document.addEventListener('DOMContentLoaded', init);
window.addEventListener('pmokAuthChanged', init);
</script>
</body>
</html>
