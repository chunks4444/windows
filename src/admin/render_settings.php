<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/admin_guard.php';
require_admin_role('s');
?>
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
    <style>
        .rp-table { width:100%; border-collapse:collapse; font-size:13px; }
        .rp-table th { background:var(--bg); padding:8px 12px; text-align:left; font-weight:600; color: var(--text); border-bottom:2px solid var(--border); }
        .rp-table td { padding:8px 12px; border-bottom:1px solid var(--border); vertical-align:middle; }
        .rp-table tr:hover td { background:var(--bg); }
        .rp-id { font-family:monospace; font-size:11px; color: var(--text); background:var(--bg); padding:2px 6px; border-radius:4px; }
        .rp-label-input  { border:1px solid var(--border); border-radius:5px; padding:4px 8px; font-size:13px; width:130px; }
        .rp-prompt-input { border:1px solid var(--border); border-radius:5px; padding:4px 8px; font-size:13px; width:100%; min-width:260px; }
        .rp-sort-input   { border:1px solid var(--border); border-radius:5px; padding:4px 6px; font-size:13px; width:52px; text-align:center; }
        .rp-btn { border:none; border-radius:5px; padding:4px 10px; font-size:12px; font-weight:600; cursor:pointer; white-space:nowrap; }
        .rp-btn-save { background:var(--accent); color:var(--bg); } .rp-btn-save:hover { opacity:.85; }
        .rp-btn-del  { background:var(--bg); color:var(--danger); }    .rp-btn-del:hover  { background:var(--danger-tint); }
        .rp-inactive td { opacity:.4; }
        .rp-status { font-size:12px; margin-left:6px; }
        .rp-status.ok { color:var(--accent); } .rp-status.err { color:var(--danger); }
        .rp-add-row { display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin-top:16px; }
        .rp-add-row input { border:1px solid var(--border); border-radius:6px; padding:6px 10px; font-size:13px; }
        .rp-add-btn { background:var(--accent); color:var(--bg); border:none; border-radius:6px; padding:6px 18px; font-size:13px; font-weight:600; cursor:pointer; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>
<?php include __DIR__ . '/../components/admin_sidenav.php'; ?>

<div class="db-page" id="renderSettingsPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>AI 렌더링 설정</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-stars me-2"></i>AI 렌더링 설정</h1>
    </div>

    <div class="adm-tab-bar" id="rsTabs" style="margin-bottom:20px;">
        <button class="adm-tab-btn active" data-tab="openai">OpenAI 이미지 렌더링</button>
        <button class="adm-tab-btn" data-tab="anthropic">Anthropic Claude AI 채팅</button>
        <button class="adm-tab-btn" data-tab="preset">재질/조명 프리셋</button>
    </div>

    <div id="rsPanelOpenai">
        <div class="oauth-card">
            <div class="oauth-card-header">
                <div class="oauth-card-logo" style="background:#10a37f;color:var(--bg);font-size:11px;font-weight:700;letter-spacing:-.5px;">AI</div>
                <span class="oauth-card-title">OpenAI 이미지 렌더링</span>
            </div>
            <div class="oauth-field">
                <label>OpenAI API Key</label>
                <input type="text" id="openai_api_key" placeholder="sk-proj-…" autocomplete="off" style="font-family:monospace;font-size:11px;">
            </div>
            <div class="oauth-field">
                <label>품질 (gpt-image-1 · 1024×1024)</label>
                <select id="render_quality" style="width:100%;padding:8px 10px;border:1px solid var(--border,var(--border));border-radius:6px;font-size:13px;background:var(--bg);cursor:pointer;">
                    <option value="low">low — ~$0.011/장 &nbsp;(최저가)</option>
                    <option value="medium">medium — ~$0.042/장</option>
                    <option value="high">high — ~$0.167/장 &nbsp;(최고품질)</option>
                </select>
            </div>
            <div class="oauth-field">
                <label>렌더링 기본 명령어 (프롬프트 템플릿)</label>
                <textarea id="render_base_prompt" rows="14" style="width:100%;padding:8px 10px;border:1px solid var(--border,var(--border));border-radius:6px;font-size:12px;font-family:monospace;resize:vertical;" placeholder="{{prompt}} 자리에 사용자가 고른/입력한 프롬프트가 삽입됩니다"></textarea>
                <div style="font-size:11px;color: var(--text);margin-top:4px;">문살 구조를 바꾸지 말라는 지시문. <code>{{prompt}}</code> 위치에 사용자 입력이 들어갑니다.</div>
            </div>
            <button class="oauth-save" onclick="saveRenderConfig()">저장</button>
            <span class="oauth-status" id="render_status"></span>
        </div>
    </div>

    <div id="rsPanelAnthropic" style="display:none;">
        <div class="oauth-card">
            <div class="oauth-card-header">
                <div class="oauth-card-logo" style="background:#6B4FBB;color:var(--bg);font-size:10px;font-weight:700;letter-spacing:-.5px;">ANT</div>
                <span class="oauth-card-title">Anthropic Claude AI 채팅</span>
            </div>
            <div class="oauth-field">
                <label>Anthropic API Key</label>
                <input type="password" id="anthropic_api_key" placeholder="sk-ant-…" autocomplete="off" style="font-family:monospace;font-size:11px;">
            </div>
            <div class="oauth-field">
                <label>모델</label>
                <select id="ai_chat_model" style="width:100%;padding:8px 10px;border:1px solid var(--border,var(--border));border-radius:6px;font-size:13px;background:var(--bg);cursor:pointer;">
                    <option value="claude-sonnet-4-6">Sonnet 4.6 — 고성능 (권장)</option>
                    <option value="claude-haiku-4-5-20251001">Haiku 4.5 — 빠르고 저렴</option>
                </select>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <button class="oauth-save" onclick="saveAiConfig()" style="margin:0;">저장</button>
                <button class="oauth-save" id="btnTest" onclick="testAiConnection()" style="margin:0;background:var(--text-muted,var(--text-muted));">연결 테스트</button>
            </div>
            <div style="margin-top:6px;display:flex;gap:10px;">
                <span class="oauth-status" id="ai_status"></span>
                <span class="oauth-status" id="test_status"></span>
            </div>
        </div>
    </div>

    <div id="rsPanelPreset" style="display:none;">
        <div class="oauth-card">
            <div class="oauth-card-header">
                <div class="oauth-card-logo" style="background:var(--accent);color:var(--bg);font-size:11px;font-weight:700;letter-spacing:-.5px;"><i class="bi bi-palette"></i></div>
                <span class="oauth-card-title">재질/조명 프리셋 (엔진 사이드바 선택 박스)</span>
            </div>
            <p style="font-size:13px;color: var(--text);margin:-4px 0 12px;">여기서 추가·수정한 항목이 모든 엔진의 "렌더링" 선택 박스에 그대로 표시됩니다.</p>
            <div style="overflow-x:auto;">
                <table class="rp-table" id="rpTable">
                    <thead><tr><th>ID</th><th>이름</th><th>프롬프트</th><th>정렬</th><th>활성</th><th></th></tr></thead>
                    <tbody id="rpBody"></tbody>
                </table>
            </div>
            <div class="rp-add-row">
                <input id="rpAddLabel" placeholder="이름 (예: 형광등 조명)" style="width:160px;">
                <input id="rpAddPrompt" placeholder="프롬프트 (예: 실내 형광등 조명, 밝고 균일한 화이트 톤)" style="flex:1;min-width:260px;">
                <button class="rp-add-btn" onclick="addPreset()">추가</button>
                <span class="rp-status" id="rpAddStatus"></span>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('rsTabs').addEventListener('click', e => {
    const btn = e.target.closest('.adm-tab-btn');
    if (!btn) return;
    document.querySelectorAll('#rsTabs .adm-tab-btn').forEach(b => b.classList.toggle('active', b === btn));
    document.getElementById('rsPanelOpenai').style.display    = btn.dataset.tab === 'openai'    ? '' : 'none';
    document.getElementById('rsPanelAnthropic').style.display = btn.dataset.tab === 'anthropic' ? '' : 'none';
    document.getElementById('rsPanelPreset').style.display    = btn.dataset.tab === 'preset'    ? '' : 'none';
});
</script>

<script src="/src/js/admin/render_settings.js"></script>
</body>
</html>
