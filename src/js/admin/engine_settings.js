const token = () => localStorage.getItem('pmok_auth_token');
const hdr   = () => ({ 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() });

const ENGINES = window.__pmokEngineKeys || ['classic', 'square', 'cross', 'diamond', 'triangle', 'hexagon'];
const ENGINE_LABEL = window.__pmokEngineLabels || {};

const ENGINE_SECTIONS = [
    {
        id: 'basic', title: '문 기본',
        fields: [
            { key: 'doorType',    label: '문 타입',           hint: 'swing / slide' },
            { key: 'doorCount',   label: '기본 짝 수',         hint: '1~4' },
            { key: 'W',           label: '문틀 가로 (mm)',     hint: '' },
            { key: 'H',           label: '문틀 세로 (mm)',     hint: '' },
            { key: 'gap',         label: '문짝 사이 틈 (mm)',  hint: '여닫이용' },
            { key: 'min_days',       label: '최소 납기',      hint: '일 · 납기 기준' },
            { key: 'min_work_hours', label: '최소 작업 시간', hint: '시간 · 제작 원가 기준' },
            { key: 'dimensionOn', label: '치수 표기 기본값',  hint: '0 / 1' },
            { key: 'basePadding', label: '캔버스 여백 (px)',  hint: '' },
        ],
    },
    {
        id: 'ulgeomi', title: '울거미·살',
        fields: [
            { key: 'frame',      label: '좌우 울거미 두께 (mm)', hint: '' },
            { key: 'frameH',     label: '상하 울거미 두께 (mm)', hint: '' },
            { key: 'ulgeomiW',   label: '울거미 폭 (mm)',        hint: '원가 계산용' },
            { key: 'slat',       label: '살 두께 (mm)',          hint: '' },
            { key: 'slatW',      label: '살 폭 (mm)',            hint: '원가 계산용' },
            { key: 'cols',       label: '가로 칸수',             hint: '' },
            { key: 'ratio',      label: '세로 비율',             hint: 'classic/square' },
            { key: 'patternTop', label: '가로살 상',             hint: 'classic 전용' },
            { key: 'patternMid', label: '가로살 중',             hint: 'classic 전용' },
            { key: 'patternBot', label: '가로살 하',             hint: 'classic 전용' },
            { key: 'shrinkH',    label: '세로 자동 맞춤',        hint: '0 / 1' },
            { key: 'rowsManual', label: '가로살 개수 직접 지정', hint: '0 / 1 · square 전용' },
            { key: 'rows',       label: '세로 칸수',             hint: 'square 전용, rowsManual=1일 때' },
            { key: 'rotate',     label: '패턴 세로 방향',        hint: '0 / 1' },
        ],
    },
    {
        id: 'muntol_pungpan', title: '문틀 · 풍판',
        fields: [
            { key: 'frameThick',  label: '문틀 설치 깊이 (mm)',   hint: '캔버스 윤곽선 기준' },
            { key: 'frameGap',    label: '문틀-문 설치 틈 (mm)',  hint: '' },
            { key: 'muntolFace',  label: '문틀 정면 폭 (mm)',     hint: '보이는 면' },
            { key: 'muntolT',     label: '문틀 두께 (mm)',        hint: '30 보이는 면 + 8 턱' },
            { key: 'muntolW',     label: '문틀 폭 (mm)',          hint: '벽 안으로 들어가는 깊이' },
            { key: 'pungpanOn',   label: '풍판 기본 사용',        hint: '0 / 1' },
            { key: 'pungpan',     label: '풍판 높이 (mm)',        hint: '' },
            { key: 'pungpanT',    label: '풍판 판재 두께 (mm)',   hint: '원가 계산용' },
        ],
    },
];

const engineSelect = document.getElementById('engineSelect');
let currentSettings = {};

function init() {
    ENGINES.forEach(e => {
        const opt = document.createElement('option');
        opt.value = e;
        opt.textContent = ENGINE_LABEL[e] || e;
        engineSelect.appendChild(opt);
    });
}

function renderGrid() {
    const grid = document.getElementById('settingsGrid');
    const knownKeys = new Set(ENGINE_SECTIONS.flatMap(s => s.fields.map(f => f.key)));

    let html = '';

    // 엔진별 섹션들 (2열 그리드에 들어감)
    ENGINE_SECTIONS.forEach(sec => {
        const rows = sec.fields.map(f => fieldRow(f.key, currentSettings[f.key] ?? '', f.label, f.hint)).join('');
        html += `<div class="eng-section">
            <div class="eng-section-title">${esc(sec.title)}</div>
            ${rows}
        </div>`;
    });

    // 사용자 정의 키 (어떤 섹션에도 없는 것)
    const rest = Object.keys(currentSettings).filter(k => !knownKeys.has(k));
    if (rest.length) {
        const rows = rest.map(k => fieldRow(k, currentSettings[k], k, '')).join('');
        html += `<div class="eng-section">
            <div class="eng-section-title">사용자 정의</div>
            ${rows}
        </div>`;
    }

    grid.innerHTML = html;
}

function fieldRow(key, value, label, hint) {
    return `<div class="field-row">
        <label class="field-label">${esc(label)}<span class="field-hint">${esc(hint)}</span></label>
        <div class="field-input">
            <input type="text" class="form-control form-control-sm"
                   data-key="${esc(key)}"
                   value="${esc(String(value ?? ''))}">
        </div>
    </div>`;
}

async function saveSettings() {
    const engine  = engineSelect.value;
    const inputs  = [...document.querySelectorAll('#settingsGrid input[data-key]')];
    const merged  = Object.assign({}, currentSettings);
    inputs.forEach(inp => { merged[inp.dataset.key] = inp.value; });

    const res  = await fetch('/src/api/admin/engine_settings.php', {
        method: 'POST', headers: hdr(),
        body: JSON.stringify({ engine, settings: merged }),
    });
    const data = await res.json();
    if (!res.ok) { alert(data.error || '저장 실패'); return; }
    currentSettings = data.settings || {};

    renderGrid();
    const btn = document.getElementById('btnSave');
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check-lg"></i> 저장 완료';
    setTimeout(() => { btn.innerHTML = orig; }, 1500);
}

async function loadAll() {
    const engine = engineSelect.value;
    const res  = await fetch(`/src/api/admin/engine_settings.php?engine=${encodeURIComponent(engine)}`, { headers: hdr() });
    const data = await res.json();
    if (!res.ok) { alert(data.error || '불러오기 실패'); return; }
    currentSettings = data.settings || {};
    renderGrid();
    document.getElementById('engineSettingsPage').style.display = '';
}

function esc(s) {
    return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

init();
engineSelect.addEventListener('change', loadAll);
document.getElementById('btnSave').addEventListener('click', saveSettings);
window.addEventListener('load', loadAll);
