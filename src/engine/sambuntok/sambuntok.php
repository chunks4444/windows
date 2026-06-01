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
    <title>창호 설계 — 삼분턱 V0.1</title>
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

    .hdr-title-badge {
        display: flex;
        align-items: center;
        gap: 6px;
        background: var(--accent-bg);
        border: 1px solid rgba(58, 140, 130, 0.25);
        border-radius: 20px;
        padding: 3px 12px 3px 8px;
        cursor: text;
        transition: border-color .15s;
    }
    .hdr-title-badge:focus-within {
        border-color: var(--teal);
    }
    .hdr-title-badge.shake {
        border-color: #e03030;
        background: #fff0f0;
        animation: titleShake .35s ease;
    }
    .hdr-title-badge.shake .badge-dot { background: #e03030; }
    .hdr-title-badge.shake .drawing-name-input { color: #e03030; }
    @keyframes titleShake {
        0%,100% { transform: translateX(0); }
        20%      { transform: translateX(-5px); }
        40%      { transform: translateX(5px); }
        60%      { transform: translateX(-4px); }
        80%      { transform: translateX(3px); }
    }

    .drawing-name-input {
        border: none;
        background: transparent;
        color: var(--teal);
        font-size: 11px;
        font-weight: 500;
        font-family: inherit;
        padding: 0;
        outline: none;
        width: 130px;
        transition: width .2s;
        letter-spacing: -0.1px;
    }
    .drawing-name-input:focus { width: 200px; }
    .drawing-name-input::placeholder { color: rgba(58,140,130,0.45); font-weight: 400; }

    .drawing-dates {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 8px;
        margin-left: 10px;
        font-size: 9.5px;
        color: var(--text-3);
        font-weight: 400;
        white-space: nowrap;
    }

    .drawing-date-item strong {
        color: var(--text-2);
        font-weight: 500;
    }

    .drawing-date-sep {
        opacity: 0.3;
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
        position: relative;
        transition: width 0.22s cubic-bezier(.4, 0, .2, 1),
            opacity 0.22s ease,
            border-color 0.22s ease;
        scrollbar-width: thin;
        scrollbar-color: var(--border) transparent;
    }

    .sidebar-col {
        flex-shrink: 0;
        width: 14px;
        align-self: stretch;
        background: var(--canvas-bg);
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .sidebar-tab {
        width: 14px;
        height: 44px;
        flex-shrink: 0;
        background: var(--sidebar-bg);
        border: 1px solid var(--border);
        border-left: none;
        border-radius: 0 6px 6px 0;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        transition: background 0.15s;
    }

    .sidebar-tab:hover {
        background: var(--input-bg);
    }

    .sidebar-tab svg {
        transition: transform 0.22s ease;
        flex-shrink: 0;
        color: var(--text-3);
    }

    .sidebar-tab.collapsed svg {
        transform: rotate(180deg);
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

    /* ── 부재 목록 ───────────────────────────── */
    .slat-group {
        margin-bottom: 10px;
    }
    .slat-group:last-child { margin-bottom: 0; }

    .slat-group-title {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: var(--text-3);
        text-transform: uppercase;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .slat-count-badge {
        background: var(--accent-bg);
        color: var(--teal);
        border-radius: 20px;
        padding: 1px 7px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0;
        text-transform: none;
    }

    .slat-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 5px 8px;
        background: var(--input-bg);
        border: 1px solid var(--border);
        border-radius: var(--r-xs);
        margin-bottom: 3px;
    }
    .slat-row:last-child { margin-bottom: 0; }

    .slat-len {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-1);
        letter-spacing: -0.3px;
    }

    .slat-len-unit {
        font-size: 10px;
        font-weight: 400;
        color: var(--text-3);
        margin-left: 1px;
    }

    .slat-cnt {
        font-size: 11px;
        font-weight: 600;
        color: var(--teal);
        background: var(--accent-bg);
        border-radius: 20px;
        padding: 1px 8px;
    }

    .diag-list {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3px;
    }

    /* ── 색상 드롭다운 ───────────────────────── */
    .color-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    .color-row:last-child { margin-bottom: 0; }

    .color-label {
        font-size: 12px;
        font-weight: 500;
        color: var(--text-2);
    }

    .color-picker-wrap {
        position: relative;
    }

    .color-preview-btn {
        display: flex;
        align-items: center;
        gap: 5px;
        height: 24px;
        padding: 0 7px 0 4px;
        border: 1px solid var(--border-md);
        border-radius: var(--r-xs);
        background: var(--input-bg);
        cursor: pointer;
        font-size: 10px;
        font-family: inherit;
        color: var(--text-2);
        font-weight: 500;
        transition: border-color 0.12s;
        white-space: nowrap;
    }
    .color-preview-btn:hover { border-color: var(--teal); }

    .color-preview-dot {
        width: 14px;
        height: 14px;
        border-radius: 3px;
        flex-shrink: 0;
        border: 1px solid rgba(0,0,0,0.12);
    }

    .color-popup {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% + 4px);
        background: var(--sidebar-bg);
        border: 1px solid var(--border-md);
        border-radius: var(--r-sm);
        padding: 8px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        z-index: 300;
        grid-template-columns: repeat(3, 24px);
        gap: 3px;
    }
    .color-popup.open { display: grid; }

    .color-swatch {
        width: 24px;
        height: 18px;
        border-radius: var(--r-xs);
        border: 2px solid transparent;
        cursor: pointer;
        transition: transform 0.1s, border-color 0.15s;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding-bottom: 2px;
        font-size: 8px;
        color: rgba(255,255,255,0.85);
        text-shadow: 0 1px 2px rgba(0,0,0,0.5);
        font-weight: 600;
    }
    .color-swatch:hover { transform: scale(1.08); }
    .color-swatch.selected {
        border-color: var(--teal);
        box-shadow: 0 0 0 1px var(--teal);
    }

    /* ── VERSION DROPDOWN ────────────────────── */
    .ver-wrap { position: relative; margin-left: 8px; }
    .ver-btn {
        height: 26px;
        padding: 0 10px;
        border-radius: 20px;
        border: 1px solid var(--border-md);
        background: var(--input-bg);
        color: var(--teal);
        font-size: 11px;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 5px;
        transition: border-color .15s, background .15s;
        letter-spacing: -0.1px;
    }
    .ver-btn:hover { border-color: var(--teal); background: var(--accent-bg); }
    .ver-dropdown {
        display: none;
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        background: var(--sidebar-bg);
        border: 1px solid var(--border-md);
        border-radius: var(--r-sm);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        min-width: 240px;
        max-height: 300px;
        overflow-y: auto;
        z-index: 200;
        scrollbar-width: thin;
        scrollbar-color: var(--border) transparent;
    }
    .ver-dropdown.open { display: block; }
    .ver-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 9px 12px;
        cursor: pointer;
        border-bottom: 1px solid var(--border);
        transition: background .1s;
    }
    .ver-item:last-child { border-bottom: none; }
    .ver-item:hover { background: var(--input-bg); }
    .ver-item.active { background: var(--accent-bg); }
    .ver-num { font-size: 11px; font-weight: 700; color: var(--teal); min-width: 30px; }
    .ver-date { font-size: 11px; color: var(--text-3); }
    .ver-empty { padding: 14px 12px; text-align: center; font-size: 12px; color: var(--text-3); }
    </style>
</head>

<body class="pm-generator">

    <?php include __DIR__ . '/../../components/nav.php'; ?>

    <!-- HEADER -->
    <header>

        <label class="hdr-title-badge" for="drawingName">
            <div class="badge-dot"></div>
            <input type="text" class="drawing-name-input" id="drawingName" placeholder="도면 이름 입력…" maxlength="40">
        </label>

        <div class="drawing-dates">
            <span class="drawing-date-item">작성일 <strong id="dateCreated">—</strong></span>
            <span class="drawing-date-sep">·</span>
            <span class="drawing-date-item">수정일 <strong id="dateModified">—</strong></span>
        </div>

        <div class="ver-wrap">
            <button class="ver-btn" id="verBtn">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 15"/>
                </svg>
                <span id="verLabel">—</span>
            </button>
            <div class="ver-dropdown" id="verDropdown">
                <div id="verList"></div>
            </div>
        </div>

        <div class="hdr-actions">



            <button class="hbtn" id="btnSavePNG">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3v13M7 11l5 5 5-5"/>
                    <path d="M5 20h14"/>
                </svg>
                <span>PNG</span>
            </button>
            <button class="hbtn" id="btnSavePDF">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3v13M7 11l5 5 5-5"/>
                    <path d="M5 20h14"/>
                </svg>
                <span>PDF</span>
            </button>

            <button class="hbtn" id="btnAICompose">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                </svg>
                <span>Rendering</span>
            </button>
            <input type="file" id="aiFileUploader" accept="image/*" multiple style="display: none;">


            <!-- 저장 -->
            <button class="hbtn hbtn-dark" id="btnSave">
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



        </div>
    </header>

    <!-- MAIN -->
    <div class="main">

        <!-- SIDEBAR -->
        <div class="controls" id="sidebar">
            <div class="sb-inner">
                <div class="sb-section">

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
                            <input type="range" id="txtW" min="400" max="1500" step="1" value="600">
                            <input type="number" class="slider-num" id="numW" min="400" max="1500" step="1" value="600">
                        </div>
                    </div>

                    <div class="ctrl">
                        <div class="ctrl-header">
                            <span class="ctrl-label">세로높이</span>
                        </div>
                        <div class="slider-row">
                            <input type="range" id="txtH" min="400" max="2600" step="1" value="1707">
                            <input type="number" class="slider-num" id="numH" min="400" max="2600" step="1" value="1707">
                        </div>
                    </div>
                </div>

                <!-- 창살 설정 -->
                <div class="sb-section">
                    <div class="sb-section-title">창살 설정</div>

                    <div class="ctrl">
                        <div class="ctrl-header">
                            <span class="ctrl-label" id="lblCols">가로 칸수</span>
                        </div>
                        <div class="slider-row">
                            <input type="range" id="txtCols" min="2" max="30" step="1" value="4">
                            <input type="number" class="slider-num" id="numCols" min="2" max="30" step="1" value="4">
                        </div>
                    </div>

                    <div class="ctrl">
                        <div class="ctrl-header">
                            <span class="ctrl-label">좌우울거미 두께</span>
                        </div>
                        <div class="slider-row">
                            <input type="range" id="txtFrame" min="20" max="150" step="1" value="60">
                            <input type="number" class="slider-num" id="numFrame" min="20" max="150" step="1" value="60">
                        </div>
                    </div>
                    <div class="ctrl">
                        <div class="ctrl-header">
                            <span class="ctrl-label">상하울거미 두께</span>
                        </div>
                        <div class="slider-row">
                            <input type="range" id="txtFrameH" min="20" max="150" step="1" value="60">
                            <input type="number" class="slider-num" id="numFrameH" min="20" max="150" step="1" value="60">
                        </div>
                    </div>                    

                    <div class="ctrl">
                        <div class="ctrl-header">
                            <span class="ctrl-label">창살 두께</span>
                        </div>
                        <div class="slider-row">
                            <input type="range" id="txtSlat" min="8" max="35" step="1" value="12">
                            <input type="number" class="slider-num" id="numSlat" min="8" max="35" step="1" value="12">
                        </div>
                    </div>
                </div>

                <!-- 풍판 -->
                <div class="sb-section">
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
                <!-- 마감 옵션 -->
                <div class="sb-section">
                    <div class="toggle-row" style="margin-bottom:10px;">
                        <span class="toggle-label">패턴 세로 방향</span>
                        <label class="toggle-switch">
                            <input type="checkbox" id="chkRotate">
                            <span class="toggle-track"></span>
                        </label>
                    </div>
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

                <!-- 색상 -->
                <div class="sb-section">
                    <div class="toggle-row" style="margin-bottom:10px;">
                        <span class="toggle-label">나뭇결 질감</span>
                        <label class="toggle-switch">
                            <input type="checkbox" id="chkGrain" checked>
                            <span class="toggle-track"></span>
                        </label>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                        <div>
                            <div class="color-label" style="margin-bottom:4px;">울거미</div>
                            <div class="color-picker-wrap">
                                <button class="color-preview-btn" id="framePreviewBtn" style="width:100%;">
                                    <span class="color-preview-dot" id="framePreviewDot"></span>
                                    <span id="framePreviewName">—</span>
                                </button>
                                <div class="color-popup" id="framePopup"></div>
                            </div>
                        </div>
                        <div>
                            <div class="color-label" style="margin-bottom:4px;">살</div>
                            <div class="color-picker-wrap">
                                <button class="color-preview-btn" id="slatPreviewBtn" style="width:100%;">
                                    <span class="color-preview-dot" id="slatPreviewDot"></span>
                                    <span id="slatPreviewName">—</span>
                                </button>
                                <div class="color-popup" id="slatPopup"></div>
                            </div>
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
                        <div class="spec-card accent">
                            <div class="spec-lbl">간격 먹줄</div>
                            <div class="spec-val"><span id="spStep">0</span><span class="spec-unit">mm</span></div>
                        </div>
                        <div class="spec-card accent">
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
                        <div class="spec-card accent">
                            <div class="spec-lbl">상/하 울거미</div>
                            <div class="spec-val"><span id="spFrameHTop">0</span><span class="spec-unit">mm</span></div>
                        </div>
                        <div class="spec-card accent">
                            <div class="spec-lbl">전체 문폭</div>
                            <div class="spec-val">
                                <span id="spTotalDoorW">0</span>
                                <span class="spec-unit">mm</span>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- 부재 목록 -->
                <div class="sb-section">
                    <div class="sb-section-title">부재 목록<small>(정치수에 살두께 곱하기 2)</small></div>

                    <div class="slat-group">
                        <div class="slat-group-title">울거미</div>
                        <div class="diag-list">
                            <div class="slat-row">
                                <span class="slat-len" id="spFrVLen">—</span><span class="slat-len-unit">mm</span>
                                <span class="slat-cnt" id="spFrVCnt">2개</span>
                            </div>
                            <div class="slat-row">
                                <span class="slat-len" id="spFrHLen">—</span><span class="slat-len-unit">mm</span>
                                <span class="slat-cnt" id="spFrHCnt">2개</span>
                            </div>
                        </div>
                    </div>

                    <div class="slat-group" id="pungpanMaterialGroup" style="display:none;">
                        <div class="slat-group-title">풍판 <span class="slat-count-badge">1개</span></div>
                        <div class="slat-row">
                            <span class="slat-len" id="spPpHLen">—</span><span class="slat-len-unit">mm</span>
                            <span style="color:var(--text-3);font-size:11px;margin:0 3px;">×</span>
                            <span class="slat-len" id="spPpVLen">—</span><span class="slat-len-unit">mm</span>
                        </div>
                    </div>

                    <div class="slat-group">
                        <div class="slat-group-title" id="dirSlatGroupTitle">가로부재</div>
                        <div class="slat-row">
                            <span class="slat-len" id="spHSlatLen">—</span><span class="slat-len-unit">mm</span>
                            <span class="slat-cnt" id="spHSlatCnt">—</span>
                        </div>
                    </div>

                    <div class="slat-group">
                        <div class="slat-group-title">사선살</div>
                        <div id="spDiagList" class="diag-list"></div>
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
        <div class="sidebar-col">
            <button class="sidebar-tab" id="btnSidebarTab" title="치수창 열기/닫기">
                <svg width="6" height="10" viewBox="0 0 6 10" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="5,1 1,5 5,9"/>
                </svg>
            </button>
        </div>

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
                    Rendering
                </button>
            </div>
        </div>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
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
    let rotateOn  = false;

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
    </script>
</body>

</html>