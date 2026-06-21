<?php
header('Content-Type: text/html; charset=UTF-8');
?>
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
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="metaPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>SEO 메타 관리</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-search me-2"></i>SEO 메타 관리</h1>
        <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="openAddModal()">
            <i class="bi bi-plus-lg"></i> 추가
        </button>
    </div>

    <div class="adm-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>경로</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Keywords</th>
                    <th style="width:80px;"></th>
                </tr>
            </thead>
            <tbody id="metaTbody"></tbody>
        </table>
    </div>
</div>

<!-- 추가/수정 모달 -->
<div class="adm-modal-overlay" id="metaModalOverlay">
    <div class="adm-modal" style="max-width:560px;">
        <div class="adm-modal-head">
            <h3 id="metaModalTitle">메타 추가</h3>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <div id="metaModalAlert" style="display:none;"></div>
            <div class="adm-mfield">
                <label>경로 <small style="color:var(--text-3);font-weight:400;">(예: /src/company/index.php)</small></label>
                <input type="text" id="metaPath" placeholder="/index.php" maxlength="120">
            </div>
            <div class="adm-mfield">
                <label>Title <small style="color:var(--text-3);font-weight:400;">최대 60자 권장</small></label>
                <input type="text" id="metaTitle" maxlength="200" oninput="updateCount('metaTitle', 'cntTitle', 60)">
                <small id="cntTitle" style="color:var(--text-3);font-size:11px;"></small>
            </div>
            <div class="adm-mfield">
                <label>Description <small style="color:var(--text-3);font-weight:400;">최대 160자 권장</small></label>
                <textarea id="metaDesc" rows="3" maxlength="320" style="resize:none;border:1px solid #e0e0e0;border-radius:4px;padding:9px 12px;font-size:13px;font-family:inherit;width:100%;outline:none;" oninput="updateCount('metaDesc', 'cntDesc', 160)"></textarea>
                <small id="cntDesc" style="color:var(--text-3);font-size:11px;"></small>
            </div>
            <div class="adm-mfield">
                <label>Keywords</label>
                <input type="text" id="metaKeywords" maxlength="500" placeholder="쉼표로 구분">
            </div>
            <div class="adm-mfield" style="margin-bottom:0;">
                <label>OG Image URL</label>
                <input type="text" id="metaOgImage" maxlength="500" placeholder="https://…">
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" id="metaSaveBtn" onclick="saveMeta()">저장</button>
        </div>
    </div>
</div>

<script src="/src/js/admin/meta.js"></script>
</body>
</html>
