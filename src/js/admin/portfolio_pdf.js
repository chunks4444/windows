const PF_API = '/src/api/admin/portfolio_pdf.php';
const PF_TOKEN = () => localStorage.getItem('pmok_auth_token');
const pfEsc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

function pfStatus(msg, ok) {
    const el = document.getElementById('pfStatus');
    el.className = 'oauth-status ' + (ok ? 'ok' : 'err');
    el.textContent = msg;
    if (ok) setTimeout(() => { if (el.textContent === msg) el.textContent = ''; }, 4000);
}

function pfRender(data) {
    const box = document.getElementById('pfCurrent');
    const btns = document.getElementById('pfCurrentBtns');
    const cur = data.current;
    if (!cur) {
        box.textContent = '공개 중인 포트폴리오 PDF가 없습니다. 아래에서 올려 주세요. (회사 소개 페이지의 다운로드 버튼도 숨겨져 있습니다)';
        btns.style.display = 'none';
        return;
    }
    const mb = (cur.size / 1048576).toFixed(1);
    const src = cur.source === 'upload'
        ? '이 페이지에서 올린 파일'
        : '홍보메일 템플릿에 첨부된 파일 (이 페이지에서 올리면 이 파일 대신 새로 올린 파일이 공개됩니다)';
    box.innerHTML =
        '<strong>' + pfEsc(cur.name) + '</strong> <span style="color:var(--text-muted);">· ' + mb + 'MB</span><br>' +
        '<span style="color:var(--text-muted);font-size:13px;">' + pfEsc(src) + ' · ' + pfEsc((cur.uploaded_at || '').slice(0, 16)) + '</span>' +
        (data.template_same ? '<br><span style="color:var(--text-muted);font-size:13px;">홍보메일 템플릿에도 같은 파일이 붙어 있습니다.</span>' : '');
    document.getElementById('pfOpen').href = cur.url;
    document.getElementById('pfCopy').dataset.url = location.origin + cur.url;
    btns.style.display = 'flex';
}

async function pfLoad() {
    try {
        const res = await fetch(PF_API, { headers: { Authorization: 'Bearer ' + PF_TOKEN() } });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || '불러오기 실패');
        pfRender(data);
    } catch (e) {
        document.getElementById('pfCurrent').textContent = '불러오지 못했습니다: ' + (e.message || e);
    }
}

async function pfUpload() {
    const input = document.getElementById('pfFile');
    const file = input.files[0];
    if (!file) { pfStatus('PDF 파일을 선택해 주세요.', false); return; }
    if (!/\.pdf$/i.test(file.name)) { pfStatus('PDF 파일만 올릴 수 있습니다.', false); return; }
    if (file.size > 30 * 1048576) { pfStatus('PDF는 30MB 이하여야 합니다.', false); return; }
    const btn = document.getElementById('pfUpload');
    btn.disabled = true;
    pfStatus('올리는 중… (' + (file.size / 1048576).toFixed(1) + 'MB)', true);
    try {
        const fd = new FormData();
        fd.append('pdf', file);
        if (document.getElementById('pfApplyTpl').checked) fd.append('apply_template', '1');
        const res = await fetch(PF_API, { method: 'POST', headers: { Authorization: 'Bearer ' + PF_TOKEN() }, body: fd });
        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.ok) throw new Error(data.error || '업로드 실패');
        pfRender(data);
        input.value = '';
        pfStatus('올렸습니다.' + (data.applied_template ? ' 홍보메일 템플릿도 바꿨습니다.' : ''), true);
    } catch (e) {
        pfStatus(e.message || '업로드 실패', false);
    } finally {
        btn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('pfPdfPage');
    const start = () => { page.style.display = ''; pfLoad(); };
    if (authGetToken()) start();
    window.addEventListener('pmokAuthChanged', start);
    authUpdateNav();

    document.getElementById('pfUpload').addEventListener('click', pfUpload);
    document.getElementById('pfCopy').addEventListener('click', async e => {
        const url = e.currentTarget.dataset.url || '';
        try { await navigator.clipboard.writeText(url); pfStatus('링크를 복사했습니다.', true); }
        catch { pfStatus('복사하지 못했습니다: ' + url, false); }
    });
});
