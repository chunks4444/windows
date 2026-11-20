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
                <a id="pfOpen" class="oauth-save pf-btn" href="#" target="_blank" rel="noopener">다운로드 화면 열기</a>
                <button type="button" class="oauth-save pf-btn pf-btn--ghost" id="pfCopy">공개 링크 복사</button>
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

    <div style="max-width:880px;margin:32px auto 0;">
        <div class="oauth-card">
            <div class="oauth-card-header">
                <div class="oauth-card-logo" style="background:var(--accent);color:var(--bg);font-size:14px;">↓</div>
                <span class="oauth-card-title">다운로드 기록</span>
            </div>
            <div class="oauth-callback">누가·언제·몇 번 열람하거나 내려받았는지 기록합니다. 회원은 이름·이메일, 비회원은 IP로 구분하고, 비회원 IP는 1년 뒤 지워집니다. 회사 소개의 버튼과 홍보메일 링크 모두 같은 기록에 쌓입니다.</div>
            <label style="display:flex;gap:8px;align-items:center;font-size:13px;margin:0 0 12px;cursor:pointer;">
                <input type="checkbox" id="pfDlAll">
                <span>봇·관리자 계정의 열람도 포함 <span style="color:var(--text-muted);font-size:12px;">(메일 앱·메신저가 링크를 미리 열어 보는 기록이 섞여 있어 기본은 뺍니다)</span></span>
            </label>
            <div id="pfDlSummary" style="font-size:14px;line-height:1.8;margin-bottom:10px;">불러오는 중…</div>
            <div style="overflow-x:auto;">
                <table class="table table-sm" style="font-size:13px;min-width:640px;">
                    <thead><tr><th>누구</th><th>구분</th><th class="text-end">내려받기</th><th class="text-end">바로 보기</th><th class="text-end">안내 화면</th><th>경로</th><th>처음</th><th>마지막</th></tr></thead>
                    <tbody id="pfDlPeople"></tbody>
                </table>
            </div>
            <details style="margin-top:12px;">
                <summary style="cursor:pointer;font-size:13px;">최근 기록 (최대 60건)</summary>
                <div style="overflow-x:auto;margin-top:8px;">
                    <table class="table table-sm" style="font-size:13px;min-width:560px;">
                        <thead><tr><th>시각</th><th>누구</th><th>동작</th><th>경로</th></tr></thead>
                        <tbody id="pfDlRecent"></tbody>
                    </table>
                </div>
            </details>
        </div>
    </div>
</div>

<script src="/src/js/admin/portfolio_pdf.js?v=<?= md5_file(__DIR__ . '/../js/admin/portfolio_pdf.js') ?>"></script>
</body>
</html>
