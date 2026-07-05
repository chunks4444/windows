<?php
// 전통 창호 사분턱 도면 설계기
// 모든 계산은 클라이언트(JS)에서 처리됩니다.
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../lib/colors.php';
require_once __DIR__ . '/../../lib/engine_settings.php';
$cfg = get_engine_settings('diamond');
$costCfg = get_cost_config('diamond');
$patternCategories = get_pattern_categories();
$renderPresets = get_render_presets();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once __DIR__ . '/../../lib/meta.php'; meta_tags(); ?>
    <link rel="stylesheet" href="/src/css/tokens.css?v=<?= md5_file(__DIR__ . '/../../css/tokens.css') ?>">
    <link rel="stylesheet" href="/src/css/engine-common.css?v=<?= md5_file(__DIR__ . '/../../css/engine-common.css') ?>">

</head>

<body class="pm-generator">
    <?php include __DIR__ . '/../../components/engine-nav.php'; ?>
    <?php $blogEngineKey = 'diamond'; include __DIR__ . '/../../components/blog_engine_link.php'; ?>

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
                            <select id="txtDoorType" class="sb-select">
                                <option value="swing" <?= $cfg['doorType'] === 'swing' ? 'selected' : '' ?>>여닫이</option>
                                <option value="slide" <?= $cfg['doorType'] === 'slide' ? 'selected' : '' ?>>미서기</option>
                            </select>
                        </div>
                        <div class="ctrl">
                            <select id="txtDoorCount" class="sb-select">
                                <option value="1" <?= $cfg['doorCount'] === '1' ? 'selected' : '' ?>>1짝</option>
                                <option value="2" <?= $cfg['doorCount'] === '2' ? 'selected' : '' ?>>2짝</option>
                                <option value="3" <?= $cfg['doorCount'] === '3' ? 'selected' : '' ?>>3짝</option>
                                <option value="4" <?= $cfg['doorCount'] === '4' ? 'selected' : '' ?>>4짝</option>
                            </select>
                        </div>
                    </div>

                    <hr class="sb-divider">

                    <div class="ctrl">
                        <div class="ctrl-header"><span class="ctrl-label">문틀 가로</span></div>
                        <div class="slider-row">
                            <input type="range" id="txtW" min="400" max="3000" step="1" value="<?= htmlspecialchars($cfg['W']) ?>">
                            <input type="number" class="slider-num" id="numW" min="400" max="3000" step="1" value="<?= htmlspecialchars($cfg['W']) ?>">
                        </div>
                    </div>
                    <div class="ctrl">
                        <div class="ctrl-header"><span class="ctrl-label">문틀 세로</span></div>
                        <div class="slider-row">
                            <input type="range" id="txtH" min="400" max="3000" step="1" value="<?= htmlspecialchars($cfg['H']) ?>">
                            <input type="number" class="slider-num" id="numH" min="400" max="3000" step="1" value="<?= htmlspecialchars($cfg['H']) ?>">
                        </div>
                    </div>

                    <hr class="sb-divider">
                    <div class="ctrl">
                        <div class="ctrl-header"><span class="ctrl-label">가로 칸수</span></div>
                        <div class="slider-row">
                            <input type="range" id="txtCols" min="2" max="30" step="1" value="<?= htmlspecialchars($cfg['cols']) ?>">
                            <input type="number" class="slider-num" id="numCols" min="2" max="30" step="1" value="<?= htmlspecialchars($cfg['cols']) ?>">
                        </div>
                    </div>
                    <hr class="sb-divider">

                    <div class="ctrl">
                        <div class="ctrl-header"><span class="ctrl-label">좌우 울거미 두께</span></div>
                        <div class="slider-row">
                            <input type="range" id="txtFrame" min="20" max="150" step="1" value="<?= htmlspecialchars($cfg['frame']) ?>">
                            <input type="number" class="slider-num" id="numFrame" min="20" max="150" step="1" value="<?= htmlspecialchars($cfg['frame']) ?>">
                        </div>
                    </div>
                    <div class="ctrl">
                        <div class="ctrl-header"><span class="ctrl-label">상하 울거미 두께</span></div>
                        <div class="slider-row">
                            <input type="range" id="txtFrameH" min="20" max="150" step="1" value="<?= htmlspecialchars($cfg['frameH']) ?>">
                            <input type="number" class="slider-num" id="numFrameH" min="20" max="150" step="1" value="<?= htmlspecialchars($cfg['frameH']) ?>">
                        </div>
                    </div>
                    <hr class="sb-divider">

                    <div class="ctrl">
                        <div class="ctrl-header"><span class="ctrl-label">살 두께</span></div>
                        <div class="slider-row">
                            <input type="range" id="txtSlat" min="8" max="35" step="1" value="<?= htmlspecialchars($cfg['slat']) ?>">
                            <input type="number" class="slider-num" id="numSlat" min="8" max="35" step="1" value="<?= htmlspecialchars($cfg['slat']) ?>">
                        </div>
                    </div>

                    <hr class="sb-divider">
                    <div class="toggle-row">
                        <span class="toggle-label">세로 자동 맞춤</span>
                        <label class="toggle-switch">
                            <input type="checkbox" id="chkShrinkH" <?= $cfg['shrinkH'] === '1' ? 'checked' : '' ?>>
                            <span class="toggle-track"></span>
                        </label>
                    </div>

                    <hr class="sb-divider">
                    <div class="toggle-row">
                        <span class="toggle-label">풍판 사용</span>
                        <label class="toggle-switch">
                            <input type="checkbox" id="chkPungpan" <?= $cfg['pungpanOn'] === '1' ? 'checked' : '' ?>>
                            <span class="toggle-track"></span>
                        </label>
                    </div>
                    <div class="ctrl" id="pungpanCtrl" style="display:<?= $cfg['pungpanOn'] === '1' ? 'block' : 'none' ?>;">
                        <div class="ctrl-header"><span class="ctrl-label">풍판 높이</span></div>
                        <div class="slider-row">
                            <input type="range" id="txtPungpan" min="0" max="600" step="1" value="<?= htmlspecialchars($cfg['pungpan']) ?>">
                            <input type="number" class="slider-num" id="numPungpan" min="0" max="600" step="1" value="<?= htmlspecialchars($cfg['pungpan']) ?>">
                        </div>
                    </div>

                    <hr class="sb-divider">
                    <div class="toggle-row" style="margin-bottom:0;">
                        <span class="toggle-label">치수 표기</span>
                        <label class="toggle-switch">
                            <input type="checkbox" id="chkDimension" <?= $cfg['dimensionOn'] === '1' ? 'checked' : '' ?>>
                            <span class="toggle-track"></span>
                        </label>
                    </div>
                    <div class="toggle-row" style="margin-top:6px;margin-bottom:0;">
                        <span class="toggle-label">문틀 표시</span>
                        <label class="toggle-switch">
                            <input type="checkbox" id="chkMuntol" checked>
                            <span class="toggle-track"></span>
                        </label>
                    </div>
                </div>

                <!-- ── 제작 시방서 ────────────────── -->
                <div class="sb-section sb-collapsed admin-only" style="display:none">
                    <div class="sb-section-title">제작 시방서</div>
                    <div class="spec-grid">
                        <div class="spec-card">
                            <div class="spec-lbl">문틀 가로</div>
                            <div class="spec-val"><span id="spFrameOpeningW">0</span><span class="spec-unit">mm</span></div>
                        </div>
                        <div class="spec-card">
                            <div class="spec-lbl">문틀 세로</div>
                            <div class="spec-val"><span id="spFrameOpeningH">0</span><span class="spec-unit">mm</span></div>
                        </div>
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
                            <div class="spec-lbl">가로 먹줄</div>
                            <div class="spec-val"><span id="spStep">0</span><span class="spec-unit">mm</span></div>
                        </div>
                        <div class="spec-card accent">
                            <div class="spec-lbl">세로 먹줄</div>
                            <div class="spec-val"><span id="spStepV">0</span><span class="spec-unit">mm</span></div>
                        </div>
                        <div class="spec-card accent-blue">
                            <div class="spec-lbl">사선 간격</div>
                            <div class="spec-val"><span id="spDiagEye">0</span><span class="spec-unit">mm</span></div>
                        </div>
                        <div class="spec-card accent-blue">
                            <div class="spec-lbl">살 먹줄</div>
                            <div class="spec-val"><span id="spHalfLapWD">0</span><span class="spec-unit">mm</span></div>
                        </div>
                        <div class="spec-card accent-blue">
                            <div class="spec-lbl">울거미홈폭</div>
                            <div class="spec-val"><span id="spGrooveWDiag">0</span><span class="spec-unit">mm</span></div>
                        </div>
                        <div class="spec-card accent">
                            <div class="spec-lbl">풍판 높이</div>
                            <div class="spec-val"><span id="spPungpan">0</span><span class="spec-unit">mm</span></div>
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
                <div class="sb-section sb-collapsed admin-only" style="display:none">
                    <div class="sb-section-title">부재 목록 <small>폭×두께×길이</small></div>

                    <div class="slat-group">
                        <div class="slat-group-title">울거미</div>
                        <div class="diag-list">
                            <div class="slat-row">
                                <span class="slat-len" id="spFrVLen">—</span>
                                <span class="slat-cnt" id="spFrVCnt">2개</span>
                            </div>
                            <div class="slat-row">
                                <span class="slat-len" id="spFrHLen">—</span>
                                <span class="slat-cnt" id="spFrHCnt">2개</span>
                            </div>
                        </div>
                    </div>

                    <div class="slat-group" id="pungpanMaterialGroup" style="display:none;">
                        <div class="slat-group-title">풍판 <span class="slat-count-badge">1개</span></div>
                        <div class="slat-row">
                            <span class="slat-len" id="spPpLen">—</span>
                        </div>
                    </div>

                    <div class="slat-group">
                        <div class="slat-group-title">가로살 · 세로살</div>
                        <div class="diag-list">
                            <div class="slat-row">
                                <span class="slat-len" id="spHSlatLen">—</span>
                                <span class="slat-cnt" id="spHSlatCnt">—</span>
                            </div>
                            <div class="slat-row">
                                <span class="slat-len" id="spVSlatLen">—</span>
                                <span class="slat-cnt" id="spVSlatCnt">—</span>
                            </div>
                        </div>
                    </div>

                    <div class="slat-group">
                        <div class="slat-group-title">사선살</div>
                        <div id="spDiagList" class="diag-list"></div>
                    </div>

                    <div class="slat-group">
                        <div class="slat-group-title">문틀</div>
                        <div class="diag-list">
                            <div class="slat-row">
                                <span class="slat-len" id="spMtVLen">—</span>
                                <span class="slat-cnt">2개</span>
                            </div>
                            <div class="slat-row">
                                <span class="slat-len" id="spMtHLen">—</span>
                                <span class="slat-cnt">2개</span>
                            </div>
                        </div>
                    </div>
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

            <!-- 캔버스 컨트롤 버튼 -->
            <div class="canvas-controls">
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
                <!-- 핸드(팬) -->
                <button class="cv-btn" id="btnPan" title="이동">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 11V6a2 2 0 0 0-4 0v5"/>
                        <path d="M14 10V4a2 2 0 0 0-4 0v6"/>
                        <path d="M10 10.5V6a2 2 0 0 0-4 0v8.5"/>
                        <path d="M18 8a2 2 0 0 1 4 0v6a8 8 0 0 1-8 8h-2c-2.76 0-4.5-.86-5.99-2.34l-3.6-3.6a2 2 0 0 1 2.83-2.82L7 15"/>
                    </svg>
                </button>
                <div class="cv-sep"></div>
                <!-- 변형 -->
                <button class="cv-btn" id="btnScale" title="스케일/이동/변형">
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
                        <circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/>
                        <line x1="20" y1="4" x2="8.12" y2="15.88"/>
                        <line x1="14.47" y1="14.48" x2="20" y2="20"/>
                        <line x1="8.12" y1="8.12" x2="12" y2="12"/>
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
                <div class="cv-sep"></div>
                <button class="cv-btn" id="btnShapeSelect" title="선택&#10;살 클릭 → 색상·삭제&#10;도형 클릭 → 이동·크기조절·회전">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 3l14 9-7 1-4 7L5 3z"/>
                    </svg>
                </button>
                <button class="cv-btn" id="btnShapeCircle" title="원 그리기&#10;클릭 → 원 배치">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <circle cx="12" cy="12" r="9"/>
                    </svg>
                </button>
                <button class="cv-btn" id="btnShapeLine" title="선 그리기&#10;시작점 클릭 → 끝점 클릭">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <line x1="5" y1="19" x2="19" y2="5"/>
                    </svg>
                </button>
                <button class="cv-btn" id="btnShapeRect" title="사각형 그리기&#10;클릭 → 사각형 배치">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                    </svg>
                </button>
                <button class="cv-btn" id="btnShapeText" title="텍스트 추가&#10;클릭 → 텍스트 입력">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <line x1="4" y1="6" x2="20" y2="6"/><line x1="12" y1="6" x2="12" y2="20"/>
                    </svg>
                </button>
                <button class="cv-btn" id="btnShapeClear" title="도형 모두 삭제">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
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
                    <select id="patternCategory" class="title-group-btn" title="패턴 분류" style="padding:0 6px;font-size:var(--fs-12);cursor:pointer;border:none;background:transparent;color:var(--text-muted);font-weight:600;height:28px;">
                        <option value="">분류 없음</option>
                        <?php foreach ($patternCategories as $pc): ?>
                        <option value="<?= (int)$pc['id'] ?>"><?= htmlspecialchars($pc['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="title-group-btn save-btn" id="btnSave" title="저장">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 4H17L20 7V20H5V4Z"/><path d="M8 4V9H15V4"/><path d="M9 15H15"/>
                        </svg>
                        <span>저장</span>
                    </button>
                </div>
            </div>

            <canvas id="doorCanvas"></canvas>
            <div id="konvaStageContainer" style="position:absolute;top:0;left:0;pointer-events:none;"></div>
            <canvas id="rulerCanvas" style="position:absolute;top:0;left:0;pointer-events:none;z-index:1;"></canvas>

            <div id="konvaShapePanel" style="display:none;position:absolute;top:44px;left:50%;transform:translateX(-50%);
                display:none;align-items:center;gap:8px;background:rgba(var(--bg-rgb), 0.97);
                border:1px solid var(--border);border-radius:8px;padding:6px 12px;
                box-shadow:0 2px 10px rgba(var(--text-rgb), 0.13);z-index:200;white-space:nowrap;">
                <span style="font-size:11px;color:var(--text-muted);font-weight:600;">도형</span>
                <label style="font-size:11px;color:var(--text-muted);">선</label>
                <input type="color" id="konvaStrokeColor" value="#e03030" style="width:26px;height:22px;border:none;padding:0;cursor:pointer;border-radius:4px;">
                <label style="font-size:11px;color:var(--text-muted);">채우기</label>
                <input type="color" id="konvaFillColor" value="#e03030" style="width:26px;height:22px;border:none;padding:0;cursor:pointer;border-radius:4px;">
                <label style="font-size:11px;color:var(--text-muted);">두께</label>
                <select id="konvaStrokeWidth" style="height:22px;font-size:11px;border:1px solid var(--border);border-radius:4px;padding:0 4px;">
                    <option value="1">1</option><option value="2" selected>2</option>
                    <option value="3">3</option><option value="5">5</option><option value="8">8</option>
                </select>
                <label style="font-size:11px;color:var(--text-muted);">투명도</label>
                <input type="range" id="konvaOpacity" min="10" max="100" value="100" style="width:60px;">
            </div>

            <div id="slatSelPanel" style="display:none;position:absolute;top:44px;left:50%;transform:translateX(-50%);
                align-items:center;gap:8px;background:rgba(var(--bg-rgb), 0.97);
                border:1px solid var(--border);border-radius:8px;padding:6px 12px;
                box-shadow:0 2px 10px rgba(var(--text-rgb), 0.13);z-index:200;white-space:nowrap;">
                <span style="font-size:11px;color:var(--text-muted);font-weight:600;">살</span>
                <label style="font-size:11px;color:var(--text-muted);">색상</label>
                <input type="color" id="slatOverrideColor" value="#e03030" style="width:26px;height:22px;border:none;padding:0;cursor:pointer;border-radius:4px;">
                <button id="btnApplySlatColor" style="height:22px;padding:0 8px;font-size:11px;border:1px solid var(--accent);border-radius:4px;background:var(--accent);color:var(--bg);cursor:pointer;">적용</button>
                <button id="btnResetSlatColor" style="height:22px;padding:0 8px;font-size:11px;border:1px solid var(--border);border-radius:4px;background:var(--bg);cursor:pointer;">초기화</button>
                <button id="btnDeleteSelectedSlat" style="height:22px;padding:0 8px;font-size:11px;border:1px solid var(--danger);border-radius:4px;background:var(--bg);color:var(--danger);cursor:pointer;">삭제</button>
            </div>

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
                    <div class="sb-price-box">
                        <div class="sb-price-label">예상가격</div>
                        <div class="sb-price-amount"><span class="sb-price-start">–</span><span class="sb-price-end"></span></div>
                        <div class="sb-price-breakdown">
                            <div class="super-only" style="display:none">
                            <div class="sb-break-row"><span>문(창호) 목재</span><span id="spCostDoor">–</span></div>
                            <div class="sb-break-row"><span>문틀 목재</span><span id="spCostMuntol">–</span></div>
                            <div class="sb-break-row sb-break-sub sb-break-key"><span>목재비</span><span id="spWoodCost">–</span></div>
                            <div class="sb-break-divider"></div>
                            <div class="sb-break-row sb-break-key"><span>제작비 <small id="spCraftTime"></small></span><span id="spCraftCost">–</span></div>
                            <div class="sb-break-row sb-break-key"><span>부자재</span><span id="spHardwareCost">–</span></div>
                            <div class="sb-break-row sb-break-key"><span>마감</span><span id="spFinishCost">–</span></div>
                            <div class="sb-break-divider"></div>
                            <div class="sb-break-row sb-break-key"><span>간접비</span><span id="spOverheadCost">–</span></div>
                            <div class="sb-break-row sb-break-key"><span>이익</span><span id="spProfitCost">–</span></div>
                            <div class="sb-break-divider"></div>
                            <div class="sb-break-row sb-break-total sb-break-key"><span>판매가</span><span id="spTotalCost">–</span></div>
                            </div>
                            <div class="sb-lead-time sb-break-row" data-min-days="<?= (int)$cfg['min_days'] ?>"><span>최소 납기</span><span><strong><?= (int)$cfg['min_days'] ?></strong>일</span></div>
                            <div class="sb-price-note">※ 배송비·시공비 제외</div>
                            <div class="sb-price-disclaimer">※ 본 금액은 예상 견적입니다. 사용자 편집 내용을 검토한 후 최종 견적이 확정됩니다.</div>
                        </div>
                    </div>
                    <div style="display:flex;gap:6px;width:100%;">
                        <button class="hbtn hbtn-primary" id="btnOrder" style="flex:1;justify-content:center;width:100%;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                <path d="M7 3H14L19 8V20H7V3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                                <path d="M14 3V8H19" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                                <path d="M10 12L11.8 13.8L15 10.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M10 17H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            </svg>
                            견적요청
                        </button>
                    </div>
                </div>
                <div class="sb-section">
                    <div class="sb-section-title" style="cursor:default;">마감</div>
                    <div class="ctrl">
                        <select id="txtWood" class="sb-select">
                            <?php foreach (get_wood_options() as $w): ?>
                            <option value="<?= htmlspecialchars($w['name'], ENT_QUOTES) ?>"
                                data-price="<?= (int)$w['unit_price'] ?>"
                                data-weight="<?= (float)$w['weight'] ?>">
                                <?= htmlspecialchars($w['name'], ENT_QUOTES) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="ctrl">
                        <select id="txtFinish" class="sb-select">
                            <option value="" data-price="0" data-time="0" data-coats="0">마감 없음</option>
                            <?php foreach (get_finish_options() as $f): ?>
                            <option value="<?= htmlspecialchars($f['name'], ENT_QUOTES) ?>"
                                data-price="<?= (int)$f['unit_price'] ?>"
                                data-time="<?= (int)$f['work_time_min'] ?>"
                                data-coats="<?= (int)$f['coat_count'] ?>">
                                <?= htmlspecialchars($f['name'], ENT_QUOTES) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="ctrl">
                        <select id="txtHardware" class="sb-select">
                            <option value="" data-price="0">부자재 없음</option>
                            <?php foreach (get_hardware_options() as $h): ?>
                            <option value="<?= htmlspecialchars($h['name'], ENT_QUOTES) ?>"
                                data-price="<?= (int)$h['unit_price'] ?>">
                                <?= htmlspecialchars($h['name'], ENT_QUOTES) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <hr class="sb-divider">
                    <div style="display:flex;flex-direction:column;gap:6px;margin-top:8px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;">
                            <span class="color-label">울거미 컬러</span>
                            <div class="color-picker-wrap">
                                <button class="color-preview-btn" id="framePreviewBtn">
                                    <span class="color-preview-dot" id="framePreviewDot"></span>
                                    <span id="framePreviewName">—</span>
                                </button>
                                <div class="color-popup" id="framePopup"></div>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;">
                            <span class="color-label">살 컬러</span>
                            <div class="color-picker-wrap">
                                <button class="color-preview-btn" id="slatPreviewBtn">
                                    <span class="color-preview-dot" id="slatPreviewDot"></span>
                                    <span id="slatPreviewName">—</span>
                                </button>
                                <div class="color-popup" id="slatPopup"></div>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;">
                            <span class="color-label">문틀 컬러</span>
                            <div class="color-preview-btn" style="gap:5px;">
                                <input type="color" id="muntolColorInput" value="#4a4a4a" style="width:14px;height:14px;padding:0;border:none;border-radius:3px;cursor:pointer;flex-shrink:0;">
                                <span id="muntolColorCode">#4a4a4a</span>
                            </div>
                        </div>
                    </div>
                    <?php /* 면칠하기 임시 비활성화 (복구: display:none 제거) */ ?>
                    <div style="margin-top:8px;display:flex;align-items:center;justify-content:space-between;display:none;">
                        <span class="color-label">면 컬러</span>
                        <div class="color-preview-btn" style="gap:5px;">
                            <input type="color" id="faceColorInput" value="#c8102e" style="width:14px;height:14px;padding:0;border:none;border-radius:3px;cursor:pointer;flex-shrink:0;">
                            <span id="faceColorCode">#c8102e</span>
                        </div>
                    </div>
                    <div style="display:flex;gap:6px;margin-top:6px;display:none;">
                        <button id="btnFacePaint" type="button" class="hbtn" style="flex:1;justify-content:center;font-size:11px;display:none;">면컬러 칠하기</button>
                        <button id="btnFaceClear" type="button" class="hbtn" style="flex-shrink:0;padding:0 8px;font-size:11px;display:none;width:auto;justify-content:center;">초기화</button>
                    </div>
                </div>
                <div class="sb-section">
                    <div class="sb-section-title" style="cursor:default;">렌더링</div>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <div style="display:flex;gap:6px;width:100%;">
                        <button class="rp-add-btn" id="btnAddThumb" style="flex:1;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                <polyline points="17 8 12 3 7 8" />
                                <line x1="12" y1="3" x2="12" y2="15" />
                            </svg>
                            배경 업로드
                        </button>
                        <button id="btnClearBg" style="display:none;flex-shrink:0;width:28px;height:28px;border:none;background:none;padding:0;cursor:pointer;color:var(--danger);align-items:center;justify-content:center;align-self:center;" title="배경 지우기">
                            <i class="bi bi-x-lg" style="font-size:13px;"></i>
                        </button>
                        </div>
                        <div class="rp-thumb-list" id="thumbList"></div>
                        <select id="aiPromptPreset" class="rp-prompt-select" onchange="if(this.value) document.getElementById('aiPrompt').value = this.value;">
                            <option value="">재질/조명 선택…</option>
                            <?php foreach ($renderPresets as $rp): ?>
                            <option value="<?= htmlspecialchars($rp['prompt_text']) ?>"><?= htmlspecialchars($rp['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <textarea id="aiPrompt" class="rp-prompt" placeholder="프리셋을 선택하거나 직접 입력하세요" rows="3"></textarea>
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
                    <div class="sb-section-title" style="cursor:default;">문양 삽입</div>
                    <div class="svg-insert-actions">
                        <button class="hbtn" onclick="openSvgLibraryPicker()">
                            <i class="bi bi-flower1"></i> 라이브러리
                        </button>
                        <button class="hbtn" onclick="document.getElementById('svgFileInput').click()">
                            <i class="bi bi-upload"></i> 업로드
                        </button>
                        <input type="file" id="svgFileInput" accept="image/svg+xml,.svg" style="display:none;" onchange="handleSvgFileUpload(this)">
                    </div>
                    <div id="svgInsertControls" style="display:none;">
                        <div class="svg-insert-row">
                            <label>크기</label>
                            <input type="range" id="svgInsertScale" min="10" max="300" step="1" value="100">
                        </div>
                        <div class="svg-insert-row">
                            <label>회전</label>
                            <input type="range" id="svgInsertRotation" min="0" max="360" step="1" value="0">
                        </div>
                        <div class="svg-insert-actions" style="margin-top:6px;">
                            <button type="button" class="hbtn" id="btnSvgInsertDuplicate"><i class="bi bi-copy"></i> 복사</button>
                            <button type="button" class="svg-insert-delete" id="btnSvgInsertDelete">삭제</button>
                        </div>
                    </div>
                </div>
                <div class="sb-section">
                    <div class="sb-section-title" style="cursor:default;">내보내기</div>
                    <div style="display:flex;gap:6px;width:100%;">
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

    <div id="svgPickerModal">
        <div class="svg-picker-box">
            <div class="svg-picker-head">
                <h3>문양 라이브러리</h3>
                <button class="svg-picker-close" onclick="closeSvgLibraryPicker()">&#x2715;</button>
            </div>
            <div id="svgPickerGrid"></div>
        </div>
    </div>

    <?php include __DIR__ . '/../../components/order_modal.php'; ?>

    <script src="https://unpkg.com/konva@9/konva.min.js"></script>
    <script src="/src/js/konva-overlay.js?v=<?= md5_file(__DIR__ . '/../../js/konva-overlay.js') ?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        window.__pmokOpenDrawing         = <?= isset($_POST['drawing'])    ? json_encode($_POST['drawing'],    JSON_UNESCAPED_UNICODE) : 'null' ?>;
        window.__pmokCollectionDrawingId = <?= isset($_GET['drawing_id']) ? (int)$_GET['drawing_id']          : 'null' ?>;
        window.__pmokColorGroups         = <?= json_encode(get_color_groups(), JSON_UNESCAPED_UNICODE) ?>;
        window.__pmokEngineLayout        = <?= json_encode(['gap' => (float)$cfg['gap'], 'basePadding' => (float)$cfg['basePadding'], 'frameGap' => (float)$cfg['frameGap'], 'frameThick' => (float)$cfg['frameThick'], 'craftTime' => (float)$costCfg['craft_time'], 'ulgeomiTime' => (float)$costCfg['ulgeomi_time'], 'trimTime' => (float)$costCfg['trim_time'], 'muntolTime' => (float)$costCfg['muntol_time'], 'minWorkHours' => (float)$cfg['min_work_hours']], JSON_UNESCAPED_UNICODE) ?>;
        window.__pmokCostConfig           = <?= json_encode(array_merge($costCfg, ['slatW' => (float)$cfg['slatW'], 'slatThick' => (float)$cfg['slat'], 'ulgeomiW' => (float)$cfg['ulgeomiW']]), JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <script src="/src/js/drawing-sync.js?v=<?= md5_file(__DIR__ . '/../../js/drawing-sync.js') ?>"></script>
    <script src="/src/js/engine-common.js?v=<?= md5_file(__DIR__ . '/../../js/engine-common.js') ?>"></script>
    <script src="/src/js/diamond.js?v=<?= md5_file(__DIR__ . '/../../js/diamond.js') ?>"></script>
</body>

</html>