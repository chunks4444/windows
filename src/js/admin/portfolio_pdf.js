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


function pfWho(r) {
    if (r.user_id) {
        const nm = r.name ? pfEsc(r.name) : '(이름 없음)';
        return nm + (r.email ? ' <span style="color:var(--text-muted);">' + pfEsc(r.email) + '</span>' : '');
    }
    if (r.ip) return pfEsc(r.ip);
    return '<span style="color:var(--text-muted);">IP 삭제됨 · ' + pfEsc(r.ip_hash || '-') + '</span>';
}

function pfKind(r) {
    return r.user_id ? '회원' : '비회원';
}

function pfDlRender(d) {
    const sum = document.getElementById('pfDlSummary');
    const body = document.getElementById('pfDlPeople');
    const recent = document.getElementById('pfDlRecent');
    if (!d) { sum.textContent = ''; body.innerHTML = ''; recent.innerHTML = ''; return; }
    const t = d.totals;
    sum.innerHTML =
        '내려받기 <strong>' + t.dl + '</strong>회 · 바로 보기 <strong>' + t.view + '</strong>회 · 안내 화면 <strong>' + t.landing + '</strong>회<br>' +
        '<span style="color:var(--text-muted);font-size:13px;">사람 ' + t.people + '명 (회원 ' + t.members + ' · 비회원 ' + t.guests + ')</span>';
    if (!d.people.length) {
        body.innerHTML = '<tr><td colspan="8" style="color:var(--text-muted);">아직 기록이 없습니다.</td></tr>';
    } else {
        body.innerHTML = d.people.map(r =>
            '<tr><td>' + pfWho(r) + '</td><td>' + pfKind(r) + '</td>' +
            '<td class="text-end">' + r.dl + '</td><td class="text-end">' + r.view_cnt + '</td><td class="text-end">' + r.landing + '</td>' +
            '<td>' + (r.from_company > 0 ? '회사 소개' : '링크') + (r.bot_hits > 0 ? ' <span style="color:var(--text-muted);">· 봇 ' + r.bot_hits + '</span>' : '') + '</td>' +
            '<td>' + pfEsc((r.first_at || '').slice(0, 16)) + '</td><td>' + pfEsc((r.last_at || '').slice(0, 16)) + '</td></tr>'
        ).join('');
    }
    const modeLabel = { dl: '내려받기', view: '바로 보기', landing: '안내 화면' };
    recent.innerHTML = d.recent.length ? d.recent.map(r =>
        '<tr><td>' + pfEsc((r.created_at || '').slice(0, 16)) + '</td><td>' + pfWho(r) + '</td><td>' + (modeLabel[r.mode] || pfEsc(r.mode)) +
        (r.visitor_type && r.visitor_type !== 'human' ? ' <span style="color:var(--text-muted);">· 봇</span>' : '') + '</td>' +
        '<td>' + (r.source === 'company' ? '회사 소개' : '링크') + '</td></tr>'
    ).join('') : '<tr><td colspan="4" style="color:var(--text-muted);">아직 기록이 없습니다.</td></tr>';
}

async function pfLoad() {
    try {
        const all = document.getElementById('pfDlAll')?.checked;
        const res = await fetch(PF_API + (all ? '?all=1' : ''), { headers: { Authorization: 'Bearer ' + PF_TOKEN() } });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || '불러오기 실패');
        pfRender(data);
        pfDlRender(data.downloads);
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
    document.getElementById('pfDlAll').addEventListener('change', pfLoad);
    document.getElementById('pfCopy').addEventListener('click', async e => {
        const url = e.currentTarget.dataset.url || '';
        try { await navigator.clipboard.writeText(url); pfStatus('링크를 복사했습니다.', true); }
        catch { pfStatus('복사하지 못했습니다: ' + url, false); }
    });
});
