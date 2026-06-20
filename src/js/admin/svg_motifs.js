const API = '/src/api/admin/svg_motifs.php';
function _h() { return { 'Authorization': 'Bearer ' + localStorage.getItem('pmok_auth_token'), 'Content-Type': 'application/json' }; }

let motifs = [], dragSrc;

async function loadMotifs() {
    const res  = await fetch(API, { headers: _h() });
    const data = await res.json();
    if (!res.ok) { document.getElementById('svgMotifsAuthWall').style.display = ''; return; }
    motifs = data.motifs || [];
    render();
}

function render() {
    document.getElementById('svgMotifsBody').innerHTML = motifs.map(m => `
        <tr data-id="${m.id}" draggable="true">
            <td style="text-align:center;"><span class="drag-handle"><i class="bi bi-grip-vertical"></i></span></td>
            <td>${m.svg_url
                ? `<img class="motif-thumb" src="${esc(m.svg_url)}" alt="">`
                : `<div class="motif-thumb-empty"><i class="bi bi-flower1"></i></div>`}</td>
            <td><strong>${esc(m.name)}</strong></td>
            <td style="color:var(--text-3);font-size:12px;">${esc(m.category || '')}</td>
            <td><span class="${m.is_active ? 'adm-active-badge' : 'adm-withdrawn-badge'}">${m.is_active ? '노출' : '숨김'}</span></td>
            <td>
                <div class="adm-action-cell">
                    <button class="adm-edit-btn" style="height:28px;padding:0 10px;font-size:12px;" onclick="openModal(${m.id})">수정</button>
                    <button class="adm-withdraw-btn" onclick="toggleMotif(${m.id})">${m.is_active ? '숨김' : '표시'}</button>
                    <button class="adm-withdraw-btn" style="background:#c00;color:#fff;" onclick="deleteMotif(${m.id})">삭제</button>
                </div>
            </td>
        </tr>`).join('');
    bindDrag();
}

function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function openModal(id) {
    const m = id ? motifs.find(x => x.id == id) : null;
    document.getElementById('svgMotifModalTitle').textContent = m ? '문양 수정' : '문양 추가';
    document.getElementById('motifId').value       = m?.id ?? '';
    document.getElementById('motifName').value     = m?.name ?? '';
    document.getElementById('motifCategory').value = m?.category ?? '';
    const prev = document.getElementById('motifPreview');
    if (m?.svg_url) { prev.src = m.svg_url; prev.classList.add('show'); }
    else            { prev.src = ''; prev.classList.remove('show'); }
    document.getElementById('motifFile').value = '';
    window._motifSvgData = null;
    document.getElementById('svgMotifModalOverlay').classList.add('open');
}

function closeModal() {
    document.getElementById('svgMotifModalOverlay').classList.remove('open');
}

function previewMotif(input) {
    const file = input.files[0];
    if (!file) return;
    if (!/\.svg$/i.test(file.name) && file.type !== 'image/svg+xml') {
        alert('SVG 파일만 업로드할 수 있습니다.');
        input.value = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = e => {
        window._motifSvgData = e.target.result;
        const prev = document.getElementById('motifPreview');
        prev.src = e.target.result;
        prev.classList.add('show');
    };
    reader.readAsDataURL(file);
}

async function saveMotif() {
    const body = {
        action:   'save',
        id:       parseInt(document.getElementById('motifId').value) || 0,
        name:     document.getElementById('motifName').value.trim(),
        category: document.getElementById('motifCategory').value.trim(),
    };
    if (!body.name) { alert('이름을 입력해주세요.'); return; }
    if (window._motifSvgData) body.svg_data = window._motifSvgData;
    else if (!body.id) { alert('SVG 파일을 업로드해주세요.'); return; }
    const res  = await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify(body) });
    const data = await res.json();
    if (data.ok) { closeModal(); loadMotifs(); }
    else alert(data.error || '저장 실패');
}

async function deleteMotif(id) {
    if (!confirm('이 문양을 삭제할까요?')) return;
    await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'delete', id }) });
    loadMotifs();
}

async function toggleMotif(id) {
    await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'toggle', id }) });
    loadMotifs();
}

function bindDrag() {
    document.querySelectorAll('#svgMotifsBody tr').forEach(tr => {
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
            await loadMotifs();
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('svgMotifModalOverlay').addEventListener('click', e => {
        if (e.target === e.currentTarget) closeModal();
    });
    const user = JSON.parse(localStorage.getItem('pmok_auth_user') || 'null');
    if (!user || user.role !== 's') { document.getElementById('svgMotifsAuthWall').style.display = ''; return; }
    document.getElementById('svgMotifsPage').style.display = '';
    loadMotifs();
});
