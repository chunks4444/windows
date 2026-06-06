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
        <h1 class="db-title">회사 정보</h1>
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

<script>
const token = () => localStorage.getItem('pmok_auth_token');

async function loadCompany() {
    if (!token()) { location.href = '/'; return; }
    document.getElementById('companyPage').style.display = '';

    const [meRes, res] = await Promise.all([
        fetch('/src/api/auth/profile.php', { headers: { 'Authorization': 'Bearer ' + token() } }),
        fetch('/src/api/auth/company.php',  { headers: { 'Authorization': 'Bearer ' + token() } }),
    ]);
    const meData = await meRes.json();
    const data   = await res.json();
    if (!res.ok) return;

    document.getElementById('cpEmail').textContent = meData.user?.email || '—';

    const c = data.company;
    document.getElementById('cpName').value          = c.company_name           || '';
    document.getElementById('cpBizNo').value         = c.company_biz_no         || '';
    document.getElementById('cpBizType').value       = c.company_biz_type       || '';
    document.getElementById('cpBizCat').value        = c.company_biz_category   || '';
    document.getElementById('cpCeo').value           = c.company_ceo            || '';
    document.getElementById('cpPhone').value         = c.company_phone          || '';
    document.getElementById('cpZipcode').value       = c.company_zipcode        || '';
    document.getElementById('cpAddress').value       = c.company_address        || '';
    document.getElementById('cpAddressDetail').value = c.company_address_detail || '';
}

document.getElementById('cpForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('cpSaveBtn');
    btn.disabled = true;
    btn.textContent = '저장 중…';

    const body = {
        company_name:           document.getElementById('cpName').value.trim(),
        company_biz_no:         document.getElementById('cpBizNo').value.trim(),
        company_biz_type:       document.getElementById('cpBizType').value.trim(),
        company_biz_category:   document.getElementById('cpBizCat').value.trim(),
        company_ceo:            document.getElementById('cpCeo').value.trim(),
        company_phone:          document.getElementById('cpPhone').value.trim(),
        company_zipcode:        document.getElementById('cpZipcode').value.trim(),
        company_address:        document.getElementById('cpAddress').value.trim(),
        company_address_detail: document.getElementById('cpAddressDetail').value.trim(),
    };

    try {
        const res  = await fetch('/src/api/auth/company.php', {
            method:  'PUT',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() },
            body: JSON.stringify(body),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || '오류가 발생했습니다.');
        showAlert('저장되었습니다.', 'success');
    } catch (err) {
        showAlert(err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = '저장';
    }
});

function showAlert(msg, type) {
    const el = document.getElementById('cpAlert');
    el.textContent   = msg;
    el.className     = 'pf-alert pf-alert--' + type;
    el.style.display = '';
    setTimeout(() => { el.style.display = 'none'; }, 3000);
}

function openPostcode() {
    new daum.Postcode({
        oncomplete(data) {
            document.getElementById('cpZipcode').value       = data.zonecode;
            document.getElementById('cpAddress').value       = data.roadAddress || data.autoRoadAddress || data.jibunAddress;
            document.getElementById('cpAddressDetail').value = '';
            document.getElementById('cpAddressDetail').focus();
        }
    }).open();
}

document.addEventListener('DOMContentLoaded', loadCompany);
window.addEventListener('pmokAuthChanged', () => {
    document.getElementById('pageAuthWall').style.display = 'none';
    loadCompany();
});
</script>

</body>
</html>
