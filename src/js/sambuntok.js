    let appBackgroundImage = null;
    let placementMode        = false;
    let doorNaturalSize      = { w: 0, h: 0 };
    let doorCornerPositions  = null;
    let overlayDrag          = null;
    let placementNaturalSize = null;
    let handlesVisible       = false;

    // ── 색상 그룹 ─────────────────────────────────
    const colorGroups = [
        {
            label: '스테인',
            colors: [
                { code: '930-00', name: '투명',        hex: '#dec898' },
                { code: '930-01', name: '노랑',        hex: '#f2aa00' },
                { code: '930-02', name: '오렌지',      hex: '#e05218' },
                { code: '930-04', name: '레드브라운',  hex: '#7a1e08' },
                { code: '930-05', name: '황토브라운',  hex: '#906020' },
                { code: '930-06', name: '밤색/브라운', hex: '#5a2e10' },
                { code: '930-08', name: '녹색',        hex: '#2c7030' },
                { code: '930-10', name: '흑단',        hex: '#222218' },
                { code: '930-11', name: '회색',        hex: '#888885' },
            ]
        },
        {
            label: '천연오일',
            colors: [
                { code: 'NO-01', name: '자연',    hex: '#e2c98a' },
                { code: 'NO-02', name: '소나무',  hex: '#c8952a' },
                { code: 'NO-03', name: '참나무',  hex: '#a06828' },
                { code: 'NO-04', name: '느티나무',hex: '#8c4e22' },
                { code: 'NO-05', name: '호두',    hex: '#6a3518' },
                { code: 'NO-06', name: '체리',    hex: '#7a2e18' },
                { code: 'NO-07', name: '황칠',    hex: '#b8880a' },
                { code: 'NO-08', name: '옻칠',    hex: '#1c0c06' },
                { code: 'NO-09', name: '먹',      hex: '#28241e' },
            ]
        }
    ];

    let selectedFrameColor = '#28241e';
    let selectedSlatColor  = '#28241e';

    let rotateOn  = true;

    function lightenHex(hex, amount) {
        const r = Math.min(255, parseInt(hex.slice(1, 3), 16) + amount);
        const g = Math.min(255, parseInt(hex.slice(3, 5), 16) + amount);
        const b = Math.min(255, parseInt(hex.slice(5, 7), 16) + amount);
        return `rgb(${r},${g},${b})`;
    }

    function buildColorPopup(popupId, previewDotId, previewNameId, btnId, onSelect, defaultHex) {
        const popup  = document.getElementById(popupId);
        const dot    = document.getElementById(previewDotId);
        const nameEl = document.getElementById(previewNameId);
        const btn    = document.getElementById(btnId);

        function updatePreview(color) {
            dot.style.background = color.hex;
            nameEl.textContent   = color.name;
        }

        const allColors = colorGroups.flatMap(g => g.colors);

        colorGroups.forEach(group => {
            // 그룹 레이블
            const isFirst = group === colorGroups[0];
            const lbl = document.createElement('div');
            lbl.style.cssText = `grid-column:1/-1;font-size:9px;font-weight:700;letter-spacing:0.8px;text-transform:uppercase;color:var(--text-3);padding:2px 0 1px;${isFirst ? '' : 'border-top:1px solid var(--border);margin-top:4px;'}`;
            lbl.textContent = group.label;
            popup.appendChild(lbl);

            group.colors.forEach(color => {
                const sw = document.createElement('div');
                sw.className = 'color-swatch' + (color.hex === defaultHex ? ' selected' : '');
                sw.style.background = color.hex;
                sw.title = color.name;
                sw.addEventListener('click', e => {
                    e.stopPropagation();
                    popup.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
                    sw.classList.add('selected');
                    updatePreview(color);
                    onSelect(color.hex);
                    popup.classList.remove('open');
                    draw();
                });
                popup.appendChild(sw);
            });
        });

        const def = allColors.find(c => c.hex === defaultHex) || allColors[0];
        updatePreview(def);

        btn.addEventListener('click', e => {
            e.stopPropagation();
            document.querySelectorAll('.color-popup').forEach(p => {
                if (p !== popup) p.classList.remove('open');
            });
            popup.classList.toggle('open');
        });

        function selectColor(hex) {
            const color = allColors.find(c => c.hex === hex) || allColors[0];
            popup.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
            const swatches = popup.querySelectorAll('.color-swatch');
            const idx = allColors.indexOf(color);
            if (swatches[idx]) swatches[idx].classList.add('selected');
            updatePreview(color);
            onSelect(color.hex);
        }

        return { selectColor };
    }

    document.addEventListener('click', () => {
        document.querySelectorAll('.color-popup').forEach(p => p.classList.remove('open'));
    });

    const DEFAULT_FRAME_COLOR = '#28241e';
    const DEFAULT_SLAT_COLOR  = '#28241e';

    selectedFrameColor = DEFAULT_FRAME_COLOR;
    selectedSlatColor  = DEFAULT_SLAT_COLOR;

    // ── 오프스크린 캔버스 (배치 모드 투시 변환용) ────────
    const offCanvas = document.createElement('canvas');

    function drawTriangleAffine(tctx, img,
        sx0, sy0, sx1, sy1, sx2, sy2,
        dx0, dy0, dx1, dy1, dx2, dy2
    ) {
        const det = sx0*(sy1-sy2) + sx1*(sy2-sy0) + sx2*(sy0-sy1);
        if (Math.abs(det) < 0.001) return;
        const a  = (dx0*(sy1-sy2) + dx1*(sy2-sy0) + dx2*(sy0-sy1)) / det;
        const b  = (dy0*(sy1-sy2) + dy1*(sy2-sy0) + dy2*(sy0-sy1)) / det;
        const c  = (sx0*(dx1-dx2) + sx1*(dx2-dx0) + sx2*(dx0-dx1)) / det;
        const d  = (sx0*(dy1-dy2) + sx1*(dy2-dy0) + sx2*(dy0-dy1)) / det;
        const e  = dx0 - a*sx0 - c*sy0;
        const f  = dy0 - b*sx0 - d*sy0;
        tctx.save();
        tctx.beginPath();
        tctx.moveTo(dx0, dy0); tctx.lineTo(dx1, dy1); tctx.lineTo(dx2, dy2);
        tctx.closePath(); tctx.clip();
        tctx.transform(a, b, c, d, e, f);
        tctx.drawImage(img, 0, 0);
        tctx.restore();
    }

    function drawPerspectiveQuad(tctx, img, tl, tr, br, bl) {
        const W = img.width, H = img.height;
        drawTriangleAffine(tctx, img,
            0,0,  W,0,  0,H,
            tl.x,tl.y, tr.x,tr.y, bl.x,bl.y
        );
        drawTriangleAffine(tctx, img,
            W,0,  W,H,  0,H,
            tr.x,tr.y, br.x,br.y, bl.x,bl.y
        );
    }

    const frameColorPicker = buildColorPopup('framePopup', 'framePreviewDot', 'framePreviewName', 'framePreviewBtn',
        hex => { selectedFrameColor = hex; }, selectedFrameColor);
    const slatColorPicker  = buildColorPopup('slatPopup',  'slatPreviewDot',  'slatPreviewName',  'slatPreviewBtn',
        hex => { selectedSlatColor  = hex; }, selectedSlatColor);
    const canvas = document.getElementById('doorCanvas');
    const ctx = canvas.getContext('2d');
    const container = document.getElementById('canvasContainer');
    const sidebar = document.getElementById('sidebar');

    const txtW = document.getElementById('txtW');
    const txtCols = document.getElementById('txtCols');
    const txtH = document.getElementById('txtH');
    
    const txtFrame = document.getElementById('txtFrame');
    const txtFrameH = document.getElementById('txtFrameH');


    const txtSlat = document.getElementById('txtSlat');
    const txtDoorType = document.getElementById('txtDoorType');
    const txtDoorCount = document.getElementById('txtDoorCount');
    const btnSavePNG = document.getElementById('btnSavePNG');
    const btnSavePDF = document.getElementById('btnSavePDF');
    const aiFileUploader = document.getElementById('aiFileUploader');

    let geo = {};
    let scaleFactor = 1.0;
    let panX = 0;
    let panY = 0;
    let logW = 0, logH = 0;
    let isDragging = false;
    let startX, startY;
    let panMode = false;

    // ── 공통 모달 유틸리티 ─────────────────────────
    let _pmModalEl = null;
    function _pmGetEl() {
        if (_pmModalEl) return _pmModalEl;
        const el = document.createElement('div');
        el.className = 'pm-modal-backdrop';
        el.innerHTML = `
            <div class="pm-modal">
                <div class="pm-modal-icon-wrap" id="pmIconWrap">
                    <svg id="pmIconSvg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></svg>
                </div>
                <p class="pm-modal-msg" id="pmMsg"></p>
                <p class="pm-modal-sub" id="pmSub"></p>
                <div class="pm-modal-btns" id="pmBtns"></div>
            </div>`;
        document.body.appendChild(el);
        el.addEventListener('click', e => { if (e.target === el) _pmHide(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') _pmHide(); });
        _pmModalEl = el;
        return el;
    }
    function _pmHide() {
        if (!_pmModalEl) return;
        _pmModalEl.classList.remove('pm-active');
    }
    function _pmShow(msg, sub, iconType, btnsHtml) {
        const el = _pmGetEl();
        const wrap = document.getElementById('pmIconWrap');
        const svg  = document.getElementById('pmIconSvg');
        document.getElementById('pmMsg').textContent = msg;
        const subEl = document.getElementById('pmSub');
        subEl.textContent = sub || '';
        subEl.style.display = sub ? '' : 'none';
        if (iconType === 'danger') {
            wrap.className = 'pm-modal-icon-wrap warn';
            svg.setAttribute('stroke', '#E03030');
            svg.innerHTML = '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>';
        } else {
            wrap.className = 'pm-modal-icon-wrap info';
            svg.setAttribute('stroke', 'var(--teal)');
            svg.innerHTML = '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>';
        }
        document.getElementById('pmBtns').innerHTML = btnsHtml;
        el.style.display = 'flex';
        requestAnimationFrame(() => el.classList.add('pm-active'));
    }
    function pmConfirm(msg, onConfirm, { sub = '', type = 'danger', confirmText = '삭제' } = {}) {
        _pmShow(msg, sub, type, `
            <button class="pm-btn-cancel" id="pmCancel">취소</button>
            <button class="pm-btn-${type}" id="pmConfirmBtn">${confirmText}</button>`);
        document.getElementById('pmCancel').onclick = _pmHide;
        document.getElementById('pmConfirmBtn').onclick = () => { _pmHide(); onConfirm(); };
    }
    function pmAlert(msg, { sub = '', type = 'info' } = {}) {
        _pmShow(msg, sub, type, `<button class="pm-btn-ok" id="pmOk">확인</button>`);
        document.getElementById('pmOk').onclick = _pmHide;
    }

    // ── 렌더링 결과 저장 ───────────────────────────
    const RENDERS_KEY = 'pmok_sambuntok_renders';
    const MAX_RENDERS = 9;
    let savedRenders = [];

    function loadSavedRenders() {
        try { savedRenders = JSON.parse(localStorage.getItem(RENDERS_KEY)) || []; } catch(e) { savedRenders = []; }
        renderSavedThumbList();
    }

    function saveRender(src) {
        savedRenders.push({ src, savedAt: Date.now() });
        if (savedRenders.length > MAX_RENDERS) savedRenders.shift();
        let ok = false;
        while (!ok && savedRenders.length > 0) {
            try { localStorage.setItem(RENDERS_KEY, JSON.stringify(savedRenders)); ok = true; }
            catch(e) { savedRenders.shift(); }
        }
        renderSavedThumbList();
    }

    function renderSavedThumbList() {
        const list = document.getElementById('renderSavedList');
        if (!list) return;
        list.innerHTML = '';
        [...savedRenders].reverse().forEach((r, i) => {
            const realIdx = savedRenders.length - 1 - i;
            const item = document.createElement('div');
            item.className = 'render-saved-item';
            item.innerHTML = `<img src="${r.src}"><span class="render-saved-del"><i class="bi bi-x"></i></span>`;
            item.querySelector('img').addEventListener('click', () => {
                const img = new Image();
                img.onload = () => { appBackgroundImage = img; updateClearBgBtn(); draw(); };
                img.src = r.src;
            });
            item.querySelector('.render-saved-del').addEventListener('click', (e) => {
                e.stopPropagation();
                savedRenders.splice(realIdx, 1);
                try { localStorage.setItem(RENDERS_KEY, JSON.stringify(savedRenders)); } catch(e2) {}
                renderSavedThumbList();
            });
            list.appendChild(item);
        });
    }

    // ── Rendering ──────────────────────────────
    function startAISynthesis() {
        if (!appBackgroundImage) {
            pmAlert('먼저 사진을 업로드해주세요.', { type: 'info' });
            return;
        }
        const prompt = (document.getElementById('aiPrompt')?.value || '').trim();
        if (!prompt) {
            pmAlert('렌더링 프롬프트를 입력해주세요.', { type: 'info' });
            document.getElementById('aiPrompt')?.focus();
            return;
        }

        const dpr = window.devicePixelRatio || 1;

        const composite = document.createElement('canvas');
        composite.width  = logW;
        composite.height = logH;
        const compCtx = composite.getContext('2d');
        if (appBackgroundImage) {
            const img = appBackgroundImage;
            const s = Math.min(logW / img.width, logH / img.height);
            const dW = img.width * s, dH = img.height * s;
            compCtx.drawImage(img, (logW - dW) / 2, (logH - dH) / 2, dW, dH);
        }
        compCtx.globalAlpha = 0.92;
        compCtx.drawImage(canvas, 0, 0, logW, logH);
        compCtx.globalAlpha = 1.0;

        const cx = logW / 2 + panX;
        const cy = logH / 2 + panY;
        const dl = Math.max(0, Math.round(cx - doorNaturalSize.w / 2));
        const dt = Math.max(0, Math.round(cy - doorNaturalSize.h / 2));
        const dw = Math.min(logW - dl, Math.round(doorNaturalSize.w));
        const dh = Math.min(logH - dt, Math.round(doorNaturalSize.h));

        const maskCvs = document.createElement('canvas');
        maskCvs.width  = logW;
        maskCvs.height = logH;
        const mCtx = maskCvs.getContext('2d');
        mCtx.fillStyle = '#000';
        mCtx.fillRect(0, 0, logW, logH);
        mCtx.fillStyle = '#fff';
        mCtx.fillRect(dl, dt, dw, dh);

        const overlay = document.getElementById('renderOverlay');
        overlay.style.display = 'flex';

        fetch('api/render.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                image:  composite.toDataURL('image/jpeg', 0.95),
                mask:   maskCvs.toDataURL('image/png'),
                prompt
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                pmAlert(data.error, { type: 'danger' });
                return;
            }
            const img = new Image();
            img.onload = () => { saveRender(img.src); showRenderResult(img.src); };
            img.src = data.image;
        })
        .catch(() => pmAlert('렌더링 중 오류가 발생했습니다.', { type: 'danger' }))
        .finally(() => { overlay.style.display = 'none'; });
    }

    function showRenderResult(src) {
        let pop = document.getElementById('renderResultPop');
        if (!pop) {
            pop = document.createElement('div');
            pop.id = 'renderResultPop';
            pop.className = 'render-result-pop';
            pop.innerHTML = `
                <div class="render-result-inner">
                    <div class="render-result-toolbar">
                        <span class="render-result-title">Rendering 결과</span>
                        <div style="display:flex;gap:6px;">
                            <button class="render-result-apply" id="rrApply">배경 적용</button>
                            <button class="render-result-close" id="rrClose">✕</button>
                        </div>
                    </div>
                    <img class="render-result-img" id="rrImg" src="" alt="render">
                </div>`;
            document.body.appendChild(pop);
            document.getElementById('rrClose').onclick = () => pop.classList.remove('rr-visible');
            document.getElementById('rrApply').onclick = () => {
                const img = new Image();
                img.onload = () => { appBackgroundImage = img; draw(); };
                img.src = document.getElementById('rrImg').src;
                pop.classList.remove('rr-visible');
            };
        }
        document.getElementById('rrImg').src = src;
        requestAnimationFrame(() => pop.classList.add('rr-visible'));
    }

    function resizeCanvas() {
        const dpr = window.devicePixelRatio || 1;
        const w = container.clientWidth;
        const h = container.clientHeight;
        logW = w;
        logH = h;
        const pw = Math.round(w * dpr);
        const ph = Math.round(h * dpr);
        if (canvas.width === pw && canvas.height === ph) return;
        canvas.width  = pw;
        canvas.height = ph;
        canvas.style.width  = w + 'px';
        canvas.style.height = h + 'px';
        draw();
    }

    let _resizeTimer;
    function resizeCanvasDebounced() {
        cancelAnimationFrame(_resizeTimer);
        _resizeTimer = requestAnimationFrame(resizeCanvas);
    }

    // ── 다중 썸네일 관리 ─────────────────────────
    const rightSidebar       = document.getElementById('rightSidebar');
    const btnRightSidebarTab = document.getElementById('btnRightSidebarTab');
    const thumbList          = document.getElementById('thumbList');
    const btnAddThumb        = document.getElementById('btnAddThumb');

    function animatePanelResize() {
        const duration = 270;
        const start = performance.now();
        function step(now) {
            resizeCanvas();
            if (now - start < duration) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    function showRightSidebar() {
        rightSidebar.classList.remove('collapsed');
        btnRightSidebarTab.classList.remove('collapsed');
        animatePanelResize();
    }
    function hideRightSidebar() {
        rightSidebar.classList.add('collapsed');
        btnRightSidebarTab.classList.add('collapsed');
        animatePanelResize();
    }

    btnRightSidebarTab.addEventListener('click', () => {
        if (rightSidebar.classList.contains('collapsed')) showRightSidebar();
        else hideRightSidebar();
    });

    // 이미지 목록: [{id, src, img}]
    let thumbImages   = [];
    let activeThumbId = null;

    const btnClearBg = document.getElementById('btnClearBg');

    function updateClearBgBtn() {
        btnClearBg.style.display = appBackgroundImage ? 'flex' : 'none';
    }

    btnClearBg.addEventListener('click', () => {
        appBackgroundImage = null;
        localStorage.removeItem(BG_IMAGE_KEY);
        thumbList.querySelectorAll('.rp-thumb-item').forEach(el => el.classList.remove('active'));
        activeThumbId = null;
        updateClearBgBtn();
        draw();
    });

    function setActiveThumb(id) {
        activeThumbId = id;
        const found = thumbImages.find(t => t.id === id);
        appBackgroundImage = found ? found.img : null;

        try {
            if (found) localStorage.setItem(BG_IMAGE_KEY, found.src);
            else        localStorage.removeItem(BG_IMAGE_KEY);
        } catch(e) {}

        thumbList.querySelectorAll('.rp-thumb-item').forEach(el => {
            el.classList.toggle('active', el.dataset.id === String(id));
        });
        updateClearBgBtn();
        draw();
    }

    function addThumbItem(id, src, filename) {
        const item = document.createElement('div');
        item.className = 'rp-thumb-item';
        item.dataset.id = id;

        const img = document.createElement('img');
        img.src = src;
        img.alt = filename;

        const btn = document.createElement('button');
        btn.className = 'rp-remove';
        btn.title = '제거';
        btn.textContent = '✕';
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            thumbImages = thumbImages.filter(t => t.id !== id);
            item.remove();
            saveThumbsToStorage();
            if (activeThumbId === id) {
                if (thumbImages.length > 0) {
                    setActiveThumb(thumbImages[thumbImages.length - 1].id);
                } else {
                    appBackgroundImage = null;
                    activeThumbId = null;
                    localStorage.removeItem(BG_IMAGE_KEY);
                    hideRightSidebar();
                    draw();
                }
            }
        });

        item.appendChild(img);
        item.appendChild(btn);
        item.addEventListener('click', () => setActiveThumb(id));
        thumbList.appendChild(item);
    }

    function compressImage(src, callback) {
        const img = new Image();
        img.onload = function() {
            const MAX = 1600;
            let w = img.width, h = img.height;
            if (w > MAX || h > MAX) {
                const s = Math.min(MAX / w, MAX / h);
                w = Math.round(w * s);
                h = Math.round(h * s);
            }
            const c = document.createElement('canvas');
            c.width = w; c.height = h;
            c.getContext('2d').drawImage(img, 0, 0, w, h);
            callback(c.toDataURL('image/jpeg', 0.82));
        };
        img.src = src;
    }

    function saveThumbsToStorage() {
        try {
            localStorage.setItem(THUMBS_KEY, JSON.stringify(
                thumbImages.map(t => ({ src: t.src, filename: t.filename || '' }))
            ));
        } catch(e) {
            console.warn('썸네일 저장 실패 (용량 초과)');
        }
    }

    btnAddThumb.addEventListener('click', () => aiFileUploader.click());

    aiFileUploader.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = function(event) {
                compressImage(event.target.result, function(src) {
                    const id = Date.now() + Math.random();
                    const imgObj = new Image();
                    imgObj.src = src;
                    imgObj.onload = function() {
                        thumbImages.push({ id, src, img: imgObj, filename: file.name });
                        addThumbItem(id, src, file.name);
                        saveThumbsToStorage();
                        showRightSidebar();
                        setActiveThumb(id);
                    };
                });
            };
            reader.readAsDataURL(file);
        });
        aiFileUploader.value = '';
    });



// ── 라인 편집 ──────────────────────────────────
let deletedSegs = new Set();  // 삭제된 세그먼트 key (쌍으로 관리)
let addedLines   = [];
let lineEditMode = null;
let addLineStart = null;

let lastSegMap   = new Map();   // segKey → {mx,my,normAngle,cx,cy}
let lastNodeList = [];
let lastILeft=0, lastITop=0, lastIW=1, lastIH=1, lastSlatPx=1, lastCellSize=1;

// 캔버스 좌표 → 내경 mm 좌표 변환
function canvasToInnerMm(cx, cy) {
    if (!lastIW || !lastIH) return { nx: 0, ny: 0 };
    return {
        nx: (cx - lastILeft) * (geo.innerW || 1) / lastIW,
        ny: (cy - lastITop)  * (geo.innerH || 1) / lastIH,
    };
}

// 30° 사선살 (rotateOn=true) 끝점 계산 (inner mm)
function getSlatEndpoints30(b, iW, iH) {
    const S3 = Math.sqrt(3), EPS = 0.5;
    const x1 = b < -EPS ? -b * S3 : 0;
    const y1 = b < -EPS ? 0 : Math.max(0, b);
    const yR = iW / S3 + b;
    const x2 = yR < iH - EPS ? iW : (iH - b) * S3;
    const y2 = yR < iH - EPS ? yR : iH;
    if (x1 > iW + EPS || x2 < -EPS || x1 > x2 + EPS) return null;
    return { x1, y1, x2, y2 };
}

// 60° 사선살 (rotateOn=false) 끝점 계산 (inner mm)
function getSlatEndpoints60(b, iW, iH) {
    const S3 = Math.sqrt(3), EPS = 0.5;
    const x1 = b < -EPS ? -b / S3 : 0;
    const y1 = b < -EPS ? 0 : Math.max(0, b);
    const yR = iW * S3 + b;
    const x2 = yR < iH - EPS ? iW : (iH - b) / S3;
    const y2 = yR < iH - EPS ? yR : iH;
    if (x1 > iW + EPS || x2 < -EPS || x1 > x2 + EPS) return null;
    return { x1, y1, x2, y2 };
}

function makeLineKey(cx, cy, normAngle) {
    const perp = cx * Math.sin(normAngle) - cy * Math.cos(normAngle);
    return `${Math.round(normAngle * 1000)}:${Math.round(perp)}`;
}

// 삭제된 선의 내경 mm 길이 계산
function getSlatMmLength(mm, normAngle, iW, iH) {
    const S3 = Math.sqrt(3), EPS = 0.1;
    if (normAngle < EPS) return iW;                          // horizontal (rotateOn=false 직선)
    if (Math.abs(normAngle - Math.PI / 2) < EPS) return iH; // vertical   (rotateOn=true  직선)
    if (Math.abs(normAngle - Math.PI / 6) < EPS) {          // slope  1/√3 (30°)
        const b  = mm.ny - mm.nx / S3;
        const ep = getSlatEndpoints30(b, iW, iH);
        return ep ? Math.round(Math.hypot(ep.x2 - ep.x1, ep.y2 - ep.y1)) : 0;
    }
    if (Math.abs(normAngle - 5 * Math.PI / 6) < EPS) {      // slope -1/√3 (150°) → x 반전 후 30° 공식
        const b  = (mm.ny + mm.nx / S3) - iW / S3;
        const ep = getSlatEndpoints30(b, iW, iH);
        return ep ? Math.round(Math.hypot(ep.x2 - ep.x1, ep.y2 - ep.y1)) : 0;
    }
    if (Math.abs(normAngle - Math.PI / 3) < EPS) {          // slope  √3  (60°)
        const b  = mm.ny - mm.nx * S3;
        const ep = getSlatEndpoints60(b, iW, iH);
        return ep ? Math.round(Math.hypot(ep.x2 - ep.x1, ep.y2 - ep.y1)) : 0;
    }
    if (Math.abs(normAngle - 2 * Math.PI / 3) < EPS) {      // slope -√3  (120°) → x 반전 후 60° 공식
        const b  = (mm.ny + mm.nx * S3) - iW * S3;
        const ep = getSlatEndpoints60(b, iW, iH);
        return ep ? Math.round(Math.hypot(ep.x2 - ep.x1, ep.y2 - ep.y1)) : 0;
    }
    return 0;
}


function screenToCtxCoord(clientX, clientY) {
    const rect = canvas.getBoundingClientRect();
    return {
        x: (clientX - rect.left - logW / 2 - panX) / scaleFactor,
        y: (clientY - rect.top  - logH / 2 - panY) / scaleFactor,
    };
}
function ctxToNorm(cx, cy) {
    return { nx: (cx - lastILeft) / lastIW, ny: (cy - lastITop) / lastIH };
}
function normToCtx(nx, ny) {
    return { x: lastILeft + nx * lastIW, y: lastITop + ny * lastIH };
}

let _geoController = null;

async function fetchGeometry() {
    if (_geoController) _geoController.abort();
    _geoController = new AbortController();
    const body = new URLSearchParams({
        cols:      txtCols.value,
        outerW:    txtW.value,
        outerH:    txtH.value,
        pungpanH:  document.getElementById('txtPungpan').value || 0,
        pungpanOn: document.getElementById('chkPungpan').checked ? '1' : '0',
        frameW:    txtFrame.value,
        frameH:    txtFrameH.value,
        slatT:     txtSlat.value,
        rotateOn:  rotateOn ? '1' : '0',
        doorType:  txtDoorType.value,
        doorCount: txtDoorCount.value,
    });
    try {
        const res = await fetch('api/geometry.php', {
            method: 'POST',
            body,
            signal: _geoController.signal,
        });
        return res.json();
    } catch (e) {
        if (e.name === 'AbortError') return null;
        throw e;
    }
}

async function draw() {
    const data = await fetchGeometry();
    if (!data) return;
    geo = data.geo;

    const s = data.specs;
    document.getElementById('spOuterW').innerText     = s.outerW;
    document.getElementById('spOuterH').innerText     = s.outerH;
    document.getElementById('spInnerW').innerText     = s.innerW;
    document.getElementById('spInnerH').innerText     = s.innerH;
    document.getElementById('spCounts').innerText     = s.cols;
    document.getElementById('spRows').innerText       = s.rows;
    document.getElementById('spStep').innerText       = s.step;
    document.getElementById('spPungpan').innerText    = s.pungpan;
    document.getElementById('spEye').innerText        = s.eye;
    document.getElementById('spDiagEye').innerText    = s.diagEye;
    document.getElementById('spFrameHTop').innerText  = s.frameHTop;
    document.getElementById('spTotalDoorW').innerText = s.totalDoorW;

    const overlapCard = document.getElementById('spOverlapCard');
    if (overlapCard) {
        const isSlide = document.getElementById('txtDoorType').value === 'slide';
        overlapCard.style.display = isSlide ? '' : 'none';
        document.getElementById('spOverlap').innerText = s.overlap ?? '';
    }

    // 세로 자동 맞춤: 마지막 격자 행이 하부 울거미에 닿도록 outerH 조정
    const chkShrinkH = document.getElementById('chkShrinkH');
    if (chkShrinkH?.checked) {
        const pungpanOn = document.getElementById('chkPungpan').checked;
        const pungpanInput = parseInt(document.getElementById('txtPungpan').value) || 0;
        const effectivePungpan = pungpanOn ? pungpanInput : 0;
        const rawTarget = Math.round(geo.frameH * 2 + geo.innerH) + effectivePungpan;
        // 유효한 값이고 슬라이더 범위 안에 있을 때만 조정
        if (Number.isFinite(rawTarget) && rawTarget >= 400 && rawTarget <= 2600) {
            const currentH = parseInt(txtH.value);
            if (rawTarget !== currentH) {
                setSlider('txtH', 'numH', rawTarget);
                draw();
                return;
            }
        }
    }

    const p = data.parts;
    document.getElementById('spFrVLen').textContent = p.frVLen;
    document.getElementById('spFrVCnt').textContent = p.frVCnt;
    document.getElementById('spFrHLen').textContent = p.frHLen;
    document.getElementById('spFrHCnt').textContent = p.frHCnt;

    const ppGroup = document.getElementById('pungpanMaterialGroup');
    if (p.pungpanVisible) {
        ppGroup.style.display = '';
        document.getElementById('spPpHLen').textContent = p.ppHLen;
        document.getElementById('spPpVLen').textContent = p.ppVLen;
        document.querySelector('#pungpanMaterialGroup .slat-count-badge').textContent = p.pungpanCnt;
    } else {
        ppGroup.style.display = 'none';
    }

    document.getElementById('dirSlatGroupTitle').textContent = p.dirTitle;
    document.getElementById('spHSlatLen').textContent = p.hSlatLen;
    document.getElementById('spHSlatCnt').textContent = p.hSlatCnt;

    const diagListEl = document.getElementById('spDiagList');
    diagListEl.innerHTML = '';
    p.diagList.forEach(({ len, cnt }) => {
        const el = document.createElement('div');
        el.className = 'slat-row';
        el.innerHTML = `<span class="slat-len">${len}<span class="slat-len-unit">mm</span></span><span class="slat-cnt">${cnt}개</span>`;
        diagListEl.appendChild(el);
    });

    // draw() 안에서 ctx를 재할당 가능하도록 로컬 변수로 섀도잉
    let ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;

    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, logW, logH);

    ctx.save();

    ctx.translate(
        logW / 2 + panX,
        logH / 2 + panY
    );

    ctx.scale(scaleFactor, scaleFactor);

    // 배경 이미지 (pan/zoom 좌표계 안에서 그리기)
    if (appBackgroundImage) {
        const img = appBackgroundImage;
        const s = Math.min(canvas.width / img.width, canvas.height / img.height);
        const dW = img.width * s, dH = img.height * s;
        ctx.drawImage(img, -dW / 2, -dH / 2, dW, dH);
    }



    const basePadding = 60;

    const doorType = txtDoorType.value;
    const doorCount = parseInt(txtDoorCount.value);
    const gap = 2;
    const overlap = geo.frameW;

    // 전체 가로폭(짝수 포함) 기준으로 baseScale 계산
    let totalWidth;
    if (doorType === 'slide') {
        if      (doorCount === 1) totalWidth = geo.outerW;
        else if (doorCount === 2) totalWidth = geo.outerW * 2 - overlap;
        else if (doorCount === 3) totalWidth = geo.outerW * 3 - overlap * 2;
        else                      totalWidth = geo.outerW * 4 - overlap * 2;
    } else {
        totalWidth = (geo.outerW * doorCount) + (gap * (doorCount - 1));
    }

    const pungpanH = parseInt(document.getElementById('txtPungpan').value) || 0;
    const totalH   = geo.outerH;  // 외경 고정

    const baseScale = Math.min(
        (logW - basePadding * 2) / totalWidth,
        (logH - basePadding * 2) / totalH
    );

    const renderOrder = [...Array(doorCount).keys()];

    const offsetX =
        -(totalWidth * baseScale) / 2;

    const offsetY =
        -(totalH * baseScale) / 2;

    doorNaturalSize = { w: totalWidth * baseScale, h: totalH * baseScale };

    // 배치 모드: 도면 크기가 바뀌면 자동 취소
    if (placementMode && placementNaturalSize &&
        (Math.abs(doorNaturalSize.w - placementNaturalSize.w) > 1 ||
         Math.abs(doorNaturalSize.h - placementNaturalSize.h) > 1)) {
        placementMode = false;
        doorCornerPositions = null;
        canvas.style.cursor = panMode ? 'grab' : 'default';
        document.getElementById('btnScale').classList.remove('cv-btn-active');
    }

    // 배치 모드: 오프스크린 캔버스로 리다이렉트
    if (placementMode && doorCornerPositions) {
        const W = Math.max(1, Math.ceil(doorNaturalSize.w));
        const H = Math.max(1, Math.ceil(doorNaturalSize.h));
        if (offCanvas.width !== W || offCanvas.height !== H) {
            offCanvas.width = W; offCanvas.height = H;
        }
        ctx.restore();
        const offCtx = offCanvas.getContext('2d');
        offCtx.clearRect(0, 0, W, H);
        offCtx.save();
        offCtx.translate(W / 2, H / 2);
        ctx = offCtx;
    }

    // 패턴 비율 체크: 셀 크기가 0 이하이거나 내경이 너무 작아 패턴 불가능할 때 레드
    const patternBroken = geo.cellW <= 0 || geo.cellH <= 0 || geo.innerW <= 0 || geo.innerH <= 0;

    const Color_Slat_Fill  = patternBroken ? '#cc0000' : selectedSlatColor;
    const Color_Tenon_Fill = patternBroken ? '#cc0000' : selectedSlatColor;

    // ====== 내경 배경 (살 내부 화이트) ======
    for (const d of renderOrder) {
        let pOffX = 0;
        if (doorType === 'swing') {
            pOffX = d * (geo.outerW + gap);
        } else if (doorType === 'slide') {
            if      (doorCount === 2) pOffX = d === 0 ? 0 : geo.outerW - geo.frameW;
            else if (doorCount === 3) pOffX = d === 0 ? 0 : d === 1 ? geo.outerW - geo.frameW : (geo.outerW * 2) - (geo.frameW * 2);
            else if (doorCount === 4) pOffX = d === 0 ? 0 : d === 1 ? geo.outerW - geo.frameW : d === 2 ? (geo.outerW * 2) - geo.frameW : (geo.outerW * 3) - (geo.frameW * 2);
        }
        const tX = rx => offsetX + (pOffX + rx) * baseScale;
        const tY = ry => offsetY + ry * baseScale;
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(tX(geo.frameW), tY(geo.frameHTop), geo.innerW * baseScale, geo.innerH * baseScale);
    }

    // ====== 1차 루프: 패턴만 그리기 (세로살, 가로살, 사분턱) ======
    lastSegMap.clear();
    lastNodeList = [];
    for (const d of renderOrder) {

        let panelOffsetX = 0;

        // ====================================
        // 문 위치 계산
        // ====================================

        if (doorType === 'swing') {

            panelOffsetX =
                d * (geo.outerW + gap);

        } else if (doorType === 'slide') {

            if (doorCount === 1) {

                panelOffsetX = 0;
            }

            else if (doorCount === 2) {

                if (d === 0)
                    panelOffsetX = 0;

                if (d === 1)
                    panelOffsetX =
                        geo.outerW - overlap;
            }

            else if (doorCount === 3) {

                if (d === 0) {
                    panelOffsetX = 0;
                }

                if (d === 1) {
                    panelOffsetX =
                        geo.outerW - overlap;
                }

                if (d === 2) {
                    panelOffsetX =
                        (geo.outerW * 2) -
                        (overlap * 2);
                }
            }

            else if (doorCount === 4) {

                if (d === 0) {
                    panelOffsetX = 0;
                }

                if (d === 1) {
                    panelOffsetX =
                        geo.outerW - overlap;
                }

                if (d === 2) {
                    panelOffsetX =
                        (geo.outerW * 2) - overlap;
                }

                if (d === 3) {
                    panelOffsetX =
                        (geo.outerW * 3) -
                        (overlap * 2);
                }
            }
        }

        const toCanvasX = (realX) =>
            offsetX +
            (panelOffsetX + realX) * baseScale;

        const toCanvasY = (realY) =>
            offsetY +
            realY * baseScale;

        // ====================================
        // 세로살
        // ====================================

        // ====================================
        // 삼분턱 패턴
        // ====================================

        const clipH      = geo.innerH;
        const slatPxHalf = geo.slatT * baseScale / 2;
        const iLeft      = toCanvasX(geo.frameW);
        const iTop       = toCanvasY(geo.frameHTop);
        const iW         = geo.innerW * baseScale;
        const iH         = clipH * baseScale;
        const slatPx     = geo.slatT * baseScale;

        if (d === renderOrder[0]) {
            lastILeft = iLeft; lastITop = iTop; lastIW = iW; lastIH = iH; lastSlatPx = slatPx;
        }

        ctx.strokeStyle = patternBroken ? '#cc0000' : selectedSlatColor;
        ctx.lineWidth   = slatPx;
        ctx.lineCap     = 'round';

        if (!rotateOn) {
            // ── 가로 방향 (기본) ──────────────────────────
            // clip: 상하 slatT/2 연장
            ctx.save();
            ctx.beginPath();
            ctx.rect(iLeft, iTop - slatPxHalf, iW, iH + slatPxHalf * 2);
            ctx.clip();

            const size    = iW / (geo.cols * Math.sqrt(3));
            const width   = size * Math.sqrt(3);
            const rowStep = size * 1.5;
            const startY  = iTop - slatPx / 2;

            lastCellSize = size;
            for (let y = startY - rowStep, rIdx = 0; y < iTop + iH + rowStep; y += rowStep, rIdx++) {
                for (let x = iLeft - width, cIdx = 0; x < iLeft + iW + width; x += width, cIdx++) {
                    const offX = (rIdx % 2 === 0) ? width / 2 : 0;
                    const cx = x + offX, cy = y;
                    for (let i = 0; i < 6; i++) {
                        const angle = i * Math.PI / 3;
                        const ex = cx + size * Math.cos(angle);
                        const ey = cy + size * Math.sin(angle);
                        const segKey = `${d}:h:${rIdx}:${cIdx}:${i}`;
                        const normAngle = ((angle % Math.PI) + Math.PI) % Math.PI;
                        const lineKey  = makeLineKey(cx, cy, normAngle);
                        lastSegMap.set(segKey, { cx, cy, ex, ey, mx: (cx + ex) / 2, my: (cy + ey) / 2, normAngle, lineKey });
                        if (i === 0) lastNodeList.push({ cx, cy });
                        if (deletedSegs.has(segKey)) continue;
                        ctx.beginPath();
                        ctx.moveTo(cx, cy);
                        ctx.lineTo(ex, ey);
                        ctx.stroke();
                    }
                }
            }
            ctx.restore();

        } else {
            // ── 세로 방향 (90° 회전) ──────────────────────
            // size = iW 기준 (가로 칸수 동일 적용)
            // clip: 상하 slatT/2(세로 정렬) + 좌우 slatT/2(수평 경계)
            ctx.save();
            ctx.beginPath();
            // 좌우: slatPx/2 반살 / 상하: 클립으로 정확히 크롭
            ctx.rect(iLeft - slatPxHalf, iTop, iW + slatPxHalf * 2, iH);
            ctx.clip();

            const size    = (iW + slatPx) / (geo.cols * 1.5);
            const width   = size * Math.sqrt(3);
            const colStep = size * 1.5;
            const startX  = iLeft - slatPx / 2;

            lastCellSize = size;
            for (let x = startX - colStep, cIdx = 0; x < iLeft + iW + colStep; x += colStep, cIdx++) {
                for (let y = iTop - width, rowIdx = 0; y < iTop + iH + width; y += width, rowIdx++) {
                    const offY = (cIdx % 2 === 0) ? width / 2 : 0;
                    const cx = x, cy = y + offY;
                    for (let i = 0; i < 6; i++) {
                        const angle = i * Math.PI / 3 + Math.PI / 2;
                        const ex = cx + size * Math.cos(angle);
                        const ey = cy + size * Math.sin(angle);
                        const segKey = `${d}:v:${cIdx}:${rowIdx}:${i}`;
                        const normAngle = ((angle % Math.PI) + Math.PI) % Math.PI;
                        const lineKey  = makeLineKey(cx, cy, normAngle);
                        lastSegMap.set(segKey, { cx, cy, ex, ey, mx: (cx + ex) / 2, my: (cy + ey) / 2, normAngle, lineKey });
                        if (i === 0) lastNodeList.push({ cx, cy });
                        if (deletedSegs.has(segKey)) continue;
                        ctx.beginPath();
                        ctx.moveTo(cx, cy);
                        ctx.lineTo(ex, ey);
                        ctx.stroke();
                    }
                }
            }
            ctx.restore();
        }

    }   // ← 1차 루프 끝

    // ── 편집 반영 부재 목록 최종 업데이트 ──────
    {
        const tenonLen = 2 * geo.slatT;
        const pxToMm  = geo.innerW / lastIW;

        // lineKey별 세그먼트 그룹화 (내경 범위 밖 세그먼트 제외)
        const lineGroups = new Map();
        const EPS = lastCellSize;
        for (const [segKey, seg] of lastSegMap) {
            if (segKey.startsWith('added:')) continue;
            if (seg.mx < lastILeft - EPS || seg.mx > lastILeft + lastIW + EPS) continue;
            if (seg.my < lastITop - EPS || seg.my > lastITop + lastIH + EPS) continue;
            if (!lineGroups.has(seg.lineKey)) lineGroups.set(seg.lineKey, []);
            lineGroups.get(seg.lineKey).push({ segKey, mx: seg.mx, my: seg.my, normAngle: seg.normAngle });
        }

        let adjStraightCnt = parseInt(p.hSlatCnt) || 0;
        let adjDiag        = p.diagList.map(item => ({ ...item }));
        const extraPieces  = [];

        for (const [, segs] of lineGroups) {
            const normAngle  = segs[0].normAngle;
            const isStraight = rotateOn
                ? Math.abs(normAngle - Math.PI / 2) < 0.05
                : normAngle < 0.05;
            const ldx = Math.cos(normAngle), ldy = Math.sin(normAngle);

            // 쌍 중복 제거
            const visited = new Set();
            const vSegs = [];
            for (const s of segs) {
                if (visited.has(s.segKey)) continue;
                let partnerKey = null;
                for (const t of segs) {
                    if (t.segKey === s.segKey || visited.has(t.segKey)) continue;
                    if (Math.hypot(t.mx - s.mx, t.my - s.my) < lastCellSize * 0.9) {
                        partnerKey = t.segKey; break;
                    }
                }
                visited.add(s.segKey);
                if (partnerKey) visited.add(partnerKey);
                const isDeleted = deletedSegs.has(s.segKey) || (partnerKey && deletedSegs.has(partnerKey));
                vSegs.push({ isDeleted, pos: s.mx * ldx + s.my * ldy, segKey: s.segKey });
            }

            if (!vSegs.some(vs => vs.isDeleted)) continue;

            vSegs.sort((a, b) => a.pos - b.pos);

            // [i0, i1] 구간의 실제 끝점 간 mm 길이
            const runLen = (i0, i1) => {
                let minP = Infinity, maxP = -Infinity, minN = null, maxN = null;
                for (let i = i0; i <= i1; i++) {
                    const sg = lastSegMap.get(vSegs[i].segKey);
                    if (!sg) continue;
                    for (const [nx, ny] of [[sg.cx, sg.cy], [sg.ex, sg.ey]]) {
                        const proj = nx * ldx + ny * ldy;
                        if (proj < minP) { minP = proj; minN = [nx, ny]; }
                        if (proj > maxP) { maxP = proj; maxN = [nx, ny]; }
                    }
                }
                if (!minN || !maxN) return 0;
                return Math.round(Math.hypot(maxN[0] - minN[0], maxN[1] - minN[1]) * pxToMm + tenonLen);
            };

            // 원본 부재 1개 제거
            const fullLen = runLen(0, vSegs.length - 1);
            if (isStraight) {
                adjStraightCnt = Math.max(0, adjStraightCnt - 1);
            } else {
                const m = adjDiag.find(it => it.cnt > 0 && Math.abs(it.len - fullLen) <= tenonLen + 1);
                if (m) m.cnt = Math.max(0, m.cnt - 1);
            }

            // 남은 연속 구간 → 실제 끝점 길이로 추가
            let runStart = -1;
            for (let i = 0; i <= vSegs.length; i++) {
                if (i < vSegs.length && !vSegs[i].isDeleted) {
                    if (runStart < 0) runStart = i;
                } else if (runStart >= 0) {
                    extraPieces.push({ len: runLen(runStart, i - 1), isStraight });
                    runStart = -1;
                }
            }
        }

        // 분할 조각 병합 (사선살 목록에 표시)
        extraPieces.forEach(({ len }) => {
            const m = adjDiag.find(it => Math.abs(it.len - len) <= tenonLen + 1);
            if (m) m.cnt++;
            else adjDiag.push({ len, cnt: 1 });
        });

        // 세로/가로부재 업데이트
        document.getElementById('spHSlatCnt').textContent =
            Math.max(0, adjStraightCnt) + '개';

        adjDiag = adjDiag.filter(item => item.cnt > 0);

        // 추가 선 → 길이 계산 후 병합
        addedLines.forEach(ln => {
            const dx = (ln.nx2 - ln.nx1) * geo.innerW;
            const dy = (ln.ny2 - ln.ny1) * geo.innerH;
            const realLen = Math.round(Math.hypot(dx, dy) + tenonLen);
            const m = adjDiag.find(it => Math.abs(it.len - realLen) <= tenonLen);
            if (m) m.cnt++;
            else adjDiag.push({ len: realLen, cnt: 1 });
        });
        adjDiag.sort((a, b) => b.len - a.len);

        diagListEl.innerHTML = '';
        adjDiag.forEach(({ len, cnt }) => {
            const el = document.createElement('div');
            el.className = 'slat-row';
            el.innerHTML = `<span class="slat-len">${len}<span class="slat-len-unit">mm</span></span><span class="slat-cnt">${cnt}개</span>`;
            diagListEl.appendChild(el);
        });
    }

    // ====== 추가 선 그리기 ======
    if (addedLines.length > 0) {
        ctx.save();
        ctx.strokeStyle = patternBroken ? '#cc0000' : selectedSlatColor;
        ctx.lineWidth   = lastSlatPx;
        ctx.lineCap     = 'round';
        ctx.setLineDash([]);
        addedLines.forEach((ln, idx) => {
            const p1 = normToCtx(ln.nx1, ln.ny1);
            const p2 = normToCtx(ln.nx2, ln.ny2);
            lastSegMap.set(`added:${idx}`, { mx: (p1.x + p2.x) / 2, my: (p1.y + p2.y) / 2, normAngle: 0 });
            ctx.beginPath();
            ctx.moveTo(p1.x, p1.y);
            ctx.lineTo(p2.x, p2.y);
            ctx.stroke();
        });
        ctx.restore();
    }

    // 선 추가 모드 — 시작점 표시
    if (lineEditMode === 'add' && addLineStart) {
        const p = normToCtx(addLineStart.nx, addLineStart.ny);
        ctx.save();
        ctx.fillStyle = '#3A8C82';
        ctx.beginPath();
        ctx.arc(p.x, p.y, lastSlatPx * 1.5, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();
    }

    // ====== 2차 루프: 울거미만 그리기 (패턴 위에 덮음) ======
    for (const d of renderOrder) {

        let panelOffsetX = 0;

        if (doorType === 'swing') {
            panelOffsetX = d * (geo.outerW + gap);
        } else if (doorType === 'slide') {
            if      (doorCount === 1) panelOffsetX = 0;
            else if (doorCount === 2) panelOffsetX = d === 0 ? 0 : geo.outerW - overlap;
            else if (doorCount === 3) panelOffsetX = d === 0 ? 0 : d === 1 ? geo.outerW - overlap : (geo.outerW * 2) - (overlap * 2);
            else if (doorCount === 4) panelOffsetX = d === 0 ? 0 : d === 1 ? geo.outerW - overlap : d === 2 ? (geo.outerW * 2) - overlap : (geo.outerW * 3) - (overlap * 2);
        }

        const toCanvasX = (realX) => offsetX + (panelOffsetX + realX) * baseScale;
        const toCanvasY = (realY) => offsetY + realY * baseScale;

        // 창호 실제 높이
        const actualH = geo.actualPatternH;

        ctx.fillStyle = selectedFrameColor;

        // 좌측 세로 울거미
        ctx.fillRect(toCanvasX(0), toCanvasY(0), geo.frameW * baseScale, geo.outerH * baseScale);

        // 상부 가로 울거미
        ctx.fillRect(toCanvasX(geo.frameW), toCanvasY(0), geo.innerW * baseScale, geo.frameHTop * baseScale);

        // 하단 울거미
        ctx.fillRect(toCanvasX(geo.frameW), toCanvasY(geo.frameHTop + geo.innerH), geo.innerW * baseScale, geo.frameHBottom * baseScale);

        // 우측 세로 울거미
        ctx.fillRect(toCanvasX(geo.outerW - geo.frameW), toCanvasY(0), geo.frameW * baseScale, geo.outerH * baseScale);

    }

    // ====================================
    // 풍판 (하단 판재 + 울거미)
    // ====================================
    if (geo.effectivePungpanH > 0) {

        const pungpanY = geo.actualPatternH;  // 패턴 끝에 딱 붙음
        const pungpanDrawH = geo.actualPungpanH;

        for (const d of renderOrder) {

            let ppOffsetX = 0;

            if (doorType === 'swing') {
                ppOffsetX = d * (geo.outerW + gap);
            } else if (doorType === 'slide') {
                if      (doorCount === 1) ppOffsetX = 0;
                else if (doorCount === 2) ppOffsetX = d === 0 ? 0 : geo.outerW - overlap;
                else if (doorCount === 3) ppOffsetX = d === 0 ? 0 : d === 1 ? geo.outerW - overlap : (geo.outerW * 2) - (overlap * 2);
                else if (doorCount === 4) ppOffsetX = d === 0 ? 0 : d === 1 ? geo.outerW - overlap : d === 2 ? (geo.outerW * 2) - overlap : (geo.outerW * 3) - (overlap * 2);
            }

            const toCX = (rx) => offsetX + (ppOffsetX + rx) * baseScale;
            const toCY = (ry) => offsetY + ry * baseScale;

            // 풍판 내부 판재 (울거미 안쪽)
            const ppInnerX = geo.frameW;
            const ppInnerW = geo.innerW;
            const ppInnerH = pungpanDrawH - geo.frameH;

            ctx.fillStyle = lightenHex(selectedFrameColor, 40);
            ctx.fillRect(
                toCX(ppInnerX),
                toCY(pungpanY),
                ppInnerW * baseScale,
                ppInnerH * baseScale
            );

            ctx.fillStyle = selectedFrameColor;

            // 좌측 세로 울거미 (풍판 전체 높이)
            ctx.fillRect(
                toCX(0),
                toCY(pungpanY),
                geo.frameW * baseScale,
                pungpanDrawH * baseScale
            );

            // 우측 세로 울거미 (풍판 전체 높이)
            ctx.fillRect(
                toCX(geo.outerW - geo.frameW),
                toCY(pungpanY),
                geo.frameW * baseScale,
                pungpanDrawH * baseScale
            );

            // 하단 가로 울거미
            ctx.fillRect(
                toCX(geo.frameW),
                toCY(pungpanY + pungpanDrawH - geo.frameH),
                geo.innerW * baseScale,
                geo.frameH * baseScale
            );
        }
    }

    if (placementMode && doorCornerPositions) {
        ctx.restore();
        ctx = canvas.getContext('2d');
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        const _c = getOverlayCorners();
        drawPerspectiveQuad(ctx, offCanvas, _c.tl, _c.tr, _c.br, _c.bl);
    } else {
        ctx.restore();
    }

    if (placementMode && doorNaturalSize.w > 0) {
        const c = getOverlayCorners();
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        ctx.strokeStyle = handlesVisible ? 'rgba(58,140,130,0.9)' : 'rgba(58,140,130,0.35)';
        ctx.lineWidth   = handlesVisible ? 2 : 1.5;
        ctx.setLineDash([6, 4]);
        ctx.beginPath();
        ctx.moveTo(c.tl.x, c.tl.y); ctx.lineTo(c.tr.x, c.tr.y);
        ctx.lineTo(c.br.x, c.br.y); ctx.lineTo(c.bl.x, c.bl.y);
        ctx.closePath(); ctx.stroke();
        ctx.setLineDash([]);

        if (handlesVisible) {
            [c.tl, c.tr, c.br, c.bl].forEach(({ x, y }) => {
                ctx.fillStyle   = '#fff';
                ctx.strokeStyle = '#3A8C82';
                ctx.lineWidth   = 2.5;
                ctx.fillRect(x - 7, y - 7, 14, 14);
                ctx.strokeRect(x - 7, y - 7, 14, 14);
            });

            const { x: cx, y: cy } = c.center;
            ctx.fillStyle   = 'rgba(255,255,255,0.92)';
            ctx.strokeStyle = '#3A8C82';
            ctx.lineWidth   = 2;
            ctx.beginPath(); ctx.arc(cx, cy, 14, 0, Math.PI * 2); ctx.fill(); ctx.stroke();

            ctx.strokeStyle = '#3A8C82'; ctx.lineWidth = 1.8; ctx.lineCap = 'round';
            const a = 8;
            ctx.beginPath(); ctx.moveTo(cx, cy - 3); ctx.lineTo(cx, cy - a);
            ctx.lineTo(cx - 3, cy - a + 3); ctx.moveTo(cx, cy - a); ctx.lineTo(cx + 3, cy - a + 3); ctx.stroke();
            ctx.beginPath(); ctx.moveTo(cx, cy + 3); ctx.lineTo(cx, cy + a);
            ctx.lineTo(cx - 3, cy + a - 3); ctx.moveTo(cx, cy + a); ctx.lineTo(cx + 3, cy + a - 3); ctx.stroke();
            ctx.beginPath(); ctx.moveTo(cx - 3, cy); ctx.lineTo(cx - a, cy);
            ctx.lineTo(cx - a + 3, cy - 3); ctx.moveTo(cx - a, cy); ctx.lineTo(cx - a + 3, cy + 3); ctx.stroke();
            ctx.beginPath(); ctx.moveTo(cx + 3, cy); ctx.lineTo(cx + a, cy);
            ctx.lineTo(cx + a - 3, cy - 3); ctx.moveTo(cx + a, cy); ctx.lineTo(cx + a - 3, cy + 3); ctx.stroke();

            const th = getTransformHandlePos();
            if (th) {
                ctx.setLineDash([]);
                ctx.fillStyle = '#fff'; ctx.strokeStyle = '#3A8C82'; ctx.lineWidth = 2;
                ctx.beginPath(); ctx.arc(th.x, th.y, 10, 0, Math.PI * 2); ctx.fill(); ctx.stroke();
                const hx = th.x, hy = th.y, as = 5;
                ctx.strokeStyle = '#3A8C82'; ctx.lineWidth = 1.8; ctx.lineCap = 'round';
                ctx.beginPath(); ctx.moveTo(hx, hy - as - 1); ctx.lineTo(hx, hy + as + 1); ctx.stroke();
                ctx.beginPath(); ctx.moveTo(hx - 3, hy - as + 2); ctx.lineTo(hx, hy - as - 1); ctx.lineTo(hx + 3, hy - as + 2); ctx.stroke();
                ctx.beginPath(); ctx.moveTo(hx - 3, hy + as - 2); ctx.lineTo(hx, hy + as + 1); ctx.lineTo(hx + 3, hy + as - 2); ctx.stroke();
            }
        }
    }
}

    function drawCenterLine(x1, y1, x2, y2) {
        ctx.save();
        ctx.strokeStyle = 'rgba(255, 0, 0, 0.4)';
        ctx.lineWidth = 0.5;
        ctx.setLineDash([4, 3]);
        ctx.beginPath();
        ctx.moveTo(x1, y1);
        ctx.lineTo(x2, y2);
        ctx.stroke();
        ctx.restore();
    }

    function getOverlayCorners() {
        if (!doorCornerPositions) return null;
        const { tl, tr, br, bl } = doorCornerPositions;
        const center = {
            cx: (tl.cx + tr.cx + br.cx + bl.cx) / 4,
            cy: (tl.cy + tr.cy + br.cy + bl.cy) / 4,
        };
        const ox = logW/2 + panX, oy = logH/2 + panY;
        const ts = p => ({ x: ox + p.cx * scaleFactor, y: oy + p.cy * scaleFactor });
        return { tl: ts(tl), tr: ts(tr), br: ts(br), bl: ts(bl), center: ts(center) };
    }

    function getTransformHandlePos() {
        if (!doorCornerPositions) return null;
        const c = getOverlayCorners();
        if (!c) return null;
        const topMidX = (c.tl.x + c.tr.x) / 2;
        const topMidY = (c.tl.y + c.tr.y) / 2;
        const edgeDx = c.tr.x - c.tl.x;
        const edgeDy = c.tr.y - c.tl.y;
        const edgeLen = Math.hypot(edgeDx, edgeDy) || 1;
        const perpX = -edgeDy / edgeLen;
        const perpY = edgeDx / edgeLen;
        const OFFSET = 36;
        return { x: topMidX + perpX * OFFSET, y: topMidY + perpY * OFFSET };
    }

    function isMouseNearOverlay(clientX, clientY) {
        if (!placementMode || !doorCornerPositions) return false;
        const rect = canvas.getBoundingClientRect();
        const mx = (clientX - rect.left) * (logW / rect.width);
        const my = (clientY - rect.top)  * (logH / rect.height);
        const c = getOverlayCorners();
        const pad = 24;
        const minX = Math.min(c.tl.x, c.tr.x, c.br.x, c.bl.x) - pad;
        const maxX = Math.max(c.tl.x, c.tr.x, c.br.x, c.bl.x) + pad;
        const minY = Math.min(c.tl.y, c.tr.y, c.br.y, c.bl.y) - pad;
        const maxY = Math.max(c.tl.y, c.tr.y, c.br.y, c.bl.y) + pad;
        return mx >= minX && mx <= maxX && my >= minY && my <= maxY;
    }

    function getHitOverlayCorner(clientX, clientY) {
        if (!placementMode || !doorCornerPositions) return null;
        const rect = canvas.getBoundingClientRect();
        const ratioX = logW / rect.width;
        const ratioY = logH / rect.height;
        const mx = (clientX - rect.left) * ratioX;
        const my = (clientY - rect.top)  * ratioY;
        const th = getTransformHandlePos();
        if (th && Math.hypot(mx - th.x, my - th.y) < 18) return 'transform';
        const c = getOverlayCorners();
        let best = null, bestD = 20;
        for (const [k, pt] of Object.entries(c)) {
            const hitR = k === 'center' ? 18 : 20;
            const d = Math.hypot(mx - pt.x, my - pt.y);
            if (d < hitR && d < bestD) { bestD = d; best = k; }
        }
        return best;
    }

    function snapToNode(cx, cy) {
        let best = null, bestDist = Infinity;
        for (const node of lastNodeList) {
            const d = Math.hypot(cx - node.cx, cy - node.cy);
            if (d < bestDist) { bestDist = d; best = node; }
        }
        return (best && bestDist < lastCellSize) ? best : null;
    }

    function handleEditClick(e) {
        const coord = screenToCtxCoord(e.clientX, e.clientY);

        if (lineEditMode === 'delete') {
            let bestKey = null, bestDist = Infinity;
            for (const [key, pos] of lastSegMap) {
                const dist = Math.hypot(coord.x - pos.mx, coord.y - pos.my);
                if (dist < bestDist) { bestDist = dist; bestKey = key; }
            }
            if (!bestKey || bestDist > lastCellSize * 1.2) return;
            if (bestKey.startsWith('added:')) {
                const idx = parseInt(bestKey.split(':')[1]);
                addedLines.splice(idx, 1);
            } else {
                const seg = lastSegMap.get(bestKey);
                if (seg) {
                    // 같은 방향·같은 중점의 쌍 세그먼트 탐색
                    let partnerKey = null, bestPDist = Infinity;
                    for (const [k, s] of lastSegMap) {
                        if (k === bestKey || k.startsWith('added:')) continue;
                        if (Math.abs(s.normAngle - seg.normAngle) > 0.05) continue;
                        const d = Math.hypot(s.mx - seg.mx, s.my - seg.my);
                        if (d < lastCellSize * 0.9 && d < bestPDist) { bestPDist = d; partnerKey = k; }
                    }
                    if (deletedSegs.has(bestKey)) {
                        deletedSegs.delete(bestKey);
                        if (partnerKey) deletedSegs.delete(partnerKey);
                    } else {
                        deletedSegs.add(bestKey);
                        if (partnerKey) deletedSegs.add(partnerKey);
                    }
                }
            }
            draw();
            return;
        }

        if (lineEditMode === 'add') {
            const snapped = snapToNode(coord.x, coord.y);
            if (!snapped) return; // 교점에서만 동작
            const norm = ctxToNorm(snapped.cx, snapped.cy);
            if (!addLineStart) {
                addLineStart = norm;
                draw();
            } else {
                // 같은 점이면 무시
                if (Math.abs(norm.nx - addLineStart.nx) < 0.001 && Math.abs(norm.ny - addLineStart.ny) < 0.001) return;
                addedLines.push({ nx1: addLineStart.nx, ny1: addLineStart.ny, nx2: norm.nx, ny2: norm.ny });
                addLineStart = null;
                draw();
            }
        }
    }

    container.addEventListener('mousedown', function(e) {
        if (e.target.closest('.canvas-controls')) return;
        if (lineEditMode) { handleEditClick(e); return; }
        const cornerHit = getHitOverlayCorner(e.clientX, e.clientY);
        const corner = cornerHit === 'center' ? 'move' : cornerHit;
        const sp = () => ({
            tl: { ...doorCornerPositions.tl },
            tr: { ...doorCornerPositions.tr },
            br: { ...doorCornerPositions.br },
            bl: { ...doorCornerPositions.bl },
        });
        if (corner) {
            handlesVisible = true;
            const startPos = sp();
            const drag = { corner, startPositions: startPos, startMx: e.clientX, startMy: e.clientY };
            if (corner === 'transform') {
                const cCx = (startPos.tl.cx + startPos.tr.cx + startPos.br.cx + startPos.bl.cx) / 4;
                const cCy = (startPos.tl.cy + startPos.tr.cy + startPos.br.cy + startPos.bl.cy) / 4;
                const rect_ = canvas.getBoundingClientRect();
                const mxC = (e.clientX - rect_.left) * (logW / rect_.width);
                const myC = (e.clientY - rect_.top)  * (logH / rect_.height);
                const ox_ = logW / 2 + panX, oy_ = logH / 2 + panY;
                drag.scaleCenter = { cx: cCx, cy: cCy };
                drag.startDist = Math.hypot(mxC - (ox_ + cCx * scaleFactor), myC - (oy_ + cCy * scaleFactor)) || 1;
            }
            overlayDrag = drag;
            return;
        }
        if (placementMode) {
            overlayDrag = { corner: 'move', startPositions: sp(), startMx: e.clientX, startMy: e.clientY };
            return;
        }
        if (!panMode) return;
        isDragging = true;
        startX = e.clientX - panX;
        startY = e.clientY - panY;
    });
    window.addEventListener('mousemove', function(e) {
        if (overlayDrag) {
            const dcx = (e.clientX - overlayDrag.startMx) / scaleFactor;
            const dcy = (e.clientY - overlayDrag.startMy) / scaleFactor;
            const { corner, startPositions: sp } = overlayDrag;

            if (corner === 'move') {
                doorCornerPositions.tl = { cx: sp.tl.cx + dcx, cy: sp.tl.cy + dcy };
                doorCornerPositions.tr = { cx: sp.tr.cx + dcx, cy: sp.tr.cy + dcy };
                doorCornerPositions.br = { cx: sp.br.cx + dcx, cy: sp.br.cy + dcy };
                doorCornerPositions.bl = { cx: sp.bl.cx + dcx, cy: sp.bl.cy + dcy };
            } else if (corner === 'tl') {
                doorCornerPositions.tl = { cx: sp.tl.cx + dcx, cy: sp.tl.cy + dcy };
            } else if (corner === 'tr') {
                doorCornerPositions.tr = { cx: sp.tr.cx + dcx, cy: sp.tr.cy + dcy };
            } else if (corner === 'br') {
                doorCornerPositions.br = { cx: sp.br.cx + dcx, cy: sp.br.cy + dcy };
            } else if (corner === 'bl') {
                doorCornerPositions.bl = { cx: sp.bl.cx + dcx, cy: sp.bl.cy + dcy };
            } else if (corner === 'transform') {
                const rect_ = canvas.getBoundingClientRect();
                const mxC = (e.clientX - rect_.left) * (logW / rect_.width);
                const myC = (e.clientY - rect_.top)  * (logH / rect_.height);
                const { scaleCenter, startDist } = overlayDrag;
                const ox_ = logW / 2 + panX, oy_ = logH / 2 + panY;
                const curDist = Math.hypot(mxC - (ox_ + scaleCenter.cx * scaleFactor), myC - (oy_ + scaleCenter.cy * scaleFactor)) || 0.001;
                const s = Math.max(0.05, curDist / startDist);
                for (const k of ['tl', 'tr', 'br', 'bl']) {
                    doorCornerPositions[k] = {
                        cx: scaleCenter.cx + (sp[k].cx - scaleCenter.cx) * s,
                        cy: scaleCenter.cy + (sp[k].cy - scaleCenter.cy) * s,
                    };
                }
            }
            draw();
            return;
        }
        if (placementMode) {
            const near = isMouseNearOverlay(e.clientX, e.clientY);
            if (near !== handlesVisible) {
                handlesVisible = near;
                draw();
            }
            const ch = getHitOverlayCorner(e.clientX, e.clientY);
            if (ch === 'center') canvas.style.cursor = 'move';
            else if (ch === 'transform') canvas.style.cursor = 'ns-resize';
            else if (ch === 'tl' || ch === 'br') canvas.style.cursor = 'nwse-resize';
            else if (ch === 'tr' || ch === 'bl') canvas.style.cursor = 'nesw-resize';
            else canvas.style.cursor = near ? 'move' : (panMode ? 'grab' : 'default');
        }
        if (!isDragging) return;
        panX = e.clientX - startX;
        panY = e.clientY - startY;
        draw();
    });
    window.addEventListener('mouseup', function() {
        if (overlayDrag) {
            overlayDrag = null;
            handlesVisible = false;
            draw();
        }
        isDragging = false;
    });
    container.addEventListener('wheel', function(e) {
        e.preventDefault();
        const intensity = 0.1;
        if (e.deltaY < 0) scaleFactor *= (1 + intensity);
        else scaleFactor /= (1 + intensity);
        scaleFactor = Math.max(0.3, Math.min(scaleFactor, 20));
        draw();
    }, {
        passive: false
    });

    const btnSidebarTab = document.getElementById('btnSidebarTab');

    function toggleSidebar() {
        sidebar.classList.toggle('collapsed');
        btnSidebarTab.classList.toggle('collapsed');
        animatePanelResize();
    }

    btnSidebarTab.addEventListener('click', toggleSidebar);

    // Section collapse
    document.querySelectorAll('.sb-section-title').forEach(title => {
        const section = title.closest('.sb-section');

        const body = document.createElement('div');
        body.className = 'sb-section-body';
        let next = title.nextElementSibling;
        while (next) {
            const tmp = next.nextElementSibling;
            body.appendChild(next);
            next = tmp;
        }
        section.appendChild(body);
        body.style.overflow = 'visible';

        title.insertAdjacentHTML('beforeend',
            '<svg class="sb-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6,9 12,15 18,9"/></svg>'
        );

        title.addEventListener('click', () => {
            const isCollapsing = !section.classList.contains('sb-collapsed');
            if (isCollapsing) {
                body.style.overflow = 'hidden';
                section.classList.add('sb-collapsed');
            } else {
                section.classList.remove('sb-collapsed');
                body.addEventListener('transitionend', () => {
                    if (!section.classList.contains('sb-collapsed')) body.style.overflow = 'visible';
                }, { once: true });
            }
        });
    });

    document.getElementById('btnScale').addEventListener('click', () => {
        placementMode = !placementMode;
        if (placementMode) {
            const W = doorNaturalSize.w, H = doorNaturalSize.h;
            placementNaturalSize = { w: W, h: H };
            doorCornerPositions = {
                tl: { cx: -W/2, cy: -H/2 },
                tr: { cx:  W/2, cy: -H/2 },
                br: { cx:  W/2, cy:  H/2 },
                bl: { cx: -W/2, cy:  H/2 },
            };
        } else {
            doorCornerPositions = null;
            placementNaturalSize = null;
            canvas.style.cursor = panMode ? 'grab' : 'default';
        }
        document.getElementById('btnScale').classList.toggle('cv-btn-active', placementMode);
        draw();
    });

    document.getElementById('btnZoomIn').addEventListener('click', () => {
        scaleFactor = Math.min(scaleFactor * 1.2, 20);
        draw();
    });
    document.getElementById('btnZoomOut').addEventListener('click', () => {
        scaleFactor = Math.max(scaleFactor / 1.2, 0.3);
        draw();
    });
    document.getElementById('btnResetView').addEventListener('click', () => {
        scaleFactor = 1.0;
        panX = 0;
        panY = 0;
        frameColorPicker.selectColor(DEFAULT_FRAME_COLOR);
        slatColorPicker.selectColor(DEFAULT_SLAT_COLOR);
        draw();
    });
    // ── 풍판 토글 ─────────────────────────────
    const chkPungpan   = document.getElementById('chkPungpan');
    const pungpanCtrl  = document.getElementById('pungpanCtrl');


    chkPungpan.addEventListener('change', async () => {
        pungpanCtrl.style.display = chkPungpan.checked ? 'block' : 'none';
        if (!chkPungpan.checked) {
            document.getElementById('txtPungpan').value = 0;
            document.getElementById('numPungpan').value = 0;
            await draw();
            const newH = Math.round(geo.actualPatternH);
            txtH.value = newH;
            document.getElementById('numH').value = newH;
        }
        draw();
    });

    document.getElementById('chkShrinkH').addEventListener('change', draw);

    // ── 슬라이더 ↔ 인풋창 양방향 동기화 ──────────────────

    const syncPairs = [
        { range: txtW,      num: document.getElementById('numW'),       min: 400,  max: 2600 },
        { range: txtH,      num: document.getElementById('numH'),       min: 400,  max: 2600 },
        { range: txtCols,   num: document.getElementById('numCols'),    min: 2,    max: 30   },
        { range: txtFrame,  num: document.getElementById('numFrame'),   min: 20,   max: 150  },
        { range: txtFrameH, num: document.getElementById('numFrameH'),  min: 20,   max: 150  },
        { range: txtSlat,   num: document.getElementById('numSlat'),    min: 8,    max: 35   },
        { range: document.getElementById('txtPungpan'), num: document.getElementById('numPungpan'), min: 0, max: 600 },
    ];

    syncPairs.forEach(({ range, num, min, max }) => {
        range.addEventListener('input', () => {
            num.value = range.value;
            draw();
        });
        num.addEventListener('input', () => {
            const v = parseInt(num.value);
            if (isNaN(v)) return;
            if (v >= min && v <= max) {
                range.value = v;
                draw();
            }
        });
        num.addEventListener('blur', () => {
            let v = parseInt(num.value);
            if (isNaN(v)) v = parseInt(range.value);
            v = Math.min(max, Math.max(min, v));
            range.value = v;
            num.value   = v;
            draw();
        });
        num.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') num.blur();
        });
    });

    function updateDoorCountOptions() {
        const isSwing = txtDoorType.value === 'swing';
        Array.from(txtDoorCount.options).forEach(opt => {
            const v = parseInt(opt.value);
            opt.hidden = isSwing && v > 2;
        });
        if (isSwing && parseInt(txtDoorCount.value) > 2) {
            txtDoorCount.value = '2';
        }
        draw();
    }

    txtDoorType.addEventListener('input', updateDoorCountOptions);
    txtDoorCount.addEventListener('input', draw);
    document.getElementById('chkRotate').addEventListener('change', e => {
        rotateOn = e.target.checked;
        draw();
    });
    updateDoorCountOptions();

    // ── 편집 버튼 ──────────────────────────────────
    const CURSOR_ERASER = `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'%3E%3Crect x='5' y='10' width='14' height='9' rx='2' fill='%23fff' stroke='%23555' stroke-width='1.5'/%3E%3Crect x='5' y='10' width='6' height='9' rx='0' fill='%23f87171' stroke='none'/%3E%3Crect x='5' y='10' width='6' height='9' rx='0' fill='none' stroke='%23555' stroke-width='1.5'/%3E%3Cline x1='5' y1='19' x2='19' y2='19' stroke='%23555' stroke-width='1.5'/%3E%3C/svg%3E") 4 20, crosshair`;
    const CURSOR_PEN    = `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'%3E%3Cpath d='M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z' fill='%23fff' stroke='%23555' stroke-width='1.5' stroke-linejoin='round'/%3E%3C/svg%3E") 2 22, crosshair`;

    function setPanMode() {
        panMode = !panMode;
        document.getElementById('btnPan').classList.toggle('cv-btn-active', panMode);
        if (panMode) {
            lineEditMode = null;
            addLineStart = null;
            document.getElementById('btnEditDelete').classList.remove('cv-btn-active');
            document.getElementById('btnEditAdd').classList.remove('cv-btn-active');
            canvas.style.cursor = 'grab';
            draw();
        } else {
            canvas.style.cursor = 'default';
        }
    }

    function setEditMode(mode) {
        panMode = false;
        document.getElementById('btnPan').classList.remove('cv-btn-active');
        lineEditMode = lineEditMode === mode ? null : mode;
        addLineStart = null;
        document.getElementById('btnEditDelete').classList.toggle('cv-btn-active', lineEditMode === 'delete');
        document.getElementById('btnEditAdd').classList.toggle('cv-btn-active', lineEditMode === 'add');
        if (lineEditMode === 'delete')     canvas.style.cursor = CURSOR_ERASER;
        else if (lineEditMode === 'add')   canvas.style.cursor = CURSOR_PEN;
        else                               canvas.style.cursor = 'default';
        if (lineEditMode === 'add') draw();
    }
    document.getElementById('btnPan').addEventListener('click', setPanMode);
    document.getElementById('btnEditDelete').addEventListener('click', () => setEditMode('delete'));
    document.getElementById('btnEditAdd').addEventListener('click', () => setEditMode('add'));
    document.getElementById('btnEditClear').addEventListener('click', () => {
        pmConfirm('편집 내용을 모두 초기화하시겠습니까?', () => {
            deletedSegs.clear();
            addedLines  = [];
            addLineStart = null;
            draw();
        });
    });

    // 작성일 / 수정일
    function fmtDate(ts) {
        const d = new Date(ts);
        const yy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        const hh = String(d.getHours()).padStart(2, '0');
        const mi = String(d.getMinutes()).padStart(2, '0');
        return `${yy}.${mm}.${dd} ${hh}:${mi}`;
    }

    const CREATED_KEY       = 'pmok_sambuntok_created';
    const MODIFIED_KEY      = 'pmok_sambuntok_modified';
    const VERSIONS_KEY      = 'pmok_sambuntok_versions';
    const BG_IMAGE_KEY      = 'pmok_sambuntok_bg';
    const THUMBS_KEY        = 'pmok_sambuntok_thumbs';
    const CURRENT_TITLE_KEY = 'pmok_sambuntok_current_title';
    const NAME_KEY          = 'pmok_sambuntok_name';
    const MAX_VERSIONS      = 20;

    // ── 작업 시간 추적 ──────────────────────────────
    let workAccum = 0;  // 누적 작업 시간(초) — DB 기준 최신값 + 이번 세션 추가분
    let workStart = Date.now();

    function workElapsedSec() {
        return workAccum + Math.floor((Date.now() - workStart) / 1000);
    }

    function pauseWorkTimer() {
        workAccum += Math.floor((Date.now() - workStart) / 1000);
    }

    function resumeWorkTimer() {
        workStart = Date.now();
    }

    // ── 썸네일 캡처 ────────────────────────────────
    function captureThumbnail() {
        const src = document.getElementById('doorCanvas');
        const W   = 320;
        const H   = Math.round(W * src.height / src.width);
        const tmp = document.createElement('canvas');
        tmp.width  = W;
        tmp.height = H;
        const ctx  = tmp.getContext('2d');
        ctx.fillStyle = '#E5E7EA';
        ctx.fillRect(0, 0, W, H);
        ctx.drawImage(src, 0, 0, W, H);
        return tmp.toDataURL('image/jpeg', 0.65);
    }

    // 썸네일 + 배경 이미지 복원
    (function restoreThumbs() {
        let saved = [];
        try { saved = JSON.parse(localStorage.getItem(THUMBS_KEY) || '[]'); } catch(e) {}
        if (!saved.length) return;
        const activeSrc = localStorage.getItem(BG_IMAGE_KEY);
        showRightSidebar();
        saved.forEach(({ src, filename }) => {
            const id = Date.now() + Math.random();
            const imgObj = new Image();
            imgObj.onload = function() {
                thumbImages.push({ id, src, img: imgObj, filename });
                addThumbItem(id, src, filename);
                if (src === activeSrc) setActiveThumb(id);
            };
            imgObj.src = src;
        });
    })();

    function setElText(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    if (!localStorage.getItem(CREATED_KEY)) {
        localStorage.setItem(CREATED_KEY, Date.now());
    }
    setElText('dateCreated', fmtDate(Number(localStorage.getItem(CREATED_KEY))));

    const savedModified = localStorage.getItem(MODIFIED_KEY) || localStorage.getItem(CREATED_KEY);
    setElText('dateModified', fmtDate(Number(savedModified)));

    function updateModified() {
        const now = Date.now();
        localStorage.setItem(MODIFIED_KEY, now);
        setElText('dateModified', fmtDate(now));
    }

    // ── 버전 시스템 ────────────────────────────────
    let versions = [];
    let currentVerIdx = -1;

    function getParams() {
        return {
            name:      document.getElementById('drawingName').value,
            W:         parseInt(txtW.value),
            H:         parseInt(txtH.value),
            cols:      parseInt(txtCols.value),
            frame:     parseInt(txtFrame.value),
            frameH:    parseInt(txtFrameH.value),
            slat:      parseInt(txtSlat.value),
            doorType:  txtDoorType.value,
            doorCount: parseInt(txtDoorCount.value),
            pungpanOn: document.getElementById('chkPungpan').checked,
            pungpan:   parseInt(document.getElementById('txtPungpan').value) || 0,
            shrinkH:   document.getElementById('chkShrinkH').checked,
            rotate:    document.getElementById('chkRotate').checked,
            wood:      document.getElementById('txtWood').value,
            finish:    document.getElementById('txtFinish').value,
            frameColor: selectedFrameColor,
            slatColor:  selectedSlatColor,
            panX, panY, scaleFactor,
            placementMode,
            doorCornerPositions: doorCornerPositions ? { ...doorCornerPositions } : null,
            placementNaturalSize: placementNaturalSize ? { ...placementNaturalSize } : null,
            deletedSegs: [...deletedSegs],
            addedLines,
        };
    }

    function setSlider(rangeId, numId, val) {
        document.getElementById(rangeId).value = val;
        document.getElementById(numId).value   = val;
    }

    function applyParams(p) {
        document.getElementById('drawingName').value = p.name || '';
        setSlider('txtW',      'numW',      p.W);
        setSlider('txtH',      'numH',      p.H);
        setSlider('txtCols',   'numCols',   p.cols);
        setSlider('txtFrame',  'numFrame',  p.frame);
        setSlider('txtFrameH', 'numFrameH', p.frameH);
        setSlider('txtSlat',   'numSlat',   p.slat);
        txtDoorType.value  = p.doorType;
        txtDoorCount.value = p.doorCount;
        document.getElementById('chkPungpan').checked = p.pungpanOn;
        setSlider('txtPungpan', 'numPungpan', p.pungpan);
        document.getElementById('chkShrinkH').checked = p.shrinkH || false;
        document.getElementById('chkRotate').checked = p.rotate;
        document.getElementById('txtWood').value     = p.wood || 'hongsong';
        document.getElementById('txtFinish').value   = p.finish;
        rotateOn = p.rotate;
        document.getElementById('pungpanCtrl').style.display = p.pungpanOn ? 'block' : 'none';
        if (p.panX !== undefined) { panX = p.panX; panY = p.panY; scaleFactor = p.scaleFactor; }
        placementMode        = p.placementMode        || false;
        doorCornerPositions  = p.doorCornerPositions  || null;
        placementNaturalSize = p.placementNaturalSize || null;
        deletedSegs  = new Set(p.deletedSegs || []);
        addedLines      = p.addedLines || [];
        addLineStart    = null;
        document.getElementById('btnScale').classList.toggle('cv-btn-active', placementMode);
        frameColorPicker.selectColor(p.frameColor);
        slatColorPicker.selectColor(p.slatColor);
        updateDoorCountOptions();
        draw();
    }

    function renderVerList() {
        const list = document.getElementById('verList');
        if (versions.length === 0) {
            list.innerHTML = '<div class="ver-empty">저장된 버전이 없습니다</div>';
            return;
        }
        list.innerHTML = '';
        [...versions].reverse().forEach((ver, i) => {
            const realIdx = versions.length - 1 - i;
            const item = document.createElement('div');
            item.className = 'ver-item' + (realIdx === currentVerIdx ? ' active' : '');
            item.innerHTML = `<span class="ver-num">v${realIdx + 1}</span><span class="ver-date">${fmtDate(ver.savedAt)}</span><span class="ver-del" title="삭제"><i class="bi bi-x-lg"></i></span>`;
            item.addEventListener('click', () => {
                currentVerIdx = realIdx;
                applyParams(ver.params);
                document.getElementById('verLabel').textContent = 'v' + (realIdx + 1);
                renderVerList();
                document.getElementById('verDropdown').classList.remove('open');
            });
            item.querySelector('.ver-del').addEventListener('click', (e) => {
                e.stopPropagation();
                pmConfirm(`v${realIdx + 1}를 정말 삭제하시겠습니까?`, () => {
                    versions.splice(realIdx, 1);
                    localStorage.setItem(VERSIONS_KEY, JSON.stringify(versions));
                    if (currentVerIdx >= versions.length) currentVerIdx = versions.length - 1;
                    const label = versions.length > 0 ? 'v' + (currentVerIdx + 1) : '—';
                    document.getElementById('verLabel').textContent = label;
                    renderVerList();
                });
            });
            list.appendChild(item);
        });
    }

    // 버전 삭제 확인 모달 (1회 생성)
    const _verDelModalEl = document.createElement('div');
    _verDelModalEl.id = 'verDelModal';
    _verDelModalEl.className = 'modal fade';
    _verDelModalEl.tabIndex = -1;
    _verDelModalEl.innerHTML = `
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="mb-2" style="font-size:28px;color:#e05218;"><i class="bi bi-exclamation-circle"></i></div>
                    <p class="mb-0 fw-semibold" id="verDelModalMsg" style="font-size:14px;"></p>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0 pb-4 gap-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="button" class="btn btn-sm btn-danger" id="verDelModalConfirm">삭제</button>
                </div>
            </div>
        </div>`;
    document.body.appendChild(_verDelModalEl);

    function showVerDelConfirm(label, onConfirm) {
        document.getElementById('verDelModalMsg').textContent = `${label}를 정말 삭제하시겠습니까?`;
        const modal = new bootstrap.Modal(_verDelModalEl);
        document.getElementById('verDelModalConfirm').onclick = () => { modal.hide(); onConfirm(); };
        modal.show();
    }

    async function loadVersions() {
        const savedTitle = localStorage.getItem(CURRENT_TITLE_KEY);

        if (savedTitle) {
            document.getElementById('drawingName').value = savedTitle;
            localStorage.setItem(NAME_KEY, savedTitle);
        }

        if (savedTitle) {
            const ok = await loadFromDb(savedTitle);
            if (ok) return;
        } else {
            // 저장된 제목 없으면 가장 최근 도면 자동 로드
            const drawings = await DrawingSync.list('sambuntok');
            if (drawings.length > 0) {
                const ok = await loadFromDb(drawings[0].title);
                if (ok) return;
            }
        }
        // DB 로드 실패 시 localStorage fallback
        try { versions = JSON.parse(localStorage.getItem(VERSIONS_KEY)) || []; } catch(e) { versions = []; }
        if (versions.length > 0) {
            currentVerIdx = versions.length - 1;
            document.getElementById('verLabel').textContent = 'v' + (currentVerIdx + 1);
        }
        renderVerList();
    }

    async function syncToDb() {
        const title = document.getElementById('drawingName').value.trim();
        if (!title) return;
        pauseWorkTimer();
        resumeWorkTimer();
        localStorage.setItem(CURRENT_TITLE_KEY, title);
        /** @type {any} */ (window.DrawingSync).save(
            'sambuntok', title,
            Number(localStorage.getItem(CREATED_KEY)),
            versions,
            captureThumbnail(),
            workAccum
        );
    }

    async function loadFromDb(title) {
        const data = await /** @type {any} */ (window.DrawingSync).load('sambuntok', title);
        if (!data || !data.versions || !data.versions.length) return false;
        versions      = data.versions;
        currentVerIdx = versions.length - 1;
        applyParams(versions[currentVerIdx].params);
        document.getElementById('drawingName').value    = title;
        document.getElementById('verLabel').textContent = 'v' + (currentVerIdx + 1);
        localStorage.setItem(VERSIONS_KEY,      JSON.stringify(versions));
        localStorage.setItem(CURRENT_TITLE_KEY, title);
        localStorage.setItem(NAME_KEY,          title);
        if (data.drawing) {
            const cAt = new Date(data.drawing.created_at).getTime();
            const uAt = new Date(data.drawing.updated_at).getTime();
            localStorage.setItem(CREATED_KEY,  cAt);
            localStorage.setItem(MODIFIED_KEY, uAt);
            setElText('dateCreated', fmtDate(cAt));
            setElText('dateModified', fmtDate(uAt));
            // 작업 시간 DB 기준값으로 초기화
            workAccum = parseInt(data.drawing.work_time_sec) || 0;
            workStart = Date.now();
        }
        renderVerList();
        return true;
    }

    function saveVersion() {
        const badge = document.querySelector('.hdr-title-badge');
        const title = document.getElementById('drawingName').value.trim();
        if (!title) {
            badge.classList.remove('shake');
            void badge.offsetWidth;
            badge.classList.add('shake');
            badge.addEventListener('animationend', () => badge.classList.remove('shake'), { once: true });
            document.getElementById('drawingName').focus();
            return;
        }

        // 제목이 바뀌면 새 도면 → 버전·작업시간·생성일 초기화
        const prevTitle = localStorage.getItem(CURRENT_TITLE_KEY);
        if (prevTitle && prevTitle !== title) {
            versions      = [];
            currentVerIdx = -1;
            const now = Date.now();
            localStorage.setItem(CREATED_KEY, now);
            localStorage.setItem(MODIFIED_KEY, now);
            workAccum  = 0;
            workStart  = Date.now();
        }

        versions.push({ savedAt: Date.now(), params: getParams() });
        if (versions.length > MAX_VERSIONS) versions.shift();
        localStorage.setItem(VERSIONS_KEY, JSON.stringify(versions));
        currentVerIdx = versions.length - 1;
        document.getElementById('verLabel').textContent = 'v' + (currentVerIdx + 1);
        renderVerList();
        updateModified();
        syncToDb();
    }

    document.getElementById('btnSave').addEventListener('click', saveVersion);

    const verBtn      = document.getElementById('verBtn');
    const verDropdown = document.getElementById('verDropdown');
    verBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        verDropdown.classList.toggle('open');
    });
    document.addEventListener('click', () => verDropdown.classList.remove('open'));

    // ── 도면 목록 관리 ─────────────────────────────
    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function openDrawingManager() {
        document.getElementById('dmBackdrop').classList.add('pm-active');
        refreshDrawingList();
    }

    function closeDrawingManager() {
        document.getElementById('dmBackdrop').classList.remove('pm-active');
    }

    async function refreshDrawingList() {
        const list = document.getElementById('dmList');
        list.innerHTML = '<div class="dm-empty">불러오는 중…</div>';
        const drawings = await /** @type {any} */ (window.DrawingSync).list('sambuntok');
        if (!drawings.length) {
            list.innerHTML = '<div class="dm-empty">저장된 도면이 없습니다</div>';
            return;
        }
        list.innerHTML = '';
        const curTitle = document.getElementById('drawingName').value.trim();
        drawings.forEach(d => {
            const item = document.createElement('div');
            item.className = 'dm-item' + (d.title === curTitle ? ' dm-active' : '');
            item.innerHTML = `
                <div class="dm-item-info">
                    <div class="dm-title">${escHtml(d.title)}</div>
                    <div class="dm-date">${fmtDate(new Date(d.updated_at).getTime())}</div>
                </div>
                <div class="dm-actions">
                    <button class="dm-btn dm-rename-btn" title="이름 변경"><i class="bi bi-pencil"></i></button>
                    <button class="dm-btn dm-del-btn" title="삭제"><i class="bi bi-x-lg"></i></button>
                </div>`;
            item.addEventListener('click', (e) => {
                if (e.target.closest('.dm-actions')) return;
                openDrawingByTitle(d.title);
            });
            item.querySelector('.dm-rename-btn').addEventListener('click', (e) => {
                e.stopPropagation();
                showRenameModal(d.title);
            });
            item.querySelector('.dm-del-btn').addEventListener('click', (e) => {
                e.stopPropagation();
                pmConfirm(`"${d.title}" 도면을 삭제하시겠습니까?`, async () => {
                    await /** @type {any} */ (window.DrawingSync).delete('sambuntok', d.title);
                    if (d.title === document.getElementById('drawingName').value.trim()) {
                        startNewDrawing();
                    }
                    refreshDrawingList();
                }, { sub: '모든 버전이 함께 삭제됩니다.' });
            });
            list.appendChild(item);
        });
    }

    async function openDrawingByTitle(title) {
        const data = await /** @type {any} */ (window.DrawingSync).load('sambuntok', title);
        if (!data || !data.versions || !data.versions.length) {
            pmAlert('도면을 불러올 수 없습니다.');
            return;
        }
        versions      = data.versions;
        currentVerIdx = versions.length - 1;
        applyParams(versions[currentVerIdx].params);
        document.getElementById('drawingName').value    = title;
        document.getElementById('verLabel').textContent = 'v' + (currentVerIdx + 1);
        localStorage.setItem(VERSIONS_KEY,      JSON.stringify(versions));
        localStorage.setItem(CURRENT_TITLE_KEY, title);
        localStorage.setItem(NAME_KEY,          title);
        if (data.drawing) {
            const cAt = new Date(data.drawing.created_at).getTime();
            const uAt = new Date(data.drawing.updated_at).getTime();
            localStorage.setItem(CREATED_KEY,  cAt);
            localStorage.setItem(MODIFIED_KEY, uAt);
            setElText('dateCreated', fmtDate(cAt));
            setElText('dateModified', fmtDate(uAt));
        }
        renderVerList();
        closeDrawingManager();
    }

    function startNewDrawing() {
        versions      = [];
        currentVerIdx = -1;
        document.getElementById('drawingName').value    = '';
        document.getElementById('verLabel').textContent = '—';
        const now = Date.now();
        localStorage.setItem(CREATED_KEY,  now);
        localStorage.setItem(MODIFIED_KEY, now);
        localStorage.removeItem(CURRENT_TITLE_KEY);
        localStorage.removeItem(NAME_KEY);
        setElText('dateCreated', fmtDate(now));
        setElText('dateModified', fmtDate(now));
        renderVerList();
        closeDrawingManager();
    }

    let _renameTarget = '';
    function showRenameModal(title) {
        _renameTarget = title;
        const input = document.getElementById('dmRenameInput');
        input.value = title;
        document.getElementById('dmRenameBackdrop').classList.add('pm-active');
        setTimeout(() => { input.select(); }, 60);
    }

    document.getElementById('dmBtn').addEventListener('click', openDrawingManager);
    document.getElementById('dmNewBtn').addEventListener('click', startNewDrawing);
    document.getElementById('dmCloseBtn').addEventListener('click', closeDrawingManager);
    document.getElementById('dmBackdrop').addEventListener('click', (e) => {
        if (e.target === document.getElementById('dmBackdrop')) closeDrawingManager();
    });

    document.getElementById('dmRenameCancel').addEventListener('click', () => {
        document.getElementById('dmRenameBackdrop').classList.remove('pm-active');
    });
    document.getElementById('dmRenameOk').addEventListener('click', async () => {
        const newTitle = document.getElementById('dmRenameInput').value.trim();
        if (!newTitle || newTitle === _renameTarget) {
            document.getElementById('dmRenameBackdrop').classList.remove('pm-active');
            return;
        }
        const ok = await /** @type {any} */ (window.DrawingSync).rename('sambuntok', _renameTarget, newTitle);
        if (ok) {
            if (_renameTarget === document.getElementById('drawingName').value.trim()) {
                document.getElementById('drawingName').value = newTitle;
                localStorage.setItem(NAME_KEY,          newTitle);
                localStorage.setItem(CURRENT_TITLE_KEY, newTitle);
            }
            document.getElementById('dmRenameBackdrop').classList.remove('pm-active');
            refreshDrawingList();
        } else {
            pmAlert('이름 변경에 실패했습니다.', { sub: '이미 같은 제목의 도면이 있을 수 있습니다.' });
        }
    });
    document.getElementById('dmRenameInput').addEventListener('keydown', (e) => {
        if (e.key === 'Enter')  document.getElementById('dmRenameOk').click();
        if (e.key === 'Escape') document.getElementById('dmRenameCancel').click();
    });

    loadSavedRenders();
    loadVersions();

    // ── 도면 이름 자동 저장 ────────────────────────
    const drawingNameEl = document.getElementById('drawingName');
    const savedName = localStorage.getItem(NAME_KEY);
    if (savedName) drawingNameEl.value = savedName;
    drawingNameEl.addEventListener('input', () => {
        localStorage.setItem(NAME_KEY, drawingNameEl.value);
    });

    window.addEventListener('resize', resizeCanvasDebounced);
    resizeCanvas();

    //출력
    btnSavePNG.addEventListener('click', function() {

        updateModified();

        // 배경 포함 저장용 캔버스 생성
        const exportCanvas = document.createElement('canvas');
        const exportCtx = exportCanvas.getContext('2d');

        exportCanvas.width = logW * 2;
        exportCanvas.height = logH * 2;

        // 배경
        exportCtx.fillStyle = '#E5E7EA';
        exportCtx.fillRect(0, 0, logW * 2, logH * 2);

        // 기존 캔버스 복사 (HiDPI → 2x 논리 크기로)
        exportCtx.drawImage(canvas, 0, 0, logW * 2, logH * 2);

        // 다운로드
        const link = document.createElement('a');

        const doorTypeText =
            txtDoorType.options[txtDoorType.selectedIndex].text;

        const filename =
            `창호_${doorTypeText}_${txtDoorCount.value}짝_${txtW.value}x${txtH.value}.png`;

        link.download = filename;

        link.href = exportCanvas.toDataURL('image/png');

        link.click();
    });

    btnSavePDF.addEventListener('click', function() {

        updateModified();

        const {
            jsPDF
        } = window.jspdf;

        const pdf = new jsPDF({
            orientation: 'landscape',
            unit: 'mm',
            format: 'a4'
        });

        // 고해상도 캔버스
        const exportCanvas = document.createElement('canvas');
        const exportCtx = exportCanvas.getContext('2d');

        exportCanvas.width = logW * 2;
        exportCanvas.height = logH * 2;

        // 배경
        exportCtx.fillStyle = '#ffffff';
        exportCtx.fillRect(0, 0, logW * 2, logH * 2);

        // 원본 그리기 (HiDPI → 2x 논리 크기로)
        exportCtx.drawImage(canvas, 0, 0, logW * 2, logH * 2);

        const imgData =
            exportCanvas.toDataURL('image/png');

        // PDF 사이즈 계산
        const pageWidth = 297;
        const pageHeight = 210;

        const imgRatio =
            exportCanvas.width / exportCanvas.height;

        let imgWidth = 260;
        let imgHeight = imgWidth / imgRatio;

        if (imgHeight > 180) {
            imgHeight = 180;
            imgWidth = imgHeight * imgRatio;
        }

        const x = (pageWidth - imgWidth) / 2;
        const y = (pageHeight - imgHeight) / 2;

        pdf.addImage(
            imgData,
            'PNG',
            x,
            y,
            imgWidth,
            imgHeight
        );

        const doorTypeText =
            txtDoorType.options[
                txtDoorType.selectedIndex
            ].text;

        pdf.save(
            `창호_${doorTypeText}_${txtDoorCount.value}짝_${txtW.value}x${txtH.value}.pdf`
        );
    });