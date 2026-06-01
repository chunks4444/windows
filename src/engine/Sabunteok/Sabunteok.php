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
    <link rel="stylesheet" href="/src/css/sambuntok.css">
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
                    <div class="sb-section-title">문 설정</div>

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
                            <span class="ctrl-label">가로 칸수</span>
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

                <!-- 색상 -->
                <div class="sb-section">
                    <div class="sb-section-title">색상</div>
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
    <script src="/src/js/Sabunteok.js"></script>
</body>

</html>
