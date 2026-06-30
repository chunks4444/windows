const TOKEN = () => localStorage.getItem('pmok_auth_token');

async function loadRenderConfig() {
    const res  = await fetch('/src/api/admin/render_config.php', { headers: { Authorization: 'Bearer ' + TOKEN() } });
    const data = await res.json();
    if (data.render_quality)    document.getElementById('render_quality').value    = data.render_quality;
    if (data.openai_api_key)    document.getElementById('openai_api_key').value    = data.openai_api_key;
    if (data.anthropic_api_key) document.getElementById('anthropic_api_key').value = data.anthropic_api_key;
    if (data.ai_chat_model)     document.getElementById('ai_chat_model').value     = data.ai_chat_model;
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

async function saveAiConfig() {
    const status = document.getElementById('ai_status');
    status.textContent = '';
    try {
        const res = await fetch('/src/api/admin/render_config.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + TOKEN() },
            body:    JSON.stringify({
                anthropic_api_key: document.getElementById('anthropic_api_key').value,
                ai_chat_model:     document.getElementById('ai_chat_model').value,
            }),
        });
        const data = await res.json();
        if (res.ok) { status.className = 'oauth-status ok';  status.textContent = '저장됨'; }
        else        { status.className = 'oauth-status err'; status.textContent = data.error || '오류'; }
    } catch {
        status.className = 'oauth-status err'; status.textContent = '서버 오류';
    } finally {
        setTimeout(() => status.textContent = '', 3000);
    }
}

async function testAiConnection() {
    const btn    = document.getElementById('btnTest');
    const status = document.getElementById('test_status');
    btn.disabled = true;
    status.className = 'oauth-status';
    status.textContent = '테스트 중…';
    try {
        const res  = await fetch('/src/api/admin/render_config.php', {
            method:  'DELETE',
            headers: { Authorization: 'Bearer ' + TOKEN() },
        });
        const data = await res.json();
        if (data.ok) { status.className = 'oauth-status ok';  status.textContent = '✓ ' + data.message; }
        else         { status.className = 'oauth-status err'; status.textContent = '✗ ' + (data.error || '실패'); }
    } catch {
        status.className = 'oauth-status err'; status.textContent = '서버 오류';
    } finally {
        btn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('renderSettingsPage');
    if (authGetToken()) { page.style.display = ''; loadRenderConfig(); }
    window.addEventListener('pmokAuthChanged', () => { page.style.display = ''; loadRenderConfig(); });
    authUpdateNav();
});
