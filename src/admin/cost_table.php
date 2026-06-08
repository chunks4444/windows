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
        .drag-handle { cursor:grab; color:var(--text-3); }
        .drag-handle:active { cursor:grabbing; }
        tr.dragging { opacity:.4; }
        tr.drag-over td { background:var(--accent-bg); }
        .num-input { text-align:right; }
        .notes-cell { font-size:12px; color:var(--text-3); max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="wtAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="wtPage" style="display:none;">
    <div class="db-header">
        <div>
            <h1 class="db-title">원가 테이블</h1>
            <p style="font-size:12px;color:var(--text-3);margin:2px 0 0;">목재 종류별 단가 및 가중치를 관리합니다.</p>
        </div>
        <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="openModal()">
            <i class="bi bi-plus-lg"></i> 추가
        </button>
    </div>

    <div class="adm-table-wrap" style="margin-top:8px;">
        <table id="wtTable">
            <thead>
                <tr>
                    <th style="width:32px;"></th>
                    <th style="width:100px;">구분</th>
                    <th>항목</th>
                    <th style="width:140px;text-align:right;">단가</th>
                    <th style="width:80px;">단위</th>
                    <th style="width:100px;">단위명</th>
                    <th style="width:100px;text-align:right;">가중치</th>
                    <th>메모</th>
                    <th style="width:72px;">상태</th>
                    <th style="width:160px;"></th>
                </tr>
            </thead>
            <tbody id="wtBody"></tbody>
        </table>
    </div>
</div>

<!-- 편집 모달 -->
<div class="adm-modal-overlay" id="wtModalOverlay">
    <div class="adm-modal" style="max-width:420px;">
        <div class="adm-modal-head">
            <h3 id="wtModalTitle">항목 추가</h3>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <input type="hidden" id="wtId">
            <div class="adm-mfield">
                <label>구분</label>
                <select id="wtCategory">
                    <option value="">— 선택 —</option>
                    <option value="wood">우드</option>
                    <option value="grid">그리드</option>
                    <option value="labor">인건비</option>
                    <option value="overhead">일반경비</option>
                    <option value="delivery">배송비</option>
                </select>
            </div>
            <div class="adm-mfield">
                <label>항목</label>
                <input id="wtName" type="text" placeholder="예: 홍송" maxlength="100">
            </div>
            <div style="display:flex;gap:12px;">
                <div class="adm-mfield" style="flex:2;">
                    <label>단가</label>
                    <input id="wtUnitPrice" type="number" min="0" step="100" placeholder="12000" class="num-input">
                </div>
                <div class="adm-mfield" style="flex:1;">
                    <label>단위</label>
                    <input id="wtUnit" type="text" placeholder="사이, %, 시간…" maxlength="30">
                </div>
                <div class="adm-mfield" style="flex:1;">
                    <label>단위명</label>
                    <input id="wtUnitName" type="text" placeholder="재(才), 퍼센트…" maxlength="50">
                </div>
            </div>
            <div class="adm-mfield">
                <label>가중치</label>
                <input id="wtWeight" type="number" min="0" step="0.01" placeholder="1.00" class="num-input">
            </div>
            <div class="adm-mfield">
                <label>메모</label>
                <textarea id="wtNotes" rows="3" placeholder="특이사항, 구매처, 참고사항 등"
                    style="width:100%;padding:8px 10px;border:1px solid var(--border-md);border-radius:var(--r-sm);background:var(--input-bg);font-family:inherit;font-size:13px;color:var(--text-1);outline:none;resize:vertical;"></textarea>
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" onclick="saveItem()">저장</button>
        </div>
    </div>
</div>

<script>
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
</script>
</body>
</html>
