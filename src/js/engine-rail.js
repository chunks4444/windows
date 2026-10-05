// ═══════════════════════════════════════════════════════════════════════════
// 엔진 레일 레이아웃 (Canva식) — 왼쪽 아이콘 레일 + 탭 패널 + 상단 툴바
// ───────────────────────────────────────────────────────────────────────────
// 엔진 페이지에 <nav class="tool-rail">가 있을 때만 동작한다. 스타일은 src/css/engine-rail.css.
// engine-common.js 바로 다음, 엔진 JS({type}.js) 앞에 불러온다 — DOMContentLoaded 처리 순서를 유지하기 위함.
//
// 기존 사이드바 요소(id)와 엔진 JS 동작은 그대로 두고 배치·표시만 바꾼다. 여기서 쓰는 전역들:
//   engine-common.js — toggleSidebar, _engineName, fmtDate, pmConfirm, pmAlert, addSvgInsert,
//                      renderSavedThumbList, resizeCanvasDebounced, OIL_* / isOilFinish
//   {type}.js        — versions, currentVerIdx, openDrawingByTitle, facePaintMode, scaleFactor
// ═══════════════════════════════════════════════════════════════════════════

// ── 툴 레일 · 탭 패널 ─────────────────────────────
// 엔진 페이지에 <nav class="tool-rail">가 있을 때만 동작한다 (아직 옛 좌/우 사이드바 구조인 엔진은 그대로).
// 패널(#sidebar)은 탭마다 <section class="rail-pane" data-pane="...">를 하나씩 갖고, 고른 탭 하나만 보인다.
// 같은 탭을 다시 누르면 패널이 접힌다(toggleSidebar → 캔버스가 넓어지며 다시 맞춰짐).
// 컬렉션·내 도면·문양 탭은 처음 열 때 서버에서 목록을 불러온다.
(function () {
    const PANE_KEY = 'pmok_rail_pane';
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const lh  = p => (typeof window._lh === 'function' ? window._lh(p) : p);
    const token = () => { try { return localStorage.getItem('pmok_auth_token'); } catch { return null; } };
    const authHeaders = () => ({ 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() });
    const emptyMsg = (msg, btn) => `<div class="pane-empty">${msg}${btn ? `<button type="button" class="hbtn pane-login-btn">${btn}</button>` : ''}</div>`;
    const loaders = {};

    document.addEventListener('DOMContentLoaded', () => {
        const rail  = document.getElementById('toolRail');
        const panel = document.getElementById('sidebar');
        if (!rail || !panel) return;
        const buttons = [...rail.querySelectorAll('.rail-btn')];
        const panes   = [...panel.querySelectorAll('.rail-pane')];
        let current   = 'door';

        function syncActive() {
            const open = !panel.classList.contains('collapsed');
            buttons.forEach(b => b.classList.toggle('active', open && b.dataset.pane === current));
        }

        function show(key) {
            if (!panes.some(p => p.dataset.pane === key)) key = 'door';
            current = key;
            panes.forEach(p => { p.hidden = p.dataset.pane !== key; });
            panel.scrollTop = 0;
            try { localStorage.setItem(PANE_KEY, key); } catch {}
            loaders[key]?.();
            syncActive();
        }

        buttons.forEach(btn => btn.addEventListener('click', () => {
            const collapsed = panel.classList.contains('collapsed');
            if (btn.dataset.pane === current && !collapsed) { toggleSidebar(); syncActive(); return; }
            show(btn.dataset.pane);
            if (collapsed) toggleSidebar();
            syncActive();
        }));

        // 패널 오른쪽 가장자리 가운데의 접기 탭(Canva의 ‹ 버튼) — 패널 밖(.main)에 두어야 패널 스크롤에 안 딸려간다
        const fold = document.createElement('button');
        fold.type = 'button';
        fold.className = 'panel-fold';
        fold.title = _t('패널 접기/펴기');
        fold.innerHTML = '<i class="bi bi-chevron-left"></i>';
        panel.after(fold);
        fold.addEventListener('click', () => { toggleSidebar(); syncActive(); });

        // 다른 곳(도면 목록 버튼 등)에서 특정 탭을 열 때 쓰는 진입점
        window.pmokOpenRailPane = key => {
            show(key);
            if (panel.classList.contains('collapsed')) toggleSidebar();
            syncActive();
        };

        // 주소 끝 #pane=finish 처럼 특정 탭을 바로 열 수 있다 (가이드 링크 등). 없으면 마지막에 열었던 탭
        let saved = (/#pane=(\w+)/.exec(location.hash) || [])[1] || null;
        if (!saved) try { saved = localStorage.getItem(PANE_KEY); } catch {}
        // 탭별 로더(loaders.*)를 먼저 등록해야 처음 열리는 탭도 목록을 불러온다
        initCollectionPane();
        initMinePane();
        initMotifPane();
        initFinishColors();
        initRenderPane();

        show(saved || 'door');
        // 처음 열 때는 패널을 접어 캔버스를 넓게 — 주소에 #pane=으로 탭을 지정해 들어온 경우만 펼친 채로.
        // 애니메이션 없이 즉시 접어야 첫 resizeCanvas가 넓어진 폭을 읽는다(모바일 초기화와 같은 방식)
        if (!/#pane=\w+/.test(location.hash) && !panel.classList.contains('collapsed')) {
            const noAnim = document.createElement('style');
            noAnim.textContent = '.controls,.panel-fold,.panel-fold i,.pm-engine-story-link{transition:none!important}';
            document.head.appendChild(noAnim);
            panel.classList.add('collapsed');
            requestAnimationFrame(() => { noAnim.remove(); resizeCanvasDebounced(); });
        }
        requestAnimationFrame(syncActive);
    });

    // ── 컬렉션: 이 엔진으로 만든 컬렉션 도면 썸네일 ─────────────
    function initCollectionPane() {
        const grid = document.getElementById('collectionGrid');
        if (!grid) return;
        const search = document.getElementById('collectionSearch');
        const more   = document.getElementById('collectionMore');
        document.querySelectorAll('.pane-link[data-lh]').forEach(a => a.setAttribute('href', lh(a.getAttribute('href'))));
        let page = 1, q = '', loaded = false, busy = false;

        async function load(reset) {
            if (busy) return;
            busy = true;
            if (reset) { page = 1; grid.innerHTML = emptyMsg(_t('불러오는 중…')); }
            try {
                const res  = await fetch('/src/api/collection.php', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ engine: _engineName(), q, page }),
                });
                const data = await res.json();
                if (reset) grid.innerHTML = '';
                const items = data.patterns || [];
                if (reset && !items.length) grid.innerHTML = emptyMsg(_t('검색 결과가 없습니다.'));
                items.forEach(p => {
                    if (!p.drawing_id || !p.editor_url) return;
                    const name = p.display_name || p.name_ko || '';
                    const card = document.createElement('button');
                    card.type = 'button';
                    card.className = 'thumb-card';
                    card.title = name;
                    card.innerHTML = (p.image_path ? `<img src="${esc(p.image_path)}" alt="" loading="lazy">` : '<div class="thumb-ph"><i class="bi bi-image"></i></div>')
                        + `<span class="thumb-name">${esc(name)}</span>`;
                    card.addEventListener('click', () => {
                        const url = lh(p.editor_url) + '?drawing_id=' + encodeURIComponent(p.drawing_id);
                        pmConfirm(_t('이 컬렉션 도면을 여시겠습니까?'), () => { location.href = url; },
                            { sub: _t('저장하지 않은 작업은 사라집니다.'), type: 'primary', confirmText: _t('열기') });
                    });
                    grid.appendChild(card);
                });
                more.hidden = !data.has_more;
                page++;
            } catch {
                if (reset) grid.innerHTML = emptyMsg(_t('불러오기 실패'));
            } finally { busy = false; }
        }

        loaders.collection = () => { if (!loaded) { loaded = true; load(true); } };
        more?.addEventListener('click', () => load(false));
        let t;
        search?.addEventListener('input', () => {
            clearTimeout(t);
            t = setTimeout(() => { q = search.value.trim(); load(true); }, 300);
        });
    }

    // ── 파일: 도면 툴바(이름·분류·저장·새 도면·공유) + 버전 드롭다운 + 내 도면 썸네일 ─────────
    // 도면 열기·버전 전환·저장은 각 엔진 JS의 기존 함수/버튼을 그대로 쓴다 (openDrawingByTitle, #verList, #btnSave)
    function initMinePane() {
        const grid = document.getElementById('mineGrid');
        if (!grid) return;
        const pane = grid.closest('.rail-pane');
        const click = id => document.getElementById(id)?.click();
        document.getElementById('btnNewDrawing')?.addEventListener('click', () => setTimeout(refreshList, 0));

        // 버전 드롭다운 버튼(#verBtn) 옆에 현재 버전의 저장 시각 (예: v5  2026.07.11 10:34)
        function renderVerDate() {
            const vs = typeof versions !== 'undefined' ? versions : [];
            const cur = typeof currentVerIdx !== 'undefined' ? currentVerIdx : -1;
            const lbl = document.getElementById('verLabel');
            let dateEl = document.getElementById('verBtnDate');
            if (lbl && !dateEl) { dateEl = document.createElement('small'); dateEl.id = 'verBtnDate'; lbl.after(dateEl); }
            if (dateEl) dateEl.textContent = vs[cur] ? fmtDate(vs[cur].savedAt) : _t('저장된 버전이 없습니다');
        }

        let listTimer;
        async function refreshList() {
            if (!token()) {
                grid.innerHTML = emptyMsg(_t('로그인하면 저장한 도면을 여기서 볼 수 있습니다.'), _t('로그인'));
                grid.querySelector('.pane-login-btn')?.addEventListener('click', () => pmokRequireAuth(refreshList));
                return;
            }
            if (!grid.children.length) grid.innerHTML = emptyMsg(_t('불러오는 중…'));
            const drawings = await window.DrawingSync.list(_engineName());
            if (!drawings.length) { grid.innerHTML = emptyMsg(_t('저장된 도면이 없습니다')); return; }
            const curTitle = (document.getElementById('drawingName')?.value || '').trim();
            grid.innerHTML = '';
            const cards = {};
            drawings.forEach(d => {
                // 카드 안에 삭제 버튼(<button>)이 들어가므로 카드 자체는 div — 버튼 안에 버튼은 넣을 수 없다
                const card = document.createElement('div');
                card.className = 'thumb-card' + (d.title === curTitle ? ' active' : '');
                card.title = d.title;
                card.tabIndex = 0;
                card.setAttribute('role', 'button');
                card.innerHTML = '<div class="thumb-ph"><i class="bi bi-image"></i></div>'
                    + `<span class="thumb-name">${d.locked_at ? '<i class="bi bi-lock-fill"></i> ' : ''}${esc(d.title)}</span>`
                    + `<span class="thumb-date">${esc(fmtDate(new Date(d.updated_at).getTime()))}</span>`
                    + `<button type="button" class="thumb-del" title="${esc(_t('삭제'))}"><i class="bi bi-trash3"></i></button>`;
                card.addEventListener('click', async e => {
                    if (e.target.closest('.thumb-del')) return;
                    if (d.title === (document.getElementById('drawingName')?.value || '').trim()) return;
                    await openDrawingByTitle(d.title);
                    refreshList();
                });
                // 삭제 — 도면 목록 모달(refreshDrawingList)의 삭제와 같은 규칙: 견적요청 중이면 불가, 지금 연 도면이면 새 도면으로
                card.querySelector('.thumb-del').addEventListener('click', e => {
                    e.stopPropagation();
                    if (d.locked_at) { pmAlert(_t('이 도면은 견적요청 중이라 삭제할 수 없습니다.'), { type: 'danger' }); return; }
                    pmConfirm(_t('"%s" 도면을 삭제하시겠습니까?', d.title), async () => {
                        await window.DrawingSync.delete(_engineName(), d.title);
                        if (d.title === (document.getElementById('drawingName')?.value || '').trim()) click('btnNewDrawing');
                        refreshList();
                    }, { sub: _t('모든 버전이 함께 삭제됩니다.') });
                });
                cards[d.id] = card;
                grid.appendChild(card);
            });
            // 썸네일은 목록 API에 없어서 따로 한 번에 받아온다 (대시보드와 같은 방식)
            try {
                const res = await fetch('/src/api/drawings/thumbnails.php', {
                    method: 'POST', headers: authHeaders(), body: JSON.stringify({ ids: drawings.map(d => d.id) }),
                });
                const thumbs = await res.json();
                Object.entries(thumbs || {}).forEach(([id, src]) => {
                    const ph = src && cards[id]?.querySelector('.thumb-ph');
                    if (ph) ph.outerHTML = `<img src="${esc(src)}" alt="" loading="lazy">`;
                });
            } catch {}
        }

        loaders.file = () => { renderVerDate(); refreshList(); };
        // 저장·버전 전환·도면 열기 때마다 엔진이 renderVerList()로 #verList를 다시 그리므로, 그걸 신호로 칩·목록을 갱신
        const verList = document.getElementById('verList');
        if (verList) new MutationObserver(() => {
            renderVerDate();
            if (pane && !pane.hidden) { clearTimeout(listTimer); listTimer = setTimeout(refreshList, 400); }
        }).observe(verList, { childList: true });
    }

    // ── 문양 라이브러리: 내가 올린 문양 + 관리자 라이브러리, 클릭=삽입 / 아이콘=SVG 다운로드 ─────
    function initMotifPane() {
        const libGrid  = document.getElementById('motifLibGrid');
        const mineGrid = document.getElementById('motifMineGrid');
        if (!libGrid || !mineGrid) return;
        let libLoaded = false;

        function insert(url) {
            const img = new Image();
            img.onload  = () => addSvgInsert(url, img.naturalWidth, img.naturalHeight);
            img.onerror = () => addSvgInsert(url, 100, 100);
            img.src = url;
        }

        // 카드 오른쪽 위 버튼 묶음(다운로드·삭제) — 파일 탭 내 도면 카드의 삭제 버튼과 같은 모양·규칙(마우스를 올리면 표시)
        function tile(url, name, onDelete) {
            const el = document.createElement('div');
            el.className = 'motif-tile';
            el.innerHTML = `<img src="${esc(url)}" alt="" loading="lazy"><span>${esc(name)}</span>`
                + '<div class="tile-acts">'
                + `<a class="tile-act" href="${esc(url)}" download="${esc(name.replace(/\.svg$/i, ''))}.svg" title="${esc(_t('SVG 다운로드'))}"><i class="bi bi-download"></i></a>`
                + (onDelete ? `<button type="button" class="tile-act tile-act-del" title="${esc(_t('삭제'))}"><i class="bi bi-trash3"></i></button>` : '')
                + '</div>';
            el.addEventListener('click', e => { if (!e.target.closest('.tile-acts')) insert(url); });
            el.querySelector('.tile-act-del')?.addEventListener('click', e => { e.stopPropagation(); onDelete(); });
            return el;
        }

        // 내가 올린 문양 삭제 — 파일만 지우므로, 이미 도면에 넣어 둔 같은 문양은 그 도면에서 안 보이게 된다
        function deleteMine(it, label) {
            pmConfirm(_t('"%s" 문양을 삭제하시겠습니까?', label), async () => {
                try {
                    const res  = await fetch('/src/api/uploads/svg_insert_delete.php', {
                        method: 'POST', headers: authHeaders(), body: JSON.stringify({ name: it.name }),
                    });
                    const data = await res.json();
                    if (!data.ok) { pmAlert(data.error || _t('삭제에 실패했습니다.'), { type: 'danger' }); return; }
                } catch { pmAlert(_t('삭제에 실패했습니다.'), { type: 'danger' }); return; }
                loadMine();
            }, { sub: _t('이 문양을 넣어 둔 도면에서도 문양이 보이지 않게 됩니다.') });
        }

        async function loadLib() {
            libGrid.innerHTML = emptyMsg(_t('불러오는 중…'));
            try {
                const data = await (await fetch('/src/api/svg_motifs.php')).json();
                const motifs = data.motifs || [];
                libGrid.innerHTML = motifs.length ? '' : emptyMsg(_t('등록된 문양이 없습니다.'));
                motifs.forEach(m => libGrid.appendChild(tile(m.svg_url, m.name)));
            } catch { libGrid.innerHTML = emptyMsg(_t('불러오기 실패')); }
        }

        async function loadMine() {
            if (!token()) {
                mineGrid.innerHTML = emptyMsg(_t('로그인하면 올린 문양이 여기에 모입니다.'), _t('로그인'));
                mineGrid.querySelector('.pane-login-btn')?.addEventListener('click', () => pmokRequireAuth(loadMine));
                return;
            }
            try {
                const res  = await fetch('/src/api/uploads/svg_insert_list.php', { headers: authHeaders() });
                const data = await res.json();
                const items = data.items || [];
                mineGrid.innerHTML = items.length ? '' : emptyMsg(_t('아직 올린 문양이 없습니다.'));
                items.forEach((it, i) => {
                    const label = _t('내 문양') + ' ' + (items.length - i);
                    mineGrid.appendChild(tile(it.url, label, () => deleteMine(it, label)));
                });
            } catch { mineGrid.innerHTML = emptyMsg(_t('불러오기 실패')); }
        }

        loaders.motif = () => { if (!libLoaded) { libLoaded = true; loadLib(); } loadMine(); };
        document.addEventListener('pmok:svg-uploaded', loadMine);
    }

    // ── 렌더링: 공간 사진 그리드 · 분위기 칩 · 직접 입력 · AI 렌더링 버튼 · 결과 갤러리 ─────────────
    // 원래 요소(id)·동작은 그대로 두고 배치와 표시만 바꾼다 — 배경 업로드/지우기 버튼은 그리드 칸이 대신 눌러 준다
    function initRenderPane() {
        const pane = document.querySelector('.rail-pane[data-pane="render"]');
        const thumbs = document.getElementById('thumbList');
        const preset = document.getElementById('aiPromptPreset');
        const prompt = document.getElementById('aiPrompt');
        const saved  = document.getElementById('renderSavedList');
        const runBtn = pane?.querySelector('.rp-ai-btn');
        if (!pane || !thumbs || !preset || !prompt || !saved || !runBtn) return;
        const wrap = thumbs.parentElement;     // 원래 세로로 쌓여 있던 렌더링 칸
        const label = (txt, hint) => { const d = document.createElement('div'); d.className = 'fin-label rp-label'; d.innerHTML = txt + (hint ? `<small>${hint}</small>` : ''); return d; };

        // 1) 공간 사진 — [배경 없음] [+ 업로드] [사진들…] 3열 그리드
        const bgHead = label(_t('공간 사진'), _t('도면을 넣을 실내·실외 사진'));
        const noneTile = document.createElement('button');
        noneTile.type = 'button';
        noneTile.className = 'rp-mini';
        noneTile.title = _t('배경 없음');
        noneTile.innerHTML = `<i class="bi bi-slash-circle"></i><span>${_t('배경 없음')}</span>`;
        noneTile.addEventListener('click', () => document.getElementById('btnClearBg')?.click());
        const addTile = document.createElement('button');
        addTile.type = 'button';
        addTile.className = 'rp-mini';
        addTile.title = _t('사진 올리기');
        addTile.innerHTML = `<i class="bi bi-upload"></i><span>${_t('사진 올리기')}</span>`;
        addTile.addEventListener('click', () => document.getElementById('btnAddThumb')?.click());
        // 두 칸은 #thumbList 밖(같은 그리드)에 둔다 — 엔진이 도면을 열 때 thumbList.innerHTML=''로 비우기 때문
        const bgGrid = document.createElement('div');
        bgGrid.className = 'rp-bg-grid';
        thumbs.before(bgGrid);
        bgGrid.append(thumbs);
        // 배경 없음·사진 올리기는 사진과 구분되게 제목 줄 오른쪽의 작은 버튼으로
        const acts = document.createElement('div');
        acts.className = 'rp-mini-row';
        acts.append(noneTile, addTile);
        bgHead.classList.add('rp-label-row');
        bgHead.appendChild(acts);
        // 사진이 추가·삭제·선택될 때마다: '배경 없음' 선택 표시 + 사진 아래 제목(파일명, 확장자 뺌)
        const syncNone = () => {
            noneTile.classList.toggle('active', !thumbs.querySelector('.rp-thumb-item.active'));
            // 제목 옆 장수 — 사진이 있으면 안내 문구 대신 'N장'
            const cnt = thumbs.querySelectorAll('.rp-thumb-item').length;
            const sm = bgHead.querySelector('small');
            if (sm) sm.textContent = cnt ? _t('%s장', cnt) : _t('도면을 넣을 실내·실외 사진');
            thumbs.querySelectorAll('.rp-thumb-item').forEach((it, n) => {
                if (it.querySelector('.rp-thumb-name')) return;
                // 해시·'-' 같은 의미 없는 파일명은 '배경 N'으로
                let name = (it.querySelector('img')?.alt || '').replace(/\.[a-z0-9]+$/i, '').trim();
                if (!name || name.length < 2 || /^[-_\s]+$/.test(name) || /^[0-9a-f_-]{12,}$/i.test(name)) name = '';
                const cap = document.createElement('span');
                cap.className = 'rp-thumb-name';
                cap.textContent = name || _t('배경') + ' ' + (n + 1);
                cap.title = name;
                it.appendChild(cap);
            });
        };
        new MutationObserver(syncNone).observe(thumbs, { subtree: true, childList: true, attributes: true, attributeFilter: ['class'] });
        syncNone();

        // 2) 추천 프롬프트 — 원래 프리셋 드롭다운을 그대로 쓰고 제목만 붙인다 (고르면 아래 입력칸이 채워짐: select의 기존 onchange)
        const moodHead = label(_t('추천 프롬프트'));

        // 3) 직접 입력 · 4) 실행 버튼 · 5) 결과
        const promptHead = label(_t('직접 입력'));
        runBtn.innerHTML = `<i class="bi bi-stars"></i> ${_t('AI 렌더링')}`;
        const resHead = label(_t('렌더링 결과'), '');
        resHead.querySelector('small')?.remove();
        resHead.insertAdjacentHTML('beforeend', '<small id="renderSavedCount"></small>');

        document.getElementById('btnAddThumb')?.parentElement?.classList.add('rp-hidden');
        wrap.classList.add('rp-pane');
        wrap.prepend(bgHead);
        // 커스텀 셀렉트는 껍데기(.cs-wrap)를 select '바로 앞 형제'로 끼운다 — 이미 끼워졌으면 둘을 같이, 아직이면 select만
        // 옮긴다(나중에 옮긴 자리 앞에 끼워짐)
        const csWrap = preset.previousElementSibling?.classList.contains('cs-wrap') ? preset.previousElementSibling : null;
        bgGrid.after(moodHead, ...(csWrap ? [csWrap, preset] : [preset]), promptHead);
        // prompt(textarea)·실행 버튼은 원래 자리(promptHead 뒤)에 그대로 오도록 옮긴다
        promptHead.after(prompt, runBtn, resHead, saved);
        if (typeof renderSavedThumbList === 'function') renderSavedThumbList();
    }

    // ── 마감 (컬러 먼저 고르기, Canva '색상' 패널식) ─────────────────────────────
    // 마감 셀렉트 대신 [마감 없음] · 천연오일 마감 칩 · 제품별 팔레트(AURO 560/930)를 한 화면에 펼친다.
    // 팔레트의 색을 누르면 그 색이 속한 마감이 자동 선택된다 — txtFinish 값을 바꾸고 change를 보내므로
    // 팔레트 제한·오일 자동색(applyFinishColorPicker)·견적 재계산은 기존 로직 그대로 돈다.
    // 색 적용은 엔진 JS가 만든 피커(buildColorPopup)의 selectColor를 그대로 쓰고, 원래 피커 UI(#finishColorBlock)는 숨겨 둔다.
    function initFinishColors() {
        const block  = document.getElementById('finishColorBlock');
        const finSel = document.getElementById('txtFinish');
        if (!block || !finSel || !block.closest('.rail-pane')) return;
        const pickers = () => window.__pmokColorPickers || [];
        const groups  = window.__pmokColorGroups || [];
        const toRgb   = h => { const m = /^#?([0-9a-f]{6})$/i.exec(String(h || '').trim()); return m ? [0, 2, 4].map(k => parseInt(m[1].substr(k, 2), 16)).join(',') : ''; };
        const dotRgb  = el => ((el && getComputedStyle(el).backgroundColor) || '').replace(/[^\d,]/g, '').split(',').slice(0, 3).join(',');

        // 부위: 원래 피커 칸(.color-row-stack)마다 하나 — 라벨은 그 칸의 글자(번역 포함)를 그대로
        const parts = [...block.querySelectorAll('.color-row-stack')].map(st => {
            const btn = st.querySelector('.color-preview-btn');
            const key = btn?.id.replace('PreviewBtn', '');
            return key && { key, label: (st.querySelector('.color-label')?.textContent || '').replace(/\s*(컬러|colou?r)\s*$/i, '').trim(),
                            dot: document.getElementById(key + 'PreviewDot'), name: document.getElementById(key + 'PreviewName') };
        }).filter(Boolean);
        if (!parts.length) return;
        let target = null;      // 처음엔 부위 미선택 — 부위를 안 고르고 색을 누르면 안내를 띄운다
        let needPart = false;

        const finishOpts = [...finSel.options].map(o => o.value);
        const finishLabel = v => [...finSel.options].find(o => o.value === v)?.textContent.trim() || v;
        const productOf = v => (/AURO\s*(\d{3})/i.exec(v || '') || [])[1] || null;
        const stainGroups = groups.map(g => {
            const code = productOf(g.key || g.label);
            const fin  = code && finishOpts.find(v => productOf(v) === code && !isOilFinish(v));
            return fin ? { g, fin } : null;
        }).filter(Boolean);
        const oilFinishes = finishOpts.filter(v => v && isOilFinish(v));
        const oilColors   = groups.filter(g => String(g.key || g.label).startsWith(OIL_GROUP_KEY)).flatMap(g => g.colors);
        const oilHexFor   = v => {
            const want = OIL_FINISH_COLOR_CODE[v] || OIL_WOOD_COLOR_CODE[document.getElementById('txtWood')?.value || ''] || 'NO-01';
            return (oilColors.find(c => c.code === want) || oilColors[0] || {}).hex || '#c8b48a';
        };

        // 수종·부자재는 라벨을 붙여 맨 위 두 칸으로, 마감 셀렉트는 아래 팔레트가 대신하므로 숨긴다(CSS)
        const basics = document.createElement('div');
        basics.className = 'fin-basics';
        [['txtWood', _t('수종')], ['txtHardware', _t('부자재')]].forEach(([id, lbl]) => {
            const ctrl = document.getElementById(id)?.closest('.ctrl');
            if (!ctrl) return;
            const cell = document.createElement('div');
            cell.innerHTML = `<div class="fin-label">${lbl}</div>`;
            cell.appendChild(ctrl);
            basics.appendChild(cell);
        });
        finSel.closest('.ctrl')?.before(basics);

        const root = document.createElement('div');
        root.className = 'fin-ui';
        block.before(root);
        // 면 칠하기 버튼은 새 화면 맨 아래로 옮긴다(리스너는 그대로)
        const paintRow = document.getElementById('btnFacePaint')?.parentElement;

        function setFinish(v) {
            if (finSel.value === v) return;
            finSel.value = v;
            finSel.dispatchEvent(new Event('change', { bubbles: true }));
        }
        // 누른 색 칩 바로 위에 잠깐 뜨는 말풍선 (마우스 근처에서 바로 보이도록)
        let tipEl, tipTimer;
        // rect는 render()로 칩이 새로 그려지기 전에 잰 값을 받는다 (다시 그린 뒤엔 누른 칩이 DOM에서 빠져 0,0이 된다)
        // 말풍선은 다른 곳을 누르거나 패널을 스크롤하면 닫는다 (터치 기기는 mouseout이 없어서 남아 있을 수 있음)
        const hideTip = () => { clearTimeout(tipTimer); tipLockUntil = 0; tipEl?.classList.remove('show'); };
        document.addEventListener('pointerdown', e => { if (tipEl?.classList.contains('show') && !e.target.closest('.fin-sw, .fin-base')) hideTip(); }, true);
        document.getElementById('sidebar')?.addEventListener('scroll', hideTip, { passive: true });

        // 안내 말풍선(부위를 고르세요)은 2초 동안 색 코드 오버 말풍선이 덮어쓰지 못하게 잠근다 —
        // 색을 누르면 패널을 다시 그리므로 커서 아래 새 칩에서 mouseover가 곧바로 다시 발생한다
        let tipLockUntil = 0;
        function tipAbove(r, msg, sticky) {
            if (sticky && Date.now() < tipLockUntil) return;
            if (!sticky) tipLockUntil = Date.now() + 2000;
            if (!tipEl) { tipEl = document.createElement('div'); tipEl.className = 'fin-tip'; document.body.appendChild(tipEl); }
            tipEl.textContent = msg;
            tipEl.style.left = (r.left + r.width / 2) + 'px';
            tipEl.style.top  = r.top + 'px';
            tipEl.classList.remove('show'); void tipEl.offsetWidth; tipEl.classList.add('show');
            clearTimeout(tipTimer);
            if (!sticky) tipTimer = setTimeout(() => tipEl.classList.remove('show'), 2000);   // sticky=마우스 오버 동안 유지
        }

        function pickColor(groupFin, hex, el) {
            if (!target) {
                const rect = el?.getBoundingClientRect();
                needPart = true;
                render();
                const row = root.querySelector('.fin-parts');
                row?.classList.remove('shake'); void row?.offsetWidth; row?.classList.add('shake');
                row?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                if (rect) tipAbove(rect, _t('먼저 색칠할 부위를 고르세요.'));
                return;
            }
            setFinish(groupFin);
            pickers().find(p => p.id === target + 'Popup')?.selectColor(hex);
            if (target === 'face') setPaint(true);   // 면 색을 고르면 바로 칠하기 — 도면 위에선 커서가 페인트 버킷
            window.draw?.();
            render();
        }

        function render() {
            const fin   = finSel.value;
            const stain = stainGroups.some(sg => sg.fin === fin);
            const cur   = parts.find(p => p.key === target);
            const curRgb = dotRgb(cur?.dot);
            const esc2 = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

            let h = '';
            // 부위 고르기 — 스테인일 때만 부위별로 색이 다르다 (마감 없음·오일은 한 가지 색)
            h += `<div class="fin-label">${_t('색칠할 부위')}${target ? '' : `<small>${_t('부위를 고른 뒤 아래에서 색을 누르세요.')}</small>`}</div><div class="fin-parts${needPart ? ' is-need' : ''}">`;
            parts.forEach(p => {
                h += `<button type="button" class="fin-part${p.key === target ? ' active' : ''}" data-part="${p.key}">`
                   + `<span class="fin-dot" style="background:${esc2(p.dot?.style.background || '')}"></span>${esc2(p.label)}</button>`;
            });
            h += '</div>';
            if (needPart) h += `<p class="fin-need"><i class="bi bi-exclamation-circle"></i> ${_t('먼저 색칠할 부위를 고르세요.')}</p>`;
            if (target) h += `<div class="fin-current">` + (!target ? '' : fin ? `<span class="fin-dot" style="background:${esc2(cur?.dot?.style.background || '')}"></span>` : '<span class="fin-dot fin-dot-none"></span>')
                + `<span>${esc2(!target ? _t('부위를 고른 뒤 아래에서 색을 누르세요.') : stain ? (cur?.name?.textContent || '') : (fin ? finishLabel(fin) : _t('마감 없음')))}</span></div>`;
            if (!stain && target) h += `<p class="fin-hint">${fin ? _t('오일 마감은 나무결 그대로 한 가지 색으로 칠해집니다.') : _t('나무 본래 색 그대로입니다. 아래에서 색을 고르면 그 마감이 함께 선택됩니다.')}</p>`;

            // 기본 마감 — 마감 없음 + 천연오일을 팔레트와 같은 동그라미(아래 작은 이름)로
            // 이름의 괄호 안(제품명, 예: 'AURO 126')은 떼어 두고 마우스를 올리면 말풍선으로 보여준다
            const splitName = n => { const m = /^(.*?)\s*\(([^)]+)\)\s*$/.exec(n); return m ? [m[1], m[2]] : [n, '']; };
            const base = [{ v: '', label: _t('마감 없음'), bg: '', product: _t('원목 그대로') }].concat(oilFinishes.map(v => {
                const [label, product] = splitName(finishLabel(v));
                return { v, label, product, bg: oilHexFor(v) };
            }));
            h += `<div class="fin-group"><div class="fin-group-head">${_t('기본')}</div><div class="fin-bases">`;
            base.forEach(b => {
                h += `<button type="button" class="fin-base${fin === b.v ? ' active' : ''}" data-fin="${esc2(b.v)}" data-tip="${esc2(b.product || b.label)}">`
                   + `<span class="fin-base-dot${b.v ? '' : ' fin-dot-none'}"${b.bg ? ` style="background:${esc2(b.bg)}"` : ''}></span>`
                   + `<span class="fin-base-name">${esc2(b.label)}</span></button>`;
            });
            h += '</div></div>';
            // 제품별 팔레트
            stainGroups.forEach(({ g, fin: gf }) => {
                h += `<div class="fin-group${fin === gf ? ' is-on' : ''}"><div class="fin-group-head">${esc2(finishLabel(gf))}<small>${_t('%s색', g.colors.length)}</small></div><div class="fin-swatches">`;
                g.colors.forEach(c => {
                    const on = fin === gf && toRgb(c.hex) === curRgb;
                    const tip = [c.brand, c.code, c.name].filter(Boolean).join(' ');
                    h += `<button type="button" class="fin-sw${on ? ' active' : ''}" style="background:${esc2(c.hex)}" data-tip="${esc2(tip)}" data-fin="${esc2(gf)}" data-hex="${esc2(c.hex)}"></button>`;
                });
                h += '</div></div>';
            });
            root.innerHTML = h;
            if (paintRow) { root.appendChild(paintRow); paintRow.hidden = target !== 'face'; }
            if (target === 'face') {
                const on = typeof facePaintMode !== 'undefined' && facePaintMode;
                root.querySelector('.fin-current')?.insertAdjacentHTML('afterend',
                    `<p class="fin-hint">${on ? _t('도면에서 칠할 면을 클릭하세요. 오른쪽 클릭은 지우기입니다.') : _t('아래에서 색을 고르면 도면에서 면을 칠할 수 있습니다.')}</p>`);
            }
        }

        // 모든 색(기본 마감·팔레트 칩)에 마우스를 올리면 그 동그라미 위에 제품명·색 이름 말풍선 (브라우저 title 툴팁 대신)
        root.addEventListener('mouseover', e => {
            const b = e.target.closest('[data-tip]');
            if (!b || b.contains(e.relatedTarget)) return;
            clearTimeout(tipTimer);
            tipAbove((b.querySelector('.fin-base-dot') || b).getBoundingClientRect(), b.dataset.tip, true);
        });
        root.addEventListener('mouseout', e => {
            const b = e.target.closest('[data-tip]');
            if (b && !b.contains(e.relatedTarget) && Date.now() >= tipLockUntil) tipEl?.classList.remove('show');
        });

        // 면 칠하기 모드 — 엔진 JS의 기존 토글(#btnFacePaint)을 그대로 눌러 facePaintMode를 바꾸고,
        // 켜져 있는 동안 캔버스 영역에 .pm-face-painting을 달아 커서를 페인트 버킷으로 바꾼다(CSS)
        const facePaintBtn = document.getElementById('btnFacePaint');
        const canvasArea   = document.getElementById('canvasContainer');
        function setPaint(on) {
            const cur = typeof facePaintMode !== 'undefined' && facePaintMode;
            if (cur !== on) facePaintBtn?.click();
        }
        // 다른 곳(선 칠하기 등)에서 꺼져도 커서가 따라가도록 버튼 상태를 기준으로 클래스를 맞춘다
        if (facePaintBtn) new MutationObserver(() => {
            canvasArea?.classList.toggle('pm-face-painting', facePaintBtn.classList.contains('cv-btn-active'));
            render();
        }).observe(facePaintBtn, { attributes: true, attributeFilter: ['class'] });
        // 마감 탭을 떠나면(다른 탭·패널 접기) 칠하기를 끈다
        const finPane = block.closest('.rail-pane');
        new MutationObserver(() => { if (finPane.hidden) setPaint(false); }).observe(finPane, { attributes: true, attributeFilter: ['hidden'] });
        new MutationObserver(() => { if (document.getElementById('sidebar')?.classList.contains('collapsed')) setPaint(false); })
            .observe(document.getElementById('sidebar'), { attributes: true, attributeFilter: ['class'] });

        root.addEventListener('click', e => {
            const part = e.target.closest('.fin-part');
            if (part) { target = part.dataset.part; needPart = false; if (target !== 'face') setPaint(false); render(); return; }
            const sw = e.target.closest('.fin-sw');
            if (sw) { pickColor(sw.dataset.fin, sw.dataset.hex, sw); return; }
            const chip = e.target.closest('.fin-base');
            if (chip) { setFinish(chip.dataset.fin); render(); }
        });
        // 도면 불러오기·수종 변경·오일 자동색 등 다른 곳에서 바뀌어도 화면을 맞춘다
        finSel.addEventListener('change', () => setTimeout(render, 0));
        document.getElementById('txtWood')?.addEventListener('change', () => setTimeout(render, 0));
        let t;
        const mo = new MutationObserver(() => { clearTimeout(t); t = setTimeout(render, 30); });
        parts.forEach(p => p.dot && mo.observe(p.dot, { attributes: true, attributeFilter: ['style'] }));
        render();
    }
})();

// ── 하단 툴바 (Canva식 단순화) ─────────────────────────────
// 레일 구조 엔진에서만: 기존 버튼 14개(id·클릭 동작·cv-btn-active 표시 모두 그대로)를 옮겨서
//   가운데 큰 버튼 5개 [선택·이동·선 편집▾·도형▾·배치] + 오른쪽 줌 [− 100% + 화면맞춤] 로 다시 묶는다.
// 선 편집/도형은 누르면 위로 작은 메뉴가 펼쳐지고, 안의 버튼이 켜져 있으면 묶음 버튼도 켜진 것으로 보인다.
(function () {
    const LABELS = {
        btnShapeSelect: '선택', btnPan: '이동', btnScale: '배치', btnResetPlacement: '배치 초기화',
        btnEditDelete: '선 삭제', btnEditAdd: '선 추가', btnEditClear: '편집 초기화',
        // 원은 '원형' — '원'은 번역 사전에서 통화(KRW)로 쓰여 겹친다
        btnShapeCircle: '원형', btnShapeLine: '선', btnShapeRect: '사각형', btnShapeText: '텍스트', btnShapeClear: '모두 삭제',
        btnResetView: '화면 맞춤',
    };
    const GROUPS = [
        { label: '선 편집', icon: 'btnEditDelete', items: ['btnEditDelete', 'btnEditAdd', 'btnEditClear'] },
        { label: '도형', icon: null, items: ['btnShapeCircle', 'btnShapeLine', 'btnShapeRect', 'btnShapeText', 'btnShapeClear'] },
    ];
    const SHAPE_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="8" r="5"/><rect x="11" y="11" width="10" height="10" rx="1.5"/></svg>';

    document.addEventListener('DOMContentLoaded', () => {
        const bar = document.querySelector('.canvas-controls');
        if (!bar || !document.getElementById('toolRail')) return;
        const $ = id => document.getElementById(id);

        function labeled(btn) {
            if (!btn) return null;
            const text = LABELS[btn.id];
            if (text && !btn.querySelector('.cv-label')) btn.insertAdjacentHTML('beforeend', `<span class="cv-label">${_t(text)}</span>`);
            btn.dataset.tip = btn.title;   // 큰 버튼은 글자가 붙어 있으므로 hover 말풍선(::before의 title)은 끈다
            btn.removeAttribute('title');
            btn.title = '';
            return btn;
        }

        const tools = document.createElement('div');
        tools.className = 'cv-tools';
        const zoom = document.createElement('div');
        zoom.className = 'cv-zoom';

        ['btnShapeSelect', 'btnPan'].forEach(id => { const b = labeled($(id)); if (b) tools.appendChild(b); });

        const groups = GROUPS.map(g => {
            const wrap = document.createElement('div');
            wrap.className = 'cv-group';
            const trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'cv-btn cv-group-btn';
            const iconSrc = g.icon && $(g.icon)?.querySelector('svg');
            trigger.innerHTML = (iconSrc ? iconSrc.outerHTML : SHAPE_ICON) + `<span class="cv-label">${_t(g.label)} <i class="bi bi-chevron-up"></i></span>`;
            const fly = document.createElement('div');
            fly.className = 'cv-flyout';
            const items = g.items.map($).filter(Boolean);
            items.forEach(b => fly.appendChild(labeled(b)));
            wrap.append(fly, trigger);
            tools.appendChild(wrap);
            trigger.addEventListener('click', e => {
                e.stopPropagation();
                const open = !wrap.classList.contains('open');
                document.querySelectorAll('.cv-group.open').forEach(w => w.classList.remove('open'));
                wrap.classList.toggle('open', open);
            });
            // 메뉴 안 버튼을 고르면 메뉴는 닫는다 (기능 실행은 버튼 자체 리스너가 함)
            fly.addEventListener('click', () => setTimeout(() => wrap.classList.remove('open'), 0));
            const sync = () => trigger.classList.toggle('cv-btn-active', items.some(b => b.classList.contains('cv-btn-active')));
            items.forEach(b => new MutationObserver(sync).observe(b, { attributes: true, attributeFilter: ['class'] }));
            sync();
            return wrap;
        });
        document.addEventListener('click', e => { if (!e.target.closest('.cv-group')) groups.forEach(w => w.classList.remove('open')); });

        ['btnScale', 'btnResetPlacement'].forEach(id => { const b = labeled($(id)); if (b) tools.appendChild(b); });

        // 줌: − [100%] + 화면맞춤
        const pct = document.createElement('button');
        pct.type = 'button';
        pct.className = 'cv-zoom-pct';
        pct.textContent = '100%';
        pct.addEventListener('click', () => $('btnResetView')?.click());
        [$('btnZoomOut'), pct, $('btnZoomIn'), labeled($('btnResetView'))].forEach(el => el && zoom.appendChild(el));
        $('btnZoomOut')?.classList.add('cv-icon-only');
        $('btnZoomIn')?.classList.add('cv-icon-only');
        // scaleFactor는 엔진 JS의 전역 변수 — 휠·핀치·버튼 등 바뀌는 곳이 많아 주기적으로 읽어서 표시만 갱신
        let last = null;
        setInterval(() => {
            const v = typeof scaleFactor === 'number' ? Math.round(scaleFactor * 100) : null;
            if (v !== null && v !== last) { last = v; pct.textContent = v + '%'; }
        }, 200);

        // 문짝 치수 — 엔진의 눈금자 그리기(drawRulers)가 #cvDims가 있으면 캔버스 대신 여기에 글자로 넣는다
        const dims = document.createElement('span');
        dims.id = 'cvDims';
        dims.className = 'cv-dims';
        dims.hidden = true;
        tools.appendChild(dims);

        // 남는 버튼(혹시 엔진별로 추가된 것)도 잃지 않도록 tools 끝에 붙인다
        bar.querySelectorAll(':scope > .cv-btn').forEach(b => tools.insertBefore(b, dims));
        bar.innerHTML = '';
        bar.classList.add('cv-dock');
        bar.append(tools, zoom);
    });
})();
