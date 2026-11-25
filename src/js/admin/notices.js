const API = '/src/api/admin/notices.php';
function _h() { return { 'Authorization': 'Bearer ' + localStorage.getItem('pmok_auth_token'), 'Content-Type': 'application/json' }; }

let notices = [], serverNow = '', quill, quillEn;

const TOOLBAR = [
    [{ header: [2, 3, false] }],
    ['bold', 'italic', 'underline'],
    [{ list: 'ordered' }, { list: 'bullet' }],
    ['blockquote', 'link'],
    ['clean'],
];

async function loadNotices() {
    const res  = await fetch(API, { headers: _h() });
    const data = await res.json();
    if (!res.ok) { document.getElementById('ntAuthWall').style.display = ''; return; }
    notices   = data.notices || [];
    serverNow = data.now || '';
    render();
}

function stateOf(n) {
    if (!+n.is_active) return '<span class="adm-withdrawn-badge">숨김</span>';
    if (n.published_at > serverNow) return '<span class="nt-state-wait">예약</span>';
    return '<span class="adm-active-badge">공개</span>';
}

function render() {
    document.getElementById('ntBody').innerHTML = notices.length ? notices.map(n => `
        <tr>
            <td style="text-align:center;">
                <button onclick="togglePin(${n.id})" title="목록 위에 고정"
                    style="border:none;background:none;padding:0;cursor:pointer;font-size:18px;line-height:1;">${+n.is_pinned ? '★' : '☆'}</button>
            </td>
            <td><div class="nt-title-cell">${n.label ? '<span class="adm-active-badge" style="margin-right:6px;">' + esc(n.label) + '</span>' : ''}<strong>${esc(n.title)}</strong></div></td>
            <td>${esc(n.published_at.slice(0, 16))}</td>
            <td>${stateOf(n)}</td>
            <td>
                <div class="adm-action-cell">
                    <a class="adm-withdraw-btn" style="text-decoration:none;" href="/company/notice/${n.id}" target="_blank" rel="noopener">보기</a>
                    <button class="adm-edit-btn" style="height:28px;padding:0 10px;font-size:12px;" onclick="openModal(${n.id})">수정</button>
                    <button class="adm-withdraw-btn" onclick="toggleNotice(${n.id})">${+n.is_active ? '숨김' : '공개'}</button>
                    <button class="adm-withdraw-btn" style="background:#c00;color:#fff;" onclick="deleteNotice(${n.id})">삭제</button>
                </div>
            </td>
        </tr>`).join('') : '<tr><td colspan="5" style="text-align:center;padding:32px;">등록된 공지가 없습니다.</td></tr>';
}

function toLocalInput(sqlDate) {
    return (sqlDate || '').slice(0, 16).replace(' ', 'T');
}

function nowLocalInput() {
    const d = new Date();
    d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
    return d.toISOString().slice(0, 16);
}

function openModal(id) {
    const n = id ? notices.find(x => x.id == id) : null;
    document.getElementById('ntModalTitle').textContent = n ? '공지 수정' : '새 공지';
    document.getElementById('ntId').value        = n?.id ?? '';
    document.getElementById('ntTitle').value     = n?.title ?? '';
    document.getElementById('ntTitleEn').value   = n?.title_en ?? '';
    document.getElementById('ntLabel').value     = n?.label ?? '';
    document.getElementById('ntLabelEn').value   = n?.label_en ?? '';
    document.getElementById('ntCtaText').value   = n?.cta_text ?? '';
    document.getElementById('ntCtaTextEn').value = n?.cta_text_en ?? '';
    document.getElementById('ntCtaUrl').value    = n?.cta_url ?? '';
    document.getElementById('ntPublished').value = n ? toLocalInput(n.published_at) : nowLocalInput();
    document.getElementById('ntPinned').checked  = n ? !!+n.is_pinned : false;
    document.getElementById('ntActive').checked  = n ? !!+n.is_active : true;
    quill.root.innerHTML   = n?.body ?? '';
    quillEn.root.innerHTML = n?.body_en ?? '';
    document.getElementById('ntModalOverlay').classList.add('open');
    setTimeout(() => document.getElementById('ntTitle').focus(), 50);
}

function closeModal() {
    document.getElementById('ntModalOverlay').classList.remove('open');
}

function editorHtml(q) {
    const html = q.root.innerHTML.trim();
    return html === '<p><br></p>' ? '' : html;
}

async function saveNotice() {
    const body = {
        action:       'save',
        id:           parseInt(document.getElementById('ntId').value) || 0,
        title:        document.getElementById('ntTitle').value.trim(),
        body:         editorHtml(quill),
        title_en:     document.getElementById('ntTitleEn').value.trim(),
        body_en:      editorHtml(quillEn),
        label:        document.getElementById('ntLabel').value.trim(),
        label_en:     document.getElementById('ntLabelEn').value.trim(),
        cta_text:     document.getElementById('ntCtaText').value.trim(),
        cta_text_en:  document.getElementById('ntCtaTextEn').value.trim(),
        cta_url:      document.getElementById('ntCtaUrl').value.trim(),
        published_at: document.getElementById('ntPublished').value,
        is_pinned:    document.getElementById('ntPinned').checked,
        is_active:    document.getElementById('ntActive').checked,
    };
    const res  = await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify(body) });
    const data = await res.json();
    if (data.ok) { closeModal(); loadNotices(); }
    else alert(data.error || '저장 실패');
}

async function ntAction(action, id) {
    await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action, id }) });
    loadNotices();
}

function togglePin(id) { ntAction('toggle_pin', id); }
function toggleNotice(id) { ntAction('toggle', id); }
function deleteNotice(id) {
    if (!confirm('이 공지를 삭제할까요? 되돌릴 수 없습니다.')) return;
    ntAction('delete', id);
}

document.addEventListener('DOMContentLoaded', () => {
    quill = new Quill('#ntEditor', { theme: 'snow', modules: { toolbar: TOOLBAR }, placeholder: '공지 내용을 입력하세요.' });
    quillEn = new Quill('#ntEditorEn', { theme: 'snow', modules: { toolbar: TOOLBAR }, placeholder: 'English body (leave blank to show the Korean body on the English site).' });

    document.getElementById('ntModalOverlay').addEventListener('click', e => {
        if (e.target === e.currentTarget) closeModal();
    });
    const user = JSON.parse(localStorage.getItem('pmok_auth_user') || 'null');
    if (!user || user.role !== 's') { document.getElementById('ntAuthWall').style.display = ''; return; }
    document.getElementById('ntPage').style.display = '';
    loadNotices();
});
