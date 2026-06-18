<?php
// 회원가입 / 로그인 모달 컴포넌트
// nav.php 에서 include 됨
?>
<!-- AUTH MODAL -->
<div class="modal fade" id="authModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content border-0" style="border-radius:16px;box-shadow:0 8px 16px rgba(0,0,0,0.06), 0 24px 64px rgba(0,0,0,0.14);overflow:hidden;">

            <!-- 탭 헤더 -->
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div id="authTabsRow">
                    <button class="auth-tab active" id="tabLogin" onclick="authSwitchTab('login')">로그인</button>
                    <button class="auth-tab" id="tabRegister" onclick="authSwitchTab('register')">회원가입</button>
                </div>
                <div id="authBackRow" style="display:none;width:100%;">
                    <button type="button" onclick="authSwitchTab('login')">&#8592; 로그인으로</button>
                </div>
                <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" style="font-size:11px;"></button>
            </div>

            <div class="modal-body px-4 pt-3 pb-4">
                <div id="authError" class="auth-notice auth-notice-error" style="display:none;"></div>
                <div id="authSuccess" class="auth-notice auth-notice-success" style="display:none;"></div>

                <!-- SNS 로그인 버튼 -->
                <div id="formSns">
                    <div class="auth-sns-btns">
                        <a href="/src/api/auth/oauth/redirect.php?provider=google" class="auth-sns-btn">
                            <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                            Google로 계속하기
                        </a>
                        <a href="/src/api/auth/oauth/redirect.php?provider=kakao" class="auth-sns-btn auth-sns-kakao">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="#000000"><path d="M12 3C6.477 3 2 6.477 2 10.8c0 2.7 1.696 5.077 4.273 6.496L5.1 21l4.91-2.618A11.6 11.6 0 0 0 12 18.6c5.523 0 10-3.477 10-7.8S17.523 3 12 3z"/></svg>
                            카카오로 계속하기
                        </a>
                        <a href="/src/api/auth/oauth/redirect.php?provider=naver" class="auth-sns-btn auth-sns-naver">
                            <span style="font-weight:800;font-size:14px;line-height:1;">N</span>
                            네이버로 계속하기
                        </a>
                    </div>
                    <div class="auth-divider"><span>또는 이메일로</span></div>
                </div>

                <!-- 로그인 폼 -->
                <form id="formLogin" onsubmit="authLogin(event)">
                    <div class="auth-field">
                        <label>이메일</label>
                        <input type="email" id="loginEmail" placeholder="hello@example.com" required autocomplete="email">
                    </div>
                    <div class="auth-field">
                        <label>비밀번호</label>
                        <input type="password" id="loginPassword" placeholder="••••••••" required autocomplete="current-password">
                    </div>
                    <button type="submit" class="auth-submit" id="btnLogin">로그인</button>
                    <div class="auth-forgot-link">
                        <button type="button" onclick="authSwitchPanel('forgot')">비밀번호를 잊으셨나요?</button>
                    </div>
                </form>

                <!-- 회원가입 폼 -->
                <form id="formRegister" onsubmit="authRegister(event)" style="display:none;">
                    <div class="auth-field">
                        <label>이메일</label>
                        <input type="email" id="regEmail" placeholder="hello@example.com" required autocomplete="email">
                    </div>
                    <div class="auth-field">
                        <label>비밀번호</label>
                        <input type="password" id="regPassword" placeholder="6자 이상" required autocomplete="new-password" minlength="6">
                    </div>
                    <div class="auth-field">
                        <label>비밀번호 확인</label>
                        <input type="password" id="regPassword2" placeholder="비밀번호 재입력" required autocomplete="new-password">
                    </div>
                    <button type="submit" class="auth-submit" id="btnRegister">회원가입</button>
                </form>

                <!-- 비밀번호 찾기 폼 -->
                <form id="formForgot" onsubmit="authForgot(event)" style="display:none;">
                    <p class="auth-hint">가입하신 이메일 주소를 입력하시면<br>비밀번호 재설정 링크를 보내드립니다.</p>
                    <div class="auth-field">
                        <label>이메일</label>
                        <input type="email" id="forgotEmail" placeholder="hello@example.com" required autocomplete="email">
                    </div>
                    <button type="submit" class="auth-submit" id="btnForgot">재설정 메일 발송</button>
                </form>

                <!-- 비밀번호 재설정 폼 (URL에 ?reset=TOKEN 있을 때) -->
                <form id="formReset" onsubmit="authReset(event)" style="display:none;">
                    <p class="auth-reset-hint">새 비밀번호를 입력해 주세요.</p>
                    <input type="hidden" id="resetToken">
                    <div class="auth-field">
                        <label>새 비밀번호</label>
                        <input type="password" id="resetPassword" placeholder="6자 이상" required autocomplete="new-password" minlength="6">
                    </div>
                    <div class="auth-field">
                        <label>비밀번호 확인</label>
                        <input type="password" id="resetPassword2" placeholder="비밀번호 재입력" required autocomplete="new-password">
                    </div>
                    <button type="submit" class="auth-submit" id="btnReset">비밀번호 변경</button>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- WELCOME MODAL -->
<div class="modal fade" id="welcomeModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content border-0" style="border-radius:16px;box-shadow:0 8px 16px rgba(0,0,0,0.06),0 24px 64px rgba(0,0,0,0.14);overflow:hidden;text-align:center;">
            <div class="modal-body px-5 py-5">
                <div style="font-size:40px;margin-bottom:20px;">🎉</div>
                <h4 style="font-family:'Noto Sans KR',sans-serif;font-size:20px;font-weight:800;letter-spacing:-0.5px;margin-bottom:10px;">평목에 오신 것을 환영합니다!</h4>
                <p id="welcomeEmail" style="font-size:13px;color:#888;margin-bottom:6px;"></p>
                <p style="font-size:13px;color:#555;line-height:1.8;margin-bottom:32px;">이제 창호를 직접 설계하고,<br>AI로 공간에 적용된 모습까지 확인해 보세요.</p>
                <button class="auth-submit" onclick="welcomeGo()" style="max-width:240px;margin:0 auto;">스튜디오 시작하기</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../lib/meta.php'; ?>
<?php css_tag('/src/css/auth_modal.css'); ?>

<script>
const AUTH_TOKEN_KEY = 'pmok_auth_token';
const AUTH_USER_KEY  = 'pmok_auth_user';

function authSwitchPanel(panel) {
    document.getElementById('formLogin').style.display    = panel === 'login'    ? '' : 'none';
    document.getElementById('formRegister').style.display = panel === 'register' ? '' : 'none';
    document.getElementById('formForgot').style.display   = panel === 'forgot'   ? '' : 'none';
    document.getElementById('formReset').style.display    = panel === 'reset'    ? '' : 'none';
    const isTab = panel === 'login' || panel === 'register';
    document.getElementById('authTabsRow').style.display = isTab ? '' : 'none';
    document.getElementById('authBackRow').style.display = isTab ? 'none' : 'flex';
    document.getElementById('formSns').style.display     = isTab ? '' : 'none';
    authHideError();
    authHideSuccess();
}

function authSwitchTab(tab) {
    authSwitchPanel(tab);
    document.getElementById('tabLogin').classList.toggle('active', tab === 'login');
    document.getElementById('tabRegister').classList.toggle('active', tab === 'register');
}

function authShowError(msg) {
    const el = document.getElementById('authError');
    el.textContent = msg;
    el.style.display = 'flex';
}
function authHideError() {
    document.getElementById('authError').style.display = 'none';
}
function authShowSuccess(msg) {
    const el = document.getElementById('authSuccess');
    el.textContent = msg;
    el.style.display = 'flex';
}
function authHideSuccess() {
    document.getElementById('authSuccess').style.display = 'none';
}

async function authLogin(e) {
    e.preventDefault();
    authHideError();
    const btn = document.getElementById('btnLogin');
    btn.disabled = true;
    try {
        const res = await fetch('/src/api/auth/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email:    document.getElementById('loginEmail').value,
                password: document.getElementById('loginPassword').value,
            }),
        });
        const data = await res.json();
        if (!res.ok) { authShowError(data.error || '로그인 실패'); return; }
        authSaveSession(data.token, data.user);
        bootstrap.Modal.getInstance(document.getElementById('authModal')).hide();
        authUpdateNav();
        window.dispatchEvent(new CustomEvent('pmokAuthChanged'));
        if (!window.__pmokGuardedPage) location.href = '/src/mypage/dashboard.php';
    } catch {
        authShowError('서버 오류가 발생했습니다.');
    } finally {
        btn.disabled = false;
    }
}

async function authRegister(e) {
    e.preventDefault();
    authHideError();
    const pw  = document.getElementById('regPassword').value;
    const pw2 = document.getElementById('regPassword2').value;
    if (pw !== pw2) { authShowError('비밀번호가 일치하지 않습니다.'); return; }
    const btn = document.getElementById('btnRegister');
    btn.disabled = true;
    try {
        const res = await fetch('/src/api/auth/register.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email:    document.getElementById('regEmail').value,
                password: pw,
            }),
        });
        const data = await res.json();
        if (!res.ok) { authShowError(data.error || '회원가입 실패'); return; }
        authSaveSession(data.token, data.user);
        bootstrap.Modal.getInstance(document.getElementById('authModal')).hide();
        authUpdateNav();
        window.dispatchEvent(new CustomEvent('pmokAuthChanged'));
        document.getElementById('welcomeEmail').textContent = data.user.email;
        new bootstrap.Modal(document.getElementById('welcomeModal')).show();
    } catch {
        authShowError('서버 오류가 발생했습니다.');
    } finally {
        btn.disabled = false;
    }
}

function welcomeGo() {
    bootstrap.Modal.getInstance(document.getElementById('welcomeModal')).hide();
    location.href = '/src/mypage/dashboard.php';
}

function authSaveSession(token, user) {
    localStorage.setItem('pmok_last_login', Date.now().toString());
    localStorage.setItem(AUTH_TOKEN_KEY, token);
    localStorage.setItem(AUTH_USER_KEY, JSON.stringify(user));
}

function authLogout() {
    localStorage.removeItem(AUTH_TOKEN_KEY);
    localStorage.removeItem(AUTH_USER_KEY);
    fetch('/src/api/auth/logout.php', {
        method: 'POST',
        keepalive: true,
    }).finally(() => { location.href = '/'; });
}

function authGetUser() {
    try { return JSON.parse(localStorage.getItem(AUTH_USER_KEY)); } catch { return null; }
}

function authGetToken() {
    return localStorage.getItem(AUTH_TOKEN_KEY);
}

function authUpdateNav() {
    const user = authGetUser();
    const loginBtn  = document.getElementById('navLoginBtn');
    const userMenu  = document.getElementById('navUserMenu');
    const userEmail = document.getElementById('navUserEmail');
    if (!loginBtn || !userMenu) return;
    if (user) {
        loginBtn.style.display = 'none';
        userMenu.style.display = '';
        if (userEmail) userEmail.textContent = user.email;
        const lastLogin = document.getElementById('navLastLogin');
        if (lastLogin) {
            const ts = parseInt(localStorage.getItem('pmok_last_login') || '0');
            if (ts) {
                const d = new Date(ts);
                const pad = n => String(n).padStart(2, '0');
                lastLogin.textContent = `${d.getFullYear()}.${pad(d.getMonth()+1)}.${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
            }
        }
        const isSuper = user.role === 's';
        const adminLink  = document.getElementById('navAdminLink');
        const adminMenu  = document.getElementById('navAdminMenu');
        const adminStats = document.getElementById('navAdminStats');
        const adminLib   = document.getElementById('navAdminLib');
        if (adminLink)  adminLink.style.display  = isSuper ? '' : 'none';
        if (adminMenu)  adminMenu.style.display  = isSuper ? '' : 'none';
        if (adminStats) adminStats.style.display = isSuper ? '' : 'none';
        if (adminLib)   adminLib.style.display   = isSuper ? '' : 'none';
        const adminMeta        = document.getElementById('navAdminMeta');
        const adminSpaceCards  = document.getElementById('navAdminSpaceCards');
        const adminHeroSlides  = document.getElementById('navAdminHeroSlides');
        const adminWoodTypes   = document.getElementById('navAdminWoodTypes');
        const adminOauth       = document.getElementById('navAdminOauth');
        const adminColors      = document.getElementById('navAdminColors');
        if (adminMeta)        adminMeta.style.display        = isSuper ? '' : 'none';
        if (adminSpaceCards)  adminSpaceCards.style.display  = isSuper ? '' : 'none';
        if (adminHeroSlides)  adminHeroSlides.style.display  = isSuper ? '' : 'none';
        if (adminWoodTypes)   adminWoodTypes.style.display   = isSuper ? '' : 'none';
        if (adminOauth)       adminOauth.style.display       = isSuper ? '' : 'none';
        if (adminColors)      adminColors.style.display      = isSuper ? '' : 'none';
        const adminFaq         = document.getElementById('navAdminFaq');
        if (adminFaq)         adminFaq.style.display         = isSuper ? '' : 'none';
        const adminWorks       = document.getElementById('navAdminWorks');
        if (adminWorks)       adminWorks.style.display       = isSuper ? '' : 'none';
        loadNavBoards();
    } else {
        loginBtn.style.display = '';
        userMenu.style.display = 'none';
        const boardSection = document.getElementById('navBoardSection');
        const boardList    = document.getElementById('navBoardList');
        if (boardSection) boardSection.style.display = 'none';
        if (boardList)    boardList.innerHTML = '';
    }
}

async function loadNavBoards() {
    const boardSection = document.getElementById('navBoardSection');
    const boardList    = document.getElementById('navBoardList');
    if (!boardSection || !boardList) return;
    const token = authGetToken();
    if (!token) return;
    try {
        const res  = await fetch('/src/api/boards/list.php', {
            headers: { 'Authorization': 'Bearer ' + token },
        });
        const data = await res.json();
        const boards = data.boards || [];
        if (!boards.length) {
            boardSection.style.display = 'none';
            boardList.innerHTML = '';
            return;
        }
        boardSection.style.display = '';
        boardList.innerHTML = boards.map(b =>
            `<li><a class="dropdown-item d-flex align-items-center gap-2" href="/src/mypage/dashboard.php?board=${b.id}">
                <i class="bi bi-collection" style="font-size:14px;"></i>
                <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${_escHtml(b.name)}</span>
                <span style="font-size:10px;color:#aaa;flex-shrink:0;">${b.item_count}</span>
            </a></li>`
        ).join('');
    } catch {}
}

function _escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

async function authForgot(e) {
    e.preventDefault();
    authHideError();
    const btn = document.getElementById('btnForgot');
    btn.disabled = true;
    try {
        const res = await fetch('/src/api/auth/forgot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: document.getElementById('forgotEmail').value }),
        });
        const data = await res.json();
        if (!res.ok) { authShowError(data.error || '오류가 발생했습니다.'); return; }
        document.getElementById('formForgot').style.display = 'none';
        authShowSuccess('재설정 링크를 이메일로 보냈습니다. 메일함을 확인해 주세요.');
    } catch {
        authShowError('서버 오류가 발생했습니다.');
    } finally {
        btn.disabled = false;
    }
}

async function authReset(e) {
    e.preventDefault();
    authHideError();
    const pw  = document.getElementById('resetPassword').value;
    const pw2 = document.getElementById('resetPassword2').value;
    if (pw !== pw2) { authShowError('비밀번호가 일치하지 않습니다.'); return; }
    const btn = document.getElementById('btnReset');
    btn.disabled = true;
    try {
        const res = await fetch('/src/api/auth/reset.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                token:    document.getElementById('resetToken').value,
                password: pw,
            }),
        });
        const data = await res.json();
        if (!res.ok) { authShowError(data.error || '오류가 발생했습니다.'); return; }
        document.getElementById('formReset').style.display = 'none';
        authShowSuccess('비밀번호가 변경되었습니다. 다시 로그인해 주세요.');
        history.replaceState(null, '', location.pathname);
        setTimeout(() => authSwitchTab('login'), 2000);
    } catch {
        authShowError('서버 오류가 발생했습니다.');
    } finally {
        btn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    authUpdateNav();

    const params = new URLSearchParams(location.search);

    const oauthToken = params.get('oauth_token');
    if (oauthToken) {
        try {
            const raw     = oauthToken.split('.')[1];
            const b64     = raw.replace(/-/g, '+').replace(/_/g, '/');
            const padded  = b64.padEnd(b64.length + (4 - b64.length % 4) % 4, '=');
            const payload = JSON.parse(atob(padded));
            authSaveSession(oauthToken, { id: payload.sub, email: payload.email, role: payload.role });
            authUpdateNav();
            history.replaceState(null, '', location.pathname);
            window.dispatchEvent(new CustomEvent('pmokAuthChanged'));
            if (!window.__pmokGuardedPage) location.href = '/src/mypage/dashboard.php';
        } catch {}
    }

    const oauthError = params.get('oauth_error');
    if (oauthError) {
        var modal = new bootstrap.Modal(document.getElementById('authModal'));
        modal.show();
        authShowError(decodeURIComponent(oauthError));
        history.replaceState(null, '', location.pathname);
    }

    const resetToken = params.get('reset');
    if (resetToken) {
        document.getElementById('resetToken').value = resetToken;
        var modal = new bootstrap.Modal(document.getElementById('authModal'));
        modal.show();
        authSwitchPanel('reset');
    }

    var el = document.getElementById('authModal');
    if (el) {
        el.addEventListener('hidden.bs.modal', function () {
            if (!authGetToken()) location.href = '/';
        });
    }
});
</script>
