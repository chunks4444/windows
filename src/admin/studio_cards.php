<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../lib/meta.php'; ?>
    <?php css_tag('/src/css/dashboard.css'); ?>
    <?php css_tag('/src/css/users.css'); ?>
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>
    <?php css_tag('/src/css/admin/space_cards.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="scAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="scPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>스튜디오 카드 관리</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-grid-1x2 me-2"></i>스튜디오 카드 관리</h1>
    </div>
    <p style="font-size:var(--fs-13);color:var(--text-3);margin:-8px 0 16px;">행을 드래그해 순서를 변경할 수 있습니다. 제목·설명·배경이미지를 수정할 수 있습니다.</p>

    <div class="adm-table-wrap">
        <table id="scTable">
            <thead>
                <tr>
                    <th style="width:32px;"></th>
                    <th style="width:80px;">이미지</th>
                    <th style="width:80px;">엔진</th>
                    <th>제목</th>
                    <th>설명</th>
                    <th style="width:72px;">상태</th>
                    <th style="width:120px;"></th>
                </tr>
            </thead>
            <tbody id="scBody"></tbody>
        </table>
    </div>
</div>

<!-- 편집 모달 -->
<div class="adm-modal-overlay" id="scModalOverlay">
    <div class="adm-modal" style="max-width:520px;">
        <div class="adm-modal-head">
            <h3>스튜디오 카드 수정 — <span id="scEngineLabel" style="color:var(--teal);"></span></h3>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <input type="hidden" id="scId">
            <input type="hidden" id="scEngineKey">
            <div class="adm-mfield">
                <label>제목</label>
                <input id="scTitle" type="text" placeholder="예: Classic Lattice" maxlength="100">
            </div>
            <div class="adm-mfield">
                <label>설명</label>
                <textarea id="scDesc" rows="3" style="width:100%;padding:8px 10px;border:1px solid var(--border-md);border-radius:var(--r-sm);background:var(--input-bg);font-family:inherit;font-size:var(--fs-14);color:var(--text-1);outline:none;resize:vertical;" placeholder="카드 설명 텍스트 (HTML 허용)"></textarea>
            </div>
            <div class="adm-mfield">
                <label>배경 이미지</label>
                <img id="scImgPreview" class="sc-img-preview" src="" alt="">
                <p style="font-size:11px;color:var(--text-3);margin:0 0 6px;">표준 사이즈 1200 X 800 px 권장</p>
                <label class="sc-upload-label" for="scImgFile">
                    <i class="bi bi-upload"></i> 이미지 업로드 (PNG/JPG, 최대 1200px)
                </label>
                <input type="file" id="scImgFile" accept="image/jpeg,image/png" style="display:none;" onchange="previewImage(this)">
                <input id="scImageUrl" type="text" placeholder="또는 https://... URL 직접 입력"
                    style="margin-top:6px;height:38px;padding:0 10px;border:1px solid var(--border-md);border-radius:var(--r-sm);background:var(--input-bg);font-family:inherit;font-size:var(--fs-14);color:var(--text-1);outline:none;width:100%;">
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" onclick="saveCard()">저장</button>
        </div>
    </div>
</div>

<script src="/src/js/admin/studio_cards.js"></script>
</body>
</html>
