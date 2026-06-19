<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../lib/meta.php'; ?>
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
        .ql-toolbar { border-color:var(--border-md) !important; border-radius:var(--r-sm) var(--r-sm) 0 0; }
        .ql-container { border-color:var(--border-md) !important; border-radius:0 0 var(--r-sm) var(--r-sm); font-family:inherit; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="blogAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="blogPage" style="display:none;">
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
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <input type="hidden" id="postId">
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
