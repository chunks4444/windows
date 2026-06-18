const API = '/src/api/admin/hero_slides.php';
function _h() { return { 'Authorization': 'Bearer ' + localStorage.getItem('pmok_auth_token'), 'Content-Type': 'application/json' }; }

let slides = [], dragSrc;

async function loadSlides() {
    const res  = await fetch(API, { headers: _h() });
    const data = await res.json();
    if (!res.ok) { document.getElementById('hsAuthWall').style.display = ''; return; }
    slides = data.slides || [];
    render();
}

function render() {
    document.getElementById('hsBody').innerHTML = slides.map(s => `
        <tr data-id="${s.id}" draggable="true">
            <td style="text-align:center;"><span class="drag-handle"><i class="bi bi-grip-vertical"></i></span></td>
            <td>${s.image_url
                ? `<img class="hs-thumb" src="${esc(s.image_url)}" alt="">`
                : `<div class="hs-thumb-empty"><i class="bi bi-image"></i></div>`}</td>
            <td><strong>${esc(s.title)}</strong></td>
            <td style="color:var(--text-3);font-size:12px;">${esc(s.subtitle)}</td>
            <td><span class="${s.is_active ? 'adm-active-badge' : 'adm-withdrawn-badge'}">${s.is_active ? '노출' : '숨김'}</span></td>
            <td>
                <div class="adm-action-cell">
                    <button class="adm-edit-btn" style="height:28px;padding:0 10px;font-size:12px;" onclick="openModal(${s.id})">수정</button>
                    <button class="adm-withdraw-btn" onclick="toggleSlide(${s.id})">${s.is_active ? '숨김' : '표시'}</button>
                    <button class="adm-withdraw-btn" style="background:#c00;color:#fff;" onclick="deleteSlide(${s.id},'${esc(s.title)}')">삭제</button>
                </div>
            </td>
        </tr>`).join('');
    bindDrag();
}

function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function openModal(id) {
    const s = id ? slides.find(x => x.id == id) : null;
    document.getElementById('hsModalTitle').textContent = s ? '슬라이드 수정' : '슬라이드 추가';
    document.getElementById('hsId').value       = s?.id ?? '';
    document.getElementById('hsTitle').value    = s?.title ?? '';
    document.getElementById('hsSubtitle').value = s?.subtitle ?? '';
    document.getElementById('hsImageUrl').value = s?.image_url ?? '';
    const prev = document.getElementById('hsImgPreview');
    if (s?.image_url) { prev.src = s.image_url; prev.classList.add('show'); }
    else              { prev.src = ''; prev.classList.remove('show'); }
    document.getElementById('hsImgFile').value = '';
    window._hsImageData = null;
    document.getElementById('hsModalOverlay').classList.add('open');
}

function closeModal() {
    document.getElementById('hsModalOverlay').classList.remove('open');
}

function previewImage(input) {
    const file = input.files[0];
    if (!file) return;
    if (!['image/jpeg', 'image/png'].includes(file.type)) { alert('PNG 또는 JPG 파일만 업로드할 수 있습니다.'); input.value = ''; return; }
    const reader = new FileReader();
    reader.onload = e => {
        const img = new Image();
        img.onload = () => {
            const MAX_W = 1920, MAX_H = 1080;
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
            window._hsImageData = dataUrl;
            const prev = document.getElementById('hsImgPreview');
            prev.src = dataUrl;
            prev.classList.add('show');
            document.getElementById('hsImageUrl').value = '';
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

async function saveSlide() {
    const body = {
        action:    'save',
        id:        parseInt(document.getElementById('hsId').value) || 0,
        title:     document.getElementById('hsTitle').value.trim(),
        subtitle:  document.getElementById('hsSubtitle').value.trim(),
        image_url: document.getElementById('hsImageUrl').value.trim(),
        is_active: 1,
    };
    if (window._hsImageData) body.image_data = window._hsImageData;
    const res  = await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify(body) });
    const data = await res.json();
    if (data.ok) { closeModal(); loadSlides(); }
    else alert(data.error || '저장 실패');
}

async function deleteSlide(id, title) {
    if (!confirm(`"${title}" 슬라이드를 삭제할까요?`)) return;
    await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'delete', id }) });
    loadSlides();
}

async function toggleSlide(id) {
    await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'toggle', id }) });
    loadSlides();
}

function bindDrag() {
    document.querySelectorAll('#hsBody tr').forEach(tr => {
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
            await loadSlides();
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('hsModalOverlay').addEventListener('click', e => {
        if (e.target === e.currentTarget) closeModal();
    });
    const user = JSON.parse(localStorage.getItem('pmok_auth_user') || 'null');
    if (!user || user.role !== 's') { document.getElementById('hsAuthWall').style.display = ''; return; }
    document.getElementById('hsPage').style.display = '';
    loadSlides();
});
