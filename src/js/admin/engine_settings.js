const token = () => localStorage.getItem('pmok_auth_token');
const hdr   = () => ({ 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() });

// 알려진 키 → 한글 레이블 (없는 키는 키 이름 그대로 표시 — 새로 생기는 설정값도 자동으로 보임)
const KEY_LABELS = {
    doorType:    '문 타입 (swing/slide)',
    doorCount:   '문짝 수 (1~4)',
    W:           '문틀 가로 (mm)',
    H:           '문틀 세로 (mm)',
    cols:        '가로 칸수',
    frame:       '좌우 울거미 두께 (mm)',
    frameH:      '상하 울거미 두께 (mm)',
    frameThick:  '문틀 두께 (mm)',
    frameGap:    '문틀-문 설치 틈 (mm)',
    slat:        '살 두께 (mm)',
    ulgeomiW:    '울거미 폭 (mm)',
    slatW:       '살 폭 (mm)',
    ratio:       '세로 비율',
    patternTop:  '가로살 상',
    patternMid:  '가로살 중',
    patternBot:  '가로살 하',
    shrinkH:     '세로 자동 맞춤 (0/1)',
    rotate:      '패턴 세로 방향 (0/1)',
    pungpanOn:   '풍판 사용 (0/1)',
    pungpan:     '풍판 높이 (mm)',
    pungpanT:    '풍판 판재 두께 (mm)',
    dimensionOn: '치수 표기 (0/1)',
    gap:         '문짝 사이 틈 — 여닫이 (mm)',
    basePadding: '캔버스 여백 (px)',
};

const engineSelect = document.getElementById('engineSelect');
let currentSettings = {};

function esc(s) {
    return String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

async function loadSettings() {
    const engine = engineSelect.value;
    const res  = await fetch(`/src/api/admin/engine_settings.php?engine=${encodeURIComponent(engine)}`, { headers: hdr() });
    const data = await res.json();
    if (!res.ok) { alert(data.error || '불러오기 실패'); return; }
    currentSettings = data.settings || {};
    renderTable();
    document.getElementById('engineSettingsPage').style.display = '';
}

function renderTable() {
    const tbody = document.getElementById('settingsBody');
    tbody.innerHTML = '';
    Object.keys(currentSettings).forEach(key => addSettingRow(key, currentSettings[key]));
}

function addSettingRow(key = '', value = '') {
    const tbody = document.getElementById('settingsBody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="setting-label">${esc(KEY_LABELS[key] || (key ? '' : '(새 항목)'))}</td>
        <td><input type="text" class="form-control form-control-sm setting-key" value="${esc(key)}" placeholder="키 이름" oninput="this.closest('tr').querySelector('.setting-label').textContent = (window.KEY_LABELS||{})[this.value] || ''"></td>
        <td><input type="text" class="form-control form-control-sm setting-value" value="${esc(value)}"></td>
        <td><button class="adm-del-btn" style="height:26px;padding:0 10px;font-size:11px;" onclick="this.closest('tr').remove()">삭제</button></td>`;
    tbody.appendChild(tr);
}

async function saveSettings() {
    const engine = engineSelect.value;
    const rows = [...document.querySelectorAll('#settingsBody tr')];
    const settings = {};
    for (const row of rows) {
        const key = row.querySelector('.setting-key').value.trim();
        const value = row.querySelector('.setting-value').value;
        if (key) settings[key] = value;
    }
    const res  = await fetch('/src/api/admin/engine_settings.php', {
        method: 'POST', headers: hdr(), body: JSON.stringify({ engine, settings }),
    });
    const data = await res.json();
    if (!res.ok) { alert(data.error || '저장 실패'); return; }
    currentSettings = data.settings || {};
    renderTable();
    alert('저장되었습니다.');
}

window.KEY_LABELS = KEY_LABELS;
engineSelect.addEventListener('change', loadSettings);
window.addEventListener('load', loadSettings);
