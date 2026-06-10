<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/src/css/dashboard.css">
    <link rel="stylesheet" href="/src/css/users.css">
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>
    
    <link rel="stylesheet" href="/src/css/admin/hero_slides.css">
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="hsAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="hsPage" style="display:none;">
    <div class="db-header">
        <h1 class="db-title">히어로 슬라이드 관리</h1>
        <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="openModal()">
            <i class="bi bi-plus-lg"></i> 추가
        </button>
    </div>
    <p style="font-size:12px;color:var(--text-3);margin:-8px 0 16px;">행을 드래그해 순서를 변경할 수 있습니다.</p>

    <div class="adm-table-wrap">
        <table id="hsTable">
            <thead>
                <tr>
                    <th style="width:32px;"></th>
                    <th style="width:104px;">이미지</th>
                    <th>제목</th>
                    <th>설명</th>
                    <th style="width:72px;">상태</th>
                    <th style="width:160px;"></th>
                </tr>
            </thead>
            <tbody id="hsBody"></tbody>
        </table>
    </div>
</div>

<!-- 편집 모달 -->
<div class="adm-modal-overlay" id="hsModalOverlay">
    <div class="adm-modal" style="max-width:520px;">
        <div class="adm-modal-head">
            <h3 id="hsModalTitle">슬라이드 추가</h3>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <input type="hidden" id="hsId">
            <div class="adm-mfield">
                <label>제목</label>
                <input id="hsTitle" type="text" placeholder="예: 빛과 바람의 길을 설계하세요" maxlength="120">
            </div>
            <div class="adm-mfield">
                <label>설명</label>
                <input id="hsSubtitle" type="text" placeholder="예: 나만의 창호를 브라우저에서 직접 디자인합니다" maxlength="255">
            </div>
            <div class="adm-mfield">
                <label>이미지</label>
                <img id="hsImgPreview" class="hs-img-preview" src="" alt="">
                <label class="hs-upload-label" for="hsImgFile">
                    <i class="bi bi-upload"></i> 이미지 업로드
                </label>
                <input type="file" id="hsImgFile" accept="image/*" style="display:none;" onchange="previewImage(this)">
                <input id="hsImageUrl" type="text" placeholder="또는 https://... URL 직접 입력"
                    style="height:38px;padding:0 10px;border:1px solid var(--border-md);border-radius:var(--r-sm);background:var(--input-bg);font-family:inherit;font-size:13px;color:var(--text-1);outline:none;width:100%;">
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" onclick="saveSlide()">저장</button>
        </div>
    </div>
</div>

<script src="/src/js/admin/hero_slides.js"></script>
</body>
</html>
