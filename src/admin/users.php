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
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>
<?php include __DIR__ . '/../components/admin_sidenav.php'; ?>

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
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>회원 관리</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-shield-lock me-2"></i>회원 관리</h1>
    </div>

    <div class="adm-table-wrap">
        <div class="adm-toolbar">
            <div class="adm-search">
                <i class="bi bi-search" style="color: var(--text);font-size:12px;flex-shrink:0;"></i>
                <input type="text" id="admSearch" placeholder="이메일, 이름 또는 회사명 검색" oninput="onSearchInput()">
            </div>
            <select id="admPermFilter" onchange="loadUsers(1)" style="height:34px;border:1px solid var(--border);border-radius:var(--r-sm);background:var(--bg);color:var(--text);padding:0 8px;font-size:13px;">
                <option value="">전체 열람 권한</option>
                <option value="view_spec">제작 시방서 허용된 회원</option>
                <option value="view_parts">부재목록 허용된 회원</option>
                <option value="view_cost">예산견적 상세 허용된 회원</option>
                <option value="view_price">예상가격 허용된 회원</option>
                <option value="view_leadtime">최소 납기 허용된 회원</option>
                <option value="view_shipping">배송비 허용된 회원</option>
                <option value="view_desc">설명 허용된 회원</option>
            </select>
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
                        <th>회사정보</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="admTbody"></tbody>
            </table>
        </div>

        <div class="adm-pagination" id="admPagination"></div>
    </div>
</div>

<!-- 회사정보 모달 -->
<div class="adm-modal-overlay" id="companyModalOverlay">
    <div class="adm-modal" style="max-width:560px;">
        <div class="adm-modal-head">
            <h3>회사정보 수정</h3>
            <button class="adm-modal-close" onclick="closeCompanyModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <div id="companyModalAlert" style="display:none;"></div>
            <div class="adm-mfield">
                <label>회사명</label>
                <input type="text" id="cmName" placeholder="(주)평목" maxlength="100">
            </div>
            <div class="adm-mfield">
                <label>사업자등록번호</label>
                <input type="text" id="cmBizNo" placeholder="000-00-00000" maxlength="20">
            </div>
            <div class="adm-mfield">
                <label>업태</label>
                <input type="text" id="cmBizType" placeholder="제조업" maxlength="100">
            </div>
            <div class="adm-mfield">
                <label>업종</label>
                <input type="text" id="cmBizCat" placeholder="목재가구" maxlength="100">
            </div>
            <div class="adm-mfield">
                <label>대표자명</label>
                <input type="text" id="cmCeo" placeholder="홍길동" maxlength="100">
            </div>
            <div class="adm-mfield">
                <label>대표 연락처</label>
                <input type="text" id="cmPhone" placeholder="02-0000-0000" maxlength="30">
            </div>
            <div class="adm-mfield">
                <label>우편번호</label>
                <input type="text" id="cmZipcode" placeholder="우편번호" maxlength="10">
            </div>
            <div class="adm-mfield">
                <label>주소</label>
                <input type="text" id="cmAddress" placeholder="도로명 주소" maxlength="255">
            </div>
            <div class="adm-mfield" style="margin-bottom:0;">
                <label>상세 주소</label>
                <input type="text" id="cmAddressDetail" placeholder="층/호 등" maxlength="100">
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeCompanyModal()">취소</button>
            <button class="adm-btn-save" id="companySaveBtn" onclick="saveCompany()">저장</button>
        </div>
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
            <div class="adm-mfield">
                <label>계정 상태</label>
                <select id="admMWithdrawn">
                    <option value="0">정상</option>
                    <option value="1">탈퇴</option>
                </select>
            </div>
            <div class="adm-mfield" style="margin-bottom:0;">
                <label>엔진 열람 권한 <small style="font-weight:400;color: var(--text);text-transform:none;letter-spacing:normal;">(role과 별개로 개별 승인)</small></label>
                <div style="display:flex;flex-direction:column;gap:6px;">
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:400;color:var(--text);text-transform:none;letter-spacing:normal;"><input type="checkbox" id="admMViewSpec" style="width:auto;height:auto;"> 제작 시방서 열람 허용</label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:400;color:var(--text);text-transform:none;letter-spacing:normal;"><input type="checkbox" id="admMViewParts" style="width:auto;height:auto;"> 부재목록 열람 허용</label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:400;color:var(--text);text-transform:none;letter-spacing:normal;"><input type="checkbox" id="admMViewCost" style="width:auto;height:auto;"> 예산견적 상세 열람 허용</label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:400;color:var(--text);text-transform:none;letter-spacing:normal;"><input type="checkbox" id="admMViewPrice" style="width:auto;height:auto;"> 예상가격 열람 허용</label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:400;color:var(--text);text-transform:none;letter-spacing:normal;"><input type="checkbox" id="admMViewLeadtime" style="width:auto;height:auto;"> 최소 납기 열람 허용</label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:400;color:var(--text);text-transform:none;letter-spacing:normal;"><input type="checkbox" id="admMViewShipping" style="width:auto;height:auto;"> 배송비 안내문구 열람 허용</label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:400;color:var(--text);text-transform:none;letter-spacing:normal;"><input type="checkbox" id="admMViewDesc" style="width:auto;height:auto;"> 예상견적 설명(안내문구) 열람 허용</label>
                </div>
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
                        <th style="padding:10px 14px;text-align:left;border-bottom:1px solid var(--border);color: var(--text);font-weight:500;">일시</th>
                        <th style="padding:10px 14px;text-align:left;border-bottom:1px solid var(--border);color: var(--text);font-weight:500;">엔진</th>
                        <th style="padding:10px 14px;text-align:left;border-bottom:1px solid var(--border);color: var(--text);font-weight:500;">형식</th>
                        <th style="padding:10px 14px;text-align:left;border-bottom:1px solid var(--border);color: var(--text);font-weight:500;">도면명</th>
                        <th style="padding:10px 14px;text-align:left;border-bottom:1px solid var(--border);color: var(--text);font-weight:500;">버전</th>
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
