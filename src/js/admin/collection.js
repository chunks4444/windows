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
    const res  = await fetch('/src/api/admin/collection.php', { headers: headers() });
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
        if (!['image/jpeg', 'image/png'].includes(file.type)) { alert('PNG 또는 JPG 파일만 업로드할 수 있습니다.'); return; }
        const reader = new FileReader();
        reader.onload = ev => {
            const img = new Image();
            img.onload = () => {
                const MAX = 1024;
                let w = img.width, h = img.height;
                if (w > MAX || h > MAX) { const s = Math.min(MAX/w, MAX/h); w = Math.round(w*s); h = Math.round(h*s); }
                const canvas = document.createElement('canvas');
                canvas.width = w; canvas.height = h;
                canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                pendingImg = canvas.toDataURL('image/jpeg', 0.92);
                document.getElementById('lpImgPreview').src = pendingImg;
                document.getElementById('lpImgPreview').classList.add('show');
            };
            img.src = ev.target.result;
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
        const res  = await fetch('/src/api/admin/collection.php', {
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
    const res  = await fetch('/src/api/admin/collection.php', {
        method:  'PUT',
        headers: headers(),
        body:    JSON.stringify({ id, name_ko: name, is_active: active }),
    });
    if (res.ok) await loadPatterns();
}

async function deletePattern(id, name) {
    if (!confirm(`"${name}" 패턴을 삭제하시겠습니까?`)) return;
    const res = await fetch('/src/api/admin/collection.php', {
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
