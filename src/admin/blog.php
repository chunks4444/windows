<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/admin_guard.php';
require_admin_role('s');
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/engine_icons.php';
try {
    $blogSeriesList = db()->query('SELECT id, name FROM blog_series ORDER BY sort_order, id')->fetchAll();
} catch (Throwable $e) {
    $blogSeriesList = [];
}
$engineOptions = [];
foreach (ENGINE_LABELS as $engineKey => $engineLabel) {
    $engineOptions[$engineKey] = $engineLabel . '(' . ucfirst($engineKey) . ')';
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../lib/meta.php'; ?>
    <?php meta_tags(); ?>
<?php css_tag('/src/css/dashboard.css'); ?>
    <?php css_tag('/src/css/users.css'); ?>
    <?php css_tag('/src/css/stats.css'); ?>
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>
    <style>
        .blog-thumb { width:80px; height:60px; object-fit:cover; border-radius:4px; background:var(--bg); }
        .blog-thumb-empty { width:80px; height:60px; border-radius:4px; background:var(--bg); display:flex; align-items:center; justify-content:center; color: var(--text); font-size:18px; }
        .blog-img-preview { width:100%; max-height:220px; object-fit:cover; border-radius:8px; background:var(--bg); display:none; margin-bottom:8px; }
        .blog-img-preview.show { display:block; }
        .blog-upload-label { display:block; padding:10px; border:1.5px dashed var(--border); border-radius:8px; text-align:center; cursor:pointer; color: var(--text); font-size:13px; margin-bottom:6px; }
        .blog-upload-label:hover { border-color:var(--accent); color:var(--accent); }
        .blog-textarea { resize:vertical; padding:8px 10px; border:1px solid var(--border); border-radius:var(--r-sm); background:var(--bg); font-family:inherit; font-size:13px; color:var(--text); outline:none; width:100%; }
        #postContentEditor { height:320px; background:var(--bg); font-size:13px; }
        .ql-editor { font-family:'Noto Sans KR','Inter',-apple-system,sans-serif; color: var(--text); }
        .ql-editor strong, .ql-editor b { font-weight: 600; }
        .ql-editor h1, .ql-editor h2, .ql-editor h3, .ql-editor h4 { font-weight: 600; }
        .ql-editor td { box-sizing: border-box; }
        .ql-toolbar { border-color:var(--border) !important; border-radius:var(--r-sm) var(--r-sm) 0 0; }
        .ql-container { border-color:var(--border) !important; border-radius:0 0 var(--r-sm) var(--r-sm); font-family:inherit; }
        .adm-modal-fullscreen-btn { background:none; border:none; cursor:pointer; color: var(--text); font-size:16px; line-height:1; padding:0; margin-right:14px; }
        .adm-modal-fullscreen-btn:hover { color:var(--text); }
        #blogModalOverlay.fullscreen-active { padding:0; }
        #blogModalOverlay.fullscreen-active .adm-modal { max-width:100%; width:100%; height:100%; max-height:100%; border-radius:0; }
        #blogModalOverlay.fullscreen-active #postContentEditor { height:calc(100vh - 220px); }
        .blog-info-section { border:1px solid var(--border); border-radius:var(--r-sm); margin-bottom:14px; }
        .blog-info-toggle { width:100%; display:flex; align-items:center; gap:8px; background:var(--bg); border:none; padding:10px 12px; font-size:12px; font-weight:700; color: var(--text); letter-spacing:0.04em; text-transform:uppercase; cursor:pointer; border-radius:var(--r-sm); }
        .blog-info-toggle i { transition:transform .2s ease; }
        .blog-info-body { padding:14px 12px 2px; }
        .blog-info-section.collapsed .blog-info-toggle { border-radius:var(--r-sm); }
        .blog-info-section.collapsed .blog-info-toggle i { transform:rotate(-90deg); }
        .blog-info-section.collapsed .blog-info-body { display:none; }
        .blog-series-manage-btn { border:none; background:none; color:var(--accent); font-size:12px; font-weight:700; cursor:pointer; text-decoration:underline; padding:0; margin-left:6px; }
        .pc-table { width:100%; border-collapse:collapse; font-size:13px; }
        .pc-table th { background:var(--bg); padding:8px 10px; text-align:left; font-weight:600; color: var(--text); border-bottom:2px solid var(--border); }
        .pc-table td { padding:6px 10px; border-bottom:1px solid var(--border); vertical-align:middle; }
        .pc-name-input { border:1px solid var(--border); border-radius:5px; padding:4px 8px; font-size:13px; width:100%; }
        .pc-sort-input { border:1px solid var(--border); border-radius:5px; padding:4px 6px; font-size:13px; width:52px; text-align:center; }
        .pc-btn { border:none; border-radius:5px; padding:4px 10px; font-size:12px; font-weight:600; cursor:pointer; }
        .pc-btn-save { background:var(--accent); color:var(--bg); } .pc-btn-save:hover { opacity:.85; }
        .pc-btn-del  { background:var(--bg); color:var(--danger); }    .pc-btn-del:hover  { background:var(--danger-tint); }
        .pc-status { font-size:12px; } .pc-status.ok { color:var(--accent); } .pc-status.err { color:var(--danger); }
        .pc-add-row { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
        .pc-add-row input { border:1px solid var(--border); border-radius:6px; padding:6px 10px; font-size:13px; }
        .pc-add-btn { background:var(--accent); color:var(--bg); border:none; border-radius:6px; padding:6px 18px; font-size:13px; font-weight:600; cursor:pointer; }
        .ql-table-grid-popup { position:fixed; z-index:2000; background:var(--bg-1,var(--bg)); border:1px solid var(--border); border-radius:8px; padding:10px; box-shadow:0 6px 20px rgba(0,0,0,.15); }
        .ql-table-grid { display:grid; grid-template-columns:repeat(8, 16px); grid-template-rows:repeat(6, 16px); gap:2px; }
        .ql-table-grid-cell { width:16px; height:16px; border:1px solid var(--border); background:var(--bg); cursor:pointer; }
        .ql-table-grid-cell.active { background:var(--accent); border-color:var(--accent); }
        .ql-table-grid-label { margin-top:6px; font-size:12px; color:var(--text); text-align:center; white-space:nowrap; }
        .ql-table-float-toolbar { position:fixed; z-index:2000; display:none; gap:4px; background:var(--bg-1,var(--bg)); border:1px solid var(--border); border-radius:8px; padding:5px; box-shadow:0 6px 20px rgba(0,0,0,.15); }
        .ql-table-float-toolbar button { border:none; background:var(--bg); color:var(--text); border-radius:5px; padding:5px 9px; font-size:12px; white-space:nowrap; cursor:pointer; }
        .ql-table-float-toolbar button:hover { background:var(--accent); color:var(--bg); }
        .ql-table-col-handle { position:fixed; z-index:1900; width:6px; cursor:col-resize; background:transparent; }
        .ql-table-col-handle:hover { background:var(--accent); opacity:.5; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>
<?php include __DIR__ . '/../components/admin_sidenav.php'; ?>

<div class="db-page" id="blogAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="blogPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>블로그 관리</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-journal-text me-2"></i>블로그 관리</h1>
        <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="openModal()">
            <i class="bi bi-plus-lg"></i> 추가
        </button>
    </div>

    <p style="font-size:12px;color:var(--text);margin:-8px 0 16px;">행을 드래그해 순서를 변경할 수 있습니다.</p>

    <div class="st-panel" style="margin-bottom:16px;">
        <div class="st-panel-head">
            <span class="st-panel-title">블로그 일별 조회수</span>
            <span style="font-size:12px;color:var(--text-muted);">총 조회수 <strong id="blogTotalViews" style="font-size:16px;color:var(--text);">—</strong></span>
        </div>
        <div class="st-panel-body">
            <div class="st-chart-wrap"><canvas id="blogViewTrendChart"></canvas></div>
        </div>
    </div>

    <div class="adm-table-wrap">
        <table id="blogTable">
            <thead>
                <tr>
                    <th style="width:32px;"></th>
                    <th style="width:96px;">썸네일</th>
                    <th>제목</th>
                    <th style="width:110px;">시리즈</th>
                    <th>요약</th>
                    <th style="width:64px;">조회수</th>
                    <th style="width:72px;">상태</th>
                    <th style="width:160px;"></th>
                </tr>
            </thead>
            <tbody id="blogBody"></tbody>
        </table>
    </div>
</div>

<!-- 편집 모달 -->
<div class="adm-modal-overlay" id="blogModalOverlay">
    <div class="adm-modal" style="max-width:1024px;width:92vw;">
        <div class="adm-modal-head">
            <h3 id="blogModalTitle">글 추가</h3>
            <div style="display:flex;align-items:center;">
                <button class="adm-modal-fullscreen-btn" id="blogModalFullscreenBtn" onclick="toggleModalFullscreen()" title="전체화면">
                    <i class="bi bi-arrows-fullscreen"></i>
                </button>
                <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
            </div>
        </div>
        <div class="adm-modal-body">
            <input type="hidden" id="postId">
            <div class="blog-info-section" id="blogInfoSection">
                <button type="button" class="blog-info-toggle" onclick="toggleInfoSection()">
                    <i class="bi bi-chevron-down" id="blogInfoToggleIcon"></i> 제목 / 이미지 / 요약
                </button>
                <div class="blog-info-body" id="blogInfoBody">
                    <div class="adm-mfield">
                        <label>타이틀 이미지 <span style="font-size:11px;color: var(--text);font-weight:400;">(상세페이지 제목 하단 + 목록 썸네일)</span></label>
                        <img id="postImgPreview" class="blog-img-preview" src="" alt="">
                        <div id="postImgWarn" style="display:none;font-size:12px;color:#c00;margin:4px 0;">
                            ⚠️ 원본 이미지 폭이 1200px보다 작아 확대되었습니다. 구글 검색에 큰 썸네일로 노출되려면 가로 1200px 이상 원본을 권장합니다.
                        </div>
                        <label class="blog-upload-label" for="postImgFile">
                            <i class="bi bi-upload"></i> 이미지 업로드
                        </label>
                        <input type="file" id="postImgFile" accept="image/*" style="display:none;" onchange="previewImage(this)">
                        <input id="postThumbUrl" type="text" placeholder="또는 https://... URL 직접 입력"
                            style="height:38px;padding:0 10px;border:1px solid var(--border);border-radius:var(--r-sm);background:var(--bg);font-family:inherit;font-size:13px;color:var(--text);outline:none;width:100%;">
                    </div>
                    <div class="adm-mfield">
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                            <input id="postIsFeatured" type="checkbox" style="width:auto;">
                            히어로 캐로셀 노출 <span style="font-size:11px;color: var(--text);font-weight:400;">(블로그 메인·홈 상단 캐로셀에 직접 선택해 노출, 날짜 무관)</span>
                        </label>
                    </div>
                    <div class="adm-mfield">
                        <label>제목 <span style="font-size:11px;color: var(--text);font-weight:400;">(- — " ' * : 문자는 검색엔진 연산자와 혼동될 수 있어 사용 불가)</span></label>
                        <input id="postTitle" type="text" placeholder="예: 한옥 살창, 사계절을 담다" maxlength="150">
                    </div>
                    <div class="adm-mfield">
                        <label>요약 <span style="font-size:11px;color: var(--text);font-weight:400;">(목록 카드 / 검색결과 설명)</span></label>
                        <textarea id="postSummary" class="blog-textarea" rows="2" maxlength="300" placeholder="목록과 검색엔진에 노출될 한 줄 요약"></textarea>
                    </div>
                    <div class="adm-mfield">
                        <label>하단 CTA 문구 <span style="font-size:11px;color: var(--text);font-weight:400;">(본문 하단 컬렉션 유도 문구, 비워두면 기본 문구 사용)</span></label>
                        <input id="postCtaText" type="text" maxlength="200" placeholder="예: 평목 스튜디오의 다양한 패턴 디자인 보러가기">
                    </div>
                    <div class="adm-mfield">
                        <label>출처 <span style="font-size:11px;color: var(--text);font-weight:400;">(이 글이 참고한 출처, 비워두면 본문에 노출 안 됨. 여러 개면 줄바꿈으로 구분 — 리스트로 노출됩니다)</span></label>
                        <textarea id="postSourceText" class="blog-textarea" rows="3" maxlength="500" placeholder="예: OOO 홈페이지&#10;OOO(2024), 저자명 등"></textarea>
                    </div>
                </div>
            </div>
            <div class="blog-info-section collapsed" id="blogSeriesSection">
                <button type="button" class="blog-info-toggle" onclick="toggleInfoSection('blogSeriesSection')">
                    <i class="bi bi-chevron-down"></i> 시리즈 / 엔진 연동 / 질문
                </button>
                <div class="blog-info-body" id="blogSeriesBody">
                    <div class="adm-mfield">
                        <label>시리즈 <a href="/src/admin/blog_series.php" target="_blank" rel="noopener" class="blog-series-manage-btn">시리즈 관리</a></label>
                        <select id="postSeriesId" style="width:100%;padding:8px 10px;border:1.5px solid var(--border);border-radius:8px;background:var(--bg-1);color:var(--text);font-size:14px;">
                            <option value="">— 없음 —</option>
                            <?php foreach ($blogSeriesList as $s): ?>
                            <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="adm-mfield">
                        <label>시리즈 내 순서</label>
                        <input id="postSeriesOrder" type="number" min="0" value="0" style="width:100px;">
                    </div>
                    <div class="adm-mfield">
                        <label>질문형 인덱스용 한 줄 질문 <span style="font-size:11px;color: var(--text);font-weight:400;">(예: 귀신은 왜 벽을 뚫지 않고 문으로 다니는가)</span></label>
                        <input id="postQuestion" type="text" maxlength="200" placeholder="비워두면 제목으로 대체됩니다">
                    </div>
                    <div class="adm-mfield">
                        <label>연관 엔진 <span style="font-size:11px;color: var(--text);font-weight:400;">(있으면 글에 "직접 만들어보기" 버튼, 엔진 페이지에 "이 살의 이야기" 링크가 뜸)</span></label>
                        <select id="postRelatedEngine" style="width:100%;padding:8px 10px;border:1.5px solid var(--border);border-radius:8px;background:var(--bg-1);color:var(--text);font-size:14px;">
                            <option value="">— 없음 —</option>
                            <?php foreach ($engineOptions as $key => $label): ?>
                            <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="adm-mfield">
                        <label>연관 대표 도면 <span style="font-size:11px;color: var(--text);font-weight:400;">(선택 — 컬렉션에 등록된 패턴 중에서만 고를 수 있습니다. 지정하면 그 도면을 바로 불러온 상태로 엔진이 열림)</span></label>
                        <select id="postRelatedDrawingId" style="width:100%;padding:8px 10px;border:1.5px solid var(--border);border-radius:8px;background:var(--bg-1);color:var(--text);font-size:14px;">
                            <option value="">— 없음 —</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="adm-mfield">
                <label>본문</label>
                <div id="postContentEditor"></div>
                <input type="file" id="postContentImgFile" accept="image/*" style="display:none;">
            </div>
        </div>
        <div class="adm-modal-foot">
            <span id="postSaveStatus" class="pc-status" style="margin-right:auto;"></span>
            <button class="adm-btn-cancel" onclick="closeModal()">닫기</button>
            <button class="adm-btn-save" onclick="savePost()">저장</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="/src/js/admin/blog.js?v=<?= md5_file(__DIR__ . '/../js/admin/blog.js') ?>"></script>
</body>
</html>
