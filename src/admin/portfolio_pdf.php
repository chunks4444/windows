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

    <?php css_tag('/src/css/admin.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>
<?php include __DIR__ . '/../components/admin_sidenav.php'; ?>

<div class="db-page" id="pfPdfPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>포트폴리오 PDF</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-file-earmark-pdf me-2"></i>포트폴리오 PDF</h1>
    </div>

    <div style="max-width:640px;margin:0 auto;">

        <div class="oauth-card">
            <div class="oauth-card-header">
                <div class="oauth-card-logo" style="background:var(--accent);color:var(--bg);font-size:14px;">PDF</div>
                <span class="oauth-card-title">지금 공개 중인 파일</span>
            </div>
            <div class="oauth-callback">회사 소개 페이지의 "포트폴리오 다운로드" 버튼이 이 파일을 내려받게 합니다.</div>
            <div id="pfCurrent" style="font-size:14px;line-height:1.8;">불러오는 중…</div>
            <div id="pfCurrentBtns" style="display:none;margin-top:12px;gap:8px;flex-wrap:wrap;">
                <a id="pfOpen" class="oauth-save" href="#" target="_blank" rel="noopener" style="text-decoration:none;display:inline-block;">다운로드 화면 열기</a>
                <button type="button" class="oauth-save" id="pfCopy" style="background:transparent;color:var(--text);border:1px solid var(--border);">공개 링크 복사</button>
            </div>
        </div>

        <div class="oauth-card" style="margin-top:32px;">
            <div class="oauth-card-header">
                <div class="oauth-card-logo" style="background:var(--accent);color:var(--bg);font-size:14px;">↑</div>
                <span class="oauth-card-title">새 PDF 올리기</span>
            </div>
            <div class="oauth-callback">PDF 파일만, 30MB 이하. 올리면 공개 중인 파일이 바로 교체되고, 쓰는 곳이 없어진 옛 파일은 지워집니다.</div>
            <div class="oauth-field">
                <label>PDF 파일</label>
                <input type="file" id="pfFile" accept="application/pdf,.pdf">
            </div>
            <label style="display:flex;gap:8px;align-items:flex-start;font-size:13px;margin:6px 0 14px;cursor:pointer;">
                <input type="checkbox" id="pfApplyTpl" style="margin-top:3px;">
                <span>홍보메일 템플릿(#1)의 첨부 PDF도 같은 파일로 바꾸기<br><span style="color:var(--text-muted);font-size:12px;">이미 만들어 둔 캠페인의 첨부는 그대로 유지되고, 앞으로 이 템플릿으로 만드는 캠페인부터 새 PDF가 붙습니다.</span></span>
            </label>
            <button class="oauth-save" id="pfUpload" type="button">업로드</button>
            <span class="oauth-status" id="pfStatus"></span>
        </div>

    </div>
</div>

<script src="/src/js/admin/portfolio_pdf.js?v=<?= md5_file(__DIR__ . '/../js/admin/portfolio_pdf.js') ?>"></script>
</body>
</html>
