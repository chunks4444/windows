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
const PATTERNS = [
    {
        id: 'jeongja',
        name: '정자살',
        sub: 'Square Grid · 사분턱',
        category: 'geosil',
        editorUrl: '/src/engine/Sabunteok/Sabunteok.php',
        aspect: 1.4,
        color: '#e8ede9',
        render: (w, h) => `
          <svg viewBox="0 0 ${w} ${h}" xmlns="http://www.w3.org/2000/svg">
            <rect width="${w}" height="${h}" fill="#eef1ee"/>
            ${gridLines(w, h, 4, 5, '#5a6b58', 6)}
          </svg>`
    },
    {
        id: 'aja',
        name: '아자살',
        sub: '亞字 Grid · 사분턱',
        category: 'gyeongbok',
        editorUrl: '/src/engine/Sabunteok/Sabunteok.php',
        aspect: 1.6,
        color: '#ede9e3',
        render: (w, h) => `
          <svg viewBox="0 0 ${w} ${h}" xmlns="http://www.w3.org/2000/svg">
            <rect width="${w}" height="${h}" fill="#f0ece4"/>
            ${ajaPattern(w, h)}
          </svg>`
    },
    {
        id: 'bit',
        name: '빗살',
        sub: 'Diagonal Grid · 삼분턱',
        category: 'cafe',
        editorUrl: '/src/engine/sambuntok/sambuntok.php',
        aspect: 1.2,
        color: '#e3e8ed',
        render: (w, h) => `
          <svg viewBox="0 0 ${w} ${h}" xmlns="http://www.w3.org/2000/svg">
            <rect width="${w}" height="${h}" fill="#e8ecf0"/>
            ${diagonalLines(w, h, '#4a5c6b', 5)}
          </svg>`
    },
    {
        id: 'sosl',
        name: '소슬살',
        sub: 'Soaring Grid · 삼분턱',
        category: 'anbang',
        editorUrl: '/src/engine/sambuntok/sambuntok.php',
        aspect: 2.0,
        color: '#ede3e3',
        render: (w, h) => `
          <svg viewBox="0 0 ${w} ${h}" xmlns="http://www.w3.org/2000/svg">
            <rect width="${w}" height="${h}" fill="#f0e8e8"/>
            ${soaringPattern(w, h)}
          </svg>`
    },
    {
        id: 'gwiap',
        name: '귀갑살',
        sub: 'Hexagonal · 특수형',
        category: 'changdeok',
        editorUrl: '/src/engine/Sabunteok/Sabunteok.php',
        aspect: 1.1,
        color: '#e3ede8',
        render: (w, h) => `
          <svg viewBox="0 0 ${w} ${h}" xmlns="http://www.w3.org/2000/svg">
            <rect width="${w}" height="${h}" fill="#e8f0ec"/>
            ${hexPattern(w, h)}
          </svg>`
    },
    {
        id: 'wanja',
        name: '완자살',
        sub: '卍字 Grid · 사분턱',
        category: 'hotel',
        editorUrl: '/src/engine/Sabunteok/Sabunteok.php',
        aspect: 1.3,
        color: '#ede8e3',
        render: (w, h) => `
          <svg viewBox="0 0 ${w} ${h}" xmlns="http://www.w3.org/2000/svg">
            <rect width="${w}" height="${h}" fill="#f0ece8"/>
            ${wanjaPattern(w, h)}
          </svg>`
    },
    {
        id: 'bitkkot',
        name: '빗꽃살',
        sub: 'Diagonal Floral · 삼분턱',
        category: 'wall',
        editorUrl: '/src/engine/sambuntok/sambuntok.php',
        aspect: 1.7,
        color: '#ebe3ed',
        render: (w, h) => `
          <svg viewBox="0 0 ${w} ${h}" xmlns="http://www.w3.org/2000/svg">
            <rect width="${w}" height="${h}" fill="#ede8f0"/>
            ${floralDiagonal(w, h)}
          </svg>`
    },
    {
        id: 'kkot',
        name: '꽃살',
        sub: 'Floral Grid · 특수형',
        category: 'jungmun',
        editorUrl: '/src/engine/Sabunteok/Sabunteok.php',
        aspect: 1.0,
        color: '#f0e8ea',
        render: (w, h) => `
          <svg viewBox="0 0 ${w} ${h}" xmlns="http://www.w3.org/2000/svg">
            <rect width="${w}" height="${h}" fill="#f4ecee"/>
            ${floralPattern(w, h)}
          </svg>`
    },
    {
        id: 'sosl2',
        name: '솟을살',
        sub: 'Rising Grid · 삼분턱',
        category: 'hyeongwan',
        editorUrl: '/src/engine/sambuntok/sambuntok.php',
        aspect: 2.4,
        color: '#e3eaed',
        render: (w, h) => `
          <svg viewBox="0 0 ${w} ${h}" xmlns="http://www.w3.org/2000/svg">
            <rect width="${w}" height="${h}" fill="#eaeff2"/>
            ${risingPattern(w, h)}
          </svg>`
    },
    {
        id: 'gyosa',
        name: '교살',
        sub: 'Cross Grid · 사분턱',
        category: 'lobby',
        editorUrl: '/src/engine/Sabunteok/Sabunteok.php',
        aspect: 1.5,
        color: '#e8ede3',
        render: (w, h) => `
          <svg viewBox="0 0 ${w} ${h}" xmlns="http://www.w3.org/2000/svg">
            <rect width="${w}" height="${h}" fill="#ecf0e8"/>
            ${crossPattern(w, h)}
          </svg>`
    },
    {
        id: 'man',
        name: '만살',
        sub: '萬字 Grid · 사분턱',
        category: 'office',
        editorUrl: '/src/engine/Sabunteok/Sabunteok.php',
        aspect: 1.2,
        color: '#ede8e8',
        render: (w, h) => `
          <svg viewBox="0 0 ${w} ${h}" xmlns="http://www.w3.org/2000/svg">
            <rect width="${w}" height="${h}" fill="#f0ecec"/>
            ${manPattern(w, h)}
          </svg>`
    },
    {
        id: 'balgot',
        name: '발꽃살',
        sub: 'Radial Floral · 특수형',
        category: 'gyeongbok',
        editorUrl: '/src/engine/Sabunteok/Sabunteok.php',
        aspect: 0.9,
        color: '#e3ede3',
        render: (w, h) => `
          <svg viewBox="0 0 ${w} ${h}" xmlns="http://www.w3.org/2000/svg">
            <rect width="${w}" height="${h}" fill="#e8f0e8"/>
            ${radialFloral(w, h)}
          </svg>`
    },
];

/* ── SVG 패턴 생성 함수들 ─────────────────────── */
function gridLines(w, h, cols, rows, color, sw) {
    let d = '';
    for (let i = 1; i < cols; i++) {
        const x = (w / cols) * i;
        d += `<line x1="${x}" y1="0" x2="${x}" y2="${h}" stroke="${color}" stroke-width="${sw}" stroke-linecap="square"/>`;
    }
    for (let i = 1; i < rows; i++) {
        const y = (h / rows) * i;
        d += `<line x1="0" y1="${y}" x2="${w}" y2="${y}" stroke="${color}" stroke-width="${sw}" stroke-linecap="square"/>`;
    }
    return d;
}

function diagonalLines(w, h, color, sw) {
    const step = Math.min(w, h) / 5;
    let d = '';
    for (let i = -h; i < w + h; i += step) {
        d += `<line x1="${i}" y1="0" x2="${i + h}" y2="${h}" stroke="${color}" stroke-width="${sw}" stroke-linecap="square"/>`;
        d += `<line x1="${i}" y1="${h}" x2="${i + h}" y2="0" stroke="${color}" stroke-width="${sw}" stroke-linecap="square"/>`;
    }
    return d;
}

function ajaPattern(w, h) {
    const sw = 5, c = '#5a5040';
    const cw = w / 3, ch = h / 4;
    let d = gridLines(w, h, 3, 4, c, sw);
    for (let col = 0; col < 3; col++) {
        for (let row = 0; row < 4; row++) {
            const x = col * cw, y = row * ch;
            const mx = x + cw / 2, my = y + ch / 2;
            const ir = Math.min(cw, ch) * 0.28;
            d += `<line x1="${mx - ir}" y1="${my}" x2="${mx + ir}" y2="${my}" stroke="${c}" stroke-width="${sw}"/>`;
            d += `<line x1="${mx}" y1="${my - ir}" x2="${mx}" y2="${my + ir}" stroke="${c}" stroke-width="${sw}"/>`;
            d += `<rect x="${mx - ir}" y="${my - ir}" width="${ir * 2}" height="${ir * 2}" fill="none" stroke="${c}" stroke-width="${sw * 0.7}"/>`;
        }
    }
    return d;
}

function soaringPattern(w, h) {
    const sw = 5, c = '#5a3a3a';
    const step = w / 4;
    let d = '';
    for (let i = 0; i <= 4; i++) {
        const x = i * step;
        d += `<line x1="${x}" y1="0" x2="${x}" y2="${h}" stroke="${c}" stroke-width="${sw}"/>`;
    }
    const rows = Math.ceil(h / step) * 2;
    for (let i = 0; i < rows; i++) {
        const y = (i * step) / 2;
        const offset = (i % 2) * step / 2;
        d += `<line x1="0" y1="${y + offset}" x2="${w}" y2="${y + offset}" stroke="${c}" stroke-width="${sw * 0.6}"/>`;
    }
    return d;
}

function hexPattern(w, h) {
    const c = '#3a5a4a', sw = 5;
    const r = Math.min(w, h) / 7;
    let d = '';
    const cols = Math.ceil(w / (r * 1.732)) + 2;
    const rows = Math.ceil(h / (r * 1.5)) + 2;
    for (let row = -1; row < rows; row++) {
        for (let col = -1; col < cols; col++) {
            const cx = col * r * 1.732 + (row % 2) * r * 0.866;
            const cy = row * r * 1.5;
            let pts = '';
            for (let i = 0; i < 6; i++) {
                const angle = (Math.PI / 180) * (60 * i - 30);
                const px = cx + r * 0.85 * Math.cos(angle);
                const py = cy + r * 0.85 * Math.sin(angle);
                pts += `${px},${py} `;
            }
            d += `<polygon points="${pts.trim()}" fill="none" stroke="${c}" stroke-width="${sw}"/>`;
        }
    }
    return d;
}

function wanjaPattern(w, h) {
    const c = '#5a4030', sw = 4;
    const s = Math.min(w, h) / 4;
    let d = gridLines(w, h, Math.round(w / s), Math.round(h / s), c, sw);
    const cols = Math.round(w / s), rows = Math.round(h / s);
    for (let row = 0; row < rows; row++) {
        for (let col = 0; col < cols; col++) {
            const x = col * s, y = row * s;
            const cx = x + s / 2, cy = y + s / 2;
            const inner = s * 0.22;
            d += `<line x1="${cx - inner}" y1="${cy - inner * 1.8}" x2="${cx - inner}" y2="${cy + inner}" stroke="${c}" stroke-width="${sw}"/>`;
            d += `<line x1="${cx - inner}" y1="${cy + inner}" x2="${cx + inner * 1.8}" y2="${cy + inner}" stroke="${c}" stroke-width="${sw}"/>`;
        }
    }
    return d;
}

function floralDiagonal(w, h) {
    const c = '#4a3a5a', sw = 4;
    let d = diagonalLines(w, h, c, sw);
    const step = Math.min(w, h) / 5;
    for (let x = step / 2; x < w; x += step) {
        for (let y = step / 2; y < h; y += step) {
            d += `<circle cx="${x}" cy="${y}" r="${step * 0.15}" fill="${c}"/>`;
        }
    }
    return d;
}

function floralPattern(w, h) {
    const c = '#5a3040', sw = 4;
    const s = Math.min(w, h) / 4;
    let d = gridLines(w, h, Math.round(w / s), Math.round(h / s), c, sw);
    const cols = Math.round(w / s), rows = Math.round(h / s);
    for (let row = 0; row < rows; row++) {
        for (let col = 0; col < cols; col++) {
            const cx = col * s + s / 2;
            const cy = row * s + s / 2;
            const r = s * 0.22;
            for (let i = 0; i < 4; i++) {
                const angle = (Math.PI / 2) * i;
                d += `<ellipse cx="${cx + Math.cos(angle) * r * 0.9}" cy="${cy + Math.sin(angle) * r * 0.9}" rx="${r * 0.55}" ry="${r * 0.35}" transform="rotate(${i * 90} ${cx + Math.cos(angle) * r * 0.9} ${cy + Math.sin(angle) * r * 0.9})" fill="${c}" opacity="0.7"/>`;
            }
        }
    }
    return d;
}

function risingPattern(w, h) {
    const c = '#3a4a5a', sw = 5;
    const step = w / 5;
    let d = '';
    for (let i = 0; i <= 5; i++) {
        const x = i * step;
        d += `<line x1="${x}" y1="0" x2="${x}" y2="${h}" stroke="${c}" stroke-width="${sw}"/>`;
    }
    let y = 0;
    let level = 0;
    while (y < h) {
        const segH = step * (0.6 + level * 0.3);
        d += `<line x1="0" y1="${y + segH}" x2="${w}" y2="${y + segH}" stroke="${c}" stroke-width="${sw * 0.6}"/>`;
        y += segH;
        level = (level + 1) % 3;
    }
    return d;
}

function crossPattern(w, h) {
    const c = '#3a5a3a', sw = 5;
    const s = Math.min(w, h) / 4;
    let d = gridLines(w, h, Math.round(w / s), Math.round(h / s), c, sw);
    const cols = Math.round(w / s), rows = Math.round(h / s);
    for (let row = 0; row < rows; row++) {
        for (let col = 0; col < cols; col++) {
            const cx = col * s + s / 2;
            const cy = row * s + s / 2;
            const r = s * 0.3;
            d += `<line x1="${cx - r}" y1="${cy}" x2="${cx + r}" y2="${cy}" stroke="${c}" stroke-width="${sw * 0.8}"/>`;
            d += `<line x1="${cx}" y1="${cy - r}" x2="${cx}" y2="${cy + r}" stroke="${c}" stroke-width="${sw * 0.8}"/>`;
        }
    }
    return d;
}

function manPattern(w, h) {
    const c = '#5a3a3a', sw = 4;
    const s = Math.min(w, h) / 3;
    let d = gridLines(w, h, Math.round(w / s), Math.round(h / s), c, sw);
    const cols = Math.round(w / s), rows = Math.round(h / s);
    for (let row = 0; row < rows; row++) {
        for (let col = 0; col < cols; col++) {
            const x = col * s, y = row * s;
            const cx = x + s / 2, cy = y + s / 2;
            const p = s * 0.28;
            d += `<line x1="${cx - p}" y1="${cy - p}" x2="${cx + p}" y2="${cy + p}" stroke="${c}" stroke-width="${sw * 0.7}"/>`;
            d += `<line x1="${cx + p}" y1="${cy - p}" x2="${cx - p}" y2="${cy + p}" stroke="${c}" stroke-width="${sw * 0.7}"/>`;
        }
    }
    return d;
}

function radialFloral(w, h) {
    const c = '#3a5a3a', sw = 4;
    const petals = 6;
    const s = Math.min(w, h) / 4;
    let d = '';
    const cols = Math.round(w / s) + 1;
    const rows = Math.round(h / s) + 1;
    for (let row = 0; row < rows; row++) {
        for (let col = 0; col < cols; col++) {
            const cx = col * s + (row % 2 === 0 ? 0 : s / 2);
            const cy = row * s * 0.866;
            for (let i = 0; i < petals; i++) {
                const angle = (Math.PI * 2 / petals) * i;
                const px = cx + Math.cos(angle) * s * 0.35;
                const py = cy + Math.sin(angle) * s * 0.35;
                d += `<line x1="${cx}" y1="${cy}" x2="${px}" y2="${py}" stroke="${c}" stroke-width="${sw}"/>`;
            }
            d += `<circle cx="${cx}" cy="${cy}" r="${sw * 0.8}" fill="${c}"/>`;
        }
    }
    return d;
}

/* ── 렌더링 ──────────────────────────────────── */
let activeFilter = 'all';
let searchQuery  = '';

function buildCard(p) {
    const W = 300, H = Math.round(W * p.aspect);
    const svgContent = p.render(W, H);
    return `
        <div class="lib-item" data-id="${p.id}" data-category="${p.category}">
            <div class="lib-card-img" style="aspect-ratio:${1}/${p.aspect}">
                ${svgContent}
                <div class="lib-overlay">
                    <div class="lib-overlay-top">
                        <button class="lib-icon-btn lib-like-btn" onclick="toggleLike(event, '${p.id}')" title="좋아요">
                            <i class="bi bi-heart"></i>
                        </button>
                        <button class="lib-icon-btn lib-board-btn" onclick="openBoardModal(event, '${p.id}', '${p.name}')" title="보드에 저장">
                            <i class="bi bi-collection"></i>
                        </button>
                    </div>
                    <div class="lib-overlay-bottom">
                        <div class="lib-overlay-title">${p.name}</div>
                        <div class="lib-overlay-actions">
                            <a href="${p.editorUrl}" class="lib-btn lib-btn-primary">
                                <i class="bi bi-pencil"></i> 열기
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="lib-card-body">
                <div class="lib-card-name">${p.name}</div>
                <div class="lib-card-sub">${p.sub}</div>
            </div>
        </div>`;
}

function renderMasonry() {
    const masonry = document.getElementById('libMasonry');
    const filtered = PATTERNS.filter(p => {
        const matchCat = activeFilter === 'all'    ? true
                       : activeFilter === 'liked'  ? !!likes[p.id]
                       : p.category === activeFilter;
        const matchQ   = !searchQuery || p.name.includes(searchQuery) || p.sub.toLowerCase().includes(searchQuery.toLowerCase());
        return matchCat && matchQ;
    });

    if (!filtered.length) {
        masonry.innerHTML = '<p style="color:var(--text-3);font-size:13px;grid-column:1/-1;padding:40px 0;text-align:center;">검색 결과가 없습니다.</p>';
        return;
    }
    masonry.innerHTML = filtered.map(buildCard).join('');
}

/* ── 필터 버튼 이벤트 바인딩 ─────────────────── */
function bindFilterBtns() {
    document.querySelectorAll('.lib-filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.lib-filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            activeFilter = btn.dataset.filter;
            renderMasonry();
        });
    });
}

async function loadCategories() {
    const res  = await fetch('/src/api/categories.php');
    const data = await res.json();
    const container = document.getElementById('libFilters');
    data.categories.forEach(c => {
        const btn = document.createElement('button');
        btn.className   = 'lib-filter-btn';
        btn.dataset.filter = c.slug;
        btn.textContent = c.name_ko;
        container.appendChild(btn);
    });
    bindFilterBtns();
}

document.getElementById('libSearch').addEventListener('input', e => {
    searchQuery = e.target.value.trim();
    renderMasonry();
});

document.addEventListener('DOMContentLoaded', async () => {
    bindFilterBtns();
    await loadCategories();
    renderMasonry();
});

/* ── 좋아요 ──────────────────────────────────── */
const likes = JSON.parse(localStorage.getItem('pmok_likes') || '{}');

function toggleLike(e, id) {
    e.stopPropagation();
    e.preventDefault();
    likes[id] = !likes[id];
    localStorage.setItem('pmok_likes', JSON.stringify(likes));
    const btn = e.currentTarget;
    const icon = btn.querySelector('i');
    if (likes[id]) {
        icon.className = 'bi bi-heart-fill';
        btn.classList.add('liked');
    } else {
        icon.className = 'bi bi-heart';
        btn.classList.remove('liked');
    }
}

function applyLikeState() {
    document.querySelectorAll('.lib-like-btn').forEach(btn => {
        const id = btn.closest('.lib-item').dataset.id;
        if (likes[id]) {
            btn.querySelector('i').className = 'bi bi-heart-fill';
            btn.classList.add('liked');
        }
    });
}

/* ── 보드 모달 ──────────────────────────────── */
let boardTarget = null;
const boards = JSON.parse(localStorage.getItem('pmok_boards') || '[]');

function openBoardModal(e, id, name) {
    e.stopPropagation();
    e.preventDefault();
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
                <div class="bm-board-name">${escHtml(b.name)}</div>
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
    const board = { name, items: boardTarget ? [boardTarget.id] : [] };
    boards.push(board);
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

document.getElementById('boardModal').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeBoardModal();
});

document.getElementById('boardNameInput').addEventListener('keydown', e => {
    if (e.key === 'Enter') createBoard();
});

const _origRender = renderMasonry;
window.renderMasonry = function() {
    _origRender();
    applyLikeState();
};
renderMasonry();
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
