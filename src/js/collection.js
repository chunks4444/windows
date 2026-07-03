/* ── 상태 ─────────────────────────────────────── */
let loadedPatterns = [];
let currentPage    = 1;
let hasMore        = true;
let isLoading      = false;
let currentQ       = '';
const likes        = {};
let activeFilter   = 'all';
let activeCategory = '';
let searchTimer    = null;
let scrollObserver = null;

/* ── API ──────────────────────────────────────── */
async function fetchPage(q, page, category) {
    try {
        const res  = await fetch('/src/api/collection.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ q, page, category: category || '' }),
        });
        const data = await res.json();
        if (data.error) { console.error('collection API:', data.error); return { patterns: [], has_more: false }; }
        return data;
    } catch(e) {
        console.error('collection fetch error:', e);
        return { patterns: [], has_more: false };
    }
}

/* ── 그리드 초기화 후 로드 ────────────────────── */
function resetAndLoad(q) {
    currentQ       = q;
    currentPage    = 1;
    hasMore        = true;
    loadedPatterns = [];
    document.getElementById('libMasonry').innerHTML = '';
    setLoadMore(false);
    setupObserver();
    loadNextPage();
}

/* ── 다음 페이지 로드 (무한스크롤 + 더보기 공용) */
async function loadNextPage() {
    if (isLoading || !hasMore) return;
    isLoading = true;
    setLoadMore(false, true); // 버튼 로딩 상태

    const data     = await fetchPage(currentQ, currentPage, activeCategory);
    const patterns = data.patterns || [];

    if (currentPage === 1 && typeof data.total === 'number') {
        const countEl = document.getElementById('libResultCount');
        if (countEl) countEl.innerHTML = `<strong>${data.total}</strong>개 패턴`;
    }

    loadedPatterns = [...loadedPatterns, ...patterns];
    hasMore        = !!data.has_more;
    currentPage++;

    const masonry = document.getElementById('libMasonry');
    if (!patterns.length && currentPage === 2) {
        masonry.innerHTML = '<p style="color:var(--text-3);font-size:13px;grid-column:1/-1;padding:40px 0;text-align:center;">검색 결과가 없습니다.</p>';
    } else {
        masonry.insertAdjacentHTML('beforeend', patterns.map(buildCard).join(''));
    }

    isLoading = false;
    setLoadMore(hasMore, false);
    if (!hasMore) scrollObserver?.disconnect();
}

/* ── 더보기 버튼 상태 ─────────────────────────── */
function setLoadMore(visible, loading = false) {
    const wrap    = document.getElementById('libLoadMore');
    const btn     = wrap.querySelector('.lib-loadmore-btn');
    const text    = document.getElementById('libLoadMoreText');
    const spinner = document.getElementById('libLoadMoreSpinner');
    wrap.style.display          = visible || loading ? 'block' : 'none';
    btn.disabled                = loading;
    text.style.display          = loading ? 'none' : '';
    spinner.style.display       = loading ? 'inline-block' : 'none';
}

/* ── 무한 스크롤 옵저버 ───────────────────────── */
function setupObserver() {
    scrollObserver?.disconnect();
    const btn = document.querySelector('#libLoadMore .lib-loadmore-btn');
    if (!btn) return;
    scrollObserver = new IntersectionObserver(entries => {
        if (entries[0].isIntersecting && hasMore && !isLoading) loadNextPage();
    }, { rootMargin: '400px' });
    scrollObserver.observe(document.getElementById('libLoadMore'));
}

/* ── 좋아요 필터 렌더 ─────────────────────────── */
function renderLiked() {
    scrollObserver?.disconnect();
    setLoadMore(false);
    const masonry   = document.getElementById('libMasonry');
    const patterns  = loadedPatterns.filter(p => !!likes[p.id]);
    const countEl   = document.getElementById('libResultCount');
    if (countEl) countEl.innerHTML = `<strong>${patterns.length}</strong>개 좋아요`;
    if (!patterns.length) {
        masonry.innerHTML = '<p style="color:var(--text-3);font-size:13px;grid-column:1/-1;padding:40px 0;text-align:center;">좋아요한 패턴이 없습니다.</p>';
        return;
    }
    masonry.innerHTML = patterns.map(buildCard).join('');
}

/* ── 카드 HTML ────────────────────────────────── */
function buildCard(p) {
    const imgHtml = p.image_path
        ? `<img src="${esc(p.image_path)}" alt="${esc(p.name_ko)}" loading="lazy" style="width:100%;height:100%;object-fit:cover;">`
        : `<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--text-3);font-size:40px;"><i class="bi bi-image"></i></div>`;

    const editorBtn = p.editor_url && p.drawing_id
        ? `<a href="${esc(p.editor_url)}?drawing_id=${p.drawing_id}" class="lib-btn lib-btn-primary"><i class="bi bi-pencil"></i> 열기</a>`
        : '';

    const kwHtml = (p.keywords || []).slice(0, 3).map(k => `<span style="font-size:11px;color:var(--text-3);">${esc(k)}</span>`).join(' · ');

    return `
        <div class="lib-item" data-id="${p.id}">
            <div class="lib-card-img" style="aspect-ratio:4/3;">
                ${imgHtml}
                <div class="lib-overlay">
                    <div class="lib-overlay-top">
                        <button class="lib-icon-btn lib-like-btn${likes[p.id] ? ' liked' : ''}" onclick="toggleLike(event,${p.id})" title="좋아요">
                            <i class="bi bi-heart${likes[p.id] ? '-fill' : ''}"></i>
                        </button>
                        <button class="lib-icon-btn lib-board-btn" onclick="openBoardModal(event,${p.id},'${esc(p.name_ko)}')" title="보드에 저장">
                            <i class="bi bi-collection"></i>
                        </button>
                    </div>
                    <div class="lib-overlay-bottom">
                        <div class="lib-overlay-title">${esc(p.name_ko)}</div>
                        <div class="lib-overlay-actions">${editorBtn}</div>
                    </div>
                </div>
            </div>
            <div class="lib-card-body">
                <div class="lib-card-name">${engineIconHtml(p.engine)}${esc(p.name_ko)}</div>
                <div class="lib-card-sub">${kwHtml}</div>
            </div>
        </div>`;
}

/* ── 좋아요 토글 버튼 ─────────────────────────── */
function clearLikeActive() {
    const btn = document.getElementById('libLikeBtn');
    if (btn) btn.classList.remove('active');
    activeFilter = 'all';
}

function bindLikeBtn() {
    const btn = document.getElementById('libLikeBtn');
    if (!btn) return;
    btn.onclick = () => {
        const activating = !btn.classList.contains('active');
        btn.classList.toggle('active', activating);
        activeFilter = activating ? 'liked' : 'all';
        if (activating) {
            renderLiked();
        } else {
            resetAndLoad(currentQ);
        }
    };
}

async function initCategoryFilter() {
    const select = document.getElementById('libCatSelect');
    if (!select) return;
    try {
        const res  = await fetch('/src/api/drawings/categories.php');
        const cats = (await res.json()).categories || [];
        cats.forEach(c => {
            const opt = document.createElement('option');
            opt.value       = c.id;
            opt.textContent = c.name;
            select.appendChild(opt);
        });
    } catch {}

    select.addEventListener('change', () => {
        activeCategory = select.value || '';
        clearLikeActive();
        resetAndLoad(currentQ);
    });
}

/* ── 공간 셀렉트 ──────────────────────────────── */
function initSpaceFilter() {
    const select = document.getElementById('libSpaceSelect');
    if (!select) return;
    select.addEventListener('change', () => {
        const q = select.value || '';
        clearLikeActive();
        document.getElementById('libSearch').value = q;
        resetAndLoad(q);
    });
}

/* ── 검색 ─────────────────────────────────────── */
document.getElementById('libSearch').addEventListener('input', e => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        clearLikeActive();
        const spaceSelect = document.getElementById('libSpaceSelect');
        if (spaceSelect) spaceSelect.value = '';
        resetAndLoad(e.target.value.trim());
    }, 300);
});

/* ── 좋아요 ───────────────────────────────────── */
function authToken() { return localStorage.getItem('pmok_auth_token'); }

async function loadLikes() {
    const token = authToken();
    if (!token) return;
    try {
        const res  = await fetch('/src/api/collection_likes.php', { headers: { 'Authorization': 'Bearer ' + token } });
        if (!res.ok) return;
        const data = await res.json();
        (data.likes || []).forEach(id => { likes[id] = true; });
    } catch(e) {}
}

async function toggleLike(e, id) {
    e.stopPropagation(); e.preventDefault();
    const btn   = e.currentTarget;
    const token = authToken();
    if (!token) { pmokRequireAuth(() => toggleLikeFor(btn, id)); return; }
    await toggleLikeFor(btn, id);
}

async function toggleLikeFor(btn, id) {
    const token = authToken();
    if (!token) return;
    try {
        const res  = await fetch('/src/api/collection_likes.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
            body:    JSON.stringify({ pattern_id: id }),
        });
        const data = await res.json();
        if (data.error) { console.error('likes API:', data.error); return; }
        likes[id]  = data.liked;
    } catch(e) { console.error('toggleLike error:', e); return; }

    const icon = btn.querySelector('i');
    if (likes[id]) {
        icon.className = 'bi bi-heart-fill';
        btn.classList.add('liked');
    } else {
        icon.className = 'bi bi-heart';
        btn.classList.remove('liked');
    }
    if (activeFilter === 'liked') renderLiked();
}

/* ── 보드 모달 ────────────────────────────────── */
let boardTarget = null;
let boardCache  = null;

function _authHeaders() {
    const t = authToken();
    return t ? { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + t } : { 'Content-Type': 'application/json' };
}

async function openBoardModal(e, id, name) {
    e.stopPropagation(); e.preventDefault();
    if (!authToken()) { pmokRequireAuth(() => openBoardModalFor(id, name)); return; }
    await openBoardModalFor(id, name);
}

async function openBoardModalFor(id, name) {
    boardTarget = { id, name };
    document.getElementById('boardModal').style.display = 'flex';
    await fetchAndRenderBoards();
}

function closeBoardModal() {
    document.getElementById('boardModal').style.display = 'none';
    boardTarget = null;
}

async function fetchAndRenderBoards() {
    const list = document.getElementById('boardList');
    list.innerHTML = '<p class="bm-empty">불러오는 중…</p>';
    try {
        const res  = await fetch('/src/api/boards/list.php', { headers: _authHeaders() });
        const data = await res.json();
        boardCache = data.boards || [];
        renderBoardList();
    } catch {
        list.innerHTML = '<p class="bm-empty">불러오기 실패</p>';
    }
}

function renderBoardList() {
    const list = document.getElementById('boardList');
    if (!boardCache.length) {
        list.innerHTML = '<p class="bm-empty">아직 보드가 없습니다.</p>';
        return;
    }
    list.innerHTML = boardCache.map(b => `
        <div class="bm-board-row" onclick="addToBoard(${b.id},'${esc(b.name)}')">
            <div class="bm-board-icon"><i class="bi bi-collection"></i></div>
            <div class="bm-board-info">
                <div class="bm-board-name">${esc(b.name)}</div>
                <div class="bm-board-count">${b.item_count}개 패턴</div>
            </div>
            <i class="bi bi-plus bm-board-plus"></i>
        </div>`).join('');
}

async function addToBoard(boardId, boardName) {
    if (!boardTarget) return;
    await fetch('/src/api/boards/add_item.php', {
        method: 'POST',
        headers: _authHeaders(),
        body: JSON.stringify({ board_id: boardId, pattern_id: boardTarget.id }),
    });
    closeBoardModal();
    showToast(`"${boardName}" 보드에 저장됐습니다.`);
}

async function createBoard() {
    const input = document.getElementById('boardNameInput');
    const name  = input.value.trim();
    if (!name) { input.focus(); return; }
    const res  = await fetch('/src/api/boards/create.php', {
        method: 'POST',
        headers: _authHeaders(),
        body: JSON.stringify({ name }),
    });
    const data = await res.json();
    if (!res.ok) { alert(data.error || '보드 생성 실패'); return; }
    if (boardTarget) {
        await fetch('/src/api/boards/add_item.php', {
            method: 'POST',
            headers: _authHeaders(),
            body: JSON.stringify({ board_id: data.board_id, pattern_id: boardTarget.id }),
        });
    }
    input.value = '';
    closeBoardModal();
    showToast(`"${name}" 보드가 만들어졌습니다.`);
}

function showToast(msg) {
    const t = document.getElementById('libToast');
    t.textContent = msg;
    t.classList.add('visible');
    setTimeout(() => t.classList.remove('visible'), 2400);
}

function engineIconHtml(engine) {
    const svg = (window.__pmokEngineIcons || {})[engine];
    if (!svg) return '';
    return `<span class="lib-card-engine-icon">${svg}</span>`;
}

function esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

/* ── 초기화 ───────────────────────────────────── */
document.addEventListener('DOMContentLoaded', async () => {
    bindLikeBtn();
    await initCategoryFilter();
    initSpaceFilter();
    document.getElementById('boardModal').addEventListener('click', e => {
        if (e.target === e.currentTarget) closeBoardModal();
    });
    document.getElementById('boardNameInput').addEventListener('keydown', e => {
        if (e.key === 'Enter') createBoard();
    });

    await loadLikes();

    const q = new URLSearchParams(location.search).get('q')
           || sessionStorage.getItem('collectionQ')
           || '';
    sessionStorage.removeItem('collectionQ');

    if (q) {
        document.getElementById('libSearch').value = q;
        const spaceSelect = document.getElementById('libSpaceSelect');
        if (spaceSelect && [...spaceSelect.options].some(o => o.value === q)) spaceSelect.value = q;
    }
    resetAndLoad(q);
});
