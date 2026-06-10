const TYPE_CONFIG = {
    'classic': {
        label: 'Classic Grid',
        editorUrl: '/src/engine/classic/classic.php',
        titleKey: 'pmok_classic_current_title',
        icon: `<svg class="db-section-icon" viewBox="0 0 680 680" fill="none" xmlns="http://www.w3.org/2000/svg">
                 <g transform="rotate(90 340 340)">
                   <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="23"/>
                   <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="23"/>
                   <rect fill="currentColor" x="148" y="148" width="46" height="384" rx="23"/>
                   <rect fill="currentColor" x="294" y="148" width="46" height="384" rx="23"/>
                   <rect fill="currentColor" x="486" y="148" width="46" height="384" rx="23"/>
                 </g>
               </svg>`,
        newUrl: '/src/engine/classic/classic.php',
    },
    'square': {
        label: 'Square Grid',
        editorUrl: '/src/engine/square/square.php',
        titleKey: 'pmok_square_current_title',
        icon: `<svg class="db-section-icon" viewBox="0 0 680 680" fill="none" xmlns="http://www.w3.org/2000/svg">
                 <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="23"/>
                 <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="23"/>
                 <rect fill="currentColor" x="204" y="148" width="46" height="384" rx="23"/>
                 <rect fill="currentColor" x="430" y="148" width="46" height="384" rx="23"/>
               </svg>`,
        newUrl: '/src/engine/square/square.php',
    },
    'cross': {
        label: 'Cross Grid',
        editorUrl: '/src/engine/cross/cross.php',
        titleKey: 'pmok_cross_current_title',
        icon: `<svg class="db-section-icon" viewBox="0 0 680 680" fill="none" xmlns="http://www.w3.org/2000/svg">
                 <g transform="rotate(45 340 340)">
                   <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="23"/>
                   <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="23"/>
                   <rect fill="currentColor" x="204" y="148" width="46" height="384" rx="23"/>
                   <rect fill="currentColor" x="430" y="148" width="46" height="384" rx="23"/>
                 </g>
               </svg>`,
        newUrl: '/src/engine/cross/cross.php',
    },
    'triangle': {
        label: 'Triangle Grid',
        editorUrl: '/src/engine/triangle/triangle.php',
        titleKey: 'pmok_triangle_current_title',
        icon: `<svg class="db-section-icon" viewBox="0 0 680 680" fill="none" xmlns="http://www.w3.org/2000/svg">
                 <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/>
                 <g transform="rotate(60 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
                 <g transform="rotate(120 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
               </svg>`,
        newUrl: '/src/engine/triangle/triangle.php',
    },
    'diamond': {
        label: 'Diamond Grid',
        editorUrl: '/src/engine/diamond/diamond.php',
        titleKey: 'pmok_diamond_current_title',
        icon: `<svg class="db-section-icon" viewBox="0 0 680 680" fill="none" xmlns="http://www.w3.org/2000/svg">
                 <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/>
                 <rect fill="currentColor" x="148" y="317" width="384" height="46" rx="23"/>
                 <g transform="rotate(45 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
                 <g transform="rotate(135 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
               </svg>`,
        newUrl: '/src/engine/diamond/diamond.php',
    },
};

function fmtDate(ts) {
    const d  = new Date(ts);
    const yy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${yy}.${mm}.${dd}`;
}

function fmtWorkTime(sec) {
    if (!sec || sec < 60)  return '1분 미만';
    const h = Math.floor(sec / 3600);
    const m = Math.floor((sec % 3600) / 60);
    if (h > 0) return m > 0 ? `${h}시간 ${m}분` : `${h}시간`;
    return `${m}분`;
}

function openDrawing(type, title) {
    const cfg = TYPE_CONFIG[type];
    if (!cfg) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = cfg.editorUrl;
    const input = document.createElement('input');
    input.type  = 'hidden';
    input.name  = 'drawing';
    input.value = title;
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

function renderCard(d) {
    const cfg   = TYPE_CONFIG[d.type] || { label: d.type };
    const thumb = d.thumbnail
        ? `<img src="${escAttr(d.thumbnail)}" alt="${escAttr(d.title)}" loading="lazy">`
        : `<div class="db-thumb-placeholder">
             <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                 <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
             </svg>
           </div>`;
    return `
        <div class="db-card" onclick="openDrawing('${escAttr(d.type)}', '${escAttr(d.title)}')">
            <button class="db-card-delete" onclick="deleteDrawing(event,'${escAttr(d.type)}','${escAttr(d.title)}')" title="삭제">
                <i class="bi bi-trash"></i>
            </button>
            <div class="db-thumb">${thumb}</div>
            <div class="db-card-body">
                <div class="db-card-title">${escHtml(d.title)}</div>
                <div class="db-card-meta">
                    <div class="db-card-meta-row">
                        <i class="bi bi-clock"></i>
                        <span>작업 <strong>${fmtWorkTime(d.work_time_sec)}</strong></span>
                    </div>
                    <div class="db-card-meta-row">
                        <i class="bi bi-layers"></i>
                        <span>ver <strong>${d.version_count || 0}</strong></span>
                    </div>
                    <div class="db-card-meta-row">
                        <i class="bi bi-pencil"></i>
                        <span>수정 <strong>${fmtDate(new Date(d.updated_at).getTime())}</strong></span>
                    </div>
                </div>
            </div>
        </div>`;
}

function showDeleteModal(desc, onConfirm) {
    const modal   = document.getElementById('dbDeleteModal');
    const descEl  = document.getElementById('dbDeleteModalDesc');
    const confirm = document.getElementById('dbDeleteModalConfirm');
    const cancel  = document.getElementById('dbDeleteModalCancel');

    descEl.textContent = desc;
    modal.style.display = 'flex';

    function close() {
        modal.style.display = 'none';
        confirm.removeEventListener('click', handleConfirm);
        cancel.removeEventListener('click', close);
        modal.removeEventListener('click', handleBackdrop);
    }
    function handleConfirm() { close(); onConfirm(); }
    function handleBackdrop(ev) { if (ev.target === modal) close(); }

    confirm.addEventListener('click', handleConfirm);
    cancel.addEventListener('click', close);
    modal.addEventListener('click', handleBackdrop);
}

async function deleteDrawing(e, type, title) {
    e.stopPropagation();
    const card = e.target.closest('.db-card');
    showDeleteModal(`"${title}" 도면을 삭제합니다.\n이 작업은 되돌릴 수 없습니다.`, async () => {
        const res = await fetch('/src/api/drawings/delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', ..._headers() },
            body: JSON.stringify({ type, title }),
        });
        if (res.ok) {
            card.remove();
            const grid = document.querySelector('#dbContent .db-grid');
            if (grid && !grid.children.length) {
                document.getElementById('dbContent').innerHTML = '<div class="db-empty">저장된 도면이 없습니다.</div>';
            }
        } else {
            alert('삭제에 실패했습니다.');
        }
    });
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function escAttr(str) {
    return String(str).replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

let currentTab = 'drawings';

function switchTab(tab) {
    currentTab = tab;
    document.getElementById('tabDrawings').classList.toggle('active', tab === 'drawings');
    document.getElementById('tabBoards').classList.toggle('active', tab === 'boards');
    document.getElementById('dbContent').style.display       = tab === 'drawings' ? '' : 'none';
    document.getElementById('dbBoardsContent').style.display = tab === 'boards'   ? '' : 'none';
    if (tab === 'boards') loadBoards();
}

function _token() { return localStorage.getItem('pmok_auth_token'); }
function _headers() { return { 'Authorization': 'Bearer ' + _token() }; }

async function loadDashboard() {
    if (!_token()) {
        document.getElementById('dbAuthWall').style.display = '';
        return;
    }

    document.getElementById('dbPage').style.display = '';
    document.getElementById('dbContent').innerHTML = '<div class="db-loading">불러오는 중…</div>';

    try {
        const res  = await fetch('/src/api/drawings/dashboard.php', { headers: _headers() });
        const data = await res.json();

        if (!res.ok || data.error) {
            document.getElementById('dbContent').innerHTML = '<div class="db-loading">불러오기 실패</div>';
            return;
        }

        const drawings = data.drawings || [];
        if (!drawings.length) {
            document.getElementById('dbContent').innerHTML = '<div class="db-empty">저장된 도면이 없습니다.</div>';
            return;
        }
        document.getElementById('dbContent').innerHTML = `<div class="db-grid">${drawings.map(renderCard).join('')}</div>`;
    } catch (e) {
        document.getElementById('dbContent').innerHTML = '<div class="db-loading">오류가 발생했습니다.</div>';
    }
}

/* ── 보드 ─────────────────────────────── */
let boardsLoaded = false;

async function loadBoards() {
    if (boardsLoaded) return;
    const el = document.getElementById('dbBoardsContent');
    el.innerHTML = '<div class="db-loading">불러오는 중…</div>';
    try {
        const res    = await fetch('/src/api/boards/list.php', { headers: _headers() });
        const data   = await res.json();
        const boards = data.boards || [];
        if (!boards.length) {
            el.innerHTML = '<div class="db-empty">저장된 보드가 없습니다.<br><small style="font-weight:400;color:#999;">컬렉션에서 패턴을 보드에 추가해보세요.</small></div>';
        } else {
            el.innerHTML = `<div class="db-grid">${boards.map(renderBoardCard).join('')}</div>`;
        }
        boardsLoaded = true;
    } catch {
        el.innerHTML = '<div class="db-loading">오류가 발생했습니다.</div>';
    }
}

function renderBoardCard(b) {
    return `
        <div class="db-card" data-board-id="${b.id}" onclick="openBoard(${b.id},'${escAttr(b.name)}')">
            <button class="db-card-delete" onclick="deleteBoard(event,${b.id},'${escAttr(b.name)}')" title="삭제">
                <i class="bi bi-trash"></i>
            </button>
            <div class="db-thumb db-board-thumb">
                <i class="bi bi-collection" style="font-size:36px;color:#ccc;"></i>
            </div>
            <div class="db-card-body">
                <div class="db-card-title">${escHtml(b.name)}</div>
                <div class="db-card-meta">
                    <div class="db-card-meta-row">
                        <i class="bi bi-image"></i>
                        <span>패턴 <strong>${b.item_count}</strong>개</span>
                    </div>
                </div>
            </div>
        </div>`;
}

async function openBoard(boardId, boardName) {
    const res   = await fetch(`/src/api/boards/items.php?board_id=${boardId}`, { headers: _headers() });
    const data  = await res.json();
    const items = data.items || [];
    if (!boardName) boardName = data.board?.name || '';

    const content = items.length
        ? items.map(p => `
            <div class="db-board-item">
                <img src="${escAttr(p.image_path)}" alt="${escAttr(p.name_ko)}" loading="lazy">
                <div class="db-board-item-name">${escHtml(p.name_ko)}</div>
                <button class="db-board-item-remove" onclick="removeBoardItem(event,${boardId},${p.id},this)" title="제거">
                    <i class="bi bi-x"></i>
                </button>
            </div>`).join('')
        : '<p style="color:#999;padding:20px;">패턴이 없습니다.</p>';

    document.getElementById('dbBoardModalTitle').textContent = boardName;
    document.getElementById('dbBoardModalBody').innerHTML    = content;
    document.getElementById('dbBoardModal').style.display    = 'flex';
    document.getElementById('dbBoardModal').dataset.boardId  = boardId;
}

async function removeBoardItem(e, boardId, patternId, btn) {
    e.stopPropagation();
    await fetch('/src/api/boards/remove_item.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', ..._headers() },
        body: JSON.stringify({ board_id: boardId, pattern_id: patternId }),
    });
    btn.closest('.db-board-item').remove();
    boardsLoaded = false;
}

async function deleteBoard(e, boardId, boardName) {
    e.stopPropagation();
    const card = e.target.closest('.db-card');
    showDeleteModal(`"${boardName}" 보드를 삭제합니다.\n이 작업은 되돌릴 수 없습니다.`, async () => {
        const res = await fetch('/src/api/boards/delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', ..._headers() },
            body: JSON.stringify({ board_id: boardId }),
        });
        if (res.ok) {
            card.remove();
            boardsLoaded = false;
            const grid = document.querySelector('#dbBoardsContent .db-grid');
            if (grid && !grid.children.length) {
                document.getElementById('dbBoardsContent').innerHTML = '<div class="db-empty">저장된 보드가 없습니다.</div>';
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', async () => {
    await loadDashboard();
    document.getElementById('dbBoardModal').addEventListener('click', e => {
        if (e.target === e.currentTarget) e.currentTarget.style.display = 'none';
    });
    const boardParam = new URLSearchParams(location.search).get('board');
    if (boardParam && _token()) {
        switchTab('boards');
        await loadBoards();
        const card = document.querySelector(`[data-board-id="${boardParam}"]`);
        if (card) card.click();
        else openBoard(parseInt(boardParam), '');
    }
});

window.addEventListener('pmokAuthChanged', () => {
    document.getElementById('dbAuthWall').style.display = 'none';
    loadDashboard();
});
