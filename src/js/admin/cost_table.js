const API = '/src/api/admin/cost_table.php';
function _h() { return { 'Authorization': 'Bearer ' + localStorage.getItem('pmok_auth_token'), 'Content-Type': 'application/json' }; }

let items = [], dragSrc;

async function load() {
    const res  = await fetch(API, { headers: _h() });
    const data = await res.json();
    if (!res.ok) { document.getElementById('wtAuthWall').style.display = ''; return; }
    items = data.items || [];
    render();
}

const CAT_LABEL = { wood:'우드', grid:'그리드', labor:'인건비', overhead:'일반경비', delivery:'배송비' };
function catLabel(v) { return CAT_LABEL[v] || v || '—'; }
function fmt(n) { return Number(n).toLocaleString('ko-KR'); }
function esc(s) { return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }

function render() {
    document.getElementById('wtBody').innerHTML = items.map(it => `
        <tr data-id="${it.id}" draggable="true">
            <td style="text-align:center;"><span class="drag-handle"><i class="bi bi-grip-vertical"></i></span></td>
            <td style="font-size:12px;color:var(--text-3);">${catLabel(it.category)}</td>
            <td><strong>${esc(it.name)}</strong></td>
            <td style="text-align:right;">${fmt(it.unit_price)}</td>
            <td style="font-size:12px;color:var(--text-3);">${esc(it.unit) || '—'}</td>
            <td style="font-size:12px;color:var(--text-3);">${esc(it.unit_name) || '—'}</td>
            <td style="text-align:right;">${parseFloat(it.weight).toFixed(2)}</td>
            <td class="notes-cell" title="${esc(it.notes)}">${esc(it.notes) || '—'}</td>
            <td><span class="${it.is_active ? 'adm-active-badge' : 'adm-withdrawn-badge'}">${it.is_active ? '활성' : '비활성'}</span></td>
            <td>
                <div class="adm-action-cell">
                    <button class="adm-edit-btn" style="height:28px;padding:0 10px;font-size:12px;" onclick="openModal(${it.id})">수정</button>
                    <button class="adm-withdraw-btn" onclick="toggle(${it.id})">${it.is_active ? '비활성' : '활성'}</button>
                    <button class="adm-withdraw-btn" style="background:#c00;color:#fff;" onclick="del(${it.id},'${esc(it.name)}')">삭제</button>
                </div>
            </td>
        </tr>`).join('');
    bindDrag();
}

function openModal(id) {
    const it = id ? items.find(x => x.id == id) : null;
    document.getElementById('wtModalTitle').textContent = it ? '항목 수정' : '항목 추가';
    document.getElementById('wtId').value        = it?.id ?? '';
    document.getElementById('wtCategory').value  = it?.category ?? '';
    document.getElementById('wtName').value      = it?.name ?? '';
    document.getElementById('wtUnitPrice').value = it?.unit_price ?? '';
    document.getElementById('wtUnit').value      = it?.unit ?? '';
    document.getElementById('wtUnitName').value  = it?.unit_name ?? '';
    document.getElementById('wtWeight').value    = it ? parseFloat(it.weight).toFixed(2) : '1.00';
    document.getElementById('wtNotes').value     = it?.notes ?? '';
    document.getElementById('wtModalOverlay').classList.add('open');
    setTimeout(() => document.getElementById('wtName').focus(), 50);
}

function closeModal() { document.getElementById('wtModalOverlay').classList.remove('open'); }

async function saveItem() {
    const body = {
        action:     'save',
        id:         parseInt(document.getElementById('wtId').value) || 0,
        category:   document.getElementById('wtCategory').value.trim(),
        name:       document.getElementById('wtName').value.trim(),
        unit_price: parseFloat(document.getElementById('wtUnitPrice').value) || 0,
        unit:       document.getElementById('wtUnit').value.trim(),
        unit_name:  document.getElementById('wtUnitName').value.trim(),
        weight:     parseFloat(document.getElementById('wtWeight').value) || 1,
        notes:      document.getElementById('wtNotes').value.trim(),
    };
    if (!body.name) { alert('항목을 입력하세요.'); return; }
    const res  = await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify(body) });
    const data = await res.json();
    if (data.ok) { closeModal(); load(); }
    else alert(data.error || '저장 실패');
}

async function del(id, name) {
    if (!confirm(`"${name}" 을(를) 삭제할까요?`)) return;
    await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'delete', id }) });
    load();
}

async function toggle(id) {
    await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'toggle', id }) });
    load();
}

function bindDrag() {
    document.querySelectorAll('#wtBody tr').forEach(tr => {
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
            await load();
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('wtModalOverlay').addEventListener('click', e => {
        if (e.target === e.currentTarget) closeModal();
    });
    const user = JSON.parse(localStorage.getItem('pmok_auth_user') || 'null');
    if (!user || user.role !== 's') { document.getElementById('wtAuthWall').style.display = ''; return; }
    document.getElementById('wtPage').style.display = '';
    load();
});
