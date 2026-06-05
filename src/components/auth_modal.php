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

<style>
#authModal * { font-family: 'Noto Sans KR', -apple-system, 'Malgun Gothic', sans-serif; }
.auth-tab {
    background: none; border: none; padding: 0 0 8px;
    font-size: 14px; font-weight: 700; color: #aaa;
    border-bottom: 2px solid transparent; cursor: pointer;
    transition: color .15s, border-color .15s;
}
.auth-tab.active { color: #111; border-bottom-color: #111; }
.auth-field { display: flex; flex-direction: column; gap: 5px; margin-bottom: 12px; }
.auth-field label { font-size: 11px; font-weight: 600; color: #666; letter-spacing: 0.02em; }
.auth-field input {
    border: 1px solid #e0e0e0; border-radius: 0;
    padding: 9px 12px; font-size: 13px; outline: none;
    font-family: 'Noto Sans KR', -apple-system, 'Malgun Gothic', sans-serif;
    transition: border-color .15s;
}
.auth-field input:focus { border-color: #111; }
.auth-submit {
    width: 100%; height: 38px; margin-top: 4px;
    background: #3A8C82; color: #fff; border: none;
    border-radius: 6px;
    font-size: 12px; font-weight: 600; cursor: pointer;
    font-family: 'Noto Sans KR', -apple-system, 'Malgun Gothic', sans-serif;
    letter-spacing: -0.2px;
    display: flex; align-items: center; justify-content: center; gap: 6px;
    transition: background .12s;
}
.auth-submit:hover { background: #2F7169; }
.auth-submit:disabled { background: #999; cursor: not-allowed; }
.auth-error {
    background: #fff3f0; border-left: 3px solid #e05218;
    padding: 9px 12px; font-size: 12px; color: #c0392b;
    margin-bottom: 14px;
}
</style>

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
        if (!window.__pmokGuardedPage) location.href = '/dashboard.php';
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
        if (!window.__pmokGuardedPage) location.href = '/dashboard.php';
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
    document.cookie = 'pmok_auth=' + token + '; path=/; SameSite=Lax';
}

function authLogout() {
    localStorage.removeItem(AUTH_TOKEN_KEY);
    localStorage.removeItem(AUTH_USER_KEY);
    document.cookie = 'pmok_auth=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT';
    location.href = '/';
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
        if (adminLink)  adminLink.style.display  = isSuper ? '' : 'none';
        if (adminMenu)  adminMenu.style.display  = isSuper ? '' : 'none';
        if (adminStats) adminStats.style.display = isSuper ? '' : 'none';
    } else {
        loginBtn.style.display = '';
        userMenu.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', authUpdateNav);
</script>
