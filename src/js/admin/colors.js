const token = () => localStorage.getItem('pmok_auth_token');
const hdr   = () => ({ 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() });

async function loadColors() {
    const res  = await fetch('/src/api/admin/colors.php', { headers: hdr() });
    const data = await res.json();
    if (!res.ok) { alert(data.error || '불러오기 실패'); return; }
    allColors = data.colors || [];
    renderFilters();
    renderTable(filteredColors());
    document.getElementById('colorPage').style.display = '';
}

// ── 필터: 그룹 탭('' = 전체) + 검색어(코드·이름·헥스·브랜드). 선택한 그룹은 새로고침해도 유지 ──
let allColors   = [];
let activeGroup = (() => { try { return sessionStorage.getItem('pmok_color_group') || ''; } catch (e) { return ''; } })();
let searchText  = '';

function filteredColors() {
    const q = searchText.trim().toLowerCase();
    return allColors.filter(c =>
        (!activeGroup || c.group_name === activeGroup) &&
        (!q || [c.code, c.name, c.hex, c.brand].some(v => String(v || '').toLowerCase().includes(q))));
}

function renderFilters() {
    const groups = [...new Set(allColors.map(c => c.group_name))];
    if (activeGroup && !groups.includes(activeGroup)) activeGroup = '';
    const count = g => allColors.filter(c => !g || c.group_name === g).length;
    const tabs  = document.getElementById('colorGroupTabs');
    tabs.innerHTML = ['', ...groups].map(g =>
        `<button type="button" class="adm-tab-btn${g === activeGroup ? ' active' : ''}" data-group="${esc(g)}">${g ? esc(g) : '전체'} <span class="color-tab-count">${count(g)}</span></button>`
    ).join('');
    tabs.querySelectorAll('.adm-tab-btn').forEach(btn => btn.addEventListener('click', () => {
        activeGroup = btn.dataset.group;
        try { sessionStorage.setItem('pmok_color_group', activeGroup); } catch (e) {}
        renderFilters();
        renderTable(filteredColors());
    }));
}

function renderTable(colors) {
    const tbody = document.getElementById('colorBody');
    tbody.innerHTML = '';
    if (!colors.length) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:24px;color:var(--text-muted);">조건에 맞는 색상이 없습니다.</td></tr>';
        return;
    }
    let lastGroup = null;
    colors.forEach(c => {
        if (c.group_name !== lastGroup) {
            lastGroup = c.group_name;
            const gr = document.createElement('tr');
            gr.className = 'group-header';
            gr.innerHTML = `<td colspan="9">${esc(c.group_name)}</td>`;
            tbody.appendChild(gr);
        }
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><span class="color-dot" style="background:${esc(c.hex)};"></span></td>
            <td style="font-size:12px;color:#999;">${esc(c.group_name)}</td>
            <td>${esc(c.brand || '')}</td>
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
    document.getElementById('editGroup').value = activeGroup; // 그룹 탭을 고른 상태면 그 그룹으로 미리 채움
    document.getElementById('editBrand').value = '';
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
    document.getElementById('editBrand').value = c.brand || '';
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
        brand:      document.getElementById('editBrand').value.trim(),
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
    document.getElementById('colorSearch').addEventListener('input', e => {
        searchText = e.target.value;
        renderTable(filteredColors());
    });
    if (token()) loadColors();
});
window.addEventListener('pmokAuthChanged', loadColors);
