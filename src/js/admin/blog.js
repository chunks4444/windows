const API = '/src/api/admin/blog.php';
function _h() { return { 'Authorization': 'Bearer ' + localStorage.getItem('pmok_auth_token'), 'Content-Type': 'application/json' }; }

let posts = [], dragSrc, quill;

async function loadPosts() {
    const res  = await fetch(API, { headers: _h() });
    const data = await res.json();
    if (!res.ok) { document.getElementById('blogAuthWall').style.display = ''; return; }
    posts = data.posts || [];
    render();
}

function render() {
    document.getElementById('blogBody').innerHTML = posts.map(p => `
        <tr data-id="${p.id}" draggable="true">
            <td style="text-align:center;"><span class="drag-handle"><i class="bi bi-grip-vertical"></i></span></td>
            <td>${p.thumbnail_url
                ? `<img class="blog-thumb" src="${esc(p.thumbnail_url)}" alt="">`
                : `<div class="blog-thumb-empty"><i class="bi bi-image"></i></div>`}</td>
            <td><strong>${esc(p.title)}</strong></td>
            <td style="color:var(--text-3);font-size:12px;">${esc(p.summary || '')}</td>
            <td style="text-align:center;color:var(--text-3);font-size:12px;">${p.view_count ?? 0}</td>
            <td><span class="${p.is_active ? 'adm-active-badge' : 'adm-withdrawn-badge'}">${p.is_active ? '노출' : '숨김'}</span></td>
            <td>
                <div class="adm-action-cell">
                    <button class="adm-edit-btn" style="height:28px;padding:0 10px;font-size:12px;" onclick="openModal(${p.id})">수정</button>
                    <button class="adm-withdraw-btn" onclick="togglePost(${p.id})">${p.is_active ? '숨김' : '표시'}</button>
                    <button class="adm-withdraw-btn" style="background:#c00;color:#fff;" onclick="deletePost(${p.id})">삭제</button>
                </div>
            </td>
        </tr>`).join('');
    bindDrag();
}

function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function openModal(id) {
    const p = id ? posts.find(x => x.id == id) : null;
    document.getElementById('blogModalTitle').textContent = p ? '글 수정' : '글 추가';
    document.getElementById('postId').value      = p?.id ?? '';
    document.getElementById('postTitle').value   = p?.title ?? '';
    document.getElementById('postSummary').value = p?.summary ?? '';
    document.getElementById('postCtaText').value = p?.cta_text ?? '';
    quill.root.innerHTML = p?.content ?? '';
    document.getElementById('postThumbUrl').value = p?.thumbnail_url ?? '';
    const prev = document.getElementById('postImgPreview');
    if (p?.thumbnail_url) { prev.src = p.thumbnail_url; prev.classList.add('show'); }
    else                  { prev.src = ''; prev.classList.remove('show'); }
    document.getElementById('postImgFile').value = '';
    window._postThumbData = null;
    document.getElementById('blogModalOverlay').classList.add('open');
    document.getElementById('blogModalOverlay').classList.remove('fullscreen-active');
    document.getElementById('blogInfoSection').classList.remove('collapsed');
    setTimeout(() => quill.focus(), 50);
}

function closeModal() {
    document.getElementById('blogModalOverlay').classList.remove('open');
}

function toggleModalFullscreen() {
    document.getElementById('blogModalOverlay').classList.toggle('fullscreen-active');
}

function toggleInfoSection() {
    document.getElementById('blogInfoSection').classList.toggle('collapsed');
}

function fileToResizedDataUrl(file, maxDim) {
    return new Promise((resolve, reject) => {
        if (!['image/jpeg', 'image/png'].includes(file.type)) { reject('PNG 또는 JPG 파일만 업로드할 수 있습니다.'); return; }
        const reader = new FileReader();
        reader.onload = e => {
            const img = new Image();
            img.onload = () => {
                let w = img.width, h = img.height;
                if (w > maxDim || h > maxDim) {
                    const scale = Math.min(maxDim / w, maxDim / h);
                    w = Math.round(w * scale); h = Math.round(h * scale);
                }
                const canvas = document.createElement('canvas');
                canvas.width = w; canvas.height = h;
                canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                resolve(canvas.toDataURL('image/jpeg', 0.92));
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}

async function previewImage(input) {
    const file = input.files[0];
    if (!file) return;
    try {
        const dataUrl = await fileToResizedDataUrl(file, 1024);
        window._postThumbData = dataUrl;
        const prev = document.getElementById('postImgPreview');
        prev.src = dataUrl; prev.classList.add('show');
        document.getElementById('postThumbUrl').value = '';
    } catch (err) {
        alert(err);
        input.value = '';
    }
}

async function insertContentImage(file) {
    const range = quill.getSelection(true);
    try {
        const dataUrl = await fileToResizedDataUrl(file, 1600);
        const res  = await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'upload_content_image', image_data: dataUrl }) });
        const data = await res.json();
        if (!data.ok) { alert(data.error || '이미지 업로드 실패'); return; }
        quill.insertEmbed(range.index, 'image', data.url, 'user');
        quill.setSelection(range.index + 1);
    } catch (err) {
        alert(err);
    }
}

async function savePost() {
    const body = {
        action:        'save',
        id:            parseInt(document.getElementById('postId').value) || 0,
        title:         document.getElementById('postTitle').value.trim(),
        summary:       document.getElementById('postSummary').value.trim(),
        cta_text:      document.getElementById('postCtaText').value.trim(),
        content:       quill.root.innerHTML.trim(),
        thumbnail_url: document.getElementById('postThumbUrl').value.trim(),
    };
    if (!body.title || quill.getText().trim().length === 0) { alert('제목과 본문을 입력해주세요.'); return; }
    if (window._postThumbData) body.thumbnail_data = window._postThumbData;
    const res  = await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify(body) });
    const data = await res.json();
    if (data.ok) { closeModal(); loadPosts(); }
    else alert(data.error || '저장 실패');
}

async function deletePost(id) {
    if (!confirm('이 글을 삭제할까요?')) return;
    await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'delete', id }) });
    loadPosts();
}

async function togglePost(id) {
    await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'toggle', id }) });
    loadPosts();
}

function bindDrag() {
    document.querySelectorAll('#blogBody tr').forEach(tr => {
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
            await loadPosts();
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const contentImgFile = document.getElementById('postContentImgFile');

    quill = new Quill('#postContentEditor', {
        theme: 'snow',
        modules: {
            toolbar: {
                container: [
                    [{ header: [2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ color: [] }, { background: [] }],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    [{ align: [] }],
                    ['blockquote', 'link', 'image'],
                    ['clean'],
                ],
                handlers: {
                    image: () => contentImgFile.click(),
                },
            },
        },
        placeholder: '글 내용을 입력하세요. 이미지 버튼으로 본문에 사진을 삽입할 수 있습니다.',
    });

    contentImgFile.addEventListener('change', () => {
        const file = contentImgFile.files[0];
        contentImgFile.value = '';
        if (file) insertContentImage(file);
    });

    document.getElementById('blogModalOverlay').addEventListener('click', e => {
        if (e.target === e.currentTarget) closeModal();
    });
    const user = JSON.parse(localStorage.getItem('pmok_auth_user') || 'null');
    if (!user || user.role !== 's') { document.getElementById('blogAuthWall').style.display = ''; return; }
    document.getElementById('blogPage').style.display = '';
    loadPosts();
});
