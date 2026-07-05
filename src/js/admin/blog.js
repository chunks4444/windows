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
            <td style="color:var(--text-3);font-size:12px;">${p.series_name ? esc(p.series_name) + (p.series_order ? ' #' + p.series_order : '') : '—'}</td>
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
    document.getElementById('postSeriesId').value          = p?.series_id ?? '';
    document.getElementById('postSeriesOrder').value        = p?.series_order ?? 0;
    document.getElementById('postQuestion').value           = p?.question ?? '';
    document.getElementById('postRelatedEngine').value      = p?.related_engine ?? '';
    document.getElementById('postRelatedDrawingId').value   = p?.related_drawing_id ?? '';
    window._postThumbData = null;
    document.getElementById('blogModalOverlay').classList.add('open');
    document.getElementById('blogModalOverlay').classList.remove('fullscreen-active');
    document.getElementById('blogInfoSection').classList.remove('collapsed');
    document.getElementById('blogSeriesSection').classList.add('collapsed');
    setTimeout(() => quill.focus(), 50);
}

function closeModal() {
    document.getElementById('blogModalOverlay').classList.remove('open');
}

function toggleModalFullscreen() {
    document.getElementById('blogModalOverlay').classList.toggle('fullscreen-active');
}

function toggleInfoSection(sectionId) {
    document.getElementById(sectionId || 'blogInfoSection').classList.toggle('collapsed');
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
        action:              'save',
        id:                  parseInt(document.getElementById('postId').value) || 0,
        title:               document.getElementById('postTitle').value.trim(),
        summary:             document.getElementById('postSummary').value.trim(),
        cta_text:            document.getElementById('postCtaText').value.trim(),
        content:             quill.root.innerHTML.trim(),
        thumbnail_url:       document.getElementById('postThumbUrl').value.trim(),
        series_id:           parseInt(document.getElementById('postSeriesId').value) || 0,
        series_order:        parseInt(document.getElementById('postSeriesOrder').value) || 0,
        question:            document.getElementById('postQuestion').value.trim(),
        related_engine:      document.getElementById('postRelatedEngine').value,
        related_drawing_id:  parseInt(document.getElementById('postRelatedDrawingId').value) || 0,
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

/* ── 시리즈 관리 ── */
const SERIES_API = '/src/api/admin/blog_series.php';
let seriesList = [];

function openSeriesModal() {
    document.getElementById('seriesModalOverlay').classList.add('open');
    loadSeriesList();
}
function closeSeriesModal() {
    document.getElementById('seriesModalOverlay').classList.remove('open');
    refreshPostSeriesSelect();
}

async function loadSeriesList() {
    const res  = await fetch(SERIES_API, { headers: _h() });
    const data = await res.json();
    seriesList = data.series || [];
    renderSeriesTable();
}

function renderSeriesTable() {
    document.getElementById('seriesTbody').innerHTML = seriesList.map(s => `
        <tr id="series-row-${s.id}">
            <td><input class="pc-name-input" id="series-name-${s.id}" value="${esc(s.name)}"></td>
            <td><input class="pc-name-input" id="series-tagline-${s.id}" value="${esc(s.tagline)}"></td>
            <td><input class="pc-sort-input" id="series-order-${s.id}" type="number" value="${s.sort_order}"></td>
            <td style="white-space:nowrap;">
                <button class="pc-btn pc-btn-save" onclick="saveSeriesRow(${s.id})">저장</button>
                <button class="pc-btn pc-btn-del" onclick="deleteSeriesRow(${s.id}, '${esc(s.name)}')">삭제</button>
            </td>
        </tr>`).join('') || '<tr><td colspan="4" style="text-align:center;color:var(--text-3);padding:16px;">시리즈가 없습니다.</td></tr>';
}

async function saveSeriesRow(id) {
    const name    = document.getElementById(`series-name-${id}`).value.trim();
    const tagline = document.getElementById(`series-tagline-${id}`).value.trim();
    const order   = parseInt(document.getElementById(`series-order-${id}`).value) || 0;
    const st = document.getElementById('seriesStatus');
    if (!name) { st.className = 'pc-status err'; st.textContent = '이름 필수'; return; }
    const data = await (await fetch(SERIES_API, { method: 'PUT', headers: _h(),
        body: JSON.stringify({ id, name, tagline, sort_order: order }) })).json();
    st.className = data.ok ? 'pc-status ok' : 'pc-status err';
    st.textContent = data.ok ? '저장됨' : (data.error || '오류');
    if (data.ok) { await loadSeriesList(); setTimeout(() => st.textContent = '', 2000); }
}

async function deleteSeriesRow(id, name) {
    if (!confirm(`"${name}" 시리즈를 삭제하시겠습니까? (연결된 글은 시리즈 없음으로 변경됩니다)`)) return;
    await fetch(SERIES_API, { method: 'DELETE', headers: _h(), body: JSON.stringify({ id }) });
    await loadSeriesList();
}

async function addSeries() {
    const name    = document.getElementById('addSeriesName').value.trim();
    const tagline = document.getElementById('addSeriesTagline').value.trim();
    const order   = parseInt(document.getElementById('addSeriesOrder').value) || 0;
    const st = document.getElementById('seriesStatus');
    if (!name) { st.className = 'pc-status err'; st.textContent = '이름을 입력하세요'; return; }
    const data = await (await fetch(SERIES_API, { method: 'POST', headers: _h(),
        body: JSON.stringify({ name, tagline, sort_order: order }) })).json();
    if (data.ok) {
        document.getElementById('addSeriesName').value = '';
        document.getElementById('addSeriesTagline').value = '';
        st.className = 'pc-status ok'; st.textContent = '추가됨';
        await loadSeriesList();
        setTimeout(() => st.textContent = '', 2000);
    } else {
        st.className = 'pc-status err'; st.textContent = data.error || '오류';
    }
}

// 시리즈 관리 모달을 닫을 때 글 편집 모달의 시리즈 select를 최신 목록으로 갱신
async function refreshPostSeriesSelect() {
    const sel = document.getElementById('postSeriesId');
    if (!sel) return;
    const current = sel.value;
    const res  = await fetch(SERIES_API, { headers: _h() });
    const data = await res.json();
    const list = data.series || [];
    sel.innerHTML = '<option value="">— 없음 —</option>' +
        list.map(s => `<option value="${s.id}">${esc(s.name)}</option>`).join('');
    if (list.some(s => String(s.id) === current)) sel.value = current;
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
    document.getElementById('seriesModalOverlay').addEventListener('click', e => {
        if (e.target === e.currentTarget) closeSeriesModal();
    });
    const user = JSON.parse(localStorage.getItem('pmok_auth_user') || 'null');
    if (!user || user.role !== 's') { document.getElementById('blogAuthWall').style.display = ''; return; }
    document.getElementById('blogPage').style.display = '';
    loadPosts();
});
