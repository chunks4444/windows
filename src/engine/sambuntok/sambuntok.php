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
    <link rel="stylesheet" href="/src/css/editor.css">
    <link rel="stylesheet" href="/src/css/sidebar.css">
    <link rel="stylesheet" href="/src/css/sambuntok.css">
</head>

<body class="pm-generator">

    <?php include __DIR__ . '/../../components/nav.php'; ?>

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
                        <div class="ctrl-header"><span class="ctrl-label" id="lblCols">가로 칸수</span></div>
                        <div class="slider-row">
                            <input type="range" id="txtCols" min="2" max="30" step="1" value="4">
                            <input type="number" class="slider-num" id="numCols" min="2" max="30" step="1" value="4">
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

                    <div class="sb-sub-title">여분 처리</div>
                    <div class="toggle-row">
                        <span class="toggle-label">세로 자동 맞춤</span>
                        <label class="toggle-switch">
                            <input type="checkbox" id="chkShrinkH">
                            <span class="toggle-track"></span>
                        </label>
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

                <div class="sb-section">
                    <div class="toggle-row" style="margin-bottom:0;">
                        <span class="toggle-label">패턴 세로 방향</span>
                        <label class="toggle-switch">
                            <input type="checkbox" id="chkRotate" checked>
                            <span class="toggle-track"></span>
                        </label>
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
                <!-- 변형 -->
                <button class="cv-btn" id="btnScale" title="모서리 변형">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="2" width="14" height="14" rx="1"/>
                        <circle cx="19" cy="19" r="3" fill="currentColor" stroke="none"/>
                        <line x1="16" y1="16" x2="14" y2="14"/>
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

            <!-- 캔버스 상단 제목 오버레이 -->
            <div class="canvas-title-bar">
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
                    <polyline points="1,1 5,5 1,9"/>
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
                                <path d="M5 4H17L20 7V20H5V4Z" stroke="currentColor" stroke-width="2"/>
                                <path d="M8 4V10H16V4" stroke="currentColor" stroke-width="2"/>
                                <path d="M9 15H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            저장
                        </button>
                        <button class="hbtn hbtn-primary" style="flex:1;justify-content:center;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                <path d="M7 3H14L19 8V20H7V3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                <path d="M14 3V8H19" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                <path d="M10 12L11.8 13.8L15 10.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 17H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            주문
                        </button>
                    </div>
                </div>
                <div class="sb-section">
                    <div class="sb-section-title" style="cursor:default;">마감</div>
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
                </div>
                <div class="sb-section">
                    <div class="sb-section-title" style="cursor:default;">배경 사진</div>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <div style="display:flex;gap:6px;">
                        <button class="rp-add-btn" id="btnAddThumb" style="flex:1;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="17 8 12 3 7 8"/>
                                <line x1="12" y1="3" x2="12" y2="15"/>
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
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
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
                                <path d="M12 3v13M7 11l5 5 5-5"/><path d="M5 20h14"/>
                            </svg>
                            PNG
                        </button>
                        <button class="hbtn" id="btnSavePDF" style="flex:1;justify-content:center;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 3v13M7 11l5 5 5-5"/><path d="M5 20h14"/>
                            </svg>
                            PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="/src/js/sambuntok.js"></script>
</body>

</html>