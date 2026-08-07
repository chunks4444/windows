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
    closeTableGridPopup();
    hideTableFloatBar();
    removeTableColHandles();
}

function toggleModalFullscreen() {
    document.getElementById('blogModalOverlay').classList.toggle('fullscreen-active');
    setTimeout(syncTableUI, 0);
}

function toggleInfoSection(sectionId) {
    document.getElementById(sectionId || 'blogInfoSection').classList.toggle('collapsed');
}

// ── 본문 에디터 표(table) 도구 ─────────────────────────────
const TABLE_GRID_ROWS = 6, TABLE_GRID_COLS = 8;
let tableGridPopup = null, tableFloatBar = null, tableFloatQuill = null;

function closeTableGridPopup() {
    if (!tableGridPopup) return;
    tableGridPopup.remove();
    tableGridPopup = null;
    document.removeEventListener('mousedown', onTableGridDocClick, true);
}

function onTableGridDocClick(e) {
    const btn = document.querySelector('#blogModalOverlay .ql-table');
    if (tableGridPopup && !tableGridPopup.contains(e.target) && e.target !== btn) closeTableGridPopup();
}

function toggleTableGridPopup() {
    if (tableGridPopup) { closeTableGridPopup(); return; }
    const btn = document.querySelector('#blogModalOverlay .ql-table');
    if (!btn) return;

    const label = document.createElement('div');
    label.className = 'ql-table-grid-label';
    label.textContent = '표 삽입';

    const grid = document.createElement('div');
    grid.className = 'ql-table-grid';
    const cells = [];
    for (let r = 0; r < TABLE_GRID_ROWS; r++) {
        for (let c = 0; c < TABLE_GRID_COLS; c++) {
            const cell = document.createElement('div');
            cell.className = 'ql-table-grid-cell';
            cell.addEventListener('mouseenter', () => {
                cells.forEach(cc => cc.el.classList.toggle('active', cc.r <= r && cc.c <= c));
                label.textContent = `${r + 1} x ${c + 1} 표 삽입`;
            });
            cell.addEventListener('click', () => {
                quill.getModule('table').insertTable(r + 1, c + 1);
                closeTableGridPopup();
            });
            cells.push({ el: cell, r, c });
            grid.appendChild(cell);
        }
    }

    tableGridPopup = document.createElement('div');
    tableGridPopup.className = 'ql-table-grid-popup';
    // 팝업 클릭 시 에디터가 포커스를 잃으면 selection이 사라져
    // insertTable()이 조용히 실패하므로(quill.getSelection()===null), 포커스 유지
    tableGridPopup.addEventListener('mousedown', e => e.preventDefault());
    tableGridPopup.appendChild(grid);
    tableGridPopup.appendChild(label);
    document.body.appendChild(tableGridPopup);

    // position:fixed 이므로 뷰포트 기준 좌표를 그대로 사용 (모달 자체가
    // position:fixed라 페이지 스크롤이 버튼 위치에 영향을 주지 않음)
    const rect = btn.getBoundingClientRect();
    tableGridPopup.style.top  = `${rect.bottom + 4}px`;
    tableGridPopup.style.left = `${rect.left}px`;
    setTimeout(() => document.addEventListener('mousedown', onTableGridDocClick, true), 0);
}

function hideTableFloatBar() {
    if (tableFloatBar) tableFloatBar.style.display = 'none';
}

const TABLE_FLOAT_ACTIONS = [
    ['위에 행 추가',   t => t.insertRowAbove()],
    ['아래 행 추가',   t => t.insertRowBelow()],
    ['왼쪽 열 추가',   t => t.insertColumnLeft()],
    ['오른쪽 열 추가', t => t.insertColumnRight()],
    ['행 삭제',        t => t.deleteRow()],
    ['열 삭제',        t => t.deleteColumn()],
    ['표 삭제',        t => t.deleteTable()],
];

function ensureTableFloatBar() {
    if (tableFloatBar) return tableFloatBar;
    tableFloatBar = document.createElement('div');
    tableFloatBar.className = 'ql-table-float-toolbar';
    TABLE_FLOAT_ACTIONS.forEach(([label, action]) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.textContent = label;
        b.addEventListener('mousedown', e => e.preventDefault());
        b.addEventListener('click', () => {
            if (tableFloatQuill) action(tableFloatQuill.getModule('table'));
            setTimeout(syncTableUI, 0);
        });
        tableFloatBar.appendChild(b);
    });
    document.body.appendChild(tableFloatBar);
    return tableFloatBar;
}

function showTableFloatBar(tableEl) {
    const bar = ensureTableFloatBar();
    const rect = tableEl.getBoundingClientRect();
    bar.style.display = 'flex';
    bar.style.top  = `${rect.top - bar.offsetHeight - 6}px`;
    bar.style.left = `${rect.left}px`;
}

// ── 표 컬럼 너비 조정(드래그 리사이즈) ──────────────────
const TABLE_MIN_COL_WIDTH = 40;
let tableColHandleEls = [], tableResizeTableEl = null;

function removeTableColHandles() {
    tableColHandleEls.forEach(el => el.remove());
    tableColHandleEls = [];
}

function buildTableColHandles(tableEl) {
    removeTableColHandles();
    const firstRow = tableEl.querySelector('tr');
    if (!firstRow) return;
    const cells = Array.from(firstRow.children);
    if (cells.length < 2) return; // 컬럼이 1개면 조정할 경계가 없음
    const tableRect = tableEl.getBoundingClientRect();
    for (let i = 0; i < cells.length - 1; i++) {
        const cellRect = cells[i].getBoundingClientRect();
        const handle = document.createElement('div');
        handle.className = 'ql-table-col-handle';
        handle.style.left   = `${cellRect.right - 3}px`;
        handle.style.top    = `${tableRect.top}px`;
        handle.style.height = `${tableRect.height}px`;
        handle.addEventListener('mousedown', e => startTableColResize(e, tableEl, i));
        document.body.appendChild(handle);
        tableColHandleEls.push(handle);
    }
}

function startTableColResize(e, tableEl, colIndex) {
    e.preventDefault();
    const rows       = Array.from(tableEl.querySelectorAll('tr'));
    const leftCells  = rows.map(r => r.children[colIndex]).filter(Boolean);
    const rightCells = rows.map(r => r.children[colIndex + 1]).filter(Boolean);
    if (!leftCells.length || !rightCells.length) return;
    const startX          = e.clientX;
    const startLeftWidth  = leftCells[0].getBoundingClientRect().width;
    const startRightWidth = rightCells[0].getBoundingClientRect().width;

    function onMove(me) {
        const dx      = me.clientX - startX;
        const newLeft  = Math.max(TABLE_MIN_COL_WIDTH, startLeftWidth + dx);
        const newRight = Math.max(TABLE_MIN_COL_WIDTH, startRightWidth - dx);
        leftCells.forEach(c  => { c.style.width = `${newLeft}px`; });
        rightCells.forEach(c => { c.style.width = `${newRight}px`; });
    }
    function onUp() {
        document.removeEventListener('mousemove', onMove);
        document.removeEventListener('mouseup', onUp);
        buildTableColHandles(tableEl);
    }
    document.addEventListener('mousemove', onMove);
    document.addEventListener('mouseup', onUp);
}

function syncTableUI() {
    closeTableGridPopup();
    const range = tableFloatQuill?.getSelection();
    if (!range) { hideTableFloatBar(); removeTableColHandles(); tableResizeTableEl = null; return; }
    const [leaf] = tableFloatQuill.getLeaf(range.index);
    const node = leaf?.domNode;
    const tableEl = node instanceof Element ? node.closest('table') : node?.parentElement?.closest('table');
    if (tableEl) {
        showTableFloatBar(tableEl);
        buildTableColHandles(tableEl);
        tableResizeTableEl = tableEl;
    } else {
        hideTableFloatBar();
        removeTableColHandles();
        tableResizeTableEl = null;
    }
}

function initTableTools(quillInstance) {
    tableFloatQuill = quillInstance;
    quillInstance.on('selection-change', syncTableUI);
    // 에디터 내부 스크롤 시(모달 자체는 fixed라 페이지 스크롤엔 영향 없음)
    // 표 위치가 바뀌므로 핸들/툴바 위치를 다시 계산
    quillInstance.root.addEventListener('scroll', () => {
        if (tableResizeTableEl) { showTableFloatBar(tableResizeTableEl); buildTableColHandles(tableResizeTableEl); }
    });
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

// 타이틀 이미지(썸네일)는 구글 검색 큰 썸네일 조건(가로·세로 각각 최소 1200px)을 서버
// (resize_image_min_size)가 보장한다. 원본이 그보다 작으면 서버에서 확대되어 흐려질 수 있어
// 여기서 미리 경고한다. 다운스케일 상한은 그보다 넉넉하게 잡아 서버가 다시 확대할 일이 없게 한다.
const BLOG_THUMB_MIN_RECOMMENDED = 1200;
const BLOG_THUMB_DOWNSCALE_MAX = 2000;

async function previewImage(input) {
    const file = input.files[0];
    if (!file) return;
    try {
        const { dataUrl, originalWidth } = await fileToResizedDataUrl(file, BLOG_THUMB_DOWNSCALE_MAX);
        window._postThumbData = dataUrl;
        const prev = document.getElementById('postImgPreview');
        prev.src = dataUrl; prev.classList.add('show');
        document.getElementById('postThumbUrl').value = '';
        document.getElementById('postImgWarn').style.display = originalWidth < BLOG_THUMB_MIN_RECOMMENDED ? '' : 'none';
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
            table: true,
            toolbar: {
                container: [
                    [{ header: [2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ color: [] }, { background: [] }],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    [{ align: [] }],
                    ['blockquote', 'link', 'image', 'table'],
                    ['clean'],
                ],
                handlers: {
                    image: () => contentImgFile.click(),
                    table: () => toggleTableGridPopup(),
                },
            },
        },
        placeholder: '글 내용을 입력하세요. 이미지 버튼으로 본문에 사진을 삽입할 수 있습니다.',
    });

    initTableTools(quill);

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
