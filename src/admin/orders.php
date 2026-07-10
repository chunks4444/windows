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
    <?php css_tag('/src/css/admin/orders.css'); ?>

    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<!-- 비로그인 -->
<div class="db-page" id="adminAuthWall" style="display:none;">
    <div class="db-auth-banner">
        <p>관리자 페이지에 접근하려면 로그인이 필요합니다.</p>
        <button class="db-auth-btn" data-bs-toggle="modal" data-bs-target="#authModal">
            <i class="bi bi-person-circle"></i> 로그인
        </button>
    </div>
</div>

<!-- 권한 없음 -->
<div class="db-page" id="adminForbidden" style="display:none;">
    <div class="db-auth-banner">
        <p>슈퍼 권한이 필요합니다.</p>
    </div>
</div>

<!-- 주문 관리 페이지 -->
<div class="db-page" id="adminPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>주문 관리</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-receipt me-2"></i>주문 관리</h1>
    </div>

    <div class="adm-table-wrap">
        <div class="adm-toolbar">
            <div class="adm-search">
                <i class="bi bi-search" style="color: var(--text);font-size:12px;flex-shrink:0;"></i>
                <input type="text" id="admSearch" placeholder="고객명, 연락처, 회사명 또는 도면명 검색" oninput="onSearchInput()">
            </div>
            <select id="admStatusFilter" class="ord-status-filter" onchange="loadOrders(1)" style="height:34px;border:1px solid var(--border);border-radius:var(--r-sm);background:var(--bg);color:var(--text);padding:0 8px;font-size:13px;">
                <option value="">전체 상태</option>
            </select>
            <span class="adm-total" id="admTotal"></span>
        </div>

        <div id="admTableBody">
            <table>
                <thead>
                    <tr>
                        <th>주문번호</th>
                        <th>주문일</th>
                        <th>고객</th>
                        <th>회사</th>
                        <th>엔진/도면</th>
                        <th>납기희망일</th>
                        <th>상태</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="admTbody"></tbody>
            </table>
        </div>

        <div class="adm-pagination" id="admPagination"></div>
    </div>
</div>

<!-- 주문 상세/상태 변경 모달 -->
<div class="adm-modal-overlay" id="admModalOverlay">
    <div class="adm-modal" style="max-width:640px;">
        <div class="adm-modal-head">
            <h3 id="admModalTitle">주문 상세</h3>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body" id="admModalBody">
            <div id="admModalAlert" style="display:none;"></div>
            <div id="admDetailContent"></div>

            <div class="ord-section-title">상태 변경</div>
            <div class="adm-mfield">
                <label>처리 상태</label>
                <select id="admMStatus"></select>
            </div>
            <div class="adm-mfield" id="admMNoteField" style="display:none;">
                <label>수정요청 사유</label>
                <textarea id="admMNote" rows="3" style="width:100%;border:1px solid var(--border);border-radius:var(--r-sm);background:var(--bg);color:var(--text);padding:8px;font-size:13px;" maxlength="2000"></textarea>
            </div>
            <div class="adm-mfield" id="admMTrackingField" style="display:none;">
                <label>택배사</label>
                <input type="text" id="admMCarrier" placeholder="예: CJ대한통운" maxlength="50">
            </div>
            <div class="adm-mfield" id="admMTrackingNoField" style="display:none;">
                <label>운송장번호</label>
                <input type="text" id="admMTrackNo" placeholder="운송장번호" maxlength="50">
            </div>

            <div class="ord-section-title">확정 가격 <small style="font-weight:400;color:var(--text);text-transform:none;letter-spacing:normal;">(공식 자동계산 전까지 고객과 협의해 수기 입력)</small></div>
            <div class="adm-mfield">
                <label>확정 가격 (원)</label>
                <input type="number" id="admMFinalPrice" placeholder="예: 1500000" min="0" step="1000">
            </div>
            <div class="adm-mfield" style="margin-bottom:0;">
                <label>가격 협의 메모</label>
                <textarea id="admMPriceNote" rows="3" style="width:100%;border:1px solid var(--border);border-radius:var(--r-sm);background:var(--bg);color:var(--text);padding:8px;font-size:13px;" maxlength="2000" placeholder="예: 오일마감 추가, 배송비 별도 협의 등"></textarea>
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" id="admSaveBtn" onclick="saveOrder()">저장</button>
        </div>
    </div>
</div>

<script src="/src/js/order-status-labels.js?v=<?= md5_file(__DIR__ . '/../js/order-status-labels.js') ?>"></script>
<script src="/src/js/admin/orders.js?v=<?= md5_file(__DIR__ . '/../js/admin/orders.js') ?>"></script>
</body>
</html>
