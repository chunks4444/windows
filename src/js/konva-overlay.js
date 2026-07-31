/**
 * Konva 오버레이 모듈 — 도형·SVG 삽입·살 선택
 *
 * getState() → { logW, logH, panX, panY, scaleFactor, lastSlatPx, lastCellSize }
 * getSegMap() → Map  /  deletedSegs → Set  /  draw() → void
 *
 * 좌표계: 모든 Konva 노드는 canvas "logic 좌표" (translated+scaled 공간) 사용.
 * drawSlatOverlay() 호출 시마다 konvaShapeLayer 변환을 canvas와 동기화.
 */
window.initKonvaOverlay = function ({ canvas, getState, getSegMap, deletedSegs, draw, snapNode, nodeToCtx }) {

    // ── DOM ──────────────────────────────────────────────
    const konvaContainer  = document.getElementById('konvaStageContainer');
    const konvaShapePanel = document.getElementById('konvaShapePanel');
    const slatSelPanel    = document.getElementById('slatSelPanel');

    // ── Konva Stage + Layer ───────────────────────────────
    const konvaStage = new Konva.Stage({
        container: konvaContainer,
        width: canvas.offsetWidth,
        height: canvas.offsetHeight,
    });
    const patternLayer    = new Konva.Layer({ listening: true });
    const konvaShapeLayer = new Konva.Layer();
    konvaStage.add(patternLayer);
    konvaStage.add(konvaShapeLayer);

    const konvaTransformer = new Konva.Transformer({
        rotateEnabled: true,
        enabledAnchors: ['top-left','top-right','bottom-left','bottom-right',
                         'middle-left','middle-right','top-center','bottom-center'],
        boundBoxFunc: (oldBox, newBox) =>
            (newBox.width > 5 && newBox.height > 5 ? newBox : oldBox),
    });
    konvaShapeLayer.add(konvaTransformer);

    // ── 상태 ─────────────────────────────────────────────
    let konvaShapeMode  = null;
    let konvaLineStart  = null;   // logic coords
    let konvaLinePreview = null;
    let slatColorOverrides = {};
    let _slatSelectMode  = false;
    let _selectedLineKey = null;
    let _patternClipGroups = {};
    let _patternSlatNodes  = {};
    let _usePatternLayer   = false;
    let _activeClipGroup   = null;

    // 도형(원/선/사각형/텍스트)의 마지막 동기화 시점 문틀 변환 — 문 크기를 바꾸면
    // baseScale/offsetX/offsetY가 달라지는데, 도형은 pan/zoom 좌표계에만 붙어있고
    // 문틀 변환과는 무관하게 저장돼 있어서 문 크기 변경 시 그리드만 리스케일되고
    // 도형은 그 자리에 그대로 남아 "따로 노는" 현상이 생겼다.
    let _lastDoorX = null, _lastDoorY = null, _lastDoorScale = null;

    // ── 좌표 변환 ─────────────────────────────────────────
    // stage px → logic coords (konvaShapeLayer 기준)
    function stageToLogic(sx, sy) {
        const s = getState();
        return {
            x: (sx - s.logW / 2 - s.panX) / s.scaleFactor,
            y: (sy - s.logH / 2 - s.panY) / s.scaleFactor,
        };
    }

    // ── 레이어 변환 동기화 ────────────────────────────────
    // canvas의 translate+scale 좌표계와 일치시킴
    function syncLayerTransform() {
        const s = getState();
        if (!s.logW) return;
        const pos   = { x: s.logW / 2 + s.panX, y: s.logH / 2 + s.panY };
        const scale = { x: s.scaleFactor, y: s.scaleFactor };
        patternLayer.position(pos);    patternLayer.scale(scale);
        konvaShapeLayer.position(pos); konvaShapeLayer.scale(scale);
        syncShapesToDoor(s);
        patternLayer.batchDraw();
        konvaShapeLayer.batchDraw();
    }

    // 문틀(offsetX/offsetY/baseScale)이 이전 프레임과 달라졌으면(문 크기 변경 등) 모든
    // 도형을 같은 비율로 이동·리스케일해서 문틀에 붙어 따라오게 한다. 엔진이 아직
    // lastOLeft/lastOTop/lastBaseScale을 안 주면(구버전) 조용히 건너뛴다.
    function syncShapesToDoor(s) {
        if (!s.lastBaseScale) return;
        if (_lastDoorScale !== null &&
            (_lastDoorX !== s.lastOLeft || _lastDoorY !== s.lastOTop || _lastDoorScale !== s.lastBaseScale)) {
            const ratio = s.lastBaseScale / _lastDoorScale;
            const dx = s.lastOLeft, dy = s.lastOTop, ox = _lastDoorX, oy = _lastDoorY;
            konvaShapeLayer.getChildren(n => n !== konvaTransformer).forEach(shape => {
                // 격자 교점에 스냅되어 그려진 선(nodeRef 보유)은 비율 계산 대신 그 교점의
                // 최신 실제 위치를 다시 조회해서 정확히 맞춘다 — 일반 도형(자유 위치)만 비율로 이동.
                const ref = typeof shape.getAttr === 'function' ? shape.getAttr('nodeRef') : null;
                if (ref && nodeToCtx) {
                    const p1 = nodeToCtx(ref.xi1, ref.yi1);
                    const p2 = nodeToCtx(ref.xi2, ref.yi2);
                    shape.points([p1.x, p1.y, p2.x, p2.y]);
                } else if (shape.getClassName() === 'Line') {
                    const pts = shape.points();
                    const newPts = [];
                    for (let i = 0; i < pts.length; i += 2) {
                        newPts.push(dx + (pts[i]     - ox) * ratio);
                        newPts.push(dy + (pts[i + 1] - oy) * ratio);
                    }
                    shape.points(newPts);
                } else {
                    shape.x(dx + (shape.x() - ox) * ratio);
                    shape.y(dy + (shape.y() - oy) * ratio);
                }
                if (typeof shape.strokeWidth === 'function' && shape.strokeWidth() != null) {
                    shape.strokeWidth(shape.strokeWidth() * ratio);
                }
                const cls = shape.getClassName();
                if (cls === 'Circle') shape.radius(shape.radius() * ratio);
                else if (cls === 'Rect') { shape.width(shape.width() * ratio); shape.height(shape.height() * ratio); }
                else if (cls === 'Text') shape.fontSize(shape.fontSize() * ratio);
            });
            konvaTransformer.forceUpdate();
        }
        _lastDoorX = s.lastOLeft; _lastDoorY = s.lastOTop; _lastDoorScale = s.lastBaseScale;
    }

    // ── Stage 크기 동기화 ─────────────────────────────────
    function syncSize() {
        konvaStage.width(canvas.offsetWidth);
        konvaStage.height(canvas.offsetHeight);
        konvaContainer.style.width  = canvas.offsetWidth  + 'px';
        konvaContainer.style.height = canvas.offsetHeight + 'px';
        konvaContainer.style.top    = canvas.offsetTop    + 'px';
        konvaContainer.style.left   = canvas.offsetLeft   + 'px';
        syncLayerTransform();
    }

    // ── 모드 설정 ─────────────────────────────────────────
    function setMode(mode) {
        if (mode && _slatSelectMode) {
            _slatSelectMode  = false;
            _selectedLineKey = null;
            updatePatternHighlight();
            el('btnSlatSelect')?.classList.remove('cv-btn-active');
            if (_usePatternLayer) konvaContainer.style.pointerEvents = 'none';
            canvas.style.cursor = '';
        }
        konvaShapeMode = mode;
        konvaLineStart = null;
        if (konvaLinePreview) { konvaLinePreview.destroy(); konvaLinePreview = null; }
        if (!mode) { konvaTransformer.nodes([]); konvaShapeLayer.batchDraw(); }
        konvaContainer.style.pointerEvents = mode ? 'auto' : 'none';
        const isDrawMode = ['circle','line','rect','text'].includes(mode);
        const cursorVal  = isDrawMode ? 'crosshair' : mode === 'select' ? 'default' : '';
        konvaContainer.style.cursor = cursorVal || 'default';
        canvas.style.cursor = cursorVal;
        document.getElementById('btnShapeSelect')?.classList.toggle('cv-btn-active', mode === 'select');
        document.getElementById('btnShapeCircle')?.classList.toggle('cv-btn-active', mode === 'circle');
        document.getElementById('btnShapeLine')?.classList.toggle('cv-btn-active', mode === 'line');
        document.getElementById('btnShapeRect')?.classList.toggle('cv-btn-active', mode === 'rect');
        document.getElementById('btnShapeText')?.classList.toggle('cv-btn-active', mode === 'text');
        updatePanels();
    }

    function addShape(shape) {
        shape.draggable(true);
        konvaShapeLayer.add(shape);
        konvaTransformer.moveToTop();
        konvaShapeLayer.batchDraw();
    }

    // ── SVG 삽입 (Konva.Image) ────────────────────────────
    function addKonvaInsert(url, naturalW, naturalH) {
        const s = getState();
        const w = naturalW || 100, h = naturalH || 100;
        const targetSize = Math.min(s.logW, s.logH) * 0.25;
        // logic 좌표 단위 크기
        const nodeW = (targetSize / Math.max(w, h) / s.scaleFactor) * w;
        const nodeH = (targetSize / Math.max(w, h) / s.scaleFactor) * h;

        const img = new window.Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => {
            const node = new Konva.Image({
                x: 0, y: 0,
                image: img,
                width: nodeW, height: nodeH,
                offsetX: nodeW / 2, offsetY: nodeH / 2,
                rotation: 0,
                draggable: true,
            });
            node.setAttr('_ins_url', url);
            node.setAttr('_ins_nw', w);
            node.setAttr('_ins_nh', h);
            addShape(node);
            konvaTransformer.nodes([node]);
            konvaShapeLayer.batchDraw();
            updatePanels();
        };
        img.src = url;
    }

    // ── Konva 이벤트 ──────────────────────────────────────
    konvaStage.on('click tap', e => {
        const clicked  = e.target;
        const pmokType = clicked.getAttr?.('_pmok_type');

        // 전용 살 선택 모드 (canvas 폴백용 / doorCornerPositions)
        if (_slatSelectMode) {
            if (pmokType === 'slat') {
                const lineKey = clicked.getAttr('_pmok_lineKey');
                _selectedLineKey = (lineKey === _selectedLineKey) ? null : lineKey;
            } else if (pmokType === 'frame' || clicked === konvaStage) {
                _selectedLineKey = null;
            }
            updatePatternHighlight();
            updatePanels();
            return;
        }

        if (konvaShapeMode !== 'select') return;

        if (pmokType === 'slat') {
            // 선택 모드에서 살 클릭 → 도형 선택 해제 + 살 선택
            konvaTransformer.nodes([]);
            const lineKey = clicked.getAttr('_pmok_lineKey');
            _selectedLineKey = (lineKey === _selectedLineKey) ? null : lineKey;
            updatePatternHighlight();
        } else if (pmokType === 'frame' || clicked === konvaStage) {
            // 프레임·빈 공간 클릭 → 모두 해제
            konvaTransformer.nodes([]);
            _selectedLineKey = null;
            updatePatternHighlight();
        } else {
            // 도형 클릭 → 살 선택 해제 + 도형 선택
            const isAnchor = clicked.getParent?.() === konvaTransformer;
            if (!isAnchor) {
                konvaTransformer.nodes([clicked]);
                _selectedLineKey = null;
                updatePatternHighlight();
            }
        }
        konvaShapeLayer.batchDraw();
        updatePanels();
    });

    konvaStage.on('pointerdown', e => {
        if (!['circle','line','rect','text'].includes(konvaShapeMode)) return;
        const pos  = konvaStage.getPointerPosition();
        const lpos = stageToLogic(pos.x, pos.y);

        if (konvaShapeMode === 'circle') {
            addShape(new Konva.Circle({
                x: lpos.x, y: lpos.y, radius: 30 / getState().scaleFactor,
                stroke: '#e03030', strokeWidth: 2 / getState().scaleFactor,
                fill: 'rgba(224,48,48,0.08)',
                strokeScaleEnabled: false,
                // 채우기 투명도가 낮으면 안쪽 클릭이 히트되지 않는 경우가 있어 전체 영역을 명시적으로 히트 처리
                hitFunc(context, shape) {
                    context.beginPath();
                    context.arc(0, 0, shape.radius(), 0, Math.PI * 2, false);
                    context.closePath();
                    context.fillStrokeShape(shape);
                },
            }));
            konvaShapeLayer.batchDraw();

        } else if (konvaShapeMode === 'line') {
            // 격자 교점 근처를 클릭하면 그 교점에 붙여서 기억해둔다 — 그래야 문 크기가
            // 바뀌어도(교점 위치 자체가 다시 계산되므로) 정확한 위치로 다시 그릴 수 있다.
            // 교점과 멀면(자유 위치) snapNode가 null을 줘서 기존처럼 자유롭게 그려진다.
            const snapped = snapNode?.(lpos.x, lpos.y);
            const pt = snapped || lpos;
            if (!konvaLineStart) {
                konvaLineStart = { x: pt.x, y: pt.y, xi: snapped?.xi, yi: snapped?.yi };
                konvaLinePreview = new Konva.Line({
                    points: [pt.x, pt.y, pt.x, pt.y],
                    stroke: '#e03030', strokeWidth: 2 / getState().scaleFactor,
                    dash: [6 / getState().scaleFactor, 3 / getState().scaleFactor],
                    listening: false, strokeScaleEnabled: false,
                });
                konvaShapeLayer.add(konvaLinePreview);
                konvaShapeLayer.batchDraw();
            } else {
                const line = new Konva.Line({
                    points: [konvaLineStart.x, konvaLineStart.y, pt.x, pt.y],
                    stroke: '#e03030', strokeWidth: 2 / getState().scaleFactor,
                    lineCap: 'round', strokeScaleEnabled: false,
                    hitStrokeWidth: 24 / getState().scaleFactor, // 선이 얇아 터치로 선택하기 어려운 것 보완
                });
                if (konvaLineStart.xi !== undefined && snapped) {
                    line.setAttr('nodeRef', { xi1: konvaLineStart.xi, yi1: konvaLineStart.yi, xi2: snapped.xi, yi2: snapped.yi });
                }
                if (konvaLinePreview) { konvaLinePreview.destroy(); konvaLinePreview = null; }
                konvaLineStart = null;
                addShape(line);
                konvaShapeLayer.batchDraw();
            }

        } else if (konvaShapeMode === 'rect') {
            const s  = getState();
            const hw = 50 / s.scaleFactor, hh = 30 / s.scaleFactor;
            const rect = new Konva.Rect({
                x: lpos.x - hw, y: lpos.y - hh, width: hw * 2, height: hh * 2,
                stroke: '#e03030', strokeWidth: 2 / s.scaleFactor,
                fill: 'rgba(224,48,48,0.06)', cornerRadius: 2,
                strokeScaleEnabled: false,
                // 채우기 투명도가 낮으면 안쪽 클릭이 히트되지 않는 경우가 있어 전체 영역을 명시적으로 히트 처리
                hitFunc(context, shape) {
                    context.beginPath();
                    context.rect(0, 0, shape.width(), shape.height());
                    context.closePath();
                    context.fillStrokeShape(shape);
                },
            });
            addShape(rect);
            konvaTransformer.nodes([rect]);
            konvaShapeLayer.batchDraw();

        } else if (konvaShapeMode === 'text') {
            const s = getState();
            const text = new Konva.Text({
                x: lpos.x, y: lpos.y,
                text: '텍스트', fontSize: 16 / s.scaleFactor,
                fontFamily: 'sans-serif', fill: '#e03030', padding: 0,
            });
            addShape(text);
            konvaTransformer.nodes([text]);
            konvaShapeLayer.batchDraw();
            text.on('dblclick dbltap', () => editText(text));
        }
        updatePanels();
    });

    konvaStage.on('pointermove', () => {
        if (konvaShapeMode !== 'line' || !konvaLineStart || !konvaLinePreview) return;
        const pos  = konvaStage.getPointerPosition();
        const lpos = stageToLogic(pos.x, pos.y);
        const snapped = snapNode?.(lpos.x, lpos.y);
        const pt = snapped || lpos;
        konvaLinePreview.points([konvaLineStart.x, konvaLineStart.y, pt.x, pt.y]);
        konvaShapeLayer.batchDraw();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') setMode(null);
        if ((e.key === 'Delete' || e.key === 'Backspace') && konvaTransformer.nodes().length) {
            konvaTransformer.nodes().forEach(n => n.destroy());
            konvaTransformer.nodes([]);
            konvaShapeLayer.batchDraw();
            updatePanels();
        }
    });

    // ── 텍스트 인라인 편집 ────────────────────────────────
    function editText(textNode) {
        konvaTransformer.nodes([]);
        konvaShapeLayer.batchDraw();
        const apos  = konvaStage.container().getBoundingClientRect();
        const absP  = textNode.absolutePosition();
        const absS  = textNode.getAbsoluteScale();
        const ta = document.createElement('textarea');
        ta.value = textNode.text();
        Object.assign(ta.style, {
            position: 'fixed',
            top:  (apos.top  + absP.y) + 'px',
            left: (apos.left + absP.x) + 'px',
            fontSize: (textNode.fontSize() * absS.x) + 'px',
            fontFamily: textNode.fontFamily(),
            color: textNode.fill(),
            border: '1px dashed #2979ff',
            background: 'rgba(255,255,255,0.92)',
            padding: '4px', minWidth: '80px',
            outline: 'none', zIndex: 9999, lineHeight: '1.2',
        });
        document.body.appendChild(ta);
        ta.focus(); ta.select();
        const finish = () => {
            if (!ta.parentNode) return;
            textNode.text(ta.value || '텍스트');
            ta.remove();
            konvaTransformer.nodes([textNode]);
            konvaShapeLayer.batchDraw();
        };
        ta.addEventListener('blur', finish);
        ta.addEventListener('keydown', ev => {
            if (ev.key === 'Enter' && !ev.shiftKey) { ev.preventDefault(); finish(); }
        });
    }

    // ── 패널 ─────────────────────────────────────────────
    function toHex(val) {
        if (!val || val === 'transparent') return '#e03030';
        const c = document.createElement('canvas').getContext('2d');
        c.fillStyle = val;
        const v = c.fillStyle;
        if (v.startsWith('#')) return v;
        const m = v.match(/\d+/g);
        return m ? '#' + m.slice(0,3).map(x => (+x).toString(16).padStart(2,'0')).join('') : '#e03030';
    }

    function updatePanels() {
        if (!konvaShapePanel || !slatSelPanel) return;
        const hasShape = konvaTransformer.nodes().length > 0;
        konvaShapePanel.style.display = hasShape ? 'flex' : 'none';
        slatSelPanel.style.display    = _selectedLineKey ? 'flex' : 'none';

        if (hasShape) {
            const node = konvaTransformer.nodes()[0];
            const el   = id => document.getElementById(id);
            const sw   = el('konvaStrokeWidth');
            const sw_v = Math.round(node.strokeWidth?.() ?? 2);
            if (el('konvaStrokeColor')) el('konvaStrokeColor').value = toHex(node.stroke?.() || '#e03030');
            if (el('konvaFillColor'))   el('konvaFillColor').value   = toHex(node.fill?.()   || '#e03030');
            if (sw) {
                const opts = [...sw.options].map(o => +o.value);
                sw.value = String(opts.reduce((a, b) => Math.abs(b - sw_v) < Math.abs(a - sw_v) ? b : a));
            }
            if (el('konvaOpacity')) el('konvaOpacity').value = String(Math.round((node.opacity?.() ?? 1) * 100));
        }
        if (_selectedLineKey) {
            const oc = slatColorOverrides[_selectedLineKey];
            const el = document.getElementById('slatOverrideColor');
            if (el) el.value = oc || '#e03030';
        }
    }

    // ── 버튼 이벤트 ──────────────────────────────────────
    const el = id => document.getElementById(id);

    el('btnShapeSelect')?.addEventListener('click', () => setMode(konvaShapeMode === 'select' ? null : 'select'));
    el('btnShapeCircle')?.addEventListener('click', () => setMode(konvaShapeMode === 'circle' ? null : 'circle'));
    el('btnShapeLine')?.addEventListener('click',   () => setMode(konvaShapeMode === 'line'   ? null : 'line'));
    el('btnShapeRect')?.addEventListener('click',   () => setMode(konvaShapeMode === 'rect'   ? null : 'rect'));
    el('btnShapeText')?.addEventListener('click',   () => setMode(konvaShapeMode === 'text'   ? null : 'text'));
    el('btnShapeClear')?.addEventListener('click', () => {
        konvaShapeLayer.find('Circle,Line,Rect,Text,Image').forEach(n => n.destroy());
        konvaTransformer.nodes([]);
        konvaShapeLayer.batchDraw();
        setMode(null);
    });

    el('konvaStrokeColor')?.addEventListener('input', e => {
        konvaTransformer.nodes().forEach(n => n.stroke?.(e.target.value));
        konvaShapeLayer.batchDraw();
    });
    el('konvaFillColor')?.addEventListener('input', e => {
        konvaTransformer.nodes().forEach(n => n.fill?.(e.target.value));
        konvaShapeLayer.batchDraw();
    });
    el('konvaStrokeWidth')?.addEventListener('change', e => {
        const s = getState();
        konvaTransformer.nodes().forEach(n => n.strokeWidth?.(+e.target.value / s.scaleFactor));
        konvaShapeLayer.batchDraw();
    });
    el('konvaOpacity')?.addEventListener('input', e => {
        konvaTransformer.nodes().forEach(n => n.opacity?.(+e.target.value / 100));
        konvaShapeLayer.batchDraw();
    });

    // ── 살 패널 ──────────────────────────────────────────
    el('btnApplySlatColor')?.addEventListener('click', () => {
        if (!_selectedLineKey) return;
        slatColorOverrides[_selectedLineKey] = el('slatOverrideColor')?.value || '#e03030';
        draw();
    });
    el('btnResetSlatColor')?.addEventListener('click', () => {
        if (!_selectedLineKey) return;
        delete slatColorOverrides[_selectedLineKey];
        _selectedLineKey = null;
        draw();
        updatePatternHighlight();
        updatePanels();
    });
    function deleteSelectedSlat() {
        if (!_selectedLineKey || !deletedSegs) return;
        const segMap = getSegMap();
        for (const [key, seg] of segMap) {
            if (seg.lineKey === _selectedLineKey) deletedSegs.add(key);
        }
        _selectedLineKey = null;
        updatePanels();
        draw();
    }
    el('btnDeleteSelectedSlat')?.addEventListener('click', deleteSelectedSlat);

    // 살을 선택한 상태에서 Delete/Backspace로도 삭제 (텍스트 입력 중일 땐 무시)
    document.addEventListener('keydown', e => {
        if (!_selectedLineKey) return;
        if (e.key !== 'Delete' && e.key !== 'Backspace') return;
        const tag = document.activeElement?.tagName;
        if (tag === 'INPUT' || tag === 'TEXTAREA' || document.activeElement?.isContentEditable) return;
        e.preventDefault();
        deleteSelectedSlat();
    });

    // ── 살 선택 버튼 ──────────────────────────────────────
    el('btnSlatSelect')?.addEventListener('click', () => {
        const willActivate = !_slatSelectMode;
        if (typeof deactivateAllModes === 'function') deactivateAllModes();
        if (willActivate) {
            _slatSelectMode = true;
            el('btnSlatSelect')?.classList.add('cv-btn-active');
            canvas.style.cursor = 'crosshair';
            if (_usePatternLayer) {
                konvaContainer.style.pointerEvents = 'auto';
                konvaContainer.style.cursor = 'crosshair';
            }
        }
    });

    // ── 공개 API ──────────────────────────────────────────
    function distToSeg(px, py, seg) {
        const dx = seg.ex - seg.cx, dy = seg.ey - seg.cy;
        const len2 = dx * dx + dy * dy;
        if (len2 === 0) return Math.hypot(px - seg.cx, py - seg.cy);
        const t = Math.max(0, Math.min(1, ((px - seg.cx) * dx + (py - seg.cy) * dy) / len2));
        return Math.hypot(px - seg.cx - t * dx, py - seg.cy - t * dy);
    }

    function handleSlatSelect(e) {
        const state = getState();
        const segMap = getSegMap();
        const dpr  = window.devicePixelRatio || 1;
        const rect = canvas.getBoundingClientRect();
        const lx = ((e.clientX - rect.left) * (canvas.width / rect.width / dpr) - (state.logW / 2 + state.panX)) / state.scaleFactor;
        const ly = ((e.clientY - rect.top)  * (canvas.height / rect.height / dpr) - (state.logH / 2 + state.panY)) / state.scaleFactor;
        const threshold = Math.max(state.lastSlatPx * 4, (state.lastCellSize || 1) * 0.35);
        let bestKey = null, bestDist = Infinity;
        for (const [key, seg] of segMap) {
            if (key.startsWith('added:')) continue;
            const d = distToSeg(lx, ly, seg);
            if (d < bestDist) { bestDist = d; bestKey = key; }
        }
        _selectedLineKey = (!bestKey || bestDist > threshold) ? null : (segMap.get(bestKey)?.lineKey ?? null);
        updatePanels();
        draw();
    }

    function drawSlatOverlay() {
        syncLayerTransform();
        if (_usePatternLayer) return; // 색상·하이라이트는 Konva patternLayer에서 처리

        const segMap = getSegMap();
        if (!segMap || segMap.size === 0) return;
        const hasOverride = Object.keys(slatColorOverrides).length > 0;
        if (!hasOverride && !_selectedLineKey) return;

        const state = getState();
        const dpr = window.devicePixelRatio || 1;
        const mainCtx = canvas.getContext('2d');
        mainCtx.save();
        mainCtx.setTransform(dpr, 0, 0, dpr, 0, 0);
        mainCtx.translate(state.logW / 2 + state.panX, state.logH / 2 + state.panY);
        mainCtx.scale(state.scaleFactor, state.scaleFactor);
        mainCtx.lineCap = 'round';

        if (hasOverride) {
            const groups = {};
            for (const [, seg] of segMap) {
                const oc = slatColorOverrides[seg.lineKey];
                if (!oc) continue;
                (groups[oc] = groups[oc] || []).push(seg);
            }
            for (const [color, segs] of Object.entries(groups)) {
                mainCtx.strokeStyle = color;
                mainCtx.lineWidth   = state.lastSlatPx;
                mainCtx.globalAlpha = 1;
                for (const seg of segs) {
                    mainCtx.beginPath();
                    mainCtx.moveTo(seg.cx, seg.cy);
                    mainCtx.lineTo(seg.ex, seg.ey);
                    mainCtx.stroke();
                }
            }
        }

        if (_selectedLineKey) {
            mainCtx.strokeStyle = '#2979ff';
            mainCtx.lineWidth   = Math.max(state.lastSlatPx * 2.5, 3);
            mainCtx.globalAlpha = 0.55;
            for (const [, seg] of segMap) {
                if (seg.lineKey !== _selectedLineKey) continue;
                mainCtx.beginPath();
                mainCtx.moveTo(seg.cx, seg.cy);
                mainCtx.lineTo(seg.ex, seg.ey);
                mainCtx.stroke();
            }
        }
        mainCtx.restore();
    }

    function onDeactivate() {
        _slatSelectMode  = false;
        _selectedLineKey = null;
        updatePatternHighlight();
        updatePanels();
        el('btnSlatSelect')?.classList.remove('cv-btn-active');
        if (_usePatternLayer) {
            konvaContainer.style.pointerEvents = 'none';
            konvaContainer.style.cursor = '';
        }
        canvas.style.cursor = '';
    }

    // ── 패턴 레이어 API ───────────────────────────────────
    function beginPattern() {
        patternLayer.destroyChildren();
        patternLayer.clear(); // 이전 프레임 잔상 즉시 제거 (batchDraw는 비동기)
        _patternClipGroups = {};
        _patternSlatNodes  = {};
        _activeClipGroup   = null;
        _usePatternLayer   = true;
        _editMarkerNode    = null; // destroyChildren()이 같이 파괴함
    }

    function clearPattern() {
        // doorCornerPositions(배치모드)처럼 캔버스가 직접 그릴 때 patternLayer 비우기
        if (_usePatternLayer) {
            patternLayer.destroyChildren();
            patternLayer.clear();
            _patternClipGroups = {};
            _patternSlatNodes  = {};
            _activeClipGroup   = null;
            _editMarkerNode    = null;
        }
        _usePatternLayer = false;
    }

    // 선 추가 모드 시작점 마커 — beginPattern()의 캐시 갱신(buildKonvaPattern)과 무관하게
    // 매 draw()마다 즉시 갱신·표시되어야 하므로 별도 지속 노드로 관리
    let _editMarkerNode = null;

    function setEditMarker(x, y, radius, fill) {
        if (_editMarkerNode) _editMarkerNode.destroy();
        _editMarkerNode = new Konva.Circle({ x, y, radius, fill, listening: false, perfectDrawEnabled: false });
        patternLayer.add(_editMarkerNode);
        patternLayer.batchDraw();
    }

    function clearEditMarker() {
        if (!_editMarkerNode) return;
        _editMarkerNode.destroy();
        _editMarkerNode = null;
        patternLayer.batchDraw();
    }

    function addPatternBg(x, y, w, h) {
        patternLayer.add(new Konva.Rect({ x, y, width: w, height: h, fill: '#ffffff', listening: false, perfectDrawEnabled: false }));
    }

    function addPatternClipGroup(id, x, y, w, h) {
        const group = new Konva.Group({ clipX: x, clipY: y, clipWidth: w, clipHeight: h });
        patternLayer.add(group);
        _activeClipGroup = group;
        _patternClipGroups[id] = group;
    }

    function endPatternClipGroup() {
        _activeClipGroup = null;
    }

    function addPatternSlatRect(groupId, x, y, w, h, baseFill, segKey, lineKey) {
        const oc   = slatColorOverrides[lineKey];
        const fill = oc || baseFill;
        const rect = new Konva.Rect({ x, y, width: w, height: h, fill, listening: true, perfectDrawEnabled: false });
        rect.setAttr('_pmok_type',     'slat');
        rect.setAttr('_pmok_segKey',   segKey);
        rect.setAttr('_pmok_lineKey',  lineKey);
        rect.setAttr('_pmok_baseFill', baseFill);
        _patternSlatNodes[segKey] = rect;
        (_activeClipGroup || patternLayer).add(rect);
    }

    function addPatternRectToGroup(groupId, x, y, w, h, fill, pmokType) {
        const rect = new Konva.Rect({ x, y, width: w, height: h, fill, listening: false, perfectDrawEnabled: false });
        if (pmokType) rect.setAttr('_pmok_type', pmokType);
        (_activeClipGroup || patternLayer).add(rect);
    }

    function addPatternFrameRect(x, y, w, h, fill) {
        _activeClipGroup = null; // 프레임 rect는 항상 clip 밖 (patternLayer 직접)
        const rect = new Konva.Rect({ x, y, width: w, height: h, fill, listening: true, perfectDrawEnabled: false });
        rect.setAttr('_pmok_type', 'frame');
        patternLayer.add(rect);
    }

    function addPatternLine(x1, y1, x2, y2, stroke, width) {
        (_activeClipGroup || patternLayer).add(new Konva.Line({ points: [x1, y1, x2, y2], stroke, strokeWidth: width, lineCap: 'round', listening: false }));
    }

    // addPatternLine과 달리 클릭으로 선택 가능한(listening) 살. 대각선/방사형 살처럼
    // 축에 맞지 않는 살은 addPatternSlatRect(회전 없는 사각형)로 표현할 수 없어
    // Konva.Line + hitStrokeWidth로 클릭 판정 폭을 넓혀서 대신한다.
    function addPatternSlatLine(x1, y1, x2, y2, baseFill, segKey, lineKey, width) {
        const oc = slatColorOverrides[lineKey];
        const line = new Konva.Line({
            points: [x1, y1, x2, y2],
            stroke: oc || baseFill,
            strokeWidth: width,
            lineCap: 'round',
            listening: true,
            hitStrokeWidth: Math.max(width * 3, 16),
        });
        line.setAttr('_pmok_type',     'slat');
        line.setAttr('_pmok_segKey',   segKey);
        line.setAttr('_pmok_lineKey',  lineKey);
        line.setAttr('_pmok_baseFill', baseFill);
        _patternSlatNodes[segKey] = line;
        (_activeClipGroup || patternLayer).add(line);
    }

    function _applySlatColor(node, color) {
        if (node.getClassName() === 'Line') node.stroke(color);
        else node.fill(color);
    }

    function addPatternPolygon(points, fill, pmokType) {
        const line = new Konva.Line({ points, fill, closed: true, strokeWidth: 0, listening: false, perfectDrawEnabled: false });
        if (pmokType) line.setAttr('_pmok_type', pmokType);
        (_activeClipGroup || patternLayer).add(line);
    }

    // 프리젠테이션 모드 썸네일용 — patternLayer에 쌓인 살/울거미/면색 지오메트리만 읽어서 반환.
    // _pmok_type이 없는 rect(장부 등 시공 디테일)는 여전히 제외된다.
    function getPatternGeometry() {
        const frameRects = [];
        const slatRects  = [];
        const slatLines  = [];
        const paintRects = [];
        patternLayer.find('Rect').forEach(node => {
            const type = node.getAttr('_pmok_type');
            if (type === 'frame')    { frameRects.push(node.getClientRect({ relativeTo: patternLayer })); return; }
            if (type === 'slat')     { slatRects.push(node.getClientRect({ relativeTo: patternLayer }));  return; }
            if (type === 'facepaint') {
                const box = node.getClientRect({ relativeTo: patternLayer });
                paintRects.push({ ...box, fill: node.fill() });
            }
        });
        const paintPolygons = [];
        patternLayer.find('Line').forEach(node => {
            if (!node.closed()) {
                if (!node.stroke()) return;
                const t  = node.getAbsoluteTransform(patternLayer);
                const pt = node.points();
                const p1 = t.point({ x: pt[0], y: pt[1] });
                const p2 = t.point({ x: pt[2], y: pt[3] });
                slatLines.push({ x1: p1.x, y1: p1.y, x2: p2.x, y2: p2.y, strokeWidth: node.strokeWidth() });
                return;
            }
            if (node.getAttr('_pmok_type') !== 'facepaint') return;
            const t = node.getAbsoluteTransform(patternLayer);
            const pts = node.points();
            const points = [];
            for (let i = 0; i < pts.length; i += 2) {
                const p = t.point({ x: pts[i], y: pts[i + 1] });
                points.push(p.x, p.y);
            }
            paintPolygons.push({ points, fill: node.fill() });
        });
        return { frameRects, slatRects, slatLines, paintRects, paintPolygons };
    }

    function commitPattern() {
        const s = getState();
        if (s.logW) {
            patternLayer.position({ x: s.logW / 2 + s.panX, y: s.logH / 2 + s.panY });
            patternLayer.scale({ x: s.scaleFactor, y: s.scaleFactor });
        }
        // 노드 재생성 직후 선택 하이라이트·색상 오버라이드를 반영한 뒤 렌더
        for (const [, rect] of Object.entries(_patternSlatNodes)) {
            const lineKey  = rect.getAttr('_pmok_lineKey');
            const baseFill = rect.getAttr('_pmok_baseFill');
            const oc       = slatColorOverrides[lineKey];
            _applySlatColor(rect, lineKey === _selectedLineKey ? 'rgba(41,121,255,0.55)' : (oc || baseFill));
        }
        patternLayer.draw(); // 동기 렌더 — canvas와 같은 프레임에 출력
    }

    // 노드 재생성 없이 transform만 업데이트 (줌·팬 전용)
    function syncPatternTransform() {
        if (!_usePatternLayer) return;
        const s = getState();
        if (!s.logW) return;
        patternLayer.position({ x: s.logW / 2 + s.panX, y: s.logH / 2 + s.panY });
        patternLayer.scale({ x: s.scaleFactor, y: s.scaleFactor });
        patternLayer.draw(); // 동기
        konvaShapeLayer.position({ x: s.logW / 2 + s.panX, y: s.logH / 2 + s.panY });
        konvaShapeLayer.scale({ x: s.scaleFactor, y: s.scaleFactor });
        konvaShapeLayer.batchDraw();
    }

    function updatePatternHighlight() {
        if (!_usePatternLayer) return;
        for (const [, rect] of Object.entries(_patternSlatNodes)) {
            const lineKey  = rect.getAttr('_pmok_lineKey');
            const baseFill = rect.getAttr('_pmok_baseFill');
            const oc       = slatColorOverrides[lineKey];
            _applySlatColor(rect, lineKey === _selectedLineKey ? 'rgba(41,121,255,0.55)' : (oc || baseFill));
        }
        patternLayer.batchDraw();
    }

    function getKonvaParams() {
        return konvaShapeLayer
            .getChildren(n => n !== konvaTransformer)
            .map(s => {
                const cls   = s.getClassName();
                const attrs = s.getAttrs();
                if (cls === 'Image') {
                    // Image 객체는 직렬화 불가 — URL로 대체
                    const { image: _img, ...rest } = attrs;
                    return { className: cls, attrs: rest };
                }
                return { className: cls, attrs };
            });
    }

    function getSlatColorOverrides() {
        return { ...slatColorOverrides };
    }

    function applyKonvaParams(p) {
        konvaShapeLayer.getChildren(n => n !== konvaTransformer).forEach(n => n.destroy());
        konvaTransformer.nodes([]);
        (p.konvaShapes || []).forEach(({ className, attrs }) => {
            if (className === 'Image') {
                const url = attrs._ins_url;
                if (!url) return;
                const img = new window.Image();
                img.crossOrigin = 'anonymous';
                img.onload = () => {
                    const node = new Konva.Image({ ...attrs, image: img });
                    node.on('dblclick dbltap', () => {});
                    addShape(node);
                    konvaShapeLayer.batchDraw();
                };
                img.src = url;
            } else {
                const Cls = Konva[className];
                if (!Cls) return;
                const node = new Cls(attrs);
                if (className === 'Text') node.on('dblclick dbltap', () => editText(node));
                if (className === 'Circle') {
                    node.hitFunc((context, shape) => {
                        context.beginPath();
                        context.arc(0, 0, shape.radius(), 0, Math.PI * 2, false);
                        context.closePath();
                        context.fillStrokeShape(shape);
                    });
                } else if (className === 'Rect') {
                    node.hitFunc((context, shape) => {
                        context.beginPath();
                        context.rect(0, 0, shape.width(), shape.height());
                        context.closePath();
                        context.fillStrokeShape(shape);
                    });
                }
                addShape(node);
            }
        });
        konvaShapeLayer.batchDraw();
        slatColorOverrides = p.slatColorOverrides ? { ...p.slatColorOverrides } : {};
    }

    return {
        syncSize,
        handleSlatSelect,
        drawSlatOverlay,
        onDeactivate,
        addKonvaInsert,
        getKonvaParams,
        getSlatColorOverrides,
        applyKonvaParams,
        get slatSelectMode()  { return _slatSelectMode; },
        get usePatternLayer() { return _usePatternLayer; },
        beginPattern,
        clearPattern,
        addPatternBg,
        addPatternClipGroup,
        endPatternClipGroup,
        addPatternSlatRect,
        addPatternSlatLine,
        addPatternRectToGroup,
        addPatternFrameRect,
        addPatternLine,
        addPatternPolygon,
        getPatternGeometry,
        setEditMarker,
        clearEditMarker,
        commitPattern,
        syncPatternTransform,
        updatePatternHighlight,
        getStageCanvas: () => konvaStage.toCanvas(),
    };
};
