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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/src/css/dashboard.css">
    <link rel="stylesheet" href="/src/css/profile.css">
    <script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js" defer></script>
    <?php include __DIR__ . '/../components/auth_guard.php'; ?>
</head>
<body>

<?php include __DIR__ . '/../components/nav.php'; ?>

<!-- 로그인 필요 -->
<div class="db-page" id="profileAuthWall" style="display:none;">
    <div class="db-auth-banner">
        <p>프로필을 수정하려면 로그인이 필요합니다.</p>
        <button class="db-auth-btn" data-bs-toggle="modal" data-bs-target="#authModal">
            <i class="bi bi-person-circle"></i> 로그인
        </button>
    </div>
</div>

<!-- 프로필 폼 -->
<div class="db-page" id="profilePage" style="display:none;">
    <div class="db-header">
        <h1 class="db-title">프로필</h1>
    </div>

    <div class="pf-card">
        <div class="pf-section">
            <h2 class="pf-section-title">기본 정보</h2>

            <div class="pf-email-row">
                <span class="pf-label">이메일</span>
                <span class="pf-email" id="pfEmail">—</span>
                <span class="pf-role-badge" id="pfRoleBadge"></span>
            </div>
        </div>

        <div class="pf-section">
            <h2 class="pf-section-title">비밀번호 수정</h2>

            <div id="pfPwAlert" class="pf-alert" style="display:none;"></div>

            <form id="pfPwForm" novalidate>
                <div class="pf-columns">
                    <div class="pf-col">
                        <div class="pf-field">
                            <label class="pf-label" for="pfPwCurrent">현재 비밀번호</label>
                            <input id="pfPwCurrent" type="password" class="pf-input pf-input--sm"
                                   placeholder="현재 비밀번호" autocomplete="current-password">
                        </div>
                    </div>
                    <div class="pf-col pf-col--address">
                        <div class="pf-field">
                            <label class="pf-label" for="pfPwNew">새 비밀번호</label>
                            <input id="pfPwNew" type="password" class="pf-input pf-input--sm"
                                   placeholder="6자 이상" autocomplete="new-password">
                        </div>
                        <div class="pf-field">
                            <label class="pf-label" for="pfPwConfirm">새 비밀번호 확인</label>
                            <input id="pfPwConfirm" type="password" class="pf-input pf-input--sm"
                                   placeholder="동일하게 입력" autocomplete="new-password">
                        </div>
                    </div>
                </div>
                <div class="pf-actions">
                    <button type="submit" class="pf-btn-save" id="pfPwSaveBtn">변경</button>
                </div>
            </form>
        </div>

        <div class="pf-section">
            <h2 class="pf-section-title">개인 정보</h2>

            <div id="pfAlert" class="pf-alert" style="display:none;"></div>

            <form id="pfForm" novalidate>
                <div class="pf-columns">
                    <!-- 왼쪽: 기본 정보 -->
                    <div class="pf-col">
                        <div class="pf-field">
                            <label class="pf-label" for="pfName">이름</label>
                            <input id="pfName" name="name" type="text" class="pf-input pf-input--sm"
                                   placeholder="홍길동" maxlength="100">
                        </div>
                        <div class="pf-field">
                            <label class="pf-label" for="pfPhone">연락처</label>
                            <input id="pfPhone" name="phone" type="tel" class="pf-input pf-input--sm"
                                   placeholder="010-0000-0000" maxlength="30">
                        </div>
                    </div>

                    <!-- 오른쪽: 주소 -->
                    <div class="pf-col pf-col--address">
                        <div class="pf-field">
                            <label class="pf-label" for="pfZipcode">주소</label>
                            <div class="pf-zipcode-row">
                                <input id="pfZipcode" name="zipcode" type="text" class="pf-input pf-input--sm"
                                       placeholder="우편번호" maxlength="6" readonly>
                                <button type="button" class="pf-btn-zip" onclick="openPostcode()">우편번호 검색</button>
                            </div>
                            <input id="pfAddress" name="address" type="text" class="pf-input"
                                   placeholder="도로명 주소" maxlength="255" readonly>
                            <input id="pfAddressDetail" name="address_detail" type="text" class="pf-input"
                                   placeholder="상세 주소 (동/호수 등)" maxlength="100">
                        </div>
                    </div>
                </div>

                <div class="pf-actions">
                    <button type="submit" class="pf-btn-save" id="pfSaveBtn">
                        저장
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const token = () => localStorage.getItem('pmok_auth_token');

async function loadProfile() {
    if (!token()) { location.href = '/'; return; }
    document.getElementById('profilePage').style.display = '';

    const res  = await fetch('/src/api/auth/profile.php', {
        headers: { 'Authorization': 'Bearer ' + token() }
    });
    const data = await res.json();
    if (!res.ok) return;

    const u = data.user;
    document.getElementById('pfEmail').textContent        = u.email          || '—';
    const roleMap = { s: '슈퍼', m: '관리자', a: '작가', u: '회원' };
    const badge = document.getElementById('pfRoleBadge');
    badge.textContent  = roleMap[u.role] || u.role || '';
    badge.dataset.role = u.role || 'u';
    document.getElementById('pfName').value               = u.name           || '';
    document.getElementById('pfPhone').value              = u.phone          || '';
    document.getElementById('pfZipcode').value            = u.zipcode        || '';
    document.getElementById('pfAddress').value            = u.address        || '';
    document.getElementById('pfAddressDetail').value      = u.address_detail || '';
}

document.getElementById('pfForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const alert = document.getElementById('pfAlert');
    const btn   = document.getElementById('pfSaveBtn');

    alert.style.display = 'none';
    btn.disabled = true;
    btn.textContent = '저장 중…';

    const body = {
        name:           document.getElementById('pfName').value.trim(),
        phone:          document.getElementById('pfPhone').value.trim(),
        zipcode:        document.getElementById('pfZipcode').value.trim(),
        address:        document.getElementById('pfAddress').value.trim(),
        address_detail: document.getElementById('pfAddressDetail').value.trim(),
    };

    try {
        const res  = await fetch('/src/api/auth/profile.php', {
            method:  'PUT',
            headers: {
                'Content-Type':  'application/json',
                'Authorization': 'Bearer ' + token(),
            },
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

document.getElementById('pfPwForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn  = document.getElementById('pfPwSaveBtn');
    const current = document.getElementById('pfPwCurrent').value;
    const next    = document.getElementById('pfPwNew').value;
    const confirm = document.getElementById('pfPwConfirm').value;

    if (next.length < 6) return showPwAlert('새 비밀번호는 6자 이상이어야 합니다.', 'error');
    if (next !== confirm) return showPwAlert('새 비밀번호가 일치하지 않습니다.', 'error');

    btn.disabled = true;
    btn.textContent = '변경 중…';
    try {
        const res  = await fetch('/src/api/auth/password.php', {
            method:  'PUT',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() },
            body: JSON.stringify({ current, password: next }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || '오류가 발생했습니다.');
        showPwAlert('비밀번호가 변경되었습니다.', 'success');
        document.getElementById('pfPwForm').reset();
    } catch (err) {
        showPwAlert(err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = '변경';
    }
});

function showPwAlert(msg, type) {
    const el = document.getElementById('pfPwAlert');
    el.textContent   = msg;
    el.className     = 'pf-alert pf-alert--' + type;
    el.style.display = '';
    setTimeout(() => { el.style.display = 'none'; }, 3000);
}

function showAlert(msg, type) {
    const el = document.getElementById('pfAlert');
    el.textContent    = msg;
    el.className      = 'pf-alert pf-alert--' + type;
    el.style.display  = '';
    setTimeout(() => { el.style.display = 'none'; }, 3000);
}

function openPostcode() {
    new daum.Postcode({
        oncomplete(data) {
            document.getElementById('pfZipcode').value = data.zonecode;
            document.getElementById('pfAddress').value = data.roadAddress || data.autoRoadAddress || data.jibunAddress;
            document.getElementById('pfAddressDetail').value = '';
            document.getElementById('pfAddressDetail').focus();
        }
    }).open();
}

document.addEventListener('DOMContentLoaded', loadProfile);
window.addEventListener('pmokAuthChanged', () => {
    document.getElementById('profileAuthWall').style.display = 'none';
    loadProfile();
});
</script>

</body>
</html>
