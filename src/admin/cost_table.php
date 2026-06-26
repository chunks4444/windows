<?php header('Content-Type: text/html; charset=UTF-8'); ?>
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
    
    <?php css_tag('/src/css/admin/cost_table.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="wtAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="wtPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>원가 테이블</div>
    <div class="db-header">
        <div>
            <h1 class="db-title"><i class="bi bi-calculator me-2"></i>원가 테이블</h1>
            <p style="font-size:12px;color:var(--text-3);margin:2px 0 0;">목재 종류별 단가 및 가중치를 관리합니다.</p>
        </div>
        <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="openModal()">
            <i class="bi bi-plus-lg"></i> 추가
        </button>
    </div>

    <div class="adm-table-wrap" style="margin-top:8px;">
        <table id="wtTable">
            <thead>
                <tr>
                    <th style="width:32px;"></th>
                    <th id="thCategory" style="width:100px;cursor:pointer;user-select:none;">구분 <span id="thCategorySort"></span></th>
                    <th>항목</th>
                    <th style="width:140px;text-align:right;">단가</th>
                    <th style="width:80px;">단위</th>
                    <th style="width:100px;">단위명</th>
                    <th style="width:100px;text-align:right;">가중치</th>
                    <th>메모</th>
                    <th style="width:72px;">상태</th>
                    <th style="width:160px;"></th>
                </tr>
            </thead>
            <tbody id="wtBody"></tbody>
        </table>
    </div>
</div>

<!-- 편집 모달 -->
<div class="adm-modal-overlay" id="wtModalOverlay">
    <div class="adm-modal" style="max-width:420px;">
        <div class="adm-modal-head">
            <h3 id="wtModalTitle">항목 추가</h3>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <input type="hidden" id="wtId">
            <div class="adm-mfield">
                <label>구분</label>
                <select id="wtCategory">
                    <option value="">— 선택 —</option>
                    <option value="wood">목재</option>
                    <option value="oil">오일</option>
                    <option value="grid">엔진</option>
                    <option value="labor">인건비</option>
                    <option value="overhead">일반경비</option>
                    <option value="finish">마감</option>
                    <option value="delivery">배송비</option>
                </select>
            </div>
            <div class="adm-mfield">
                <label>항목</label>
                <input id="wtName" type="text" placeholder="예: 홍송" maxlength="100">
            </div>
            <div class="wt-price-row">
                <div class="adm-mfield wt-price-field">
                    <label>단가</label>
                    <input id="wtUnitPrice" type="number" min="0" step="100" placeholder="12000" class="num-input">
                </div>
                <div class="adm-mfield wt-unit-field">
                    <label>단위</label>
                    <input id="wtUnit" type="text" placeholder="사이, %, 시간…" maxlength="30">
                </div>
                <div class="adm-mfield wt-unit-field">
                    <label>단위명</label>
                    <input id="wtUnitName" type="text" placeholder="재(才), 퍼센트…" maxlength="50">
                </div>
            </div>
            <div class="adm-mfield">
                <label>가중치</label>
                <input id="wtWeight" type="number" min="0" step="0.01" placeholder="1.00" class="num-input">
            </div>
            <div class="adm-mfield">
                <label>메모</label>
                <textarea id="wtNotes" rows="3" placeholder="특이사항, 구매처, 참고사항 등"
                    style="width:100%;padding:8px 10px;border:1px solid var(--border-md);border-radius:var(--r-sm);background:var(--input-bg);font-family:inherit;font-size:13px;color:var(--text-1);outline:none;resize:vertical;"></textarea>
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" onclick="saveItem()">저장</button>
        </div>
    </div>
</div>

<script src="/src/js/admin/cost_table.js"></script>
</body>
</html>
