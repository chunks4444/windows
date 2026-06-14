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
    <link rel="stylesheet" href="/src/css/profile.css">
    <script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js" defer></script>
    <?php include __DIR__ . '/../components/auth_guard.php'; ?>
</head>
<body>

<?php include __DIR__ . '/../components/nav.php'; ?>

<!-- 로그인 필요 -->
<div class="db-page" id="pageAuthWall" style="display:none;">
    <div class="db-auth-banner">
        <p>회사 정보를 수정하려면 로그인이 필요합니다.</p>
        <button class="db-auth-btn" data-bs-toggle="modal" data-bs-target="#authModal">
            <i class="bi bi-person-circle"></i> 로그인
        </button>
    </div>
</div>

<!-- 회사정보 폼 -->
<div class="db-page" id="companyPage" style="display:none;">
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-building me-2"></i>회사 정보</h1>
    </div>

    <div class="pf-card">
        <div class="pf-section">
            <h2 class="pf-section-title">기본 정보</h2>
            <div class="pf-email-row">
                <span class="pf-label">이메일</span>
                <span class="pf-email" id="cpEmail">—</span>
            </div>
        </div>

        <div class="pf-section">
            <h2 class="pf-section-title">회사 정보</h2>

            <div id="cpAlert" class="pf-alert" style="display:none;"></div>

            <form id="cpForm" novalidate>
                <div class="pf-columns">
                    <!-- 왼쪽 -->
                    <div class="pf-col">
                        <div class="pf-row">
                            <div class="pf-field">
                                <label class="pf-label" for="cpName">회사명</label>
                                <input id="cpName" name="company_name" type="text" class="pf-input"
                                       placeholder="(주)평목" maxlength="100">
                            </div>
                            <div class="pf-field">
                                <label class="pf-label" for="cpBizNo">사업자등록번호</label>
                                <input id="cpBizNo" name="company_biz_no" type="text" class="pf-input"
                                       placeholder="000-00-00000" maxlength="20">
                            </div>
                        </div>
                        <div class="pf-row" style="margin-top:12px;">
                            <div class="pf-field">
                                <label class="pf-label" for="cpBizType">업태</label>
                                <input id="cpBizType" name="company_biz_type" type="text" class="pf-input"
                                       placeholder="제조업" maxlength="100">
                            </div>
                            <div class="pf-field">
                                <label class="pf-label" for="cpBizCat">업종</label>
                                <input id="cpBizCat" name="company_biz_category" type="text" class="pf-input"
                                       placeholder="목재가구" maxlength="100">
                            </div>
                        </div>
                        <div class="pf-row" style="margin-top:12px;">
                            <div class="pf-field">
                                <label class="pf-label" for="cpCeo">대표자명</label>
                                <input id="cpCeo" name="company_ceo" type="text" class="pf-input"
                                       placeholder="홍길동" maxlength="100">
                            </div>
                            <div class="pf-field">
                                <label class="pf-label" for="cpPhone">대표 연락처</label>
                                <input id="cpPhone" name="company_phone" type="tel" class="pf-input"
                                       placeholder="02-0000-0000" maxlength="30">
                            </div>
                        </div>
                    </div>

                    <!-- 오른쪽: 주소 -->
                    <div class="pf-col pf-col--address">
                        <div class="pf-field">
                            <label class="pf-label" for="cpZipcode">회사 주소</label>
                            <div class="pf-zipcode-row">
                                <input id="cpZipcode" name="company_zipcode" type="text" class="pf-input pf-input--sm"
                                       placeholder="우편번호" maxlength="6" readonly>
                                <button type="button" class="pf-btn-zip" onclick="openPostcode()">우편번호 검색</button>
                            </div>
                            <input id="cpAddress" name="company_address" type="text" class="pf-input"
                                   placeholder="도로명 주소" maxlength="255" readonly>
                            <input id="cpAddressDetail" name="company_address_detail" type="text" class="pf-input"
                                   placeholder="상세 주소 (층/호 등)" maxlength="100">
                        </div>
                    </div>
                </div>

                <div class="pf-actions">
                    <button type="submit" class="pf-btn-save" id="cpSaveBtn">저장</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="/src/js/company.js"></script>

</body>
</html>
