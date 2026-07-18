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
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="statsAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="statsPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>접속 통계</div>
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
        <div class="st-card">
            <div class="st-card-label">공유중인 도면</div>
            <div class="st-card-value" id="sumShared">—</div>
            <div class="st-card-sub">현재 시점</div>
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

    <div class="st-panel">
        <div class="st-panel-head" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <div style="display:flex;gap:4px;flex-wrap:wrap;">
                <button class="st-tab-btn active" data-tab="topPages" onclick="switchStatsTab('topPages')">인기 페이지</button>
                <button class="st-tab-btn" data-tab="topUsers" onclick="switchStatsTab('topUsers')">회원접속</button>
                <button class="st-tab-btn" data-tab="anonVisits" onclick="switchStatsTab('anonVisits')">비로그인 방문</button>
                <button class="st-tab-btn" data-tab="exportLogs" onclick="switchStatsTab('exportLogs')">내보내기 기록 (최근 100건)</button>
            </div>
            <div id="exportSummaryBadges" style="display:flex;gap:6px;flex-wrap:wrap;"></div>
        </div>

        <div class="st-tab-panel" id="tab-topPages">
            <table>
                <thead><tr><th>#</th><th>페이지</th><th>PV</th><th>UV</th></tr></thead>
                <tbody id="topPagesTbody"></tbody>
            </table>
            <div style="display:flex;align-items:center;justify-content:center;gap:12px;padding:14px;">
                <button class="st-tab-btn" id="pagesPrevBtn" onclick="loadTopPages(topPagesPage - 1)">이전</button>
                <span id="pagesPageLabel" style="font-size:12px;color:var(--text-3);"></span>
                <button class="st-tab-btn" id="pagesNextBtn" onclick="loadTopPages(topPagesPage + 1)">다음</button>
            </div>
        </div>

        <div class="st-tab-panel" id="tab-topUsers" style="display:none;">
            <table>
                <thead><tr><th>날짜</th><th>회원</th><th>몇회</th><th>디바이스</th><th>마지막 접속시간</th><th>IP</th></tr></thead>
                <tbody id="topUsersTbody"></tbody>
            </table>
            <div style="display:flex;align-items:center;justify-content:center;gap:12px;padding:14px;">
                <button class="st-tab-btn" id="usersPrevBtn" onclick="loadTopUsers(topUsersPage - 1)">이전</button>
                <span id="usersPageLabel" style="font-size:12px;color:var(--text-3);"></span>
                <button class="st-tab-btn" id="usersNextBtn" onclick="loadTopUsers(topUsersPage + 1)">다음</button>
            </div>
        </div>

        <div class="st-tab-panel" id="tab-anonVisits" style="display:none;">
            <div style="display:flex;gap:6px;padding:10px 14px 0;">
                <input type="text" id="anonIpFilter" placeholder="IP 검색 (예: 20. 로 접두사 검색 가능)"
                    style="flex:1;max-width:280px;height:30px;padding:0 10px;font-size:12px;border:1px solid var(--border);border-radius:var(--r-sm);background:var(--bg);color:var(--text);"
                    onkeydown="if(event.key==='Enter') loadAnonVisits(1)">
                <button class="st-tab-btn" onclick="loadAnonVisits(1)">검색</button>
                <button class="st-tab-btn" onclick="document.getElementById('anonIpFilter').value='';loadAnonVisits(1)">초기화</button>
            </div>
            <table>
                <thead><tr><th>날짜</th><th>IP</th><th>몇회</th><th>디바이스</th><th>마지막 접속시간</th></tr></thead>
                <tbody id="anonVisitsTbody"></tbody>
            </table>
            <div style="display:flex;align-items:center;justify-content:center;gap:12px;padding:14px;">
                <button class="st-tab-btn" id="anonPrevBtn" onclick="loadAnonVisits(anonPage - 1)">이전</button>
                <span id="anonPageLabel" style="font-size:12px;color:var(--text-3);"></span>
                <button class="st-tab-btn" id="anonNextBtn" onclick="loadAnonVisits(anonPage + 1)">다음</button>
            </div>
        </div>

        <div class="st-tab-panel" id="tab-exportLogs" style="display:none;">
            <table>
                <thead><tr><th>일시</th><th>이메일</th><th>엔진</th><th>형식</th><th>도면명</th><th>버전</th></tr></thead>
                <tbody id="exportLogsTbody"></tbody>
            </table>
        </div>
    </div>

    <!-- 회원 방문 상세 모달 -->
    <div id="userVisitsOverlay" onclick="if(event.target===this) closeUserVisits()" style="display:none;position:fixed;inset:0;z-index:2000;background:rgba(0,0,0,.45);align-items:center;justify-content:center;">
        <div style="background:var(--bg);border-radius:12px;max-width:560px;width:92%;max-height:80vh;display:flex;flex-direction:column;overflow:hidden;">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border);">
                <span id="userVisitsTitle" style="font-weight:700;font-size:14px;color:var(--text);"></span>
                <button onclick="closeUserVisits()" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--text);">&times;</button>
            </div>
            <div id="userVisitsBody" style="overflow-y:auto;padding:16px 20px;font-size:12px;color:var(--text);"></div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="/src/js/admin/stats.js?v=<?= md5_file(__DIR__ . '/../js/admin/stats.js') ?>"></script>
</body>
</html>
