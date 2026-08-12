<?php
require_once __DIR__ . '/../lib/studio_card_content.php';
$guide_current = 'studio-diamond.php';
$guide_title   = '격자 빗살';
$guide_prev    = ['href' => 'studio-cross.php', 'title' => '빗살'];
$guide_next    = ['href' => 'studio-triangle.php', 'title' => '세모 솟을살'];
include __DIR__ . '/_head.php';
?>

<h1><span class="guide-h1-icon"><?= $guideEngineIcons['diamond'] ?></span>격자 빗살</h1>
<p class="guide-lead"><?= studio_card_description('diamond', "가로세로 격자 위에 45도 빗살을 겹쳐 짠 격자빗살을 재현한 엔진입니다.
    정(井)자 짜임과 대각선 빗살 짜임이 한 면에서 만나 격자 한 칸이 다시 네 개의 작은 삼각으로 나뉩니다.
    빗살 엔진과 마찬가지로 셀은 항상 정사각형으로 고정되며, 그 위에 가로살·세로살과 사선살이 모두 그려집니다.") ?></p>

<h2>살 구성</h2>
<table class="guide-table">
    <thead><tr><th>살 방향</th><th>각도</th><th>역할</th></tr></thead>
    <tbody>
        <tr><td>수직살 · 수평살</td><td>0° / 90°</td><td>정자살과 동일한 직교 기본 골격</td></tr>
        <tr><td>사선살 A · B</td><td>45° / 135°</td><td>정사각 셀의 대각선을 따라 겹쳐지는 빗살</td></tr>
    </tbody>
</table>

<h2>주요 파라미터</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td><strong>문 종류 / 문 짝수</strong></td><td>여닫이·미서기, 1~4짝</td></tr>
        <tr><td><strong>문틀 가로 / 문틀 세로</strong></td><td>벽 개구부 치수 (400~3,000mm). 문틀 두께를 제외한 값이 문짝 외경으로 자동 계산됩니다.</td></tr>
        <tr><td><strong>가로 칸수</strong></td><td>2~30. 빗살과 마찬가지로 정사각 셀의 가로 개수를 정하며, 세로 칸수는 자동 산출됩니다.</td></tr>
        <tr><td><strong>세로 자동 맞춤</strong></td><td>체크 시 마지막 행이 하단 울거미에 딱 맞도록 문틀 세로 값을 자동 재조정</td></tr>
        <tr><td><strong>좌우 / 상하 울거미 두께</strong></td><td>외곽 프레임 두께 (mm)</td></tr>
        <tr><td><strong>창살 두께</strong></td><td>전체 살(직교·사선 공통)의 기본 두께 (mm)</td></tr>
        <tr><td><strong>풍판 사용</strong></td><td>체크 시 상단 풍판 구역 추가</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>정자살의 "세로 비율"이나 세살의 "상/중/하 배열" 같은 구획 설정은 격자 빗살에는 없습니다. 사선 밀도는 별도 항목이 아니라 가로 칸수·창살 두께에 종속됩니다.</span>
</div>

<h2>제작 시방서</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td>문틀 / 외경 / 내경 가로·세로</td><td>자동 계산된 실측 치수</td></tr>
        <tr><td>사선 간격</td><td>대각선 살 사이의 간격</td></tr>
        <tr><td>살 먹줄</td><td>직교살·사선살 교차점 간 거리</td></tr>
        <tr><td>울거미홈폭</td><td>좌우·상하 구분 없이 단일값으로 계산되는 홈 폭</td></tr>
    </tbody>
</table>

<h2>부재 목록</h2>
<p>울거미 → <strong>가로살·세로살</strong> → <strong>사선살</strong> → (풍판 사용 시) 풍판 → 문틀 순서로, 빗살보다 그룹이 하나 더 있습니다(직교살과 사선살이 모두 별도 목록으로 집계).</p>

<h2>마감 · 색상</h2>
<p>목재·마감·부자재, 울거미·살 컬러는 세살과 동일합니다. <strong>면 컬러 칠하기 기능은 현재 비활성화</strong>되어 있습니다.</p>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>가로 칸수를 지나치게 높이면 직교살·사선살이 촘촘히 겹쳐 실제 제작이 어려울 수 있습니다. PDF로 출력해 실측을 확인하세요.</span>
</div>

<h2>활용 예시</h2>
<ul>
    <li>궁궐·사찰의 아자살(亞字窓) 변형 패턴</li>
    <li>고급 한옥 펜션 창호</li>
    <li>전통 공예 전시관 파티션</li>
</ul>

<?php include __DIR__ . '/_foot.php'; ?>
