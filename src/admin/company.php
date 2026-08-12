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
    <style>
        .cc-section { border:1px solid var(--border); border-radius:var(--r-sm); margin-bottom:16px; padding:14px 16px; }
        .cc-section h2 { font-size:14px; font-weight:700; color:var(--text); margin:0 0 12px; }
        .cc-textarea { width:100%; resize:vertical; padding:8px 10px; border:1px solid var(--border); border-radius:var(--r-sm); background:var(--bg); font-family:inherit; font-size:13px; color:var(--text); outline:none; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>
<?php include __DIR__ . '/../components/admin_sidenav.php'; ?>

<div class="db-page" id="ccAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="ccPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>회사소개 관리</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-building me-2"></i>회사소개 관리</h1>
        <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="saveContent()">저장</button>
    </div>
    <p style="font-size:var(--fs-13);color:var(--text);margin:-8px 0 16px;">
        /company 페이지의 문구를 수정합니다. 비워두면 원래 기본 문구가 표시됩니다. HTML 태그(&lt;br&gt;, &lt;strong&gt; 등)를 그대로 사용할 수 있습니다.
        <span id="ccSaveStatus" class="pc-status" style="margin-left:8px;"></span>
    </p>

    <div class="cc-section">
        <h2>히어로</h2>
        <div class="adm-mfield"><label>라벨</label><input class="cc-textarea" id="cc_hero_label" placeholder="About 평목"></div>
        <div class="adm-mfield"><label>제목</label><textarea class="cc-textarea" id="cc_hero_title" rows="2" placeholder="나무로 만드는&lt;br&gt;빛과 바람의 길,&lt;em&gt;평목&lt;/em&gt;"></textarea></div>
        <div class="adm-mfield"><label>설명</label><textarea class="cc-textarea" id="cc_hero_desc" rows="4"></textarea></div>
    </div>

    <div class="cc-section">
        <h2>철학 (Philosophy)</h2>
        <div class="adm-mfield"><label>제목</label><input class="cc-textarea" id="cc_phil_heading" placeholder="평목(平木)&lt;br&gt;..."></div>
        <div class="adm-mfield"><label>본문 (문단 여러 개, &lt;p&gt;로 구분)</label><textarea class="cc-textarea" id="cc_phil_text" rows="6"></textarea></div>
        <hr style="border-color:var(--border);margin:14px 0;">
        <div class="adm-mfield"><label>항목 01 제목</label><input class="cc-textarea" id="cc_phil_item1_title"></div>
        <div class="adm-mfield"><label>항목 01 설명</label><textarea class="cc-textarea" id="cc_phil_item1_desc" rows="2"></textarea></div>
        <div class="adm-mfield"><label>항목 02 제목</label><input class="cc-textarea" id="cc_phil_item2_title"></div>
        <div class="adm-mfield"><label>항목 02 설명</label><textarea class="cc-textarea" id="cc_phil_item2_desc" rows="2"></textarea></div>
        <div class="adm-mfield"><label>항목 03 제목</label><input class="cc-textarea" id="cc_phil_item3_title"></div>
        <div class="adm-mfield"><label>항목 03 설명</label><textarea class="cc-textarea" id="cc_phil_item3_desc" rows="2"></textarea></div>
    </div>

    <div class="cc-section">
        <h2>스튜디오 소개</h2>
        <div class="adm-mfield"><label>라벨</label><input class="cc-textarea" id="cc_studio_label" placeholder="Studio"></div>
        <div class="adm-mfield"><label>제목</label><input class="cc-textarea" id="cc_studio_title"></div>
        <div class="adm-mfield"><label>설명</label><textarea class="cc-textarea" id="cc_studio_body" rows="3"></textarea></div>
    </div>

    <div class="cc-section">
        <h2>연락처</h2>
        <div class="adm-mfield"><label>라벨</label><input class="cc-textarea" id="cc_contact_label" placeholder="Contact"></div>
        <div class="adm-mfield"><label>제목</label><input class="cc-textarea" id="cc_contact_title"></div>
        <div class="adm-mfield"><label>설명</label><textarea class="cc-textarea" id="cc_contact_body" rows="3"></textarea></div>
    </div>
</div>

<script src="/src/js/admin/company.js?v=<?= md5_file(__DIR__ . '/../js/admin/company.js') ?>"></script>
</body>
</html>
