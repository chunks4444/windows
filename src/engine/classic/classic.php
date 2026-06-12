<?php
// 전통 창호 정자살 도면 설계기
// 모든 계산은 클라이언트(JS)에서 처리됩니다.
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../lib/colors.php';
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once __DIR__ . '/../../lib/meta.php'; meta_tags(); ?>
    <link rel="stylesheet" href="/src/css/engine-common.css?v=<?= md5_file(__DIR__ . '/../../css/engine-common.css') ?>">
</head>

<body class="pm-generator">
    <?php include __DIR__ . '/../../components/nav.php'; ?>
    <?php include __DIR__ . '/../../components/auth_guard.php'; ?>

    <input type="file" id="aiFileUploader" accept="image/*" multiple style="display: none;">

    <!-- MAIN -->
    <div class="main">

        <!-- SIDEBAR -->
        <div class="controls" id="sidebar">
            <div class="sb-inner">

                <!-- ── 문 설정 그룹 ─────────────── -->
                <div class="sb-section">
                    <div class="sb-section-title">문 설정</div>

                    <div class="door-row">
                        <div class="ctrl">
                            <div class="ctrl-header"><span class="ctrl-label">문 종류</span></div>
                            <select id="txtDoorType" class="sb-select">
                                <option value="swing">여닫이</option>
                                <option value="slide">미서기</option>
                            </select>
                        </div>
                        <div class="ctrl">
                            <div class="ctrl-header"><span class="ctrl-label">문 짝수</span></div>
                            <select id="txtDoorCount" class="sb-select">
                                <option value="1">1짝</option>
                                <option value="2">2짝</option>
                                <option value="3">3짝</option>
                                <option value="4">4짝</option>
                            </select>
                        </div>
                    </div>

                    <div class="sb-sub-title">문 치수</div>
                    <div class="ctrl">
                        <div class="ctrl-header"><span class="ctrl-label">가로폭</span></div>
                        <div class="slider-row">
                            <input type="range" id="txtW" min="400" max="1500" step="1" value="600">
                            <input type="number" class="slider-num" id="numW" min="400" max="1500" step="1" value="600">
                        </div>
                    </div>
                    <div class="ctrl">
                        <div class="ctrl-header"><span class="ctrl-label">세로높이</span></div>
                        <div class="slider-row">
                            <input type="range" id="txtH" min="400" max="2600" step="1" value="1707">
                            <input type="number" class="slider-num" id="numH" min="400" max="2600" step="1" value="1707">
                        </div>
                    </div>

                    <div class="sb-sub-title">창살 설정</div>
                    <div class="ctrl">
                        <div class="ctrl-header"><span class="ctrl-label">가로 칸수</span></div>
                        <div class="slider-row">
                            <input type="range" id="txtCols" min="2" max="30" step="1" value="12">
                            <input type="number" class="slider-num" id="numCols" min="2" max="30" step="1" value="12">
                        </div>
                    </div>
                    <div class="ctrl">
                        <div class="ctrl-header"><span class="ctrl-label">좌우울거미 두께</span></div>
                        <div class="slider-row">
                            <input type="range" id="txtFrame" min="20" max="150" step="1" value="60">
                            <input type="number" class="slider-num" id="numFrame" min="20" max="150" step="1" value="60">
                        </div>
                    </div>
                    <div class="ctrl">
                        <div class="ctrl-header"><span class="ctrl-label">상하울거미 두께</span></div>
                        <div class="slider-row">
                            <input type="range" id="txtFrameH" min="20" max="150" step="1" value="60">
                            <input type="number" class="slider-num" id="numFrameH" min="20" max="150" step="1" value="60">
                        </div>
                    </div>
                    <div class="ctrl">
                        <div class="ctrl-header"><span class="ctrl-label">창살 두께</span></div>
                        <div class="slider-row">
                            <input type="range" id="txtSlat" min="8" max="35" step="1" value="12">
                            <input type="number" class="slider-num" id="numSlat" min="8" max="35" step="1" value="12">
                        </div>
                    </div>

                    <div id="ratioCtrl">
                    <div class="ctrl">
                        <div class="ctrl-header"><span class="ctrl-label">가로살 배열</span></div>
                        <div style="display:flex;gap:6px;align-items:center;">
                            <div style="display:flex;flex-direction:column;align-items:center;gap:3px;flex:1;">
                                <span style="font-size:10px;color:var(--text-3);font-weight:600;">상</span>
                                <select id="txtPatternTop" class="sb-select" style="width:100%;">
                                    <option value="0">0</option>
                                    <option value="1" style="font-weight:700;">1</option>
                                    <option value="2">2</option>
                                    <option value="3" selected style="font-weight:700;">3</option>
                                    <option value="4">4</option>
                                    <option value="5" style="font-weight:700;">5</option>
                                    <option value="6">6</option>
                                    <option value="7" style="font-weight:700;">7</option>
                                </select>
                            </div>
                            <div style="display:flex;flex-direction:column;align-items:center;gap:3px;flex:1;">
                                <span style="font-size:10px;color:var(--text-3);font-weight:600;">중</span>
                                <select id="txtPatternMid" class="sb-select" style="width:100%;">
                                    <option value="0">0</option>
                                    <option value="1" style="font-weight:700;">1</option>
                                    <option value="2">2</option>
                                    <option value="3" style="font-weight:700;">3</option>
                                    <option value="4">4</option>
                                    <option value="5" selected style="font-weight:700;">5</option>
                                    <option value="6">6</option>
                                    <option value="7" style="font-weight:700;">7</option>
                                    <option value="8">8</option>
                                    <option value="9" style="font-weight:700;">9</option>
                                </select>
                            </div>
                            <div style="display:flex;flex-direction:column;align-items:center;gap:3px;flex:1;">
                                <span style="font-size:10px;color:var(--text-3);font-weight:600;">하</span>
                                <select id="txtPatternBot" class="sb-select" style="width:100%;">
                                    <option value="0">0</option>
                                    <option value="1" style="font-weight:700;">1</option>
                                    <option value="2">2</option>
                                    <option value="3" selected style="font-weight:700;">3</option>
                                    <option value="4">4</option>
                                    <option value="5" style="font-weight:700;">5</option>
                                    <option value="6">6</option>
                                    <option value="7" style="font-weight:700;">7</option>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" id="txtPattern" value="3/5/3">
                    </div>
                    <div class="ctrl">
                        <div class="ctrl-header"><span class="ctrl-label">세로 비율</span></div>
                        <div class="slider-row">
                            <input type="range" id="txtRatio" min="1.0" max="5.0" step="0.1" value="1.2">
                            <input type="number" class="slider-num" id="numRatio" min="1.0" max="5.0" step="0.1" value="1.2">
                        </div>
                    </div>
                    </div>

                    <div class="sb-sub-title">풍판</div>
                    <div class="toggle-row">
                        <span class="toggle-label">풍판 사용</span>
                        <label class="toggle-switch">
                            <input type="checkbox" id="chkPungpan">
                            <span class="toggle-track"></span>
                        </label>
                    </div>
                    <div class="ctrl" id="pungpanCtrl" style="display:none;">
                        <div class="ctrl-header"><span class="ctrl-label">풍판 높이</span></div>
                        <div class="slider-row">
                            <input type="range" id="txtPungpan" min="0" max="600" step="1" value="0">
                            <input type="number" class="slider-num" id="numPungpan" min="0" max="600" step="1" value="0">
                        </div>
                    </div>
                </div>


                <!-- ── 제작 시방서 ────────────────── -->
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

                        <div class="spec-card accent">
                            <div class="spec-lbl">상/하 울거미</div>
                            <div class="spec-val"><span id="spFrameHTop">0</span><span class="spec-unit">mm</span></div>
                        </div>
                        <div class="spec-card accent">
                            <div class="spec-lbl">전체 문폭</div>
                            <div class="spec-val"><span id="spTotalDoorW">0</span><span class="spec-unit">mm</span></div>
                        </div>
                        <div class="spec-card" id="spOverlapCard" style="display:none;">
                            <div class="spec-lbl">겹침</div>
                            <div class="spec-val"><span id="spOverlap">0</span><span class="spec-unit">mm</span></div>
                        </div>
                    </div>
                </div>

                <!-- ── 부재 목록 ──────────────────── -->
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
                        <div class="slat-group-title">가로살 · 세로살(내경에 살두께 곱하기 2)</div>
                        <div class="diag-list">
                            <div class="slat-row">
                                <span class="slat-len" id="spHSlatLen">—</span><span class="slat-len-unit">mm</span>
                                <span class="slat-cnt" id="spHSlatCnt">—</span>
                            </div>
                            <div class="slat-row">
                                <span class="slat-len" id="spVSlatLen">—</span><span class="slat-len-unit">mm</span>
                                <span class="slat-cnt" id="spVSlatCnt">—</span>
                            </div>
                        </div>
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
                    <polyline points="5,1 1,5 5,9" />
                </svg>
            </button>
        </div>

        <div class="canvas-area" id="canvasContainer">
            <div class="zoom-hint">휠: 확대/축소 &nbsp;·&nbsp; 드래그: 이동</div>

            <!-- 캔버스 컨트롤 버튼 -->
            <div class="canvas-controls">
                <!-- 핸드(팬) -->
                <button class="cv-btn" id="btnPan" title="이동 (팬)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 11V6a2 2 0 0 0-4 0v5"/>
                        <path d="M14 10V4a2 2 0 0 0-4 0v6"/>
                        <path d="M10 10.5V6a2 2 0 0 0-4 0v8.5"/>
                        <path d="M18 8a2 2 0 0 1 4 0v6a8 8 0 0 1-8 8h-2c-2.76 0-4.5-.86-5.99-2.34l-3.6-3.6a2 2 0 0 1 2.83-2.82L7 15"/>
                    </svg>
                </button>
                <div class="cv-sep"></div>
                <!-- 줌인 -->
                <button class="cv-btn" id="btnZoomIn" title="확대">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <circle cx="11" cy="11" r="7" />
                        <line x1="11" y1="8" x2="11" y2="14" />
                        <line x1="8" y1="11" x2="14" y2="11" />
                        <line x1="16.5" y1="16.5" x2="21" y2="21" />
                    </svg>
                </button>
                <!-- 줌아웃 -->
                <button class="cv-btn" id="btnZoomOut" title="축소">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <circle cx="11" cy="11" r="7" />
                        <line x1="8" y1="11" x2="14" y2="11" />
                        <line x1="16.5" y1="16.5" x2="21" y2="21" />
                    </svg>
                </button>
                <div class="cv-sep"></div>
                <!-- 변형 -->
                <button class="cv-btn" id="btnScale" title="모서리 변형">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="2" width="14" height="14" rx="1" />
                        <circle cx="19" cy="19" r="3" fill="currentColor" stroke="none" />
                        <line x1="16" y1="16" x2="14" y2="14" />
                    </svg>
                </button>
                <button class="cv-btn" id="btnResetPlacement" title="배치 초기화" style="display:none;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <line x1="9" y1="12" x2="15" y2="12"/>
                        <line x1="12" y1="9" x2="12" y2="15"/>
                    </svg>
                </button>
                <div class="cv-sep"></div>
                <!-- 초기화 -->
                <button class="cv-btn" id="btnResetView" title="화면 초기화">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4V10H10" />
                        <path d="M20 20V14H14" />
                        <path d="M20 9A8 8 0 0 0 6.34 5.34L4 8" />
                        <path d="M4 15A8 8 0 0 0 17.66 18.66L20 16" />
                    </svg>
                </button>
                <div class="cv-sep"></div>
                <button class="cv-btn" id="btnEditDelete" title="선 삭제&#10;클릭 → 선 삭제&#10;다시 클릭 → 복구">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4" y="11" width="16" height="8" rx="2"/>
                        <rect x="4" y="11" width="7" height="8" rx="0" fill="currentColor" opacity="0.4" stroke="none"/>
                        <rect x="4" y="11" width="7" height="8" rx="0" fill="none" stroke="currentColor" stroke-width="1.5"/>
                        <line x1="4" y1="19" x2="20" y2="19"/>
                        <path d="M9 8l3-3 3 3" stroke-width="1.8"/>
                    </svg>
                </button>
                <button class="cv-btn" id="btnEditAdd" title="선 추가&#10;① 시작 교점 클릭&#10;② 끝 교점 클릭 → 선 완성">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"/>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                    </svg>
                </button>
                <button class="cv-btn" id="btnEditClear" title="편집 초기화&#10;모든 삭제·추가 선 초기화">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="1 4 1 10 7 10"/>
                        <path d="M3.51 15a9 9 0 1 0 .49-3.37"/>
                    </svg>
                </button>
            </div>

            <!-- 캔버스 상단 제목 오버레이 -->
            <div class="canvas-title-bar">
                <div class="title-btn-group">
                    <label class="hdr-title-badge" for="drawingName">
                        <div class="badge-dot"></div>
                        <input type="text" class="drawing-name-input" id="drawingName" placeholder="도면 이름 입력…" maxlength="40">
                    </label>
                    <button class="title-group-btn" id="btnNewDrawing" title="새 도면">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        <span>새 도면</span>
                    </button>
                    <div class="ver-wrap" style="margin:0;">
                        <button class="title-group-btn" id="dmBtn" title="도면 목록">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                            </svg>
                            <span>도면</span>
                        </button>
                    </div>
                    <div class="ver-wrap" style="margin:0;">
                        <button class="title-group-btn" id="verBtn">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="9" />
                                <polyline points="12 7 12 12 15 15" />
                            </svg>
                            <span id="verLabel">—</span>
                        </button>
                        <div class="ver-dropdown" id="verDropdown">
                            <div id="verList"></div>
                        </div>
                    </div>
                </div>
            </div>

            <canvas id="doorCanvas"></canvas>

            <!-- 렌더링 로딩 오버레이 -->
            <div class="render-overlay" id="renderOverlay" style="display:none;">
                <div class="render-spinner"></div>
                <div class="render-overlay-msg">AI 렌더링 중…</div>
            </div>
        </div>

        <!-- RIGHT SIDEBAR TAB -->
        <div class="sidebar-col">
            <button class="sidebar-tab sidebar-tab-right" id="btnRightSidebarTab" title="배경사진 패널 열기/닫기">
                <svg width="6" height="10" viewBox="0 0 6 10" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="1,1 5,5 1,9" />
                </svg>
            </button>
        </div>

        <!-- RIGHT SIDEBAR -->
        <div class="controls-right" id="rightSidebar">
            <div class="sb-inner-right">
                <div class="sb-section">
                    <div style="display:flex;gap:6px;">
                        <button class="hbtn" id="btnSave" style="flex:1;justify-content:center;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                <path d="M5 4H17L20 7V20H5V4Z" stroke="currentColor" stroke-width="2" />
                                <path d="M8 4V10H16V4" stroke="currentColor" stroke-width="2" />
                                <path d="M9 15H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            </svg>
                            저장
                        </button>
                        <button class="hbtn hbtn-primary" style="flex:1;justify-content:center;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                <path d="M7 3H14L19 8V20H7V3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                                <path d="M14 3V8H19" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                                <path d="M10 12L11.8 13.8L15 10.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M10 17H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            </svg>
                            주문
                        </button>
                    </div>
                </div>
                <div class="sb-section">
                    <div class="sb-section-title" style="cursor:default;">마감</div>
                    <div class="ctrl">
                        <div class="ctrl-header"><span class="ctrl-label">목재</span></div>
                        <select id="txtWood" class="sb-select">
                            <option value="hongsong">홍송</option>
                            <option value="sonamuPine">소나무</option>
                            <option value="oak">참나무</option>
                        </select>
                    </div>
                    <div class="ctrl">
                        <div class="ctrl-header"><span class="ctrl-label">내부 마감</span></div>
                        <select id="txtFinish" class="sb-select">
                            <option value="changhoji">창호지</option>
                            <option value="glass">유리</option>
                            <option value="acrylic">아크릴</option>
                        </select>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:8px;">
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
                    <div style="margin-top:8px;">
                        <div class="color-label" style="margin-bottom:4px;">면</div>
                        <div style="display:flex;gap:6px;align-items:center;">
                            <input type="color" id="faceColorInput" value="#c8102e" style="width:30px;height:28px;padding:2px;border:1px solid var(--border);border-radius:4px;cursor:pointer;flex-shrink:0;">
                            <button id="btnFacePaint" type="button" class="sb-select" style="cursor:pointer;flex:1;font-size:11px;">칠하기</button>
                            <button id="btnFaceClear" type="button" class="sb-select" style="cursor:pointer;flex-shrink:0;padding:0 8px;font-size:11px;display:none;width:auto;">초기화</button>
                        </div>
                    </div>
                </div>
                <div class="sb-section">
                    <div class="sb-section-title" style="cursor:default;">배경 사진</div>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <div style="display:flex;gap:6px;">
                        <button class="rp-add-btn" id="btnAddThumb" style="flex:1;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                <polyline points="17 8 12 3 7 8" />
                                <line x1="12" y1="3" x2="12" y2="15" />
                            </svg>
                            사진 추가
                        </button>
                        <button id="btnClearBg" style="display:none;flex-shrink:0;width:28px;height:28px;border:none;background:none;padding:0;cursor:pointer;color:#e05218;align-items:center;justify-content:center;align-self:center;" title="배경 지우기">
                            <i class="bi bi-x-lg" style="font-size:13px;"></i>
                        </button>
                        </div>
                        <div class="rp-thumb-list" id="thumbList"></div>
                        <textarea id="aiPrompt" class="rp-prompt" placeholder="한국어 또는 영어로 입력&#10;예) 전통 한옥 창호, 따뜻한 실내 조명" rows="3"></textarea>
                        <button class="rp-ai-btn" onclick="startAISynthesis()">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                            </svg>
                            Rendering
                        </button>
                        <div id="renderSavedList" class="render-saved-list"></div>
                    </div>
                </div>
                <div class="sb-section">
                    <div class="sb-section-title" style="cursor:default;">내보내기</div>
                    <div style="display:flex;gap:6px;">
                        <button class="hbtn" id="btnSavePNG" style="flex:1;justify-content:center;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 3v13M7 11l5 5 5-5" />
                                <path d="M5 20h14" />
                            </svg>
                            PNG
                        </button>
                        <button class="hbtn" id="btnSavePDF" style="flex:1;justify-content:center;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 3v13M7 11l5 5 5-5" />
                                <path d="M5 20h14" />
                            </svg>
                            PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- 도면 목록 모달 -->
    <div class="pm-modal-backdrop" id="dmBackdrop" style="z-index:8500;">
        <div class="dm-modal">
            <div class="dm-header">
                <span class="dm-header-title">도면 목록</span>
                <button class="hbtn" id="dmNewBtn" style="height:28px;padding:0 10px;font-size:11px;">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    새 도면
                </button>
            </div>
            <div class="dm-list" id="dmList"></div>
            <div class="dm-footer">
                <button class="hbtn" id="dmCloseBtn" style="width:100%;justify-content:center;">닫기</button>
            </div>
        </div>
    </div>

    <!-- 제목 변경 모달 -->
    <div class="pm-modal-backdrop" id="dmRenameBackdrop" style="z-index:9100;">
        <div class="pm-modal" style="width:320px;">
            <div class="pm-modal-msg">도면 제목 변경</div>
            <input type="text" id="dmRenameInput" class="rp-prompt" style="width:100%;margin-top:4px;" maxlength="40" placeholder="새 제목 입력…">
            <div class="pm-modal-btns">
                <button class="pm-btn-cancel" id="dmRenameCancel">취소</button>
                <button class="pm-btn-ok" id="dmRenameOk">변경</button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        window.__pmokOpenDrawing         = <?= isset($_POST['drawing'])    ? json_encode($_POST['drawing'],    JSON_UNESCAPED_UNICODE) : 'null' ?>;
        window.__pmokCollectionDrawingId = <?= isset($_GET['drawing_id']) ? (int)$_GET['drawing_id']          : 'null' ?>;
        window.__pmokColorGroups         = <?= json_encode(get_color_groups(), JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <script src="/src/js/drawing-sync.js?v=<?= md5_file(__DIR__ . '/../../js/drawing-sync.js') ?>"></script>
    <script src="/src/js/engine-common.js?v=<?= md5_file(__DIR__ . '/../../js/engine-common.js') ?>"></script>
    <script src="/src/js/classic.js?v=<?= md5_file(__DIR__ . '/../../js/classic.js') ?>"></script>
</body>

</html>