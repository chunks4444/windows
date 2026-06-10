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
    <style>
        .color-dot { width:28px;height:28px;border-radius:50%;border:1px solid rgba(0,0,0,0.1);display:inline-block;vertical-align:middle; }
        .adm-table-wrap table td { vertical-align:middle; }
        .group-header td { background:var(--bg-2,#f5f5f5);font-weight:700;font-size:12px;color:#666;letter-spacing:.05em; }
        input[type=color] { width:36px;height:28px;padding:2px;border:1px solid #ddd;border-radius:4px;cursor:pointer; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="colorPage" style="display:none;">
    <div class="db-header">
        <h1 class="db-title">컬러 팔레트 관리</h1>
        <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="openAddModal()">
            <i class="bi bi-plus-lg"></i> 색상 추가
        </button>
    </div>
    <div class="adm-table-wrap">
        <table id="colorTable">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>그룹</th>
                    <th>코드</th>
                    <th>이름</th>
                    <th>헥스</th>
                    <th style="width:70px;">순서</th>
                    <th style="width:80px;">활성</th>
                    <th style="width:140px;"></th>
                </tr>
            </thead>
            <tbody id="colorBody"></tbody>
        </table>
    </div>
</div>

<!-- 추가/수정 모달 -->
<div id="colorModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;width:360px;padding:24px;">
        <h5 id="colorModalTitle" style="margin:0 0 16px;font-size:15px;font-weight:700;">색상 추가</h5>
        <input type="hidden" id="editId">
        <div style="display:flex;flex-direction:column;gap:10px;">
            <label style="font-size:12px;font-weight:600;">그룹명<input type="text" id="editGroup" class="form-control form-control-sm mt-1" placeholder="스테인"></label>
            <label style="font-size:12px;font-weight:600;">정렬 순서<input type="number" id="editOrder" class="form-control form-control-sm mt-1" value="0" min="0"></label>
            <label style="font-size:12px;font-weight:600;">코드<input type="text" id="editCode" class="form-control form-control-sm mt-1" placeholder="930-00"></label>
            <label style="font-size:12px;font-weight:600;">이름<input type="text" id="editName" class="form-control form-control-sm mt-1" placeholder="투명"></label>
            <label style="font-size:12px;font-weight:600;">헥스 코드
                <div style="display:flex;gap:8px;align-items:center;margin-top:4px;">
                    <input type="color" id="editHexPicker" value="#dec898" oninput="document.getElementById('editHex').value=this.value">
                    <input type="text" id="editHex" class="form-control form-control-sm" placeholder="#dec898" oninput="syncColorPicker()">
                </div>
            </label>
        </div>
        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:20px;">
            <button class="btn btn-sm btn-secondary" onclick="closeModal()">취소</button>
            <button class="btn btn-sm btn-dark" onclick="saveColor()">저장</button>
        </div>
    </div>
</div>

<script>
const token = () => localStorage.getItem('pmok_auth_token');
const hdr   = () => ({ 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() });

async function loadColors() {
    const res  = await fetch('/src/api/admin/colors.php', { headers: hdr() });
    const data = await res.json();
    if (!res.ok) { alert(data.error || '불러오기 실패'); return; }
    renderTable(data.colors || []);
    document.getElementById('colorPage').style.display = '';
}

function renderTable(colors) {
    const tbody = document.getElementById('colorBody');
    tbody.innerHTML = '';
    let lastGroup = null;
    colors.forEach(c => {
        if (c.group_name !== lastGroup) {
            lastGroup = c.group_name;
            const gr = document.createElement('tr');
            gr.className = 'group-header';
            gr.innerHTML = `<td colspan="8">${esc(c.group_name)}</td>`;
            tbody.appendChild(gr);
        }
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><span class="color-dot" style="background:${esc(c.hex)};"></span></td>
            <td style="font-size:12px;color:#999;">${esc(c.group_name)}</td>
            <td><code>${esc(c.code)}</code></td>
            <td>${esc(c.name)}</td>
            <td><code>${esc(c.hex)}</code></td>
            <td style="text-align:center;">${c.sort_order}</td>
            <td style="text-align:center;">
                <input type="checkbox" ${c.is_active ? 'checked' : ''} onchange="toggleActive(${c.id}, this.checked)">
            </td>
            <td style="white-space:nowrap;">
                <button class="adm-edit-btn" style="height:26px;padding:0 12px;font-size:11px;" onclick='openEditModal(${JSON.stringify(c)})'>수정</button>
                <button class="adm-del-btn" style="height:26px;padding:0 12px;font-size:11px;margin-left:6px;" onclick="deleteColor(${c.id})">삭제</button>
            </td>`;
        tbody.appendChild(tr);
    });
}

function openAddModal() {
    document.getElementById('editId').value    = '';
    document.getElementById('editGroup').value = '';
    document.getElementById('editOrder').value = 0;
    document.getElementById('editCode').value  = '';
    document.getElementById('editName').value  = '';
    document.getElementById('editHex').value   = '#dec898';
    document.getElementById('editHexPicker').value = '#dec898';
    document.getElementById('colorModalTitle').textContent = '색상 추가';
    document.getElementById('colorModal').style.display = 'flex';
}

function openEditModal(c) {
    document.getElementById('editId').value    = c.id;
    document.getElementById('editGroup').value = c.group_name;
    document.getElementById('editOrder').value = c.sort_order;
    document.getElementById('editCode').value  = c.code;
    document.getElementById('editName').value  = c.name;
    document.getElementById('editHex').value   = c.hex;
    document.getElementById('editHexPicker').value = c.hex;
    document.getElementById('colorModalTitle').textContent = '색상 수정';
    document.getElementById('colorModal').style.display = 'flex';
}

function closeModal() { document.getElementById('colorModal').style.display = 'none'; }

function syncColorPicker() {
    const val = document.getElementById('editHex').value;
    if (/^#[0-9a-fA-F]{6}$/.test(val)) document.getElementById('editHexPicker').value = val;
}

async function saveColor() {
    const id   = document.getElementById('editId').value;
    const body = {
        id:         id ? parseInt(id) : null,
        group_name: document.getElementById('editGroup').value.trim(),
        sort_order: parseInt(document.getElementById('editOrder').value) || 0,
        code:       document.getElementById('editCode').value.trim(),
        name:       document.getElementById('editName').value.trim(),
        hex:        document.getElementById('editHex').value.trim(),
    };
    if (!body.group_name || !body.code || !body.name || !body.hex) { alert('모든 항목을 입력하세요.'); return; }
    const res  = await fetch('/src/api/admin/colors.php', { method: 'POST', headers: hdr(), body: JSON.stringify(body) });
    const data = await res.json();
    if (!res.ok) { alert(data.error || '저장 실패'); return; }
    closeModal();
    loadColors();
}

async function toggleActive(id, active) {
    await fetch('/src/api/admin/colors.php', {
        method: 'POST', headers: hdr(),
        body: JSON.stringify({ id, is_active: active ? 1 : 0, _action: 'toggle' }),
    });
}

async function deleteColor(id) {
    if (!confirm('삭제하시겠습니까?')) return;
    const res = await fetch('/src/api/admin/colors.php', {
        method: 'DELETE', headers: hdr(), body: JSON.stringify({ id }),
    });
    if (res.ok) loadColors();
}

function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

document.addEventListener('DOMContentLoaded', () => {
    if (token()) loadColors();
});
window.addEventListener('pmokAuthChanged', loadColors);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>
