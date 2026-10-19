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
    <?php css_tag('/src/css/admin.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>
<?php include __DIR__ . '/../components/admin_sidenav.php'; ?>

<div class="db-page" id="fssPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>울거미 두께·나비 단계표</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-rulers me-2"></i>울거미 두께·나비 단계표</h1>
        <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="saveSteps()">
            <i class="bi bi-check-lg"></i> 저장
        </button>
    </div>
    <p style="font-size:13px;color:var(--text-2);max-width:720px;">
        문 높이(문짝 외경 세로, outerH)에 따라 울거미 두께·나비를 몇 단계로 나눠 7개 엔진이 공용으로 참고합니다.
        <strong>두께</strong>는 도면에 보이는 값(frame/frameH 슬라이더)의 최소치이자 추천값이고,
        <strong>나비</strong>는 도면에는 안 보이고 부재목록 목재량 계산에만 쓰입니다.
        마지막 단계의 "문 높이 상한"은 비워두면 "이 키 이상이면 전부 이 값"이 됩니다.
        엔진별 정책(최소치만 막기 / 추천값으로 덮어쓰기)은 <a href="/src/admin/engine_settings.php">엔진 기본값 관리</a>의
        "울거미 두께 단계표 정책" 항목에서 따로 고릅니다.
    </p>

    <div id="fssGroups"></div>
</div>

<script src="/src/js/admin/frame_size_steps.js?v=<?= md5_file(__DIR__ . '/../js/admin/frame_size_steps.js') ?>"></script>
</body>
</html>
