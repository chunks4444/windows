const TOKEN = () => localStorage.getItem('pmok_auth_token');

async function loadRenderConfig() {
    const res  = await fetch('/src/api/admin/render_config.php', { headers: { Authorization: 'Bearer ' + TOKEN() } });
    const data = await res.json();
    if (data.render_quality) document.getElementById('render_quality').value = data.render_quality;
    if (data.openai_api_key) document.getElementById('openai_api_key').value = data.openai_api_key;
}

async function saveRenderConfig() {
    const btn    = event.target;
    const status = document.getElementById('render_status');
    btn.disabled = true;
    status.textContent = '';
    try {
        const res  = await fetch('/src/api/admin/render_config.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + TOKEN() },
            body:    JSON.stringify({
                render_quality: document.getElementById('render_quality').value,
                openai_api_key: document.getElementById('openai_api_key').value,
            }),
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

document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('renderSettingsPage');
    if (authGetToken()) { page.style.display = ''; loadRenderConfig(); }
    window.addEventListener('pmokAuthChanged', () => { page.style.display = ''; loadRenderConfig(); });
    authUpdateNav();
});
