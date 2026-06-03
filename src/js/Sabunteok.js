    let appBackgroundImage = null;
    let placementMode       = false;
    let doorNaturalSize     = { w: 0, h: 0 };
    let doorOverlay         = { tx: 0, ty: 0, sx: 1, sy: 1, skewX: 0, skewY: 0 };
    let doorCornerPositions = null;
    let overlayDrag         = null;
    let placementNaturalSize = null;

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
    let isDragging = false;
    let startX, startY;

    // 3. Rendering 함수 (버튼에 연결)
    // [스크립트 상단 등 최상위 레벨에 작성]
    function startAISynthesis() {
        console.log("버튼이 클릭되었습니다!"); // 확인용 로그

        if (typeof appBackgroundImage === 'undefined' || !appBackgroundImage) {
            alert("먼저 사진을 업로드해주세요.");
            return;
        }

        // 캔버스 데이터 추출
        const canvas = document.getElementById('doorCanvas');
        const designData = canvas.toDataURL('image/png');

        alert("Rendering을 시작합니다.");
        // 여기에 이후 전송 로직 작성
    }

    function resizeCanvas() {
        const w = container.clientWidth;
        const h = container.clientHeight;
        if (canvas.width === w && canvas.height === h) return;
        canvas.width  = w;
        canvas.height = h;
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

    function setActiveThumb(id) {
        activeThumbId = id;
        const found = thumbImages.find(t => t.id === id);
        appBackgroundImage = found ? found.img : null;

        try {
            if (found) localStorage.setItem(BG_IMAGE_KEY, found.src);
            else        localStorage.removeItem(BG_IMAGE_KEY);
        } catch(e) {}

        // 활성 표시 갱신
        thumbList.querySelectorAll('.rp-thumb-item').forEach(el => {
            el.classList.toggle('active', el.dataset.id === String(id));
        });
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

    const diagListEl = document.getElementById('spDiagList');
    diagListEl.innerHTML = '';
    p.diagList.forEach(function(item) {
        const el = document.createElement('div');
        el.className = 'slat-row';
        el.innerHTML = '<span class="slat-len">' + item.len + '<span class="slat-len-unit">mm</span></span><span class="slat-cnt">' + item.cnt + '개</span>';
        diagListEl.appendChild(el);
    });

    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    ctx.save();

    ctx.translate(
        canvas.width / 2 + panX,
        canvas.height / 2 + panY
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
    const totalWidth =
        (geo.outerW * doorCount) +
        (gap * (doorCount - 1));

    const pungpanH = parseInt(document.getElementById('txtPungpan').value) || 0;
    const totalH   = geo.outerH;  // 외경 고정

    const baseScale = Math.min(
        (canvas.width  - basePadding * 2) / totalWidth,
        (canvas.height - basePadding * 2) / totalH
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
        canvas.style.cursor = 'grab';
        document.getElementById('btnScale').classList.remove('cv-btn-active');
    }

    // 배치 모드: doorCornerPositions에서 직접 transform 계산
    if (placementMode && doorCornerPositions) {
        const _tl = doorCornerPositions.tl, _tr = doorCornerPositions.tr, _bl = doorCornerPositions.bl;
        const _W = doorNaturalSize.w, _H = doorNaturalSize.h;
        const _sx = (_tr.cx - _tl.cx) / _W,  _skewY = (_tr.cy - _tl.cy) / _W;
        const _skewX = (_bl.cx - _tl.cx) / _H, _sy = (_bl.cy - _tl.cy) / _H;
        const _tx = _tl.cx + _sx * _W/2 + _skewX * _H/2;
        const _ty = _tl.cy + _skewY * _W/2 + _sy * _H/2;
        ctx.save();
        ctx.transform(_sx, _skewY, _skewX, _sy, _tx, _ty);
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

        for (let i = 1; i < geo.cols; i++) {

            // 내경 시작(frameW)에서 i번째 살 중심 위치
            // = frameW + i*cellW + (i-0.5)*slatV
            // = frameW + i*(cellW + slatV) - slatV/2
            // = frameW + i*stepW - slatV/2
            const cx    = geo.frameW + i * (geo.cellW + geo.slatV) - geo.slatV / 2;
            const left  = cx - geo.slatV / 2;
            const topY  = geo.frameHTop;
            const botY  = geo.frameHTop + geo.innerH;

            // 촉
            ctx.fillStyle =
                Color_Tenon_Fill;

            ctx.fillRect(
                toCanvasX(left),
                toCanvasY(topY - geo.tenonDepth),
                geo.slatV * baseScale,
                geo.tenonDepth * baseScale
            );

            ctx.fillRect(
                toCanvasX(left),
                toCanvasY(botY),
                geo.slatV * baseScale,
                geo.tenonDepth * baseScale
            );

            // 몸통
            ctx.fillStyle = Color_Slat_Fill;
            ctx.fillRect(toCanvasX(left), toCanvasY(topY), geo.slatV * baseScale, geo.innerH * baseScale);

            drawCenterLine(
                toCanvasX(cx),
                toCanvasY(topY - geo.tenonDepth),
                toCanvasX(cx),
                toCanvasY(botY + geo.tenonDepth)
            );
        }

        // ====================================
        // 가로살
        // ====================================

        for (let j = 1; j < geo.rowsInt; j++) {

            // j번째 가로살 중심
            const ry    = geo.frameHTop + j * (geo.cellH + geo.slatH) - geo.slatH / 2;
            const top   = ry - geo.slatH / 2;
            const leftX  = geo.frameW;
            const rightX = geo.frameW + geo.innerW;

            // 촉
            ctx.fillStyle =
                Color_Tenon_Fill;

            ctx.fillRect(
                toCanvasX(leftX - geo.tenonDepth),
                toCanvasY(top),
                geo.tenonDepth * baseScale,
                geo.slatH * baseScale
            );

            ctx.fillRect(
                toCanvasX(rightX),
                toCanvasY(top),
                geo.tenonDepth * baseScale,
                geo.slatH * baseScale
            );

            // 몸통
            ctx.fillStyle = Color_Slat_Fill;
            ctx.fillRect(toCanvasX(leftX), toCanvasY(top), geo.innerW * baseScale, geo.slatH * baseScale);

            drawCenterLine(
                toCanvasX(leftX - geo.tenonDepth),
                toCanvasY(ry),
                toCanvasX(rightX + geo.tenonDepth),
                toCanvasY(ry)
            );
        }

        // ====================================
        // 사분턱
        // ====================================

        ctx.strokeStyle = patternBroken ? '#cc0000' : selectedSlatColor;

        ctx.lineWidth =
            ((geo.slatV + geo.slatH) / 2) * baseScale;

        for (let row = 0; row < geo.rowsInt; row++) {

            for (let col = 0; col < geo.cols; col++) {

                // 각 셀의 좌상 코너 (mm)
                const x = geo.frameW + col * (geo.cellW + geo.slatV);
                const y = geo.frameHTop + row * (geo.cellH + geo.slatH);

                // 좌상 → 우하
                ctx.beginPath();
                ctx.moveTo(toCanvasX(x),              toCanvasY(y));
                ctx.lineTo(toCanvasX(x + geo.cellW),  toCanvasY(y + geo.cellH));
                ctx.stroke();

                // 우상 → 좌하
                ctx.beginPath();
                ctx.moveTo(toCanvasX(x + geo.cellW),  toCanvasY(y));
                ctx.lineTo(toCanvasX(x),              toCanvasY(y + geo.cellH));
                ctx.stroke();
            }
        }

        // 클리핑 해제
        ctx.restore();

    }   // ← 1차 루프 끝

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

    if (placementMode) ctx.restore();

    ctx.restore();

    if (placementMode && doorNaturalSize.w > 0) {
        const c = getOverlayCorners();
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.strokeStyle = 'rgba(58,140,130,0.9)';
        ctx.lineWidth = 2;
        ctx.setLineDash([6, 4]);
        ctx.beginPath();
        ctx.moveTo(c.tl.x, c.tl.y); ctx.lineTo(c.tr.x, c.tr.y);
        ctx.lineTo(c.br.x, c.br.y); ctx.lineTo(c.bl.x, c.bl.y);
        ctx.closePath(); ctx.stroke();
        ctx.setLineDash([]);
        // TL·TR·BL: 사각형 (자유 이동), BR: 원 (비율 스케일)
        [c.tl, c.tr, c.bl].forEach(({ x, y }) => {
            ctx.fillStyle = '#fff';
            ctx.strokeStyle = '#3A8C82';
            ctx.lineWidth = 2.5;
            ctx.fillRect(x - 7, y - 7, 14, 14);
            ctx.strokeRect(x - 7, y - 7, 14, 14);
        });
        ctx.fillStyle = '#3A8C82';
        ctx.strokeStyle = '#fff';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.arc(c.br.x, c.br.y, 8, 0, Math.PI * 2);
        ctx.fill();
        ctx.stroke();
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
        if (!doorCornerPositions || doorNaturalSize.w === 0) return;
        const { tl, tr, bl } = doorCornerPositions;
        const W = doorNaturalSize.w, H = doorNaturalSize.h;
        const sx    = (tr.cx - tl.cx) / W;
        const skewY = (tr.cy - tl.cy) / W;
        const skewX = (bl.cx - tl.cx) / H;
        const sy    = (bl.cy - tl.cy) / H;
        doorOverlay.sx    = sx;
        doorOverlay.sy    = sy;
        doorOverlay.skewX = skewX;
        doorOverlay.skewY = skewY;
        doorOverlay.tx    = tl.cx + sx * W/2 + skewX * H/2;
        doorOverlay.ty    = tl.cy + skewY * W/2 + sy * H/2;
    }

    function getOverlayCorners() {
        if (!doorCornerPositions) return null;
        const { tl, tr, bl } = doorCornerPositions;
        const br = { cx: tr.cx + bl.cx - tl.cx, cy: tr.cy + bl.cy - tl.cy };
        const ox = canvas.width/2 + panX, oy = canvas.height/2 + panY;
        const ts = p => ({ x: ox + p.cx * scaleFactor, y: oy + p.cy * scaleFactor });
        return { tl: ts(tl), tr: ts(tr), bl: ts(bl), br: ts(br) };
    }

    function getHitOverlayCorner(clientX, clientY) {
        if (!placementMode || !doorCornerPositions) return null;
        const rect = canvas.getBoundingClientRect();
        const ratioX = canvas.width  / rect.width;
        const ratioY = canvas.height / rect.height;
        const mx = (clientX - rect.left) * ratioX;
        const my = (clientY - rect.top)  * ratioY;
        const c = getOverlayCorners();
        let best = null, bestD = 20;
        for (const [k, pt] of Object.entries(c)) {
            const d = Math.hypot(mx - pt.x, my - pt.y);
            if (d < bestD) { bestD = d; best = k; }
        }
        return best;
    }

    container.addEventListener('mousedown', function(e) {
        const corner = getHitOverlayCorner(e.clientX, e.clientY);
        if (corner) {
            overlayDrag = {
                corner,
                startPositions: {
                    tl: { ...doorCornerPositions.tl },
                    tr: { ...doorCornerPositions.tr },
                    bl: { ...doorCornerPositions.bl },
                },
                startMx: e.clientX,
                startMy: e.clientY,
            };
            return;
        }
        if (placementMode) {
            overlayDrag = {
                corner: 'move',
                startPositions: {
                    tl: { ...doorCornerPositions.tl },
                    tr: { ...doorCornerPositions.tr },
                    bl: { ...doorCornerPositions.bl },
                },
                startMx: e.clientX,
                startMy: e.clientY,
            };
            return;
        }
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
                doorCornerPositions.bl = { cx: sp.bl.cx + dcx, cy: sp.bl.cy + dcy };
            } else if (corner === 'tl') {
                doorCornerPositions.tl = { cx: sp.tl.cx + dcx, cy: sp.tl.cy + dcy };
            } else if (corner === 'tr') {
                doorCornerPositions.tr = { cx: sp.tr.cx + dcx, cy: sp.tr.cy + dcy };
            } else if (corner === 'bl') {
                doorCornerPositions.bl = { cx: sp.bl.cx + dcx, cy: sp.bl.cy + dcy };
            } else if (corner === 'br') {
                // TL 고정, 마우스까지 거리 비율로 TR·BL 균등 스케일
                const tl = sp.tl;
                const origBrCx = sp.tr.cx + sp.bl.cx - tl.cx;
                const origBrCy = sp.tr.cy + sp.bl.cy - tl.cy;
                const origD = Math.hypot(origBrCx - tl.cx, origBrCy - tl.cy);
                const newD  = Math.hypot(origBrCx + dcx - tl.cx, origBrCy + dcy - tl.cy);
                if (origD > 0) {
                    const s = Math.max(0.05, newD / origD);
                    doorCornerPositions.tr = { cx: tl.cx + s*(sp.tr.cx-tl.cx), cy: tl.cy + s*(sp.tr.cy-tl.cy) };
                    doorCornerPositions.bl = { cx: tl.cx + s*(sp.bl.cx-tl.cx), cy: tl.cy + s*(sp.bl.cy-tl.cy) };
                }
            }

            updateOverlayFromCorners();
            draw();
            return;
        }
        if (placementMode) {
            const c = getHitOverlayCorner(e.clientX, e.clientY);
            canvas.style.cursor = c ? (c==='tl'||c==='br' ? 'nwse-resize' : 'nesw-resize') : 'grab';
        }
        if (!isDragging) return;
        panX = e.clientX - startX;
        panY = e.clientY - startY;
        draw();
    });
    window.addEventListener('mouseup', function() {
        overlayDrag = null;
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
                bl: { cx: -W/2, cy:  H/2 },
            };
            updateOverlayFromCorners();
        } else {
            doorCornerPositions = null;
            placementNaturalSize = null;
            canvas.style.cursor = 'grab';
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
    updateDoorCountOptions();

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

    const CREATED_KEY  = 'pmok_sabunteok_created';
    const MODIFIED_KEY = 'pmok_sabunteok_modified';
    const VERSIONS_KEY = 'pmok_sabunteok_versions';
    const BG_IMAGE_KEY = 'pmok_sabunteok_bg';
    const THUMBS_KEY   = 'pmok_sabunteok_thumbs';
    const MAX_VERSIONS = 20;

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

    if (!localStorage.getItem(CREATED_KEY)) {
        localStorage.setItem(CREATED_KEY, Date.now());
    }
    document.getElementById('dateCreated').textContent = fmtDate(Number(localStorage.getItem(CREATED_KEY)));

    const savedModified = localStorage.getItem(MODIFIED_KEY) || localStorage.getItem(CREATED_KEY);
    document.getElementById('dateModified').textContent = fmtDate(Number(savedModified));

    function updateModified() {
        const now = Date.now();
        localStorage.setItem(MODIFIED_KEY, now);
        document.getElementById('dateModified').textContent = fmtDate(now);
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
            finish:    document.getElementById('txtFinish').value,
            frameColor: selectedFrameColor,
            slatColor:  selectedSlatColor,
            panX, panY, scaleFactor,
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
        document.getElementById('txtFinish').value  = p.finish;
        document.getElementById('pungpanCtrl').style.display = p.pungpanOn ? 'block' : 'none';
        if (p.panX !== undefined) { panX = p.panX; panY = p.panY; scaleFactor = p.scaleFactor; }
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
            item.innerHTML = `<span class="ver-num">v${realIdx + 1}</span><span class="ver-date">${fmtDate(ver.savedAt)}</span>`;
            item.addEventListener('click', () => {
                currentVerIdx = realIdx;
                applyParams(ver.params);
                document.getElementById('verLabel').textContent = 'v' + (realIdx + 1);
                renderVerList();
                document.getElementById('verDropdown').classList.remove('open');
            });
            list.appendChild(item);
        });
    }

    function loadVersions() {
        try { versions = JSON.parse(localStorage.getItem(VERSIONS_KEY)) || []; } catch(e) { versions = []; }
        if (versions.length > 0) {
            currentVerIdx = versions.length - 1;
            document.getElementById('verLabel').textContent = 'v' + (currentVerIdx + 1);
        }
        renderVerList();
    }

    function saveVersion() {
        const badge = document.querySelector('.hdr-title-badge');
        if (!document.getElementById('drawingName').value.trim()) {
            badge.classList.remove('shake');
            void badge.offsetWidth;
            badge.classList.add('shake');
            badge.addEventListener('animationend', () => badge.classList.remove('shake'), { once: true });
            document.getElementById('drawingName').focus();
            return;
        }
        versions.push({ savedAt: Date.now(), params: getParams() });
        if (versions.length > MAX_VERSIONS) versions.shift();
        localStorage.setItem(VERSIONS_KEY, JSON.stringify(versions));
        currentVerIdx = versions.length - 1;
        document.getElementById('verLabel').textContent = 'v' + (currentVerIdx + 1);
        renderVerList();
        updateModified();
    }

    document.getElementById('btnSave').addEventListener('click', saveVersion);

    const verBtn      = document.getElementById('verBtn');
    const verDropdown = document.getElementById('verDropdown');
    verBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        verDropdown.classList.toggle('open');
    });
    document.addEventListener('click', () => verDropdown.classList.remove('open'));

    loadVersions();

    // ── 도면 이름 자동 저장 ────────────────────────
    const NAME_KEY    = 'pmok_sabunteok_name';
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

        exportCanvas.width = canvas.width * 2;
        exportCanvas.height = canvas.height * 2;

        exportCtx.scale(2, 2);

        // 배경
        exportCtx.fillStyle = '#E5E7EA';
        exportCtx.fillRect(0, 0, canvas.width, canvas.height);

        // 기존 캔버스 복사
        exportCtx.drawImage(canvas, 0, 0);

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

        exportCanvas.width = canvas.width * 2;
        exportCanvas.height = canvas.height * 2;

        exportCtx.scale(2, 2);

        // 배경
        exportCtx.fillStyle = '#ffffff';
        exportCtx.fillRect(0, 0, canvas.width, canvas.height);

        // 원본 그리기
        exportCtx.drawImage(canvas, 0, 0);

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