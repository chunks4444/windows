    let appBackgroundImage = null;
    let placementMode        = false;
    let doorNaturalSize      = { w: 0, h: 0 };
    let doorCornerPositions  = null;
    let overlayDrag          = null;
    let placementNaturalSize = null;
    let handlesVisible       = false;

    // ── 색상 그룹 ─────────────────────────────────
    const colorGroups = window.__pmokColorGroups || [];

    let selectedFrameColor  = '#28241e';
    let selectedMuntolColor = '#4a4a4a';
    let showMuntol          = true;
    let selectedSlatColor  = '#28241e';
    let faceColorMap       = null;
    let facePaintMode      = false;
    let facePaintIsDown    = false;

    let rotateOn       = true;
    let showDimensions = true;

    document.addEventListener('click', () => {
        document.querySelectorAll('.color-popup').forEach(p => p.classList.remove('open'));
    });

    const DEFAULT_FRAME_COLOR = '#28241e';
    const DEFAULT_SLAT_COLOR  = '#28241e';

    selectedFrameColor = DEFAULT_FRAME_COLOR;
    selectedSlatColor  = DEFAULT_SLAT_COLOR;

    // ── 오프스크린 캔버스 (배치 모드 투시 변환용) ────────
    const offCanvas = document.createElement('canvas');
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
    const faceColorUI = buildFaceColorUI(
        () => { faceColorMap = null; draw(); }
    );
    const canvas = document.getElementById('doorCanvas');
    const ctx = canvas.getContext('2d');
    const container = document.getElementById('canvasContainer');

    // ── 라인 편집 상태 (initKonvaOverlay보다 먼저 선언 필요) ──
    let deletedSegs = new Set();
    let addedLines   = [];
    let lineEditMode = null;
    let addLineStart = null;
    let lastDrawnDoorCount = 1;

    // ── Konva 오버레이 모듈 초기화 ───────────────────────
    const kv = initKonvaOverlay({
        canvas,
        getState: () => ({ logW, logH, panX, panY, scaleFactor, lastSlatPx, lastCellSize }),
        getSegMap: () => lastSegMap,
        deletedSegs,
        draw,
    });
    window.__pmokOnDeactivate    = kv.onDeactivate;
    window.__pmokKonvaInserts    = true;
    window.__pmokAddKonvaInsert  = (url, w, h) => kv.addKonvaInsert(url, w, h);

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
    let _versionsLoaded = false;

    // ── 공통 모달 유틸리티 ─────────────────────────
    let _pmModalEl = null;
    // ── 렌더링 결과 저장 ───────────────────────────
    const RENDERS_KEY = 'pmok_triangle_renders';
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

        const dpr = window.devicePixelRatio || 1;

        // 배경+도면 전체를 AI로 전송
        const composite = document.createElement('canvas');
        composite.width  = logW;
        composite.height = logH;
        const compCtx = composite.getContext('2d');
        compCtx.drawImage(canvas, 0, 0, logW, logH);

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

        const _tok = localStorage.getItem('pmok_auth_token');
        const _renderAbort = new AbortController();
        const _renderTimer = setTimeout(() => { _renderAbort.abort(); overlay.style.display = 'none'; pmAlert('렌더링 시간이 초과됐습니다. (120초)', { type: 'danger' }); }, 120000);
        fetch('api/render.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', ...(_tok ? { 'Authorization': 'Bearer ' + _tok } : {}) },
            body: JSON.stringify({ image: composite.toDataURL('image/jpeg', 0.95), prompt }),
            signal: _renderAbort.signal,
        })
        .then(r => { clearTimeout(_renderTimer); return r.json(); })
        .then(data => {
            overlay.style.display = 'none';
            if (data.error) { pmAlert(data.error, { type: 'danger' }); return; }
            if (!data.image) { pmAlert('서버 오류', { type: 'danger' }); return; }
            const img = new Image();
            img.onload = () => {
                // AI 결과 비율 역변환 후 전체 표시
                const out = document.createElement('canvas');
                out.width = logW; out.height = logH;
                const ctx = out.getContext('2d');
                const scl = Math.min(1024/logW, 1024/logH);
                const fitW = Math.round(logW*scl), fitH = Math.round(logH*scl);
                const ox = Math.round((1024-fitW)/2), oy = Math.round((1024-fitH)/2);
                ctx.drawImage(img, ox, oy, fitW, fitH, 0, 0, logW, logH);
                const finalSrc = out.toDataURL('image/jpeg', 0.95);
                saveRender(finalSrc);
                showRenderResult(finalSrc);
            };
            img.src = data.image;
        })
        .catch(() => { pmAlert('렌더링 중 오류가 발생했습니다.', { type: 'danger' }); overlay.style.display = 'none'; });
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
                            <button class="render-result-apply" id="rrDownload">다운로드</button>
                            <button class="render-result-close" id="rrClose">✕</button>
                        </div>
                    </div>
                    <img class="render-result-img" id="rrImg" src="" alt="render">
                </div>`;
            document.body.appendChild(pop);
            document.getElementById('rrClose').onclick = () => pop.classList.remove('rr-visible');
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
        kv.syncSize();
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
            if (found?.serverId) localStorage.setItem(BG_IMAGE_KEY, String(found.serverId));
            else                  localStorage.removeItem(BG_IMAGE_KEY);
        } catch(e) {}

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
            if (!res.ok || data.error) { console.warn('배경 업로드 실패:', data.error); return null; }
            return { id: data.id, url: data.url };
        } catch { return null; }
    }

    btnAddThumb.addEventListener('click', () => aiFileUploader.click());

    let _uploadPending = 0;
    function _setThumbBtnLoading(v) {
        btnAddThumb.classList.toggle('btn-loading', v);
        btnAddThumb.disabled = v;
    }

    aiFileUploader.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        files.forEach(file => {
            if (!['image/jpeg', 'image/png'].includes(file.type)) { alert('PNG 또는 JPG 파일만 업로드할 수 있습니다.'); return; }
            if (file.size > 700 * 1024) { alert('파일 크기는 700KB 이하여야 합니다.'); return; }
            _uploadPending++;
            _setThumbBtnLoading(true);
            const reader = new FileReader();
            reader.onload = function(event) {
                compressImage(event.target.result, async function(dataUrl) {
                    const id = Date.now() + Math.random();
                    const imgObj = new Image();
                    imgObj.src = dataUrl;
                    imgObj.onload = function() {
                        thumbImages.push({ id, serverId: null, src: dataUrl, img: imgObj, filename: file.name });
                        addThumbItem(id, dataUrl, file.name);
                        showRightSidebar();
                        setActiveThumb(id);
                    };
                    const result = await uploadWallpaperToServer(dataUrl, file.name, drawingId, _currentVersionSavedAt());
                    if (result) {
                        const thumb = thumbImages.find(t => t.id === id);
                        if (thumb) {
                            thumb.serverId = result.id;
                            thumb.src      = result.url;
                        }
                        if (activeThumbId === id) localStorage.setItem(BG_IMAGE_KEY, String(result.id));
                    }
                    if (--_uploadPending <= 0) { _uploadPending = 0; _setThumbBtnLoading(false); }
                });
            };
            reader.readAsDataURL(file);
        });
        aiFileUploader.value = '';
    });



// ── 라인 편집 ──────────────────────────────────
// (deletedSegs / addedLines / lineEditMode / addLineStart / lastDrawnDoorCount 는
//  initKonvaOverlay 앞 쪽에서 미리 선언됨)

// 문짝 수가 늘어날 때, 0번 문짝(기준)의 편집 패턴을 새로 생기는 문짝에 복사
function cloneDoorPatternToNewDoors(oldCount, newCount) {
    if (newCount <= oldCount) return;
    const templateKeys = [...deletedSegs].filter(k => k.startsWith('0:'));
    for (let d = oldCount; d < newCount; d++) {
        templateKeys.forEach(k => deletedSegs.add(d + k.slice(k.indexOf(':'))));
    }
}

let lastSegMap   = new Map();   // segKey → {mx,my,normAngle,cx,cy}
let lastNodeList = [];
let lastILeft=0, lastITop=0, lastIW=1, lastIH=1, lastSlatPx=1, lastCellSize=1;
let lastBaseScale = 1, lastOLeft = 0, lastOTop = 0, lastDoorWpx = 0, lastDoorHpx = 0;
let _exportCanvas = null;
let _kvKey  = null;
let _kvCornerMode = null;

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
    const body = new URLSearchParams({
        cols:      txtCols.value,
        frameOpeningW: txtW.value,
        frameOpeningH: txtH.value,
        frameThick:    window.__pmokEngineLayout?.frameThick ?? 30,
        frameGap:      window.__pmokEngineLayout?.frameGap ?? 2,
        pungpanH:  document.getElementById('txtPungpan').value || 0,
        pungpanOn: document.getElementById('chkPungpan').checked ? '1' : '0',
        frameW:    txtFrame.value,
        frameH:    txtFrameH.value,
        slatT:     txtSlat.value,
        rotateOn:  rotateOn ? '1' : '0',
        doorType:  txtDoorType.value,
        doorCount: txtDoorCount.value,
    });
    // ⚠️ 건드리지 말 것: 줌/팬/스케일은 이 캐시 덕분에 서버 호출이 없음.
    // 캐시를 빼거나 채우는 코드를 빠뜨리면 매 프레임 geometry.php 호출로 회귀함 (운영서버 줌/드래그 무거움 버그 원인이었음).
    const sig = body.toString();
    if (_geoCache && _geoCache.sig === sig) return _geoCache.data;
    if (_geoController) _geoController.abort();
    _geoController = new AbortController();
    try {
        const _tok = localStorage.getItem('pmok_auth_token');
        const res = await fetch('api/geometry.php', {
            method: 'POST',
            headers: _tok ? { 'Authorization': 'Bearer ' + _tok } : {},
            body,
            signal: _geoController.signal,
        });
        const data = await res.json();
        _geoCache = { sig, data };
        return data;
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
    const data = await fetchGeometry();
    if (!data) return;
    if (data.error) {
        if (data.error.includes('인증')) {
            const el = document.getElementById('authModal');
            if (el && window.bootstrap) bootstrap.Modal.getOrCreateInstance(el).show();
        } else {
            console.error('[draw] geometry error:', data.error);
        }
        return;
    }
    geo = data.geo;

    const s = data.specs;
    document.getElementById('spFrameOpeningW').innerText = s.frameOpeningW;
    document.getElementById('spFrameOpeningH').innerText = s.frameOpeningH;
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
        if (Number.isFinite(rawTarget) && rawTarget >= 400 && rawTarget <= 3000) {
            const currentH = parseInt(txtH.value);
            if (rawTarget !== currentH) {
                setSlider('txtH', 'numH', rawTarget);
                draw();
                return;
            }
        }
    }

    const p = data.parts;
    window.__pmokLastParts = p;
    window.__pmokUpdateWoodCost?.();
    document.getElementById('spFrVLen').textContent = `${geo.frameW}×${p.frT}×${p.frVLen}mm`;
    document.getElementById('spFrVCnt').textContent = p.frVCnt;
    document.getElementById('spFrHLen').textContent = `${geo.frameH}×${p.frT}×${p.frHLen}mm`;
    document.getElementById('spFrHCnt').textContent = p.frHCnt;

    const ppGroup = document.getElementById('pungpanMaterialGroup');
    if (p.pungpanVisible) {
        ppGroup.style.display = '';
        document.getElementById('spPpLen').textContent = `${p.ppVLen}×${p.pungpanT}×${p.ppHLen}mm`;
        document.querySelector('#pungpanMaterialGroup .slat-count-badge').textContent = p.pungpanCnt;
    } else {
        ppGroup.style.display = 'none';
    }

    document.getElementById('dirSlatGroupTitle').textContent = p.dirTitle;
    document.getElementById('spHSlatLen').textContent = `${p.slatW}×${geo.slatT}×${p.hSlatLen}mm`;
    document.getElementById('spHSlatCnt').textContent = p.hSlatCnt;

    const diagListEl = document.getElementById('spDiagList');
    diagListEl.innerHTML = '';
    p.diagList.forEach(({ len, cnt }) => {
        const el = document.createElement('div');
        el.className = 'slat-row';
        el.innerHTML = `<span class="slat-len">${p.slatW}×${geo.slatT}×${len}mm</span><span class="slat-cnt">${cnt}개</span>`;
        diagListEl.appendChild(el);
    });
    document.getElementById('spMtVLen').textContent = `${p.mtFace}×${p.mtW}×${p.mtVLen}mm`;
    document.getElementById('spMtHLen').textContent = `${p.mtFace}×${p.mtW}×${p.mtHLen}mm`;

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



    const basePadding = window.__pmokEngineLayout?.basePadding ?? 60;

    const doorType = txtDoorType.value;
    const doorCount = parseInt(txtDoorCount.value);
    lastDrawnDoorCount = doorCount;
    const gap = window.__pmokEngineLayout?.gap ?? 2;
    const overlap = geo.frameW;
    const doorPanelOffsetX = [];

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

    const dimPad = showDimensions ? 90 : 0; // 치수선/문짝 라벨이 잘리지 않도록 추가 여백
    const baseScale = Math.min(
        (logW - basePadding * 2 - dimPad) / totalWidth,
        (logH - basePadding * 2 - dimPad) / totalH
    );

    const renderOrder = [...Array(doorCount).keys()];

    const offsetX =
        -(totalWidth * baseScale) / 2;

    const offsetY =
        -(totalH * baseScale) / 2;

    doorNaturalSize = { w: totalWidth * baseScale, h: totalH * baseScale };

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

    // ── Konva 패턴 빌드 여부 결정 ───────────────────────
    const _newKvKey = JSON.stringify([
        _geoCache?.sig ?? '',
        [...deletedSegs].sort().join(','),
        addedLines.length,
        faceColorMap ? JSON.stringify(Object.entries(faceColorMap).sort()) : '',
        JSON.stringify(Object.entries(kv.getSlatColorOverrides()).sort()),
        selectedSlatColor,
        selectedFrameColor,
        Math.round(baseScale * 1000),
        rotateOn,
    ]);
    const useKonvaPattern = !doorCornerPositions;
    const buildKonvaPattern = useKonvaPattern && (_newKvKey !== _kvKey || !!doorCornerPositions !== !!_kvCornerMode);
    if (buildKonvaPattern) kv.beginPattern();
    else if (!useKonvaPattern) { kv.clearPattern(); _kvKey = null; }

    // ====== 문틀(벽 개구부) 채움 + 내경 밝은 테두리 ======
    if (!doorCornerPositions && showMuntol) {
        const m = (geo.frameThick + geo.frameGap) * baseScale;
        ctx.save();
        ctx.fillStyle = selectedMuntolColor;
        ctx.fillRect(offsetX - m, offsetY - m, totalWidth * baseScale + 2 * m, totalH * baseScale + 2 * m);
        ctx.strokeStyle = lightenHex(selectedMuntolColor, 40);
        ctx.lineWidth = 1.5;
        ctx.strokeRect(offsetX, offsetY, totalWidth * baseScale, totalH * baseScale);
        ctx.restore();
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
        if (buildKonvaPattern) {
            kv.addPatternBg(tX(geo.frameW), tY(geo.frameHTop), geo.innerW * baseScale, geo.innerH * baseScale);
        } else {
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(tX(geo.frameW), tY(geo.frameHTop), geo.innerW * baseScale, geo.innerH * baseScale);
        }
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

        doorPanelOffsetX[d] = panelOffsetX;

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
            lastOLeft     = toCanvasX(0);
            lastOTop      = toCanvasY(0);
            lastBaseScale = baseScale;
            lastDoorWpx   = totalWidth * baseScale;
            lastDoorHpx   = totalH     * baseScale;
        }

        const _slatColor = patternBroken ? '#cc0000' : selectedSlatColor;
        if (!buildKonvaPattern) {
            ctx.strokeStyle = _slatColor;
            ctx.lineWidth   = slatPx;
            ctx.lineCap     = 'round';
        }

        if (!rotateOn) {
            // ── 가로 방향 (기본) ──────────────────────────
            if (buildKonvaPattern) {
                kv.addPatternClipGroup(d, iLeft, iTop - slatPxHalf, iW, iH + slatPxHalf * 2);
            } else {
                ctx.save();
                ctx.beginPath();
                ctx.rect(iLeft, iTop - slatPxHalf, iW, iH + slatPxHalf * 2);
                ctx.clip();
            }

            const size    = iW / (geo.cols * Math.sqrt(3));
            const width   = size * Math.sqrt(3);
            const rowStep = size * 1.5;
            const startY  = iTop - slatPx / 2;

            lastCellSize = size;
            // Konva: 면색 선행 패스 (살 선보다 아래 레이어)
            if (buildKonvaPattern && faceColorMap) {
                for (let y2 = startY - rowStep, rIdx2 = 0; y2 < iTop + iH + rowStep; y2 += rowStep, rIdx2++) {
                    for (let x2 = iLeft - width, cIdx2 = 0; x2 < iLeft + iW + width; x2 += width, cIdx2++) {
                        const offX2 = (rIdx2 % 2 === 0) ? width / 2 : 0;
                        const cx2 = x2 + offX2, cy2 = y2;
                        for (let i = 0; i < 6; i++) {
                            const _fc = faceColorMap[`tri:${rIdx2}:${cIdx2}:${i}`] ?? null;
                            if (_fc) {
                                const a1 = i * Math.PI / 3, a2 = (i + 1) * Math.PI / 3;
                                kv.addPatternPolygon([cx2, cy2, cx2 + size * Math.cos(a1), cy2 + size * Math.sin(a1), cx2 + size * Math.cos(a2), cy2 + size * Math.sin(a2)], _fc);
                            }
                        }
                    }
                }
            }
            for (let y = startY - rowStep, rIdx = 0; y < iTop + iH + rowStep; y += rowStep, rIdx++) {
                for (let x = iLeft - width, cIdx = 0; x < iLeft + iW + width; x += width, cIdx++) {
                    const offX = (rIdx % 2 === 0) ? width / 2 : 0;
                    const cx = x + offX, cy = y;
                    for (let i = 0; i < 6; i++) {
                        const _fc = faceColorMap ? (faceColorMap[`tri:${rIdx}:${cIdx}:${i}`] ?? null) : null;
                        if (_fc && !buildKonvaPattern) {
                            const a1 = i * Math.PI / 3, a2 = (i + 1) * Math.PI / 3;
                            ctx.beginPath(); ctx.moveTo(cx, cy);
                            ctx.lineTo(cx + size * Math.cos(a1), cy + size * Math.sin(a1));
                            ctx.lineTo(cx + size * Math.cos(a2), cy + size * Math.sin(a2));
                            ctx.closePath(); ctx.fillStyle = _fc; ctx.fill();
                        }
                        const angle = i * Math.PI / 3;
                        const ex = cx + size * Math.cos(angle);
                        const ey = cy + size * Math.sin(angle);
                        const segKey = `${d}:h:${rIdx}:${cIdx}:${i}`;
                        const normAngle = ((angle % Math.PI) + Math.PI) % Math.PI;
                        const lineKey  = makeLineKey(cx, cy, normAngle);
                        lastSegMap.set(segKey, { cx, cy, ex, ey, mx: (cx + ex) / 2, my: (cy + ey) / 2, normAngle, lineKey });
                        if (i === 0) lastNodeList.push({ cx, cy });
                        if (deletedSegs.has(segKey)) continue;
                        if (buildKonvaPattern) {
                            kv.addPatternLine(cx, cy, ex, ey, _slatColor, slatPx);
                        } else {
                            ctx.beginPath();
                            ctx.moveTo(cx, cy);
                            ctx.lineTo(ex, ey);
                            ctx.stroke();
                        }
                    }
                }
            }
            if (!buildKonvaPattern) ctx.restore();

        } else {
            // ── 세로 방향 (90° 회전) ──────────────────────
            if (buildKonvaPattern) {
                kv.addPatternClipGroup(d, iLeft - slatPxHalf, iTop, iW + slatPxHalf * 2, iH);
            } else {
                ctx.save();
                ctx.beginPath();
                ctx.rect(iLeft - slatPxHalf, iTop, iW + slatPxHalf * 2, iH);
                ctx.clip();
            }

            const size    = (iW + slatPx) / (geo.cols * 1.5);
            const width   = size * Math.sqrt(3);
            const colStep = size * 1.5;
            const startX  = iLeft - slatPx / 2;

            lastCellSize = size;
            // Konva: 면색 선행 패스 (살 선보다 아래 레이어)
            if (buildKonvaPattern && faceColorMap) {
                for (let x2 = startX - colStep, cIdx2 = 0; x2 < iLeft + iW + colStep; x2 += colStep, cIdx2++) {
                    for (let y2 = iTop - width, rowIdx2 = 0; y2 < iTop + iH + width; y2 += width, rowIdx2++) {
                        const offY2 = (cIdx2 % 2 === 0) ? width / 2 : 0;
                        const cx2 = x2, cy2 = y2 + offY2;
                        for (let i = 0; i < 6; i++) {
                            const _fc = faceColorMap[`tri:${cIdx2}:${rowIdx2}:${i}`] ?? null;
                            if (_fc) {
                                const a1 = i * Math.PI / 3 + Math.PI / 2, a2 = (i + 1) * Math.PI / 3 + Math.PI / 2;
                                kv.addPatternPolygon([cx2, cy2, cx2 + size * Math.cos(a1), cy2 + size * Math.sin(a1), cx2 + size * Math.cos(a2), cy2 + size * Math.sin(a2)], _fc);
                            }
                        }
                    }
                }
            }
            for (let x = startX - colStep, cIdx = 0; x < iLeft + iW + colStep; x += colStep, cIdx++) {
                for (let y = iTop - width, rowIdx = 0; y < iTop + iH + width; y += width, rowIdx++) {
                    const offY = (cIdx % 2 === 0) ? width / 2 : 0;
                    const cx = x, cy = y + offY;
                    for (let i = 0; i < 6; i++) {
                        const _fc = faceColorMap ? (faceColorMap[`tri:${cIdx}:${rowIdx}:${i}`] ?? null) : null;
                        if (_fc && !buildKonvaPattern) {
                            const a1 = i * Math.PI / 3 + Math.PI / 2, a2 = (i + 1) * Math.PI / 3 + Math.PI / 2;
                            ctx.beginPath(); ctx.moveTo(cx, cy);
                            ctx.lineTo(cx + size * Math.cos(a1), cy + size * Math.sin(a1));
                            ctx.lineTo(cx + size * Math.cos(a2), cy + size * Math.sin(a2));
                            ctx.closePath(); ctx.fillStyle = _fc; ctx.fill();
                        }
                        const angle = i * Math.PI / 3 + Math.PI / 2;
                        const ex = cx + size * Math.cos(angle);
                        const ey = cy + size * Math.sin(angle);
                        const segKey = `${d}:v:${cIdx}:${rowIdx}:${i}`;
                        const normAngle = ((angle % Math.PI) + Math.PI) % Math.PI;
                        const lineKey  = makeLineKey(cx, cy, normAngle);
                        lastSegMap.set(segKey, { cx, cy, ex, ey, mx: (cx + ex) / 2, my: (cy + ey) / 2, normAngle, lineKey });
                        if (i === 0) lastNodeList.push({ cx, cy });
                        if (deletedSegs.has(segKey)) continue;
                        if (buildKonvaPattern) {
                            kv.addPatternLine(cx, cy, ex, ey, _slatColor, slatPx);
                        } else {
                            ctx.beginPath();
                            ctx.moveTo(cx, cy);
                            ctx.lineTo(ex, ey);
                            ctx.stroke();
                        }
                    }
                }
            }
            if (!buildKonvaPattern) ctx.restore();
        }

    }   // ← 1차 루프 끝

    if (buildKonvaPattern) kv.endPatternClipGroup();

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
            el.innerHTML = `<span class="slat-len">${p.slatW}×${geo.slatT}×${len}mm</span><span class="slat-cnt">${cnt}개</span>`;
            diagListEl.appendChild(el);
        });
    }

    // ====== 추가 선 그리기 (모든 문짝에 동일하게 복제) ======
    if (addedLines.length > 0) {
        const _addStroke = patternBroken ? '#cc0000' : selectedSlatColor;
        if (!buildKonvaPattern) {
            ctx.save();
            ctx.strokeStyle = _addStroke;
            ctx.lineWidth   = lastSlatPx;
            ctx.lineCap     = 'round';
            ctx.setLineDash([]);
        }
        for (const d of renderOrder) {
            const px = doorPanelOffsetX[d];
            const toX = rx => offsetX + (px + rx) * baseScale;
            const toY = ry => offsetY + ry * baseScale;
            addedLines.forEach((ln, idx) => {
                const x1 = toX(geo.frameW + ln.nx1 * geo.innerW);
                const y1 = toY(geo.frameHTop + ln.ny1 * geo.innerH);
                const x2 = toX(geo.frameW + ln.nx2 * geo.innerW);
                const y2 = toY(geo.frameHTop + ln.ny2 * geo.innerH);
                lastSegMap.set(`added:${d}:${idx}`, { mx: (x1 + x2) / 2, my: (y1 + y2) / 2, normAngle: 0 });
                if (buildKonvaPattern) {
                    kv.addPatternLine(x1, y1, x2, y2, _addStroke, lastSlatPx);
                } else {
                    ctx.beginPath();
                    ctx.moveTo(x1, y1);
                    ctx.lineTo(x2, y2);
                    ctx.stroke();
                }
            });
        }
        if (!buildKonvaPattern) ctx.restore();
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

        if (buildKonvaPattern) {
            kv.addPatternFrameRect(toCanvasX(0), toCanvasY(0), geo.frameW * baseScale, geo.outerH * baseScale, selectedFrameColor);
            kv.addPatternFrameRect(toCanvasX(geo.frameW), toCanvasY(0), geo.innerW * baseScale, geo.frameHTop * baseScale, selectedFrameColor);
            kv.addPatternFrameRect(toCanvasX(geo.frameW), toCanvasY(geo.frameHTop + geo.innerH), geo.innerW * baseScale, geo.frameHBottom * baseScale, selectedFrameColor);
            kv.addPatternFrameRect(toCanvasX(geo.outerW - geo.frameW), toCanvasY(0), geo.frameW * baseScale, geo.outerH * baseScale, selectedFrameColor);
        } else {
            ctx.fillStyle = selectedFrameColor;
            ctx.fillRect(toCanvasX(0), toCanvasY(0), geo.frameW * baseScale, geo.outerH * baseScale);
            ctx.fillRect(toCanvasX(geo.frameW), toCanvasY(0), geo.innerW * baseScale, geo.frameHTop * baseScale);
            ctx.fillRect(toCanvasX(geo.frameW), toCanvasY(geo.frameHTop + geo.innerH), geo.innerW * baseScale, geo.frameHBottom * baseScale);
            ctx.fillRect(toCanvasX(geo.outerW - geo.frameW), toCanvasY(0), geo.frameW * baseScale, geo.outerH * baseScale);
        }

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

            if (buildKonvaPattern) {
                kv.addPatternFrameRect(toCX(ppInnerX), toCY(pungpanY), ppInnerW * baseScale, ppInnerH * baseScale, lightenHex(selectedFrameColor, 40));
                kv.addPatternFrameRect(toCX(0), toCY(pungpanY), geo.frameW * baseScale, pungpanDrawH * baseScale, selectedFrameColor);
                kv.addPatternFrameRect(toCX(geo.outerW - geo.frameW), toCY(pungpanY), geo.frameW * baseScale, pungpanDrawH * baseScale, selectedFrameColor);
                kv.addPatternFrameRect(toCX(geo.frameW), toCY(pungpanY + pungpanDrawH - geo.frameH), geo.innerW * baseScale, geo.frameH * baseScale, selectedFrameColor);
            } else {
                ctx.fillStyle = lightenHex(selectedFrameColor, 40);
                ctx.fillRect(toCX(ppInnerX), toCY(pungpanY), ppInnerW * baseScale, ppInnerH * baseScale);
                ctx.fillStyle = selectedFrameColor;
                ctx.fillRect(toCX(0), toCY(pungpanY), geo.frameW * baseScale, pungpanDrawH * baseScale);
                ctx.fillRect(toCX(geo.outerW - geo.frameW), toCY(pungpanY), geo.frameW * baseScale, pungpanDrawH * baseScale);
                ctx.fillRect(toCX(geo.frameW), toCY(pungpanY + pungpanDrawH - geo.frameH), geo.innerW * baseScale, geo.frameH * baseScale);
            }
        }
    }

    if (useKonvaPattern) {
        if (buildKonvaPattern) {
            kv.commitPattern();
            _kvKey = _newKvKey;
        } else {
            kv.syncPatternTransform();
        }
        _kvCornerMode = !!doorCornerPositions;
    }

    if (doorCornerPositions) {
        ctx.restore();
        ctx = canvas.getContext('2d');
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        const _c = getOverlayCorners();

        // 문틀·치수 공통 좌표 계산 (CSS px 단위, _c 코너 기준)
        const mFrameSc = (geo.frameThick + geo.frameGap) * baseScale * scaleFactor;
        const dL = Math.min(_c.tl.x, _c.bl.x) - mFrameSc;
        const dR = Math.max(_c.tr.x, _c.br.x) + mFrameSc;
        const dT = Math.min(_c.tl.y, _c.tr.y) - mFrameSc;
        const dB = Math.max(_c.bl.y, _c.br.y) + mFrameSc;

        // 문틀(벽 개구부) 화이트 윤곽선 — 배치 모드: 메인 캔버스에 직접 그림
        ctx.save();
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 2;
        ctx.setLineDash([]);
        ctx.strokeRect(dL, dT, dR - dL, dB - dT);
        ctx.restore();

        // 4코너 투시 변환으로 메인 캔버스에 합성
        drawPerspectiveQuad(ctx, offCanvas, _c.tl, _c.tr, _c.br, _c.bl);
        if (showDimensions && totalWidth > 0) {
            const GAP = 24, TICK = 5, ITICK = 12, R = 3, lw = 1, fs = 14;
            const extra = 30 * baseScale * scaleFactor;
            ctx.save();
            ctx.strokeStyle = 'rgba(50,50,50,0.7)';
            ctx.fillStyle   = 'rgba(50,50,50,0.7)';
            ctx.lineWidth   = lw;
            ctx.font        = `${fs}px -apple-system,sans-serif`;
            const _dot2 = (x, y) => { ctx.beginPath(); ctx.arc(x, y, R, 0, Math.PI * 2); ctx.fill(); };
            const bY = dB + extra + GAP;
            ctx.beginPath();
            ctx.moveTo(dL, bY - ITICK); ctx.lineTo(dL, bY + TICK);
            ctx.moveTo(dR, bY - ITICK); ctx.lineTo(dR, bY + TICK);
            ctx.moveTo(dL, bY);         ctx.lineTo(dR, bY);
            ctx.stroke();
            _dot2(dL, bY); _dot2(dR, bY);
            ctx.textAlign = 'center'; ctx.textBaseline = 'top';
            ctx.fillText(`${Math.round(geo.frameOpeningW)}mm`, (dL + dR) / 2, bY + TICK + 3);
            const rX = dR + extra + GAP;
            ctx.beginPath();
            ctx.moveTo(rX - ITICK, dT); ctx.lineTo(rX + TICK, dT);
            ctx.moveTo(rX - ITICK, dB); ctx.lineTo(rX + TICK, dB);
            ctx.moveTo(rX, dT);         ctx.lineTo(rX, dB);
            ctx.stroke();
            _dot2(rX, dT); _dot2(rX, dB);
            ctx.textAlign = 'left'; ctx.textBaseline = 'middle';
            ctx.fillText(`${Math.round(geo.frameOpeningH)}mm`, rX + TICK + 3, (dT + dB) / 2);
            ctx.restore();
        }
    } else {
        if (showDimensions && totalWidth > 0) {
            const _sf  = scaleFactor;
            const GAP  = 24 / _sf, TICK = 5 / _sf, ITICK = 12 / _sf;
            const R    = 3  / _sf, lw   = 1 / _sf, fs    = 13 / _sf;
            const mFrame = (geo.frameThick + geo.frameGap) * baseScale;
            const extra  = 30 * baseScale;
            const dL = offsetX - mFrame, dR = offsetX + totalWidth * baseScale + mFrame;
            const dT = offsetY - mFrame, dB = offsetY + totalH * baseScale + mFrame;
            ctx.save();
            ctx.strokeStyle = 'rgba(50,50,50,0.7)';
            ctx.fillStyle   = 'rgba(50,50,50,0.7)';
            ctx.lineWidth   = lw;
            ctx.font        = `${fs}px -apple-system,sans-serif`;
            const _dot = (x, y) => { ctx.beginPath(); ctx.arc(x, y, R, 0, Math.PI * 2); ctx.fill(); };
            const bY = dB + extra + GAP;
            ctx.beginPath();
            ctx.moveTo(dL, bY - ITICK); ctx.lineTo(dL, bY + TICK);
            ctx.moveTo(dR, bY - ITICK); ctx.lineTo(dR, bY + TICK);
            ctx.moveTo(dL, bY);         ctx.lineTo(dR, bY);
            ctx.stroke();
            _dot(dL, bY); _dot(dR, bY);
            ctx.textAlign = 'center'; ctx.textBaseline = 'top';
            ctx.fillText(`${Math.round(geo.frameOpeningW)}mm`, (dL + dR) / 2, bY + TICK + 3 / _sf);
            const rX = dR + extra + GAP;
            ctx.beginPath();
            ctx.moveTo(rX - ITICK, dT); ctx.lineTo(rX + TICK, dT);
            ctx.moveTo(rX - ITICK, dB); ctx.lineTo(rX + TICK, dB);
            ctx.moveTo(rX, dT);         ctx.lineTo(rX, dB);
            ctx.stroke();
            _dot(rX, dT); _dot(rX, dB);
            ctx.textAlign = 'left'; ctx.textBaseline = 'middle';
            ctx.fillText(`${Math.round(geo.frameOpeningH)}mm`, rX + TICK + 3 / _sf, (dT + dB) / 2);
            ctx.restore();
        }
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
    drawSvgInserts();
    kv.drawSlatOverlay();
    if (!_exportCanvas || _exportCanvas.width !== canvas.width || _exportCanvas.height !== canvas.height) {
        _exportCanvas = document.createElement('canvas');
        _exportCanvas.width = canvas.width;
        _exportCanvas.height = canvas.height;
    }
    const _ec = _exportCanvas.getContext('2d');
    _ec.clearRect(0, 0, _exportCanvas.width, _exportCanvas.height);
    _ec.drawImage(canvas, 0, 0);
    drawRulers();
}

    function drawDimensions() {
        if (!showDimensions) return;
        if (!lastBaseScale || !lastDoorWpx || !lastDoorHpx) return;
        const rCtx = canvas.getContext('2d');
        const rDpr = window.devicePixelRatio || 1;

        const mFrame = (geo.frameThick + geo.frameGap) * lastBaseScale * scaleFactor;
        const extra = 30 * lastBaseScale * scaleFactor;
        const ox = logW / 2 + panX + lastOLeft * scaleFactor - mFrame - extra;
        const oy = logH / 2 + panY + lastOTop  * scaleFactor - mFrame - extra;
        const dW = lastDoorWpx * scaleFactor + 2 * mFrame;
        const dH = lastDoorHpx * scaleFactor + 2 * mFrame;

        const wMm = Math.round(geo.frameOpeningW);
        const hMm = Math.round(geo.frameOpeningH);

        rCtx.save();
        rCtx.setTransform(rDpr, 0, 0, rDpr, 0, 0);

        const GAP  = 24;
        const TICK = 5;
        const ITICK = 12;
        const color = 'rgba(50,50,50,0.7)';
        rCtx.strokeStyle = color;
        rCtx.fillStyle   = color;
        rCtx.lineWidth   = 1;
        rCtx.font        = `15px -apple-system, sans-serif`;

        const R = 3;
        function dot(x, y) {
            rCtx.beginPath();
            rCtx.arc(x, y, R, 0, Math.PI * 2);
            rCtx.fill();
        }

        // 하단 가로 치수
        const bY = oy + dH + GAP;
        rCtx.beginPath();
        rCtx.moveTo(ox,      bY - ITICK); rCtx.lineTo(ox,      bY + TICK);
        rCtx.moveTo(ox + dW, bY - ITICK); rCtx.lineTo(ox + dW, bY + TICK);
        rCtx.moveTo(ox, bY); rCtx.lineTo(ox + dW, bY);
        rCtx.stroke();
        dot(ox, bY); dot(ox + dW, bY);
        rCtx.textAlign    = 'center';
        rCtx.textBaseline = 'top';
        rCtx.fillText(`${wMm}mm`, ox + dW / 2, bY + TICK + 3);

        // 우측 세로 치수
        const rX = ox + dW + GAP;
        rCtx.beginPath();
        rCtx.moveTo(rX - ITICK, oy);      rCtx.lineTo(rX + TICK, oy);
        rCtx.moveTo(rX - ITICK, oy + dH); rCtx.lineTo(rX + TICK, oy + dH);
        rCtx.moveTo(rX, oy); rCtx.lineTo(rX, oy + dH);
        rCtx.stroke();
        dot(rX, oy); dot(rX, oy + dH);
        rCtx.textAlign    = 'left';
        rCtx.textBaseline = 'middle';
        rCtx.fillText(`${hMm}mm`, rX + TICK + 3, oy + dH / 2);

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

        const doorL = ox;
        const doorR = ox + lastDoorWpx * scaleFactor;
        const doorT = oy;
        const doorB = oy + lastDoorHpx * scaleFactor;

        // 가로 눈금자
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

        // 세로 눈금자
        rCtx.fillStyle = BG_OUT;
        rCtx.fillRect(0, R, R, logH);
        const vT = Math.max(R, doorT), vB = Math.min(logH, doorB);
        if (vB > vT) { rCtx.fillStyle = BG_IN; rCtx.fillRect(R - 8, vT, 8, vB - vT); }
        rCtx.strokeStyle = '#ddd'; rCtx.lineWidth = 0.5;
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

        if (showDimensions && geo.outerW) {
            rCtx.fillStyle = '#fff';
            rCtx.font = '12px -apple-system, sans-serif';
            rCtx.textAlign = 'left';
            rCtx.textBaseline = 'top';
            rCtx.fillText(`• 문짝: ${Math.round(geo.outerW)} x ${Math.round(geo.outerH)}mm`, R + 16, R + 16);
        }

        rCtx.restore();
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
        return (best && bestDist < lastCellSize * 0.5) ? best : null;
    }

    function hitTestCell(px, py) {
        if (!lastBaseScale || !geo.cols) return null;
        const iLeft = lastILeft, iTop = lastITop, iW = lastIW, iH = lastIH;
        const slatPx = lastSlatPx;
        const S3 = Math.sqrt(3);
        if (!rotateOn) {
            const size    = iW / (geo.cols * S3);
            const width   = size * S3;
            const rowStep = size * 1.5;
            const startY  = iTop - slatPx / 2;
            const rIdx    = Math.round((py - (startY - rowStep)) / rowStep);
            const offX    = (rIdx % 2 === 0) ? width / 2 : 0;
            const cIdx    = Math.round((px - (iLeft - width) - offX) / width);
            const hcy     = startY - rowStep + rIdx * rowStep;
            const hcx     = iLeft - width + cIdx * width + offX;
            if (Math.hypot(px - hcx, py - hcy) > size) return null;
            const angle   = ((Math.atan2(py - hcy, px - hcx) % (2 * Math.PI)) + 2 * Math.PI) % (2 * Math.PI);
            const wedge   = Math.floor(angle / (Math.PI / 3)) % 6;
            return `tri:${rIdx}:${cIdx}:${wedge}`;
        } else {
            const size    = (iW + slatPx) / (geo.cols * 1.5);
            const width   = size * S3;
            const colStep = size * 1.5;
            const startX  = iLeft - slatPx / 2;
            const cIdx    = Math.round((px - (startX - colStep)) / colStep);
            const offY    = (cIdx % 2 === 0) ? width / 2 : 0;
            const rowIdx  = Math.round((py - (iTop - width) - offY) / width);
            const hcx     = startX - colStep + cIdx * colStep;
            const hcy     = iTop - width + rowIdx * width + offY;
            if (Math.hypot(px - hcx, py - hcy) > size) return null;
            const angle   = ((Math.atan2(py - hcy, px - hcx) - Math.PI / 2 + 2 * Math.PI) % (2 * Math.PI) + 2 * Math.PI) % (2 * Math.PI);
            const wedge   = Math.floor(angle / (Math.PI / 3)) % 6;
            return `tri:${cIdx}:${rowIdx}:${wedge}`;
        }
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
    canvas.addEventListener('contextmenu', e => {
        if (facePaintMode) {
            e.preventDefault();
            const coord = screenToCtxCoord(e.clientX, e.clientY);
            paintFaceCell(coord.x, coord.y, true);
        }
    });

    document.getElementById('btnOrder')?.addEventListener('click', () => {
        openOrderModal({
            engine:       WALLPAPER_ENGINE,
            getSpec:      getParams,
            getDrawingId: () => drawingId,
            getTitle:     () => document.getElementById('drawingName')?.value.trim(),
            getThumbnail:    captureThumbnail,
            getVersionLabel: () => document.getElementById('verLabel')?.textContent.trim(),
            onLocked:        () => { isDrawingLocked = true; updateLockBanner(true); },
        });
    });

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
                const idx = parseInt(bestKey.split(':')[2]);
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
        if (e.target.closest('.canvas-controls') || e.target.closest('.canvas-title-bar')) return;
        if (lineEditMode) { handleEditClick(e); return; }
        if (facePaintMode) {
            if (e.button === 2 || e.ctrlKey) return;
            facePaintIsDown = true;
            const coord = screenToCtxCoord(e.clientX, e.clientY);
            paintFaceCell(coord.x, coord.y, false);
            return;
        }
        if (kv.slatSelectMode && !kv.usePatternLayer) { kv.handleSlatSelect(e); return; }
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
        if (e.target.closest('.canvas-title-bar') || e.target.closest('.canvas-controls')) return;
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
    // ── 풍판 토글 ─────────────────────────────
    const chkPungpan   = document.getElementById('chkPungpan');
    const pungpanCtrl  = document.getElementById('pungpanCtrl');


    chkPungpan.addEventListener('change', async () => {
        pungpanCtrl.style.display = chkPungpan.checked ? 'block' : 'none';
        if (!chkPungpan.checked) {
            document.getElementById('txtPungpan').value = 0;
            document.getElementById('numPungpan').value = 0;
        }
        draw();
    });

    document.getElementById('chkShrinkH').addEventListener('change', draw);

    // ── 슬라이더 ↔ 인풋창 양방향 동기화 ──────────────────

    const syncPairs = [
        { range: txtW,      num: document.getElementById('numW'),       min: 400,  max: 3000 },
        { range: txtH,      num: document.getElementById('numH'),       min: 400,  max: 3000 },
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

    // 가로 칸수는 짝수만 허용 (삼각형 패턴이 좌우 한 쌍으로 맞물려야 함)
    const numCols = document.getElementById('numCols');
    const snapColsEven = (v) => Math.min(30, Math.max(2, Math.round(v / 2) * 2));
    txtCols.addEventListener('input', () => {
        const v = snapColsEven(parseInt(txtCols.value));
        txtCols.value = v;
        numCols.value = v;
        draw();
    });
    numCols.addEventListener('blur', () => {
        let v = parseInt(numCols.value);
        if (isNaN(v)) v = parseInt(txtCols.value);
        v = snapColsEven(v);
        txtCols.value = v;
        numCols.value = v;
        draw();
    });
    numCols.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') numCols.blur();
    });

    txtDoorType.addEventListener('input', updateDoorCountOptions);
    txtDoorCount.addEventListener('input', () => {
        cloneDoorPatternToNewDoors(lastDrawnDoorCount, parseInt(txtDoorCount.value));
        draw();
    });
    document.getElementById('chkRotate').addEventListener('change', e => {
        rotateOn = e.target.checked;
        draw();
    });

    document.getElementById('chkDimension').addEventListener('change', e => {
        showDimensions = e.target.checked;
        draw();
    });
    document.getElementById('chkMuntol')?.addEventListener('change', e => { showMuntol = e.target.checked; draw(); });
document.getElementById('muntolColorInput')?.addEventListener('input', e => { selectedMuntolColor = e.target.value; const el = document.getElementById('muntolColorCode'); if (el) el.textContent = e.target.value; draw(); });
document.getElementById('muntolColorInput')?.addEventListener('input', e => { selectedMuntolColor = e.target.value; document.getElementById('muntolColorCode').textContent = e.target.value; draw(); });
    updateDoorCountOptions();

    // ── 편집 버튼 ──────────────────────────────────
    const CURSOR_ERASER = `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'%3E%3Crect x='5' y='10' width='14' height='9' rx='2' fill='%23fff' stroke='%23555' stroke-width='1.5'/%3E%3Crect x='5' y='10' width='6' height='9' rx='0' fill='%23f87171' stroke='none'/%3E%3Crect x='5' y='10' width='6' height='9' rx='0' fill='none' stroke='%23555' stroke-width='1.5'/%3E%3Cline x1='5' y1='19' x2='19' y2='19' stroke='%23555' stroke-width='1.5'/%3E%3C/svg%3E") 4 20, crosshair`;
    const CURSOR_PEN    = `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'%3E%3Cpath d='M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z' fill='%23fff' stroke='%23555' stroke-width='1.5' stroke-linejoin='round'/%3E%3C/svg%3E") 2 22, crosshair`;

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
    const CREATED_KEY       = 'pmok_triangle_created';
    const MODIFIED_KEY      = 'pmok_triangle_modified';
    const VERSIONS_KEY      = 'pmok_triangle_versions';
    const BG_IMAGE_KEY      = 'pmok_triangle_bg';
    const WALLPAPER_ENGINE  = 'triangle';
    const WALLPAPER_API     = '/src/api/wallpapers/';
    const CURRENT_TITLE_KEY = 'pmok_triangle_current_title';
    const NAME_KEY          = 'pmok_triangle_name';
    const MAX_VERSIONS      = 20;

    let drawingId = null;
    let isDrawingLocked = false;

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
        const dpr = window.devicePixelRatio || 1;
        if (!lastDoorWpx || !lastDoorHpx) return null;
        const pad = 20;
        const left = logW / 2 + panX + lastOLeft * scaleFactor - pad;
        const top  = logH / 2 + panY + lastOTop  * scaleFactor - pad;
        const cw   = lastDoorWpx * scaleFactor + 2 * pad;
        const ch   = lastDoorHpx * scaleFactor + 2 * pad;
        const sx = Math.max(0, Math.round(left * dpr));
        const sy = Math.max(0, Math.round(top  * dpr));
        const sw = Math.min(canvas.width  - sx, Math.round(cw * dpr));
        const sh = Math.min(canvas.height - sy, Math.round(ch * dpr));
        if (sw <= 0 || sh <= 0) return null;
        const W = 640, H = 480;
        const maxW = W - 40, maxH = H - 40;
        const ar = sw / sh;
        let dw, dh;
        if (ar > maxW / maxH) { dw = maxW; dh = Math.round(maxW / ar); }
        else                   { dh = maxH; dw = Math.round(maxH * ar); }
        const dx = Math.round((W - dw) / 2);
        const dy = Math.round((H - dh) / 2);
        const tmp = document.createElement('canvas');
        tmp.width = W; tmp.height = H;
        const tctx = tmp.getContext('2d');
        tctx.fillStyle = '#E5E7EA';
        tctx.fillRect(0, 0, W, H);
        tctx.drawImage(canvas, sx, sy, sw, sh, dx, dy, dw, dh);
        const kvEl = document.getElementById('konvaStageContainer');
        if (kvEl) kvEl.querySelectorAll('canvas').forEach(c => { try { tctx.drawImage(c, sx, sy, sw, sh, dx, dy, dw, dh); } catch (_) {} });
        return tmp.toDataURL('image/jpeg', 0.85);
    }

    // 썸네일 + 배경 이미지 복원
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
            const serverIds   = data.wallpapers.map(w => String(w.id));
            const lastServerId = String(data.wallpapers[data.wallpapers.length - 1].id);
            const targetId = (activeServerId && serverIds.includes(activeServerId))
                ? activeServerId : lastServerId;
            data.wallpapers.forEach(({ id: serverId, filename, url: src }) => {
                const id = Date.now() + Math.random();
                const imgObj = new Image();
                imgObj.onload = function() {
                    thumbImages.push({ id, serverId: Number(serverId), src, img: imgObj, filename });
                    addThumbItem(id, src, filename);
                    if (String(serverId) === targetId) setActiveThumb(id);
                };
                imgObj.src = src;
            });
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
            faceBrushColor: faceColorUI.getCurrentHex(),
            faceColorMap:   faceColorMap ? { ...faceColorMap } : null,
            muntolColor:    selectedMuntolColor,
            showMuntol:     showMuntol,
            panX, panY, scaleFactor,
            _savedView: true,
            placementMode,
            doorCornerPositions: doorCornerPositions ? { ...doorCornerPositions } : null,
            placementNaturalSize: placementNaturalSize ? { ...placementNaturalSize } : null,
            deletedSegs: [...deletedSegs],
            addedLines,
            svgInserts: [],
            konvaShapes: kv.getKonvaParams(),
            slatColorOverrides: kv.getSlatColorOverrides(),
            estimatedPrice: window.__pmokEstimatedPrice || 0,
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
        placementMode        = p.placementMode        || false;
        doorCornerPositions  = p.doorCornerPositions  || null;
        placementNaturalSize = p.placementNaturalSize || null;
        deletedSegs  = new Set(p.deletedSegs || []);
        addedLines      = p.addedLines || [];
        addLineStart    = null;
        svgInserts       = [];
        selectedInsertId = null;
        renderSvgInsertPanel();
        kv.applyKonvaParams(p);
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

        const pendingTitle = window.__pmokOpenDrawing || null;
        if (pendingTitle) {
            const ok = await loadFromDb(pendingTitle);
            if (ok) return;
        }

        const collectionId = window.__pmokCollectionDrawingId || null;
        if (collectionId) {
            const wpLoaded = await loadFromCollectionId(collectionId);
            if (wpLoaded) return wpLoaded;
        }

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

    async function syncToDb() {
        const title = document.getElementById('drawingName').value.trim();
        if (!title) return;
        pauseWorkTimer();
        resumeWorkTimer();
        localStorage.setItem(CURRENT_TITLE_KEY, title);
        const result = await /** @type {any} */ (window.DrawingSync).save(
            'triangle', title,
            Number(localStorage.getItem(CREATED_KEY)),
            versions,
            captureThumbnail(),
            workAccum,
            document.getElementById('patternCategory')?.value || null
        );
        if (!result) { pmHideSaveToast(); return; }
        const btn = document.getElementById('btnSave');
        if (result.ok) {
            if (result.drawingId) drawingId = result.drawingId;
            btn.classList.add('save-ok');
            setTimeout(() => btn.classList.remove('save-ok'), 1200);
            pmShowSaveToast();
        } else if (result.reason === 'auth') {
            pmHideSaveToast();
            pmAlert('로그인이 필요합니다. 다시 로그인해 주세요.', { type: 'danger' });
        } else if (result.reason !== 'no_token') {
            pmHideSaveToast();
            btn.classList.add('save-err');
            setTimeout(() => btn.classList.remove('save-err'), 1200);
        } else {
            pmHideSaveToast();
        }
    }

    async function loadFromDb(title) {
        const data = await /** @type {any} */ (window.DrawingSync).load('triangle', title);
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
            isDrawingLocked = !!data.drawing.locked_at;
            updateLockBanner(isDrawingLocked);
            const catEl = document.getElementById('patternCategory');
            if (catEl) catEl.value = data.drawing.pattern_category || '';
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

    async function saveVersion() {
        if (isDrawingLocked) {
            pmAlert('이 도면은 견적요청 중이라 편집할 수 없습니다.', { type: 'danger' });
            return;
        }
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

        // 제목이 바뀌면 새 도면 → 버전·작업시간·생성일·drawingId 초기화
        const prevTitle = localStorage.getItem(CURRENT_TITLE_KEY);
        if (prevTitle && prevTitle !== title) {
            versions      = [];
            currentVerIdx = -1;
            drawingId     = null;
            const now = Date.now();
            localStorage.setItem(CREATED_KEY, now);
            localStorage.setItem(MODIFIED_KEY, now);
            workAccum  = 0;
            workStart  = Date.now();
        }

        const saveBtn = document.getElementById('btnSave');
        saveBtn.classList.add('save-busy'); saveBtn.disabled = true; pmShowSaveToast('저장 중...', true);
        try {
            const drawings = await /** @type {any} */ (window.DrawingSync).list('triangle');
            const dup = drawings.find(d => d.title === title && String(d.id) !== String(drawingId));
            if (dup) {
                pmConfirm(
                    `'${title}' 이름의 도면이 이미 있습니다.`,
                    async () => {
                        saveBtn.classList.add('save-busy'); saveBtn.disabled = true; pmShowSaveToast('저장 중...', true);
                        try {
                            const loaded = await /** @type {any} */ (window.DrawingSync).load('triangle', title);
                            const base = loaded?.versions ?? [];
                            const newVer = { savedAt: Math.floor(Date.now() / 1000) * 1000, params: getParams() };
                            versions = [...base, newVer].slice(-MAX_VERSIONS);
                            localStorage.setItem(VERSIONS_KEY, JSON.stringify(versions));
                            currentVerIdx = versions.length - 1;
                            document.getElementById('verLabel').textContent = 'v' + (currentVerIdx + 1);
                            renderVerList();
                            updateModified();
                            await syncToDb();
                        } finally {
                            saveBtn.classList.remove('save-busy'); saveBtn.disabled = false;
                        }
                    },
                    { sub: '확인하면 기존 도면에 버전이 추가됩니다.', type: 'danger', confirmText: '이어서 저장' }
                );
                pmHideSaveToast();
                return;
            }

            versions.push({ savedAt: Math.floor(Date.now() / 1000) * 1000, params: getParams() });
            if (versions.length > MAX_VERSIONS) versions.shift();
            localStorage.setItem(VERSIONS_KEY, JSON.stringify(versions));
            currentVerIdx = versions.length - 1;
            document.getElementById('verLabel').textContent = 'v' + (currentVerIdx + 1);
            renderVerList();
            updateModified();
            await syncToDb();
        } finally {
            saveBtn.classList.remove('save-busy'); saveBtn.disabled = false;
        }
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

    async function refreshDrawingList() {
        const list = document.getElementById('dmList');
        list.innerHTML = '<div class="dm-empty">불러오는 중…</div>';
        const drawings = await /** @type {any} */ (window.DrawingSync).list('triangle');
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
                    <div class="dm-title">${escHtml(d.title)}${d.locked_at ? ' <i class="bi bi-lock-fill dm-lock-icon" title="견적요청 중"></i>' : ''}</div>
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
                if (d.locked_at) { pmAlert('이 도면은 견적요청 중이라 삭제할 수 없습니다.', { type: 'danger' }); return; }
                pmConfirm(`"${d.title}" 도면을 삭제하시겠습니까?`, async () => {
                    await /** @type {any} */ (window.DrawingSync).delete('triangle', d.title);
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
        const data = await /** @type {any} */ (window.DrawingSync).load('triangle', title);
        if (!data || !data.versions || !data.versions.length) {
            pmAlert('도면을 불러올 수 없습니다.');
            return;
        }
        versions      = data.versions;
        currentVerIdx = versions.length - 1;
        const _p = versions[currentVerIdx].params;
        if (_p.panX !== undefined) { panX = _p.panX; panY = _p.panY; scaleFactor = _p.scaleFactor; }
        applyParams(_p);
        document.getElementById('drawingName').value    = title;
        document.getElementById('verLabel').textContent = 'v' + (currentVerIdx + 1);
        localStorage.setItem(VERSIONS_KEY,      JSON.stringify(versions));
        localStorage.setItem(CURRENT_TITLE_KEY, title);
        localStorage.setItem(NAME_KEY,          title);
        if (data.drawing) {
            drawingId = data.drawing.id ?? null;
            isDrawingLocked = !!data.drawing.locked_at;
            updateLockBanner(isDrawingLocked);
            const catEl = document.getElementById('patternCategory');
            if (catEl) catEl.value = data.drawing.pattern_category || '';
            const cAt = new Date(data.drawing.created_at).getTime();
            const uAt = new Date(data.drawing.updated_at).getTime();
            localStorage.setItem(CREATED_KEY,  cAt);
            localStorage.setItem(MODIFIED_KEY, uAt);
            setElText('dateCreated', fmtDate(cAt));
            setElText('dateModified', fmtDate(uAt));
        }
        renderVerList();
        closeDrawingManager();
        restoreThumbs();
    }

    function startNewDrawing() {
        versions      = [];
        currentVerIdx = -1;
        drawingId     = null;
        isDrawingLocked = false;
        updateLockBanner(false);
        document.getElementById('drawingName').value    = '';
        document.getElementById('verLabel').textContent = '—';
        const now = Date.now();
        localStorage.setItem(CREATED_KEY,  now);
        localStorage.setItem(MODIFIED_KEY, now);
        localStorage.removeItem(CURRENT_TITLE_KEY);
        localStorage.removeItem(NAME_KEY);
        localStorage.removeItem(VERSIONS_KEY);
        localStorage.removeItem(BG_IMAGE_KEY);
        setElText('dateCreated', fmtDate(now));
        setElText('dateModified', fmtDate(now));
        scaleFactor = 1.0; panX = 0; panY = 0;
        thumbImages = []; thumbList.innerHTML = '';
        appBackgroundImage = null; activeThumbId = null;
        updateClearBgBtn();
        renderVerList();
        closeDrawingManager();
        draw();
    }

    let _renameTarget = '';
    function showRenameModal(title) {
        _renameTarget = title;
        const input = document.getElementById('dmRenameInput');
        input.value = title;
        document.getElementById('dmRenameBackdrop').classList.add('pm-active');
        setTimeout(() => { input.select(); }, 60);
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
        if (!newTitle || newTitle === _renameTarget) {
            document.getElementById('dmRenameBackdrop').classList.remove('pm-active');
            return;
        }
        const ok = await /** @type {any} */ (window.DrawingSync).rename('triangle', _renameTarget, newTitle);
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

    //출력
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

        pdf.save(getExportFilename('pdf'));
        DrawingSync.logExport(drawingId, WALLPAPER_ENGINE, 'pdf', document.getElementById('drawingName')?.value.trim() || '', document.getElementById('verLabel')?.textContent.trim() || '');
    });
    // AI 채팅
    window.pmokInitAiChat?.({ engine: WALLPAPER_ENGINE, getParams, applyParams });
