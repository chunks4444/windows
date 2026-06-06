<?php
header('Content-Type: text/html; charset=UTF-8');
?>
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
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="metaPage" style="display:none;">
    <div class="db-header">
        <h1 class="db-title">SEO 메타 관리</h1>
        <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="openAddModal()">
            <i class="bi bi-plus-lg"></i> 추가
        </button>
    </div>

    <div class="adm-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>경로</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Keywords</th>
                    <th style="width:80px;"></th>
                </tr>
            </thead>
            <tbody id="metaTbody"></tbody>
        </table>
    </div>
</div>

<!-- 추가/수정 모달 -->
<div class="adm-modal-overlay" id="metaModalOverlay">
    <div class="adm-modal" style="max-width:560px;">
        <div class="adm-modal-head">
            <h3 id="metaModalTitle">메타 추가</h3>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <div id="metaModalAlert" style="display:none;"></div>
            <div class="adm-mfield">
                <label>경로 <small style="color:var(--text-3);font-weight:400;">(예: /src/company/index.php)</small></label>
                <input type="text" id="metaPath" placeholder="/index.php" maxlength="120">
            </div>
            <div class="adm-mfield">
                <label>Title <small style="color:var(--text-3);font-weight:400;">최대 60자 권장</small></label>
                <input type="text" id="metaTitle" maxlength="200" oninput="updateCount('metaTitle', 'cntTitle', 60)">
                <small id="cntTitle" style="color:var(--text-3);font-size:11px;"></small>
            </div>
            <div class="adm-mfield">
                <label>Description <small style="color:var(--text-3);font-weight:400;">최대 160자 권장</small></label>
                <textarea id="metaDesc" rows="3" maxlength="320" style="resize:none;border:1px solid #e0e0e0;border-radius:4px;padding:9px 12px;font-size:13px;font-family:inherit;width:100%;outline:none;" oninput="updateCount('metaDesc', 'cntDesc', 160)"></textarea>
                <small id="cntDesc" style="color:var(--text-3);font-size:11px;"></small>
            </div>
            <div class="adm-mfield">
                <label>Keywords</label>
                <input type="text" id="metaKeywords" maxlength="500" placeholder="쉼표로 구분">
            </div>
            <div class="adm-mfield" style="margin-bottom:0;">
                <label>OG Image URL</label>
                <input type="text" id="metaOgImage" maxlength="500" placeholder="https://…">
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" id="metaSaveBtn" onclick="saveMeta()">저장</button>
        </div>
    </div>
</div>

<script>
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
    document.getElementById('metaTbody').innerHTML = pages.map(p => `
        <tr>
            <td style="font-family:monospace;font-size:12px;white-space:nowrap;">${esc(p.path)}</td>
            <td style="font-size:12px;">${esc(p.title) || '<span style="color:var(--text-3);">—</span>'}</td>
            <td style="font-size:12px;color:var(--text-2);">${truncate(esc(p.description), 60)}</td>
            <td style="font-size:12px;color:var(--text-3);">${truncate(esc(p.keywords), 40)}</td>
            <td><div class="adm-action-cell">
                <button class="adm-edit-btn" onclick='openEditModal(${JSON.stringify(p)})'>수정</button>
                <button class="adm-withdraw-btn" style="background:#c00;color:#fff;" onclick="deleteMeta(${p.id}, '${esc(p.path)}')">삭제</button>
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
    document.getElementById('metaPath').readOnly   = true;
    document.getElementById('metaTitle').value     = p.title;
    document.getElementById('metaDesc').value      = p.description;
    document.getElementById('metaKeywords').value  = p.keywords;
    document.getElementById('metaOgImage').value   = p.og_image;
    document.getElementById('metaModalAlert').style.display = 'none';
    updateCount('metaTitle', 'cntTitle', 60);
    updateCount('metaDesc',  'cntDesc',  160);
    document.getElementById('metaModalOverlay').classList.add('open');
    document.getElementById('metaTitle').focus();
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
        if (isEdit) body.id = editingId; else body.path = path;

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
</script>
</body>
</html>
