<?php
// 어드민 '견적서' 메뉴 — 발행 목록 + 견적서 작성(주문 선택→품목 편집→발행) + 공급자 정보. 고객은 이 화면·견적서 화면을 볼 수 없다.
// 주문 관리 상세의 '견적서 작성' 버튼은 ?order_id= 로 이 페이지를 열어 작성 창을 바로 띄운다.
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
    <?php css_tag('/src/css/admin.css'); ?>

    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>
<?php include __DIR__ . '/../components/admin_sidenav.php'; ?>

<div class="db-page" id="adminAuthWall" style="display:none;">
    <div class="db-auth-banner">
        <p>관리자 페이지에 접근하려면 로그인이 필요합니다.</p>
        <button class="db-auth-btn" data-bs-toggle="modal" data-bs-target="#authModal">
            <i class="bi bi-person-circle"></i> 로그인
        </button>
    </div>
</div>
<div class="db-page" id="adminForbidden" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="adminPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>견적서</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-file-earmark-text me-2"></i>견적서</h1>
        <button class="adm-btn-save" onclick="openPicker()"><i class="bi bi-plus-lg me-1"></i>새 견적서</button>
    </div>
    <p class="qt-lead">주문을 골라 견적서를 작성·발행합니다. 견적서는 관리자만 볼 수 있고, 고객에게는 메일 본문으로만 전달됩니다. 다시 발행하면 차수가 올라가고 이전 차수는 보관됩니다.</p>

    <div class="adm-table-wrap">
        <div class="adm-toolbar">
            <div class="adm-search">
                <i class="bi bi-search" style="color: var(--text);font-size:12px;flex-shrink:0;"></i>
                <input type="text" id="qtSearch" placeholder="견적번호, 고객명, 회사명, 도면명 또는 주문번호" oninput="onSearchInput()">
            </div>
            <span class="adm-total" id="qtTotal"></span>
        </div>
        <div>
            <table>
                <thead>
                    <tr>
                        <th>견적번호</th>
                        <th>발행일</th>
                        <th>수신</th>
                        <th>도면</th>
                        <th style="text-align:right;">공급가액</th>
                        <th style="text-align:right;">합계(VAT포함)</th>
                        <th>메일</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="qtTbody"></tbody>
            </table>
        </div>
        <div class="adm-pagination" id="qtPagination"></div>
    </div>

    <details class="ord-group qt-supplier" id="qtSupplierBox">
        <summary class="ord-group-title">공급자 정보 <small>견적서 오른쪽 위 '공급자' 칸 · 저장하면 다음 발행분부터 반영</small></summary>
        <div class="ord-group-body">
            <div class="qt-grid2" id="qtSupplierForm"></div>
            <div style="display:flex;gap:8px;align-items:center;">
                <button class="adm-btn-save" onclick="saveSupplier()">공급자 정보 저장</button>
                <span id="qtSupplierMsg" class="qt-muted"></span>
            </div>
        </div>
    </details>
</div>

<!-- 1단계: 주문 선택 -->
<div class="adm-modal-overlay" id="qtPickerOverlay">
    <div class="adm-modal" style="max-width:640px;">
        <div class="adm-modal-head">
            <h3>견적서를 작성할 주문 선택</h3>
            <button class="adm-modal-close" onclick="closePicker()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <div class="adm-mfield">
                <input type="text" id="qtPickerSearch" placeholder="고객명, 회사명, 도면명 또는 주문번호" oninput="onPickerInput()">
            </div>
            <div id="qtPickerList" class="qt-picker-list"></div>
        </div>
    </div>
</div>

<!-- 2단계: 견적서 작성 -->
<div class="adm-modal-overlay" id="qtEditorOverlay">
    <div class="adm-modal" style="max-width:860px;">
        <div class="adm-modal-head">
            <h3 id="qtEditorTitle">견적서 작성</h3>
            <button class="adm-modal-close" onclick="closeEditor()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <div id="qtEditorAlert" style="display:none;"></div>
            <div id="qtOrderSummary"></div>

            <div class="ord-section-title">수신 (고객)</div>
            <div class="qt-grid2" id="qtCustomerForm"></div>

            <div class="ord-section-title">품목 <small class="qt-muted">단가는 부가세 별도 · 할인은 음수 단가로</small></div>
            <div class="qt-items-wrap">
                <table class="qt-items">
                    <thead><tr><th style="width:28px;">No</th><th>품명</th><th>규격</th><th style="width:70px;">수량</th><th style="width:120px;">단가</th><th style="width:110px;">공급가액</th><th style="width:32px;"></th></tr></thead>
                    <tbody id="qtItems"></tbody>
                </table>
            </div>
            <button class="adm-btn-cancel qt-add-row" onclick="addItem()"><i class="bi bi-plus"></i> 행 추가</button>
            <div class="qt-quick" id="qtQuick"></div>
            <div class="qt-totals" id="qtTotals"></div>

            <div class="ord-section-title">조건</div>
            <div class="qt-grid2">
                <div class="adm-mfield"><label>유효기한</label><input type="date" id="qtValidUntil"></div>
                <div class="adm-mfield"><label>납기</label><input type="text" id="qtLeadTime" maxlength="100" placeholder="예: 주문 확정 후 약 21일"></div>
            </div>
            <div class="adm-mfield">
                <label>비고 <small class="qt-muted" style="text-transform:none;letter-spacing:0;">고객에게 보이는 문구</small></label>
                <textarea id="qtNote" rows="4" maxlength="3000" class="qt-textarea" placeholder="예: 시공비 별도 / 계약금 50% 입금 후 제작 착수"></textarea>
            </div>

            <div class="ord-section-title">발행</div>
            <label class="qt-check"><input type="checkbox" id="qtSendEmail" checked> 고객에게 견적서 메일 보내기 <span id="qtSendTo" class="qt-muted"></span></label>
            <label class="qt-check" id="qtSetStatusRow"><input type="checkbox" id="qtSetStatus" checked> 주문 상태를 '견적확정'으로 바꾸기</label>
            <p class="qt-muted" style="margin:6px 0 0;">발행하면 주문의 확정 가격이 이 견적서의 공급가액으로 바뀝니다.</p>

            <div id="qtHistory"></div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeEditor()">취소</button>
            <button class="adm-btn-save" id="qtIssueBtn" onclick="issueQuote()">발행</button>
        </div>
    </div>
</div>

<script src="/src/js/order-status-labels.js?v=<?= md5_file(__DIR__ . '/../js/order-status-labels.js') ?>"></script>
<script src="/src/js/admin/quotes.js?v=<?= md5_file(__DIR__ . '/../js/admin/quotes.js') ?>"></script>
</body>
</html>
