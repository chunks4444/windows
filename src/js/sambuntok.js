    let appBackgroundImage = null;

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

    // ── 나뭇결 패턴 ───────────────────────────────
    let vGrainPat = null;
    let hGrainPat = null;
    let grainOn   = true;
    let rotateOn  = true;

    function makeGrainTile(w, h, isVertical) {
        const tc = document.createElement('canvas');
        tc.width = w; tc.height = h;
        const tx = tc.getContext('2d');
        const dim = isVertical ? w : h;
        let pos = 0;
        while (pos < dim) {
            pos += 5 + Math.random() * 18;
            const alpha = 0.012 + Math.random() * 0.038;
            tx.beginPath();
            tx.strokeStyle = `rgba(40,18,4,${alpha})`;
            tx.lineWidth = 0.3 + Math.random() * 1.0;
            if (isVertical) {
                tx.moveTo(pos, 0);
                tx.lineTo(pos + (Math.random() - 0.5) * 8, h);
            } else {
                tx.moveTo(0, pos);
                tx.lineTo(w, pos + (Math.random() - 0.5) * 8);
            }
            tx.stroke();
        }
        return tc;
    }

    function applyGrain(x, y, w, h, isVertical) {
        if (!grainOn) return;
        // ctx 준비된 후 첫 호출 시 패턴 생성 (lazy init)
        if (!vGrainPat) {
            vGrainPat = ctx.createPattern(makeGrainTile(400, 1200, true),  'repeat');
            hGrainPat = ctx.createPattern(makeGrainTile(1200, 400, false), 'repeat');
        }
        ctx.save();
        ctx.globalCompositeOperation = 'multiply';
        ctx.fillStyle = isVertical ? vGrainPat : hGrainPat;
        ctx.fillRect(x, y, w, h);
        ctx.restore();
    }

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
    const btnAICompose = document.getElementById('btnAICompose');
    const aiFileUploader = document.getElementById('aiFileUploader');

    btnAICompose.addEventListener('click', function() {
        aiFileUploader.click();
    });

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
    const rightPanel  = document.getElementById('rightPanel');
    const thumbList   = document.getElementById('thumbList');
    const btnAddThumb = document.getElementById('btnAddThumb');

    // 이미지 목록: [{id, src, img}]
    let thumbImages   = [];
    let activeThumbId = null;

    function setActiveThumb(id) {
        activeThumbId = id;
        const found = thumbImages.find(t => t.id === id);
        appBackgroundImage = found ? found.img : null;

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
            if (activeThumbId === id) {
                // 다음 이미지 활성화 또는 패널 닫기
                if (thumbImages.length > 0) {
                    setActiveThumb(thumbImages[thumbImages.length - 1].id);
                } else {
                    appBackgroundImage = null;
                    activeThumbId = null;
                    rightPanel.classList.remove('open');
                    draw();
                }
            }
        });

        item.appendChild(img);
        item.appendChild(btn);
        item.addEventListener('click', () => setActiveThumb(id));
        thumbList.appendChild(item);
    }

    btnAddThumb.addEventListener('click', () => aiFileUploader.click());

    aiFileUploader.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = function(event) {
                const id = Date.now() + Math.random();
                const src = event.target.result;

                const imgObj = new Image();
                imgObj.src = src;
                imgObj.onload = function() {
                    thumbImages.push({ id, src, img: imgObj });
                    addThumbItem(id, src, file.name);
                    rightPanel.classList.add('open');
                    setActiveThumb(id);
                };
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

    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    ctx.save();

    ctx.translate(
        canvas.width / 2 + panX,
        canvas.height / 2 + panY
    );

    ctx.scale(scaleFactor, scaleFactor);



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

            for (let y = startY - rowStep, rIdx = 0; y < iTop + iH + rowStep; y += rowStep, rIdx++) {
                for (let x = iLeft - width; x < iLeft + iW + width; x += width) {
                    const offX = (rIdx % 2 === 0) ? width / 2 : 0;
                    const cx = x + offX, cy = y;
                    for (let i = 0; i < 6; i++) {
                        ctx.beginPath();
                        ctx.moveTo(cx, cy);
                        ctx.lineTo(cx + size * Math.cos(i * Math.PI / 3),
                                   cy + size * Math.sin(i * Math.PI / 3));
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

            for (let x = startX - colStep, cIdx = 0; x < iLeft + iW + colStep; x += colStep, cIdx++) {
                for (let y = iTop - width; y < iTop + iH + width; y += width) {
                    const offY = (cIdx % 2 === 0) ? width / 2 : 0;
                    const cx = x, cy = y + offY;
                    for (let i = 0; i < 6; i++) {
                        const angle = i * Math.PI / 3 + Math.PI / 2;
                        ctx.beginPath();
                        ctx.moveTo(cx, cy);
                        ctx.lineTo(cx + size * Math.cos(angle),
                                   cy + size * Math.sin(angle));
                        ctx.stroke();
                    }
                }
            }
            ctx.restore();
        }

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
        applyGrain(toCanvasX(0), toCanvasY(0), geo.frameW * baseScale, geo.outerH * baseScale, true);
        // 상부 가로 울거미
        ctx.fillRect(toCanvasX(geo.frameW), toCanvasY(0), geo.innerW * baseScale, geo.frameHTop * baseScale);
        applyGrain(toCanvasX(geo.frameW), toCanvasY(0), geo.innerW * baseScale, geo.frameHTop * baseScale, false);
        // 하단 울거미
        ctx.fillRect(toCanvasX(geo.frameW), toCanvasY(geo.frameHTop + geo.innerH), geo.innerW * baseScale, geo.frameHBottom * baseScale);
        applyGrain(toCanvasX(geo.frameW), toCanvasY(geo.frameHTop + geo.innerH), geo.innerW * baseScale, geo.frameHBottom * baseScale, false);
        // 우측 세로 울거미
        ctx.fillRect(toCanvasX(geo.outerW - geo.frameW), toCanvasY(0), geo.frameW * baseScale, geo.outerH * baseScale);
        applyGrain(toCanvasX(geo.outerW - geo.frameW), toCanvasY(0), geo.frameW * baseScale, geo.outerH * baseScale, true);
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

    ctx.restore();
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

    container.addEventListener('mousedown', function(e) {
        isDragging = true;
        startX = e.clientX - panX;
        startY = e.clientY - panY;
    });
    window.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        panX = e.clientX - startX;
        panY = e.clientY - startY;
        draw();
    });
    window.addEventListener('mouseup', function() {
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

        const duration = 270;
        const start = performance.now();
        function animateResize(now) {
            resizeCanvas();
            if (now - start < duration) requestAnimationFrame(animateResize);
        }
        requestAnimationFrame(animateResize);
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
    document.getElementById('chkGrain').addEventListener('change', e => {
        grainOn = e.target.checked;
        draw();
    });
    document.getElementById('chkRotate').addEventListener('change', e => {
        rotateOn = e.target.checked;
        draw();
    });
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

    const CREATED_KEY  = 'pmok_sambuntok_created';
    const MODIFIED_KEY = 'pmok_sambuntok_modified';
    const VERSIONS_KEY = 'pmok_sambuntok_versions';
    const MAX_VERSIONS = 20;

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
            rotate:    document.getElementById('chkRotate').checked,
            finish:    document.getElementById('txtFinish').value,
            grain:     document.getElementById('chkGrain').checked,
            frameColor: selectedFrameColor,
            slatColor:  selectedSlatColor,
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
        document.getElementById('chkRotate').checked = p.rotate;
        document.getElementById('txtFinish').value   = p.finish;
        document.getElementById('chkGrain').checked  = p.grain;
        grainOn  = p.grain;
        rotateOn = p.rotate;
        document.getElementById('pungpanCtrl').style.display = p.pungpanOn ? 'block' : 'none';
        vGrainPat = null; hGrainPat = null;
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
    const NAME_KEY    = 'pmok_sambuntok_name';
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