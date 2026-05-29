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
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
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
    *::before,
    *::after {
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

        transition:
            opacity .18s ease,
            transform .18s ease;

        box-shadow: 0 6px 18px rgba(0, 0, 0, .18);

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

        transition:
            opacity .18s ease,
            transform .18s ease;

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
        transition: width 0.22s cubic-bezier(.4, 0, .2, 1),
            opacity 0.22s ease,
            border-color 0.22s ease;
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
        padding: 10px 16px;
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
        margin-bottom: 8px;
    }

    /* ── CONTROL ROWS ────────────────────────── */
    .ctrl {
        margin-bottom: 8px;
    }

    .ctrl:last-child {
        margin-bottom: 0;
    }

    .ctrl-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 4px;
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

    /* ── SLIDER + NUMBER INPUT ROW ───────────── */
    .slider-row {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .slider-row input[type="range"] {
        flex: 1;
    }

    .slider-num {
        width: 52px;
        height: 28px;
        border: 1px solid var(--border-md);
        border-radius: var(--r-xs);
        background: var(--input-bg);
        color: var(--text-1);
        font-size: 12px;
        font-weight: 600;
        font-family: inherit;
        text-align: center;
        outline: none;
        padding: 0 4px;
        transition: border-color .15s, background .15s;
        -moz-appearance: textfield;
    }

    .slider-num::-webkit-inner-spin-button,
    .slider-num::-webkit-outer-spin-button {
        -webkit-appearance: none;
    }

    .slider-num:focus {
        border-color: var(--teal);
        background: #fff;
    }

    .slider-num:hover {
        border-color: var(--teal-mid);
    }

    /* ── TOGGLE SWITCH ───────────────────────── */
    .toggle-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .toggle-label {
        font-size: 12px;
        font-weight: 500;
        color: var(--text-2);
    }

    .toggle-switch {
        position: relative;
        width: 36px;
        height: 20px;
        flex-shrink: 0;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-track {
        position: absolute;
        inset: 0;
        background: var(--border-md);
        border-radius: 20px;
        cursor: pointer;
        transition: background 0.2s;
    }

    .toggle-track::after {
        content: '';
        position: absolute;
        width: 14px;
        height: 14px;
        left: 3px;
        top: 3px;
        background: #fff;
        border-radius: 50%;
        transition: transform 0.2s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }

    .toggle-switch input:checked + .toggle-track {
        background: var(--teal);
    }

    .toggle-switch input:checked + .toggle-track::after {
        transform: translateX(16px);
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
        background-image:
            radial-gradient(circle, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
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

    /* ── CANVAS CONTROLS ─────────────────────── */
    .canvas-controls {
        position: absolute;
        bottom: 16px;
        left: 16px;
        display: flex;
        flex-direction: column;
        gap: 4px;
        z-index: 10;
    }

    .cv-btn {
        width: 32px;
        height: 32px;
        border-radius: var(--r-sm);
        border: 1px solid rgba(0,0,0,0.15);
        background: rgba(0,0,0,0.25);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.12s;
        backdrop-filter: blur(4px);
    }

    .cv-btn:hover {
        background: rgba(0,0,0,0.4);
    }

    .cv-sep {
        height: 1px;
        background: rgba(255,255,255,0.12);
        margin: 2px 0;
    }

    /* ── ZOOM HINT ───────────────────────────── */
    .zoom-hint {
        position: absolute;
        bottom: 16px;
        right: 16px;
        background: rgba(255, 255, 255, 0.07);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.35);
        padding: 4px 11px;
        border-radius: 20px;
        font-size: 10px;
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

        transition: border-color .15s ease,
            background .15s ease;
    }

    .sb-select:hover {
        border-color: var(--border-md);
    }

    .sb-select:focus {
        border-color: var(--teal);
        background: #fff;
    }

    .door-row {
        display: flex;
        gap: 10px;
    }

    .door-row .ctrl {
        flex: 1;
        margin-bottom: 0;
    }

    /* ── RIGHT PANEL (썸네일) ───────────────── */
    .right-panel {
        position: absolute;
        right: 0;
        top: 60px;
        bottom: 0;
        width: 200px;
        transform: translateX(100%);
        opacity: 0;
        z-index: 50;
        transition: transform 0.22s ease, opacity 0.22s ease;
        background: var(--sidebar-bg);
        border-left: 1px solid var(--border);
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: var(--border) transparent;
    }

    .right-panel.open {
        transform: translateX(0);
        opacity: 1;
    }

    .rp-inner {
        padding: 14px 12px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .rp-title {
        font-size: 9.5px;
        font-weight: 700;
        letter-spacing: 0.9px;
        text-transform: uppercase;
        color: var(--text-3);
    }

    .rp-add-btn {
        width: 100%;
        height: 32px;
        border: 1px dashed var(--border-md);
        border-radius: var(--r-sm);
        background: transparent;
        color: var(--text-3);
        font-size: 11px;
        font-family: inherit;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        transition: border-color 0.12s, color 0.12s, background 0.12s;
    }

    .rp-add-btn:hover {
        border-color: var(--teal);
        color: var(--teal);
        background: var(--accent-bg);
    }

    .rp-thumb-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .rp-thumb-item {
        position: relative;
        border-radius: var(--r-sm);
        overflow: hidden;
        border: 2px solid transparent;
        cursor: pointer;
        transition: border-color 0.12s;
    }

    .rp-thumb-item:hover {
        border-color: var(--teal-mid);
    }

    .rp-thumb-item.active {
        border-color: var(--teal);
    }

    .rp-thumb-item img {
        display: block;
        width: 100%;
        aspect-ratio: 4/3;
        object-fit: cover;
    }

    .rp-thumb-item .rp-remove {
        position: absolute;
        top: 5px;
        right: 5px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: rgba(0,0,0,0.55);
        border: none;
        color: #fff;
        font-size: 11px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.12s, background 0.12s;
    }

    .rp-thumb-item:hover .rp-remove {
        opacity: 1;
    }

    .rp-remove:hover {
        background: rgba(200,50,50,0.85) !important;
    }

    .rp-ai-btn {
        width: 100%;
        height: 34px;
        background: var(--teal);
        border: none;
        border-radius: var(--r-sm);
        color: #fff;
        font-size: 12px;
        font-weight: 600;
        font-family: inherit;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        letter-spacing: -0.2px;
        transition: background 0.12s;
    }

    .rp-ai-btn:hover {
        background: #2F7169;
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
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                    <rect y="1.5" width="14" height="1.5" rx="0.75" fill="currentColor" />
                    <rect y="6.25" width="14" height="1.5" rx="0.75" fill="currentColor" />
                    <rect y="11" width="14" height="1.5" rx="0.75" fill="currentColor" />
                </svg>
                <span>치수창</span>
            </button>

            <button class="hbtn " id="btnSavePNG">Download</button>
            <button class="hbtn" id="btnSavePDF">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                    <!-- 문서 -->
                    <path d="M6 3H14L19 8V21H6V3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                    <!-- 접힌 모서리 -->
                    <path d="M14 3V8H19" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                    <!-- PDF -->
                    <text x="12" y="17" text-anchor="middle" font-size="5" font-weight="700" fill="currentColor">
                        PDF
                    </text>
                </svg>
                <span>PDF</span>
            </button>

            <button class="hbtn" id="btnAICompose">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                </svg>
                <span>AI 합성</span>
            </button>
            <input type="file" id="aiFileUploader" accept="image/*" multiple style="display: none;">


            <!-- 저장 -->
            <button class="hbtn hbtn-dark">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                    <path d="M5 4H17L20 7V20H5V4Z" stroke="currentColor" stroke-width="2" />
                    <path d="M8 4V10H16V4" stroke="currentColor" stroke-width="2" />
                    <path d="M9 15H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
                <span>저장</span>
            </button>

            <!-- 주문 -->
            <button class="hbtn hbtn-dark hbtn-primary">

                <!-- 오더(주문) 아이콘 -->
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none">

                    <!-- 종이 -->
                    <path d="M7 3H14L19 8V20H7V3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />

                    <!-- 접힌 모서리 -->
                    <path d="M14 3V8H19" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />

                    <!-- 체크 -->
                    <path d="M10 12L11.8 13.8L15 10.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />

                    <!-- 주문 라인 -->
                    <path d="M10 17H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" />

                </svg>

                <span>주문</span>

            </button>

            <!-- 마이페이지 -->
            <button class="hbtn hbtn-dark">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="2" />
                    <path d="M4 20C4 16.5 7 14 12 14C17 14 20 16.5 20 20" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
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
                <div class="sb-section">
                    <div class="sb-section-title">문 형식</div>

                    <div class="door-row">

                        <div class="ctrl">
                            <div class="ctrl-header">
                                <span class="ctrl-label">문 종류</span>
                            </div>

                            <select id="txtDoorType" class="sb-select">
                                <option value="swing">여닫이</option>
                                <option value="slide">미서기</option>
                            </select>
                        </div>

                        <div class="ctrl">
                            <div class="ctrl-header">
                                <span class="ctrl-label">문 짝수</span>
                            </div>

                            <select id="txtDoorCount" class="sb-select">
                                <option value="1">1짝</option>
                                <option value="2">2짝</option>
                                <option value="3">3짝</option>
                                <option value="4">4짝</option>
                            </select>
                        </div>

                    </div>
                </div>
                <!-- 문 치수 -->
                <div class="sb-section">
                    <div class="sb-section-title">문 치수</div>

                    <div class="ctrl">
                        <div class="ctrl-header">
                            <span class="ctrl-label">가로폭</span>
                        </div>
                        <div class="slider-row">
                            <input type="range" id="txtW" min="400" max="1500" step="1" value="900">
                            <input type="number" class="slider-num" id="numW" min="400" max="1500" step="1" value="900">
                        </div>
                    </div>

                    <div class="ctrl">
                        <div class="ctrl-header">
                            <span class="ctrl-label">세로높이</span>
                        </div>
                        <div class="slider-row">
                            <input type="range" id="txtH" min="800" max="2400" step="1" value="1800">
                            <input type="number" class="slider-num" id="numH" min="800" max="2400" step="1" value="1800">
                        </div>
                    </div>
                </div>

                <!-- 창살 설정 -->
                <div class="sb-section">
                    <div class="sb-section-title">창살 설정</div>

                    <div class="ctrl">
                        <div class="ctrl-header">
                            <span class="ctrl-label">가로 칸수</span>
                        </div>
                        <div class="slider-row">
                            <input type="range" id="txtCols" min="2" max="30" step="1" value="4">
                            <input type="number" class="slider-num" id="numCols" min="2" max="30" step="1" value="4">
                        </div>
                    </div>

                    <div class="ctrl">
                        <div class="ctrl-header">
                            <span class="ctrl-label">세로울거미 두께</span>
                        </div>
                        <div class="slider-row">
                            <input type="range" id="txtFrame" min="35" max="150" step="1" value="45">
                            <input type="number" class="slider-num" id="numFrame" min="35" max="150" step="1" value="45">
                        </div>
                    </div>
                    <div class="ctrl">
                        <div class="ctrl-header">
                            <span class="ctrl-label">가로울거미 두께</span>
                        </div>
                        <div class="slider-row">
                            <input type="range" id="txtFrameH" min="35" max="150" step="1" value="45">
                            <input type="number" class="slider-num" id="numFrameH" min="35" max="150" step="1" value="45">
                        </div>
                    </div>                    

                    <div class="ctrl">
                        <div class="ctrl-header">
                            <span class="ctrl-label">창살 두께</span>
                        </div>
                        <div class="slider-row">
                            <input type="range" id="txtSlat" min="8" max="25" step="1" value="12">
                            <input type="number" class="slider-num" id="numSlat" min="8" max="25" step="1" value="12">
                        </div>
                    </div>
                </div>

                <!-- 풍판 -->
                <div class="sb-section">
                    <div class="sb-section-title">풍판</div>
                    <div class="toggle-row">
                        <span class="toggle-label">풍판 사용</span>
                        <label class="toggle-switch">
                            <input type="checkbox" id="chkPungpan">
                            <span class="toggle-track"></span>
                        </label>
                    </div>
                    <div class="ctrl" id="pungpanCtrl" style="display:none;">
                        <div class="ctrl-header">
                            <span class="ctrl-label">풍판 높이</span>
                        </div>
                        <div class="slider-row">
                            <input type="range" id="txtPungpan" min="0" max="600" step="1" value="0">
                            <input type="number" class="slider-num" id="numPungpan" min="0" max="600" step="1" value="0">
                        </div>
                    </div>
                </div>

                <!-- 제작 시방서 -->
                <div class="sb-section">
                    <div class="sb-section-title">제작 시방서</div>
                    <div class="spec-grid">
                        <div class="spec-card">
                            <div class="spec-lbl">외경 가로</div>
                            <div class="spec-val"><span id="spOuterW">0</span><span class="spec-unit">mm</span></div>
                        </div>
                        <div class="spec-card">
                            <div class="spec-lbl">외경 세로</div>
                            <div class="spec-val"><span id="spOuterH">0</span><span class="spec-unit">mm</span></div>
                        </div>
                        <div class="spec-card">
                            <div class="spec-lbl">내경 가로</div>
                            <div class="spec-val"><span id="spInnerW">0</span><span class="spec-unit">mm</span></div>
                        </div>
                        <div class="spec-card">
                            <div class="spec-lbl">내경 세로</div>
                            <div class="spec-val"><span id="spInnerH">0</span><span class="spec-unit">mm</span></div>
                        </div>
                        <div class="spec-card">
                            <div class="spec-lbl">가로 칸수</div>
                            <div class="spec-val"><span id="spCounts">0</span><span class="spec-unit">칸</span></div>
                        </div>
                        <div class="spec-card">
                            <div class="spec-lbl">세로 칸수</div>
                            <div class="spec-val"><span id="spRows">0</span><span class="spec-unit">칸</span></div>
                        </div>
                        <div class="spec-card">
                            <div class="spec-lbl">간격 먹줄</div>
                            <div class="spec-val"><span id="spStep">0</span><span class="spec-unit">mm</span></div>
                        </div>
                        <div class="spec-card">
                            <div class="spec-lbl">풍판 높이</div>
                            <div class="spec-val"><span id="spPungpan">0</span><span class="spec-unit">mm</span></div>
                        </div>
                        <div class="spec-card accent">
                            <div class="spec-lbl">살간격</div>
                            <div class="spec-val"><span id="spEye">0</span><span class="spec-unit">mm</span></div>
                        </div>
                        <div class="spec-card accent-blue">
                            <div class="spec-lbl">사선 간격</div>
                            <div class="spec-val"><span id="spDiagEye">0</span><span class="spec-unit">mm</span></div>
                        </div>
                        <div class="spec-card accent full">
                            <div class="spec-lbl">전체 문폭</div>
                            <div class="spec-val">
                                <span id="spTotalDoorW">0</span>
                                <span class="spec-unit">mm</span>
                            </div>
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
                    모든 칸의 순간격(살간격)이 동일하게 계산됩니다.
                    올거미 촉도면은 상세도면으로 추가 예정입니다.
                </div>

            </div>
        </div>

        <!-- CANVAS -->
        <div class="canvas-area" id="canvasContainer">
            <div class="zoom-hint">휠: 확대/축소 &nbsp;·&nbsp; 드래그: 이동</div>

            <!-- 캔버스 컨트롤 버튼 -->
            <div class="canvas-controls">
                <!-- 줌인 -->
                <button class="cv-btn" id="btnZoomIn" title="확대">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <circle cx="11" cy="11" r="7"/>
                        <line x1="11" y1="8" x2="11" y2="14"/>
                        <line x1="8" y1="11" x2="14" y2="11"/>
                        <line x1="16.5" y1="16.5" x2="21" y2="21"/>
                    </svg>
                </button>
                <!-- 줌아웃 -->
                <button class="cv-btn" id="btnZoomOut" title="축소">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <circle cx="11" cy="11" r="7"/>
                        <line x1="8" y1="11" x2="14" y2="11"/>
                        <line x1="16.5" y1="16.5" x2="21" y2="21"/>
                    </svg>
                </button>
                <div class="cv-sep"></div>
                <!-- 초기화 -->
                <button class="cv-btn" id="btnResetView" title="화면 초기화">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4V10H10"/>
                        <path d="M20 20V14H14"/>
                        <path d="M20 9A8 8 0 0 0 6.34 5.34L4 8"/>
                        <path d="M4 15A8 8 0 0 0 17.66 18.66L20 16"/>
                    </svg>
                </button>
            </div>

            <canvas id="doorCanvas"></canvas>
        </div>

        <!-- RIGHT PANEL: 업로드 썸네일 -->
        <div class="right-panel" id="rightPanel">
            <div class="rp-inner">
                <div class="rp-title">배경 사진</div>
                <button class="rp-add-btn" id="btnAddThumb">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    사진 추가
                </button>
                <div class="rp-thumb-list" id="thumbList"></div>
                <button class="rp-ai-btn" onclick="startAISynthesis()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                    AI 합성 시작
                </button>
            </div>
        </div>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
    let appBackgroundImage = null;
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
    const btnToggleSidebar = document.getElementById('btnToggleSidebar');
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

    // 3. AI 합성 시작 함수 (버튼에 연결)
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

        alert("AI 합성을 시작합니다.");
        // 여기에 이후 전송 로직 작성
    }

function calculateGeometry() {

    const cols = parseInt(txtCols.value);

    // 슬라이더값 = 외경 고정
    const outerW   = parseInt(txtW.value);
    const outerH   = parseInt(txtH.value);
    const pungpanH = parseInt(document.getElementById('txtPungpan').value) || 0;
    const chk      = document.getElementById('chkPungpan');
    const pungpanOn = chk.checked;

    // 세로울거미 (좌·우)
    const frameW = parseInt(txtFrame.value);
    // 가로울거미 (상·하)
    const frameH = parseInt(txtFrameH.value);

    const slatT = parseInt(txtSlat.value);

    // 내경 가로
    const innerW = outerW - (2 * frameW);

    // 가로: cols 균등 분할 → cellW 확정
    const cellW = (innerW - (slatT * (cols - 1))) / cols;
    const stepW = cellW + slatT;

    // 풍판 ON이면 슬라이더값 사용, OFF면 0
    const effectivePungpanInput = pungpanOn ? pungpanH : 0;

    // 세로: 풍판 제외한 사용 가능 높이
    const availH = outerH - (2 * frameH) - effectivePungpanInput;
    const rows   = Math.max(1, Math.floor((availH + slatT) / stepW));

    // cellH = cellW (정사각형 유지)
    const cellH = cellW;

    // 실제 패턴이 차지하는 세로 내경 높이
    const innerH = rows * cellW + (rows - 1) * slatT;

    // 실제 패턴 영역 높이 (상하 울거미 포함)
    const actualPatternH = frameH + innerH + frameH;

    // 남는 공간
    const surplus = (outerH - effectivePungpanInput) - actualPatternH;

    // 실제 풍판 높이 = 슬라이더값 + surplus 흡수 (풍판 ON일 때)
    const effectivePungpanH = pungpanOn ? effectivePungpanInput + surplus : 0;
    const actualPungpanH    = effectivePungpanH;

    const actualFrameHBottom = pungpanOn
        ? frameH
        : frameH + surplus;

    // 공용
    const step  = stepW;
    const stepH = cellH + slatT;
    const cellSize = cellW;

    const diagEye =
        ((step * Math.sqrt(2)) - (slatT * 2)).toFixed(1);

    const tenonDepth =
        Math.round(slatT * 0.8);

    const doorType  = txtDoorType.value;
    const doorCount = parseInt(txtDoorCount.value);
    const overlap   = frameW * 0.55;

    let totalDoorWidth;

    if (doorType === 'slide') {
        if      (doorCount === 1) totalDoorWidth = outerW;
        else if (doorCount === 2) totalDoorWidth = (outerW * 2) - overlap;
        else if (doorCount === 3) totalDoorWidth = (outerW * 3) - (overlap * 2);
        else if (doorCount === 4) totalDoorWidth = (outerW * 4) - (overlap * 2);
    } else {
        totalDoorWidth = outerW * doorCount;
    }

geo = {
    cellSize,
    cellW,
    cellH,

    outerW,
    outerH,

    frameW,
    frameH,
    frameHBottom: actualFrameHBottom,  // 하단 울거미 (남는 공간 흡수)

    slatT,
    slatV: slatT,
    slatH: slatT,

    step,
    stepH,

    cols,
    rows,
    rowsInt: rows,  // 이미 정수

    innerW,
    innerH,

    actualPatternH,
    actualPungpanH,
    effectivePungpanH,

    diagEye,
    tenonDepth,
    totalDoorWidth
};

    // 시방서 업데이트
    document.getElementById('spOuterW').innerText = Math.round(outerW);
    document.getElementById('spOuterH').innerText = Math.round(outerH);  // 외경 고정값

    document.getElementById('spInnerW').innerText = Math.round(innerW);
    document.getElementById('spInnerH').innerText = Math.round(innerH);

    document.getElementById('spCounts').innerText = cols;
    document.getElementById('spRows').innerText = Math.round(rows);

    document.getElementById('spStep').innerText = step.toFixed(1);

    document.getElementById('spPungpan').innerText = Math.round(effectivePungpanH);

    document.getElementById('spEye').innerText =
        cellW.toFixed(1);

    document.getElementById('spDiagEye').innerText =
        diagEye;

    document.getElementById('spTotalDoorW').innerText =
        Math.round(totalDoorWidth);
}

    function resizeCanvas() {
        canvas.width = container.clientWidth;
        canvas.height = container.clientHeight;
        draw();
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



function draw() {

    calculateGeometry();

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

    const Color_Slat_Fill  = patternBroken ? '#cc0000' : '#111111';
    const Color_Tenon_Fill = patternBroken ? '#cc0000' : '#111111';

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
            toCanvasY(geo.frameH),
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
            const topY  = geo.frameH;
            const botY  = geo.frameH + geo.innerH;

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
            ctx.fillStyle =
                Color_Slat_Fill;

            ctx.fillRect(
                toCanvasX(left),
                toCanvasY(topY),
                geo.slatV * baseScale,
                geo.innerH * baseScale
            );

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
            const ry    = geo.frameH + j * (geo.cellH + geo.slatH) - geo.slatH / 2;
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
            ctx.fillStyle =
                Color_Slat_Fill;

            ctx.fillRect(
                toCanvasX(leftX),
                toCanvasY(top),
                geo.innerW * baseScale,
                geo.slatH * baseScale
            );

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

        ctx.strokeStyle = patternBroken ? '#cc0000' : '#111111';

        ctx.lineWidth =
            ((geo.slatV + geo.slatH) / 2) * baseScale;

        for (let row = 0; row < geo.rowsInt; row++) {

            for (let col = 0; col < geo.cols; col++) {

                // 각 셀의 좌상 코너 (mm)
                const x = geo.frameW + col * (geo.cellW + geo.slatV);
                const y = geo.frameH + row * (geo.cellH + geo.slatH);

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

        ctx.fillStyle = '#d8c3a5';

        // 좌측 세로 울거미 — outerH 전체 높이
        ctx.fillRect(
            toCanvasX(0),
            toCanvasY(0),
            geo.frameW * baseScale,
            geo.outerH * baseScale
        );

        // 우측 세로 울거미 — outerH 전체 높이
        ctx.fillRect(
            toCanvasX(geo.outerW - geo.frameW),
            toCanvasY(0),
            geo.frameW * baseScale,
            geo.outerH * baseScale
        );

        // 상부 가로 울거미
        ctx.fillRect(
            toCanvasX(geo.frameW),
            toCanvasY(0),
            geo.innerW * baseScale,
            geo.frameH * baseScale
        );

        // 하단 울거미 (남는 공간 흡수 또는 고정)
        ctx.fillRect(
            toCanvasX(geo.frameW),
            toCanvasY(geo.frameH + geo.innerH),
            geo.innerW * baseScale,
            geo.frameHBottom * baseScale
        );
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

            ctx.fillStyle = '#e8d5b8';
            ctx.fillRect(
                toCX(ppInnerX),
                toCY(pungpanY),
                ppInnerW * baseScale,
                ppInnerH * baseScale
            );

            ctx.fillStyle = '#d8c3a5';

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

    btnToggleSidebar.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        setTimeout(resizeCanvas, 250);
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
        draw();
    });
    // ── 풍판 토글 ─────────────────────────────
    const chkPungpan   = document.getElementById('chkPungpan');
    const pungpanCtrl  = document.getElementById('pungpanCtrl');

    chkPungpan.addEventListener('change', () => {
        pungpanCtrl.style.display = chkPungpan.checked ? 'block' : 'none';
        if (!chkPungpan.checked) {
            // OFF: 풍판 슬라이더 0 리셋 후 세로 슬라이더를 패턴 높이로 축소
            document.getElementById('txtPungpan').value = 0;
            document.getElementById('numPungpan').value = 0;
            draw();  // actualPatternH 계산
            const newH = Math.round(geo.actualPatternH);
            txtH.value = newH;
            document.getElementById('numH').value = newH;
        }
        draw();
    });

    // ── 슬라이더 ↔ 인풋창 양방향 동기화 ──────────────────

    const syncPairs = [
        { range: txtW,      num: document.getElementById('numW'),       min: 400,  max: 1500 },
        { range: txtH,      num: document.getElementById('numH'),       min: 800,  max: 2400 },
        { range: txtCols,   num: document.getElementById('numCols'),    min: 2,    max: 30   },
        { range: txtFrame,  num: document.getElementById('numFrame'),   min: 35,   max: 150  },
        { range: txtFrameH, num: document.getElementById('numFrameH'),  min: 35,   max: 150  },
        { range: txtSlat,   num: document.getElementById('numSlat'),    min: 8,    max: 25   },
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

    [txtDoorType, txtDoorCount].forEach(input =>
        input.addEventListener('input', draw)
    );

    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    //출력
    btnSavePNG.addEventListener('click', function() {

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
    </script>
</body>

</html>