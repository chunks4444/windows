const API = '/src/api/admin/studio_cards.php';
function _h() { return { 'Authorization': 'Bearer ' + localStorage.getItem('pmok_auth_token'), 'Content-Type': 'application/json' }; }

let cards = [], dragSrc;

async function loadCards() {
    const res  = await fetch(API, { headers: _h() });
    const data = await res.json();
    if (!res.ok) { document.getElementById('scAuthWall').style.display = ''; return; }
    cards = data.cards || [];
    if (cards.length === 0) {
        await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'init_defaults' }) });
        return loadCards();
    }
    render();
}

const ENGINE_LABELS = { classic: 'Classic', square: 'Square', cross: 'Cross', triangle: 'Triangle', diamond: 'Diamond', hexagon: 'Hexagon' };

function render() {
    document.getElementById('scBody').innerHTML = cards.map(c => `
        <tr data-id="${c.id}" draggable="true">
            <td style="text-align:center;cursor:grab;"><i class="bi bi-grip-vertical" style="color:var(--text-3);"></i></td>
            <td>${c.image_url
                ? `<img src="${esc(c.image_url)}" style="width:72px;height:48px;object-fit:cover;border-radius:4px;">`
                : `<div style="width:72px;height:48px;background:var(--page-bg);border-radius:4px;display:flex;align-items:center;justify-content:center;color:var(--text-3);font-size:18px;"><i class="bi bi-image"></i></div>`}</td>
            <td><strong>${esc(ENGINE_LABELS[c.engine_key] || c.engine_key)}</strong></td>
            <td>${esc(c.title)}</td>
            <td style="color:var(--text-3);font-size:var(--fs-13);max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${esc(c.description || '')}</td>
            <td><span class="${c.is_active ? 'adm-active-badge' : 'adm-withdrawn-badge'}">${c.is_active ? '노출' : '숨김'}</span></td>
            <td>
                <div style="display:flex;gap:6px;">
                    <button class="adm-edit-btn" style="height:28px;padding:0 10px;font-size:var(--fs-13);" onclick="openModal(${c.id})">수정</button>
                    <button class="adm-withdraw-btn" onclick="toggleCard(${c.id})">${c.is_active ? '숨김' : '표시'}</button>
                </div>
            </td>
        </tr>`).join('');
    bindDrag();
}

function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function openModal(id) {
    const c = cards.find(x => x.id == id);
    if (!c) return;
    document.getElementById('scId').value          = c.id;
    document.getElementById('scEngineKey').value   = c.engine_key;
    document.getElementById('scEngineLabel').textContent = ENGINE_LABELS[c.engine_key] || c.engine_key;
    document.getElementById('scTitle').value       = c.title;
    document.getElementById('scDesc').value        = c.description || '';
    document.getElementById('scImageUrl').value    = c.image_url || '';
    document.getElementById('scImgFile').value     = '';
    window._scImageData = null;

    const prev = document.getElementById('scImgPreview');
    const removeBtn = document.getElementById('scRemoveImgBtn');
    if (c.image_url) { prev.src = c.image_url; prev.classList.add('show'); removeBtn.style.display = ''; }
    else { prev.src = ''; prev.classList.remove('show'); removeBtn.style.display = 'none'; }

    document.getElementById('scModalOverlay').classList.add('open');
}

function removeImage() {
    window._scImageData = null;
    document.getElementById('scImageUrl').value = '';
    document.getElementById('scImgFile').value  = '';
    const prev = document.getElementById('scImgPreview');
    prev.src = ''; prev.classList.remove('show');
    document.getElementById('scRemoveImgBtn').style.display = 'none';
}

function closeModal() {
    document.getElementById('scModalOverlay').classList.remove('open');
}

function previewImage(input) {
    const file = input.files[0];
    if (!file) return;
    if (!['image/jpeg', 'image/png'].includes(file.type)) { alert('PNG 또는 JPG 파일만 업로드할 수 있습니다.'); input.value = ''; return; }
    const reader = new FileReader();
    reader.onload = e => {
        const img = new Image();
        img.onload = () => {
            const MAX = 1200;
            let w = img.width, h = img.height;
            if (w > MAX || h > MAX) { const s = Math.min(MAX/w, MAX/h); w = Math.round(w*s); h = Math.round(h*s); }
            const canvas = document.createElement('canvas');
            canvas.width = w; canvas.height = h;
            canvas.getContext('2d').drawImage(img, 0, 0, w, h);
            const dataUrl = canvas.toDataURL('image/jpeg', 0.88);
            window._scImageData = dataUrl;
            const prev = document.getElementById('scImgPreview');
            prev.src = dataUrl; prev.classList.add('show');
            document.getElementById('scImageUrl').value = '';
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

async function saveCard() {
    const body = {
        action:      'save',
        id:          parseInt(document.getElementById('scId').value) || 0,
        engine_key:  document.getElementById('scEngineKey').value,
        title:       document.getElementById('scTitle').value.trim(),
        description: document.getElementById('scDesc').value.trim(),
        image_url:   document.getElementById('scImageUrl').value.trim(),
    };
    if (window._scImageData) body.image_data = window._scImageData;
    const res  = await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify(body) });
    const data = await res.json();
    if (data.ok) { closeModal(); loadCards(); }
    else alert(data.error || '저장 실패');
}

async function toggleCard(id) {
    await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'toggle', id }) });
    loadCards();
}

function bindDrag() {
    document.querySelectorAll('#scBody tr').forEach(tr => {
        tr.addEventListener('dragstart', () => { dragSrc = tr; tr.classList.add('dragging'); });
        tr.addEventListener('dragend',   () => tr.classList.remove('dragging'));
        tr.addEventListener('dragover',  e => { e.preventDefault(); tr.classList.add('drag-over'); });
        tr.addEventListener('dragleave', () => tr.classList.remove('drag-over'));
        tr.addEventListener('drop', async e => {
            e.preventDefault(); tr.classList.remove('drag-over');
            if (dragSrc === tr) return;
            tr.parentNode.insertBefore(dragSrc, tr.nextSibling);
            const ids = [...tr.parentNode.querySelectorAll('tr')].map(r => +r.dataset.id);
            await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'reorder', ids }) });
            await loadCards();
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('scModalOverlay').addEventListener('click', e => {
        if (e.target === e.currentTarget) closeModal();
    });
    const user = JSON.parse(localStorage.getItem('pmok_auth_user') || 'null');
    if (!user || user.role !== 's') { document.getElementById('scAuthWall').style.display = ''; return; }
    document.getElementById('scPage').style.display = '';
    loadCards();
});
