<?php
$guide_current = 'studio-hexagon.php';
$guide_title   = '육모 솟을살';
$guide_prev    = ['href' => 'studio-triangle.php', 'title' => '세모 솟을살'];
$guide_next    = ['href' => 'drawing.php', 'title' => '도면 저장 & 불러오기'];
include __DIR__ . '/_head.php';
?>

<h1><span class="guide-h1-icon"><?= $guideEngineIcons['hexagon'] ?></span>육모 솟을살</h1>
<p class="guide-lead">
    세모 솟을살과 동일한 정삼각형 격자 수식을 사용하되, 세 방향 중 한 방향의 선을 생략해 육각형(육모살) 셀을 만드는 패턴입니다.
    벌집 구조처럼 60°·120° 두 방향 사선살만으로 구성되어 독특하고 우아한 느낌을 줍니다.
</p>

<h2>세모 솟을살과의 차이</h2>
<table class="guide-table">
    <thead><tr><th></th><th>세모 솟을살</th><th>육모 솟을살</th></tr></thead>
    <tbody>
        <tr><td>그려지는 살 방향 수</td><td>3가지 (0°, 60°, 120°)</td><td>2가지 (60°, 120°)</td></tr>
        <tr><td>셀 형태</td><td>정삼각형</td><td>육각형</td></tr>
        <tr><td>가로 칸수 단위</td><td>짝수만</td><td>홀수만</td></tr>
        <tr><td>격자 계산 수식</td><td>동일한 정삼각형 테셀레이션</td><td>동일한 정삼각형 테셀레이션 (선 생략만 다름)</td></tr>
    </tbody>
</table>

<h2>주요 파라미터</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td><strong>문 종류 / 문 짝수</strong></td><td>여닫이·미서기, 1~4짝</td></tr>
        <tr><td><strong>문틀 가로 / 문틀 세로</strong></td><td>벽 개구부 치수 (400~3,000mm). 문틀 두께를 제외한 값이 문짝 외경으로 자동 계산됩니다.</td></tr>
        <tr><td><strong>가로 칸수</strong></td><td>1~29, <strong>홀수 단위</strong>로만 조절됩니다(기본값 3). 이 값과 창살 두께로 육각형 크기가 정해집니다.</td></tr>
        <tr><td><strong>패턴 세로 방향</strong></td><td>기본 켜짐. 켜짐(세로형·pointy-top)/꺼짐(가로형·flat-top)으로 육각형 방향이 바뀝니다.</td></tr>
        <tr><td><strong>세로 자동 맞춤</strong></td><td>체크 시 마지막 행이 하단 울거미에 딱 맞도록 문틀 세로 값을 자동 재조정</td></tr>
        <tr><td><strong>좌우 / 상하 울거미 두께</strong></td><td>외곽 프레임 두께 (mm)</td></tr>
        <tr><td><strong>창살 두께</strong></td><td>각 살의 폭 (mm)</td></tr>
        <tr><td><strong>풍판 사용</strong></td><td>체크 시 상단 풍판 구역 추가</td></tr>
    </tbody>
</table>

<h2>제작 시방서</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td>문틀 / 외경 / 내경 가로·세로</td><td>자동 계산된 실측 치수</td></tr>
        <tr><td>변 간격</td><td>육각형 한 변 사이의 간격</td></tr>
        <tr><td>사선 먹줄</td><td>사선살의 기준 간격</td></tr>
        <tr><td>세로울거미홈간격</td><td>세로 울거미에 파이는 홈들 사이의 간격</td></tr>
        <tr><td>반턱 너비 / 세로·가로 울거미홈폭</td><td>세모 솟을살과 동일한 방식으로 계산</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>다른 5개 엔진의 제작 시방서에는 "전체 문폭" 카드가 있지만, 육모 솟을살에는 이 항목이 없습니다. 전체 폭이 필요하면 외경 가로 값을 참고하세요.</span>
</div>

<h2>부재 목록</h2>
<p>세모 솟을살과 동일한 구조입니다 — 울거미 → 방향별 부재(패턴 세로 방향에 따라 명칭 가변) → 사선살(두 방향) → (풍판 사용 시) 풍판 → 문틀.</p>

<h2>마감 · 색상</h2>
<p>목재·마감·부자재, 울거미·살 컬러는 세살과 동일합니다. <strong>면 컬러 칠하기 기능은 현재 비활성화</strong>되어 있습니다.</p>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>육모살(六毛살)은 한국 전통 목공에서 육각형 창살 패턴을 뜻하며, 고급 창호 제작에 자주 사용됩니다.</span>
</div>

<h2>활용 예시</h2>
<ul>
    <li>전통 한옥 육모살 창호</li>
    <li>사찰 법당 창살 패턴</li>
    <li>고급 한식 레스토랑 파티션</li>
</ul>

<?php include __DIR__ . '/_foot.php'; ?>
