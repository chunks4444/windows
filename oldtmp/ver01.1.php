<?php
// 전통 창호 사분턱 도면 설계기
// 모든 계산은 클라이언트(JS)에서 처리됩니다.
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>창호 설계 — 사분턱 V0.3</title>
        <link
            href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap"
            rel="stylesheet">
        <style>
            :root {
                --teal: #3A8C82;
                --teal-mid: #5AADA2;
                --teal-pale: #E6F4F2;
                --accent: #3A8C82;
                --accent-bg: #EAF5F3;
                --blue: #2E7D6E;
                --blue-bg: #E6F4F2;
                --page-bg: #F2F3F4;
                --sidebar-bg: #FFFFFF;
                --canvas-bg: #E5E7EA;
                --text-1: #1A1F1E;
                --text-2: #5A6B69;
                --text-3: #97A8A6;
                --border: #E0E5E4;
                --border-md: #C8D4D2;
                --input-bg: #F4F6F6;
                --sidebar-w: 272px;
                --hdr-h: 52px;
                --r: 8px;
                --r-sm: 6px;
                --r-xs: 4px;
            }

            *,
            *::after,
            *::before {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            body {
                font-family: 'Noto Sans KR', -apple-system, 'Malgun Gothic', sans-serif;
                background: var(--page-bg);
                height: 100vh;
                display: flex;
                flex-direction: column;
                overflow: hidden;
                color: var(--text-1);
                font-size: 13px;
                -webkit-font-smoothing: antialiased;
            }

            /* ── HEADER ─────────────────────────────── */
            header {
                height: var(--hdr-h);
                background: var(--sidebar-bg);
                border-bottom: 1px solid var(--border);
                display: flex;
                align-items: center;
                padding: 0 16px;
                gap: 0;
                flex-shrink: 0;
                z-index: 30;
            }

            .hdr-brand {
                display: flex;
                align-items: center;
                gap: 10px;
                text-decoration: none;
                color: inherit;
            }

            .brand-mark {
                width: 30px;
                height: 30px;
                background: var(--teal);
                border-radius: var(--r-sm);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 16px;
                flex-shrink: 0;
            }

            .brand-text {
                line-height: 1.15;
            }

            .brand-title {
                font-size: 14px;
                font-weight: 700;
                color: var(--text-1);
                letter-spacing: -0.3px;
            }

            .brand-sub {
                font-size: 10px;
                color: var(--text-3);
                font-weight: 400;
            }

            .hdr-sep {
                width: 1px;
                height: 24px;
                background: var(--border);
                margin: 0 14px;
            }

            .hdr-badge {
                display: flex;
                align-items: center;
                gap: 6px;
                background: var(--accent-bg);
                border: 1px solid rgba(58, 140, 130, 0.25);
                border-radius: 20px;
                padding: 3px 10px 3px 6px;
                font-size: 11px;
                font-weight: 500;
                color: var(--teal);
            }

            .badge-dot {
                width: 6px;
                height: 6px;
                background: var(--teal);
                border-radius: 50%;
            }

            .hdr-actions {
                margin-left: auto;
                display: flex;
                align-items: center;
                gap: 6px;
            }

            .hbtn {
                height: 32px;
                padding: 0 12px;
                border-radius: var(--r-sm);
                border: 1px solid var(--border-md);
                background: transparent;
                color: var(--text-2);
                font-size: 12px;
                font-weight: 500;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 6px;
                transition: background 0.12s, border-color 0.12s, color 0.12s;
                font-family: inherit;
                letter-spacing: -0.2px;
            }

            .hbtn:hover {
                background: var(--input-bg);
                border-color: var(--border-md);
                color: var(--text-1);
            }

            .hbtn-primary {
                background: var(--teal);
                border-color: var(--teal);
                color: #fff;
            }

            .hbtn-primary:hover {
                background: #2F7169;
                border-color: #2F7169;
                color: #fff;
            }

            .hbtn svg {
                flex-shrink: 0;
            }

            /* 준비중 버튼 */
            .hbtn-dark {
                position: relative;
                overflow: visible;
            }

            /* 준비중 툴팁 */
            .hbtn-dark::after {
                content: '준비중';
                position: absolute;

                left: 50%;
                top: calc(100% + 10px);

                transform: translateX(-50%) translateY(-2px);

                background: #111;
                color: #fff;

                font-size: 10px;
                font-weight: 600;

                padding: 6px 10px;

                border-radius: 8px;

                white-space: nowrap;

                opacity: 0;
                pointer-events: none;

                transition: opacity 0.18s ease, transform 0.18s ease;

                box-shadow: 0 6px 18px rgba(0,0,0,.18);

                z-index: 999;
            }

            /* 화살표 */
            .hbtn-dark::before {
                content: '';

                position: absolute;

                left: 50%;
                top: calc(100% + 4px);

                transform: translateX(-50%) translateY(-2px);

                border-left: 5px solid transparent;
                border-right: 5px solid transparent;
                border-bottom: 6px solid #111;

                opacity: 0;
                pointer-events: none;

                transition: opacity 0.18s ease, transform 0.18s ease;

                z-index: 998;
            }

            /* hover */
            .hbtn-dark:hover::after,
            .hbtn-dark:hover::before {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }

            /* ── LAYOUT ─────────────────────────────── */
            .main {
                display: flex;
                flex: 1;
                overflow: hidden;
                min-height: 0;
            }

            /* ── SIDEBAR ────────────────────────────── */
            .controls {
                width: var(--sidebar-w);
                background: var(--sidebar-bg);
                border-right: 1px solid var(--border);
                overflow-y: auto;
                flex-shrink: 0;
                overflow-x: hidden;
                transition: width 0.22s cubic-bezier(.4,0,.2,1), opacity 0.22s ease, border-color 0.22s ease;
                scrollbar-width: thin;
                scrollbar-color: var(--border) transparent;
            }

            .controls.collapsed {
                width: 0;
                opacity: 0;
                border-color: transparent;
                overflow: hidden;
            }

            .controls::-webkit-scrollbar {
                width: 3px;
            }
            .controls::-webkit-scrollbar-thumb {
                background: var(--border);
                border-radius: 2px;
            }

            .sb-inner {
                min-width: var(--sidebar-w);
            }

            /* ── SIDEBAR SECTIONS ────────────────────── */
            .sb-section {
                padding: 16px;
                border-bottom: 1px solid var(--border);
            }

            .sb-section:last-child {
                border-bottom: none;
            }

            .sb-section-title {
                font-size: 9.5px;
                font-weight: 700;
                letter-spacing: 0.9px;
                text-transform: uppercase;
                color: var(--text-3);
                margin-bottom: 14px;
            }

            /* ── CONTROL ROWS ────────────────────────── */
            .ctrl {
                margin-bottom: 14px;
            }

            .ctrl:last-child {
                margin-bottom: 0;
            }

            .ctrl-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 7px;
            }

            .ctrl-label {
                font-size: 12px;
                font-weight: 500;
                color: var(--text-2);
            }

            .ctrl-val {
                font-size: 11px;
                font-weight: 700;
                color: var(--accent);
                background: var(--accent-bg);
                padding: 1px 8px;
                border-radius: 20px;
                letter-spacing: -0.2px;
            }

            /* ── RANGE SLIDER ────────────────────────── */
            input[type="range"] {
                -webkit-appearance: none;
                appearance: none;
                width: 100%;
                height: 4px;
                background: var(--border-md);
                border-radius: 2px;
                outline: none;
                cursor: pointer;
                display: block;
            }

            input[type="range"]::-webkit-slider-thumb {
                -webkit-appearance: none;
                appearance: none;
                width: 16px;
                height: 16px;
                border-radius: 50%;
                background: #fff;
                border: 2.5px solid var(--teal);
                cursor: pointer;
                transition: transform 0.1s ease, border-color 0.1s ease;
            }

            input[type="range"]::-webkit-slider-thumb:hover {
                transform: scale(1.2);
                border-color: var(--teal-mid);
            }

            input[type="range"]::-moz-range-thumb {
                width: 16px;
                height: 16px;
                border-radius: 50%;
                background: #fff;
                border: 2.5px solid var(--teal);
                cursor: pointer;
                box-shadow: none;
            }

            /* ── SPEC CARDS ────────────────────────── */
            .spec-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 6px;
            }

            .spec-card {
                background: var(--input-bg);
                border: 1px solid var(--border);
                border-radius: var(--r-sm);
                padding: 9px 10px;
            }

            .spec-card.accent {
                background: var(--accent-bg);
                border-color: rgba(58, 140, 130, 0.28);
            }

            .spec-card.accent-blue {
                background: #DFF0EE;
                border-color: rgba(46, 125, 110, 0.28);
            }

            .spec-card.full {
                grid-column: 1 / -1;
            }

            .spec-lbl {
                font-size: 10px;
                color: var(--text-3);
                font-weight: 500;
                margin-bottom: 4px;
                line-height: 1.2;
            }

            .spec-val {
                font-size: 16px;
                font-weight: 700;
                color: var(--text-1);
                letter-spacing: -0.5px;
                line-height: 1;
            }

            .spec-card.accent .spec-val {
                color: var(--teal);
            }
            .spec-card.accent-blue .spec-val {
                color: #236B62;
            }

            .spec-unit {
                font-size: 10px;
                font-weight: 400;
                color: var(--text-3);
                margin-left: 1px;
            }

            /* ── CANVAS ────────────────────────────── */
            .canvas-area {
                flex: 1;
                background: var(--canvas-bg);
                position: relative;
                overflow: hidden;
                background-image: radial-gradient(circle, rgba(255,255,255,0.05) 1px, transparent 1px);
                background-size: 28px 28px;
            }

            canvas {
                display: block;
                width: 100%;
                height: 100%;
                cursor: grab;
            }

            canvas:active {
                cursor: grabbing;
            }

            /* ── ZOOM HINT ───────────────────────────── */
            .zoom-hint {
                position: absolute;
                bottom: 18px;
                right: 18px;
                background: rgba(255,255,255,0.07);
                border: 1px solid rgba(255,255,255,0.1);
                color: rgba(255,255,255,0.4);
                padding: 5px 13px;
                border-radius: 20px;
                font-size: 11px;
                pointer-events: none;
                letter-spacing: 0.1px;
            }

            /* ── SIDEBAR FOOTER ───────────────────────── */
            .sb-footer {
                padding: 12px 16px;
                border-top: 1px solid var(--border);
                background: var(--input-bg);
                font-size: 10.5px;
                color: var(--text-3);
                line-height: 1.5;
            }

            .sb-footer strong {
                color: var(--teal);
                font-weight: 600;
            }

            /* 셀렉트박스 */
            .sb-select {
                width: 100%;
                height: 36px;

                border: 1px solid var(--border);
                border-radius: var(--r-sm);

                background: var(--input-bg);

                padding: 0 12px;

                font-size: 12px;
                font-family: inherit;

                color: var(--text-1);

                outline: none;

                transition: border-color 0.15s ease, background 0.15s ease;
            }

            .sb-select:hover {
                border-color: var(--border-md);
            }

            .sb-select:focus {
                border-color: var(--teal);
                background: #fff;
            }
        </style>
    </head>
    <body>

        <!-- HEADER -->
        <header>
            <div class="hdr-brand">
                <div class="brand-mark">🪵</div>
                <div class="brand-text">
                    <div class="brand-title">워크그룹 평목</div>
                    <div class="brand-sub">사분턱 디자인 생성기</div>
                </div>
            </div>

            <div class="hdr-sep"></div>

            <div class="hdr-badge">
                <div class="badge-dot"></div>
                사분턱 · 0.2 Ver
            </div>

            <div class="hdr-actions">

                <!-- 메뉴 -->
                <button class="hbtn hbtn-ghost" id="btnToggleSidebar">
                    <svg width="14" height="14" viewbox="0 0 14 14" fill="none">
                        <rect y="1.5" width="14" height="1.5" rx="0.75" fill="currentColor"/>
                        <rect y="6.25" width="14" height="1.5" rx="0.75" fill="currentColor"/>
                        <rect y="11" width="14" height="1.5" rx="0.75" fill="currentColor"/>
                    </svg>
                    <span>치수창</span>
                </button>

                <!-- 초기화 -->
                <button class="hbtn hbtn-primary" id="btnResetZoom">
                    <svg width="14" height="14" viewbox="0 0 24 24" fill="none">
                        <path
                            d="M4 4V10H10"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"/>
                        <path
                            d="M20 20V14H14"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"/>
                        <path
                            d="M20 9A8 8 0 0 0 6.34 5.34L4 8"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"/>
                        <path
                            d="M4 15A8 8 0 0 0 17.66 18.66L20 16"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"/>
                    </svg>
                    <span>초기화</span>
                </button>

                <!-- 저장 -->
                <button class="hbtn hbtn-dark">
                    <svg width="14" height="14" viewbox="0 0 24 24" fill="none">
                        <path d="M5 4H17L20 7V20H5V4Z" stroke="currentColor" stroke-width="2"/>
                        <path d="M8 4V10H16V4" stroke="currentColor" stroke-width="2"/>
                        <path
                            d="M9 15H15"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"/>
                    </svg>
                    <span>저장</span>
                </button>

                <!-- 주문 -->
                <button class="hbtn hbtn-dark">

                    <!-- 오더(주문) 아이콘 -->
                    <svg width="14" height="14" viewbox="0 0 24 24" fill="none">

                        <!-- 종이 -->
                        <path
                            d="M7 3H14L19 8V20H7V3Z"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linejoin="round"/>

                        <!-- 접힌 모서리 -->
                        <path
                            d="M14 3V8H19"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linejoin="round"/>

                        <!-- 체크 -->
                        <path
                            d="M10 12L11.8 13.8L15 10.5"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"/>

                        <!-- 주문 라인 -->
                        <path
                            d="M10 17H16"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"/>

                    </svg>

                    <span>주문</span>

                </button>

                <!-- 마이페이지 -->
                <button class="hbtn hbtn-dark">
                    <svg width="14" height="14" viewbox="0 0 24 24" fill="none">
                        <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="2"/>
                        <path
                            d="M4 20C4 16.5 7 14 12 14C17 14 20 16.5 20 20"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"/>
                    </svg>
                    <span>마이페이지</span>

                </button>

            </div>
        </header>

        <!-- MAIN -->
        <div class="main">

            <!-- SIDEBAR -->
            <div class="controls" id="sidebar">
                <div class="sb-inner">

                    <!-- 문 치수 -->
                    <div class="sb-section">
                        <div class="sb-section-title">문 치수</div>

                        <div class="ctrl">
                            <div class="ctrl-header">
                                <span class="ctrl-label">가로폭</span>
                                <span class="ctrl-val"><span id="lblW">900</span>
                                    mm</span>
                            </div>
                            <input type="range" id="txtW" min="400" max="1500" step="1" value="900">
                        </div>

                        <div class="ctrl">
                            <div class="ctrl-header">
                                <span class="ctrl-label">세로높이</span>
                                <span class="ctrl-val"><span id="lblH">1800</span>
                                    mm</span>
                            </div>
                            <input type="range" id="txtH" min="800" max="2400" step="1" value="1800">
                        </div>
                    </div>

                    <!-- 창살 설정 -->
                    <div class="sb-section">
                        <div class="sb-section-title">창살 설정</div>

                        <div class="ctrl">
                            <div class="ctrl-header">
                                <span class="ctrl-label">가로 칸수</span>
                                <span class="ctrl-val"><span id="lblCols">8</span>
                                    칸</span>
                            </div>
                            <input type="range" id="txtCols" min="2" max="30" step="1" value="4">
                        </div>

                        <div class="ctrl">
                            <div class="ctrl-header">
                                <span class="ctrl-label">울거미 두께</span>
                                <span class="ctrl-val"><span id="lblFrame">45</span>
                                    mm</span>
                            </div>
                            <input type="range" id="txtFrame" min="35" max="100" step="5" value="45">
                        </div>

                        <div class="ctrl">
                            <div class="ctrl-header">
                                <span class="ctrl-label">창살 두께</span>
                                <span class="ctrl-val"><span id="lblSlat">12</span>
                                    mm</span>
                            </div>
                            <input type="range" id="txtSlat" min="8" max="25" step="1" value="12">
                        </div>
                    </div>

                    <!-- 제작 시방서 -->
                    <div class="sb-section">
                        <div class="sb-section-title">제작 시방서</div>
                        <div class="spec-grid">
                            <div class="spec-card">
                                <div class="spec-lbl">외경 가로</div>
                                <div class="spec-val">
                                    <span id="spOuterW">0</span><span class="spec-unit">mm</span></div>
                            </div>
                            <div class="spec-card">
                                <div class="spec-lbl">외경 세로</div>
                                <div class="spec-val">
                                    <span id="spOuterH">0</span><span class="spec-unit">mm</span></div>
                            </div>
                            <div class="spec-card">
                                <div class="spec-lbl">내경 가로</div>
                                <div class="spec-val">
                                    <span id="spInnerW">0</span><span class="spec-unit">mm</span></div>
                            </div>
                            <div class="spec-card">
                                <div class="spec-lbl">내경 세로</div>
                                <div class="spec-val">
                                    <span id="spInnerH">0</span><span class="spec-unit">mm</span></div>
                            </div>
                            <div class="spec-card">
                                <div class="spec-lbl">가로 칸수</div>
                                <div class="spec-val">
                                    <span id="spCounts">0</span><span class="spec-unit">칸</span></div>
                            </div>
                            <div class="spec-card">
                                <div class="spec-lbl">세로 칸수</div>
                                <div class="spec-val">
                                    <span id="spRows">0</span><span class="spec-unit">칸</span></div>
                            </div>
                            <div class="spec-card full">
                                <div class="spec-lbl">중심선 간격 (먹줄)</div>
                                <div class="spec-val">
                                    <span id="spStep">0</span><span class="spec-unit">mm</span></div>
                            </div>
                            <div class="spec-card accent full">
                                <div class="spec-lbl">정자살 순간격 (살간격) — 전 칸 동일</div>
                                <div class="spec-val">
                                    <span id="spEye">0</span><span class="spec-unit">mm</span></div>
                            </div>
                            <div class="spec-card accent-blue full">
                                <div class="spec-lbl">사선 순치수 (살간격) — 전 칸 동일</div>
                                <div class="spec-val">
                                    <span id="spDiagEye">0</span><span class="spec-unit">mm</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- 마감 옵션 -->
                    <div class="sb-section">
                        <div class="sb-section-title">마감 옵션</div>
                        <div class="ctrl">
                            <div class="ctrl-header">
                                <span class="ctrl-label">내부 마감</span>
                            </div>
                            <select id="txtFinish" class="sb-select">
                                <option value="changhoji">창호지</option>
                                <option value="glass">유리</option>
                                <option value="acrylic">아크릴</option>
                            </select>

                        </div>
                    </div>
                    <!-- 안내 -->
                    <div class="sb-footer">
                        <strong>균등 분할.</strong><br>
                        모든 칸의 순간격(살간격)이 동일하게 계산됩니다. 올거미 촉도면은 상세도면으로 추가 예정입니다.
                    </div>

                </div>
            </div>

            <!-- CANVAS -->
            <div class="canvas-area" id="canvasContainer">
                <div class="zoom-hint">휠: 확대/축소 &nbsp;·&nbsp; 드래그: 이동</div>
                <canvas id="doorCanvas"></canvas>
            </div>

        </div>

        <script>
            const canvas = document.getElementById('doorCanvas');
            const ctx = canvas.getContext('2d');
            const container = document.getElementById('canvasContainer');
            const sidebar = document.getElementById('sidebar');

            const txtW = document.getElementById('txtW');
            const txtCols = document.getElementById('txtCols');
            const txtH = document.getElementById('txtH');
            const txtFrame = document.getElementById('txtFrame');
            const txtSlat = document.getElementById('txtSlat');
            const btnToggleSidebar = document.getElementById('btnToggleSidebar');
            const btnResetZoom = document.getElementById('btnResetZoom');

            let geo = {};
            let scaleFactor = 1.0;
            let panX = 0;
            let panY = 0;
            let isDragging = false;
            let startX,
                startY;

            function calculateGeometry() {
                const reqW = parseInt(txtW.value);
                const cols = parseInt(txtCols.value);
                const reqH = parseInt(txtH.value);
                const frameT = parseInt(txtFrame.value);
                const slatT = parseInt(txtSlat.value);

                const innerW_target = reqW - (2 * frameT);
                const cellSize = (innerW_target - (slatT * (cols - 1))) / cols;
                const step = cellSize + slatT;
                const innerW = (cols * cellSize) + (slatT * (cols - 1));
                const outerW = innerW + (2 * frameT);

                const innerH_target = reqH - (2 * frameT);
                const rows = Math.max(1, Math.round((innerH_target + slatT) / step));
                const innerH = (rows * cellSize) + (slatT * (rows - 1));
                const outerH = innerH + (2 * frameT);

                const diagEye = ((step * Math.sqrt(2)) - (slatT * 2)).toFixed(1);
                const tenonDepth = Math.round(slatT * 0.8);

                geo = {
                    cellSize,
                    outerW,
                    outerH,
                    frameT,
                    slatT,
                    step,
                    cols,
                    rows,
                    innerW,
                    innerH,
                    diagEye,
                    tenonDepth
                };

                document
                    .getElementById('lblW')
                    .innerText = reqW;
                document
                    .getElementById('lblCols')
                    .innerText = cols;
                document
                    .getElementById('lblH')
                    .innerText = reqH;
                document
                    .getElementById('lblFrame')
                    .innerText = frameT;
                document
                    .getElementById('lblSlat')
                    .innerText = slatT;

                document
                    .getElementById('spOuterW')
                    .innerText = Math.round(outerW);
                document
                    .getElementById('spOuterH')
                    .innerText = Math.round(outerH);
                document
                    .getElementById('spInnerW')
                    .innerText = Math.round(innerW);
                document
                    .getElementById('spInnerH')
                    .innerText = Math.round(innerH);
                document
                    .getElementById('spCounts')
                    .innerText = cols;
                document
                    .getElementById('spRows')
                    .innerText = rows;
                document
                    .getElementById('spStep')
                    .innerText = step.toFixed(1);
                document
                    .getElementById('spEye')
                    .innerText = cellSize.toFixed(1);
                document
                    .getElementById('spDiagEye')
                    .innerText = diagEye;
            }

            function resizeCanvas() {
                canvas.width = container.clientWidth;
                canvas.height = container.clientHeight;
                draw();
            }

            function draw() {
                calculateGeometry();
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                ctx.save();
                ctx.translate(canvas.width / 2 + panX, canvas.height / 2 + panY);
                ctx.scale(scaleFactor, scaleFactor);

                const basePadding = 60;
                const baseScale = Math.min(
                    (canvas.width - basePadding * 2) / geo.outerW,
                    (canvas.height - basePadding * 2) / geo.outerH
                );

                const offsetX = -(geo.outerW * baseScale) / 2;
                const offsetY = -(geo.outerH * baseScale) / 2;

                const toCanvasX = (realX) => offsetX + realX * baseScale;
                const toCanvasY = (realY) => offsetY + realY * baseScale;

                const Color_Slat_Fill = 'rgba(0,0,0,0.9)';;
                const Color_Tenon_Fill = '#ba9b82';
                const Color_Slat_Stroke = '#ba9b82';

                ctx.lineWidth = 0.5;

                // 1. 세로살
                for (let i = 1; i < geo.cols; i++) {
                    let cx = geo.frameT + (i * geo.cellSize) + ((i - 0.5) * geo.slatT);
                    let left = cx - geo.slatT / 2;
                    let topY = geo.frameT;
                    let bottomY = geo.frameT + geo.innerH;
                    ctx.fillStyle = Color_Tenon_Fill;
                    ctx.strokeStyle = '#555555';
                    ctx.fillRect(
                        toCanvasX(left),
                        toCanvasY(topY - geo.tenonDepth),
                        geo.slatT * baseScale,
                        geo.tenonDepth * baseScale
                    );
                    ctx.strokeRect(
                        toCanvasX(left),
                        toCanvasY(topY - geo.tenonDepth),
                        geo.slatT * baseScale,
                        geo.tenonDepth * baseScale
                    );
                    ctx.fillRect(
                        toCanvasX(left),
                        toCanvasY(bottomY),
                        geo.slatT * baseScale,
                        geo.tenonDepth * baseScale
                    );
                    ctx.strokeRect(
                        toCanvasX(left),
                        toCanvasY(bottomY),
                        geo.slatT * baseScale,
                        geo.tenonDepth * baseScale
                    );
                    ctx.fillStyle = Color_Slat_Fill;
                    ctx.strokeStyle = Color_Slat_Stroke;
                    ctx.fillRect(
                        toCanvasX(left),
                        toCanvasY(topY),
                        geo.slatT * baseScale,
                        geo.innerH * baseScale
                    );
                    ctx.strokeRect(
                        toCanvasX(left),
                        toCanvasY(topY),
                        geo.slatT * baseScale,
                        geo.innerH * baseScale
                    );
                    drawCenterLine(
                        toCanvasX(cx),
                        toCanvasY(topY - geo.tenonDepth),
                        toCanvasX(cx),
                        toCanvasY(
                            bottomY + geo.tenonDepth
                        )
                    );
                }

                // 2. 가로살
                for (let j = 1; j < geo.rows; j++) {
                    let ry = geo.frameT + (j * geo.cellSize) + ((j - 0.5) * geo.slatT);
                    let top = ry - geo.slatT / 2;
                    let bottom = ry + geo.slatT / 2;
                    let leftX = geo.frameT;
                    let rightX = geo.frameT + geo.innerW;
                    ctx.fillStyle = Color_Tenon_Fill;
                    ctx.strokeStyle = '#555555';
                    ctx.fillRect(
                        toCanvasX(leftX - geo.tenonDepth),
                        toCanvasY(top),
                        geo.tenonDepth * baseScale,
                        geo.slatT * baseScale
                    );
                    ctx.strokeRect(
                        toCanvasX(leftX - geo.tenonDepth),
                        toCanvasY(top),
                        geo.tenonDepth * baseScale,
                        geo.slatT * baseScale
                    );
                    ctx.fillRect(
                        toCanvasX(rightX),
                        toCanvasY(top),
                        geo.tenonDepth * baseScale,
                        geo.slatT * baseScale
                    );
                    ctx.strokeRect(
                        toCanvasX(rightX),
                        toCanvasY(top),
                        geo.tenonDepth * baseScale,
                        geo.slatT * baseScale
                    );
                    ctx.fillStyle = Color_Slat_Fill;
                    ctx.strokeStyle = Color_Slat_Stroke;
                    ctx.fillRect(
                        toCanvasX(leftX),
                        toCanvasY(top),
                        geo.innerW * baseScale,
                        geo.slatT * baseScale
                    );
                    ctx.strokeRect(
                        toCanvasX(leftX),
                        toCanvasY(top),
                        geo.innerW * baseScale,
                        geo.slatT * baseScale
                    );
                    drawCenterLine(toCanvasX(leftX - geo.tenonDepth), toCanvasY(ry), toCanvasX(
                        rightX + geo.tenonDepth
                    ), toCanvasY(ry));
                }

                // 3. 사선살 + 촉(장부) 생성

                const maxShift = geo.cols + geo.rows;
                const originX = geo.frameT + geo.cellSize + 0.5 * geo.slatT;
                const originY = geo.frameT + geo.cellSize + 0.5 * geo.slatT;

                const shoulderDepth = geo.frameT * 0.5;
                const tenonLength = shoulderDepth * Math.sqrt(2);

                function drawDiagonalWithTenon(body_pts) {

                    if (!body_pts) 
                        return;
                    
                    // 방향벡터
                    let dx = body_pts.x2 - body_pts.x1;
                    let dy = body_pts.y2 - body_pts.y1;

                    let len = Math.hypot(dx, dy);

                    let ux = dx / len;
                    let uy = dy / len;

                    // 수직벡터
                    let px = -uy;
                    let py = ux;

                    // 몸체 폭
                    let bodyHalf = geo.slatT / 2;

                    // 촉 끝 폭
                    let tenonW = geo.frameT - geo.slatT;
                    let tenonHalf = tenonW / 2;

                    // 촉 길이
                    let tenonLen = geo.frameT * 0.65;

                    // 몸체 시작/끝
                    let sx = body_pts.x1;
                    let sy = body_pts.y1;

                    let ex = body_pts.x2;
                    let ey = body_pts.y2;

                    // 촉 끝 연장점
                    let tsx = sx - ux * tenonLen;
                    let tsy = sy - uy * tenonLen;

                    let tex = ex + ux * tenonLen;
                    let tey = ey + uy * tenonLen;

                    ctx.beginPath();

                    // 시작 촉
                    ctx.moveTo(toCanvasX(tsx + px * tenonHalf), toCanvasY(tsy + py * tenonHalf));

                    ctx.lineTo(toCanvasX(sx + px * bodyHalf), toCanvasY(sy + py * bodyHalf));

                    // 몸체 상단
                    ctx.lineTo(toCanvasX(ex + px * bodyHalf), toCanvasY(ey + py * bodyHalf));

                    // 끝 촉
                    ctx.lineTo(toCanvasX(tex + px * tenonHalf), toCanvasY(tey + py * tenonHalf));

                    ctx.lineTo(toCanvasX(tex - px * tenonHalf), toCanvasY(tey - py * tenonHalf));

                    // 몸체 하단
                    ctx.lineTo(toCanvasX(ex - px * bodyHalf), toCanvasY(ey - py * bodyHalf));

                    ctx.lineTo(toCanvasX(sx - px * bodyHalf), toCanvasY(sy - py * bodyHalf));

                    ctx.closePath();

                    ctx.fillStyle = Color_Slat_Fill;
                    ctx.strokeStyle = Color_Slat_Stroke;
                    ctx.lineWidth = 0.5;

                    ctx.fill();
                    ctx.stroke();

                    // 중심선
                    drawCenterLine(toCanvasX(tsx), toCanvasY(tsy), toCanvasX(tex), toCanvasY(tey));

                    // V 절삭선

                    let vDepth = geo.slatT * 0.8;

                    // 시작 V
                    ctx.beginPath();

                    ctx.moveTo(toCanvasX(tsx + px * tenonHalf), toCanvasY(tsy + py * tenonHalf));

                    ctx.lineTo(toCanvasX(tsx - ux * vDepth), toCanvasY(tsy - uy * vDepth));

                    ctx.lineTo(toCanvasX(tsx - px * tenonHalf), toCanvasY(tsy - py * tenonHalf));

                    ctx.strokeStyle = '#7a3b2e';
                    ctx.stroke();

                    // 끝 V
                    ctx.beginPath();

                    ctx.moveTo(toCanvasX(tex + px * tenonHalf), toCanvasY(tey + py * tenonHalf));

                    ctx.lineTo(toCanvasX(tex + ux * vDepth), toCanvasY(tey + uy * vDepth));

                    ctx.lineTo(toCanvasX(tex - px * tenonHalf), toCanvasY(tey - py * tenonHalf));

                    ctx.stroke();
                }

                for (let k = -maxShift; k <= maxShift; k++) {

                    // ↘ 방향

                    let x1_raw = geo.frameT;
                    let y1_raw = originY + k * geo.step - (originX - geo.frameT);

                    let x2_raw = geo.frameT + geo.innerW;
                    let y2_raw = y1_raw + geo.innerW;

                    let body_pts1 = clipLineToRect(
                        x1_raw,
                        y1_raw,
                        x2_raw,
                        y2_raw,
                        geo.frameT,
                        geo.frameT,
                        geo.frameT + geo.innerW,
                        geo.frameT + geo.innerH
                    );

                    drawDiagonalWithTenon(body_pts1);

                    // ↗ 방향

                    let y1_raw2 = originY + k * geo.step + (originX - geo.frameT);
                    let y2_raw2 = y1_raw2 - geo.innerW;

                    let body_pts2 = clipLineToRect(
                        x1_raw,
                        y1_raw2,
                        x2_raw,
                        y2_raw2,
                        geo.frameT,
                        geo.frameT,
                        geo.frameT + geo.innerW,
                        geo.frameT + geo.innerH
                    );

                    drawDiagonalWithTenon(body_pts2);
                }

                // 4. 울거미(전통 구조) 세로 울거미 관통 가로 울거미는 사이 삽입

                ctx.fillStyle = '#d8c3a5';
                ctx.strokeStyle = '#ffffff';
                ctx.lineWidth = 0.5;

                // ===== 좌측 세로 울거미 =====

                ctx.fillRect(
                    toCanvasX(0),
                    toCanvasY(0),
                    geo.frameT * baseScale,
                    geo.outerH * baseScale
                );

                ctx.strokeRect(
                    toCanvasX(0),
                    toCanvasY(0),
                    geo.frameT * baseScale,
                    geo.outerH * baseScale
                );

                // ===== 우측 세로 울거미 =====

                ctx.fillRect(
                    toCanvasX(geo.outerW - geo.frameT),
                    toCanvasY(0),
                    geo.frameT * baseScale,
                    geo.outerH * baseScale
                );

                ctx.strokeRect(
                    toCanvasX(geo.outerW - geo.frameT),
                    toCanvasY(0),
                    geo.frameT * baseScale,
                    geo.outerH * baseScale
                );

                // ===== 상부 가로 울거미 =====

                ctx.fillRect(
                    toCanvasX(geo.frameT),
                    toCanvasY(0),
                    geo.innerW * baseScale,
                    geo.frameT * baseScale
                );

                ctx.strokeRect(
                    toCanvasX(geo.frameT),
                    toCanvasY(0),
                    geo.innerW * baseScale,
                    geo.frameT * baseScale
                );

                // ===== 하부 가로 울거미 =====

                ctx.fillRect(
                    toCanvasX(geo.frameT),
                    toCanvasY(geo.outerH - geo.frameT),
                    geo.innerW * baseScale,
                    geo.frameT * baseScale
                );

                ctx.strokeRect(
                    toCanvasX(geo.frameT),
                    toCanvasY(geo.outerH - geo.frameT),
                    geo.innerW * baseScale,
                    geo.frameT * baseScale
                );

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

            function clipLineToRect(x1, y1, x2, y2, rx1, ry1, rx2, ry2) {
                let t0 = 0,
                    t1 = 1;
                let dx = x2 - x1,
                    dy = y2 - y1;
                let p = [
                    -dx,
                    dx, -dy,
                    dy
                ];
                let q = [
                    x1 - rx1,
                    rx2 - x1,
                    y1 - ry1,
                    ry2 - y1
                ];
                for (let i = 0; i < 4; i++) {
                    if (p[i] === 0 && q[i] < 0) 
                        return null;
                    let r = q[i] / p[i];
                    if (p[i] < 0) {
                        if (r > t1) 
                            return null;
                        if (r > t0) 
                            t0 = r;
                        }
                    else if (p[i] > 0) {
                        if (r < t0) 
                            return null;
                        if (r < t1) 
                            t1 = r;
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
                ctx.lineTo(x1 + 6 * Math.cos(angle + Math.PI / 6), y1 + 6 * Math.sin(
                    angle + Math.PI / 6
                ));
                ctx.lineTo(
                    x1 + 6 * Math.cos(angle - Math.PI / 6),
                    y1 + 6 * Math.sin(angle - Math.PI / 6)
                );
                ctx.fill();
                ctx.beginPath();
                ctx.moveTo(x2, y2);
                ctx.lineTo(x2 - 6 * Math.cos(angle + Math.PI / 6), y2 - 6 * Math.sin(
                    angle + Math.PI / 6
                ));
                ctx.lineTo(
                    x2 - 6 * Math.cos(angle - Math.PI / 6),
                    y2 - 6 * Math.sin(angle - Math.PI / 6)
                );
                ctx.fill();
                ctx.restore();
            }

            container.addEventListener('mousedown', function (e) {
                isDragging = true;
                startX = e.clientX - panX;
                startY = e.clientY - panY;
            });
            window.addEventListener('mousemove', function (e) {
                if (!isDragging) 
                    return;
                panX = e.clientX - startX;
                panY = e.clientY - startY;
                draw();
            });
            window.addEventListener('mouseup', function () {
                isDragging = false;
            });
            container.addEventListener('wheel', function (e) {
                e.preventDefault();
                const intensity = 0.1;
                if (e.deltaY < 0) 
                    scaleFactor *= (1 + intensity);
                else 
                    scaleFactor /= (1 + intensity);
                scaleFactor = Math.max(0.3, Math.min(scaleFactor, 20));
                draw();
            }, {passive: false});

            btnToggleSidebar.addEventListener('click', function () {
                sidebar
                    .classList
                    .toggle('collapsed');
                setTimeout(resizeCanvas, 250);
            });
            btnResetZoom.addEventListener('click', function () {
                scaleFactor = 1.0;
                panX = 0;
                panY = 0;
                draw();
            });
            [txtW, txtCols, txtH, txtFrame, txtSlat].forEach(
                input => input.addEventListener('input', draw)
            );

            window.addEventListener('resize', resizeCanvas);
            resizeCanvas();
        </script>
    </body>
</html>