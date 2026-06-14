<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/src/css/dashboard.css">
    <link rel="stylesheet" href="/src/css/stats.css">
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="statsAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="statsPage" style="display:none;">
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-bar-chart-line me-2"></i>접속 통계</h1>
        <div class="st-month-btns">
            <button class="st-month-btn" data-m="1">1개월</button>
            <button class="st-month-btn" data-m="3">3개월</button>
            <button class="st-month-btn active" data-m="6">6개월</button>
        </div>
    </div>

    <!-- 요약 카드 -->
    <div class="st-cards">
        <div class="st-card">
            <div class="st-card-label">총 PV</div>
            <div class="st-card-value" id="sumPv">—</div>
            <div class="st-card-sub">페이지뷰</div>
        </div>
        <div class="st-card">
            <div class="st-card-label">총 UV</div>
            <div class="st-card-value" id="sumUv">—</div>
            <div class="st-card-sub">유니크 방문자</div>
        </div>
        <div class="st-card">
            <div class="st-card-label">회원 PV</div>
            <div class="st-card-value" id="sumMember">—</div>
            <div class="st-card-sub">로그인 상태 방문</div>
        </div>
        <div class="st-card">
            <div class="st-card-label">모바일</div>
            <div class="st-card-value" id="sumMobile">—</div>
            <div class="st-card-sub">모바일 PV</div>
        </div>
    </div>

    <!-- 일별 차트 -->
    <div class="st-panel">
        <div class="st-panel-head">
            <span class="st-panel-title">일별 PV / UV</span>
        </div>
        <div class="st-panel-body">
            <div class="st-chart-wrap"><canvas id="dailyChart"></canvas></div>
        </div>
    </div>

    <div class="st-grid-2">
        <!-- 인기 페이지 -->
        <div class="st-panel">
            <div class="st-panel-head"><span class="st-panel-title">인기 페이지 TOP 10</span></div>
            <table>
                <thead><tr><th>#</th><th>페이지</th><th>PV</th><th>UV</th></tr></thead>
                <tbody id="topPagesTbody"></tbody>
            </table>
        </div>

        <!-- 회원별 접속 -->
        <div class="st-panel">
            <div class="st-panel-head"><span class="st-panel-title">회원 접속 TOP 20</span></div>
            <table>
                <thead><tr><th>#</th><th>이메일</th><th>권한</th><th>방문</th><th>최근</th><th>IP</th></tr></thead>
                <tbody id="topUsersTbody"></tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="/src/js/admin/stats.js"></script>
</body>
</html>
