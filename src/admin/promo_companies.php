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

    <?php css_tag('/src/css/admin.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>
<?php include __DIR__ . '/../components/admin_sidenav.php'; ?>

<div class="db-page" id="pcPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>발송 대상 회사</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-building me-2"></i>발송 대상 회사</h1>
        <button class="adm-btn-save" onclick="openModal()"><i class="bi bi-plus-lg"></i> 회사 등록</button>
    </div>

    <div class="adm-table-wrap">
        <div class="adm-toolbar">
            <div class="adm-tab-bar" id="pcTabs"></div>
            <div class="adm-search">
                <i class="bi bi-search" style="color: var(--text);font-size:12px;flex-shrink:0;"></i>
                <input type="text" id="pcSearch" placeholder="회사명, 이메일, 전화번호, 주소 검색" oninput="render()">
            </div>
            <select id="pcSentFilter" onchange="render()" style="height:34px;border:1px solid var(--border);border-radius:var(--r-sm);background:var(--bg);color:var(--text);padding:0 8px;font-size:13px;">
                <option value="">발송 여부 전체</option>
                <option value="sent">발송한 회사</option>
                <option value="unsent">미발송 회사</option>
            </select>
            <span class="adm-total" id="pcTotal"></span>
        </div>

        <table>
            <thead>
                <tr>
                    <th>회사명</th>
                    <th>업종</th>
                    <th>이메일</th>
                    <th>전화번호</th>
                    <th>주소</th>
                    <th>홈페이지</th>
                    <th>발송 횟수</th>
                    <th>마지막 발송</th>
                    <th>수신</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="pcBody"></tbody>
        </table>
    </div>
</div>

<!-- 등록/수정 모달 -->
<div class="adm-modal-overlay" id="pcModalOverlay">
    <div class="adm-modal" style="max-width:520px;">
        <div class="adm-modal-head">
            <h3 id="pcModalTitle">회사 등록</h3>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <input type="hidden" id="pcId">
            <div class="adm-mfield">
                <label>회사명</label>
                <input type="text" id="pcName" maxlength="100" placeholder="(주)평목건축">
            </div>
            <div class="adm-mfield">
                <label>업종</label>
                <select id="pcIndustry" style="width:100%;height:38px;border:1px solid var(--border);border-radius:var(--r-sm);background:var(--bg);color:var(--text);padding:0 10px;font-size:13px;"></select>
            </div>
            <div class="adm-mfield">
                <label>이메일</label>
                <input type="email" id="pcEmail" maxlength="190" placeholder="info@example.com">
            </div>
            <div class="adm-mfield">
                <label>전화번호</label>
                <input type="text" id="pcPhone" maxlength="30" placeholder="02-0000-0000">
            </div>
            <div class="adm-mfield">
                <label>주소</label>
                <input type="text" id="pcAddress" maxlength="255" placeholder="도로명 주소">
            </div>
            <div class="adm-mfield">
                <label>홈페이지 주소</label>
                <input type="text" id="pcWebsite" maxlength="255" placeholder="example.com">
            </div>
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;margin:0;">
                <input type="checkbox" id="pcOptOut"> 수신거부 (발송 대상에서 제외)
            </label>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" id="pcSaveBtn" onclick="saveCompany()">저장</button>
        </div>
    </div>
</div>

<script src="/src/js/admin/promo_companies.js?v=<?= md5_file(__DIR__ . '/../js/admin/promo_companies.js') ?>"></script>
</body>
</html>
