/* ── 상태 ──────────────────────────────────────── */
let allPatterns     = [];
let currentPatterns = [];
let activeFilter    = 'all';
let searchTimer     = null;
const likes         = {};

/* ── API ──────────────────────────────────────── */
async function loadPatterns(q = '') {
    const url = '/src/api/collection.php' + (q ? '?q=' + encodeURIComponent(q) : '');
    try {
        const res  = await fetch(url);
        const data = await res.json();
        if (data.error) { console.error('library API:', data.error); currentPatterns = []; }
        else { currentPatterns = data.patterns || []; }
        if (!q) allPatterns = [...currentPatterns];
    } catch(e) {
        console.error('library fetch error:', e);
        currentPatterns = [];
    }
    applyAndRender();
}

/* ── 렌더링 ──────────────────────────────────── */
function applyAndRender() {
    let patterns = activeFilter === 'liked'
        ? allPatterns.filter(p => !!likes[p.id])
        : currentPatterns;

    const masonry = document.getElementById('libMasonry');
    if (!patterns.length) {
        masonry.innerHTML = '<p style="color:var(--text-3);font-size:13px;grid-column:1/-1;padding:40px 0;text-align:center;">검색 결과가 없습니다.</p>';
        return;
    }
    masonry.innerHTML = patterns.map(buildCard).join('');
    buildFilterButtons();
}

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
                <div class="lib-card-name">${esc(p.name_ko)}</div>
                <div class="lib-card-sub">${kwHtml}</div>
            </div>
        </div>`;
}

/* ── 필터 버튼 ────────────────────────────────── */
function buildFilterButtons() {
    const container = document.getElementById('libFilters');
    const existing  = new Set([...container.querySelectorAll('.lib-filter-btn')].map(b => b.dataset.filter));

    const keywords = [...new Set(allPatterns.flatMap(p => p.keywords || []))].sort();
    keywords.forEach(kw => {
        if (existing.has(kw)) return;
        const btn = document.createElement('button');
        btn.className      = 'lib-filter-btn';
        btn.dataset.filter = kw;
        btn.textContent    = kw;
        container.appendChild(btn);
        existing.add(kw);
    });
    bindFilterBtns();
}

function bindFilterBtns() {
    document.querySelectorAll('.lib-filter-btn').forEach(btn => {
        btn.onclick = () => {
            document.querySelectorAll('.lib-filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            activeFilter = btn.dataset.filter;
            if (activeFilter === 'all') {
                loadPatterns(document.getElementById('libSearch').value.trim());
            } else if (activeFilter === 'liked') {
                applyAndRender();
            } else {
                loadPatterns(activeFilter);
            }
        };
    });
}

/* ── 검색 ─────────────────────────────────────── */
document.getElementById('libSearch').addEventListener('input', e => {
    clearTimeout(searchTimer);
    const q = e.target.value.trim();
    searchTimer = setTimeout(() => {
        activeFilter = 'all';
        document.querySelectorAll('.lib-filter-btn').forEach(b => b.classList.remove('active'));
        document.querySelector('[data-filter="all"]').classList.add('active');
        loadPatterns(q);
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
    if (!token) { alert('로그인이 필요합니다.'); return; }

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
    if (activeFilter === 'liked') applyAndRender();
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
    if (!authToken()) { alert('로그인이 필요합니다.'); return; }
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

function esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

document.addEventListener('DOMContentLoaded', async () => {
    bindFilterBtns();
    document.getElementById('boardModal').addEventListener('click', e => {
        if (e.target === e.currentTarget) closeBoardModal();
    });
    document.getElementById('boardNameInput').addEventListener('keydown', e => {
        if (e.key === 'Enter') createBoard();
    });
    await loadLikes();
    const q = new URLSearchParams(location.search).get('q') || '';
    if (q) document.getElementById('libSearch').value = q;
    loadPatterns(q);
});
