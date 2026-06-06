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

<div class="db-page" id="catAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="catPage" style="display:none;">
    <div class="db-header">
        <h1 class="db-title">카테고리 관리</h1>
        <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="openAddModal()">
            <i class="bi bi-plus-lg"></i> 추가
        </button>
    </div>

    <div class="adm-table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width:48px;">순서</th>
                    <th>Slug</th>
                    <th>이름</th>
                    <th style="width:72px;">상태</th>
                    <th style="width:100px;"></th>
                </tr>
            </thead>
            <tbody id="catTbody"></tbody>
        </table>
    </div>
</div>

<!-- 추가/수정 모달 -->
<div class="adm-modal-overlay" id="catModalOverlay">
    <div class="adm-modal">
        <div class="adm-modal-head">
            <h3 id="catModalTitle">카테고리 추가</h3>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <div id="catModalAlert" style="display:none;"></div>
            <div class="adm-mfield">
                <label>Slug <small style="color:var(--text-3);font-weight:400;">(영문 소문자, 숫자, -, _)</small></label>
                <input type="text" id="catSlug" placeholder="예: cafe" maxlength="60">
            </div>
            <div class="adm-mfield">
                <label>이름</label>
                <input type="text" id="catName" placeholder="예: 카페" maxlength="80">
            </div>
            <div class="adm-mfield" style="margin-bottom:0;">
                <label>정렬 순서</label>
                <input type="number" id="catOrder" value="0" min="0" max="9999">
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" id="catSaveBtn" onclick="saveCategory()">저장</button>
        </div>
    </div>
</div>

<script>
let editingId = null;

function token() { return localStorage.getItem('pmok_auth_token'); }
function headers() { return { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() }; }

async function init() {
    const user = authGetUser();
    if (!user || user.role !== 's') { location.href = '/'; return; }
    document.getElementById('catPage').style.display = '';
    await loadCategories();
}

async function loadCategories() {
    const res  = await fetch('/src/api/admin/categories.php', { headers: headers() });
    const data = await res.json();
    if (!res.ok) return;
    renderTable(data.categories);
}

function renderTable(cats) {
    document.getElementById('catTbody').innerHTML = cats.map(c => `
        <tr>
            <td class="st-num" style="color:var(--text-3);">${c.sort_order}</td>
            <td style="font-family:monospace;font-size:12px;">${esc(c.slug)}</td>
            <td style="font-weight:600;">${esc(c.name_ko)}</td>
            <td>${c.is_active == 1
                ? '<span class="adm-active-badge">활성</span>'
                : '<span class="adm-withdrawn-badge">비활성</span>'}</td>
            <td><div class="adm-action-cell">
                <button class="adm-edit-btn" onclick='openEditModal(${JSON.stringify(c)})'>수정</button>
                <button class="adm-withdraw-btn" onclick="toggleActive(${c.id}, ${c.is_active == 1 ? 0 : 1})">
                    ${c.is_active == 1 ? '숨김' : '표시'}
                </button>
                <button class="adm-withdraw-btn" style="background:#c00;color:#fff;" onclick="deleteCategory(${c.id}, '${esc(c.name_ko)}')">삭제</button>
            </div></td>
        </tr>
    `).join('') || '<tr><td colspan="5" style="padding:40px;text-align:center;color:var(--text-3);">카테고리가 없습니다.</td></tr>';
}

function openAddModal() {
    editingId = null;
    document.getElementById('catModalTitle').textContent = '카테고리 추가';
    document.getElementById('catSlug').value  = '';
    document.getElementById('catSlug').readOnly = false;
    document.getElementById('catName').value  = '';
    document.getElementById('catOrder').value = '0';
    document.getElementById('catModalAlert').style.display = 'none';
    document.getElementById('catModalOverlay').classList.add('open');
    document.getElementById('catSlug').focus();
}

function openEditModal(c) {
    editingId = c.id;
    document.getElementById('catModalTitle').textContent = '카테고리 수정';
    document.getElementById('catSlug').value  = c.slug;
    document.getElementById('catSlug').readOnly = true;
    document.getElementById('catName').value  = c.name_ko;
    document.getElementById('catOrder').value = c.sort_order;
    document.getElementById('catModalAlert').style.display = 'none';
    document.getElementById('catModalOverlay').classList.add('open');
    document.getElementById('catName').focus();
}

function closeModal() {
    document.getElementById('catModalOverlay').classList.remove('open');
    editingId = null;
}

async function saveCategory() {
    const btn    = document.getElementById('catSaveBtn');
    const slug   = document.getElementById('catSlug').value.trim();
    const name   = document.getElementById('catName').value.trim();
    const order  = parseInt(document.getElementById('catOrder').value) || 0;

    if (!name) { showAlert('이름을 입력하세요.'); return; }

    btn.disabled = true; btn.textContent = '저장 중…';
    try {
        const isEdit = editingId !== null;
        const body   = isEdit
            ? { id: editingId, name_ko: name, sort_order: order, is_active: 1 }
            : { slug, name_ko: name, sort_order: order };
        const res  = await fetch('/src/api/admin/categories.php', {
            method:  isEdit ? 'PUT' : 'POST',
            headers: headers(),
            body:    JSON.stringify(body),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || '오류가 발생했습니다.');
        closeModal();
        await loadCategories();
    } catch (err) {
        showAlert(err.message);
    } finally {
        btn.disabled = false; btn.textContent = '저장';
    }
}

async function toggleActive(id, active) {
    const row  = document.querySelector(`[onclick*="toggleActive(${id},"]`).closest('tr');
    const name = row.querySelector('td:nth-child(3)').textContent;
    const res  = await fetch('/src/api/admin/categories.php', {
        method:  'PUT',
        headers: headers(),
        body:    JSON.stringify({ id, name_ko: name, sort_order: 0, is_active: active }),
    });
    if (res.ok) await loadCategories();
}

async function deleteCategory(id, name) {
    if (!confirm(`"${name}" 카테고리를 삭제하시겠습니까?`)) return;
    const res = await fetch('/src/api/admin/categories.php', {
        method:  'DELETE',
        headers: headers(),
        body:    JSON.stringify({ id }),
    });
    if (res.ok) await loadCategories();
    else { const d = await res.json(); alert(d.error || '삭제 실패'); }
}

function showAlert(msg) {
    const el = document.getElementById('catModalAlert');
    el.textContent  = msg;
    el.className    = 'adm-alert adm-alert-error';
    el.style.display = '';
}

function esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

document.getElementById('catModalOverlay').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeModal();
});

document.addEventListener('DOMContentLoaded', init);
window.addEventListener('pmokAuthChanged', init);
</script>
</body>
</html>
