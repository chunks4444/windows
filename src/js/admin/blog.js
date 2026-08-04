const API = '/src/api/admin/blog.php';
function _h() { return { 'Authorization': 'Bearer ' + localStorage.getItem('pmok_auth_token'), 'Content-Type': 'application/json' }; }

let posts = [], dragSrc, quill;

async function loadPosts() {
    const res  = await fetch(API, { headers: _h() });
    const data = await res.json();
    if (!res.ok) { document.getElementById('blogAuthWall').style.display = ''; return; }
    posts = data.posts || [];
    render();
    const totalViews = posts.reduce((sum, p) => sum + (+p.view_count || 0), 0);
    const totalEl = document.getElementById('blogTotalViews');
    if (totalEl) totalEl.textContent = totalViews.toLocaleString();
}

let blogViewTrendChart = null;
async function loadBlogViewTrend() {
    const canvas = document.getElementById('blogViewTrendChart');
    if (!canvas) return;
    const res  = await fetch('/src/api/admin/blog_view_trend.php?days=90', { headers: _h() });
    const data = await res.json();
    if (!res.ok) return;
    const rows = data.rows || [];
    // 스냅샷은 "그날 첫 방문 때" 찍히는 그 시점의 누적 총합 — 즉 snapshot(N)은 N일이
    // "시작될 때"의 값이다. 그래서 snapshot(N) - snapshot(N-1)은 N일이 아니라 N-1일
    // 하루 동안 실제로 늘어난 양이므로, 그 증가분은 앞쪽 스냅샷(N-1)의 날짜로 표시한다.
    const daily = rows.slice(1).map((r, i) => ({
        date: rows[i].snapshot_date.slice(5), // MM-DD — 증가분이 실제로 발생한 날짜(전날)
        delta: Math.max(0, (+r.total_views) - (+rows[i].total_views)),
    }));
    const labels = daily.map(d => d.date);
    const deltas = daily.map(d => d.delta);

    if (blogViewTrendChart) blogViewTrendChart.destroy();
    blogViewTrendChart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: '일별 조회수', data: deltas, backgroundColor: 'rgba(58,140,130,0.7)', borderRadius: 3 },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { labels: { font: { family: 'Noto Sans KR', size: 11 } } } },
            scales: {
                x: { ticks: { font: { size: 10 }, maxTicksLimit: 20 }, grid: { display: false } },
                y: { ticks: { font: { size: 10 }, precision: 0 }, beginAtZero: true },
            }
        }
    });
}

// "연관 대표 도면" select — 컬렉션에 실제로 등록된 패턴만 고를 수 있게 한다.
// (엔진의 드로잉 딥링크는 컬렉션에 공개된 도면만 불러올 수 있어, 임의 도면 ID를 적으면 조용히 실패함)
async function loadCollectionPatternsForPicker() {
    const res  = await fetch('/src/api/admin/collection.php', { headers: _h() });
    const data = await res.json();
    const patterns = (data.patterns || []).filter(p => p.drawing_id);
    const sel = document.getElementById('postRelatedDrawingId');
    sel.innerHTML = '<option value="">— 없음 —</option>' +
        patterns.map(p => `<option value="${p.drawing_id}">${esc(p.display_name)} (도면 #${p.drawing_id})</option>`).join('');
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
    document.getElementById('postSourceText').value = p?.source_text ?? '';
    quill.root.innerHTML = p?.content ?? '';
    document.getElementById('postThumbUrl').value = p?.thumbnail_url ?? '';
    const prev = document.getElementById('postImgPreview');
    if (p?.thumbnail_url) { prev.src = p.thumbnail_url; prev.classList.add('show'); }
    else                  { prev.src = ''; prev.classList.remove('show'); }
    document.getElementById('postImgFile').value = '';
    document.getElementById('postIsFeatured').checked = !!(p?.is_featured);
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
                resolve({ dataUrl: canvas.toDataURL('image/jpeg', 0.92), originalWidth: img.width });
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}

// 타이틀 이미지(썸네일)는 구글 검색 큰 썸네일 조건(폭 1200px 권장)에 맞춰 서버(saveBlogImage)가
// 항상 1200px로 리사이즈한다. 원본이 그보다 작으면 서버에서 확대되어 흐려질 수 있어 여기서 미리 경고한다.
const BLOG_THUMB_TARGET_W = 1200;

async function previewImage(input) {
    const file = input.files[0];
    if (!file) return;
    try {
        const { dataUrl, originalWidth } = await fileToResizedDataUrl(file, BLOG_THUMB_TARGET_W);
        window._postThumbData = dataUrl;
        const prev = document.getElementById('postImgPreview');
        prev.src = dataUrl; prev.classList.add('show');
        document.getElementById('postThumbUrl').value = '';
        document.getElementById('postImgWarn').style.display = originalWidth < BLOG_THUMB_TARGET_W ? '' : 'none';
    } catch (err) {
        alert(err);
        input.value = '';
    }
}

async function insertContentImage(file) {
    const range = quill.getSelection(true);
    try {
        const { dataUrl } = await fileToResizedDataUrl(file, 1600);
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
        source_text:         document.getElementById('postSourceText').value.trim(),
        content:             quill.root.innerHTML.trim(),
        thumbnail_url:       document.getElementById('postThumbUrl').value.trim(),
        is_featured:         document.getElementById('postIsFeatured').checked ? 1 : 0,
        series_id:           parseInt(document.getElementById('postSeriesId').value) || 0,
        series_order:        parseInt(document.getElementById('postSeriesOrder').value) || 0,
        question:            document.getElementById('postQuestion').value.trim(),
        related_engine:      document.getElementById('postRelatedEngine').value,
        related_drawing_id:  parseInt(document.getElementById('postRelatedDrawingId').value) || 0,
    };
    if (!body.title || quill.getText().trim().length === 0) { alert('제목과 본문을 입력해주세요.'); return; }
    if (/[-—"'*:]/.test(body.title)) { alert('제목에는 - — " \' * : 문자를 쓸 수 없습니다.'); return; }
    if (window._postThumbData) body.thumbnail_data = window._postThumbData;
    const res  = await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify(body) });
    const data = await res.json();
    const st = document.getElementById('postSaveStatus');
    if (data.ok) {
        if (!body.id && data.post?.id) document.getElementById('postId').value = data.post.id;
        window._postThumbData = null;
        st.className = 'pc-status ok'; st.textContent = '저장됨';
        setTimeout(() => { if (st.textContent === '저장됨') st.textContent = ''; }, 2000);
        await loadPosts();
    } else {
        st.className = 'pc-status err'; st.textContent = data.error || '저장 실패';
    }
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

document.addEventListener('DOMContentLoaded', async () => {
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
    await loadPosts();
    loadCollectionPatternsForPicker();
    loadBlogViewTrend();

    // 블로그 디테일 페이지의 "이 글 편집" 링크(?edit=123)로 들어오면 바로 편집 모달을 연다
    const editId = parseInt(new URLSearchParams(location.search).get('edit'));
    if (editId && posts.some(p => p.id === editId)) openModal(editId);
});
