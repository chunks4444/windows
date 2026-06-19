    let appBackgroundImage = null;
    let placementMode        = false;
    let doorNaturalSize      = { w: 0, h: 0 };
    let doorOverlay          = { tx: 0, ty: 0, sx: 1, sy: 1, skewX: 0, skewY: 0 };
    let doorCornerPositions  = null;
    let overlayDrag          = null;
    let placementNaturalSize = null;
    let handlesVisible       = false;

    // ── 색상 그룹 ─────────────────────────────────
    const colorGroups = window.__pmokColorGroups || [];

    let selectedFrameColor = '#28241e';
    let selectedSlatColor  = '#28241e';
    let faceColorMap       = null;
    let facePaintMode      = false;
    let facePaintIsDown    = false;

    // ── 라인 편집 상태 ──────────────────────────────
    let deletedSegs  = new Set();
    let addedLines   = [];
    let lineEditMode = null;
    let addLineStart = null;

    let lastSegMap   = new Map();
    let lastNodeList = [];
    let lastILeft = 0, lastITop = 0, lastIW = 1, lastIH = 1, lastSlatPx = 1, lastCellSize = 1;
    let lastBaseScale = 1, lastOLeft = 0, lastOTop = 0, lastDoorWpx = 0, lastDoorHpx = 0;
    let showDimensions = true;
    let _exportCanvas = null;
    let _activeDrawCtx   = null;


    document.addEventListener('click', () => {
        document.querySelectorAll('.color-popup').forEach(p => p.classList.remove('open'));
    });

    const DEFAULT_FRAME_COLOR = '#28241e';
    const DEFAULT_SLAT_COLOR  = '#28241e';

    selectedFrameColor = DEFAULT_FRAME_COLOR;
    selectedSlatColor  = DEFAULT_SLAT_COLOR;

    // ── 멀티 슬롯 ─────────────────────────────────────────
    let slots = [];
    let activeSlotIdx = 0;
    let _drawGen = 0;
    let _suppressDraw = false;
    let _slotBounds = [];

    function _makeSlot(params) {
        return {
            params: params ? { ...params } : {
                W: 600, H: 1707, cols: 12, frame: 60, frameH: 60, slat: 12,
                vRatio: 1.2, pattern: '3/5/3', doorType: 'swing', doorCount: 1,
                pungpanOn: false, pungpan: 0, wood: 'hongsong', finish: 'changhoji',
                frameColor: DEFAULT_FRAME_COLOR, slatColor: DEFAULT_SLAT_COLOR, name: '',
            },
            deletedSegs: new Set(),
            addedLines:  [],
        };
    }
    slots = [_makeSlot()];

    // ── 오프스크린 캔버스 (배치 모드 투시 변환용) ────────
    const offCanvas = document.createElement('canvas');
    function drawPerspectiveQuad(tctx, img, tl, tr, br, bl) {
        const W = img.width, H = img.height;
        // 삼각형 1: TL-TR-BL
        drawTriangleAffine(tctx, img,
            0,0,  W,0,  0,H,
            tl.x,tl.y, tr.x,tr.y, bl.x,bl.y
        );
        // 삼각형 2: TR-BR-BL
        drawTriangleAffine(tctx, img,
            W,0,  W,H,  0,H,
            tr.x,tr.y, br.x,br.y, bl.x,bl.y
        );
    }

    const frameColorPicker = buildColorPopup('framePopup', 'framePreviewDot', 'framePreviewName', 'framePreviewBtn',
        hex => { selectedFrameColor = hex; }, selectedFrameColor);
    const slatColorPicker  = buildColorPopup('slatPopup',  'slatPreviewDot',  'slatPreviewName',  'slatPreviewBtn',
        hex => { selectedSlatColor  = hex; }, selectedSlatColor);
    const faceColorUI = buildFaceColorUI(
        () => { faceColorMap = null; draw(); }
    );
    const canvas = document.getElementById('doorCanvas');
    const ctx = canvas.getContext('2d');
    const container = document.getElementById('canvasContainer');
    const sidebar = document.getElementById('sidebar');

    const txtW = document.getElementById('txtW');
    const txtCols = document.getElementById('txtCols');
    const txtH = document.getElementById('txtH');
    
    const txtFrame = document.getElementById('txtFrame');
    const txtFrameH = document.getElementById('txtFrameH');


    const txtSlat  = document.getElementById('txtSlat');
    const txtRatio    = document.getElementById('txtRatio');

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
    let _versionsLoaded = false;

    // ── 공통 모달 유틸리티 ─────────────────────────
    let _pmModalEl = null;
    // ── 렌더링 결과 저장 ───────────────────────────
    const RENDERS_KEY = 'pmok_classic_renders';
    const MAX_RENDERS = 9;
    let savedRenders = [];

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

        // 배경+도면 전체를 AI로 전송
        const composite = document.createElement('canvas');
        composite.width  = logW;
        composite.height = logH;
        const compCtx = composite.getContext('2d');
        compCtx.drawImage(canvas, 0, 0, logW, logH);

        const overlay = document.getElementById('renderOverlay');
        overlay.style.display = 'flex';

        const _tok = localStorage.getItem('pmok_auth_token');
        const _authHeader = _tok ? { 'Authorization': 'Bearer ' + _tok } : {};

        const _renderAbort = new AbortController();
        const _renderTimer = setTimeout(() => { _renderAbort.abort(); overlay.style.display = 'none'; pmAlert('렌더링 시간이 초과됐습니다. (120초)', { type: 'danger' }); }, 120000);
        fetch('api/render.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', ..._authHeader },
            body: JSON.stringify({ image: composite.toDataURL('image/jpeg', 0.95), prompt }),
            signal: _renderAbort.signal,
        })
        .then(r => { clearTimeout(_renderTimer); return r.json(); })
        .then(data => {
            overlay.style.display = 'none';
            if (data.error) { pmAlert(data.error, { type: 'danger' }); return; }
            if (!data.image) { pmAlert('서버 오류', { type: 'danger' }); return; }
            const renderedImg = new Image();
            renderedImg.onload = () => {
                // AI 결과 비율 역변환 후 전체 표시
                const out = document.createElement('canvas');
                out.width = logW; out.height = logH;
                const ctx = out.getContext('2d');
                const scl = Math.min(1024/logW, 1024/logH);
                const fitW = Math.round(logW*scl), fitH = Math.round(logH*scl);
                const ox = Math.round((1024-fitW)/2), oy = Math.round((1024-fitH)/2);
                ctx.drawImage(renderedImg, ox, oy, fitW, fitH, 0, 0, logW, logH);
                const finalSrc = out.toDataURL('image/jpeg', 0.95);
                saveRender(finalSrc);
                showRenderResult(finalSrc);
            };
            renderedImg.src = data.image;
        })
        .catch(e => { overlay.style.display = 'none'; pmAlert('렌더링 중 오류: ' + e.message, { type: 'danger' }); });
    }

    // ── 렌더링 결과 팝업 ───────────────────────────
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
                            <button class="render-result-apply" id="rrDownload">다운로드</button>
                            <button class="render-result-close" id="rrClose">✕</button>
                        </div>
                    </div>
                    <img class="render-result-img" id="rrImg" src="" alt="render">
                </div>`;
            document.body.appendChild(pop);
            document.getElementById('rrClose').onclick = () => {
                pop.classList.remove('rr-visible');
            };
            document.getElementById('rrDownload').onclick = () => {
                const link = document.createElement('a');
                const base = getExportFilename('png').replace(/\.png$/, '');
                link.download = `${base}_${savedRenders.length}.png`;
                link.href = document.getElementById('rrImg').src;
                link.click();
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
        if (_versionsLoaded) draw();
    }

    let _resizeTimer;
    // ── 다중 썸네일 관리 ─────────────────────────
    const rightSidebar       = document.getElementById('rightSidebar');
    const btnRightSidebarTab = document.getElementById('btnRightSidebarTab');
    const thumbList          = document.getElementById('thumbList');
    const btnAddThumb        = document.getElementById('btnAddThumb');

    btnRightSidebarTab.addEventListener('click', () => {
        if (rightSidebar.classList.contains('collapsed')) showRightSidebar();
        else hideRightSidebar();
    });

    const WALLPAPER_ENGINE  = 'classic';
    const WALLPAPER_API     = '/src/api/wallpapers/';
    // 이미지 목록: [{id, serverId, src, img, filename}]
    let thumbImages   = [];
    let activeThumbId = null;

    const btnClearBg = document.getElementById('btnClearBg');

    function updateClearBgBtn() {
        if (appBackgroundImage) {
            btnClearBg.style.display = 'flex';
        } else {
            btnClearBg.style.display = 'none';
        }
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
            if (found?.serverId) localStorage.setItem(BG_IMAGE_KEY, String(found.serverId));
            else                 localStorage.removeItem(BG_IMAGE_KEY);
        } catch(e) {}

        // 활성 표시 갱신
        thumbList.querySelectorAll('.rp-thumb-item').forEach(el => {
            el.classList.toggle('active', el.dataset.id === String(id));
        });
        updateClearBgBtn();
        draw();
    }

    async function uploadWallpaperToServer(dataUrl, filename, drawingId, versionSavedAt) {
        try {
            const res  = await fetch(WALLPAPER_API + 'upload.php', {
                method: 'POST', headers: _wpHeaders(),
                body: JSON.stringify({ image: dataUrl, filename, engine: WALLPAPER_ENGINE, drawing_id: drawingId || null, version_saved_at: versionSavedAt || null }),
            });
            const data = await res.json();
            if (data.error) { console.warn('배경 업로드 실패:', data.error); return null; }
            return { id: data.id, url: data.url };
        } catch { return null; }
    }

    btnAddThumb.addEventListener('click', () => aiFileUploader.click());

    aiFileUploader.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        files.forEach(file => {
            if (!['image/jpeg', 'image/png'].includes(file.type)) { alert('PNG 또는 JPG 파일만 업로드할 수 있습니다.'); return; }
            if (file.size > 700 * 1024) { alert('파일 크기는 700KB 이하여야 합니다.'); return; }
            const reader = new FileReader();
            reader.onload = function(event) {
                compressImage(event.target.result, async function(dataUrl) {
                    // 로컬 미리보기를 먼저 표시
                    const id = Date.now() + Math.random();
                    const imgObj = new Image();
                    imgObj.src = dataUrl;
                    imgObj.onload = function() {
                        thumbImages.push({ id, serverId: null, src: dataUrl, img: imgObj, filename: file.name });
                        addThumbItem(id, dataUrl, file.name);
                        showRightSidebar();
                        setActiveThumb(id);
                    };

                    // 서버 업로드 후 src를 URL로 교체
                    const result = await uploadWallpaperToServer(dataUrl, file.name, drawingId, _currentVersionSavedAt());
                    if (result) {
                        const thumb = thumbImages.find(t => t.id === id);
                        if (thumb) {
                            thumb.serverId = result.id;
                            thumb.src      = result.url;
                        }
                        if (activeThumbId === id) localStorage.setItem(BG_IMAGE_KEY, String(result.id));
                    }
                });
            };
            reader.readAsDataURL(file);
        });
        aiFileUploader.value = '';
    });



function makeLineKey(cx, cy, normAngle) {
    const perp = cx * Math.sin(normAngle) - cy * Math.cos(normAngle);
    return `${Math.round(normAngle * 1000)}:${Math.round(perp)}`;
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

function snapToNode(cx, cy) {
    if (!lastNodeList.length) return null;
    let best = null, bestDist = Infinity;
    for (const node of lastNodeList) {
        const d = Math.hypot(node.cx - cx, node.cy - cy);
        if (d < bestDist) { bestDist = d; best = node; }
    }
    return (best && bestDist < lastCellSize) ? best : null;
}

let _geoController = null;

async function fetchGeometry(p = null) {
    const body = new URLSearchParams({
        cols:      p ? p.cols                             : txtCols.value,
        outerW:    p ? p.W                                : txtW.value,
        outerH:    p ? p.H                                : txtH.value,
        pungpanH:  p ? (p.pungpan || 0)                  : (document.getElementById('txtPungpan').value || 0),
        pungpanOn: p ? (p.pungpanOn ? '1' : '0')         : (document.getElementById('chkPungpan').checked ? '1' : '0'),
        frameW:    p ? p.frame                            : txtFrame.value,
        frameH:    p ? p.frameH                           : txtFrameH.value,
        slatT:     p ? p.slat                             : txtSlat.value,
        vRatio:    p ? p.vRatio                           : txtRatio.value,
        pattern:   p ? p.pattern                          : `${document.getElementById('txtPatternTop').value}/${document.getElementById('txtPatternMid').value}/${document.getElementById('txtPatternBot').value}`,
        doorType:  p ? p.doorType                         : txtDoorType.value,
        doorCount: p ? p.doorCount                        : txtDoorCount.value,
    });
    const sig = body.toString();
    if (!p) {
        if (_geoCache && _geoCache.sig === sig) return _geoCache.data;
        if (_geoController) _geoController.abort();
        _geoController = new AbortController();
    }
    const controller = p ? new AbortController() : _geoController;
    try {
        const _tok = localStorage.getItem('pmok_auth_token');
        const res = await fetch('api/geometry.php', {
            method: 'POST',
            headers: _tok ? { 'Authorization': 'Bearer ' + _tok } : {},
            body,
            signal: controller.signal,
        });
        const _r = await res.json(); if (!p) _geoCache = { sig, data: _r }; return _r;
    } catch (e) {
        if (e.name === 'AbortError') return null;
        throw e;
    }
}

let _panRaf = null;
let _geoCache = null;
function drawPan() {
    if (_panRaf) return;
    _panRaf = requestAnimationFrame(() => { _panRaf = null; draw(); });
}

async function draw() {
    try {
    const data = await fetchGeometry();
    if (!data) return;
    if (data.error) {
        console.warn('[draw] error from server:', data.error);
        if (data.error.includes('인증') && !window.__pmokAuthPopupShown) {
            window.__pmokAuthPopupShown = true;
            const el = document.getElementById('authModal');
            if (el && window.bootstrap) bootstrap.Modal.getOrCreateInstance(el).show();
        } else if (!data.error.includes('인증')) {
            console.error('[draw] geometry error:', data.error);
        }
        return;
    }
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
    document.getElementById('spFrameHTop').innerText  = s.frameHTop;
    document.getElementById('spTotalDoorW').innerText = s.totalDoorW;

    const overlapCard = document.getElementById('spOverlapCard');
    if (overlapCard) {
        const isSlide = document.getElementById('txtDoorType').value === 'slide';
        overlapCard.style.display = isSlide ? '' : 'none';
        document.getElementById('spOverlap').innerText = s.overlap ?? '';
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

    document.getElementById('spHSlatLen').textContent = p.hSlatLen;
    document.getElementById('spHSlatCnt').textContent = p.hSlatCnt;
    document.getElementById('spVSlatLen').textContent = p.vSlatLen;
    document.getElementById('spVSlatCnt').textContent = p.vSlatCnt;

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
        const s = Math.min(logW / img.width, logH / img.height);
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
    // 배치 좌표: 도면 크기가 바뀌면 코너를 비례 조정
    if (doorCornerPositions && placementNaturalSize &&
        (Math.abs(doorNaturalSize.w - placementNaturalSize.w) > 1 ||
         Math.abs(doorNaturalSize.h - placementNaturalSize.h) > 1)) {
        const sx = doorNaturalSize.w / placementNaturalSize.w;
        const sy = doorNaturalSize.h / placementNaturalSize.h;
        for (const k of ['tl', 'tr', 'br', 'bl']) {
            doorCornerPositions[k] = {
                cx: doorCornerPositions[k].cx * sx,
                cy: doorCornerPositions[k].cy * sy,
            };
        }
        placementNaturalSize = { w: doorNaturalSize.w, h: doorNaturalSize.h };
    }

    // 배치 모드: 오프스크린 캔버스로 리다이렉트
    if (doorCornerPositions) {
        const W = Math.max(1, Math.ceil(doorNaturalSize.w));
        const H = Math.max(1, Math.ceil(doorNaturalSize.h));
        // 줌 수준에 비례한 해상도로 렌더링해 확대 시 흐림 방지 (최대 4096px)
        const renderDpr = Math.min(scaleFactor * dpr, Math.min(4096 / W, 4096 / H));
        const offW = Math.round(W * renderDpr);
        const offH = Math.round(H * renderDpr);
        if (offCanvas.width !== offW || offCanvas.height !== offH) {
            offCanvas.width = offW; offCanvas.height = offH;
        }
        ctx.restore();
        const offCtx = offCanvas.getContext('2d');
        offCtx.clearRect(0, 0, offW, offH);
        offCtx.save();
        offCtx.translate(offW / 2, offH / 2);
        offCtx.scale(renderDpr, renderDpr);
        ctx = offCtx;
    }

    _activeDrawCtx = ctx; // drawCenterLine이 항상 올바른 ctx를 사용하도록

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

        if (d === renderOrder[0]) {
            lastILeft    = toCanvasX(geo.frameW);
            lastITop     = toCanvasY(geo.frameHTop);
            lastIW       = geo.innerW * baseScale;
            lastIH       = geo.innerH * baseScale;
            lastSlatPx   = geo.slatT  * baseScale;
            lastCellSize = geo.cellW  * baseScale;
            lastOLeft     = toCanvasX(0);
            lastOTop      = toCanvasY(0);
            lastBaseScale = baseScale;
            lastDoorWpx   = totalWidth * baseScale;
            lastDoorHpx   = totalH     * baseScale;

            // 라인 편집기 노드: 세로살 중심 × 가로살 중심 교점
            const nodeXs = [geo.frameW];
            for (let i = 1; i < geo.cols; i++) {
                nodeXs.push(geo.frameW + i * (geo.cellW + geo.slatV) - geo.slatV / 2);
            }
            nodeXs.push(geo.frameW + geo.innerW);

            const nodeYs = [geo.frameHTop];
            geo.hBarYs.forEach(by => nodeYs.push(geo.frameHTop + by));
            nodeYs.push(geo.frameHTop + geo.innerH);

            for (const rx of nodeXs) {
                for (const ry of nodeYs) {
                    lastNodeList.push({ cx: toCanvasX(rx), cy: toCanvasY(ry) });
                }
            }
        }

        // ====================================
        // 세로살
        // ====================================

        // 내경 영역으로 클리핑 — innerH 기준
        const clipH = geo.innerH;
        ctx.save();
        ctx.beginPath();
        ctx.rect(
            toCanvasX(geo.frameW),
            toCanvasY(geo.frameHTop),
            geo.innerW * baseScale,
            clipH * baseScale
        );
        ctx.clip();

        // 면 채색 (오른쪽 세로살 + 아래쪽 가로살 포함해서 그림 — 살은 위에 덮여 그려지므로 지운 살 위치만 색상이 보임)
        if (faceColorMap) {
            const stepV = geo.cellW + geo.slatV;
            const cellBounds = getCellRowBounds(geo);
            for (let row = 0; row < cellBounds.length; row++) {
                const cb = cellBounds[row];
                for (let col = 0; col < geo.cols; col++) {
                    const _fc = faceColorMap[`cell:${col}:${row}`] ?? null;
                    if (_fc) {
                        ctx.fillStyle = _fc;
                        ctx.fillRect(
                            toCanvasX(geo.frameW + col * stepV),
                            toCanvasY(geo.frameHTop + cb.y0),
                            stepV * baseScale,
                            (cb.y1 - cb.y0 + geo.slatH) * baseScale
                        );
                    }
                }
            }
        }

        // ====================================
        // 세로살
        // ====================================

        for (let i = 1; i < geo.cols; i++) {

                const cx    = geo.frameW + i * (geo.cellW + geo.slatV) - geo.slatV / 2;
                const left  = cx - geo.slatV / 2;
                const topY  = geo.frameHTop;
                const botY  = geo.frameHTop + geo.innerH;

                // 촉 (항상)
                ctx.fillStyle = Color_Tenon_Fill;
                ctx.fillRect(toCanvasX(left), toCanvasY(topY - geo.tenonDepth), geo.slatV * baseScale, geo.tenonDepth * baseScale);
                ctx.fillRect(toCanvasX(left), toCanvasY(botY), geo.slatV * baseScale, geo.tenonDepth * baseScale);

                const vSegs = geo.vSegBounds;
                for (let j = 0; j < vSegs.length; j++) {
                    const segStartY = topY + vSegs[j].y0;
                    const segEndY   = topY + vSegs[j].y1;
                    const segH      = vSegs[j].y1 - vSegs[j].y0;
                    const segMidY   = (segStartY + segEndY) / 2;
                    const vsCx = toCanvasX(cx), vsCy = toCanvasY(segMidY);
                    const vsKey = `${d}:vs:${i}:${j}`;
                    lastSegMap.set(vsKey, { cx: vsCx, cy: toCanvasY(segStartY), ex: vsCx, ey: toCanvasY(segEndY), mx: vsCx, my: vsCy, normAngle: Math.PI / 2, lineKey: makeLineKey(vsCx, vsCy, Math.PI / 2) });
                    if (deletedSegs.has(vsKey)) continue;

                    ctx.fillStyle = Color_Slat_Fill;
                    ctx.fillRect(toCanvasX(left), toCanvasY(segStartY), geo.slatV * baseScale, segH * baseScale);
                    drawCenterLine(toCanvasX(cx), toCanvasY(segStartY), toCanvasX(cx), toCanvasY(segEndY));
                }
            }

            // ====================================
            // 가로살 (3-5-3 배치)
            // ====================================

            geo.hBarYs.forEach((barY, j) => {
                const ry    = geo.frameHTop + barY;
                const top   = ry - geo.slatH / 2;
                const leftX  = geo.frameW;
                const rightX = geo.frameW + geo.innerW;
                const stepW  = geo.cellW + geo.slatV;

                // 촉 (항상)
                ctx.fillStyle = Color_Tenon_Fill;
                ctx.fillRect(toCanvasX(leftX - geo.tenonDepth), toCanvasY(top), geo.tenonDepth * baseScale, geo.slatH * baseScale);
                ctx.fillRect(toCanvasX(rightX), toCanvasY(top), geo.tenonDepth * baseScale, geo.slatH * baseScale);

                for (let i = 0; i < geo.cols; i++) {
                    const segStartX = leftX + i * stepW;
                    const segEndX   = leftX + (i + 1) * stepW;
                    const segMidX   = (segStartX + segEndX) / 2;
                    const hsCy = toCanvasY(ry);
                    const hsKey = `${d}:hs:${j}:${i}`;
                    lastSegMap.set(hsKey, { cx: toCanvasX(segStartX), cy: hsCy, ex: toCanvasX(segEndX), ey: hsCy, mx: toCanvasX(segMidX), my: hsCy, normAngle: 0, lineKey: makeLineKey(toCanvasX(segMidX), hsCy, 0) });
                    if (deletedSegs.has(hsKey)) continue;

                    ctx.fillStyle = Color_Slat_Fill;
                    ctx.fillRect(toCanvasX(segStartX), toCanvasY(top), stepW * baseScale, geo.slatH * baseScale);
                    drawCenterLine(toCanvasX(segStartX), toCanvasY(ry), toCanvasX(segEndX), toCanvasY(ry));
                }
            });

        // 클리핑 해제
        ctx.restore();

    }   // ← 1차 루프 끝

    // ── 편집 반영 부재 목록 최종 업데이트 ──────
    {
        const tenonLen = 2 * geo.slatT;
        const pxToMm   = geo.innerW / lastIW;

        // 내경 범위 밖 세그먼트 제외
        const lineGroups = new Map();
        const EPS = lastCellSize;
        for (const [segKey, seg] of lastSegMap) {
            if (segKey.startsWith('added:')) continue;
            if (seg.mx < lastILeft - EPS || seg.mx > lastILeft + lastIW + EPS) continue;
            if (seg.my < lastITop  - EPS || seg.my > lastITop  + lastIH + EPS) continue;
            if (!lineGroups.has(seg.lineKey)) lineGroups.set(seg.lineKey, []);
            lineGroups.get(seg.lineKey).push({ segKey, mx: seg.mx, my: seg.my, normAngle: seg.normAngle });
        }

        let adjHSlatCnt = parseInt(p.hSlatCnt) || 0;
        let adjVSlatCnt = parseInt(p.vSlatCnt) || 0;
        const extraPieces = [];

        for (const [, segs] of lineGroups) {
            const normAngle  = segs[0].normAngle;
            const isHSlat    = normAngle < 0.05;
            const isVSlat    = Math.abs(normAngle - Math.PI / 2) < 0.05;
            const ldx = Math.cos(normAngle), ldy = Math.sin(normAngle);

            const vSegs = segs.map(s => ({
                segKey:    s.segKey,
                isDeleted: deletedSegs.has(s.segKey),
                pos:       s.mx * ldx + s.my * ldy,
            })).sort((a, b) => a.pos - b.pos);

            if (!vSegs.some(vs => vs.isDeleted)) continue;

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

            const fullLen = runLen(0, vSegs.length - 1);
            if (isHSlat) {
                adjHSlatCnt = Math.max(0, adjHSlatCnt - 1);
            } else if (isVSlat) {
                adjVSlatCnt = Math.max(0, adjVSlatCnt - 1);
            }

            let runStart = -1;
            for (let i = 0; i <= vSegs.length; i++) {
                if (i < vSegs.length && !vSegs[i].isDeleted) {
                    if (runStart < 0) runStart = i;
                } else if (runStart >= 0) {
                    extraPieces.push({ len: runLen(runStart, i - 1), isHSlat, isVSlat });
                    runStart = -1;
                }
            }
        }

        extraPieces.forEach(({ len, isHSlat, isVSlat }) => {
            if (isHSlat) {
                adjHSlatCnt++;
            } else if (isVSlat) {
                adjVSlatCnt++;
            }
        });

        document.getElementById('spHSlatCnt').textContent = Math.max(0, adjHSlatCnt) + '개';
        document.getElementById('spVSlatCnt').textContent = Math.max(0, adjVSlatCnt) + '개';

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
            lastSegMap.set(`added:${idx}`, { cx: p1.x, cy: p1.y, ex: p2.x, ey: p2.y, mx: (p1.x + p2.x) / 2, my: (p1.y + p2.y) / 2, normAngle: 0 });
            ctx.beginPath();
            ctx.moveTo(p1.x, p1.y);
            ctx.lineTo(p2.x, p2.y);
            ctx.stroke();
        });
        ctx.restore();
    }

    if (lineEditMode === 'add' && addLineStart) {
        const pt = normToCtx(addLineStart.nx, addLineStart.ny);
        ctx.save();
        ctx.fillStyle = '#3A8C82';
        ctx.beginPath();
        ctx.arc(pt.x, pt.y, lastSlatPx * 1.5, 0, Math.PI * 2);
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

    if (doorCornerPositions) {
        // 오프스크린 컨텍스트 복원 후 메인 캔버스로 전환
        ctx.restore();
        ctx = canvas.getContext('2d');
        // 4코너 투시 변환으로 메인 캔버스에 합성
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        const _c = getOverlayCorners();
        drawPerspectiveQuad(ctx, offCanvas, _c.tl, _c.tr, _c.br, _c.bl);
        if (false && showDimensions && lastDoorWpx > 0) {
            const _sf  = scaleFactor;
            const GAP  = 24 / _sf, TICK = 5 / _sf, ITICK = 12 / _sf;
            const R    = 3  / _sf, lw   = 1 / _sf, fs    = 14 / _sf;
            const dL = offsetX, dR = offsetX + totalWidth * baseScale;
            const dT = offsetY, dB = offsetY + totalH * baseScale;
            ctx.save();
            ctx.translate(logW / 2 + panX, logH / 2 + panY);
            ctx.scale(scaleFactor, scaleFactor);
            ctx.strokeStyle = 'rgba(50,50,50,0.7)';
            ctx.fillStyle   = 'rgba(50,50,50,0.7)';
            ctx.lineWidth   = lw;
            ctx.font        = `${fs}px -apple-system,sans-serif`;
            const _dot2 = (x, y) => { ctx.beginPath(); ctx.arc(x, y, R, 0, Math.PI * 2); ctx.fill(); };
            const bY = dB + GAP;
            ctx.beginPath();
            ctx.moveTo(dL, bY - ITICK); ctx.lineTo(dL, bY + TICK);
            ctx.moveTo(dR, bY - ITICK); ctx.lineTo(dR, bY + TICK);
            ctx.moveTo(dL, bY);         ctx.lineTo(dR, bY);
            ctx.stroke();
            _dot2(dL, bY); _dot2(dR, bY);
            ctx.textAlign = 'center'; ctx.textBaseline = 'top';
            ctx.fillText(`${Math.round(totalWidth)}mm`, (dL + dR) / 2, bY + TICK + 3 / _sf);
            const rX = dR + GAP;
            ctx.beginPath();
            ctx.moveTo(rX - ITICK, dT); ctx.lineTo(rX + TICK, dT);
            ctx.moveTo(rX - ITICK, dB); ctx.lineTo(rX + TICK, dB);
            ctx.moveTo(rX, dT);         ctx.lineTo(rX, dB);
            ctx.stroke();
            _dot2(rX, dT); _dot2(rX, dB);
            ctx.textAlign = 'left'; ctx.textBaseline = 'middle';
            ctx.fillText(`${Math.round(totalH)}mm`, rX + TICK + 3 / _sf, (dT + dB) / 2);
            ctx.restore();
        }
    } else {
        if (showDimensions && totalWidth > 0) {
            const _sf  = scaleFactor;
            const GAP  = 24 / _sf, TICK = 5 / _sf, ITICK = 12 / _sf;
            const R    = 3  / _sf, lw   = 1 / _sf, fs    = 14 / _sf;
            const dL = offsetX, dR = offsetX + totalWidth * baseScale;
            const dT = offsetY, dB = offsetY + totalH * baseScale;
            ctx.save();
            ctx.strokeStyle = 'rgba(50,50,50,0.7)';
            ctx.fillStyle   = 'rgba(50,50,50,0.7)';
            ctx.lineWidth   = lw;
            ctx.font        = `${fs}px -apple-system,sans-serif`;
            const _dot = (x, y) => { ctx.beginPath(); ctx.arc(x, y, R, 0, Math.PI * 2); ctx.fill(); };
            const bY = dB + GAP;
            ctx.beginPath();
            ctx.moveTo(dL, bY - ITICK); ctx.lineTo(dL, bY + TICK);
            ctx.moveTo(dR, bY - ITICK); ctx.lineTo(dR, bY + TICK);
            ctx.moveTo(dL, bY);         ctx.lineTo(dR, bY);
            ctx.stroke();
            _dot(dL, bY); _dot(dR, bY);
            ctx.textAlign = 'center'; ctx.textBaseline = 'top';
            ctx.fillText(`${Math.round(totalWidth)}mm`, (dL + dR) / 2, bY + TICK + 3 / _sf);
            const rX = dR + GAP;
            ctx.beginPath();
            ctx.moveTo(rX - ITICK, dT); ctx.lineTo(rX + TICK, dT);
            ctx.moveTo(rX - ITICK, dB); ctx.lineTo(rX + TICK, dB);
            ctx.moveTo(rX, dT);         ctx.lineTo(rX, dB);
            ctx.stroke();
            _dot(rX, dT); _dot(rX, dB);
            ctx.textAlign = 'left'; ctx.textBaseline = 'middle';
            ctx.fillText(`${Math.round(totalH)}mm`, rX + TICK + 3 / _sf, (dT + dB) / 2);
            ctx.restore();
        }
        ctx.restore();
    }

    if (placementMode && doorNaturalSize.w > 0) {
        const c = getOverlayCorners();
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        // 외곽선: hover 여부에 따라 밝기 조절
        ctx.strokeStyle = handlesVisible ? 'rgba(58,140,130,0.9)' : 'rgba(58,140,130,0.35)';
        ctx.lineWidth   = handlesVisible ? 2 : 1.5;
        ctx.setLineDash([6, 4]);
        ctx.beginPath();
        ctx.moveTo(c.tl.x, c.tl.y); ctx.lineTo(c.tr.x, c.tr.y);
        ctx.lineTo(c.br.x, c.br.y); ctx.lineTo(c.bl.x, c.bl.y);
        ctx.closePath(); ctx.stroke();
        ctx.setLineDash([]);

        if (handlesVisible) {
            // 4개 코너 핸들 (모두 사각형)
            [c.tl, c.tr, c.br, c.bl].forEach(({ x, y }) => {
                ctx.fillStyle   = '#fff';
                ctx.strokeStyle = '#3A8C82';
                ctx.lineWidth   = 2.5;
                ctx.fillRect(x - 7, y - 7, 14, 14);
                ctx.strokeRect(x - 7, y - 7, 14, 14);
            });

            // 중앙 이동 핸들 (원 + 십자 화살표)
            const { x: cx, y: cy } = c.center;
            ctx.fillStyle   = 'rgba(255,255,255,0.92)';
            ctx.strokeStyle = '#3A8C82';
            ctx.lineWidth   = 2;
            ctx.beginPath();
            ctx.arc(cx, cy, 14, 0, Math.PI * 2);
            ctx.fill(); ctx.stroke();

            ctx.strokeStyle = '#3A8C82';
            ctx.lineWidth   = 1.8;
            ctx.lineCap     = 'round';
            const a = 8;
            // 위
            ctx.beginPath(); ctx.moveTo(cx, cy - 3); ctx.lineTo(cx, cy - a);
            ctx.lineTo(cx - 3, cy - a + 3); ctx.moveTo(cx, cy - a); ctx.lineTo(cx + 3, cy - a + 3); ctx.stroke();
            // 아래
            ctx.beginPath(); ctx.moveTo(cx, cy + 3); ctx.lineTo(cx, cy + a);
            ctx.lineTo(cx - 3, cy + a - 3); ctx.moveTo(cx, cy + a); ctx.lineTo(cx + 3, cy + a - 3); ctx.stroke();
            // 왼쪽
            ctx.beginPath(); ctx.moveTo(cx - 3, cy); ctx.lineTo(cx - a, cy);
            ctx.lineTo(cx - a + 3, cy - 3); ctx.moveTo(cx - a, cy); ctx.lineTo(cx - a + 3, cy + 3); ctx.stroke();
            // 오른쪽
            ctx.beginPath(); ctx.moveTo(cx + 3, cy); ctx.lineTo(cx + a, cy);
            ctx.lineTo(cx + a - 3, cy - 3); ctx.moveTo(cx + a, cy); ctx.lineTo(cx + a - 3, cy + 3); ctx.stroke();

            // 스케일 핸들 (상단 외부)
            const th = getTransformHandlePos();
            if (th) {
                ctx.setLineDash([]);
                ctx.fillStyle = '#fff';
                ctx.strokeStyle = '#3A8C82';
                ctx.lineWidth = 2;
                ctx.beginPath(); ctx.arc(th.x, th.y, 10, 0, Math.PI * 2); ctx.fill(); ctx.stroke();
                const hx = th.x, hy = th.y, as = 5;
                ctx.strokeStyle = '#3A8C82'; ctx.lineWidth = 1.8; ctx.lineCap = 'round';
                ctx.beginPath(); ctx.moveTo(hx, hy - as - 1); ctx.lineTo(hx, hy + as + 1); ctx.stroke();
                ctx.beginPath(); ctx.moveTo(hx - 3, hy - as + 2); ctx.lineTo(hx, hy - as - 1); ctx.lineTo(hx + 3, hy - as + 2); ctx.stroke();
                ctx.beginPath(); ctx.moveTo(hx - 3, hy + as - 2); ctx.lineTo(hx, hy + as + 1); ctx.lineTo(hx + 3, hy + as - 2); ctx.stroke();
            }
        }
    }
    if (!_exportCanvas || _exportCanvas.width !== canvas.width || _exportCanvas.height !== canvas.height) {
        _exportCanvas = document.createElement('canvas');
        _exportCanvas.width = canvas.width;
        _exportCanvas.height = canvas.height;
    }
    const _ec = _exportCanvas.getContext('2d');
    _ec.clearRect(0, 0, _exportCanvas.width, _exportCanvas.height);
    _ec.drawImage(canvas, 0, 0);
    drawRulers();
    } catch(e) { console.error('[draw] EXCEPTION:', e); }
}

    function drawDimensions() {
        if (!showDimensions) return;
        if (!lastBaseScale || !lastDoorWpx || !lastDoorHpx) return;
        const rCtx = canvas.getContext('2d');
        const ox = logW / 2 + panX + lastOLeft * scaleFactor;
        const oy = logH / 2 + panY + lastOTop  * scaleFactor;
        const dW = lastDoorWpx * scaleFactor;
        const dH = lastDoorHpx * scaleFactor;
        const wMm = Math.round(lastDoorWpx / lastBaseScale);
        const hMm = Math.round(lastDoorHpx / lastBaseScale);
        rCtx.save();
        rCtx.setTransform(window.devicePixelRatio||1, 0, 0, window.devicePixelRatio||1, 0, 0);
        const GAP=24, TICK=5, ITICK=12, R=3;
        const color = 'rgba(50,50,50,0.7)';
        rCtx.strokeStyle = color; rCtx.fillStyle = color;
        rCtx.lineWidth = 1; rCtx.font = '17px -apple-system,sans-serif';
        function dot(x,y){rCtx.beginPath();rCtx.arc(x,y,R,0,Math.PI*2);rCtx.fill();}
        const bY = oy+dH+GAP;
        rCtx.beginPath();
        rCtx.moveTo(ox,bY-ITICK);rCtx.lineTo(ox,bY+TICK);
        rCtx.moveTo(ox+dW,bY-ITICK);rCtx.lineTo(ox+dW,bY+TICK);
        rCtx.moveTo(ox,bY);rCtx.lineTo(ox+dW,bY);
        rCtx.stroke(); dot(ox,bY); dot(ox+dW,bY);
        rCtx.textAlign='center';rCtx.textBaseline='top';
        rCtx.fillText(wMm+'mm',ox+dW/2,bY+TICK+3);
        const rX = ox+dW+GAP;
        rCtx.beginPath();
        rCtx.moveTo(rX-ITICK,oy);rCtx.lineTo(rX+TICK,oy);
        rCtx.moveTo(rX-ITICK,oy+dH);rCtx.lineTo(rX+TICK,oy+dH);
        rCtx.moveTo(rX,oy);rCtx.lineTo(rX,oy+dH);
        rCtx.stroke(); dot(rX,oy); dot(rX,oy+dH);
        rCtx.textAlign='left';rCtx.textBaseline='middle';
        rCtx.fillText(hMm+'mm',rX+TICK+3,oy+dH/2);
        rCtx.restore();
    }

    function drawRulers() {
        const rCtx = canvas.getContext('2d');
        const rDpr = window.devicePixelRatio || 1;
        if (!lastBaseScale) return;

        const R    = 22;
        const mmPx = lastBaseScale * scaleFactor;

        let step;
        if      (mmPx >= 8)   step = 1;
        else if (mmPx >= 3)   step = 5;
        else if (mmPx >= 1)   step = 10;
        else if (mmPx >= 0.4) step = 50;
        else                  step = 100;

        const ox = logW / 2 + panX + lastOLeft * scaleFactor;
        const oy = logH / 2 + panY + lastOTop  * scaleFactor;

        rCtx.save();
        rCtx.setTransform(rDpr, 0, 0, rDpr, 0, 0);

        const BG_IN  = 'rgba(150,150,150,0.95)';
        const BG_OUT = 'rgba(173,173,173,0.95)';
        const LINE   = 'rgba(255,255,255,0.7)';
        const LBL    = '#ffffff';
        const ZERO   = '#3A8C82';

        // 문 범위 (스크린 좌표)
        const doorL = ox;
        const doorR = ox + lastDoorWpx * scaleFactor;
        const doorT = oy;
        const doorB = oy + lastDoorHpx * scaleFactor;

        // 가로 눈금자 — 바깥 진하게, 문 범위 밝게
        rCtx.fillStyle = BG_OUT;
        rCtx.fillRect(R, 0, logW - R, R);
        const hL = Math.max(R, doorL), hR = Math.min(logW, doorR);
        if (hR > hL) { rCtx.fillStyle = BG_IN; rCtx.fillRect(hL, R - 8, hR - hL, 8); }
        rCtx.strokeStyle = '#ccc'; rCtx.lineWidth = 0.5;
        rCtx.beginPath(); rCtx.moveTo(R, R); rCtx.lineTo(logW, R); rCtx.stroke();

        const xS = Math.ceil((R - ox) / mmPx / step) * step;
        const xE = Math.floor((logW - ox) / mmPx / step) * step;
        for (let mm = xS; mm <= xE; mm += step) {
            const x = ox + mm * mmPx;
            if (x < R || x > logW) continue;
            rCtx.strokeStyle = mm === 0 ? ZERO : LINE;
            rCtx.lineWidth   = mm === 0 ? 1 : 0.5;
            rCtx.beginPath(); rCtx.moveTo(x, R - 8); rCtx.lineTo(x, R); rCtx.stroke();
            rCtx.fillStyle    = mm === 0 ? ZERO : LBL;
            rCtx.font         = '10px sans-serif';
            rCtx.textBaseline = 'top';
            rCtx.textAlign    = mm === 0 ? 'left' : 'center';
            rCtx.fillText(mm, mm === 0 ? x + 2 : x, 6);
        }

        // 세로 눈금자 — 바깥 진하게, 문 범위 밝게
        rCtx.fillStyle = BG_OUT;
        rCtx.fillRect(0, R, R, logH - R);
        const vT = Math.max(R, doorT), vB = Math.min(logH, doorB);
        if (vB > vT) { rCtx.fillStyle = BG_IN; rCtx.fillRect(R - 8, vT, 8, vB - vT); }
        rCtx.strokeStyle = '#ccc'; rCtx.lineWidth = 0.5;
        rCtx.beginPath(); rCtx.moveTo(R, R); rCtx.lineTo(R, logH); rCtx.stroke();

        const yS = Math.ceil((R - oy) / mmPx / step) * step;
        const yE = Math.floor((logH - oy) / mmPx / step) * step;
        for (let mm = yS; mm <= yE; mm += step) {
            const y = oy + mm * mmPx;
            if (y < R || y > logH) continue;
            rCtx.strokeStyle = mm === 0 ? ZERO : LINE;
            rCtx.lineWidth   = mm === 0 ? 1 : 0.5;
            rCtx.beginPath(); rCtx.moveTo(R - 8, y); rCtx.lineTo(R, y); rCtx.stroke();
            rCtx.save();
            rCtx.translate(R / 2, y);
            rCtx.rotate(-Math.PI / 2);
            rCtx.fillStyle    = mm === 0 ? ZERO : LBL;
            rCtx.font         = '10px sans-serif';
            rCtx.textAlign    = 'center';
            rCtx.textBaseline = 'middle';
            rCtx.fillText(mm, 0, 0);
            rCtx.restore();
        }

        // 코너 블록
        rCtx.fillStyle = BG_OUT;
        rCtx.fillRect(0, 0, R, R);
        rCtx.strokeStyle = '#ddd'; rCtx.lineWidth = 0.5;
        rCtx.beginPath();
        rCtx.moveTo(R, 0); rCtx.lineTo(R, R); rCtx.lineTo(0, R);
        rCtx.stroke();

        rCtx.restore();
    }

    function drawCenterLine(x1, y1, x2, y2) {
        const c = _activeDrawCtx;
        if (!c) return;
        c.save();
        c.strokeStyle = 'rgba(255, 0, 0, 0.4)';
        c.lineWidth = 0.5;
        c.setLineDash([4, 3]);
        c.beginPath();
        c.moveTo(x1, y1);
        c.lineTo(x2, y2);
        c.stroke();
        c.restore();
    }

    function clipLineToRect(x1, y1, x2, y2, rx1, ry1, rx2, ry2) {
        let t0 = 0,
            t1 = 1;
        let dx = x2 - x1,
            dy = y2 - y1;
        let p = [-dx, dx, -dy, dy];
        let q = [x1 - rx1, rx2 - x1, y1 - ry1, ry2 - y1];
        for (let i = 0; i < 4; i++) {
            if (p[i] === 0 && q[i] < 0) return null;
            let r = q[i] / p[i];
            if (p[i] < 0) {
                if (r > t1) return null;
                if (r > t0) t0 = r;
            } else if (p[i] > 0) {
                if (r < t0) return null;
                if (r < t1) t1 = r;
            }
        }
        return {
            x1: x1 + t0 * dx,
            y1: y1 + t0 * dy,
            x2: x1 + t1 * dx,
            y2: y1 + t1 * dy
        };
    }

    function drawDimensionArrow(x1, y1, x2, y2) {
        ctx.save();
        ctx.strokeStyle = ctx.fillStyle;
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(x1, y1);
        ctx.lineTo(x2, y2);
        ctx.stroke();
        let angle = Math.atan2(y2 - y1, x2 - x1);
        ctx.beginPath();
        ctx.moveTo(x1, y1);
        ctx.lineTo(x1 + 6 * Math.cos(angle + Math.PI / 6), y1 + 6 * Math.sin(angle + Math.PI / 6));
        ctx.lineTo(x1 + 6 * Math.cos(angle - Math.PI / 6), y1 + 6 * Math.sin(angle - Math.PI / 6));
        ctx.fill();
        ctx.beginPath();
        ctx.moveTo(x2, y2);
        ctx.lineTo(x2 - 6 * Math.cos(angle + Math.PI / 6), y2 - 6 * Math.sin(angle + Math.PI / 6));
        ctx.lineTo(x2 - 6 * Math.cos(angle - Math.PI / 6), y2 - 6 * Math.sin(angle - Math.PI / 6));
        ctx.fill();
        ctx.restore();
    }

    function updateOverlayFromCorners() {
        // 투시 변환 렌더링으로 변경되어 doorOverlay 행렬 계산 불필요
        // (doorSave/restore 호환성을 위해 유지)
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
        // 상단 에지 안쪽 법선 방향 (에지 방향을 +90° 회전)
        const perpX = -edgeDy / edgeLen;
        const perpY = edgeDx / edgeLen;
        const OFFSET = 36;
        return { x: topMidX + perpX * OFFSET, y: topMidY + perpY * OFFSET };
    }

    function getHitOverlayCorner(clientX, clientY) {
        if (!placementMode || !doorCornerPositions) return null;
        const rect = canvas.getBoundingClientRect();
        const mx = (clientX - rect.left) * (logW / rect.width);
        const my = (clientY - rect.top)  * (logH / rect.height);
        // transform 핸들 우선 감지
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

    container.addEventListener('mousedown', function(e) {
        if (e.target.closest('.canvas-controls')) return;
        if (lineEditMode) { handleEditClick(e); return; }
        if (facePaintMode) {
            facePaintIsDown = true;
            const coord = screenToCtxCoord(e.clientX, e.clientY);
            paintFaceCell(coord.x, coord.y, e.button === 2);
            return;
        }
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
        if (facePaintIsDown && facePaintMode) {
            const coord = screenToCtxCoord(e.clientX, e.clientY);
            paintFaceCell(coord.x, coord.y, false);
            return;
        }
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
                const nTl = { cx: sp.tl.cx + dcx, cy: sp.tl.cy + dcy };
                doorCornerPositions.tl = nTl;
                doorCornerPositions.tr = { cx: sp.br.cx,  cy: nTl.cy   };
                doorCornerPositions.br = { cx: sp.br.cx,  cy: sp.br.cy };
                doorCornerPositions.bl = { cx: nTl.cx,    cy: sp.br.cy };
            } else if (corner === 'tr') {
                const nTr = { cx: sp.tr.cx + dcx, cy: sp.tr.cy + dcy };
                doorCornerPositions.tl = { cx: sp.bl.cx,  cy: nTr.cy   };
                doorCornerPositions.tr = nTr;
                doorCornerPositions.br = { cx: nTr.cx,    cy: sp.bl.cy };
                doorCornerPositions.bl = { cx: sp.bl.cx,  cy: sp.bl.cy };
            } else if (corner === 'br') {
                const nBr = { cx: sp.br.cx + dcx, cy: sp.br.cy + dcy };
                doorCornerPositions.tl = { cx: sp.tl.cx,  cy: sp.tl.cy };
                doorCornerPositions.tr = { cx: nBr.cx,    cy: sp.tl.cy };
                doorCornerPositions.br = nBr;
                doorCornerPositions.bl = { cx: sp.tl.cx,  cy: nBr.cy   };
            } else if (corner === 'bl') {
                const nBl = { cx: sp.bl.cx + dcx, cy: sp.bl.cy + dcy };
                doorCornerPositions.tl = { cx: nBl.cx,    cy: sp.tr.cy };
                doorCornerPositions.tr = { cx: sp.tr.cx,  cy: sp.tr.cy };
                doorCornerPositions.br = { cx: sp.tr.cx,  cy: nBl.cy   };
                doorCornerPositions.bl = nBl;
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

            updateOverlayFromCorners();
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
        drawPan();
    });
    window.addEventListener('mouseup', function() {
        facePaintIsDown = false;
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
        const willActivate = !placementMode;
        deactivateAllModes();
        if (willActivate) {
            placementMode = true;
            const W = doorNaturalSize.w, H = doorNaturalSize.h;
            const sizeChanged = !placementNaturalSize ||
                Math.abs(W - placementNaturalSize.w) > 1 ||
                Math.abs(H - placementNaturalSize.h) > 1;
            if (!doorCornerPositions || sizeChanged) {
                placementNaturalSize = { w: W, h: H };
                doorCornerPositions = {
                    tl: { cx: -W/2, cy: -H/2 },
                    tr: { cx:  W/2, cy: -H/2 },
                    br: { cx:  W/2, cy:  H/2 },
                    bl: { cx: -W/2, cy:  H/2 },
                };
            }
            updateOverlayFromCorners();
            document.getElementById('btnScale').classList.add('cv-btn-active');
        }
        updateResetPlacementBtn();
        draw();
    });

    const btnResetPlacement = document.getElementById('btnResetPlacement');
    btnResetPlacement.addEventListener('click', () => {
        placementMode = false;
        doorCornerPositions = null;
        placementNaturalSize = null;
        canvas.style.cursor = panMode ? 'grab' : 'default';
        document.getElementById('btnScale').classList.remove('cv-btn-active');
        updateResetPlacementBtn();
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

    // ── 편집 버튼 ──────────────────────────────────
    const CURSOR_ERASER = `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'%3E%3Crect x='5' y='10' width='14' height='9' rx='2' fill='%23fff' stroke='%23555' stroke-width='1.5'/%3E%3Crect x='5' y='10' width='6' height='9' rx='0' fill='%23f87171' stroke='none'/%3E%3Crect x='5' y='10' width='6' height='9' rx='0' fill='none' stroke='%23555' stroke-width='1.5'/%3E%3Cline x1='5' y1='19' x2='19' y2='19' stroke='%23555' stroke-width='1.5'/%3E%3C/svg%3E") 4 20, crosshair`;
    const CURSOR_PEN    = `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'%3E%3Cpath d='M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z' fill='%23fff' stroke='%23555' stroke-width='1.5' stroke-linejoin='round'/%3E%3C/svg%3E") 2 22, crosshair`;

    function distToSeg(px, py, seg) {
        const { cx, cy, ex, ey } = seg;
        const dx = ex - cx, dy = ey - cy;
        const lenSq = dx * dx + dy * dy;
        if (lenSq === 0) return Math.hypot(px - cx, py - cy);
        const t = Math.max(0, Math.min(1, ((px - cx) * dx + (py - cy) * dy) / lenSq));
        return Math.hypot(px - cx - t * dx, py - cy - t * dy);
    }

    function getCellRowBounds(g) {
        const sortedBars = [...g.hBarYs].sort((a, b) => a - b);
        const bounds = [];
        let prev = 0;
        for (const barY of sortedBars) {
            const barTop = barY - g.slatH / 2;
            if (barTop > prev + 0.5) bounds.push({ y0: prev, y1: barTop });
            prev = barY + g.slatH / 2;
        }
        if (g.innerH > prev + 0.5) bounds.push({ y0: prev, y1: g.innerH });
        return bounds;
    }

    function hitTestCell(cx, cy) {
        if (!geo.cols || !lastBaseScale || !geo.hBarYs) return null;
        const relX = cx - lastILeft, relY = cy - lastITop;
        if (relX < 0 || relY < 0 || relX > lastIW || relY > lastIH) return null;
        const stepVpx = (geo.cellW + geo.slatV) * lastBaseScale;
        const col = Math.floor(relX / stepVpx);
        if (col < 0 || col >= geo.cols) return null;
        const relYmm = relY / lastBaseScale;
        const cellBounds = getCellRowBounds(geo);
        // 각 셀이 아래쪽 살 영역까지 커버 → 살 위치 클릭도 인접 셀로 매핑됨
        const row = cellBounds.findIndex(b => relYmm >= b.y0 && relYmm < b.y1 + geo.slatH);
        if (row < 0) return null;
        return `cell:${col}:${row}`;
    }

    function paintFaceCell(cx, cy, isErase) {
        const key = hitTestCell(cx, cy);
        if (!key) return;
        if (isErase) {
            if (faceColorMap) {
                delete faceColorMap[key];
                if (!Object.keys(faceColorMap).length) faceColorMap = null;
            }
        } else {
            if (!faceColorMap) faceColorMap = {};
            faceColorMap[key] = faceColorUI.getCurrentHex();
        }
        faceColorUI.updateClearBtn(!!faceColorMap);
        draw();
    }

    const btnFacePaint = document.getElementById('btnFacePaint');
    if (btnFacePaint) {
        btnFacePaint.addEventListener('click', () => {
            facePaintMode = !facePaintMode;
            btnFacePaint.classList.toggle('cv-btn-active', facePaintMode);
            canvas.style.cursor = facePaintMode ? 'crosshair' : (panMode ? 'grab' : 'default');
        });
    }
    canvas.addEventListener('contextmenu', e => { if (facePaintMode) e.preventDefault(); });

    function handleEditClick(e) {
        const coord = screenToCtxCoord(e.clientX, e.clientY);

        if (lineEditMode === 'delete') {
            let bestKey = null, bestDist = Infinity;
            const threshold = Math.max(lastSlatPx * 4, lastCellSize * 0.35);
            for (const [key, seg] of lastSegMap) {
                const dist = distToSeg(coord.x, coord.y, seg);
                if (dist < bestDist) { bestDist = dist; bestKey = key; }
            }
            if (!bestKey || bestDist > threshold) return;
            if (bestKey.startsWith('added:')) {
                const idx = parseInt(bestKey.split(':')[1]);
                addedLines.splice(idx, 1);
            } else {
                if (deletedSegs.has(bestKey)) deletedSegs.delete(bestKey);
                else deletedSegs.add(bestKey);
            }
            draw();
            return;
        }

        if (lineEditMode === 'add') {
            const snapped = snapToNode(coord.x, coord.y);
            if (!snapped) return;
            const norm = ctxToNorm(snapped.cx, snapped.cy);
            if (!addLineStart) {
                addLineStart = norm;
                draw();
            } else {
                if (Math.abs(norm.nx - addLineStart.nx) < 0.001 && Math.abs(norm.ny - addLineStart.ny) < 0.001) return;
                addedLines.push({ nx1: addLineStart.nx, ny1: addLineStart.ny, nx2: norm.nx, ny2: norm.ny });
                addLineStart = null;
                draw();
            }
        }
    }

    document.getElementById('btnPan').addEventListener('click', setPanMode);
    document.getElementById('btnEditDelete').addEventListener('click', () => setEditMode('delete'));
    document.getElementById('btnEditAdd').addEventListener('click', () => setEditMode('add'));
    document.getElementById('btnEditClear').addEventListener('click', () => {
        pmConfirm('편집 내용을 모두 초기화하시겠습니까?', () => {
            deletedSegs.clear();
            addedLines   = [];
            addLineStart = null;
            draw();
        });
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

    // ── 슬라이더 ↔ 인풋창 양방향 동기화 ──────────────────

    const syncPairs = [
        { range: txtW,      num: document.getElementById('numW'),       min: 400,  max: 3000 },
        { range: txtH,      num: document.getElementById('numH'),       min: 400,  max: 3000 },
        { range: txtCols,   num: document.getElementById('numCols'),    min: 2,    max: 30   },
        { range: txtFrame,  num: document.getElementById('numFrame'),   min: 20,   max: 150  },
        { range: txtFrameH, num: document.getElementById('numFrameH'),  min: 20,   max: 150  },
        { range: txtSlat,   num: document.getElementById('numSlat'),    min: 8,    max: 35   },
        { range: document.getElementById('txtPungpan'), num: document.getElementById('numPungpan'), min: 0, max: 600 },
        { range: txtRatio, num: document.getElementById('numRatio'), min: 1.0, max: 3.0, step: 0.1 },
    ];

    document.getElementById('txtPatternTop').addEventListener('change', draw);
    document.getElementById('txtPatternMid').addEventListener('change', draw);
    document.getElementById('txtPatternBot').addEventListener('change', draw);
    document.getElementById('chkDimension').addEventListener('change', e => { showDimensions = e.target.checked; draw(); });

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

    txtDoorType.addEventListener('input', updateDoorCountOptions);
    txtDoorCount.addEventListener('input', draw);
    updateDoorCountOptions();

    // 작성일 / 수정일
    const CREATED_KEY       = 'pmok_classic_created';
    const MODIFIED_KEY      = 'pmok_classic_modified';
    const VERSIONS_KEY      = 'pmok_classic_versions';
    const BG_IMAGE_KEY      = 'pmok_classic_bg';
    const CURRENT_TITLE_KEY = 'pmok_classic_current_title';
    const NAME_KEY          = 'pmok_classic_name';
    const MAX_VERSIONS      = 20;

    let workAccum = 0;
    let workStart = Date.now();
    let drawingId = null;

    function pauseWorkTimer() { workAccum += Math.floor((Date.now() - workStart) / 1000); }
    function resumeWorkTimer() { workStart = Date.now(); }

    function captureThumbnail() {
        const dpr = window.devicePixelRatio || 1;
        const R   = Math.round(22 * dpr);
        const sw  = canvas.width  - R;
        const sh  = canvas.height - R;
        const W   = 320;
        const H   = Math.round(W * sh / sw);
        const tmp = document.createElement('canvas');
        tmp.width = W; tmp.height = H;
        const tctx = tmp.getContext('2d');
        tctx.fillStyle = '#E5E7EA';
        tctx.fillRect(0, 0, W, H);
        tctx.drawImage(canvas, R, R, sw, sh, 0, 0, W, H);
        return tmp.toDataURL('image/jpeg', 0.65);
    }

    // 썸네일 + 배경 이미지 복원 (서버에서 로드)
    async function restoreThumbs() {
        // 구버전 BG_IMAGE_KEY 값(숫자 id가 아닌 경우) 정리
        const _bgRaw = localStorage.getItem(BG_IMAGE_KEY);
        if (_bgRaw && !/^\d+$/.test(_bgRaw)) localStorage.removeItem(BG_IMAGE_KEY);

        // 기존 썸네일 초기화
        thumbImages = [];
        thumbList.innerHTML = '';
        appBackgroundImage = null;
        activeThumbId = null;
        localStorage.removeItem(BG_IMAGE_KEY);
        updateClearBgBtn();
        if (!drawingId) return;
        try {
            const vsa = _currentVersionSavedAt();
            const qs  = `drawing_id=${drawingId || 0}&engine=${WALLPAPER_ENGINE}${vsa ? '&version_saved_at=' + vsa : ''}`;
            const res = await fetch(WALLPAPER_API + 'list.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + _wpToken() },
                body: JSON.stringify({ drawing_id: drawingId || 0, engine: WALLPAPER_ENGINE, version_saved_at: _currentVersionSavedAt() }),
            });
            if (!res.ok) return;
            const data = await res.json();
            if (!data.wallpapers?.length) { return; }
            const activeServerId = localStorage.getItem(BG_IMAGE_KEY);
            const serverIds    = data.wallpapers.map(w => String(w.id));
            const lastServerId = String(data.wallpapers[data.wallpapers.length - 1].id);
            const targetId = (activeServerId && serverIds.includes(activeServerId))
                ? activeServerId : lastServerId;
            const wallpaperPromises = data.wallpapers.map(({ id: serverId, filename, url: src }) => {
                return new Promise(resolve => {
                    const id = Date.now() + Math.random();
                    const imgObj = new Image();
                    imgObj.onload = function() {
                        thumbImages.push({ id, serverId: Number(serverId), src, img: imgObj, filename });
                        addThumbItem(id, src, filename);
                        if (String(serverId) === targetId) {
                            activeThumbId = id;
                            appBackgroundImage = imgObj;
                            thumbList.querySelectorAll('.rp-thumb-item').forEach(el => {
                                el.classList.toggle('active', el.dataset.id === String(id));
                            });
                            updateClearBgBtn();
                        }
                        resolve();
                    };
                    imgObj.onerror = resolve;
                    imgObj.src = src;
                });
            });
            Promise.all(wallpaperPromises).then(() => { console.log('[THUMB draw] scaleFactor=', scaleFactor); draw(); });
        } catch(e) {}
    }

    if (!localStorage.getItem(CREATED_KEY)) {
        localStorage.setItem(CREATED_KEY, Date.now());
    }
    setElText('dateCreated', fmtDate(Number(localStorage.getItem(CREATED_KEY))));

    const savedModified = localStorage.getItem(MODIFIED_KEY) || localStorage.getItem(CREATED_KEY);
    setElText('dateModified', fmtDate(Number(savedModified)));

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
            vRatio:    parseFloat(txtRatio.value),
            pattern:   `${document.getElementById('txtPatternTop').value}/${document.getElementById('txtPatternMid').value}/${document.getElementById('txtPatternBot').value}`,
            doorType:  txtDoorType.value,
            doorCount: parseInt(txtDoorCount.value),
            pungpanOn: document.getElementById('chkPungpan').checked,
            pungpan:   parseInt(document.getElementById('txtPungpan').value) || 0,
            wood:      document.getElementById('txtWood').value,
            finish:    document.getElementById('txtFinish').value,
            frameColor: selectedFrameColor,
            slatColor:  selectedSlatColor,
            faceBrushColor: faceColorUI.getCurrentHex(),
            faceColorMap:   faceColorMap ? { ...faceColorMap } : null,
            panX, panY, scaleFactor,
            _savedView: true,
            placementMode,
            doorCornerPositions: doorCornerPositions ? { ...doorCornerPositions } : null,
            placementNaturalSize: placementNaturalSize ? { ...placementNaturalSize } : null,
            deletedSegs: [...deletedSegs],
            addedLines,
        };
    }

    function applyParams(p) {
        document.getElementById('drawingName').value = p.name || '';
        setSlider('txtW',      'numW',      p.W);
        setSlider('txtH',      'numH',      p.H);
        setSlider('txtCols',   'numCols',   p.cols);
        setSlider('txtFrame',  'numFrame',  p.frame);
        setSlider('txtFrameH', 'numFrameH', p.frameH);
        setSlider('txtSlat',   'numSlat',   p.slat);
        setSlider('txtRatio',  'numRatio',  p.vRatio ?? 1.2);
        const _pp = (p.pattern || '3/5/3').split('/');
        document.getElementById('txtPatternTop').value = _pp[0] ?? '3';
        document.getElementById('txtPatternMid').value = _pp[1] ?? '5';
        document.getElementById('txtPatternBot').value = _pp[2] ?? '3';
        txtDoorType.value  = p.doorType;
        txtDoorCount.value = p.doorCount;
        document.getElementById('chkPungpan').checked = p.pungpanOn;
        setSlider('txtPungpan', 'numPungpan', p.pungpan);
        document.getElementById('txtWood').value    = p.wood || 'hongsong';
        document.getElementById('txtFinish').value  = p.finish;
        document.getElementById('pungpanCtrl').style.display = p.pungpanOn ? 'block' : 'none';
        placementMode        = p.placementMode        || false;
        doorCornerPositions  = p.doorCornerPositions  || null;
        placementNaturalSize = p.placementNaturalSize || null;
        deletedSegs  = new Set(p.deletedSegs || []);
        addedLines   = p.addedLines || [];
        addLineStart = null;
        document.getElementById('btnScale').classList.toggle('cv-btn-active', placementMode);
        frameColorPicker.selectColor(p.frameColor);
        slatColorPicker.selectColor(p.slatColor);
        faceColorMap = p.faceColorMap || null;
        faceColorUI.restoreColor(p.faceBrushColor || null);
        faceColorUI.updateClearBtn(!!faceColorMap);
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
                if (ver.params.panX !== undefined) { panX = ver.params.panX; panY = ver.params.panY; scaleFactor = ver.params.scaleFactor; }
                applyParams(ver.params);
                document.getElementById('verLabel').textContent = 'v' + (realIdx + 1);
                renderVerList();
                document.getElementById('verDropdown').classList.remove('open');
                restoreThumbs();
            });
            item.querySelector('.ver-del').addEventListener('click', (e) => {
                e.stopPropagation();
                showVerDelConfirm(`v${realIdx + 1}`, () => {
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

    function showVerDelConfirm(label, onConfirm) {
        pmConfirm(`${label}를 정말 삭제하시겠습니까?`, onConfirm, { type: 'danger' });
    }

    async function syncToDb() {
        const title = document.getElementById('drawingName').value.trim();
        if (!title) return;
        pauseWorkTimer();
        resumeWorkTimer();
        localStorage.setItem(CURRENT_TITLE_KEY, title);
        const result = await /** @type {any} */ (window.DrawingSync).save(
            'classic', title,
            Number(localStorage.getItem(CREATED_KEY)),
            versions,
            captureThumbnail(),
            workAccum
        );
        if (!result) return;
        const btn = document.getElementById('btnSave');
        if (result.ok) {
            if (result.drawingId) drawingId = result.drawingId;
            btn.classList.add('save-ok');
            setTimeout(() => btn.classList.remove('save-ok'), 1200);
            pmShowSaveToast();
        } else if (result.reason === 'auth') {
            pmAlert('로그인이 필요합니다. 다시 로그인해 주세요.', { type: 'danger' });
        } else if (result.reason !== 'no_token') {
            btn.classList.add('save-err');
            setTimeout(() => btn.classList.remove('save-err'), 1200);
        }
    }

    async function loadFromDb(title) {
        const data = await /** @type {any} */ (window.DrawingSync).load('classic', title);
        if (!data || !data.versions || !data.versions.length) return false;
        versions      = data.versions;
        currentVerIdx = versions.length - 1;
        _versionsLoaded = true;
        applyParams(versions[currentVerIdx].params);
        document.getElementById('drawingName').value    = title;
        document.getElementById('verLabel').textContent = 'v' + (currentVerIdx + 1);
        localStorage.setItem(VERSIONS_KEY,      JSON.stringify(versions));
        localStorage.setItem(CURRENT_TITLE_KEY, title);
        localStorage.setItem(NAME_KEY,          title);
        if (data.drawing) {
            drawingId = data.drawing.id ?? null;
            const cAt = new Date(data.drawing.created_at).getTime();
            const uAt = new Date(data.drawing.updated_at).getTime();
            localStorage.setItem(CREATED_KEY,  cAt);
            localStorage.setItem(MODIFIED_KEY, uAt);
            setElText('dateCreated', fmtDate(cAt));
            setElText('dateModified', fmtDate(uAt));
            workAccum = parseInt(data.drawing.work_time_sec) || 0;
            workStart = Date.now();
        }
        renderVerList();
        return true;
    }

    function _resetStoredView() {
        if (!versions.length) return;
        const p = versions[currentVerIdx].params;
        p.panX = 0; p.panY = 0; p.scaleFactor = 1.0;
        localStorage.setItem(VERSIONS_KEY, JSON.stringify(versions));
    }

    async function loadFromCollectionId(id) {
        try {
            const _tok = localStorage.getItem('pmok_auth_token');
            const res  = await fetch('/src/api/drawings/load_by_id.php', { method: 'POST', headers: { 'Content-Type': 'application/json', ...(_tok ? { headers: { 'Authorization': 'Bearer ' + _tok } } : {}) }, body: JSON.stringify({ id }) });
            const data = await res.json();
            if (!data?.versions?.length) return false;
            _versionsLoaded = true;
            versions = []; currentVerIdx = -1; drawingId = null;
            applyParams(data.versions[0].params);
            document.getElementById('drawingName').value    = '';
            document.getElementById('verLabel').textContent = '—';
            localStorage.removeItem(CURRENT_TITLE_KEY);
            localStorage.removeItem(NAME_KEY);
            renderVerList();
            // 배경 이미지 복원
            thumbImages = []; thumbList.innerHTML = '';
            appBackgroundImage = null; activeThumbId = null;
            updateClearBgBtn();
            if (data.wallpapers?.length) {
                const last = data.wallpapers[data.wallpapers.length - 1];
                await Promise.all(data.wallpapers.map(({ filename, url: src }) =>
                    new Promise(resolve => {
                        const wpId = src;
                        const imgObj = new Image();
                        imgObj.onload = () => {
                            thumbImages.push({ id: wpId, serverId: null, src, img: imgObj, filename });
                            addThumbItem(wpId, src, filename);
                            if (src === last.url) setActiveThumb(wpId);
                            resolve();
                        };
                        imgObj.onerror = resolve;
                        imgObj.src = src;
                    })
                ));
                draw();
            }
            return 'wp';
        } catch { return false; }
    }

    async function loadVersions() {
        scaleFactor = 1.0; panX = 0; panY = 0;
        console.log('[LV] start scaleFactor=1.0');

        // 내 도면에서 직접 열었을 때 PHP가 POST 값을 window.__pmokOpenDrawing에 주입
        const pendingTitle = window.__pmokOpenDrawing || null;
        if (pendingTitle) {
            const ok = await loadFromDb(pendingTitle);
            if (ok) { _resetStoredView(); return; }
        }

        // 컬랙션에서 열었을 때 (?drawing_id=ID)
        const collectionId = window.__pmokCollectionDrawingId || null;
        if (collectionId) {
            const wpLoaded = await loadFromCollectionId(collectionId);
            if (wpLoaded) return wpLoaded;
        }

        // 상단 메뉴 또는 새 도면으로 진입 → 모든 값 초기화
        versions = []; currentVerIdx = -1;
        drawingId = null;
        _versionsLoaded = true;
        localStorage.removeItem(CURRENT_TITLE_KEY);
        localStorage.removeItem(NAME_KEY);
        document.getElementById('drawingName').value = '';
        document.getElementById('verLabel').textContent = '—';
        renderVerList();
        draw();
    }

    async function saveVersion() {
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

        // 최초 저장(drawingId 미설정)일 때만 중복 체크
        {
            const drawings = await /** @type {any} */ (window.DrawingSync).list('classic');
            const dup = drawings.find(d => d.title === title && String(d.id) !== String(drawingId));
            if (dup) {
                pmConfirm(
                    `'${title}' 이름의 도면이 이미 있습니다.`,
                    async () => {
                        // 기존 DB 버전 이어받아 현재 버전 추가
                        const loaded = await /** @type {any} */ (window.DrawingSync).load('classic', title);
                        const base = loaded?.versions ?? [];
                        const newVer = { savedAt: Math.floor(Date.now() / 1000) * 1000, params: getParams() };
                        versions = [...base, newVer].slice(-MAX_VERSIONS);
                        localStorage.setItem(VERSIONS_KEY, JSON.stringify(versions));
                        currentVerIdx = versions.length - 1;
                        document.getElementById('verLabel').textContent = 'v' + (currentVerIdx + 1);
                        renderVerList();
                        updateModified();
                        await syncToDb();
                    },
                    { sub: '확인하면 기존 도면에 버전이 추가됩니다.', type: 'danger', confirmText: '이어서 저장' }
                );
                return;
            }
        }

        versions.push({ savedAt: Math.floor(Date.now() / 1000) * 1000, params: getParams() });
        if (versions.length > MAX_VERSIONS) versions.shift();
        localStorage.setItem(VERSIONS_KEY, JSON.stringify(versions));
        currentVerIdx = versions.length - 1;
        document.getElementById('verLabel').textContent = 'v' + (currentVerIdx + 1);
        renderVerList();
        updateModified();
        await syncToDb();
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
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    async function refreshDrawingList() {
        const list = document.getElementById('dmList');
        list.innerHTML = '<div class="dm-empty">불러오는 중…</div>';
        const drawings = await /** @type {any} */ (window.DrawingSync).list('classic');
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
                    await /** @type {any} */ (window.DrawingSync).delete('classic', d.title);
                    if (d.title === document.getElementById('drawingName').value.trim()) startNewDrawing();
                    refreshDrawingList();
                }, { sub: '모든 버전이 함께 삭제됩니다.' });
            });
            list.appendChild(item);
        });
    }

    async function openDrawingByTitle(title) {
        const data = await /** @type {any} */ (window.DrawingSync).load('classic', title);
        if (!data || !data.versions || !data.versions.length) { pmAlert('도면을 불러올 수 없습니다.'); return; }
        versions = data.versions; currentVerIdx = versions.length - 1;
        const _p = versions[currentVerIdx].params;
        if (_p.panX !== undefined) { panX = _p.panX; panY = _p.panY; scaleFactor = _p.scaleFactor; }
        applyParams(_p);
        document.getElementById('drawingName').value    = title;
        document.getElementById('verLabel').textContent = 'v' + (currentVerIdx + 1);
        localStorage.setItem(VERSIONS_KEY, JSON.stringify(versions));
        localStorage.setItem(CURRENT_TITLE_KEY, title);
        localStorage.setItem(NAME_KEY, title);
        if (data.drawing) {
            drawingId = data.drawing.id ?? null;
            const cAt = new Date(data.drawing.created_at).getTime();
            const uAt = new Date(data.drawing.updated_at).getTime();
            localStorage.setItem(CREATED_KEY, cAt); localStorage.setItem(MODIFIED_KEY, uAt);
            setElText('dateCreated', fmtDate(cAt));
            setElText('dateModified', fmtDate(uAt));
            workAccum = parseInt(data.drawing.work_time_sec) || 0; workStart = Date.now();
        }
        renderVerList(); closeDrawingManager();
        restoreThumbs();
    }

    function startNewDrawing() {
        versions = []; currentVerIdx = -1;
        drawingId = null;
        document.getElementById('drawingName').value    = '';
        document.getElementById('verLabel').textContent = '—';
        const now = Date.now();
        localStorage.setItem(CREATED_KEY, now); localStorage.setItem(MODIFIED_KEY, now);
        localStorage.removeItem(CURRENT_TITLE_KEY); localStorage.removeItem(NAME_KEY);
        localStorage.removeItem(VERSIONS_KEY); localStorage.removeItem(BG_IMAGE_KEY);
        setElText('dateCreated', fmtDate(now));
        setElText('dateModified', fmtDate(now));
        scaleFactor = 1.0; panX = 0; panY = 0;
        thumbImages = []; thumbList.innerHTML = '';
        appBackgroundImage = null; activeThumbId = null;
        updateClearBgBtn();
        renderVerList(); closeDrawingManager();
        draw();
    }

    let _renameTarget = '';
    function showRenameModal(title) {
        _renameTarget = title;
        const input = document.getElementById('dmRenameInput');
        input.value = title;
        document.getElementById('dmRenameBackdrop').classList.add('pm-active');
        setTimeout(() => input.select(), 60);
    }

    document.getElementById('btnNewDrawing').addEventListener('click', startNewDrawing);
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
        if (!newTitle || newTitle === _renameTarget) { document.getElementById('dmRenameBackdrop').classList.remove('pm-active'); return; }
        const ok = await /** @type {any} */ (window.DrawingSync).rename('classic', _renameTarget, newTitle);
        if (ok) {
            if (_renameTarget === document.getElementById('drawingName').value.trim()) {
                document.getElementById('drawingName').value = newTitle;
                localStorage.setItem(NAME_KEY, newTitle); localStorage.setItem(CURRENT_TITLE_KEY, newTitle);
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

    loadVersions().then(r => { if (r !== 'wp') restoreThumbs(); });
    window.addEventListener('pageshow', (e) => {
        if (e.persisted) {
            _versionsLoaded = false;
            scaleFactor = 1.0; panX = 0; panY = 0;
            loadVersions().then(r => { if (r !== 'wp') restoreThumbs(); });
        }
    });
    window.addEventListener('pmokAuthChanged', () => {
        _versionsLoaded = false;
        loadVersions().then(r => { if (r !== 'wp') restoreThumbs(); });
    });

    // ── 도면 이름 자동 저장 ────────────────────────
    const drawingNameEl = document.getElementById('drawingName');
    const savedName = localStorage.getItem(NAME_KEY);
    if (savedName) drawingNameEl.value = savedName;
    drawingNameEl.addEventListener('input', () => {
        localStorage.setItem(NAME_KEY, drawingNameEl.value);
    });

    loadSavedRenders();
    window.addEventListener('resize', resizeCanvasDebounced);
    if (window.innerWidth < 768) {
        sidebar.classList.add('collapsed');
        btnSidebarTab.classList.add('collapsed');
        rightSidebar.classList.add('collapsed');
        btnRightSidebarTab.classList.add('collapsed');
        animatePanelResize();
    } else {
        resizeCanvas();
    }

    function _exportCapture(bgColor) {
        const exportCanvas = document.createElement('canvas');
        const exportCtx = exportCanvas.getContext('2d');
        exportCanvas.width  = logW * 2;
        exportCanvas.height = logH * 2;
        exportCtx.fillStyle = bgColor;
        exportCtx.fillRect(0, 0, exportCanvas.width, exportCanvas.height);

        exportCtx.drawImage(_exportCanvas || canvas, 0, 0, logW * 2, logH * 2);
        return exportCanvas;
    }

    //출력
    btnSavePNG.addEventListener('click', function() {
        updateModified();

        const exportCanvas = _exportCapture('#E5E7EA');
        const doorTypeText = txtDoorType.options[txtDoorType.selectedIndex].text;
        const filename = getExportFilename('png');
        const link = document.createElement('a');
        link.download = filename;
        link.href = exportCanvas.toDataURL('image/png');
        link.click();
        DrawingSync.logExport(drawingId, WALLPAPER_ENGINE, 'png', document.getElementById('drawingName')?.value.trim() || '', document.getElementById('verLabel')?.textContent.trim() || '');
    });

    btnSavePDF.addEventListener('click', function() {

        updateModified();

        const { jsPDF } = window.jspdf;

        const pdf = new jsPDF({
            orientation: 'landscape',
            unit: 'mm',
            format: 'a4'
        });

        const exportCanvas = _exportCapture('#ffffff');
        const imgData = exportCanvas.toDataURL('image/png');

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

        pdf.save(getExportFilename('pdf'));
        DrawingSync.logExport(drawingId, WALLPAPER_ENGINE, 'pdf', document.getElementById('drawingName')?.value.trim() || '', document.getElementById('verLabel')?.textContent.trim() || '');
    });