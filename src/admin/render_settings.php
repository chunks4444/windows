<?php header('Content-Type: text/html; charset=UTF-8'); ?>
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

    <?php css_tag('/src/css/admin/oauth.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="renderSettingsPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>AI 렌더링 설정</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-stars me-2"></i>AI 렌더링 설정</h1>
    </div>

    <div style="max-width:600px;margin:0 auto;">

        <div class="oauth-card">
            <div class="oauth-card-header">
                <div class="oauth-card-logo" style="background:#10a37f;color:#fff;font-size:11px;font-weight:700;letter-spacing:-.5px;">AI</div>
                <span class="oauth-card-title">OpenAI 이미지 렌더링</span>
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

        <div class="oauth-card" style="margin-top:16px;">
            <div class="oauth-card-header">
                <div class="oauth-card-logo" style="background:#6B4FBB;color:#fff;font-size:10px;font-weight:700;letter-spacing:-.5px;">ANT</div>
                <span class="oauth-card-title">Anthropic Claude AI 채팅</span>
            </div>
            <div class="oauth-field">
                <label>Anthropic API Key</label>
                <input type="password" id="anthropic_api_key" placeholder="sk-ant-…" autocomplete="off" style="font-family:monospace;font-size:11px;">
            </div>
            <div class="oauth-field">
                <label>모델</label>
                <select id="ai_chat_model" style="width:100%;padding:8px 10px;border:1px solid var(--border-md,#ddd);border-radius:6px;font-size:13px;background:#fff;cursor:pointer;">
                    <option value="claude-sonnet-4-6">Sonnet 4.6 — 고성능 (권장)</option>
                    <option value="claude-haiku-4-5-20251001">Haiku 4.5 — 빠르고 저렴</option>
                </select>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <button class="oauth-save" onclick="saveAiConfig()" style="flex:1;margin:0;">저장</button>
                <button class="oauth-save" id="btnTest" onclick="testAiConnection()" style="flex:1;margin:0;background:var(--text-3,#888);">연결 테스트</button>
            </div>
            <div style="margin-top:6px;display:flex;gap:10px;">
                <span class="oauth-status" id="ai_status"></span>
                <span class="oauth-status" id="test_status"></span>
            </div>
        </div>

    </div>
</div>

<script src="/src/js/admin/render_settings.js"></script>
</body>
</html>
