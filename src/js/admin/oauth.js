const TOKEN = () => localStorage.getItem('pmok_auth_token');

async function loadConfig() {
    const res  = await fetch('/src/api/admin/oauth.php', { headers: { Authorization: 'Bearer ' + TOKEN() } });
    const data = await res.json();
    const cfg  = data.config || {};
    const fill = (id, key) => { if (cfg[key]) document.getElementById(id).value = cfg[key]; };
    fill('google_client_id',     'oauth_google_client_id');
    fill('google_client_secret', 'oauth_google_client_secret');
    fill('kakao_client_id',      'oauth_kakao_client_id');
    fill('kakao_client_secret',  'oauth_kakao_client_secret');
    fill('naver_client_id',      'oauth_naver_client_id');
    fill('naver_client_secret',  'oauth_naver_client_secret');
    loadRenderConfig();
}

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
            method:  'PUT',
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

async function saveProvider(p) {
    const btn    = event.target;
    const status = document.getElementById(p + '_status');
    btn.disabled = true;
    status.textContent = '';
    try {
        const body = {
            ['oauth_' + p + '_client_id']:     document.getElementById(p + '_client_id').value,
            ['oauth_' + p + '_client_secret']: document.getElementById(p + '_client_secret').value,
        };
        const res  = await fetch('/src/api/admin/oauth.php', {
            method:  'PUT',
            headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + TOKEN() },
            body:    JSON.stringify(body),
        });
        const data = await res.json();
        if (res.ok) { status.className = 'oauth-status ok'; status.textContent = '저장됨'; }
        else        { status.className = 'oauth-status err'; status.textContent = data.error || '오류'; }
    } catch {
        status.className = 'oauth-status err'; status.textContent = '서버 오류';
    } finally {
        btn.disabled = false;
        setTimeout(() => status.textContent = '', 3000);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('oauthPage');
    if (authGetToken()) { page.style.display = ''; loadConfig(); }
    window.addEventListener('pmokAuthChanged', () => { page.style.display = ''; loadConfig(); });
    authUpdateNav();
});
