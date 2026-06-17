const API = '/src/api/admin/works.php';
function _h() { return { 'Authorization': 'Bearer ' + localStorage.getItem('pmok_auth_token'), 'Content-Type': 'application/json' }; }

let works = [], dragSrc;

async function loadWorks() {
    const res  = await fetch(API, { headers: _h() });
    const data = await res.json();
    if (!res.ok) { document.getElementById('worksAuthWall').style.display = ''; return; }
    works = data.works || [];
    render();
}

function render() {
    document.getElementById('worksBody').innerHTML = works.map(w => `
        <tr data-id="${w.id}" draggable="true">
            <td style="text-align:center;"><span class="drag-handle"><i class="bi bi-grip-vertical"></i></span></td>
            <td>${w.image_url
                ? `<img class="work-thumb" src="${esc(w.image_url)}" alt="">`
                : `<div class="work-thumb-empty"><i class="bi bi-image"></i></div>`}</td>
            <td><strong>${esc(w.title)}</strong></td>
            <td style="color:var(--text-3);font-size:12px;">${esc(w.description)}</td>
            <td><span class="${w.is_active ? 'adm-active-badge' : 'adm-withdrawn-badge'}">${w.is_active ? '노출' : '숨김'}</span></td>
            <td>
                <div class="adm-action-cell">
                    <button class="adm-edit-btn" style="height:28px;padding:0 10px;font-size:12px;" onclick="openModal(${w.id})">수정</button>
                    <button class="adm-withdraw-btn" onclick="toggleWork(${w.id})">${w.is_active ? '숨김' : '표시'}</button>
                    <button class="adm-withdraw-btn" style="background:#c00;color:#fff;" onclick="deleteWork(${w.id})">삭제</button>
                </div>
            </td>
        </tr>`).join('');
    bindDrag();
}

function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function openModal(id) {
    const w = id ? works.find(x => x.id == id) : null;
    document.getElementById('worksModalTitle').textContent = w ? '작품 수정' : '작품 추가';
    document.getElementById('workId').value          = w?.id ?? '';
    document.getElementById('workTitle').value       = w?.title ?? '';
    document.getElementById('workDescription').value = w?.description ?? '';
    document.getElementById('workImageUrl').value    = w?.image_url ?? '';
    const prev = document.getElementById('workImgPreview');
    if (w?.image_url) { prev.src = w.image_url; prev.classList.add('show'); }
    else              { prev.src = ''; prev.classList.remove('show'); }
    document.getElementById('workImgFile').value = '';
    document.getElementById('workPanelBg').value    = w?.panel_bg    || '#111111';
    document.getElementById('workTitleColor').value = w?.title_color || '#ffffff';
    document.getElementById('workDescColor').value  = w?.desc_color  || '#888888';
    window._workImageData = null;
    document.getElementById('worksModalOverlay').classList.add('open');
}

function closeModal() {
    document.getElementById('worksModalOverlay').classList.remove('open');
}

function previewImage(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const img = new Image();
        img.onload = () => {
            const MAX = 2400;
            let w = img.width, h = img.height;
            if (w > MAX || h > MAX) {
                const scale = Math.min(MAX / w, MAX / h);
                w = Math.round(w * scale); h = Math.round(h * scale);
            }
            const canvas = document.createElement('canvas');
            canvas.width = w; canvas.height = h;
            canvas.getContext('2d').drawImage(img, 0, 0, w, h);
            const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
            window._workImageData = dataUrl;
            const prev = document.getElementById('workImgPreview');
            prev.src = dataUrl; prev.classList.add('show');
            document.getElementById('workImageUrl').value = '';
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

async function saveWork() {
    const body = {
        action:      'save',
        id:          parseInt(document.getElementById('workId').value) || 0,
        title:       document.getElementById('workTitle').value.trim(),
        description: document.getElementById('workDescription').value.trim(),
        image_url:   document.getElementById('workImageUrl').value.trim(),
        panel_bg:    document.getElementById('workPanelBg').value,
        title_color: document.getElementById('workTitleColor').value,
        desc_color:  document.getElementById('workDescColor').value,
    };
    if (window._workImageData) body.image_data = window._workImageData;
    const res  = await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify(body) });
    const data = await res.json();
    if (data.ok) { closeModal(); loadWorks(); }
    else alert(data.error || '저장 실패');
}

async function deleteWork(id) {
    if (!confirm('이 작품을 삭제할까요?')) return;
    await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'delete', id }) });
    loadWorks();
}

async function toggleWork(id) {
    await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'toggle', id }) });
    loadWorks();
}

function bindDrag() {
    document.querySelectorAll('#worksBody tr').forEach(tr => {
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
            await loadWorks();
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('worksModalOverlay').addEventListener('click', e => {
        if (e.target === e.currentTarget) closeModal();
    });
    const user = JSON.parse(localStorage.getItem('pmok_auth_user') || 'null');
    if (!user || user.role !== 's') { document.getElementById('worksAuthWall').style.display = ''; return; }
    document.getElementById('worksPage').style.display = '';
    loadWorks();
});
