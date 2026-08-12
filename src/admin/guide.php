<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/admin_guard.php';
require_admin_role('s');
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
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>
    <style>
        #guideBodyEditor { height:400px; background:var(--bg); font-size:13px; }
        .ql-editor { font-family:'Pretendard','Noto Sans KR','Apple SD Gothic Neo','Malgun Gothic',sans-serif; color: var(--text); }
        .ql-toolbar { border-color:var(--border) !important; border-radius:var(--r-sm) var(--r-sm) 0 0; }
        .ql-container { border-color:var(--border) !important; border-radius:0 0 var(--r-sm) var(--r-sm); font-family:inherit; }
        #guideBodySource { display:none; width:100%; height:400px; padding:10px; border:1px solid var(--border); border-radius:var(--r-sm); background:var(--bg); font-family:'SF Mono','Consolas',monospace; font-size:12px; color:var(--text); resize:vertical; }
        .guide-editor-toggle { border:none; background:none; color:var(--accent); font-size:12px; font-weight:600; cursor:pointer; text-decoration:underline; padding:0; margin-bottom:6px; }
        .guide-cat-row td { background:var(--bg); font-size:12px; font-weight:700; color:var(--text); padding:8px 10px; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>
<?php include __DIR__ . '/../components/admin_sidenav.php'; ?>

<div class="db-page" id="guideAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="guidePage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>가이드 관리</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-book-half me-2"></i>가이드 관리</h1>
    </div>
    <p style="font-size:var(--fs-13);color:var(--text);margin:-8px 0 16px;">
        /guide/ 아래 각 페이지의 제목·본문을 수정합니다. 메뉴 구조·순서는 이 페이지에서 바꿀 수 없습니다(신규 페이지 추가는 개발 작업 필요).
    </p>

    <div class="adm-table-wrap">
        <table id="guideTable">
            <thead>
                <tr>
                    <th>제목</th>
                    <th style="width:160px;">slug</th>
                    <th style="width:160px;">최근 수정</th>
                    <th style="width:100px;"></th>
                </tr>
            </thead>
            <tbody id="guideBody"></tbody>
        </table>
    </div>
</div>

<!-- 편집 모달 -->
<div class="adm-modal-overlay" id="guideModalOverlay">
    <div class="adm-modal" style="max-width:900px;width:92vw;">
        <div class="adm-modal-head">
            <h3>가이드 페이지 수정 — <span id="guideSlugLabel" style="color:var(--accent);"></span></h3>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <input type="hidden" id="guideSlug">
            <div class="adm-mfield">
                <label>제목</label>
                <input id="guideTitle" type="text" maxlength="200">
            </div>
            <div class="adm-mfield">
                <label>본문</label>
                <button type="button" class="guide-editor-toggle" id="guideEditorToggleBtn" onclick="toggleEditorMode()">HTML 소스로 보기</button>
                <div id="guideBodyEditor"></div>
                <textarea id="guideBodySource" spellcheck="false"></textarea>
                <input type="file" id="guideContentImgFile" accept="image/*" style="display:none;">
                <p style="font-size:11px;color:var(--text);margin:6px 0 0;">
                    guide-tip/guide-note/guide-warn/guide-table 같은 기존 스타일 블록은 서식 도구로 새로 만들 수 없습니다 — HTML 소스 모드에서 직접 마크업을 편집하세요.
                </p>
            </div>
        </div>
        <div class="adm-modal-foot">
            <span id="guideSaveStatus" class="pc-status" style="margin-right:auto;"></span>
            <button class="adm-btn-cancel" onclick="closeModal()">닫기</button>
            <button class="adm-btn-save" onclick="saveArticle()">저장</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script src="/src/js/admin/guide.js?v=<?= md5_file(__DIR__ . '/../js/admin/guide.js') ?>"></script>
</body>
</html>
