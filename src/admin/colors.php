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
    
    <?php css_tag('/src/css/admin/colors.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>
<?php include __DIR__ . '/../components/admin_sidenav.php'; ?>

<div class="db-page" id="colorPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>컬러 팔레트 관리</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-palette me-2"></i>컬러 팔레트 관리</h1>
        <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="openAddModal()">
            <i class="bi bi-plus-lg"></i> 색상 추가
        </button>
    </div>
    <div class="adm-table-wrap">
        <table id="colorTable">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>그룹</th>
                    <th>코드</th>
                    <th>이름</th>
                    <th>헥스</th>
                    <th style="width:70px;">순서</th>
                    <th style="width:80px;">활성</th>
                    <th style="width:140px;"></th>
                </tr>
            </thead>
            <tbody id="colorBody"></tbody>
        </table>
    </div>
</div>

<!-- 추가/수정 모달 -->
<div id="colorModal" style="display:none;position:fixed;inset:0;background:rgba(var(--text-rgb), 0.4);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:var(--bg);border-radius:12px;width:360px;padding:24px;">
        <h5 id="colorModalTitle" style="margin:0 0 16px;font-size:15px;font-weight:700;">색상 추가</h5>
        <input type="hidden" id="editId">
        <div style="display:flex;flex-direction:column;gap:10px;">
            <label style="font-size:12px;font-weight:600;">그룹명<input type="text" id="editGroup" class="form-control form-control-sm mt-1" placeholder="스테인"></label>
            <label style="font-size:12px;font-weight:600;">정렬 순서<input type="number" id="editOrder" class="form-control form-control-sm mt-1" value="0" min="0"></label>
            <label style="font-size:12px;font-weight:600;">코드<input type="text" id="editCode" class="form-control form-control-sm mt-1" placeholder="930-00"></label>
            <label style="font-size:12px;font-weight:600;">이름<input type="text" id="editName" class="form-control form-control-sm mt-1" placeholder="투명"></label>
            <label style="font-size:12px;font-weight:600;">헥스 코드
                <div style="display:flex;gap:8px;align-items:center;margin-top:4px;">
                    <input type="color" id="editHexPicker" value="#dec898" oninput="document.getElementById('editHex').value=this.value">
                    <input type="text" id="editHex" class="form-control form-control-sm" placeholder="#dec898" oninput="syncColorPicker()">
                </div>
            </label>
        </div>
        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:20px;">
            <button class="btn btn-sm btn-secondary" onclick="closeModal()">취소</button>
            <button class="btn btn-sm btn-dark" onclick="saveColor()">저장</button>
        </div>
    </div>
</div>

<script src="/src/js/admin/colors.js"></script>
<script src="/src/js/color-hex-input.js"></script>
</body>
</html>
