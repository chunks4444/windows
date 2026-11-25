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
    <?php css_tag('/src/css/admin.css'); ?>
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>
    <style>
        .nt-title-cell { max-width:420px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .nt-state-wait { background:var(--accent-tint); color:var(--accent); border-radius:99px; padding:2px 8px; font-size:12px; font-weight:700; }
        .ql-toolbar { border-color:var(--border) !important; border-radius:var(--r-sm) var(--r-sm) 0 0; }
        .ql-container { border-color:var(--border) !important; border-radius:0 0 var(--r-sm) var(--r-sm); font-family:inherit; }
        #ntEditor, #ntEditorEn { height:200px; background:var(--bg); font-size:13px; }
        .nt-row { display:flex; gap:16px; flex-wrap:wrap; align-items:center; }
        .nt-row label { display:flex; align-items:center; gap:6px; font-size:13px; font-weight:500; cursor:pointer; user-select:none; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>
<?php include __DIR__ . '/../components/admin_sidenav.php'; ?>

<div class="db-page" id="ntAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="ntPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>공지 글 관리</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-card-text me-2"></i>공지 글 관리</h1>
        <div style="display:flex;align-items:center;gap:12px;">
            <a href="/company/notice/" target="_blank" rel="noopener" class="adm-withdraw-btn" style="text-decoration:none;">공개 페이지 보기</a>
            <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="openModal()">
                <i class="bi bi-plus-lg"></i> 새 공지
            </button>
        </div>
    </div>
    <p style="font-size:12px;color:var(--text);margin:-8px 0 16px;">게시일이 미래이면 그 시각까지 공개되지 않습니다(예약). 고정한 공지는 목록 맨 위에 모입니다. 상단 한 줄 배너는 "공지 배너" 메뉴에서 따로 관리합니다.</p>

    <div class="adm-table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width:44px;">고정</th>
                    <th>제목</th>
                    <th style="width:150px;">게시일</th>
                    <th style="width:70px;">상태</th>
                    <th style="width:240px;"></th>
                </tr>
            </thead>
            <tbody id="ntBody"></tbody>
        </table>
    </div>
</div>

<div class="adm-modal-overlay" id="ntModalOverlay">
    <div class="adm-modal" style="max-width:680px;">
        <div class="adm-modal-head">
            <h3 id="ntModalTitle">새 공지</h3>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <input type="hidden" id="ntId">
            <div class="adm-mfield nt-row">
                <label>말머리 <input id="ntLabel" type="text" maxlength="30" placeholder="예: 함께 짤 사람" style="margin-left:6px;width:200px;"></label>
                <label>말머리 (영문) <input id="ntLabelEn" type="text" maxlength="30" placeholder="예: Join us" style="margin-left:6px;width:160px;"></label>
            </div>
            <div class="adm-mfield">
                <label>제목</label>
                <input id="ntTitle" type="text" placeholder="예: 11월 공방 휴무 안내" maxlength="200">
            </div>
            <div class="adm-mfield">
                <label>내용</label>
                <div id="ntEditor"></div>
            </div>
            <div class="adm-mfield">
                <label>제목 (영문)</label>
                <input id="ntTitleEn" type="text" placeholder="비워두면 영문 사이트에서도 한글 제목이 보입니다" maxlength="200">
            </div>
            <div class="adm-mfield">
                <label>내용 (영문)</label>
                <div id="ntEditorEn"></div>
            </div>
            <div class="adm-mfield">
                <label>글 끝 버튼 (선택)</label>
                <div class="nt-row" style="gap:8px;">
                    <input id="ntCtaText" type="text" maxlength="60" placeholder="버튼 문구 예: 이메일로 인사 보내기" style="flex:1;min-width:200px;">
                    <input id="ntCtaTextEn" type="text" maxlength="60" placeholder="버튼 문구 (영문)" style="flex:1;min-width:160px;">
                </div>
                <input id="ntCtaUrl" type="text" maxlength="300" placeholder="버튼 주소 예: #contact (문의 메일폼 열기) 또는 https://... 또는 /company/" style="margin-top:6px;">
            </div>
            <div class="adm-mfield nt-row">
                <label>게시일 <input id="ntPublished" type="datetime-local" style="margin-left:6px;"></label>
                <label><input type="checkbox" id="ntPinned" class="form-check-input" style="margin:0;"> 목록 위에 고정</label>
                <label><input type="checkbox" id="ntActive" class="form-check-input" style="margin:0;"> 공개</label>
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" onclick="saveNotice()">저장</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script src="/src/js/admin/notices.js?v=<?= md5_file(__DIR__ . '/../js/admin/notices.js') ?>"></script>
</body>
</html>
