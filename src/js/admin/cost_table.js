const API = '/src/api/admin/cost_table.php';
function _h() { return { 'Authorization': 'Bearer ' + localStorage.getItem('pmok_auth_token'), 'Content-Type': 'application/json' }; }

let items = [], dragSrc;
let activeTab    = 'wood';
let activeEngine = 'classic';

const ENGINE_LABEL = window.__pmokEngineLabels || {
    classic:  'Classic Lattice',
    square:   'Square Lattice',
    cross:    'Cross Lattice',
    diamond:  'Diamond Lattice',
    triangle: 'Triangle Lattice',
    hexagon:  'Hexagon Lattice',
};

const TABS = {
    wood:     { label: '목재',   cats: ['wood', 'oil'],        cols: 'default'         },
    finish:   { label: '마감',   cats: ['finish', 'delivery'], cols: 'finish'          },
    labor:    { label: '인건비', cats: ['labor'],              cols: 'labor_inline'    },
    overhead: { label: '간접비', cats: ['overhead'],           cols: 'overhead_inline' },
};

const LABOR_COMMON_FIELDS = [
    { name: 'hourly_rate',  label: '시간당 공임', hint: '목수 기본값',  unit: '원' },
    { name: 'finish_rate',  label: '마감 작업',   hint: '마감칠 단가',  unit: '원' },
];

const OVERHEAD_FIELDS = [
    { name: 'overhead_rate', label: '간접비율', hint: '운영비·임대료 등', unit: '%' },
    { name: 'profit_rate',   label: '이익률',   hint: '목표 이익률',     unit: '%' },
];



const COL_HEADS = {
    default: `<th style="width:32px;"></th><th>항목</th><th style="width:130px;text-align:right;">단가</th><th style="width:80px;">단위</th><th style="width:100px;">단위명</th><th style="width:90px;text-align:right;">가중치</th><th>메모</th><th style="width:72px;">상태</th><th style="width:160px;"></th>`,
    finish:  `<th style="width:32px;"></th><th>항목</th><th style="width:160px;text-align:right;">단가 <small style="font-weight:400;color:var(--text-3);">(원/㎡)</small></th><th style="width:80px;">도포</th><th style="width:100px;">시간(분/㎡)</th><th style="width:90px;text-align:right;">가중치</th><th>메모</th><th style="width:72px;">상태</th><th style="width:160px;"></th>`,
    labor:   `<th style="width:32px;"></th><th>항목</th><th style="width:110px;">엔진</th><th style="width:130px;text-align:right;">값</th><th style="width:80px;">단위</th><th>메모</th><th style="width:72px;">상태</th><th style="width:160px;"></th>`,
};

function dataCells(it, colType) {
    const eng = it.engine ? (ENGINE_LABEL[it.engine] || it.engine) : '공통';
    if (colType === 'labor') return `
        <td><strong>${esc(LABOR_NAME_LABEL[it.name] || it.name)}</strong></td>
        <td style="font-size:12px;color:var(--text-3);">${esc(eng)}</td>
        <td style="text-align:right;">${fmt(it.unit_price)}</td>
        <td style="font-size:12px;color:var(--text-3);">${esc(it.unit) || '—'}</td>
        <td class="notes-cell" title="${esc(it.notes)}">${esc(it.notes) || '—'}</td>`;
    if (colType === 'finish') return `
        <td><strong>${esc(it.name)}</strong></td>
        <td style="text-align:right;">${fmt(it.unit_price)}</td>
        <td style="font-size:12px;color:var(--text-3);text-align:center;">${it.coat_count || '—'}회</td>
        <td style="font-size:12px;color:var(--text-3);text-align:center;">${it.work_time_min || '—'}</td>
        <td style="text-align:right;">${parseFloat(it.weight).toFixed(2)}</td>
        <td class="notes-cell" title="${esc(it.notes)}">${esc(it.notes) || '—'}</td>`;
    return `
        <td><strong>${esc(it.name)}</strong></td>
        <td style="text-align:right;">${fmt(it.unit_price)}</td>
        <td style="font-size:12px;color:var(--text-3);">${esc(it.unit) || '—'}</td>
        <td style="font-size:12px;color:var(--text-3);">${esc(it.unit_name) || '—'}</td>
        <td style="text-align:right;">${parseFloat(it.weight).toFixed(2)}</td>
        <td class="notes-cell" title="${esc(it.notes)}">${esc(it.notes) || '—'}</td>`;
}

async function load() {
    const res  = await fetch(API, { headers: _h() });
    const data = await res.json();
    if (!res.ok) { document.getElementById('wtAuthWall').style.display = ''; return; }
    items = data.items || [];
    render();
}

function fmt(n) { return Number(n).toLocaleString('ko-KR'); }
function esc(s) { return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }

const LABOR_ENGINE_FIELDS = [
    { name: 'craft_time',   label: '교차점당 작업시간', unit: '분' },
    { name: 'ulgeomi_time', label: '울거미 제작 시간',  unit: '분' },
    { name: 'trim_time',    label: '짝당 다듬기 시간',  unit: '분' },
    { name: 'muntol_time',  label: '문틀 제작 시간',    unit: '분' },
];


function renderLabor() {
    const laborItems = items.filter(it => it.category === 'labor' && !it.engine);

    // 기본 단가 (고정)
    document.getElementById('wtLaborBody').innerHTML = LABOR_COMMON_FIELDS.map(f => {
        const it = laborItems.find(r => r.name === f.name) || {};
        return `<tr data-id="${it.id ?? ''}" data-labor-name="${f.name}" data-unit="${f.unit}">
            <td>
                <span style="font-weight:600;">${f.label}</span>
                ${f.hint ? `<span style="display:block;font-size:11px;color:var(--text-3);margin-top:2px;">${esc(f.hint)}</span>` : ''}
            </td>
            <td><input type="number" class="form-control form-control-sm num-input" min="0" step="1"
                       value="${it.unit_price ?? ''}"></td>
            <td style="font-size:12px;color:var(--text-3);">${f.unit}</td>
        </tr>`;
    }).join('');

    // 엔진별 작업 시간
    const laborEngineRows = items.filter(it => it.category === 'labor' && it.engine === activeEngine);
    const engBody = document.getElementById('wtLaborEngineBody');
    if (engBody) engBody.innerHTML = LABOR_ENGINE_FIELDS.map(f => {
        const it = laborEngineRows.find(r => r.name === f.name);
        return `<tr data-labor-id="${it?.id ?? ''}" data-labor-name="${f.name}">
            <td>${f.label}</td>
            <td><input type="number" min="0" step="1"
                       class="form-control form-control-sm num-input"
                       value="${it?.unit_price ?? ''}"></td>
            <td></td>
        </tr>`;
    }).join('');
}

async function saveLabor() {
    const saveOne = body => fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify(body) });

    // 기본 단가
    const baseBodies = [...document.querySelectorAll('#wtLaborBody tr')].map(tr => {
        const val = tr.querySelector('input[type="number"]').value;
        return {
            action: 'save', id: tr.dataset.id ? parseInt(tr.dataset.id) : 0,
            category: 'labor', engine: '',
            name:       tr.dataset.laborName,
            unit_price: val === '' ? 0 : parseFloat(val),
            unit: tr.dataset.unit, unit_name: '', weight: 1, thickness_mm: 0, width_mm: 0, notes: '',
        };
    });

    // 엔진별 작업 시간
    const laborEngineBodies = [...document.querySelectorAll('#wtLaborEngineBody tr')].map(tr => {
        const val = parseFloat(tr.querySelector('input').value);
        if (isNaN(val)) return null;
        const name  = tr.dataset.laborName;
        const field = LABOR_ENGINE_FIELDS.find(f => f.name === name);
        return {
            action: 'save',
            id:    tr.dataset.laborId ? parseInt(tr.dataset.laborId) : 0,
            category: 'labor', engine: activeEngine,
            name, unit_price: val, unit: field?.unit ?? '', unit_name: '', weight: 1,
            thickness_mm: 0, width_mm: 0, notes: '',
        };
    }).filter(Boolean);

    const results = await Promise.all([...baseBodies, ...laborEngineBodies].map(saveOne));
    if (results.some(r => !r.ok)) { alert('저장 실패'); return; }
    await load();
    const btn = document.getElementById('btnLaborSave');
    const orig = btn.textContent;
    btn.textContent = '저장 완료 ✓';
    setTimeout(() => { btn.textContent = orig; }, 1500);
}


// ── 간접비 탭 인라인 편집 ────────────────────────────────────────────────────
function renderOverhead() {
    const overheadItems = items.filter(it => it.category === 'overhead' && !it.engine);
    document.getElementById('wtOverheadBody').innerHTML = OVERHEAD_FIELDS.map(f => {
        const it = overheadItems.find(r => r.name === f.name) || {};
        const pct = it.unit_price != null ? parseFloat(it.unit_price) : '';
        return `<tr data-id="${it.id ?? ''}" data-name="${f.name}">
            <td>
                <span style="font-weight:600;">${f.label}</span>
                ${f.hint ? `<span style="display:block;font-size:11px;color:var(--text-3);margin-top:2px;">${esc(f.hint)}</span>` : ''}
            </td>
            <td><input type="number" class="form-control form-control-sm num-input" min="0" max="100" step="0.1"
                       style="text-align:right;" value="${pct}"></td>
            <td style="font-size:12px;color:var(--text-3);">${f.unit}</td>
        </tr>`;
    }).join('');
}

async function saveOverhead() {
    const saveOne = body => fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify(body) });
    const bodies = [...document.querySelectorAll('#wtOverheadBody tr')].map(tr => {
        const val = tr.querySelector('input[type="number"]').value;
        return {
            action: 'save', id: tr.dataset.id ? parseInt(tr.dataset.id) : 0,
            category: 'overhead', engine: '',
            name: tr.dataset.name,
            unit_price: val === '' ? 0 : parseFloat(val),
            unit: '%', unit_name: '', weight: 1, thickness_mm: 0, width_mm: 0, notes: '',
        };
    });
    const results = await Promise.all(bodies.map(saveOne));
    if (results.some(r => !r.ok)) { alert('저장 실패'); return; }
    await load();
    const btn = document.getElementById('btnOverheadSave');
    const orig = btn.textContent;
    btn.textContent = '저장 완료 ✓';
    setTimeout(() => { btn.textContent = orig; }, 1500);
}

// ── 일반 탭 테이블 + 모달 ────────────────────────────────────────────────────
function render() {
    const tab     = TABS[activeTab];
    const isLaborInline    = tab.cols === 'labor_inline';
    const isOverheadInline = tab.cols === 'overhead_inline';

    document.querySelectorAll('#wtTabs .adm-tab-btn').forEach(b => b.classList.toggle('active', b.dataset.tab === activeTab));
    document.getElementById('wtLaborPanel').style.display    = isLaborInline    ? '' : 'none';
    document.getElementById('wtOverheadPanel').style.display = isOverheadInline ? '' : 'none';
    document.getElementById('wtTableWrap').style.display     = (isLaborInline || isOverheadInline) ? 'none' : '';

    if (isLaborInline)    { renderLabor();    return; }
    if (isOverheadInline) { renderOverhead(); return; }

    document.getElementById('wtColHeads').innerHTML = COL_HEADS[tab.cols] || COL_HEADS.default;

    const list = items.filter(it => tab.cats.includes(it.category));
    document.getElementById('wtBody').innerHTML = list.map(it => `
        <tr data-id="${it.id}" draggable="true">
            <td style="text-align:center;"><span class="drag-handle"><i class="bi bi-grip-vertical"></i></span></td>
            ${dataCells(it, tab.cols)}
            <td><span class="${it.is_active ? 'adm-active-badge' : 'adm-withdrawn-badge'}">${it.is_active ? '활성' : '비활성'}</span></td>
            <td>
                <div class="adm-action-cell">
                    <button class="adm-edit-btn" style="height:28px;padding:0 10px;font-size:12px;" onclick="openModal(${it.id})">수정</button>
                    <button class="adm-withdraw-btn" onclick="toggle(${it.id})">${it.is_active ? '비활성' : '활성'}</button>
                    <button class="adm-withdraw-btn" style="background:#c00;color:#fff;" onclick="del(${it.id},'${esc(it.name)}')">삭제</button>
                </div>
            </td>
        </tr>`).join('') || `<tr><td colspan="9" style="text-align:center;padding:24px;color:var(--text-3);">항목이 없습니다.</td></tr>`;

    bindDrag();
}

function openModal(id) {
    const tab     = TABS[activeTab];
    const isLabor = tab.cols === 'labor';
    const it      = id ? items.find(x => x.id == id) : null;

    document.getElementById('wtModalTitle').textContent   = it ? '항목 수정' : '항목 추가';
    document.getElementById('wtId').value        = it?.id ?? '';
    document.getElementById('wtCategory').value  = it?.category ?? tab.cats[0];
    document.getElementById('wtName').value      = it?.name ?? '';
    document.getElementById('wtUnitPrice').value = it?.unit_price ?? '';
    document.getElementById('wtUnit').value      = it?.unit ?? '';
    document.getElementById('wtUnitName').value  = it?.unit_name ?? '';
    document.getElementById('wtWeight').value    = it ? parseFloat(it.weight).toFixed(2) : '1.00';
    document.getElementById('wtEngine').value    = it?.engine ?? '';
    document.getElementById('wtNotes').value     = it?.notes ?? '';

    const isFinish = tab.cats.includes('finish');
    document.getElementById('wtEngineField').style.display = isLabor  ? '' : 'none';
    document.getElementById('wtWeightRow').style.display   = isLabor  ? 'none' : '';
    document.getElementById('wtFinishRow').style.display   = isFinish ? '' : 'none';
    if (isFinish) {
        if (!it) document.getElementById('wtUnit').value = '원/㎡';
        document.getElementById('wtWorkTimeMin').value = it?.work_time_min ?? '';
        document.getElementById('wtCoatCount').value   = it?.coat_count    ?? 2;
    }

    document.getElementById('wtModalOverlay').classList.add('open');
    setTimeout(() => document.getElementById('wtName').focus(), 50);
}

function closeModal() { document.getElementById('wtModalOverlay').classList.remove('open'); }

async function saveItem() {
    const body = {
        action:       'save',
        id:           parseInt(document.getElementById('wtId').value) || 0,
        category:     document.getElementById('wtCategory').value,
        name:         document.getElementById('wtName').value.trim(),
        unit_price:   parseFloat(document.getElementById('wtUnitPrice').value) || 0,
        unit:         document.getElementById('wtUnit').value.trim(),
        unit_name:    document.getElementById('wtUnitName').value.trim(),
        weight:       parseFloat(document.getElementById('wtWeight').value) || 1,
        engine:        document.getElementById('wtEngine').value.trim(),
        thickness_mm:  0, width_mm: 0,
        work_time_min: parseInt(document.getElementById('wtWorkTimeMin').value) || 0,
        coat_count:    parseInt(document.getElementById('wtCoatCount').value)    || 2,
        notes:         document.getElementById('wtNotes').value.trim(),
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
    document.querySelectorAll('#wtBody tr[data-id]').forEach(tr => {
        tr.addEventListener('dragstart', () => { dragSrc = tr; tr.classList.add('dragging'); });
        tr.addEventListener('dragend',   () => tr.classList.remove('dragging'));
        tr.addEventListener('dragover',  e => { e.preventDefault(); tr.classList.add('drag-over'); });
        tr.addEventListener('dragleave', () => tr.classList.remove('drag-over'));
        tr.addEventListener('drop', async e => {
            e.preventDefault(); tr.classList.remove('drag-over');
            if (dragSrc === tr) return;
            tr.parentNode.insertBefore(dragSrc, tr.nextSibling);
            const ids = [...tr.parentNode.querySelectorAll('tr[data-id]')].map(r => +r.dataset.id);
            await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'reorder', ids }) });
            await load();
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('wtModalOverlay').addEventListener('click', e => {
        if (e.target === e.currentTarget) closeModal();
    });
    document.querySelectorAll('#wtTabs .adm-tab-btn').forEach(btn => {
        btn.addEventListener('click', () => { activeTab = btn.dataset.tab; render(); });
    });
    document.getElementById('wtEngineSelect').addEventListener('change', e => {
        activeEngine = e.target.value;
        renderLabor();
    });
    document.getElementById('btnLaborSave').addEventListener('click', saveLabor);
    document.getElementById('btnOverheadSave').addEventListener('click', saveOverhead);

    const user = JSON.parse(localStorage.getItem('pmok_auth_user') || 'null');
    if (!user || user.role !== 's') { document.getElementById('wtAuthWall').style.display = ''; return; }
    document.getElementById('wtPage').style.display = '';
    load();
});
