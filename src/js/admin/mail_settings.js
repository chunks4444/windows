const TOKEN = () => localStorage.getItem('pmok_auth_token');

async function loadMailConfig() {
    const res  = await fetch('/src/api/admin/mail_config.php', { headers: { Authorization: 'Bearer ' + TOKEN() } });
    const data = await res.json();
    if (data.mail_smtp_user) document.getElementById('mail_smtp_user').value = data.mail_smtp_user;
    if (data.mail_smtp_pass) document.getElementById('mail_smtp_pass').value = data.mail_smtp_pass;
    if (data.mail_sales)     document.getElementById('mail_sales').value     = data.mail_sales;
    if (data.mail_member)    document.getElementById('mail_member').value    = data.mail_member;
}

async function saveMailKeys(keys, statusId) {
    const status = document.getElementById(statusId);
    const btn    = event.target;
    btn.disabled = true;
    status.textContent = '';
    try {
        const body = {};
        keys.forEach(k => body[k] = document.getElementById(k).value);
        const res  = await fetch('/src/api/admin/mail_config.php', {
            method:  'PUT',
            headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + TOKEN() },
            body:    JSON.stringify(body),
        });
        const data = await res.json();
        if (res.ok) { status.className = 'oauth-status ok';  status.textContent = '저장됨'; }
        else        { status.className = 'oauth-status err'; status.textContent = data.error || '오류'; }
    } catch {
        status.className = 'oauth-status err'; status.textContent = '서버 오류';
    } finally {
        btn.disabled = false;
        setTimeout(() => status.textContent = '', 3000);
    }
}

function saveSmtpAuth()      { saveMailKeys(['mail_smtp_user', 'mail_smtp_pass'], 'smtp_status'); }
function saveMailAddresses() { saveMailKeys(['mail_sales', 'mail_member'], 'addr_status'); }

document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('mailSettingsPage');
    if (authGetToken()) { page.style.display = ''; loadMailConfig(); }
    window.addEventListener('pmokAuthChanged', () => { page.style.display = ''; loadMailConfig(); });
    authUpdateNav();
});
