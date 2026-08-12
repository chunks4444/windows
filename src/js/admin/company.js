const API = '/src/api/admin/company.php';
function _h() { return { 'Authorization': 'Bearer ' + localStorage.getItem('pmok_auth_token'), 'Content-Type': 'application/json' }; }

const CC_KEYS = [
    'hero_label', 'hero_title', 'hero_desc',
    'phil_heading', 'phil_text',
    'phil_item1_title', 'phil_item1_desc',
    'phil_item2_title', 'phil_item2_desc',
    'phil_item3_title', 'phil_item3_desc',
    'studio_label', 'studio_title', 'studio_body',
    'contact_label', 'contact_title', 'contact_body',
];

async function loadContent() {
    const res  = await fetch(API, { headers: _h() });
    const data = await res.json();
    if (!res.ok) { document.getElementById('ccAuthWall').style.display = ''; return; }
    const content = data.content || {};
    CC_KEYS.forEach(key => {
        const el = document.getElementById('cc_' + key);
        if (el) el.value = content[key] ?? '';
    });
}

async function saveContent() {
    const values = {};
    CC_KEYS.forEach(key => {
        const el = document.getElementById('cc_' + key);
        if (el) values[key] = el.value;
    });
    const res  = await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'save', values }) });
    const data = await res.json();
    const st = document.getElementById('ccSaveStatus');
    if (data.ok) {
        st.className = 'pc-status ok'; st.textContent = '저장됨';
        setTimeout(() => { if (st.textContent === '저장됨') st.textContent = ''; }, 2000);
    } else {
        st.className = 'pc-status err'; st.textContent = data.error || '저장 실패';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const user = JSON.parse(localStorage.getItem('pmok_auth_user') || 'null');
    if (!user || user.role !== 's') { document.getElementById('ccAuthWall').style.display = ''; return; }
    document.getElementById('ccPage').style.display = '';
    loadContent();
});
