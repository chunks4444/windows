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
        .blog-thumb { width:80px; height:60px; object-fit:cover; border-radius:4px; background:var(--input-bg); }
        .blog-thumb-empty { width:80px; height:60px; border-radius:4px; background:var(--input-bg); display:flex; align-items:center; justify-content:center; color:var(--text-3); font-size:18px; }
        .blog-img-preview { width:100%; max-height:220px; object-fit:cover; border-radius:8px; background:var(--input-bg); display:none; margin-bottom:8px; }
        .blog-img-preview.show { display:block; }
        .blog-upload-label { display:block; padding:10px; border:1.5px dashed var(--border-md); border-radius:8px; text-align:center; cursor:pointer; color:var(--text-3); font-size:13px; margin-bottom:6px; }
        .blog-upload-label:hover { border-color:var(--teal); color:var(--teal); }
        .blog-textarea { resize:vertical; padding:8px 10px; border:1px solid var(--border-md); border-radius:var(--r-sm); background:var(--input-bg); font-family:inherit; font-size:13px; color:var(--text-1); outline:none; width:100%; }
        #postContentEditor { height:320px; background:#fff; font-size:13px; }
        .ql-editor { font-family:'Noto Sans KR','Inter',-apple-system,sans-serif; color: var(--text-1); }
        .ql-editor strong, .ql-editor b { font-weight: 600; }
        .ql-editor h1, .ql-editor h2, .ql-editor h3, .ql-editor h4 { font-weight: 600; }
        .ql-toolbar { border-color:var(--border-md) !important; border-radius:var(--r-sm) var(--r-sm) 0 0; }
        .ql-container { border-color:var(--border-md) !important; border-radius:0 0 var(--r-sm) var(--r-sm); font-family:inherit; }
        .adm-modal-fullscreen-btn { background:none; border:none; cursor:pointer; color:var(--text-3); font-size:16px; line-height:1; padding:0; margin-right:14px; }
        .adm-modal-fullscreen-btn:hover { color:var(--text-1); }
        #blogModalOverlay.fullscreen-active { padding:0; }
        #blogModalOverlay.fullscreen-active .adm-modal { max-width:100%; width:100%; height:100%; max-height:100%; border-radius:0; }
        #blogModalOverlay.fullscreen-active #postContentEditor { height:calc(100vh - 220px); }
        .blog-info-section { border:1px solid var(--border-md); border-radius:var(--r-sm); margin-bottom:14px; }
        .blog-info-toggle { width:100%; display:flex; align-items:center; gap:8px; background:var(--input-bg); border:none; padding:10px 12px; font-size:12px; font-weight:700; color:var(--text-2); letter-spacing:0.04em; text-transform:uppercase; cursor:pointer; border-radius:var(--r-sm); }
        .blog-info-toggle i { transition:transform .2s ease; }
        .blog-info-body { padding:14px 12px 2px; }
        .blog-info-section.collapsed .blog-info-toggle { border-radius:var(--r-sm); }
        .blog-info-section.collapsed .blog-info-toggle i { transform:rotate(-90deg); }
        .blog-info-section.collapsed .blog-info-body { display:none; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

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

    <p style="font-size:12px;color:var(--text-3);margin:-8px 0 16px;">행을 드래그해 순서를 변경할 수 있습니다.</p>

    <div class="adm-table-wrap">
        <table id="blogTable">
            <thead>
                <tr>
                    <th style="width:32px;"></th>
                    <th style="width:96px;">썸네일</th>
                    <th>제목</th>
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
    <div class="adm-modal" style="max-width:1280px;width:92vw;">
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
                        <label>타이틀 이미지 <span style="font-size:11px;color:var(--text-3);font-weight:400;">(상세페이지 제목 하단 + 목록 썸네일)</span></label>
                        <img id="postImgPreview" class="blog-img-preview" src="" alt="">
                        <label class="blog-upload-label" for="postImgFile">
                            <i class="bi bi-upload"></i> 이미지 업로드
                        </label>
                        <input type="file" id="postImgFile" accept="image/*" style="display:none;" onchange="previewImage(this)">
                        <input id="postThumbUrl" type="text" placeholder="또는 https://... URL 직접 입력"
                            style="height:38px;padding:0 10px;border:1px solid var(--border-md);border-radius:var(--r-sm);background:var(--input-bg);font-family:inherit;font-size:13px;color:var(--text-1);outline:none;width:100%;">
                    </div>
                    <div class="adm-mfield">
                        <label>제목</label>
                        <input id="postTitle" type="text" placeholder="예: 한옥 살창, 사계절을 담다" maxlength="150">
                    </div>
                    <div class="adm-mfield">
                        <label>요약 <span style="font-size:11px;color:var(--text-3);font-weight:400;">(목록 카드 / 검색결과 설명)</span></label>
                        <textarea id="postSummary" class="blog-textarea" rows="2" maxlength="300" placeholder="목록과 검색엔진에 노출될 한 줄 요약"></textarea>
                    </div>
                    <div class="adm-mfield">
                        <label>하단 CTA 문구 <span style="font-size:11px;color:var(--text-3);font-weight:400;">(본문 하단 컬렉션 유도 문구, 비워두면 기본 문구 사용)</span></label>
                        <input id="postCtaText" type="text" maxlength="200" placeholder="예: 평목 스튜디오의 다양한 패턴 디자인 보러가기">
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
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" onclick="savePost()">저장</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script src="/src/js/admin/blog.js"></script>
</body>
</html>
