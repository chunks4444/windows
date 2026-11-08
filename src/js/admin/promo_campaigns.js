const API = '/src/api/admin/promo_campaigns.php';
function _h() { return { 'Authorization': 'Bearer ' + token(), 'Content-Type': 'application/json' }; }
async function _post(body) {
    const res  = await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify(body) });
    const data = await res.json().catch(() => ({}));
    return { ok: res.ok && data.ok, data };
}

const STATUS_LABEL = { draft: '임시저장', scheduled: '예약됨', sending: '발송 중', done: '완료', canceled: '취소됨' };
let campaigns = [], templates = [], industries = [], counts = {}, pollTimer = null, logCampaignId = 0;
let coAll = [];            // 선택 가능한 회사(수신거부 제외) — 발송 횟수·마지막 발송 포함
let coSel = new Set();     // 직접 고른 대상 회사 id

function fmt(dt) { return dt ? dt.replace('T', ' ').slice(0, 16) : '—'; }

async function loadAll() {
    const res  = await fetch(API, { headers: _h() });
    const data = await res.json();
    if (!res.ok) { alert(data.error || '불러오기 실패'); return; }
    coAll = data.companies || [];
    campaigns = data.campaigns; templates = data.templates || []; fillTestCompanies(data.companies || []); industries = data.industries; counts = data.counts;
    document.getElementById('pmLimitInfo').textContent = `${data.sent_24h} / ${data.daily_limit}`;
    renderList();
    // 발송 중·예약 캠페인이 있으면 진행 상황을 주기적으로 갱신
    clearTimeout(pollTimer);
    if (campaigns.some(c => c.status === 'sending' || c.status === 'scheduled')) pollTimer = setTimeout(loadAll, 15000);
}

function indLabel(c) {
    if (c.company_ids) return `직접 선택 ${c.company_ids.split(',').filter(Boolean).length}곳`;
    return c.industries ? c.industries.split(',').join(' · ') : '전체 업종';
}

function renderList() {
    document.getElementById('pmBody').innerHTML = campaigns.length ? campaigns.map(c => {
        const editable = c.status === 'draft' || c.status === 'canceled';
        const active   = c.status === 'scheduled' || c.status === 'sending';
        const when = c.status === 'draft' ? '—' : fmt(c.started_at || c.scheduled_at);
        const prog = c.total > 0 ? `${c.sent}/${c.total} 발송${c.failed > 0 ? `, <span style="color:#c00;">실패 ${c.failed}</span>` : ''}` : '—';
        return `<tr>
            <td><strong>${esc(c.title)}</strong></td>
            <td>${esc(c.subject)}</td>
            <td>${esc(indLabel(c))}</td>
            <td><span class="pm-status ${c.status}">${STATUS_LABEL[c.status] || c.status}</span></td>
            <td>${esc(when)}</td>
            <td>${prog}</td>
            <td><div class="adm-action-cell">
                ${editable ? `<button class="adm-edit-btn" style="height:28px;padding:0 10px;font-size:12px;" onclick="openEditor(${c.id})">편집</button>` : ''}
                <button class="adm-withdraw-btn" onclick="openLog(${c.id})">이력</button>
                ${active ? `<button class="adm-withdraw-btn" onclick="cancelCampaign(${c.id})">취소</button>` : ''}
                ${!active ? `<button class="adm-withdraw-btn" style="background:#c00;color:#fff;" onclick="deleteCampaign(${c.id})">삭제</button>` : ''}
            </div></td></tr>`;
    }).join('') : `<tr><td colspan="7" style="text-align:center;padding:32px 0;"><span class="adm-null">홍보메일이 없습니다.</span></td></tr>`;
}

// ── 본문 에디터 (Jodit) ──────────────────────────────────────
// 표·박스·이미지·인라인 스타일 같은 HTML을 그대로 보존하는 WYSIWYG 에디터. "소스" 버튼으로 HTML을 직접 고칠 수도 있다.
// (예전 Quill은 지원하지 않는 서식을 지워서 본문이 깨졌다.)
let jodit = null;

function initEditor() {
    jodit = Jodit.make('#pmHtml', {
        language: 'ko',
        height: 460,
        toolbarAdaptive: false,
        buttons: ['source', '|', 'bold', 'italic', 'underline', 'strikethrough', '|', 'ul', 'ol', '|', 'paragraph', 'fontsize', 'brush', '|', 'align', '|', 'table', 'link', 'image', 'hr', '|', 'undo', 'redo', 'eraser', 'fullsize'],
        showCharsCounter: false,
        showWordsCounter: false,
        showXPathInStatusbar: false,
        askBeforePasteHTML: false,
        askBeforePasteFromWord: false,
        defaultActionOnPaste: 'insert_as_html',
        placeholder: '홍보문구를 입력하세요. {{회사명}}이 받는 회사 이름으로 바뀝니다.',
    });
}

// 전체 HTML 문서(<!DOCTYPE>·<html> 포함)는 일반 편집기(Jodit)에 넣지 않고 코드 입력칸으로 연다.
// Jodit는 값을 넣는 순간 <head>·<style>(모바일 반응형)을 지워 버려서, 한 번 저장하면 메일이 깨진다 (2026-11-09).
let rawMode = false;
function isFullDoc(html) { return /<!DOCTYPE|<html\b/i.test(html || ''); }

function setRawMode(on) {
    rawMode = on;
    document.getElementById('pmRawWrap').style.display = on ? '' : 'none';
    jodit.container.style.display = on ? 'none' : '';
}

function getBodyHtml() {
    if (rawMode) return document.getElementById('pmHtmlRaw').value;
    jodit.synchronizeValues(); // 소스 보기 중이어도 최신 내용을 반영
    return jodit.value;
}

function setBodyHtml(html) {
    if (isFullDoc(html)) {
        setRawMode(true);
        document.getElementById('pmHtmlRaw').value = html;   // 그대로 — 어떤 정리도 거치지 않는다
    } else {
        setRawMode(false);
        jodit.value = html;
    }
}

// ── 편집기 ───────────────────────────────────────────────
// 새 홍보메일을 열 때 제목 칸에 미리 들어가는 기본 제목 ({{회사명}}은 발송 시 받는 회사 이름으로 바뀜)
const DEFAULT_SUBJECT = '[제작 협력] {{회사명}} — 창호·파티션 제작 파트너 제안';

async function openEditor(id) {
    autoDraftId = 0;
    let c = null;
    if (id) {
        const res = await fetch(`${API}?id=${id}`, { headers: _h() });
        c = (await res.json()).campaign;
    }
    document.getElementById('pmEditorTitle').textContent = c ? '홍보메일 편집' : '새 홍보메일';
    document.getElementById('pmId').value      = c?.id ?? '';
    document.getElementById('pmTitle').value   = c?.title ?? '';
    document.getElementById('pmSubject').value = c ? (c.subject ?? '') : DEFAULT_SUBJECT;
    setBodyHtml(c?.html_body ?? '<h2>{{회사명}} 담당자님, 안녕하세요</h2><p>워크그룹 평목입니다. </p>');
    document.getElementById('pmWhen').value    = '';
    document.getElementById('pmEditorMsg').textContent = '';
    const picked = (c?.industries || '').split(',').filter(Boolean);
    document.getElementById('pmIndustries').innerHTML = industries.map(i =>
        `<label class="pm-chk"><input type="checkbox" value="${esc(i)}" ${picked.includes(i) ? 'checked' : ''}> ${esc(i)} <span style="color:var(--text-muted);">(${counts[i] ?? 0})</span></label>`
    ).join('') + `<span style="font-size:12px;color:var(--text-muted);">수신거부 제외 · 전체 ${counts[''] ?? 0}곳</span>`;
    // 직접 고른 대상 회사
    coSel = new Set((c?.company_ids || '').split(',').filter(Boolean).map(Number));
    document.getElementById('pmCoInd').innerHTML = '<option value="">전체 업종</option>' + industries.map(i => `<option value="${esc(i)}">${esc(i)}</option>`).join('');
    document.getElementById('pmCoSearch').value = '';
    document.getElementById('pmCoPicker').style.display = 'none';
    document.getElementById('pmCoToggle').textContent = '회사 선택 열기';
    renderCoPicker();
    showPdf(c?.attachment_name, c?.attachment_size);
    renderTemplateSelect(0);
    document.getElementById('pmEditorOverlay').classList.add('open');
}

// ── 문구 템플릿 ──────────────────────────────────────────
function tplId() { return parseInt(document.getElementById('pmTplSelect').value) || 0; }

function renderTemplateSelect(selectId) {
    const sel = document.getElementById('pmTplSelect');
    sel.innerHTML = `<option value="">${templates.length ? '템플릿 선택…' : '저장된 템플릿 없음'}</option>` +
        templates.map(t => `<option value="${t.id}">${esc(t.name)}${t.attachment_name ? ' 📎' : ''}</option>`).join('');
    sel.value = selectId ? String(selectId) : '';
    onTplSelect();
}

function onTplSelect() {
    const has = tplId() > 0;
    ['pmTplLoad', 'pmTplUpdate', 'pmTplDelete'].forEach(id => document.getElementById(id).disabled = !has);
    // 선택한 템플릿에 PDF가 있으면 파일명을 바로 보여준다
    const t = templates.find(x => x.id == tplId());
    document.getElementById('pmTplInfo').innerHTML = !t ? '' : (t.attachment_name
        ? `<i class="bi bi-file-earmark-pdf"></i> <strong>${esc(t.attachment_name)}</strong> <span style="color:var(--text-muted);">(${(t.attachment_size / 1048576).toFixed(1)}MB)</span>`
        : '<span class="adm-null">이 템플릿에는 저장된 PDF 없음 (지금 첨부한 파일은 그대로 유지됩니다)</span>');
}

async function loadTemplate() {
    const t = templates.find(x => x.id == tplId());
    if (!t) return;
    const cur = getBodyHtml().trim();
    if (cur && cur !== (t.html_body || '').trim() && !confirm('지금 편집 중인 제목과 본문이 이 템플릿 내용으로 바뀝니다. 계속할까요?')) return;
    document.getElementById('pmSubject').value = t.subject || '';
    setBodyHtml(t.html_body || '');
    // 템플릿에 PDF가 있으면 이 캠페인의 첨부로 연결한다 (같은 파일을 가리키므로 다시 올릴 필요 없음).
    // 캠페인이 저장돼 있어야 하므로 필요하면 먼저 임시저장한다. 템플릿에 PDF가 없으면 지금 첨부는 그대로 둔다.
    if (t.attachment_name) {
        showPdf(t.attachment_name, t.attachment_size);
        const id = await saveDraft(true);
        if (!id) return;
        const { ok, data } = await _post({ action: 'apply_template', campaign_id: id, template_id: t.id });
        if (ok) showPdf(data.attachment_name, data.attachment_size);
        else { msg(data.error || '템플릿 PDF 적용 실패', false); return; }
    }
    msg(`"${t.name}" 템플릿을 불러왔습니다.${t.attachment_name ? ` 첨부: ${t.attachment_name}` : (document.getElementById('pmPdfRemove').style.display === 'none' ? ' (첨부 PDF 없음)' : ' 템플릿에 PDF는 없어 지금 첨부한 파일을 그대로 둡니다. 템플릿에도 저장하려면 "선택한 템플릿에 덮어쓰기"를 누르세요.')}`, true);
}

async function saveTemplate(asNew) {
    const f = formData();
    if (!f.html_body.trim()) { msg('저장할 본문이 비어 있습니다.', false); return; }
    let id = 0, name;
    if (asNew) {
        name = prompt('새 템플릿 이름을 입력하세요', '');
        if (name === null) return;
    } else {
        const t = templates.find(x => x.id == tplId());
        if (!t) return;
        if (!confirm(`"${t.name}" 템플릿을 지금 편집 중인 제목·본문으로 덮어쓸까요?`)) return;
        id = t.id; name = t.name;
    }
    // 이 캠페인이 이미 저장돼 있으면 그 첨부 PDF도 템플릿에 함께 저장한다 (PDF를 올리는 순간 캠페인은 저장됨)
    const { ok, data } = await _post({ action: 'save_template', id, name, subject: f.subject, html_body: f.html_body, campaign_id: f.id });
    if (!ok) { msg(data.error || '템플릿 저장 실패', false); return; }
    await loadAll();
    renderTemplateSelect(data.id);
    msg(`템플릿 "${name}"을(를) 저장했습니다.`, true);
}

async function deleteTemplate() {
    const t = templates.find(x => x.id == tplId());
    if (!t || !confirm(`"${t.name}" 템플릿을 삭제할까요? (이미 만든 홍보메일에는 영향이 없습니다)`)) return;
    const { ok, data } = await _post({ action: 'delete_template', id: t.id });
    if (!ok) { msg(data.error || '삭제 실패', false); return; }
    await loadAll();
    renderTemplateSelect(0);
    msg('템플릿을 삭제했습니다.', true);
}

// ── PDF 첨부 ─────────────────────────────────────────────
function showPdf(name, size) {
    const has = !!name;
    document.getElementById('pmPdfInfo').innerHTML = has
        ? `<i class="bi bi-file-earmark-pdf"></i> <strong>${esc(name)}</strong> <span style="color:var(--text-muted);">(${(size / 1048576).toFixed(1)}MB · ${size > 8 * 1048576 ? '메일에는 첨부 안 함, 다운로드 링크로만 전달' : '메일에 첨부 + 다운로드 링크'})</span>`
        : '<span class="adm-null">첨부 없음</span>';
    document.getElementById('pmPdfRemove').style.display = has ? '' : 'none';
}

async function uploadPdf(input) {
    const file = input.files[0];
    input.value = '';
    if (!file) return;
    if (file.size > 30 * 1048576) { msg('PDF는 30MB 이하여야 합니다.', false); return; }
    // 캠페인이 저장돼 있어야 파일을 붙일 수 있으므로 먼저 임시저장한다
    const id = await saveDraft(true);
    if (!id) return;
    msg('PDF 올리는 중…', true);
    document.getElementById('pmPdfInfo').textContent = '올리는 중: ' + file.name + ' (' + (file.size / 1048576).toFixed(1) + 'MB)';
    const fd = new FormData();
    fd.append('action', 'upload_pdf'); fd.append('id', id); fd.append('pdf', file);
    const res  = await fetch(API, { method: 'POST', headers: { Authorization: 'Bearer ' + token() }, body: fd });
    const data = await res.json().catch(() => ({}));
    if (res.ok && data.ok) { showPdf(data.attachment_name, data.attachment_size); msg('PDF를 첨부했습니다.', true); loadAll(); }
    else { showPdf('', 0); msg(data.error || 'PDF 업로드 실패', false); }
}

async function removePdf() {
    const id = parseInt(document.getElementById('pmId').value) || 0;
    if (!id) return;
    const { ok, data } = await _post({ action: 'remove_pdf', id });
    if (ok) { showPdf('', 0); loadAll(); } else msg(data.error || '제거 실패', false);
}

// 편집창을 여는 동안 자동으로 만들어진 초안(템플릿 불러오기·PDF 올리기 때의 자동 저장)의 id.
// 사용자가 '임시저장'이나 발송을 하지 않고 닫으면 이 초안은 지운다 — 안 그러면 열 때마다 똑같은 초안이 목록에 쌓인다.
let autoDraftId = 0;

async function closeEditor() {
    document.getElementById('pmEditorOverlay').classList.remove('open');
    if (autoDraftId) {
        const id = autoDraftId; autoDraftId = 0;
        await _post({ action: 'delete', id });
        loadAll();
    }
}

function formData() {
    return {
        id:         parseInt(document.getElementById('pmId').value) || 0,
        title:      document.getElementById('pmTitle').value,
        subject:    document.getElementById('pmSubject').value,
        html_body:  getBodyHtml(),
        industries: [...document.querySelectorAll('#pmIndustries input:checked')].map(i => i.value),
        company_ids: [...coSel],
    };
}

// ── 대상 회사 직접 선택 ──────────────────────────────────
function toggleCoPicker() {
    const box = document.getElementById('pmCoPicker');
    const open = box.style.display === 'none';
    box.style.display = open ? '' : 'none';
    document.getElementById('pmCoToggle').textContent = open ? '회사 선택 닫기' : '회사 선택 열기';
}

function coVisible() {
    const q = document.getElementById('pmCoSearch').value.trim().toLowerCase();
    const ind = document.getElementById('pmCoInd').value;
    return coAll.filter(c => (!ind || c.industry === ind) && (!q || (c.name || '').toLowerCase().includes(q)));
}

function renderCoPicker() {
    const rows = coVisible();
    document.getElementById('pmCoList').innerHTML = rows.length ? rows.map(c => `
        <label style="display:flex;align-items:center;gap:8px;padding:4px 2px;border-bottom:1px solid rgba(var(--text-rgb),.06);cursor:pointer;">
            <input type="checkbox" ${coSel.has(+c.id) ? 'checked' : ''} onchange="coToggle(${+c.id}, this.checked)">
            <span style="flex:1;">${esc(c.name)} <span style="color:var(--text-muted);">· ${esc(c.industry || '')}</span></span>
            <span style="color:var(--text-muted);font-size:12px;">${+c.sent_count ? `발송 ${+c.sent_count}회 · 마지막 ${esc(fmt(c.last_sent_at))}` : '미발송'}</span>
        </label>`).join('')
        : '<span class="adm-null">조건에 맞는 회사가 없습니다.</span>';
    updateCoSummary();
}

function coToggle(id, on) { on ? coSel.add(id) : coSel.delete(id); updateCoSummary(); }

function coPick(mode) {
    if (mode === 'none') coSel.clear();
    else coVisible().forEach(c => { if (mode === 'all' || !+c.sent_count) coSel.add(+c.id); });
    renderCoPicker();
}

function updateCoSummary() {
    document.getElementById('pmCoSummary').innerHTML = coSel.size
        ? `<strong>${coSel.size}곳</strong> 선택됨 — 이 회사들에만 발송 (업종 선택은 무시)`
        : '<span style="color:var(--text-muted);">선택 없음 — 위 업종 기준으로 발송</span>';
}


function msg(text, ok) {
    const el = document.getElementById('pmEditorMsg');
    el.textContent = text; el.style.color = ok ? '#1c6b3a' : '#c00';
}

async function saveDraft(silent) {
    // 자동 임시저장(PDF 올릴 때)은 이름·제목이 비어 있어도 막지 않는다. 발송·예약은 일반 저장을 거치므로 그때는 그대로 검사한다.
    if (silent) {
        const t = document.getElementById('pmTitle');
        if (!t.value.trim()) t.value = '새 홍보메일 ' + new Date().toISOString().slice(0, 10);
    }
    const wasNew = !(parseInt(document.getElementById('pmId').value) || 0);
    const { ok, data } = await _post({ action: 'save', ...formData(), draft: !!silent });
    if (!ok) { msg(data.error || '저장 실패', false); return 0; }
    // 자동 저장으로 새로 생긴 초안이면 기억해 두고, 사용자가 직접 저장하면 그때부터는 지우지 않는다
    if (silent && wasNew) autoDraftId = data.id;
    if (!silent) autoDraftId = 0;
    document.getElementById('pmId').value = data.id;
    if (!silent) msg('저장됨', true);
    loadAll();
    return data.id;
}

async function sendTest() {
    const f = formData();
    msg('테스트 메일 보내는 중…', true);
    // id를 같이 보내면 저장된 캠페인의 첨부 PDF도 테스트 메일에 붙는다
    const { ok, data } = await _post({ action: 'test', id: f.id, subject: f.subject, html_body: f.html_body, company_id: parseInt(document.getElementById('pmTestCo').value) || 0, to: document.getElementById('pmTestTo').value });
    msg(ok ? '테스트 메일을 보냈습니다.' : (data.error || '테스트 발송 실패'), ok);
}

async function startSend(kind) {
    const f = formData();
    const label = coSel.size ? '직접 고른' : (f.industries.length ? f.industries.join('·') : '전체 업종');
    let when = '';
    if (kind === 'schedule') {
        when = document.getElementById('pmWhen').value;
        if (!when) { msg('예약 시각을 선택하세요.', false); return; }
    }
    const target = coSel.size ? coSel.size : f.industries.length ? f.industries.reduce((n, i) => n + (counts[i] ?? 0), 0) : (counts[''] ?? 0);
    const q = kind === 'send_now'
        ? `${label} ${target}곳에 지금 발송을 시작합니다. (2분에 한 통씩, 약 ${target * 2}분 소요)\n계속할까요?`
        : `${label} ${target}곳에 ${when.replace('T', ' ')}부터 발송합니다. (2분에 한 통씩)\n계속할까요?`;
    if (!confirm(q)) return;
    const id = await saveDraft(true);
    if (!id) return;
    const { ok, data } = await _post({ action: kind, id, scheduled_at: when });
    if (!ok) { msg(data.error || '발송 시작 실패', false); return; }
    autoDraftId = 0;   // 발송·예약한 캠페인은 지우지 않는다
    closeEditor();
    loadAll();
}

// ── 목록 동작 ────────────────────────────────────────────
async function cancelCampaign(id) {
    if (!confirm('예약·발송을 취소할까요? 이미 보낸 메일은 되돌릴 수 없습니다.')) return;
    const { ok, data } = await _post({ action: 'cancel', id });
    if (!ok) alert(data.error || '취소 실패');
    loadAll();
}

async function deleteCampaign(id) {
    const c = campaigns.find(x => x.id == id);
    if (!confirm(`"${c?.title ?? ''}"과 발송 이력을 삭제할까요?`)) return;
    const { ok, data } = await _post({ action: 'delete', id });
    if (!ok) alert(data.error || '삭제 실패');
    loadAll();
}

// ── 발송 이력 ────────────────────────────────────────────
async function openLog(id) {
    const res  = await fetch(`${API}?id=${id}`, { headers: _h() });
    const data = await res.json();
    const logs = data.logs || [];
    const n = s => logs.filter(l => l.status === s).length;
    document.getElementById('pmLogTitle').textContent = `발송 이력 — ${data.campaign.title}`;
    document.getElementById('pmLogSummary').innerHTML =
        `<span class="pm-status ${data.campaign.status}">${STATUS_LABEL[data.campaign.status]}</span> &nbsp; 총 ${logs.length}곳 · 발송 ${n('sent')} · 실패 ${n('failed')} · 대기 ${n('pending') + n('sending')}`;
    const L = { sent: '발송', failed: '실패', pending: '대기', sending: '발송 중' };
    logCampaignId = id;
    // 다시 보낼 문구: 캠페인에 저장된 문구 그대로, 또는 템플릿의 최신 제목·본문·PDF로 교체
    document.getElementById('pmLogTpl').innerHTML = `<option value="">이 캠페인에 저장된 문구 그대로 (예전 내용)</option>` +
        templates.map(t => `<option value="${t.id}">템플릿 "${esc(t.name)}"의 최신 내용으로 교체 (${esc(fmt(t.updated_at))} 저장)${t.attachment_name ? ' 📎' : ''}</option>`).join('');
    document.getElementById('pmLogAll').checked = false;
    document.getElementById('pmLogMsg').textContent = '';
    document.getElementById('pmLogBody').innerHTML = logs.length ? logs.map(l => `
        <tr><td>${(l.status === 'sent' || l.status === 'failed') ? `<input type="checkbox" class="pm-logchk" value="${l.id}" data-status="${l.status}">` : ''}</td>
        <td>${esc(l.name)}</td><td>${esc(l.email)}</td>
        <td><span class="${l.status === 'sent' ? 'adm-active-badge' : l.status === 'failed' ? 'adm-withdrawn-badge' : 'pm-status'}">${L[l.status] || l.status}</span></td>
        <td>${esc(fmt(l.sent_at))}</td><td>${esc(l.error)}</td></tr>`).join('')
        : `<tr><td colspan="6" style="text-align:center;padding:24px 0;"><span class="adm-null">아직 발송 이력이 없습니다. 발송을 시작하면 대상 회사가 여기에 나타납니다.</span></td></tr>`;
    document.getElementById('pmLogOverlay').classList.add('open');
}

function closeLog() { document.getElementById('pmLogOverlay').classList.remove('open'); }

function toggleLogAll(box) { document.querySelectorAll('.pm-logchk').forEach(c => c.checked = box.checked); }

// 이미 보낸·실패한 회사를 다시 보내기: 선택한 행(또는 실패 전부)을 '대기'로 되돌리면 발송 프로그램이 2분 간격으로 다시 보낸다
async function requeueLogs(mode) {
    const m = document.getElementById('pmLogMsg');
    const boxes = [...document.querySelectorAll('.pm-logchk:checked')];
    const body = { action: 'requeue', id: logCampaignId };
    let q;
    if (mode === 'failed') {
        const n = document.querySelectorAll('.pm-logchk[data-status="failed"]').length;
        if (!n) { m.style.color = '#a02020'; m.textContent = '실패한 회사가 없습니다.'; return; }
        body.scope = 'failed';
        q = `실패한 ${n}곳을 다시 보냅니다. (2분에 한 통씩)\n계속할까요?`;
    } else {
        if (!boxes.length) { m.style.color = '#a02020'; m.textContent = '다시 보낼 회사를 선택하세요.'; return; }
        body.log_ids = boxes.map(b => parseInt(b.value));
        const sent = boxes.filter(b => b.dataset.status === 'sent').length;
        q = `${boxes.length}곳을 다시 보냅니다. (2분에 한 통씩)` + (sent ? `\n\n이미 발송된 ${sent}곳이 포함돼 있습니다. 같은 메일이 한 번 더 갑니다.` : '') + '\n계속할까요?';
    }
    const tplSel = document.getElementById('pmLogTpl');
    if (tplSel.value) {
        body.template_id = parseInt(tplSel.value);
        q += `\n\n※ 이 캠페인의 제목·본문이 ${tplSel.options[tplSel.selectedIndex].text.replace(/ \(.*$/, '')}으로 바뀝니다.`;
    } else {
        q += '\n\n※ 이 캠페인에 저장된 예전 문구 그대로 나갑니다. 고친 문구로 보내려면 "다시 보낼 문구"에서 템플릿을 고르세요.';
    }
    if (!confirm(q)) return;
    const { ok, data } = await _post(body);
    m.style.color = ok ? '#1c6b3a' : '#a02020';
    if (!ok) { m.textContent = data.error || '재발송 실패'; return; }
    m.textContent = `${data.requeued}곳을 대기로 되돌렸습니다. 곧 발송이 시작됩니다.`;
    loadAll();
    setTimeout(() => openLog(logCampaignId), 800);
}

document.addEventListener('DOMContentLoaded', () => {
    initEditor();
    ['pmEditorOverlay', 'pmLogOverlay'].forEach(id =>
        document.getElementById(id).addEventListener('click', e => { if (e.target === e.currentTarget) id === 'pmLogOverlay' ? closeLog() : closeEditor(); }));
    const page = document.getElementById('pmPage');
    const show = () => { page.style.display = ''; loadAll(); };
    if (authGetToken()) show();
    window.addEventListener('pmokAuthChanged', show);
    authUpdateNav();
});

// 테스트 발송에서 고를 수 있도록 DB에 등록된 회사 목록을 채운다 (고른 회사명으로 {수신처}·{{회사명}}이 치환된다)
function fillTestCompanies(list) {
    const sel = document.getElementById('pmTestCo');
    const cur = sel.value;
    sel.innerHTML = '<option value="">회사명 샘플</option>' + list.map(c => `<option value="${c.id}">${esc(c.name)}</option>`).join('');
    sel.value = cur;
}
