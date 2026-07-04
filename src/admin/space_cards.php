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
    <?php require_once __DIR__ . '/../lib/meta.php'; ?>
    <?php meta_tags(); ?>
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
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>메인 큐레이션 관리</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-grid me-2"></i>메인 큐레이션 관리</h1>
        <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="openModal()">
            <i class="bi bi-plus-lg"></i> 추가
        </button>
    </div>
    <p style="font-size:12px;color:var(--text-3);margin:-8px 0 16px;">행을 드래그해 순서를 변경할 수 있습니다.</p>

    <div class="adm-table-wrap">
        <table id="scTable">
            <thead>
                <tr>
                    <th style="width:32px;"></th>
                    <th style="width:80px;">이미지</th>
                    <th>라벨</th>
                    <th>컬렉션 검색어</th>
                    <th style="width:72px;">상태</th>
                    <th style="width:160px;"></th>
                </tr>
            </thead>
            <tbody id="scBody"></tbody>
        </table>
    </div>
</div>

<!-- 편집 모달 -->
<div class="adm-modal-overlay" id="scModalOverlay">
    <div class="adm-modal" style="max-width:480px;">
        <div class="adm-modal-head">
            <h3 id="scModalTitle">공간 카드 추가</h3>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <input type="hidden" id="scId">
            <div class="adm-mfield">
                <label>라벨</label>
                <input id="scLabel" type="text" placeholder="예: 중문" maxlength="50">
            </div>
            <div class="adm-mfield">
                <label>컬렉션 검색어 (?q=)</label>
                <input id="scQuery" type="text" placeholder="예: 중문" maxlength="100">
            </div>
            <div class="adm-mfield">
                <label>이미지</label>
                <img id="scImgPreview" class="sc-img-preview" src="" alt="">
                <label class="sc-upload-label" for="scImgFile">
                    <i class="bi bi-upload"></i> 이미지 업로드
                </label>
                <input type="file" id="scImgFile" accept="image/*" style="display:none;" onchange="previewImage(this)">
                <input id="scImageUrl" type="text" placeholder="또는 https://... URL 직접 입력"
                    style="height:38px;padding:0 10px;border:1px solid var(--border-md);border-radius:var(--r-sm);background:var(--input-bg);font-family:inherit;font-size:13px;color:var(--text-1);outline:none;width:100%;">
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" onclick="saveCard()">저장</button>
        </div>
    </div>
</div>

<script src="/src/js/admin/space_cards.js"></script>
</body>
</html>
