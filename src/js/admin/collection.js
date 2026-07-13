let editingId   = null;
let keywords    = [];
let pendingImg  = null;
let allDrawings = [];
let allPatterns = [];
let statusFilter = 'all';
let categoryNames = {};
let _editOriginalCategory  = '';
let _editOriginalModifier  = '';
let _editOriginalSlug      = '';
let _currentKeepDrawingId  = null; // 지금 열려있는 추가/수정 모달 자신이 쓰던 도면 (피커에서 제외 대상 예외)

function token()   { return localStorage.getItem('pmok_auth_token'); }
function headers() { return { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() }; }

async function init() {
    const user = authGetUser();
    if (!user || user.role !== 's') {
        document.getElementById('libAuthWall').style.display = '';
        return;
    }
    document.getElementById('libPage').style.display = '';
    document.querySelectorAll('#lpCategory option').forEach(o => {
        if (o.value) categoryNames[o.value] = o.textContent;
    });
    setStatusFilter('all');
    await Promise.all([loadDrawings(), loadPatterns()]);
}

async function loadDrawings() {
    const res  = await fetch('/src/api/admin/drawings.php', { headers: headers() });
    const data = await res.json();
    if (!res.ok) return;
    allDrawings = data.drawings || [];
}

// 이미 다른 컬렉션 항목에 연결된 도면은 목록에서 제거한다 — _currentKeepDrawingId는 지금 수정 중인
// 항목이 이미 쓰고 있는 도면(자기 자신)이라 계속 선택 가능해야 하므로 예외로 남겨둔다.
function getAvailableDrawings() {
    const keepId  = _currentKeepDrawingId ? Number(_currentKeepDrawingId) : null;
    const usedIds = new Set(
        allPatterns
            .filter(p => p.drawing_id && Number(p.drawing_id) !== keepId)
            .map(p => Number(p.drawing_id))
    );
    return allDrawings.filter(d => !usedIds.has(Number(d.id)));
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
        <tr data-id="${p.id}">
            <td style="text-align:center;"><span class="lib-drag-handle"><i class="bi bi-grip-vertical"></i></span></td>
            <td>${p.image_path
                ? `<img class="lib-thumb" src="${esc(p.image_path)}" loading="lazy">`
                : `<div class="lib-thumb-empty"><i class="bi bi-image"></i></div>`}</td>
            <td style="color:var(--text-3);font-size:11px;font-family:monospace;">${/^[a-z]{3}(-[a-z]{2})?-\d{3}$/.test(p.slug||'') ? esc(p.slug.toUpperCase()) : '—'}</td>
            <td style="font-weight:600;">${esc(p.name_ko)}</td>
            <td style="color:var(--text-3);font-size:12px;">${p.pattern_category ? esc(categoryNames[p.pattern_category] || p.pattern_category) : '—'}</td>
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
    `).join('') || '<tr><td colspan="10" style="padding:40px;text-align:center;color:var(--text-3);">패턴이 없습니다.</td></tr>';
    bindDrag();
}

// 네이티브 HTML5 draggable은 <tr>에서 브라우저마다(특히 Safari) 아예 안 먹는 경우가 있어
// mousedown/mousemove/mouseup 기반으로 직접 구현한다.
let dragRow = null;

function bindDrag() {
    document.querySelectorAll('#libTbody .lib-drag-handle').forEach(handle => {
        handle.onmousedown = e => {
            e.preventDefault();
            dragRow = handle.closest('tr');
            dragRow.classList.add('dragging');
        };
    });
}

document.addEventListener('mousemove', e => {
    if (!dragRow) return;
    const tbody = document.getElementById('libTbody');
    const rows  = [...tbody.querySelectorAll('tr[data-id]')].filter(r => r !== dragRow);
    const after = rows.find(r => {
        const rect = r.getBoundingClientRect();
        return e.clientY < rect.top + rect.height / 2;
    });
    if (after) tbody.insertBefore(dragRow, after);
    else tbody.appendChild(dragRow);
});

document.addEventListener('mouseup', async () => {
    if (!dragRow) return;
    dragRow.classList.remove('dragging');
    const ids = [...document.getElementById('libTbody').querySelectorAll('tr[data-id]')].map(r => +r.dataset.id);
    dragRow = null;
    await fetch('/src/api/admin/collection.php', { method: 'PATCH', headers: headers(), body: JSON.stringify({ ids }) });
    await loadPatterns();
});

function openAddModal() {
    editingId = null; keywords = []; pendingImg = null;
    document.getElementById('libModalTitle').textContent = '패턴 추가';
    _currentKeepDrawingId = null;
    document.getElementById('lpName').value      = '';
    document.getElementById('lpDrawingId').value = '';
    updateDrawingButtonDisplay();
    document.getElementById('lpCategory').value  = '';
    document.getElementById('lpModifier').value  = '';
    document.getElementById('lpOrder').value     = '0';
    document.getElementById('lpImgFile').value   = '';
    document.getElementById('lpImgPreview').src  = '';
    document.getElementById('lpImgPreview').classList.remove('show');
    document.getElementById('libModalAlert').style.display = 'none';
    renderKeywords();
    document.getElementById('libModalOverlay').classList.add('open');
    document.getElementById('lpName').focus();
    updateCodePreview();
}

function openEditModal(p) {
    editingId = p.id; keywords = [...(p.keywords || [])]; pendingImg = null;
    document.getElementById('libModalTitle').textContent = '패턴 수정';
    _currentKeepDrawingId = p.drawing_id || null;
    document.getElementById('lpName').value      = p.name_ko;
    document.getElementById('lpDrawingId').value = p.drawing_id || '';
    updateDrawingButtonDisplay();
    document.getElementById('lpCategory').value  = p.pattern_category || '';
    // select가 아직 없는 값이면 빈 값 유지 (로딩 타이밍)

    // 기존 코드에서 수식어를 역추출해 select에 미리 선택해 둔다 (추가 모달과 동일한 구성)
    const m = /^[a-z]{3}(-([a-z]{2}))?-\d{3}$/.exec(p.slug || '');
    _editOriginalModifier = m ? (m[2] || '').toUpperCase() : '';
    _editOriginalCategory = p.pattern_category ? String(p.pattern_category) : '';
    _editOriginalSlug     = p.slug || '';
    document.getElementById('lpModifier').value = _editOriginalModifier;

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
    updateCodePreview();
}

/* ── 코드 미리보기 (모양·수식어 선택에 따라 실시간 갱신, 추가·수정 모달 공통) ──
   수정 모드에서 모양·수식어를 원래 값 그대로 두면 기존 코드(공유 URL)를 그대로 보여주고,
   실제로 바꾸면 저장 시 새로 채번될 코드를 미리 계산해 보여준다. */
let _previewSeq = 0;
async function updateCodePreview() {
    const codeEl = document.getElementById('libModalCode');
    const catId    = document.getElementById('lpCategory').value;
    const modifier = document.getElementById('lpModifier').value;

    if (!catId) { codeEl.textContent = '모양을 선택하면 코드가 표시됩니다'; return; }

    if (editingId !== null && catId === _editOriginalCategory && modifier === _editOriginalModifier) {
        codeEl.textContent = /^[a-z]{3}(-[a-z]{2})?-\d{3}$/.test(_editOriginalSlug)
            ? _editOriginalSlug.toUpperCase()
            : '코드 없음 — 메모가 노출됩니다';
        return;
    }

    const seq = ++_previewSeq;
    codeEl.textContent = '계산 중…';
    const params = new URLSearchParams({ preview_slug: '1', pattern_category: catId, code_modifier: modifier });
    const res  = await fetch(`/src/api/admin/collection.php?${params}`, { headers: headers() });
    const data = await res.json();
    if (seq !== _previewSeq) return; // 응답 도착 전에 선택이 또 바뀐 경우 무시
    codeEl.textContent = data.slug
        ? data.slug.toUpperCase() + (editingId !== null ? ' (저장하면 코드가 바뀝니다)' : '')
        : '코드 없는 모양 — 저장 시 임의 코드가 부여됩니다';
}

function closeModal() {
    document.getElementById('libModalOverlay').classList.remove('open');
    editingId = null; keywords = []; pendingImg = null;
}

/* ── 도면 선택 피커 (썸네일 그리드) ── */
function updateDrawingButtonDisplay() {
    const id      = document.getElementById('lpDrawingId').value;
    const thumbEl = document.getElementById('lpDrawingPickerThumb');
    const labelEl = document.getElementById('lpDrawingPickerLabel');
    const d       = id ? allDrawings.find(x => x.id == id) : null;

    thumbEl.innerHTML  = (d && d.thumbnail) ? `<img src="${esc(d.thumbnail)}">` : '<i class="bi bi-image"></i>';
    labelEl.textContent = d ? `[${d.type}] ${d.title}` : '— 연결 안함 —';
    labelEl.classList.toggle('lib-drawing-picker-label-empty', !d);
}

function openDrawingPicker() {
    document.getElementById('drawingPickerSearch').value = '';
    renderDrawingPickerGrid('');
    document.getElementById('drawingPickerOverlay').classList.add('open');
    document.getElementById('drawingPickerSearch').focus();
}

function closeDrawingPicker() {
    document.getElementById('drawingPickerOverlay').classList.remove('open');
}

function renderDrawingPickerGrid(q) {
    const available = getAvailableDrawings();
    const needle    = q.trim().toLowerCase();
    const filtered  = needle ? available.filter(d => d.title.toLowerCase().includes(needle)) : available;

    document.getElementById('drawingPickerGrid').innerHTML = filtered.map(d => `
        <div class="lib-drawing-picker-item" onclick="selectDrawing(${d.id})">
            ${d.thumbnail
                ? `<img src="${esc(d.thumbnail)}" loading="lazy">`
                : `<div class="lib-thumb-empty"><i class="bi bi-image"></i></div>`}
            <div class="lib-drawing-picker-item-label">[${esc(d.type)}] ${esc(d.title)}</div>
        </div>
    `).join('') || '<p style="grid-column:1/-1;text-align:center;color:var(--text-muted);padding:24px 0;">검색 결과가 없습니다.</p>';
}

function selectDrawing(id) {
    document.getElementById('lpDrawingId').value = id || '';
    updateDrawingButtonDisplay();

    if (!pendingImg) {
        const preview = document.getElementById('lpImgPreview');
        const d = id ? allDrawings.find(x => x.id == id) : null;
        if (d && d.thumbnail) {
            preview.src = d.thumbnail;
            preview.classList.add('show');
        } else if (!id && editingId === null) {
            preview.src = '';
            preview.classList.remove('show');
        }
    }
    closeDrawingPicker();
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

    document.getElementById('lpCategory').addEventListener('change', updateCodePreview);
    document.getElementById('lpModifier').addEventListener('change', updateCodePreview);

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
                // PNG로 내보내 투명 배경 유지 (JPEG로 내보내면 투명 영역이 브라우저에서도
                // 검게 채워짐 — 서버 saveLibraryImage()와 동일한 이유)
                pendingImg = canvas.toDataURL('image/png');
                document.getElementById('lpImgPreview').src = pendingImg;
                document.getElementById('lpImgPreview').classList.add('show');
            };
            img.src = ev.target.result;
        };
        reader.readAsDataURL(file);
    });

    document.getElementById('drawingPickerSearch').addEventListener('input', e => renderDrawingPickerGrid(e.target.value));
});

/* ── 저장 ── */
async function savePattern() {
    const btn  = document.getElementById('lpSaveBtn');
    const name = document.getElementById('lpName').value.trim();

    const body = {
        name_ko:          name,
        drawing_id:       parseInt(document.getElementById('lpDrawingId').value) || 0,
        pattern_category: parseInt(document.getElementById('lpCategory').value) || 0,
        sort_order:       parseInt(document.getElementById('lpOrder').value) || 0,
        keywords:         keywords,
        code_modifier:    document.getElementById('lpModifier').value.trim(),
    };
    if (pendingImg) body.image = pendingImg;
    if (editingId !== null) {
        body.id        = editingId;
        body.is_active = 1;
    }

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
    const p    = allPatterns.find(x => x.id == id);
    const name = p ? p.name_ko : '';
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

document.getElementById('drawingPickerOverlay').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeDrawingPicker();
});

document.addEventListener('DOMContentLoaded', init);
window.addEventListener('pmokAuthChanged', init);
