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
    
    <link rel="stylesheet" href="/src/css/admin/oauth.css">
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="oauthPage" style="display:none;">
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-key me-2"></i>SNS 로그인 설정</h1>
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

        <!-- AI 렌더링 -->
        <div class="oauth-card" style="margin-top:32px;">
            <div class="oauth-card-header">
                <div class="oauth-card-logo" style="background:#10a37f;color:#fff;font-size:11px;font-weight:700;letter-spacing:-.5px;">AI</div>
                <span class="oauth-card-title">AI 렌더링 품질</span>
            </div>
            <div class="oauth-field">
                <label>OpenAI API Key</label>
                <input type="text" id="openai_api_key" placeholder="sk-proj-…" autocomplete="off" style="font-family:monospace;font-size:11px;">
            </div>
            <div class="oauth-field">
                <label>품질 (gpt-image-1 · 1024×1024)</label>
                <select id="render_quality" style="width:100%;padding:8px 10px;border:1px solid var(--border-md,#ddd);border-radius:6px;font-size:13px;background:#fff;cursor:pointer;">
                    <option value="low">low — ~$0.011/장 &nbsp;(최저가)</option>
                    <option value="medium">medium — ~$0.042/장</option>
                    <option value="high">high — ~$0.167/장 &nbsp;(최고품질)</option>
                </select>
            </div>
            <button class="oauth-save" onclick="saveRenderConfig()">저장</button>
            <span class="oauth-status" id="render_status"></span>
        </div>

    </div>
</div>

<script src="/src/js/admin/oauth.js"></script>
</body>
</html>
