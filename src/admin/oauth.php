<?php header('Content-Type: text/html; charset=UTF-8'); ?>
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
    <style>
        .oauth-card {
            background: #fff;
            border: 1px solid #eef1f0;
            border-radius: 12px;
            padding: 24px 28px;
            margin-bottom: 16px;
        }
        .oauth-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
        }
        .oauth-card-logo {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 800;
            flex-shrink: 0;
        }
        .oauth-logo-google { background: #fff; border: 1px solid #e0e0e0; }
        .oauth-logo-kakao  { background: #FEE500; color: #191919; }
        .oauth-logo-naver  { background: #03C75A; color: #fff; }
        .oauth-card-title  { font-size: 15px; font-weight: 700; color: #1A1F1E; }
        .oauth-callback    { font-size: 11px; color: #97A8A6; margin-bottom: 16px; background: #F5F8F7; padding: 8px 12px; border-radius: 6px; font-family: monospace; word-break: break-all; }
        .oauth-field       { margin-bottom: 12px; }
        .oauth-field label { font-size: 11px; font-weight: 700; color: #7A8C89; letter-spacing: 0.05em; text-transform: uppercase; display: block; margin-bottom: 5px; }
        .oauth-field input { width: 100%; border: 1.5px solid #E4EDEA; border-radius: 8px; padding: 9px 12px; font-size: 13px; outline: none; font-family: monospace; color: #1A1F1E; transition: border-color .15s; }
        .oauth-field input:focus { border-color: #3A8C82; }
        .oauth-save { height: 36px; padding: 0 18px; background: #3A8C82; color: #fff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background .15s; }
        .oauth-save:hover { background: #2F7169; }
        .oauth-save:disabled { background: #B8C8C4; cursor: not-allowed; }
        .oauth-status { font-size: 12px; margin-left: 10px; }
        .oauth-status.ok  { color: #2d7a72; }
        .oauth-status.err { color: #c0392b; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="oauthPage" style="display:none;">
    <div class="db-header">
        <h1 class="db-title">SNS 로그인 설정</h1>
    </div>

    <div style="max-width:600px;margin:0 auto;">

        <!-- Google -->
        <div class="oauth-card">
            <div class="oauth-card-header">
                <div class="oauth-card-logo oauth-logo-google">
                    <svg width="16" height="16" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                </div>
                <span class="oauth-card-title">Google</span>
            </div>
            <div class="oauth-callback">Redirect URI: https://windows.pyeongmok.com/src/api/auth/oauth/callback.php?provider=google</div>
            <div class="oauth-field">
                <label>Client ID</label>
                <input type="text" id="google_client_id" placeholder="123456789-xxx.apps.googleusercontent.com">
            </div>
            <div class="oauth-field">
                <label>Client Secret</label>
                <input type="password" id="google_client_secret" placeholder="GOCSPX-…">
            </div>
            <button class="oauth-save" onclick="saveProvider('google')">저장</button>
            <span class="oauth-status" id="google_status"></span>
        </div>

        <!-- 카카오 -->
        <div class="oauth-card">
            <div class="oauth-card-header">
                <div class="oauth-card-logo oauth-logo-kakao">K</div>
                <span class="oauth-card-title">카카오</span>
            </div>
            <div class="oauth-callback">Redirect URI: https://windows.pyeongmok.com/src/api/auth/oauth/callback.php?provider=kakao</div>
            <div class="oauth-field">
                <label>REST API 키 (Client ID)</label>
                <input type="text" id="kakao_client_id" placeholder="abc123def456…">
            </div>
            <div class="oauth-field">
                <label>Client Secret <span style="font-weight:400;text-transform:none;letter-spacing:0;">(선택)</span></label>
                <input type="password" id="kakao_client_secret" placeholder="선택 사항">
            </div>
            <button class="oauth-save" onclick="saveProvider('kakao')">저장</button>
            <span class="oauth-status" id="kakao_status"></span>
        </div>

        <!-- 네이버 -->
        <div class="oauth-card">
            <div class="oauth-card-header">
                <div class="oauth-card-logo oauth-logo-naver">N</div>
                <span class="oauth-card-title">네이버</span>
            </div>
            <div class="oauth-callback">Redirect URI: https://windows.pyeongmok.com/src/api/auth/oauth/callback.php?provider=naver</div>
            <div class="oauth-field">
                <label>Client ID</label>
                <input type="text" id="naver_client_id" placeholder="abc123XYZ…">
            </div>
            <div class="oauth-field">
                <label>Client Secret</label>
                <input type="password" id="naver_client_secret" placeholder="비밀키…">
            </div>
            <button class="oauth-save" onclick="saveProvider('naver')">저장</button>
            <span class="oauth-status" id="naver_status"></span>
        </div>

    </div>
</div>

<script>
const TOKEN = () => localStorage.getItem('pmok_auth_token');

async function loadConfig() {
    const res  = await fetch('/src/api/admin/oauth.php', { headers: { Authorization: 'Bearer ' + TOKEN() } });
    const data = await res.json();
    const cfg  = data.config || {};
    const fill = (id, key) => { if (cfg[key]) document.getElementById(id).value = cfg[key]; };
    fill('google_client_id',     'oauth_google_client_id');
    fill('google_client_secret', 'oauth_google_client_secret');
    fill('kakao_client_id',      'oauth_kakao_client_id');
    fill('kakao_client_secret',  'oauth_kakao_client_secret');
    fill('naver_client_id',      'oauth_naver_client_id');
    fill('naver_client_secret',  'oauth_naver_client_secret');
}

async function saveProvider(p) {
    const btn    = event.target;
    const status = document.getElementById(p + '_status');
    btn.disabled = true;
    status.textContent = '';
    try {
        const body = {
            ['oauth_' + p + '_client_id']:     document.getElementById(p + '_client_id').value,
            ['oauth_' + p + '_client_secret']: document.getElementById(p + '_client_secret').value,
        };
        const res  = await fetch('/src/api/admin/oauth.php', {
            method:  'PUT',
            headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + TOKEN() },
            body:    JSON.stringify(body),
        });
        const data = await res.json();
        if (res.ok) { status.className = 'oauth-status ok'; status.textContent = '저장됨'; }
        else        { status.className = 'oauth-status err'; status.textContent = data.error || '오류'; }
    } catch {
        status.className = 'oauth-status err'; status.textContent = '서버 오류';
    } finally {
        btn.disabled = false;
        setTimeout(() => status.textContent = '', 3000);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('oauthPage');
    if (authGetToken()) { page.style.display = ''; loadConfig(); }
    window.addEventListener('pmokAuthChanged', () => { page.style.display = ''; loadConfig(); });
    authUpdateNav();
});
</script>
</body>
</html>
