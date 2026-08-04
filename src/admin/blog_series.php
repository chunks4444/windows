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
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>
    <style>
        .bs-table { width:100%; border-collapse:collapse; font-size:14px; max-width:900px; }
        .bs-table th { background:var(--bg); padding:10px 12px; text-align:left; font-weight:600; color:var(--text); border-bottom:2px solid var(--border); }
        .bs-table td { padding:10px 12px; border-bottom:1px solid var(--border); vertical-align:middle; }
        .bs-table tr:hover td { background:var(--bg); }
        .bs-name-input { border:1px solid var(--border); border-radius:6px; padding:8px 12px; font-size:14px; width:220px; }
        .bs-tagline-input { border:1px solid var(--border); border-radius:6px; padding:8px 12px; font-size:14px; width:340px; }
        .bs-sort-input { border:1px solid var(--border); border-radius:6px; padding:8px 10px; font-size:14px; width:64px; text-align:center; }
        .bs-btn { border:none; border-radius:6px; padding:7px 14px; font-size:13px; font-weight:600; cursor:pointer; }
        .bs-btn-save { background:var(--accent); color:var(--bg); } .bs-btn-save:hover { opacity:.85; }
        .bs-btn-del  { background:var(--bg); color:var(--danger); }    .bs-btn-del:hover  { background:var(--danger-tint); }
        .bs-status { font-size:12px; margin-left:8px; }
        .bs-status.ok { color:var(--accent); } .bs-status.err { color:var(--danger); }
        .bs-add-row { display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-top:20px; padding:18px; border:1px solid var(--border); border-radius:var(--r); background:var(--bg); }
        .bs-add-row input { border:1px solid var(--border); border-radius:6px; padding:8px 12px; font-size:14px; }
        .bs-add-btn { background:var(--accent); color:var(--bg); border:none; border-radius:6px; padding:8px 22px; font-size:14px; font-weight:600; cursor:pointer; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>
<?php include __DIR__ . '/../components/admin_sidenav.php'; ?>

<div class="db-page" id="bsAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="bsPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span><a href="/src/admin/blog.php">블로그</a><span class="adm-breadcrumb-sep">/</span>시리즈 관리</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-collection me-2"></i>시리즈 관리</h1>
    </div>

    <div style="overflow-x:auto;">
        <table class="bs-table" id="bsTable">
            <thead><tr><th>이름</th><th>명제(tagline)</th><th style="width:70px;">순서</th><th style="width:70px;">홈노출</th><th style="width:70px;">완결</th><th></th></tr></thead>
            <tbody id="bsBody"></tbody>
        </table>
    </div>

    <div class="bs-add-row">
        <input id="bsAddName" class="bs-name-input" placeholder="시리즈 이름">
        <input id="bsAddTagline" class="bs-tagline-input" placeholder="핵심 명제 한 줄">
        <input id="bsAddOrder" class="bs-sort-input" type="number" value="0" placeholder="순서">
        <label style="display:inline-flex;align-items:center;gap:6px;font-size:14px;"><input id="bsAddShowOnHome" type="checkbox" checked> 홈노출</label>
        <label style="display:inline-flex;align-items:center;gap:6px;font-size:14px;"><input id="bsAddCompleted" type="checkbox"> 완결</label>
        <button class="bs-add-btn" onclick="addSeries()">추가</button>
        <span id="bsAddStatus" class="bs-status"></span>
    </div>
</div>

<script src="/src/js/admin/blog_series.js?v=<?= md5_file(__DIR__ . '/../js/admin/blog_series.js') ?>"></script>
</body>
</html>
