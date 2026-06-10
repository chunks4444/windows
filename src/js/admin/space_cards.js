const API = '/src/api/admin/space_cards.php';
function _h() { return { 'Authorization': 'Bearer ' + localStorage.getItem('pmok_auth_token'), 'Content-Type': 'application/json' }; }

let cards = [], dragSrc;

async function loadCards() {
    const res  = await fetch(API, { headers: _h() });
    const data = await res.json();
    if (!res.ok) { document.getElementById('scAuthWall').style.display = ''; return; }
    cards = data.cards || [];
    render();
}

function render() {
    document.getElementById('scBody').innerHTML = cards.map(c => `
        <tr data-id="${c.id}" draggable="true">
            <td style="text-align:center;"><span class="drag-handle"><i class="bi bi-grip-vertical"></i></span></td>
            <td>${c.image_url
                ? `<img class="sc-thumb" src="${esc(c.image_url)}" alt="">`
                : `<div class="sc-thumb-empty"><i class="bi bi-image"></i></div>`}</td>
            <td><strong>${esc(c.label)}</strong></td>
            <td style="color:var(--text-3);">${esc(c.collection_query)}</td>
            <td><span class="${c.is_active ? 'adm-active-badge' : 'adm-withdrawn-badge'}">${c.is_active ? '노출' : '숨김'}</span></td>
            <td>
                <div class="adm-action-cell">
                    <button class="adm-edit-btn" style="height:28px;padding:0 10px;font-size:12px;" onclick="openModal(${c.id})">수정</button>
                    <button class="adm-withdraw-btn" onclick="toggleCard(${c.id})">${c.is_active ? '숨김' : '표시'}</button>
                    <button class="adm-withdraw-btn" style="background:#c00;color:#fff;" onclick="deleteCard(${c.id},'${esc(c.label)}')">삭제</button>
                </div>
            </td>
        </tr>`).join('');
    bindDrag();
}

function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function openModal(id) {
    const c = id ? cards.find(x => x.id == id) : null;
    document.getElementById('scModalTitle').textContent = c ? '공간 카드 수정' : '공간 카드 추가';
    document.getElementById('scId').value       = c?.id ?? '';
    document.getElementById('scLabel').value    = c?.label ?? '';
    document.getElementById('scQuery').value    = c?.collection_query ?? '';
    document.getElementById('scImageUrl').value = c?.image_url ?? '';
    const prev = document.getElementById('scImgPreview');
    if (c?.image_url) { prev.src = c.image_url; prev.classList.add('show'); }
    else              { prev.src = ''; prev.classList.remove('show'); }
    document.getElementById('scImgFile').value = '';
    window._scImageData = null;
    document.getElementById('scModalOverlay').classList.add('open');
}

function closeModal() {
    document.getElementById('scModalOverlay').classList.remove('open');
}

function previewImage(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const img = new Image();
        img.onload = () => {
            const MAX_W = 1200, MAX_H = 800;
            let w = img.width, h = img.height;
            if (w > MAX_W || h > MAX_H) {
                const scale = Math.min(MAX_W / w, MAX_H / h);
                w = Math.round(w * scale);
                h = Math.round(h * scale);
            }
            const canvas = document.createElement('canvas');
            canvas.width = w; canvas.height = h;
            canvas.getContext('2d').drawImage(img, 0, 0, w, h);
            const dataUrl = canvas.toDataURL('image/jpeg', 0.88);
            window._scImageData = dataUrl;
            const prev = document.getElementById('scImgPreview');
            prev.src = dataUrl;
            prev.classList.add('show');
            document.getElementById('scImageUrl').value = '';
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

async function saveCard() {
    const body = {
        action:           'save',
        id:               parseInt(document.getElementById('scId').value) || 0,
        label:            document.getElementById('scLabel').value.trim(),
        image_url:        document.getElementById('scImageUrl').value.trim(),
        collection_query: document.getElementById('scQuery').value.trim(),
        is_active:        1,
    };
    if (window._scImageData) body.image_data = window._scImageData;
    const res  = await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify(body) });
    const data = await res.json();
    if (data.ok) { closeModal(); loadCards(); }
    else alert(data.error || '저장 실패');
}

async function deleteCard(id, label) {
    if (!confirm(`"${label}" 카드를 삭제할까요?`)) return;
    await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'delete', id }) });
    loadCards();
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
            e.preventDefault();
            tr.classList.remove('drag-over');
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
