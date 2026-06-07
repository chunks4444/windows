const token = () => localStorage.getItem('pmok_auth_token');

async function loadProfile() {
    if (!token()) { location.href = '/'; return; }
    document.getElementById('profilePage').style.display = '';

    const res  = await fetch('/src/api/auth/profile.php', {
        headers: { 'Authorization': 'Bearer ' + token() }
    });
    const data = await res.json();
    if (!res.ok) return;

    const u = data.user;
    document.getElementById('pfEmail').textContent        = u.email          || '—';
    const roleMap = { s: '슈퍼', m: '관리자', a: '작가', u: '회원' };
    const badge = document.getElementById('pfRoleBadge');
    badge.textContent  = roleMap[u.role] || u.role || '';
    badge.dataset.role = u.role || 'u';
    document.getElementById('pfName').value               = u.name           || '';
    document.getElementById('pfPhone').value              = u.phone          || '';
    document.getElementById('pfZipcode').value            = u.zipcode        || '';
    document.getElementById('pfAddress').value            = u.address        || '';
    document.getElementById('pfAddressDetail').value      = u.address_detail || '';
}

document.getElementById('pfForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const alertEl = document.getElementById('pfAlert');
    const btn     = document.getElementById('pfSaveBtn');

    alertEl.style.display = 'none';
    btn.disabled    = true;
    btn.textContent = '저장 중…';

    const body = {
        name:           document.getElementById('pfName').value.trim(),
        phone:          document.getElementById('pfPhone').value.trim(),
        zipcode:        document.getElementById('pfZipcode').value.trim(),
        address:        document.getElementById('pfAddress').value.trim(),
        address_detail: document.getElementById('pfAddressDetail').value.trim(),
    };

    try {
        const res  = await fetch('/src/api/auth/profile.php', {
            method:  'PUT',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() },
            body: JSON.stringify(body),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || '오류가 발생했습니다.');
        showAlert('저장되었습니다.', 'success');
    } catch (err) {
        showAlert(err.message, 'error');
    } finally {
        btn.disabled    = false;
        btn.textContent = '저장';
    }
});

document.getElementById('pfPwForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn     = document.getElementById('pfPwSaveBtn');
    const current = document.getElementById('pfPwCurrent').value;
    const next    = document.getElementById('pfPwNew').value;
    const confirm = document.getElementById('pfPwConfirm').value;

    if (next.length < 6) return showPwAlert('새 비밀번호는 6자 이상이어야 합니다.', 'error');
    if (next !== confirm) return showPwAlert('새 비밀번호가 일치하지 않습니다.', 'error');

    btn.disabled    = true;
    btn.textContent = '변경 중…';
    try {
        const res  = await fetch('/src/api/auth/password.php', {
            method:  'PUT',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() },
            body: JSON.stringify({ current, password: next }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || '오류가 발생했습니다.');
        showPwAlert('비밀번호가 변경되었습니다.', 'success');
        document.getElementById('pfPwForm').reset();
    } catch (err) {
        showPwAlert(err.message, 'error');
    } finally {
        btn.disabled    = false;
        btn.textContent = '변경';
    }
});

function showPwAlert(msg, type) {
    const el = document.getElementById('pfPwAlert');
    el.textContent   = msg;
    el.className     = 'pf-alert pf-alert--' + type;
    el.style.display = '';
    setTimeout(() => { el.style.display = 'none'; }, 3000);
}

function showAlert(msg, type) {
    const el = document.getElementById('pfAlert');
    el.textContent   = msg;
    el.className     = 'pf-alert pf-alert--' + type;
    el.style.display = '';
    setTimeout(() => { el.style.display = 'none'; }, 3000);
}

function openPostcode() {
    new daum.Postcode({
        oncomplete(data) {
            document.getElementById('pfZipcode').value       = data.zonecode;
            document.getElementById('pfAddress').value       = data.roadAddress || data.autoRoadAddress || data.jibunAddress;
            document.getElementById('pfAddressDetail').value = '';
            document.getElementById('pfAddressDetail').focus();
        }
    }).open();
}

document.addEventListener('DOMContentLoaded', loadProfile);
window.addEventListener('pmokAuthChanged', () => {
    document.getElementById('profileAuthWall').style.display = 'none';
    loadProfile();
});
