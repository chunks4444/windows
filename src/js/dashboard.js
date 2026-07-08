const TYPE_CONFIG = {
    'classic': {
        label: 'Classic Lattice',
        editorUrl: '/src/engine/classic/classic.php',
        titleKey: 'pmok_classic_current_title',
        icon: `<svg class="db-section-icon" viewBox="0 0 680 680" fill="none" xmlns="http://www.w3.org/2000/svg">
                 <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="23"/>
                 <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="23"/>
                 <rect fill="currentColor" x="148" y="148" width="46" height="384" rx="23"/>
                 <rect fill="currentColor" x="294" y="148" width="46" height="384" rx="23"/>
                 <rect fill="currentColor" x="486" y="148" width="46" height="384" rx="23"/>
               </svg>`,
        newUrl: '/src/engine/classic/classic.php',
    },
    'square': {
        label: 'Square Lattice',
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
        label: 'Cross Lattice',
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
        label: 'Triangle Lattice',
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
        label: 'Diamond Lattice',
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
    'hexagon': {
        label: 'Hexagon Lattice',
        editorUrl: '/src/engine/hexagon/hexagon.php',
        titleKey: 'pmok_hexagon_current_title',
        icon: `<svg class="db-section-icon" viewBox="0 0 680 680" fill="none" xmlns="http://www.w3.org/2000/svg">
                 <polyline points="210,265 340,190 470,265" stroke="currentColor" stroke-width="32" stroke-linejoin="round" stroke-linecap="round"/>
                 <line x1="210" y1="265" x2="210" y2="415" stroke="currentColor" stroke-width="32" stroke-linecap="round"/>
                 <line x1="470" y1="265" x2="470" y2="415" stroke="currentColor" stroke-width="32" stroke-linecap="round"/>
                 <line x1="210" y1="415" x2="340" y2="490" stroke="currentColor" stroke-width="32" stroke-linecap="round"/>
                 <line x1="470" y1="415" x2="340" y2="490" stroke="currentColor" stroke-width="32" stroke-linecap="round"/>
               </svg>`,
        newUrl: '/src/engine/hexagon/hexagon.php',
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
    const cfg         = TYPE_CONFIG[d.type] || { label: d.type };
    const thumb       = `<div class="db-thumb-placeholder">
             <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                 <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
             </svg>
           </div>`;
    const orderStatus = ORDER_STATUS_LABELS[d.order_status];
    const lockedBadge = d.locked_at
        ? `<div class="db-quote-badge"><i class="bi bi-lock-fill"></i> ${orderStatus ? escHtml(orderStatus.label) : '견적요청중'}</div>`
        : '';
    return `
        <div class="db-card" data-id="${d.id}" onclick="openDrawing('${escAttr(d.type)}', '${escAttr(d.title)}')">
            ${lockedBadge}
            <button class="db-card-copy" onclick="copyDrawing(event,'${escAttr(d.type)}','${escAttr(d.title)}')" title="복사">
                <i class="bi bi-copy"></i>
            </button>
            <button class="db-card-delete" onclick="deleteDrawing(event,'${escAttr(d.type)}','${escAttr(d.title)}')" title="삭제">
                <i class="bi bi-trash"></i>
            </button>
            <div class="db-thumb">${thumb}</div>
            <div class="db-card-body">
                <div class="db-card-title">
                    ${cfg.icon ? `<span class="db-card-engine-icon">${cfg.icon}</span>` : ''}
                    ${escHtml(d.title)}
                </div>
                <div class="db-card-meta">
                    <div class="db-card-meta-row">
                        <i class="bi bi-tag"></i>
                        <select class="db-cat-select" onclick="event.stopPropagation()" onchange="updateCategory(event,${d.id})">
                            <option value="">분류 없음</option>
                            ${_patternCats.map(c => `<option value="${c.id}"${d.pattern_category == c.id ? ' selected' : ''}>${escHtml(c.name)}</option>`).join('')}
                        </select>
                    </div>
                    <div class="db-card-meta-row">
                        <i class="bi bi-layers"></i>
                        <span>ver <strong>${d.version_count || 0}</strong></span>
                    </div>
                    <div class="db-card-meta-row">
                        <i class="bi bi-pencil"></i>
                        <span>수정 <strong>${fmtDate(new Date(d.updated_at).getTime())}</strong></span>
                    </div>
                    <div class="db-card-meta-row">
                        <i class="bi bi-clock"></i>
                        <span>작업 <strong>${fmtWorkTime(d.work_time_sec)}</strong></span>
                    </div>
                </div>
            </div>
        </div>`;
}

async function lazyLoadThumbnails(drawings) {
    const ids = drawings.map(d => d.id).filter(Boolean);
    if (!ids.length) return;
    try {
        const res  = await fetch('/src/api/drawings/thumbnails.php', {
            method: 'POST',
            headers: _headers(),
            body: JSON.stringify({ ids }),
        });
        if (!res.ok) return;
        const map = await res.json();
        for (const [id, src] of Object.entries(map)) {
            if (!src) continue;
            const card  = document.querySelector(`.db-card[data-id="${id}"]`);
            const thumb = card?.querySelector('.db-thumb');
            if (thumb) thumb.innerHTML = `<img src="${escAttr(src)}" alt="" loading="lazy">`;
        }
    } catch {}
}

function showDeleteModal(desc, onConfirm, { title, confirmText } = {}) {
    const modal   = document.getElementById('dbDeleteModal');
    const descEl  = document.getElementById('dbDeleteModalDesc');
    const confirm = document.getElementById('dbDeleteModalConfirm');
    const cancel  = document.getElementById('dbDeleteModalCancel');

    document.getElementById('dbDeleteModalTitle').textContent = title ?? '삭제하시겠습니까?';
    confirm.textContent = confirmText ?? '삭제';
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

function showCopyModal(sourceTitle, onConfirm, { desc, initialValue, title, confirmText } = {}) {
    const modal   = document.getElementById('dbCopyModal');
    const input   = document.getElementById('dbCopyModalInput');
    const confirm = document.getElementById('dbCopyModalConfirm');
    const cancel  = document.getElementById('dbCopyModalCancel');
    const closeX  = document.getElementById('dbCopyModalClose');
    document.getElementById('dbCopyModalTitle').textContent = title ?? '도면 복사';
    confirm.textContent = confirmText ?? '복사';
    document.getElementById('dbCopyModalDesc').textContent = desc ?? `"${sourceTitle}" 도면의 마지막 버전을 복사합니다.`;
    input.value = initialValue ?? `${sourceTitle} - 복사`;
    modal.style.display = 'flex';
    setTimeout(() => { input.select(); input.focus(); }, 80);

    // 편집 중 실수로 닫히지 않도록 X 버튼/취소 버튼으로만 닫음 (배경 클릭·Esc로는 안 닫힘)
    function close() {
        modal.style.display = 'none';
        confirm.removeEventListener('click', handleConfirm);
        cancel.removeEventListener('click', close);
        closeX.removeEventListener('click', close);
        input.removeEventListener('keydown', handleKey);
    }
    function handleConfirm() {
        const t = input.value.trim();
        if (!t) return;
        close();
        onConfirm(t);
    }
    function handleKey(e) {
        if (e.key === 'Enter') handleConfirm();
    }
    confirm.addEventListener('click', handleConfirm);
    cancel.addEventListener('click', close);
    closeX.addEventListener('click', close);
    input.addEventListener('keydown', handleKey);
}

async function copyDrawing(e, type, title) {
    e.stopPropagation();
    showCopyModal(title, async (newTitle) => {
        try {
            // 원본 마지막 버전 로드
            const loadRes = await fetch('/src/api/drawings/load.php', {
                method: 'POST',
                headers: _headers(),
                body: JSON.stringify({ type, title }),
            });
            const data = await loadRes.json();
            if (!data?.versions?.length) { alert('도면을 불러올 수 없습니다.'); return; }

            const last = data.versions[data.versions.length - 1];
            const now  = Date.now();

            // 새 이름으로 저장
            const saveRes = await fetch('/src/api/drawings/save.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', ..._headers() },
                body: JSON.stringify({
                    type,
                    title:          newTitle,
                    created_at:     now,
                    versions:       [{ savedAt: now, params: last.params }],
                    thumbnail:      null,
                    work_time_sec:  0,
                }),
            });
            const result = await saveRes.json();
            if (saveRes.ok && !result.error) {
                loadDashboard();
            } else {
                alert(result.error || '복사에 실패했습니다.');
            }
        } catch {
            alert('복사 중 오류가 발생했습니다.');
        }
    });
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
            const data = await res.json().catch(() => ({}));
            alert(data.error || '삭제에 실패했습니다.');
        }
    });
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function escAttr(str) {
    return String(str).replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

let _patternCats = [];

async function loadCategories() {
    try {
        const res = await fetch('/src/api/drawings/categories.php');
        _patternCats = (await res.json()).categories || [];
    } catch {}
}

async function updateCategory(e, drawingId) {
    e.stopPropagation();
    const val = e.target.value;
    try {
        await fetch('/src/api/drawings/set_category.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', ..._headers() },
            body: JSON.stringify({ drawing_id: drawingId, pattern_category: val || null }),
        });
    } catch {}
}

let currentTab = 'drawings';

function switchTab(tab) {
    currentTab = tab;
    document.getElementById('tabDrawings').classList.toggle('active', tab === 'drawings');
    document.getElementById('tabBoards').classList.toggle('active', tab === 'boards');
    document.getElementById('tabRenders').classList.toggle('active', tab === 'renders');
    document.getElementById('tabOrders').classList.toggle('active', tab === 'orders');
    document.getElementById('dbContent').style.display        = tab === 'drawings' ? '' : 'none';
    document.getElementById('dbBoardsContent').style.display  = tab === 'boards'   ? '' : 'none';
    document.getElementById('dbRendersContent').style.display = tab === 'renders'  ? '' : 'none';
    document.getElementById('dbOrdersContent').style.display  = tab === 'orders'   ? '' : 'none';
    if (tab === 'boards')  loadBoards();
    if (tab === 'renders') loadRenders();
    if (tab === 'orders')  loadOrders();
}

function _token() { return localStorage.getItem('pmok_auth_token'); }
function _headers() { return { 'Authorization': 'Bearer ' + _token() }; }

/* ── 도면 페이징 상태 */
let drawingsPage     = 1;
let drawingsHasMore  = true;
let drawingsLoading  = false;
let drawingsObserver = null;

function setDrawingsLoadMore(visible, loading = false) {
    let wrap = document.getElementById('dbLoadMore');
    if (!wrap) return;
    const btn     = wrap.querySelector('.db-loadmore-btn');
    const text    = wrap.querySelector('.db-loadmore-text');
    const spinner = wrap.querySelector('.db-loadmore-spinner');
    wrap.style.display    = visible || loading ? 'block' : 'none';
    btn.disabled          = loading;
    text.style.display    = loading ? 'none' : '';
    spinner.style.display = loading ? 'inline-block' : 'none';
}

function setupDrawingsObserver() {
    drawingsObserver?.disconnect();
    const wrap = document.getElementById('dbLoadMore');
    if (!wrap) return;
    drawingsObserver = new IntersectionObserver(entries => {
        if (entries[0].isIntersecting && drawingsHasMore && !drawingsLoading) loadMoreDrawings();
    }, { rootMargin: '400px' });
    drawingsObserver.observe(wrap);
}

async function loadMoreDrawings() {
    if (drawingsLoading || !drawingsHasMore) return;
    drawingsLoading = true;
    setDrawingsLoadMore(true, true);

    try {
        const res  = await fetch(`/src/api/drawings/dashboard.php?page=${drawingsPage}`, { headers: _headers() });
        const data = await res.json();

        if (!res.ok || data.error) { drawingsLoading = false; setDrawingsLoadMore(drawingsHasMore); return; }

        const drawings = data.drawings || [];
        drawingsHasMore = !!data.has_more;
        drawingsPage++;

        if (drawings.length) {
            let grid = document.querySelector('#dbContent .db-grid');
            if (!grid) {
                document.getElementById('dbContent').innerHTML = '<div class="db-grid"></div>';
                grid = document.querySelector('#dbContent .db-grid');
            }
            grid.insertAdjacentHTML('beforeend', drawings.map(renderCard).join(''));
            lazyLoadThumbnails(drawings);
        }
    } catch(e) {}

    drawingsLoading = false;
    setDrawingsLoadMore(drawingsHasMore, false);
    if (!drawingsHasMore) drawingsObserver?.disconnect();
}

async function loadDashboard() {
    if (!_token()) {
        document.getElementById('dbAuthWall').style.display = '';
        return;
    }

    document.getElementById('dbPage').style.display = '';
    document.getElementById('dbContent').innerHTML  = '<div class="db-loading">불러오는 중…</div>';
    drawingsPage    = 1;
    drawingsHasMore = true;

    try {
        const res  = await fetch('/src/api/drawings/dashboard.php?page=1', { headers: _headers() });
        const data = await res.json();

        if (!res.ok || data.error) {
            document.getElementById('dbContent').innerHTML = '<div class="db-loading">불러오기 실패</div>';
            return;
        }

        const drawings = data.drawings || [];
        drawingsHasMore = !!data.has_more;
        drawingsPage    = 2;

        const countBadge = document.getElementById('libCountBadge');
        if (countBadge && typeof data.total === 'number') countBadge.textContent = `${data.total}개 도면`;

        if (!drawings.length) {
            document.getElementById('dbContent').innerHTML = '<div class="db-empty">저장된 도면이 없습니다.</div>';
            return;
        }

        document.getElementById('dbContent').innerHTML =
            `<div class="db-grid">${drawings.map(renderCard).join('')}</div>` +
            `<div id="dbLoadMore" style="display:none;text-align:center;padding:24px 0;">
                <button class="db-loadmore-btn" onclick="loadMoreDrawings()">
                    <span class="db-loadmore-text">더 보기</span>
                    <span class="db-loadmore-spinner" style="display:none;"></span>
                </button>
             </div>`;

        setDrawingsLoadMore(drawingsHasMore);
        setupDrawingsObserver();
        lazyLoadThumbnails(drawings);
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
    const thumb = b.first_image
        ? `<img src="${escAttr(b.first_image)}" alt="${escAttr(b.name)}" loading="lazy">`
        : `<div class="db-thumb-placeholder"><i class="bi bi-collection" style="font-size:36px;color:#ccc;"></i></div>`;
    return `
        <div class="db-card" data-board-id="${b.id}" onclick="openBoard(${b.id},'${escAttr(b.name)}')">
            <button class="db-card-copy" onclick="renameBoard(event,${b.id},'${escAttr(b.name)}')" title="이름 변경">
                <i class="bi bi-pencil"></i>
            </button>
            <button class="db-card-delete" onclick="deleteBoard(event,${b.id},'${escAttr(b.name)}')" title="삭제">
                <i class="bi bi-trash"></i>
            </button>
            <div class="db-thumb db-board-thumb">${thumb}</div>
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
    const res   = await fetch('/src/api/boards/items.php', { method: 'POST', headers: _headers(), body: JSON.stringify({ board_id: boardId }) });
    const data  = await res.json();
    const items = data.items || [];
    if (!boardName) boardName = data.board?.name || '';

    const content = items.length
        ? items.map(p => {
            const cfg = p.drawing_id && p.engine ? TYPE_CONFIG[p.engine] : null;
            const clickAttr = cfg
                ? `onclick="openBoardItem(event,${p.drawing_id},'${escAttr(p.engine)}')" style="cursor:pointer;"`
                : '';
            return `
            <div class="db-board-item" ${clickAttr}>
                <img src="${escAttr(p.image_path)}" alt="${escAttr(p.name_ko)}" loading="lazy">
                <div class="db-board-item-name">${escHtml(p.name_ko)}</div>
                <button class="db-board-item-remove" onclick="removeBoardItem(event,${boardId},${p.id},this)" title="제거">
                    <i class="bi bi-x"></i>
                </button>
            </div>`;
          }).join('')
        : '<p style="color:#999;padding:20px;">패턴이 없습니다.</p>';

    document.getElementById('dbBoardModalTitle').textContent = boardName;
    document.getElementById('dbBoardModalBody').innerHTML    = content;
    document.getElementById('dbBoardModal').style.display    = 'flex';
    document.getElementById('dbBoardModal').dataset.boardId  = boardId;
}

function openBoardItem(e, drawingId, engine) {
    e.stopPropagation();
    const cfg = TYPE_CONFIG[engine];
    if (!cfg || !drawingId) return;
    location.href = `${cfg.editorUrl}?drawing_id=${drawingId}`;
}

async function renameBoard(e, boardId, currentName) {
    e.stopPropagation();
    showCopyModal(currentName, async (newName) => {
        const res = await fetch('/src/api/boards/rename.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', ..._headers() },
            body: JSON.stringify({ board_id: boardId, name: newName }),
        });
        if (res.ok) {
            boardsLoaded = false;
            loadBoards();
        } else {
            alert('이름 변경에 실패했습니다.');
        }
    }, { title: '이름 변경', confirmText: '변경', desc: `"${currentName}" 보드 이름을 변경합니다.`, initialValue: currentName });
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
    await loadCategories();
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

/* ── 렌더링 탭 (서버 uploads/renders에 저장된 6개 엔진의 렌더링 결과를 엔진 구분 없이 모아 보여줌) ── */
let _renderItemsCache = [];
let _renderLimitCache = 300;

function loadRenders() {
    const el = document.getElementById('dbRendersContent');
    fetch('/src/api/renders/list.php', { headers: { Authorization: 'Bearer ' + _token() } })
        .then(r => r.json())
        .then(data => {
            if (data.error) { el.innerHTML = '<div class="db-empty">렌더링 목록을 불러오지 못했습니다. 다시 로그인해주세요.</div>'; return; }
            _renderItemsCache = data.renders || [];
            _renderLimitCache = data.limit || 300;

            let html = '';
            if (_renderItemsCache.length >= _renderLimitCache) {
                html += `<div class="rh-usage-banner"><i class="bi bi-exclamation-triangle-fill"></i> 저장 가능한 렌더링(${_renderLimitCache}장)이 가득 찼습니다. 오래된 항목을 삭제해야 새로 렌더링할 수 있어요.</div>`;
            }

            if (!_renderItemsCache.length) {
                html += '<div class="db-empty">저장된 렌더링이 없습니다.</div>';
                el.innerHTML = html;
                return;
            }

            html += `<div class="rh-count">${_renderItemsCache.length} / ${_renderLimitCache}</div>`;
            html += '<div class="rh-grid">' + _renderItemsCache.map((r, i) => `
                <div class="rh-item" data-idx="${i}">
                    <img src="${r.filepath}" loading="lazy">
                    <span class="rh-item-engine">${(TYPE_CONFIG[r.engine]?.label || r.engine)}</span>
                    <span class="rh-item-del" title="삭제" onclick="event.stopPropagation(); deleteRenderItem(${r.id})"><i class="bi bi-x"></i></span>
                </div>`).join('') + '</div>';
            el.innerHTML = html;

            el.querySelectorAll('.rh-item').forEach(node => {
                node.addEventListener('click', () => openRenderModal(_renderItemsCache[parseInt(node.dataset.idx)]));
            });
        })
        .catch(() => { el.innerHTML = '<div class="db-empty">렌더링 목록을 불러오지 못했습니다.</div>'; });
}

function deleteRenderItem(id) {
    fetch('/src/api/renders/delete.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + _token() },
        body: JSON.stringify({ id }),
    }).then(() => loadRenders()).catch(() => {});
}

function openRenderModal(item) {
    const modal = document.getElementById('dbRenderModal');
    const date  = item.created_at ? new Date(item.created_at.replace(' ', 'T')) : null;
    const pad   = n => String(n).padStart(2, '0');
    const dateText = date ? `${date.getFullYear()}.${pad(date.getMonth() + 1)}.${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}` : '';
    const filename = `pmok_${item.engine}_render_${item.id}.png`;

    document.getElementById('dbRenderModalTitle').textContent = `${TYPE_CONFIG[item.engine]?.label || item.engine} · ${dateText}`;
    document.getElementById('dbRenderModalImg').src = item.filepath;
    document.getElementById('dbRenderModalDownload').onclick = () => {
        const link = document.createElement('a');
        link.download = filename;
        link.href = item.filepath;
        link.click();
    };
    document.getElementById('dbRenderModalDelete').onclick = () => {
        deleteRenderItem(item.id);
        modal.style.display = 'none';
    };
    modal.style.display = 'flex';
}

document.getElementById('dbRenderModal')?.addEventListener('click', e => {
    if (e.target === e.currentTarget) e.currentTarget.style.display = 'none';
});

/* ── 주문내역 탭 (라벨/톤은 src/js/order-status-labels.js의 ORDER_STATUS_LABELS 공유) ── */
let _orderItemsCache = [];

function loadOrders() {
    const el = document.getElementById('dbOrdersContent');
    fetch('/src/api/orders/list.php', { headers: { Authorization: 'Bearer ' + _token() } })
        .then(r => r.json())
        .then(data => {
            if (data.error) { el.innerHTML = '<div class="db-empty">주문내역을 불러오지 못했습니다. 다시 로그인해주세요.</div>'; return; }
            _orderItemsCache = data.orders || [];

            if (!_orderItemsCache.length) {
                el.innerHTML = '<div class="db-empty">주문내역이 없습니다.</div>';
                return;
            }

            el.innerHTML = '<div class="ord-list">' + _orderItemsCache.map((o, i) => {
                const st  = ORDER_STATUS_LABELS[o.status] || { label: o.status, tone: 'wait' };
                const cfg = TYPE_CONFIG[o.engine];
                return `
                <div class="ord-row" data-idx="${i}">
                    ${o.thumbnail ? `<img class="ord-row-thumb" src="${o.thumbnail}" loading="lazy">` : '<div class="ord-row-thumb ord-row-thumb-empty"></div>'}
                    <div class="ord-row-main">
                        <div class="ord-row-title">주문번호 #${o.id} · ${(cfg?.label || o.engine)} · ${escHtml(o.title || '(제목 없음)')}</div>
                        <div class="ord-row-sub">주문일 ${o.created_at ? o.created_at.slice(0, 10) : '—'}${o.due_date ? ' · 납기희망 ' + o.due_date : ''}</div>
                    </div>
                    ${o.drawing_id && o.title ? `<button class="ord-modal-btn" onclick="event.stopPropagation(); openDrawing('${escAttr(o.engine)}','${escAttr(o.title)}')">도면 보기</button>` : '<span></span>'}
                    <span class="ord-status-pill" data-tone="${st.tone}">${st.label}</span>
                </div>`;
            }).join('') + '</div>';

            el.querySelectorAll('.ord-row').forEach(node => {
                node.addEventListener('click', () => openOrderModal(_orderItemsCache[parseInt(node.dataset.idx)]));
            });
        })
        .catch(() => { el.innerHTML = '<div class="db-empty">주문내역을 불러오지 못했습니다.</div>'; });
}

function openOrderModal(o) {
    const st = ORDER_STATUS_LABELS[o.status] || { label: o.status, tone: 'wait' };
    const cfg = TYPE_CONFIG[o.engine];
    document.getElementById('dbOrderModalTitle').textContent = `주문번호 #${o.id} · ${cfg?.label || o.engine} · ${o.title || '(제목 없음)'}`;

    let html = `<div style="margin-bottom:14px;"><span class="ord-status-pill" data-tone="${st.tone}">${st.label}</span></div>`;

    if (o.memo) {
        html += `<div class="ord-modal-note"><strong>요청사항</strong><p>${escHtml(o.memo)}</p></div>`;
    }
    if (o.status === 'revision_requested' && o.revision_note) {
        html += `<div class="ord-modal-note"><strong>수정요청 사유</strong><p>${escHtml(o.revision_note)}</p></div>`;
    }
    if (o.status === 'shipped' || o.status === 'delivered') {
        if (o.tracking_carrier || o.tracking_number) {
            html += `<div class="ord-modal-note"><strong>배송 정보</strong><p>${escHtml(o.tracking_carrier || '')} ${escHtml(o.tracking_number || '')}</p></div>`;
        }
    }

    const won = n => Number(n).toLocaleString() + '원';
    html += `<div class="ord-modal-meta">
        <div><span>주문일</span>${fmtOrderDatetime(o.created_at)}</div>
        <div><span>납기희망일</span>${o.due_date || '—'}</div>
        <div><span>최근 처리일</span>${fmtOrderDatetime(o.reviewed_at)}</div>
        <div><span>예상견적</span>${o.estimated_price ? won(o.estimated_price) : '—'}</div>
        ${o.final_price ? `<div><span>확정 가격</span>${won(o.final_price)}</div>` : ''}
    </div>`;

    const actions = [];
    if (o.drawing_id && o.title) {
        actions.push(`<button class="ord-modal-btn" onclick="openDrawing('${escAttr(o.engine)}','${escAttr(o.title)}')">도면 보기</button>`);
    }
    if (['pending_review', 'revision_requested'].includes(o.status)) {
        actions.push(`<button class="ord-modal-btn ord-modal-btn-danger" onclick="cancelOrder(${o.id})">견적요청/주문취소</button>`);
    }
    if (actions.length) {
        html += `<div class="ord-modal-actions">${actions.join('')}</div>`;
    }

    document.getElementById('dbOrderModalBody').innerHTML = html;
    document.getElementById('dbOrderModal').style.display = 'flex';
}

function cancelOrder(id) {
    showDeleteModal(
        '취소 후에는 되돌릴 수 없습니다.',
        async () => {
            const res  = await fetch('/src/api/orders/cancel.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + _token() },
                body: JSON.stringify({ id }),
            });
            const data = await res.json();
            if (!res.ok) { alert(data.error || '취소에 실패했습니다.'); return; }
            document.getElementById('dbOrderModal').style.display = 'none';
            loadOrders();
        },
        { title: '견적요청/주문을 취소하시겠습니까?', confirmText: '취소하기' }
    );
}

document.getElementById('dbOrderModal')?.addEventListener('click', e => {
    if (e.target === e.currentTarget) e.currentTarget.style.display = 'none';
});
