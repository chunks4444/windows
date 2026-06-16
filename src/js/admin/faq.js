const API = '/src/api/admin/faq.php';
function _h() { return { 'Authorization': 'Bearer ' + localStorage.getItem('pmok_auth_token'), 'Content-Type': 'application/json' }; }

let faqs = [], dragSrc, quill;

async function loadFaqs() {
    const res  = await fetch(API, { headers: _h() });
    const data = await res.json();
    if (!res.ok) { document.getElementById('faqAuthWall').style.display = ''; return; }
    faqs = data.faqs || [];
    render();
}

function render() {
    document.getElementById('faqBody').innerHTML = faqs.map(f => `
        <tr data-id="${f.id}" draggable="true">
            <td style="text-align:center;"><span class="drag-handle"><i class="bi bi-grip-vertical"></i></span></td>
            <td><strong>${esc(f.question)}</strong></td>
            <td><div class="faq-answer-preview">${esc(f.answer)}</div></td>
            <td><span class="${f.is_active ? 'adm-active-badge' : 'adm-withdrawn-badge'}">${f.is_active ? '노출' : '숨김'}</span></td>
            <td>
                <div class="adm-action-cell">
                    <button class="adm-edit-btn" style="height:28px;padding:0 10px;font-size:12px;" onclick="openModal(${f.id})">수정</button>
                    <button class="adm-withdraw-btn" onclick="toggleFaq(${f.id})">${f.is_active ? '숨김' : '표시'}</button>
                    <button class="adm-withdraw-btn" style="background:#c00;color:#fff;" onclick="deleteFaq(${f.id})">삭제</button>
                </div>
            </td>
        </tr>`).join('');
    bindDrag();
}

function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function openModal(id) {
    const f = id ? faqs.find(x => x.id == id) : null;
    document.getElementById('faqModalTitle').textContent = f ? 'FAQ 수정' : 'FAQ 추가';
    document.getElementById('faqId').value       = f?.id ?? '';
    document.getElementById('faqQuestion').value = f?.question ?? '';
    quill.root.innerHTML = f?.answer ?? '';
    document.getElementById('faqModalOverlay').classList.add('open');
    setTimeout(() => quill.focus(), 50);
}

function closeModal() {
    document.getElementById('faqModalOverlay').classList.remove('open');
}

async function saveFaq() {
    const body = {
        action:   'save',
        id:       parseInt(document.getElementById('faqId').value) || 0,
        question: document.getElementById('faqQuestion').value.trim(),
        answer:   quill.root.innerHTML.trim(),
    };
    const res  = await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify(body) });
    const data = await res.json();
    if (data.ok) { closeModal(); loadFaqs(); }
    else alert(data.error || '저장 실패');
}

async function deleteFaq(id) {
    if (!confirm('이 FAQ를 삭제할까요?')) return;
    await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'delete', id }) });
    loadFaqs();
}

async function toggleFaq(id) {
    await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'toggle', id }) });
    loadFaqs();
}

function bindDrag() {
    document.querySelectorAll('#faqBody tr').forEach(tr => {
        tr.addEventListener('dragstart', () => { dragSrc = tr; tr.classList.add('dragging'); });
        tr.addEventListener('dragend',   () => tr.classList.remove('dragging'));
        tr.addEventListener('dragover',  e => { e.preventDefault(); tr.classList.add('drag-over'); });
        tr.addEventListener('dragleave', () => tr.classList.remove('drag-over'));
        tr.addEventListener('drop', async e => {
            e.preventDefault();
            tr.classList.remove('drag-over');
            if (dragSrc === tr) return;
            tr.parentNode.insertBefore(dragSrc, tr.nextSibling);
            const ids = [...tr.parentNode.querySelectorAll('tr')].map(r => +r.dataset.id);
            await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'reorder', ids }) });
            await loadFaqs();
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    quill = new Quill('#faqEditor', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link'],
                ['clean'],
            ],
        },
        placeholder: '답변 내용을 입력하세요.',
    });

    document.getElementById('faqModalOverlay').addEventListener('click', e => {
        if (e.target === e.currentTarget) closeModal();
    });
    const user = JSON.parse(localStorage.getItem('pmok_auth_user') || 'null');
    if (!user || user.role !== 's') { document.getElementById('faqAuthWall').style.display = ''; return; }
    document.getElementById('faqPage').style.display = '';
    loadFaqs();
});
