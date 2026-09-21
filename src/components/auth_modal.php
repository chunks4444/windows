<?php
// 회원가입 / 로그인 모달 컴포넌트
// nav.php 에서 include 됨
require_once __DIR__ . '/../lib/i18n.php';
?>
<!-- 관리자 대리 로그인 중 표시 배너 — localStorage에 원래 관리자 세션이 보관되어 있을 때만
     (즉 대리 로그인을 시작한 그 관리자 브라우저에서만) 보인다. 대상 회원 본인 화면·계정 데이터에는
     아무 흔적도 남지 않는다(last_login_at 갱신 안 함, page_views 접속통계 기록 제외 — impersonate.php의
     JWT 'imp' 클레임을 logger.php가 감지해서 건너뜀, 회원 쪽 localStorage/쿠키 무관). -->
<div id="pmImpersonateBar" style="display:none;position:fixed;top:0;left:0;right:0;z-index:2000;height:26px;background:var(--danger);color:#fff;align-items:center;justify-content:center;gap:10px;font-size:11px;font-weight:600;">
    <span><i class="bi bi-incognito"></i> 대리 로그인 중 — <span id="pmImpersonateEmail"></span></span>
    <button onclick="endImpersonation()" style="background:#fff;color:var(--danger);border:none;border-radius:4px;padding:1px 8px;font-size:10px;font-weight:700;cursor:pointer;">관리자로 복귀</button>
</div>

<!-- AUTH MODAL -->
<div class="modal fade" id="authModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered auth-modal-dialog">
        <div class="modal-content border-0 auth-modal-content">

            <button type="button" class="btn-close auth-modal-close" data-bs-dismiss="modal" aria-label="<?= htmlspecialchars(t('auth_close')) ?>"></button>

            <!-- 좌측: 로고 패널 -->
            <div class="auth-modal-brand">
                <img src="/src/assets/logo.svg"
                     alt="평목" class="auth-modal-brand-logo">
                <p class="auth-modal-brand-copy"><?= t('auth_brand_copy') ?></p>
            </div>

            <!-- 우측: 폼 패널 -->
            <div class="auth-modal-form-panel">

            <!-- 탭 헤더 -->
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div id="authTabsRow">
                    <button class="auth-tab active" id="tabLogin" onclick="authSwitchTab('login')"><?= htmlspecialchars(t('nav_login')) ?></button>
                    <button class="auth-tab" id="tabRegister" onclick="authSwitchTab('register')"><?= htmlspecialchars(t('auth_tab_register')) ?></button>
                </div>
                <div id="authBackRow" style="display:none;width:100%;">
                    <button type="button" onclick="authSwitchTab('login')">&#8592; <?= htmlspecialchars(t('auth_back_to_login')) ?></button>
                </div>
            </div>

            <div class="modal-body px-4 pt-4 pb-4">
                <div id="authError" class="auth-notice auth-notice-error" style="display:none;"></div>
                <div id="authSuccess" class="auth-notice auth-notice-success" style="display:none;"></div>

                <!-- 약관 동의 (회원가입 탭에서만 노출, 소셜 버튼도 이걸로 잠금) -->
                <label class="auth-consent" id="regAgreeRow" style="display:none;">
                    <input type="checkbox" id="regAgree" required form="formRegister" onchange="updateSnsLock()">
                    <?php
                    // 약관·방침 본문은 한국어 원문이 법적 기준이라 번역하지 않고 링크도 한글 페이지로 둔다.
                    $termsLink   = '<a href="/terms" target="_blank" rel="noopener">' . htmlspecialchars(t('auth_terms')) . '</a>';
                    $privacyLink = '<a href="/privacy" target="_blank" rel="noopener">' . htmlspecialchars(t('auth_privacy')) . '</a>';
                    ?>
                    <span><?= sprintf(t('auth_consent'), $termsLink, $privacyLink) ?></span>
                </label>

                <!-- SNS 로그인 버튼 -->
                <div id="formSns">
                    <div class="auth-sns-btns">
                        <a href="/src/api/auth/oauth/redirect.php?provider=google" class="auth-sns-btn" aria-label="<?= htmlspecialchars(t('auth_sns_google')) ?>" title="<?= htmlspecialchars(t('auth_sns_google')) ?>" onclick="guardSnsClick(event)">
                            <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                        </a>
                        <a href="/src/api/auth/oauth/redirect.php?provider=kakao" class="auth-sns-btn auth-sns-kakao" aria-label="<?= htmlspecialchars(t('auth_sns_kakao')) ?>" title="<?= htmlspecialchars(t('auth_sns_kakao')) ?>" onclick="guardSnsClick(event)">
                            <span class="auth-sns-badge auth-sns-badge-kakao">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="#191919"><path d="M12 3C6.477 3 2 6.477 2 10.8c0 2.7 1.696 5.077 4.273 6.496L5.1 21l4.91-2.618A11.6 11.6 0 0 0 12 18.6c5.523 0 10-3.477 10-7.8S17.523 3 12 3z"/></svg>
                            </span>
                        </a>
                        <a href="/src/api/auth/oauth/redirect.php?provider=naver" class="auth-sns-btn auth-sns-naver" aria-label="<?= htmlspecialchars(t('auth_sns_naver')) ?>" title="<?= htmlspecialchars(t('auth_sns_naver')) ?>" onclick="guardSnsClick(event)">
                            <span class="auth-sns-badge auth-sns-badge-naver">N</span>
                        </a>
                    </div>
                    <div class="auth-divider"><span><?= htmlspecialchars(t('auth_divider')) ?></span></div>
                </div>

                <!-- 로그인 폼 -->
                <form id="formLogin" onsubmit="authLogin(event)">
                    <div class="auth-field">
                        <label><?= htmlspecialchars(t('auth_email')) ?></label>
                        <input type="email" id="loginEmail" placeholder="hello@example.com" required autocomplete="email">
                    </div>
                    <div class="auth-field">
                        <label><?= htmlspecialchars(t('auth_password')) ?></label>
                        <input type="password" id="loginPassword" placeholder="••••••••" required autocomplete="current-password">
                    </div>
                    <button type="submit" class="auth-submit" id="btnLogin"><?= htmlspecialchars(t('nav_login')) ?></button>
                    <div class="auth-forgot-link">
                        <button type="button" onclick="authSwitchPanel('forgot')"><?= htmlspecialchars(t('auth_forgot')) ?></button>
                    </div>
                </form>

                <!-- 회원가입 폼 -->
                <form id="formRegister" onsubmit="authRegister(event)" style="display:none;">
                    <div class="auth-field">
                        <label><?= htmlspecialchars(t('auth_email')) ?></label>
                        <input type="email" id="regEmail" placeholder="hello@example.com" required autocomplete="email">
                    </div>
                    <div class="auth-field">
                        <label><?= htmlspecialchars(t('auth_password')) ?></label>
                        <input type="password" id="regPassword" placeholder="<?= htmlspecialchars(t('auth_password_min')) ?>" required autocomplete="new-password" minlength="6">
                    </div>
                    <div class="auth-field">
                        <label><?= htmlspecialchars(t('auth_password_confirm')) ?></label>
                        <input type="password" id="regPassword2" placeholder="<?= htmlspecialchars(t('auth_password_retype')) ?>" required autocomplete="new-password">
                    </div>
                    <button type="submit" class="auth-submit" id="btnRegister"><?= htmlspecialchars(t('auth_tab_register')) ?></button>
                </form>

                <!-- 비밀번호 찾기 폼 -->
                <form id="formForgot" onsubmit="authForgot(event)" style="display:none;">
                    <p class="auth-hint"><?= t('auth_forgot_hint') ?></p>
                    <div class="auth-field">
                        <label><?= htmlspecialchars(t('auth_email')) ?></label>
                        <input type="email" id="forgotEmail" placeholder="hello@example.com" required autocomplete="email">
                    </div>
                    <button type="submit" class="auth-submit" id="btnForgot"><?= htmlspecialchars(t('auth_btn_forgot')) ?></button>
                </form>

                <!-- 비밀번호 재설정 폼 (URL에 ?reset=TOKEN 있을 때) -->
                <form id="formReset" onsubmit="authReset(event)" style="display:none;">
                    <p class="auth-reset-hint"><?= htmlspecialchars(t('auth_reset_hint')) ?></p>
                    <input type="hidden" id="resetToken">
                    <div class="auth-field">
                        <label><?= htmlspecialchars(t('auth_new_password')) ?></label>
                        <input type="password" id="resetPassword" placeholder="<?= htmlspecialchars(t('auth_password_min')) ?>" required autocomplete="new-password" minlength="6">
                    </div>
                    <div class="auth-field">
                        <label><?= htmlspecialchars(t('auth_password_confirm')) ?></label>
                        <input type="password" id="resetPassword2" placeholder="<?= htmlspecialchars(t('auth_password_retype')) ?>" required autocomplete="new-password">
                    </div>
                    <button type="submit" class="auth-submit" id="btnReset"><?= htmlspecialchars(t('auth_btn_reset')) ?></button>
                </form>
            </div>

            </div>
        </div>
    </div>
</div>

<!-- WELCOME MODAL -->
<div class="modal fade" id="welcomeModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content border-0" style="border-radius:16px;box-shadow:0 8px 16px rgba(var(--text-rgb), 0.06),0 24px 64px rgba(var(--text-rgb), 0.14);overflow:hidden;text-align:center;">
            <div class="modal-body px-5 py-5">
                <div style="font-size:40px;margin-bottom:20px;">🎉</div>
                <h4 style="font-family:'Pretendard','Noto Sans KR','Apple SD Gothic Neo','Malgun Gothic',sans-serif;font-size:20px;font-weight:800;letter-spacing:-0.5px;margin-bottom:10px;"><?= htmlspecialchars(t('auth_welcome_title')) ?></h4>
                <p id="welcomeEmail" style="font-size:13px;color:var(--text-muted);margin-bottom:6px;"></p>
                <p style="font-size:13px;color:var(--accent-hover);line-height:1.8;margin-bottom:32px;"><?= t('auth_welcome_body') ?></p>
                <button class="auth-submit" onclick="welcomeGo()" style="max-width:240px;margin:0 auto;"><?= htmlspecialchars(t('auth_welcome_btn')) ?></button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../lib/meta.php'; ?>
<?php css_tag('/src/css/auth_modal.css'); ?>

<script>
const AUTH_TOKEN_KEY = 'pmok_auth_token';
const AUTH_USER_KEY  = 'pmok_auth_user';
// JS 메시지와 이동 경로도 현재 언어에 맞춰 PHP에서 주입.
// API는 URL에 /en/ 접두사가 없어 언어를 알 수 없으므로 요청 헤더(X-Pmok-Lang)로 알려준다.
const AUTH_LANG = <?= json_encode(current_lang()) ?>;
const AUTH_T = <?= json_encode([
    'agree'        => t('auth_msg_agree'),
    'loginFail'    => t('auth_msg_login_fail'),
    'registerFail' => t('auth_msg_register_fail'),
    'serverError'  => t('auth_msg_server_error'),
    'pwMismatch'   => t('auth_msg_pw_mismatch'),
    'error'        => t('auth_msg_error'),
    'resetSent'    => t('auth_msg_reset_sent'),
    'pwChanged'    => t('auth_msg_pw_changed'),
    'homeUrl'      => lang_href('/'),
    'dashboardUrl' => lang_href('/mypage/dashboard'),
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const AUTH_HEADERS = { 'Content-Type': 'application/json', 'X-Pmok-Lang': AUTH_LANG };

function authSwitchPanel(panel) {
    document.getElementById('formLogin').style.display    = panel === 'login'    ? '' : 'none';
    document.getElementById('formRegister').style.display = panel === 'register' ? '' : 'none';
    document.getElementById('formForgot').style.display   = panel === 'forgot'   ? '' : 'none';
    document.getElementById('formReset').style.display    = panel === 'reset'    ? '' : 'none';
    const isTab = panel === 'login' || panel === 'register';
    document.getElementById('authTabsRow').style.display = isTab ? '' : 'none';
    document.getElementById('authBackRow').style.display = isTab ? 'none' : 'flex';
    document.getElementById('formSns').style.display     = isTab ? '' : 'none';
    document.getElementById('regAgreeRow').style.display = panel === 'register' ? '' : 'none';
    authHideError();
    authHideSuccess();
    updateSnsLock();
}

function updateSnsLock() {
    const registerActive = document.getElementById('formRegister').style.display !== 'none';
    const locked = registerActive && !document.getElementById('regAgree').checked;
    document.getElementById('formSns').classList.toggle('auth-sns-locked', locked);
}

function guardSnsClick(e) {
    if (document.getElementById('formSns').classList.contains('auth-sns-locked')) {
        e.preventDefault();
        authShowError(AUTH_T.agree);
        document.getElementById('regAgree').focus();
    }
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

// 로그인이 필요한 동작을 호출하는 공용 헬퍼.
// 이미 로그인 상태면 즉시 onLoggedIn 실행, 아니면 로그인 모달을 띄우고
// 로그인 성공 시 같은 자리에서 onLoggedIn을 이어서 실행한다 (페이지 이동 없음).
function pmokRequireAuth(onLoggedIn) {
    if (authGetToken()) { onLoggedIn(); return true; }
    window.__pmokAuthSuccessCallback = onLoggedIn;
    try { sessionStorage.setItem('pmok_return_url', location.href); } catch (e) {}
    var el = document.getElementById('authModal');
    if (el && window.bootstrap) bootstrap.Modal.getOrCreateInstance(el).show();
    return false;
}

// 로그인 성공 직후 공통 후처리: 대기 중인 동작이 있으면 그걸 이어서 실행,
// 없고 저장된 복귀 URL이 있으면 그 페이지로 이동, 그 외(직접 로그인 클릭)엔 대시보드로 이동.
function pmokAfterLogin() {
    var cb = window.__pmokAuthSuccessCallback;
    window.__pmokAuthSuccessCallback = null;
    if (cb) { cb(); return; }
    var returnUrl = null;
    try {
        returnUrl = sessionStorage.getItem('pmok_return_url');
        sessionStorage.removeItem('pmok_return_url');
    } catch (e) {}
    location.href = returnUrl || AUTH_T.dashboardUrl;
}

async function authLogin(e) {
    e.preventDefault();
    authHideError();
    const btn = document.getElementById('btnLogin');
    btn.disabled = true;
    try {
        const res = await fetch('/src/api/auth/login.php', {
            method: 'POST',
            headers: AUTH_HEADERS,
            body: JSON.stringify({
                email:    document.getElementById('loginEmail').value,
                password: document.getElementById('loginPassword').value,
            }),
        });
        const data = await res.json();
        if (!res.ok) { authShowError(data.error || AUTH_T.loginFail); return; }
        authSaveSession(data.token, data.user);
        bootstrap.Modal.getInstance(document.getElementById('authModal')).hide();
        authUpdateNav(true);
        window.dispatchEvent(new CustomEvent('pmokAuthChanged'));
        pmokAfterLogin();
    } catch {
        authShowError(AUTH_T.serverError);
    } finally {
        btn.disabled = false;
    }
}

async function authRegister(e) {
    e.preventDefault();
    authHideError();
    const pw  = document.getElementById('regPassword').value;
    const pw2 = document.getElementById('regPassword2').value;
    if (pw !== pw2) { authShowError(AUTH_T.pwMismatch); return; }
    if (!document.getElementById('regAgree').checked) { authShowError(AUTH_T.agree); return; }
    const btn = document.getElementById('btnRegister');
    btn.disabled = true;
    try {
        const res = await fetch('/src/api/auth/register.php', {
            method: 'POST',
            headers: AUTH_HEADERS,
            body: JSON.stringify({
                email:    document.getElementById('regEmail').value,
                password: pw,
                agree:    true,
            }),
        });
        const data = await res.json();
        if (!res.ok) { authShowError(data.error || AUTH_T.registerFail); return; }
        authSaveSession(data.token, data.user);
        bootstrap.Modal.getInstance(document.getElementById('authModal')).hide();
        authUpdateNav(true);
        window.dispatchEvent(new CustomEvent('pmokAuthChanged'));
        document.getElementById('welcomeEmail').textContent = data.user.email;
        new bootstrap.Modal(document.getElementById('welcomeModal')).show();
    } catch {
        authShowError(AUTH_T.serverError);
    } finally {
        btn.disabled = false;
    }
}

function welcomeGo() {
    bootstrap.Modal.getInstance(document.getElementById('welcomeModal')).hide();
    pmokAfterLogin();
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
    }).finally(() => { location.href = AUTH_T.homeUrl; });
}

function authGetUser() {
    try { return JSON.parse(localStorage.getItem(AUTH_USER_KEY)); } catch { return null; }
}

function authGetToken() {
    return localStorage.getItem(AUTH_TOKEN_KEY);
}

function authUpdateNav(skipStaleCheck) {
    // 로그인/어드민 메뉴 존재 여부 자체는 서버사이드(nav.php)에서 JWT로 이미 결정되어 렌더링됨.
    // 여기서는 이미 그려진 요소의 텍스트/보드 목록만 채우고, 페이지 이동 없이 로그인/로그아웃되는
    // 드문 경우(pmokRequireAuth 콜백)를 위해 존재하는 요소에 한해 표시 상태만 동기화한다.
    let user = authGetUser();
    const loginBtn  = document.getElementById('navLoginBtn');
    const userMenu  = document.getElementById('navUserMenu');
    const userEmail = document.getElementById('navUserEmail');

    // localStorage엔 로그인 정보가 남아있지만 서버는 로그아웃으로 렌더링한 경우
    // (쿠키 만료, JWT 시크릿 교체 등) — stale 데이터이므로 정리한다. 안 그러면
    // 로그인 버튼만 숨겨지고 대체할 사용자 메뉴는 DOM에 없어 메뉴 자체가 사라진다.
    // 단, 로그인/회원가입/OAuth 직후(skipStaleCheck=true)엔 건너뛴다 — 지금 페이지는
    // 로그인 "전"에 서버가 렌더링한 것이라 userMenu가 원래 없고, 그걸 stale로 오인해
    // 방금 저장한 토큰을 스스로 지워버리는 문제가 있었음(로그인 성공 후 홈으로 튕기는 원인).
    if (!skipStaleCheck && user && loginBtn && !userMenu) {
        localStorage.removeItem(AUTH_TOKEN_KEY);
        localStorage.removeItem(AUTH_USER_KEY);
        user = null;
    }

    if (user) {
        if (loginBtn) loginBtn.style.display = 'none';
        if (userMenu) {
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
            loadNavBoards();
        }
    } else {
        if (loginBtn) loginBtn.style.display = '';
        if (userMenu) userMenu.style.display = 'none';
        const boardSection = document.getElementById('navBoardSection');
        const boardList    = document.getElementById('navBoardList');
        if (boardSection) boardSection.style.display = 'none';
        if (boardList)    boardList.innerHTML = '';
    }

    // 드로어 인증 동기화
    const drawerLoginBtn = document.getElementById('drawerLoginBtn');
    const drawerUserMenu = document.getElementById('drawerUserMenu');
    const drawerUserEmail = document.getElementById('drawerUserEmail');
    if (user) {
        if (drawerLoginBtn) drawerLoginBtn.style.display = 'none';
        if (drawerUserMenu) {
            drawerUserMenu.style.display = '';
            if (drawerUserEmail) drawerUserEmail.textContent = user.email;
        }
    } else {
        if (drawerLoginBtn) drawerLoginBtn.style.display = '';
        if (drawerUserMenu) drawerUserMenu.style.display = 'none';
    }
}

const IMPERSONATE_ADMIN_KEY = 'pmok_impersonate_admin';

// 대상 회원 계정으로 대리 로그인 시작 — users.js의 impersonateUser()에서 호출.
// 원래 관리자 세션(token+user)을 별도 키로 보관해뒀다가 복귀 시 그대로 되돌린다.
function startImpersonation(newToken, newUser) {
    const adminToken = authGetToken();
    const adminUser  = authGetUser();
    if (adminToken && adminUser) {
        localStorage.setItem(IMPERSONATE_ADMIN_KEY, JSON.stringify({ token: adminToken, user: adminUser }));
    }
    authSaveSession(newToken, newUser);
}

function checkImpersonationBar() {
    const bar = document.getElementById('pmImpersonateBar');
    if (!bar) return;
    const barHeight = 26;
    const navbar = document.querySelector('.pm-navbar');
    const raw = localStorage.getItem(IMPERSONATE_ADMIN_KEY);
    if (!raw) {
        bar.style.display = 'none';
        document.body.style.paddingTop = '';
        if (navbar) navbar.style.top = '';
        return;
    }
    const user = authGetUser();
    document.getElementById('pmImpersonateEmail').textContent = user?.email || '';
    bar.style.display = 'flex';
    // 배너가 상단바(.pm-navbar, fixed-top)를 그냥 덮어버리지 않도록 그만큼 밀어내림
    if (navbar) navbar.style.top = barHeight + 'px';
    const baseTop = parseInt(getComputedStyle(document.body).paddingTop, 10) || 68;
    document.body.style.paddingTop = (baseTop + barHeight) + 'px';
}

async function endImpersonation() {
    const raw = localStorage.getItem(IMPERSONATE_ADMIN_KEY);
    if (!raw) return;
    let admin;
    try { admin = JSON.parse(raw); } catch { admin = null; }
    if (!admin?.token || !admin?.user) { localStorage.removeItem(IMPERSONATE_ADMIN_KEY); location.href = '/'; return; }
    try {
        await fetch('/src/api/admin/impersonate_end.php', {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + admin.token },
        });
    } catch {}
    authSaveSession(admin.token, admin.user);
    localStorage.removeItem(IMPERSONATE_ADMIN_KEY);
    location.href = '/src/admin/users.php';
}

async function loadNavBoards() {
    const boardSection = document.getElementById('navBoardSection');
    const boardList    = document.getElementById('navBoardList');
    const drawerBoardSection = document.getElementById('drawerBoardSection');
    const drawerBoardList    = document.getElementById('drawerBoardList');
    const token = authGetToken();
    if (!token) return;
    try {
        const res  = await fetch('/src/api/boards/list.php', {
            headers: { 'Authorization': 'Bearer ' + token },
        });
        const data = await res.json();
        const boards = data.boards || [];

        // PC 유저 메뉴
        if (boardSection && boardList) {
            if (!boards.length) {
                boardSection.style.display = 'none';
                boardList.innerHTML = '';
            } else {
                boardSection.style.display = '';
                boardList.innerHTML = boards.map(b =>
                    `<li><a class="dropdown-item d-flex align-items-center gap-2" href="/mypage/dashboard?board=${b.id}">
                        <i class="bi bi-collection" style="font-size:14px;"></i>
                        <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${_escHtml(b.name)}</span>
                        <span style="font-size:10px;color:var(--text-muted);flex-shrink:0;">${b.item_count}</span>
                    </a></li>`
                ).join('');
            }
        }

        // 드로어
        if (drawerBoardSection && drawerBoardList) {
            if (!boards.length) {
                drawerBoardSection.style.display = 'none';
                drawerBoardList.innerHTML = '';
            } else {
                drawerBoardSection.style.display = '';
                drawerBoardList.innerHTML = boards.map(b =>
                    `<a class="pm-dw-link" href="/mypage/dashboard?board=${b.id}">
                        <i class="bi bi-collection"></i>
                        <span>${_escHtml(b.name)}</span>
                    </a>`
                ).join('');
            }
        }
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
            headers: AUTH_HEADERS,
            body: JSON.stringify({ email: document.getElementById('forgotEmail').value }),
        });
        const data = await res.json();
        if (!res.ok) { authShowError(data.error || AUTH_T.error); return; }
        document.getElementById('formForgot').style.display = 'none';
        authShowSuccess(AUTH_T.resetSent);
    } catch {
        authShowError(AUTH_T.serverError);
    } finally {
        btn.disabled = false;
    }
}

async function authReset(e) {
    e.preventDefault();
    authHideError();
    const pw  = document.getElementById('resetPassword').value;
    const pw2 = document.getElementById('resetPassword2').value;
    if (pw !== pw2) { authShowError(AUTH_T.pwMismatch); return; }
    const btn = document.getElementById('btnReset');
    btn.disabled = true;
    try {
        const res = await fetch('/src/api/auth/reset.php', {
            method: 'POST',
            headers: AUTH_HEADERS,
            body: JSON.stringify({
                token:    document.getElementById('resetToken').value,
                password: pw,
            }),
        });
        const data = await res.json();
        if (!res.ok) { authShowError(data.error || AUTH_T.error); return; }
        document.getElementById('formReset').style.display = 'none';
        authShowSuccess(AUTH_T.pwChanged);
        history.replaceState(null, '', location.pathname);
        setTimeout(() => authSwitchTab('login'), 2000);
    } catch {
        authShowError(AUTH_T.serverError);
    } finally {
        btn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    authUpdateNav();
    checkImpersonationBar();

    const params = new URLSearchParams(location.search);

    const oauthToken = params.get('oauth_token');
    if (oauthToken) {
        try {
            const raw     = oauthToken.split('.')[1];
            const b64     = raw.replace(/-/g, '+').replace(/_/g, '/');
            const padded  = b64.padEnd(b64.length + (4 - b64.length % 4) % 4, '=');
            const payload = JSON.parse(atob(padded));
            authSaveSession(oauthToken, { id: payload.sub, email: payload.email, role: payload.role });
        } catch (e) { console.error('oauth token parse error:', e); }
        // 토큰 파싱이 실패해도(위 try 안 값 형식 문제 등) 서버는 이미 로그인 쿠키를 내려준 상태이므로,
        // 홈에 멈춰있지 않도록 URL 정리와 재이동은 항상 실행한다.
        authUpdateNav(true);
        history.replaceState(null, '', location.pathname);
        window.dispatchEvent(new CustomEvent('pmokAuthChanged'));
        pmokAfterLogin();
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
            if (!authGetToken()) {
                // 로그인 없이 모달을 닫은 경우: 남아있던 복귀 URL/콜백이 나중의 다른 로그인
                // 시도에 잘못 쓰이지 않도록 같이 정리한다 (예전엔 이걸 안 지워서, 한 번 로그인을
                // 취소한 뒤 나중에 다른 경로로 로그인해도 그때의 옛 복귀 URL로 튕기는 문제가 있었음).
                window.__pmokAuthSuccessCallback = null;
                try { sessionStorage.removeItem('pmok_return_url'); } catch (e) {}
                location.href = AUTH_T.homeUrl;
            }
        });
    }
});
</script>
