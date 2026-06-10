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
