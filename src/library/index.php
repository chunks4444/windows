<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once __DIR__ . '/../lib/meta.php'; meta_tags(); ?>
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/src/css/library.css">
</head>
<body>

<?php include __DIR__ . '/../components/nav.php'; ?>

<!-- 검색 / 필터 바 -->
<div class="lib-toolbar">
    <div class="lib-toolbar-inner">
        <div class="lib-search">
            <i class="bi bi-search"></i>
            <input type="text" id="libSearch" placeholder="패턴 검색…" autocomplete="off">
        </div>
        <div class="lib-filters" id="libFilters">
            <button class="lib-filter-btn active" data-filter="all">전체</button>
            <button class="lib-filter-btn lib-filter-like" data-filter="liked"><i class="bi bi-heart-fill"></i> 좋아요</button>
        </div>
    </div>
</div>

<div class="lib-main">
    <div class="lib-masonry" id="libMasonry"></div>
</div>

<script>
/* ── 상태 ──────────────────────────────────────── */
let allPatterns     = [];
let currentPatterns = [];
let activeFilter    = 'all';
let searchTimer     = null;
const likes         = {};

/* ── API ──────────────────────────────────────── */
async function loadPatterns(q = '') {
    const url = '/src/api/library.php' + (q ? '?q=' + encodeURIComponent(q) : '');
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
        const res  = await fetch('/src/api/library_likes.php', { headers: { 'Authorization': 'Bearer ' + token } });
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
        const res  = await fetch('/src/api/library_likes.php', {
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
const boards    = JSON.parse(localStorage.getItem('pmok_boards') || '[]');

function openBoardModal(e, id, name) {
    e.stopPropagation(); e.preventDefault();
    boardTarget = { id, name };
    renderBoardList();
    document.getElementById('boardModal').style.display = 'flex';
}

function closeBoardModal() {
    document.getElementById('boardModal').style.display = 'none';
    boardTarget = null;
}

function renderBoardList() {
    const list = document.getElementById('boardList');
    if (!boards.length) {
        list.innerHTML = '<p class="bm-empty">아직 보드가 없습니다.</p>';
        return;
    }
    list.innerHTML = boards.map((b, i) => `
        <div class="bm-board-row" onclick="addToBoard(${i})">
            <div class="bm-board-icon"><i class="bi bi-collection"></i></div>
            <div class="bm-board-info">
                <div class="bm-board-name">${esc(b.name)}</div>
                <div class="bm-board-count">${b.items.length}개 패턴</div>
            </div>
            <i class="bi bi-plus bm-board-plus"></i>
        </div>`).join('');
}

function addToBoard(idx) {
    if (!boardTarget) return;
    const board = boards[idx];
    if (!board.items.includes(boardTarget.id)) {
        board.items.push(boardTarget.id);
        localStorage.setItem('pmok_boards', JSON.stringify(boards));
    }
    closeBoardModal();
    showToast(`"${board.name}" 보드에 저장됐습니다.`);
}

function createBoard() {
    const input = document.getElementById('boardNameInput');
    const name  = input.value.trim();
    if (!name) { input.focus(); return; }
    boards.push({ name, items: boardTarget ? [boardTarget.id] : [] });
    localStorage.setItem('pmok_boards', JSON.stringify(boards));
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
    loadPatterns();
});
</script>

<!-- 보드 모달 -->
<div id="boardModal" class="bm-backdrop" style="display:none;">
    <div class="bm-modal">
        <div class="bm-header">
            <span class="bm-title">보드에 저장</span>
            <button class="bm-close" onclick="closeBoardModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div id="boardList" class="bm-list"></div>
        <div class="bm-divider"></div>
        <div class="bm-new">
            <input id="boardNameInput" class="bm-input" type="text" placeholder="새 보드 이름…" maxlength="40">
            <button class="bm-create-btn" onclick="createBoard()">만들기</button>
        </div>
    </div>
</div>

<!-- 토스트 -->
<div id="libToast" class="lib-toast"></div>

</body>
</html>
