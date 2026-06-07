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
        .sc-thumb { width:72px; height:48px; object-fit:cover; border-radius:6px; background:var(--input-bg); }
        .sc-thumb-empty { width:72px; height:48px; border-radius:6px; background:var(--input-bg); display:flex; align-items:center; justify-content:center; color:var(--text-3); font-size:18px; }
        .sc-img-preview { width:100%; max-height:180px; object-fit:cover; border-radius:8px; background:var(--input-bg); display:none; margin-bottom:8px; }
        .sc-img-preview.show { display:block; }
        .sc-upload-label { display:block; padding:10px; border:1.5px dashed var(--border-md); border-radius:8px; text-align:center; cursor:pointer; color:var(--text-3); font-size:13px; margin-bottom:6px; }
        .sc-upload-label:hover { border-color:var(--teal); color:var(--teal); }
        .drag-handle { cursor:grab; color:var(--text-3); }
        .drag-handle:active { cursor:grabbing; }
        tr.dragging { opacity:.4; }
        tr.drag-over td { background:var(--accent-bg); }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="scAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="scPage" style="display:none;">
    <div class="db-header">
        <h1 class="db-title">공간 카드 관리</h1>
        <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="openModal()">
            <i class="bi bi-plus-lg"></i> 추가
        </button>
    </div>
    <p style="font-size:12px;color:var(--text-3);margin:-8px 0 16px;">행을 드래그해 순서를 변경할 수 있습니다.</p>

    <div class="adm-table-wrap">
        <table id="scTable">
            <thead>
                <tr>
                    <th style="width:32px;"></th>
                    <th style="width:80px;">이미지</th>
                    <th>라벨</th>
                    <th>컬렉션 검색어</th>
                    <th style="width:72px;">상태</th>
                    <th style="width:160px;"></th>
                </tr>
            </thead>
            <tbody id="scBody"></tbody>
        </table>
    </div>
</div>

<!-- 편집 모달 -->
<div class="adm-modal-overlay" id="scModalOverlay">
    <div class="adm-modal" style="max-width:480px;">
        <div class="adm-modal-head">
            <h3 id="scModalTitle">공간 카드 추가</h3>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <input type="hidden" id="scId">
            <div class="adm-mfield">
                <label>라벨</label>
                <input id="scLabel" type="text" placeholder="예: 중문" maxlength="50">
            </div>
            <div class="adm-mfield">
                <label>컬렉션 검색어 (?q=)</label>
                <input id="scQuery" type="text" placeholder="예: 중문" maxlength="100">
            </div>
            <div class="adm-mfield">
                <label>이미지</label>
                <img id="scImgPreview" class="sc-img-preview" src="" alt="">
                <label class="sc-upload-label" for="scImgFile">
                    <i class="bi bi-upload"></i> 이미지 업로드
                </label>
                <input type="file" id="scImgFile" accept="image/*" style="display:none;" onchange="previewImage(this)">
                <input id="scImageUrl" type="text" placeholder="또는 https://... URL 직접 입력"
                    style="height:38px;padding:0 10px;border:1px solid var(--border-md);border-radius:var(--r-sm);background:var(--input-bg);font-family:inherit;font-size:13px;color:var(--text-1);outline:none;width:100%;">
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" onclick="saveCard()">저장</button>
        </div>
    </div>
</div>

<script>
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
</script>
</body>
</html>
