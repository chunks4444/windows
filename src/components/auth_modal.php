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
                <div class="d-flex gap-3 w-100">
                    <button class="auth-tab active" id="tabLogin" onclick="authSwitchTab('login')">로그인</button>
                    <button class="auth-tab" id="tabRegister" onclick="authSwitchTab('register')">회원가입</button>
                </div>
                <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" style="font-size:11px;"></button>
            </div>

            <div class="modal-body px-4 pt-3 pb-4">
                <div id="authError" class="auth-error" style="display:none;"></div>

                <!-- 로그인 폼 -->
                <form id="formLogin" onsubmit="authLogin(event)">
                    <div class="auth-field">
                        <label>이메일</label>
                        <input type="email" id="loginEmail" placeholder="이메일 입력" required autocomplete="email">
                    </div>
                    <div class="auth-field">
                        <label>비밀번호</label>
                        <input type="password" id="loginPassword" placeholder="비밀번호 입력" required autocomplete="current-password">
                    </div>
                    <button type="submit" class="auth-submit" id="btnLogin">로그인</button>
                </form>

                <!-- 회원가입 폼 -->
                <form id="formRegister" onsubmit="authRegister(event)" style="display:none;">
                    <div class="auth-field">
                        <label>이메일</label>
                        <input type="email" id="regEmail" placeholder="이메일 입력" required autocomplete="email">
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
            </div>

        </div>
    </div>
</div>

<link rel="stylesheet" href="/src/css/auth_modal.css">

<script>
const AUTH_TOKEN_KEY = 'pmok_auth_token';
const AUTH_USER_KEY  = 'pmok_auth_user';

function authSwitchTab(tab) {
    document.getElementById('formLogin').style.display    = tab === 'login'    ? '' : 'none';
    document.getElementById('formRegister').style.display = tab === 'register' ? '' : 'none';
    document.getElementById('tabLogin').classList.toggle('active', tab === 'login');
    document.getElementById('tabRegister').classList.toggle('active', tab === 'register');
    authHideError();
}

function authShowError(msg) {
    const el = document.getElementById('authError');
    el.textContent = msg;
    el.style.display = '';
}
function authHideError() {
    document.getElementById('authError').style.display = 'none';
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
        if (!window.__pmokGuardedPage) location.href = '/src/mypage/dashboard.php';
    } catch {
        authShowError('서버 오류가 발생했습니다.');
    } finally {
        btn.disabled = false;
    }
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
        const adminMeta       = document.getElementById('navAdminMeta');
        const adminSpaceCards = document.getElementById('navAdminSpaceCards');
        if (adminMeta)       adminMeta.style.display       = isSuper ? '' : 'none';
        if (adminSpaceCards) adminSpaceCards.style.display = isSuper ? '' : 'none';
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

document.addEventListener('DOMContentLoaded', function () {
    authUpdateNav();
    var el = document.getElementById('authModal');
    if (el) {
        el.addEventListener('hidden.bs.modal', function () {
            if (!authGetToken()) location.href = '/';
        });
    }
});
</script>
