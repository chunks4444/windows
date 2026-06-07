const token = () => localStorage.getItem('pmok_auth_token');

async function loadCompany() {
    if (!token()) { location.href = '/'; return; }
    document.getElementById('companyPage').style.display = '';

    const [meRes, res] = await Promise.all([
        fetch('/src/api/auth/profile.php', { headers: { 'Authorization': 'Bearer ' + token() } }),
        fetch('/src/api/auth/company.php',  { headers: { 'Authorization': 'Bearer ' + token() } }),
    ]);
    const meData = await meRes.json();
    const data   = await res.json();
    if (!res.ok) return;

    document.getElementById('cpEmail').textContent = meData.user?.email || '—';

    const c = data.company;
    document.getElementById('cpName').value          = c.company_name           || '';
    document.getElementById('cpBizNo').value         = c.company_biz_no         || '';
    document.getElementById('cpBizType').value       = c.company_biz_type       || '';
    document.getElementById('cpBizCat').value        = c.company_biz_category   || '';
    document.getElementById('cpCeo').value           = c.company_ceo            || '';
    document.getElementById('cpPhone').value         = c.company_phone          || '';
    document.getElementById('cpZipcode').value       = c.company_zipcode        || '';
    document.getElementById('cpAddress').value       = c.company_address        || '';
    document.getElementById('cpAddressDetail').value = c.company_address_detail || '';
}

document.getElementById('cpForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('cpSaveBtn');
    btn.disabled    = true;
    btn.textContent = '저장 중…';

    const body = {
        company_name:           document.getElementById('cpName').value.trim(),
        company_biz_no:         document.getElementById('cpBizNo').value.trim(),
        company_biz_type:       document.getElementById('cpBizType').value.trim(),
        company_biz_category:   document.getElementById('cpBizCat').value.trim(),
        company_ceo:            document.getElementById('cpCeo').value.trim(),
        company_phone:          document.getElementById('cpPhone').value.trim(),
        company_zipcode:        document.getElementById('cpZipcode').value.trim(),
        company_address:        document.getElementById('cpAddress').value.trim(),
        company_address_detail: document.getElementById('cpAddressDetail').value.trim(),
    };

    try {
        const res  = await fetch('/src/api/auth/company.php', {
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

function showAlert(msg, type) {
    const el = document.getElementById('cpAlert');
    el.textContent   = msg;
    el.className     = 'pf-alert pf-alert--' + type;
    el.style.display = '';
    setTimeout(() => { el.style.display = 'none'; }, 3000);
}

function openPostcode() {
    new daum.Postcode({
        oncomplete(data) {
            document.getElementById('cpZipcode').value       = data.zonecode;
            document.getElementById('cpAddress').value       = data.roadAddress || data.autoRoadAddress || data.jibunAddress;
            document.getElementById('cpAddressDetail').value = '';
            document.getElementById('cpAddressDetail').focus();
        }
    }).open();
}

document.addEventListener('DOMContentLoaded', loadCompany);
window.addEventListener('pmokAuthChanged', () => {
    document.getElementById('pageAuthWall').style.display = 'none';
    loadCompany();
});
