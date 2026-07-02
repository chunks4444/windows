const TOKEN = () => localStorage.getItem('pmok_auth_token');

async function loadRenderConfig() {
    const res  = await fetch('/src/api/admin/render_config.php', { headers: { Authorization: 'Bearer ' + TOKEN() } });
    const data = await res.json();
    if (data.render_quality)    document.getElementById('render_quality').value    = data.render_quality;
    if (data.openai_api_key)    document.getElementById('openai_api_key').value    = data.openai_api_key;
    if (data.anthropic_api_key) document.getElementById('anthropic_api_key').value = data.anthropic_api_key;
    if (data.ai_chat_model)     document.getElementById('ai_chat_model').value     = data.ai_chat_model;
    if (data.render_base_prompt !== undefined) document.getElementById('render_base_prompt').value = data.render_base_prompt;
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
                render_quality:      document.getElementById('render_quality').value,
                openai_api_key:      document.getElementById('openai_api_key').value,
                render_base_prompt:  document.getElementById('render_base_prompt').value,
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

/* ── 재질/조명 프리셋 관리 ── */
const RP_API = '/src/api/admin/render_presets.php';
let _presets = [];

function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

async function loadPresets() {
    const res = await fetch(RP_API, { headers: { Authorization: 'Bearer ' + TOKEN() } });
    _presets = (await res.json()).presets || [];
    renderPresets();
}

function renderPresets() {
    document.getElementById('rpBody').innerHTML = _presets.map(p => `
        <tr class="${p.is_active == '1' ? '' : 'rp-inactive'}" id="rp-row-${p.id}">
            <td><span class="rp-id">${p.id}</span></td>
            <td><input class="rp-label-input" value="${escHtml(p.label)}" id="rp-label-${p.id}"></td>
            <td><input class="rp-prompt-input" value="${escHtml(p.prompt_text)}" id="rp-prompt-${p.id}"></td>
            <td><input class="rp-sort-input" type="number" value="${p.sort_order}" id="rp-sort-${p.id}"></td>
            <td><input type="checkbox" ${p.is_active == '1' ? 'checked' : ''} onchange="togglePreset(${p.id}, this.checked)"></td>
            <td style="display:flex;gap:6px;align-items:center;">
                <button class="rp-btn rp-btn-save" onclick="savePreset(${p.id})">저장</button>
                <button class="rp-btn rp-btn-del"  onclick="deletePreset(${p.id}, '${escHtml(p.label)}')">삭제</button>
                <span class="rp-status" id="rp-st-${p.id}"></span>
            </td>
        </tr>`).join('');
}

async function savePreset(id) {
    const label  = document.getElementById(`rp-label-${id}`).value.trim();
    const prompt = document.getElementById(`rp-prompt-${id}`).value.trim();
    const sort   = parseInt(document.getElementById(`rp-sort-${id}`).value) || 0;
    const active = document.querySelector(`#rp-row-${id} input[type=checkbox]`).checked ? 1 : 0;
    const st     = document.getElementById(`rp-st-${id}`);
    if (!label || !prompt) { st.className = 'rp-status err'; st.textContent = '이름/프롬프트 필수'; return; }
    const data = await (await fetch(RP_API, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + TOKEN() },
        body: JSON.stringify({ id, label, prompt_text: prompt, sort_order: sort, is_active: active }),
    })).json();
    st.className = data.ok ? 'rp-status ok' : 'rp-status err';
    st.textContent = data.ok ? '저장됨' : (data.error || '오류');
    if (data.ok) {
        const p = _presets.find(x => x.id == id);
        if (p) { p.label = label; p.prompt_text = prompt; p.sort_order = sort; p.is_active = active; }
        setTimeout(() => st.textContent = '', 2500);
    }
}

async function togglePreset(id, checked) {
    const label  = document.getElementById(`rp-label-${id}`).value.trim();
    const prompt = document.getElementById(`rp-prompt-${id}`).value.trim();
    const sort   = parseInt(document.getElementById(`rp-sort-${id}`).value) || 0;
    await fetch(RP_API, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + TOKEN() },
        body: JSON.stringify({ id, label, prompt_text: prompt, sort_order: sort, is_active: checked ? 1 : 0 }),
    });
    document.getElementById(`rp-row-${id}`).className = checked ? '' : 'rp-inactive';
}

async function deletePreset(id, label) {
    if (!confirm(`"${label}" 프리셋을 삭제하시겠습니까?`)) return;
    const data = await (await fetch(RP_API, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + TOKEN() },
        body: JSON.stringify({ id }),
    })).json();
    if (data.ok) { _presets = _presets.filter(p => p.id != id); renderPresets(); }
}

async function addPreset() {
    const st     = document.getElementById('rpAddStatus');
    const label  = document.getElementById('rpAddLabel').value.trim();
    const prompt = document.getElementById('rpAddPrompt').value.trim();
    if (!label || !prompt) { st.className = 'rp-status err'; st.textContent = '이름/프롬프트를 입력하세요'; return; }
    const data = await (await fetch(RP_API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + TOKEN() },
        body: JSON.stringify({ label, prompt_text: prompt }),
    })).json();
    if (data.ok) {
        st.className = 'rp-status ok'; st.textContent = '추가됨';
        document.getElementById('rpAddLabel').value = '';
        document.getElementById('rpAddPrompt').value = '';
        _presets.push({ id: data.id, label, prompt_text: prompt, sort_order: _presets.length, is_active: '1' });
        renderPresets();
        setTimeout(() => st.textContent = '', 2500);
    } else {
        st.className = 'rp-status err'; st.textContent = data.error || '오류';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('renderSettingsPage');
    if (authGetToken()) { page.style.display = ''; loadRenderConfig(); loadPresets(); }
    window.addEventListener('pmokAuthChanged', () => { page.style.display = ''; loadRenderConfig(); loadPresets(); });
    authUpdateNav();
});
