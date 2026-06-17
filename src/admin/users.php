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
    <link rel="stylesheet" href="/src/css/dashboard.css">
    <link rel="stylesheet" href="/src/css/users.css">
    
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

<!-- 회원 관리 페이지 -->
<div class="db-page" id="adminPage" style="display:none;">
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-shield-lock me-2"></i>회원 관리</h1>
    </div>

    <div class="adm-table-wrap">
        <div class="adm-toolbar">
            <div class="adm-search">
                <i class="bi bi-search" style="color:var(--text-3);font-size:12px;flex-shrink:0;"></i>
                <input type="text" id="admSearch" placeholder="이메일 또는 이름 검색" oninput="onSearchInput()">
            </div>
            <span class="adm-total" id="admTotal"></span>
        </div>

        <div id="admTableBody">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>이메일</th>
                        <th>권한</th>
                        <th>이름</th>
                        <th>연락처</th>
                        <th>가입일</th>
                        <th>최종 접속</th>
                        <th>접속 IP</th>
                        <th>상태</th>
                        <th>도면</th>
                        <th>내보내기</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="admTbody"></tbody>
            </table>
        </div>

        <div class="adm-pagination" id="admPagination"></div>
    </div>
</div>

<!-- 수정 모달 -->
<div class="adm-modal-overlay" id="admModalOverlay">
    <div class="adm-modal">
        <div class="adm-modal-head">
            <h3>회원 정보 수정</h3>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <div id="admModalAlert" style="display:none;"></div>
            <div class="adm-mfield">
                <label>이메일</label>
                <input type="text" id="admMEmail" readonly>
            </div>
            <div class="adm-mfield">
                <label>권한</label>
                <select id="admMRole">
                    <option value="s">슈퍼 (s)</option>
                    <option value="m">관리자 (m)</option>
                    <option value="a">작가 (a)</option>
                    <option value="u">회원 (u)</option>
                </select>
            </div>
            <div class="adm-mfield">
                <label>이름</label>
                <input type="text" id="admMName" placeholder="이름" maxlength="100">
            </div>
            <div class="adm-mfield">
                <label>연락처</label>
                <input type="text" id="admMPhone" placeholder="010-0000-0000" maxlength="30">
            </div>
            <div class="adm-mfield" style="margin-bottom:0;">
                <label>계정 상태</label>
                <select id="admMWithdrawn">
                    <option value="0">정상</option>
                    <option value="1">탈퇴</option>
                </select>
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" id="admSaveBtn" onclick="saveUser()">저장</button>
        </div>
    </div>
</div>

<!-- 내보내기 내역 모달 -->
<div class="adm-modal-overlay" id="exportModalOverlay">
    <div class="adm-modal" style="max-width:640px;">
        <div class="adm-modal-head">
            <h3 id="exportModalTitle">내보내기 내역</h3>
            <button class="adm-modal-close" onclick="closeExportModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body" style="padding:0;max-height:420px;overflow-y:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:12px;">
                <thead style="position:sticky;top:0;background:var(--card-bg);z-index:1;">
                    <tr>
                        <th style="padding:10px 14px;text-align:left;border-bottom:1px solid var(--border-md);color:var(--text-3);font-weight:500;">일시</th>
                        <th style="padding:10px 14px;text-align:left;border-bottom:1px solid var(--border-md);color:var(--text-3);font-weight:500;">엔진</th>
                        <th style="padding:10px 14px;text-align:left;border-bottom:1px solid var(--border-md);color:var(--text-3);font-weight:500;">형식</th>
                        <th style="padding:10px 14px;text-align:left;border-bottom:1px solid var(--border-md);color:var(--text-3);font-weight:500;">도면명</th>
                        <th style="padding:10px 14px;text-align:left;border-bottom:1px solid var(--border-md);color:var(--text-3);font-weight:500;">버전</th>
                    </tr>
                </thead>
                <tbody id="exportModalTbody"></tbody>
            </table>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeExportModal()">닫기</button>
        </div>
    </div>
</div>

<script src="/src/js/admin/users.js"></script>
</body>
</html>
