let editingId = null;

function token()   { return localStorage.getItem('pmok_auth_token'); }
function headers() { return { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() }; }

async function init() {
    const user = authGetUser();
    if (!user || user.role !== 's') { location.href = '/'; return; }
    document.getElementById('metaPage').style.display = '';
    await loadPages();
}

async function loadPages() {
    const res  = await fetch('/src/api/admin/meta.php', { headers: headers() });
    const data = await res.json();
    if (!res.ok) return;
    renderTable(data.pages);
}

function renderTable(pages) {
    const sorted = [...pages].sort((a, b) => (a.path === '__default__' ? -1 : b.path === '__default__' ? 1 : 0));
    document.getElementById('metaTbody').innerHTML = sorted.map(p => `
        <tr${p.path === '__default__' ? ' style="background:var(--accent-bg);"' : ''}>
            <td style="font-family:monospace;font-size:12px;white-space:nowrap;">${p.path === '__default__' ? '🌐 사이트 기본값' : esc(p.path)}</td>
            <td style="font-size:12px;">${esc(p.title) || '<span style="color:var(--text-3);">—</span>'}</td>
            <td style="font-size:12px;color:var(--text-2);">${truncate(esc(p.description), 60)}</td>
            <td style="font-size:12px;color:var(--text-3);">${truncate(esc(p.keywords), 40)}</td>
            <td><div class="adm-action-cell">
                <button class="adm-edit-btn" onclick='openEditModal(${JSON.stringify(p)})'>수정</button>
                ${p.path === '__default__' ? '' : `<button class="adm-withdraw-btn" style="background:#c00;color:#fff;" onclick="deleteMeta(${p.id}, '${esc(p.path)}')">삭제</button>`}
            </div></td>
        </tr>
    `).join('') || '<tr><td colspan="4" style="padding:40px;text-align:center;color:var(--text-3);">등록된 메타가 없습니다.</td></tr>';
}

function openAddModal() {
    editingId = null;
    document.getElementById('metaModalTitle').textContent = '메타 추가';
    document.getElementById('metaPath').value      = '';
    document.getElementById('metaPath').readOnly   = false;
    document.getElementById('metaTitle').value     = '';
    document.getElementById('metaDesc').value      = '';
    document.getElementById('metaKeywords').value  = '';
    document.getElementById('metaOgImage').value   = '';
    document.getElementById('metaOgImgFile').value = '';
    window._metaOgImageData = null;
    const prev = document.getElementById('metaOgImgPreview');
    prev.src = ''; prev.classList.remove('show');
    document.getElementById('metaModalAlert').style.display = 'none';
    updateCount('metaTitle', 'cntTitle', 60);
    updateCount('metaDesc',  'cntDesc',  160);
    document.getElementById('metaModalOverlay').classList.add('open');
    document.getElementById('metaPath').focus();
}

function openEditModal(p) {
    editingId = p.id;
    document.getElementById('metaModalTitle').textContent = '메타 수정';
    document.getElementById('metaPath').value      = p.path;
    document.getElementById('metaPath').readOnly   = (p.path === '__default__');
    document.getElementById('metaTitle').value     = p.title;
    document.getElementById('metaDesc').value      = p.description;
    document.getElementById('metaKeywords').value  = p.keywords;
    document.getElementById('metaOgImage').value   = p.og_image;
    document.getElementById('metaOgImgFile').value = '';
    window._metaOgImageData = null;
    const prev = document.getElementById('metaOgImgPreview');
    if (p.og_image) { prev.src = p.og_image; prev.classList.add('show'); }
    else            { prev.src = ''; prev.classList.remove('show'); }
    document.getElementById('metaModalAlert').style.display = 'none';
    updateCount('metaTitle', 'cntTitle', 60);
    updateCount('metaDesc',  'cntDesc',  160);
    document.getElementById('metaModalOverlay').classList.add('open');
    document.getElementById('metaTitle').focus();
}

function previewOgImage(input) {
    const file = input.files[0];
    if (!file) return;
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) { alert('PNG, JPG 또는 WEBP 파일만 업로드할 수 있습니다.'); input.value = ''; return; }
    const reader = new FileReader();
    reader.onload = e => {
        const img = new Image();
        img.onload = () => {
            const MAX_W = 1200, MAX_H = 630;
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
            window._metaOgImageData = dataUrl;
            const prev = document.getElementById('metaOgImgPreview');
            prev.src = dataUrl;
            prev.classList.add('show');
            document.getElementById('metaOgImage').value = '';
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

function closeModal() {
    document.getElementById('metaModalOverlay').classList.remove('open');
    editingId = null;
}

async function saveMeta() {
    const btn  = document.getElementById('metaSaveBtn');
    const path = document.getElementById('metaPath').value.trim();
    if (!path) { showAlert('경로를 입력하세요.'); return; }

    btn.disabled = true; btn.textContent = '저장 중…';
    try {
        const isEdit = editingId !== null;
        const body   = {
            title:       document.getElementById('metaTitle').value.trim(),
            description: document.getElementById('metaDesc').value.trim(),
            keywords:    document.getElementById('metaKeywords').value.trim(),
            og_image:    document.getElementById('metaOgImage').value.trim(),
        };
        body.path = path;
        if (isEdit) body.id = editingId;
        if (window._metaOgImageData) body.og_image_data = window._metaOgImageData;

        const res  = await fetch('/src/api/admin/meta.php', {
            method:  isEdit ? 'PUT' : 'POST',
            headers: headers(),
            body:    JSON.stringify(body),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || '오류가 발생했습니다.');
        closeModal();
        await loadPages();
    } catch (err) {
        showAlert(err.message);
    } finally {
        btn.disabled = false; btn.textContent = '저장';
    }
}

async function deleteMeta(id, path) {
    if (!confirm(`"${path}" 메타를 삭제하시겠습니까?`)) return;
    const res = await fetch('/src/api/admin/meta.php', {
        method: 'DELETE', headers: headers(), body: JSON.stringify({ id }),
    });
    if (res.ok) await loadPages();
}

function updateCount(fieldId, countId, limit) {
    const len = document.getElementById(fieldId).value.length;
    const el  = document.getElementById(countId);
    el.textContent = `${len} / ${limit}자`;
    el.style.color = len > limit ? '#c00' : 'var(--text-3)';
}

function showAlert(msg) {
    const el = document.getElementById('metaModalAlert');
    el.textContent = msg; el.className = 'adm-alert adm-alert-error'; el.style.display = '';
}

function truncate(str, n) { return str.length > n ? str.slice(0, n) + '…' : str; }

function esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

document.getElementById('metaModalOverlay').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeModal();
});

document.addEventListener('DOMContentLoaded', init);
window.addEventListener('pmokAuthChanged', init);
