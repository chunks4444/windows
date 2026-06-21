<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../lib/meta.php'; ?>
<?php css_tag('/src/css/dashboard.css'); ?>
    <?php css_tag('/src/css/users.css'); ?>
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>

    <?php css_tag('/src/css/admin/oauth.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="mailSettingsPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>메일 발송 설정</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-envelope me-2"></i>메일 발송 설정</h1>
    </div>

    <div style="max-width:600px;margin:0 auto;">

        <div class="oauth-card">
            <div class="oauth-card-header">
                <div class="oauth-card-logo" style="background:#3A8C82;color:#fff;font-size:14px;">✉</div>
                <span class="oauth-card-title">SMTP 인증 계정</span>
            </div>
            <div class="oauth-callback">사이트가 메일을 발송할 때 로그인하는 Gmail 계정입니다.</div>
            <div class="oauth-field">
                <label>인증용 메일 (SMTP 로그인 계정)</label>
                <input type="text" id="mail_smtp_user" placeholder="pyeongmok@gmail.com">
            </div>
            <div class="oauth-field">
                <label>인증 비밀번호 (Gmail 앱 비밀번호)</label>
                <input type="password" id="mail_smtp_pass" placeholder="앱 비밀번호" autocomplete="off">
            </div>
            <button class="oauth-save" onclick="saveSmtpAuth()">저장</button>
            <span class="oauth-status" id="smtp_status"></span>
        </div>

        <div class="oauth-card" style="margin-top:32px;">
            <div class="oauth-card-header">
                <div class="oauth-card-logo" style="background:#3A8C82;color:#fff;font-size:14px;">📨</div>
                <span class="oauth-card-title">발신/수신 주소</span>
            </div>
            <div class="oauth-callback">발송 목적별 주소입니다. 회원 계정 정보가 아닙니다.</div>
            <div class="oauth-field">
                <label>영업 메일 (문의·견적요청 수신)</label>
                <input type="text" id="mail_sales" placeholder="pyeongmok@gmail.com">
            </div>
            <div class="oauth-field">
                <label>회원 메일 (가입환영·비밀번호재설정 발신)</label>
                <input type="text" id="mail_member" placeholder="pyeongmok@gmail.com">
            </div>
            <button class="oauth-save" onclick="saveMailAddresses()">저장</button>
            <span class="oauth-status" id="addr_status"></span>
        </div>

    </div>
</div>

<script src="/src/js/admin/mail_settings.js"></script>
</body>
</html>
