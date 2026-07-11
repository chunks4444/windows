/**
 * Konva 오버레이 모듈 — 도형·SVG 삽입·살 선택
 *
 * getState() → { logW, logH, panX, panY, scaleFactor, lastSlatPx, lastCellSize }
 * getSegMap() → Map  /  deletedSegs → Set  /  draw() → void
 *
 * 좌표계: 모든 Konva 노드는 canvas "logic 좌표" (translated+scaled 공간) 사용.
 * drawSlatOverlay() 호출 시마다 konvaShapeLayer 변환을 canvas와 동기화.
 */
window.initKonvaOverlay = function ({ canvas, getState, getSegMap, deletedSegs, draw }) {

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
        patternLayer.batchDraw();
        konvaShapeLayer.batchDraw();
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
            if (!konvaLineStart) {
                konvaLineStart = lpos;
                konvaLinePreview = new Konva.Line({
                    points: [lpos.x, lpos.y, lpos.x, lpos.y],
                    stroke: '#e03030', strokeWidth: 2 / getState().scaleFactor,
                    dash: [6 / getState().scaleFactor, 3 / getState().scaleFactor],
                    listening: false, strokeScaleEnabled: false,
                });
                konvaShapeLayer.add(konvaLinePreview);
                konvaShapeLayer.batchDraw();
            } else {
                const line = new Konva.Line({
                    points: [konvaLineStart.x, konvaLineStart.y, lpos.x, lpos.y],
                    stroke: '#e03030', strokeWidth: 2 / getState().scaleFactor,
                    lineCap: 'round', strokeScaleEnabled: false,
                    hitStrokeWidth: 24 / getState().scaleFactor, // 선이 얇아 터치로 선택하기 어려운 것 보완
                });
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
        konvaLinePreview.points([konvaLineStart.x, konvaLineStart.y, lpos.x, lpos.y]);
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
    el('btnDeleteSelectedSlat')?.addEventListener('click', () => {
        if (!_selectedLineKey || !deletedSegs) return;
        const segMap = getSegMap();
        for (const [key, seg] of segMap) {
            if (seg.lineKey === _selectedLineKey) deletedSegs.add(key);
        }
        _selectedLineKey = null;
        updatePanels();
        draw();
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

    // 프리젠테이션 모드 썸네일용 — patternLayer에 쌓인 살/울거미 지오메트리만 읽어서 반환.
    // 사용자가 칠한 면색(addPatternRectToGroup)·면칠 다각형(addPatternPolygon)·배경(addPatternBg)은
    // _pmok_type 태그가 없거나 폐곡선(closed)이라 여기서 자동 제외된다.
    function getPatternGeometry() {
        const frameRects = [];
        const slatRects  = [];
        const slatLines  = [];
        patternLayer.find('Rect').forEach(node => {
            const type = node.getAttr('_pmok_type');
            if (type !== 'frame' && type !== 'slat') return;
            const box = node.getClientRect({ relativeTo: patternLayer });
            (type === 'frame' ? frameRects : slatRects).push(box);
        });
        patternLayer.find('Line').forEach(node => {
            if (node.closed() || !node.stroke()) return;
            const t  = node.getAbsoluteTransform(patternLayer);
            const pt = node.points();
            const p1 = t.point({ x: pt[0], y: pt[1] });
            const p2 = t.point({ x: pt[2], y: pt[3] });
            slatLines.push({ x1: p1.x, y1: p1.y, x2: p2.x, y2: p2.y, strokeWidth: node.strokeWidth() });
        });
        return { frameRects, slatRects, slatLines };
    }

    function addPatternPolygon(points, fill) {
        (_activeClipGroup || patternLayer).add(new Konva.Line({ points, fill, closed: true, strokeWidth: 0, listening: false, perfectDrawEnabled: false }));
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
            rect.fill(lineKey === _selectedLineKey ? 'rgba(41,121,255,0.55)' : (oc || baseFill));
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
            rect.fill(lineKey === _selectedLineKey ? 'rgba(41,121,255,0.55)' : (oc || baseFill));
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
