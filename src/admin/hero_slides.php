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
        .hs-thumb { width:96px; height:54px; object-fit:cover; border-radius:6px; background:var(--input-bg); }
        .hs-thumb-empty { width:96px; height:54px; border-radius:6px; background:var(--input-bg); display:flex; align-items:center; justify-content:center; color:var(--text-3); font-size:18px; }
        .hs-img-preview { width:100%; max-height:200px; object-fit:cover; border-radius:8px; background:var(--input-bg); display:none; margin-bottom:8px; }
        .hs-img-preview.show { display:block; }
        .hs-upload-label { display:block; padding:10px; border:1.5px dashed var(--border-md); border-radius:8px; text-align:center; cursor:pointer; color:var(--text-3); font-size:13px; margin-bottom:6px; }
        .hs-upload-label:hover { border-color:var(--teal); color:var(--teal); }
        .drag-handle { cursor:grab; color:var(--text-3); }
        .drag-handle:active { cursor:grabbing; }
        tr.dragging { opacity:.4; }
        tr.drag-over td { background:var(--accent-bg); }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="hsAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="hsPage" style="display:none;">
    <div class="db-header">
        <h1 class="db-title">히어로 슬라이드 관리</h1>
        <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="openModal()">
            <i class="bi bi-plus-lg"></i> 추가
        </button>
    </div>
    <p style="font-size:12px;color:var(--text-3);margin:-8px 0 16px;">행을 드래그해 순서를 변경할 수 있습니다.</p>

    <div class="adm-table-wrap">
        <table id="hsTable">
            <thead>
                <tr>
                    <th style="width:32px;"></th>
                    <th style="width:104px;">이미지</th>
                    <th>제목</th>
                    <th>설명</th>
                    <th style="width:72px;">상태</th>
                    <th style="width:160px;"></th>
                </tr>
            </thead>
            <tbody id="hsBody"></tbody>
        </table>
    </div>
</div>

<!-- 편집 모달 -->
<div class="adm-modal-overlay" id="hsModalOverlay">
    <div class="adm-modal" style="max-width:520px;">
        <div class="adm-modal-head">
            <h3 id="hsModalTitle">슬라이드 추가</h3>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <input type="hidden" id="hsId">
            <div class="adm-mfield">
                <label>제목</label>
                <input id="hsTitle" type="text" placeholder="예: 빛과 바람의 길을 설계하세요" maxlength="120">
            </div>
            <div class="adm-mfield">
                <label>설명</label>
                <input id="hsSubtitle" type="text" placeholder="예: 나만의 창호를 브라우저에서 직접 디자인합니다" maxlength="255">
            </div>
            <div class="adm-mfield">
                <label>이미지</label>
                <img id="hsImgPreview" class="hs-img-preview" src="" alt="">
                <label class="hs-upload-label" for="hsImgFile">
                    <i class="bi bi-upload"></i> 이미지 업로드
                </label>
                <input type="file" id="hsImgFile" accept="image/*" style="display:none;" onchange="previewImage(this)">
                <input id="hsImageUrl" type="text" placeholder="또는 https://... URL 직접 입력"
                    style="height:38px;padding:0 10px;border:1px solid var(--border-md);border-radius:var(--r-sm);background:var(--input-bg);font-family:inherit;font-size:13px;color:var(--text-1);outline:none;width:100%;">
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" onclick="saveSlide()">저장</button>
        </div>
    </div>
</div>

<script>
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
</script>
</body>
</html>
