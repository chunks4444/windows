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
    <?php css_tag('/src/css/stats.css'); ?>
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>

    <?php css_tag('/src/css/admin.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>
<?php include __DIR__ . '/../components/admin_sidenav.php'; ?>

<div class="db-page" id="ufPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>가입자 활동</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-funnel me-2"></i>가입자 활동</h1>
        <div class="st-month-btns">
            <button class="st-month-btn" data-m="3">3개월</button>
            <button class="st-month-btn active" data-m="6">6개월</button>
            <button class="st-month-btn" data-m="12">12개월</button>
        </div>
    </div>
    <p style="font-size:12px;color:var(--text-3);margin:0 0 16px;">
        가입한 회원이 스튜디오를 열고, 도면을 저장하고, 견적요청까지 얼마나 가는지 보여줍니다. 관리자 계정은 빼고, 각 단계는 "처음 한 시각"만 기록합니다.
        <span id="ufNote"></span>
    </p>
    <p id="ufNoTable" style="display:none;font-size:13px;color:#a02020;">아직 기록 표가 만들어지지 않았습니다. (ops/migrations/2026-11-09_user_milestones.sql 적용 필요)</p>

    <!-- 전체 단계 -->
    <div class="st-cards" id="ufCards" style="grid-template-columns:repeat(6,1fr);"></div>

    <!-- 월별 -->
    <div class="st-panel">
        <div class="st-panel-head"><span class="st-panel-title">가입 월별 — 그 달에 가입한 회원이 이후 어디까지 갔는지</span></div>
        <div class="st-panel-body">
            <table>
                <thead><tr><th>가입 월</th><th>가입</th><th>스튜디오 열기</th><th>도면 저장</th><th>내보내기</th><th>AI 렌더링</th><th>견적요청</th></tr></thead>
                <tbody id="ufCohorts"></tbody>
            </table>
        </div>
    </div>

    <!-- 회원별 -->
    <div class="st-panel">
        <div class="st-panel-head" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <span class="st-panel-title">회원별 (최근 가입 300명)</span>
            <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                <label style="display:flex;gap:6px;align-items:center;font-size:12px;cursor:pointer;">
                    <input type="checkbox" id="ufNoDrawing" onchange="renderMembers()"> 도면을 아직 안 만든 회원만
                </label>
                <input type="text" id="ufSearch" placeholder="이메일·이름 검색" oninput="renderMembers()"
                    style="height:30px;width:180px;padding:0 10px;font-size:12px;border:1px solid var(--border);border-radius:var(--r-sm);background:var(--bg);color:var(--text);">
            </div>
        </div>
        <div class="st-panel-body" style="padding:0;">
            <table>
                <thead><tr><th>가입일</th><th>회원</th><th>스튜디오</th><th>도면 저장</th><th>내보내기</th><th>렌더링</th><th>견적요청</th><th>마지막 방문</th></tr></thead>
                <tbody id="ufMembers"></tbody>
            </table>
        </div>
    </div>
</div>

<script src="/src/js/admin/user_funnel.js?v=<?= md5_file(__DIR__ . '/../js/admin/user_funnel.js') ?>"></script>
</body>
</html>
