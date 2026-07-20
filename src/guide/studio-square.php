<?php
$guide_current = 'studio-square.php';
$guide_title   = '정자살';
$guide_prev    = ['href' => 'studio-classic.php', 'title' => '세살'];
$guide_next    = ['href' => 'studio-cross.php', 'title' => '빗살'];
include __DIR__ . '/_head.php';
?>

<h1><span class="guide-h1-icon"><?= $guideEngineIcons['square'] ?></span>정자살</h1>
<p class="guide-lead">
    울거미 안에 세로살과 가로살을 모두 꽉 채워 우물 정(井)자 격자를 이룬 정자살(井字箭)을 재현한 엔진입니다.
    만살(滿箭)이라고도 부르며, 세살 다음으로 많이 쓰인 형식입니다.
    세살과 사이드바 구조(문 설정 · 문 치수 · 창살 설정 · 마감 · 배경 · 내보내기)를 대부분 공유하지만,
    상/중/하 3단 구획 대신 <strong>단일 비율의 균등 격자</strong>를 사용하고, 격자를 무작위로 재구성하는
    <strong>랜덤 패턴</strong> 기능이 추가로 있습니다.
</p>

<h2>세살과의 차이점</h2>
<table class="guide-table">
    <thead><tr><th></th><th>정자살</th><th>세살</th></tr></thead>
    <tbody>
        <tr><td>구획 방식</td><td>전체 격자에 동일한 셀 비율 적용</td><td>상/중/하 3단으로 나눠 각 구역 칸수·비율을 독립 지정</td></tr>
        <tr><td>세로 비율 범위</td><td>1.0 ~ 3.0</td><td>1.0 ~ 5.0</td></tr>
        <tr><td>랜덤 패턴</td><td>있음 (몬드리안풍 무작위 분할)</td><td>없음</td></tr>
        <tr><td>세로 자동 맞춤</td><td>있음</td><td>없음</td></tr>
    </tbody>
</table>

<h2>주요 파라미터</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td><strong>문 종류 / 문 짝수</strong></td><td>여닫이·미서기, 1~4짝. 세살과 동일하게 문틀 두께·틈새가 자동 반영되어 실제 문짝 치수가 계산됩니다.</td></tr>
        <tr><td><strong>문틀 가로 / 문틀 세로</strong></td><td>벽 개구부 치수 (400~3,000mm). 문틀 두께를 제외한 값이 문짝 외경으로 자동 계산됩니다.</td></tr>
        <tr><td><strong>가로 칸수</strong></td><td>수평 방향 격자 분할 수</td></tr>
        <tr><td><strong>세로 비율</strong></td><td>1.0~3.0. 셀의 가로:세로 비율을 결정하며 이 값에 따라 세로 칸수가 자동 산출됩니다.</td></tr>
        <tr><td><strong>세로 자동 맞춤</strong></td><td>체크 시 마지막 격자 행이 하단 울거미에 딱 맞도록 문틀 세로 값을 자동으로 재조정합니다.</td></tr>
        <tr><td><strong>좌우 / 상하 울거미 두께</strong></td><td>외곽 프레임 두께 (mm)</td></tr>
        <tr><td><strong>창살 두께</strong></td><td>격자 살 단면 두께 (mm)</td></tr>
        <tr><td><strong>풍판 사용</strong></td><td>체크 시 상단 풍판 구역 추가, 풍판 높이 별도 설정</td></tr>
        <tr><td><strong>치수 표기 / 문틀 표시</strong></td><td>캔버스에 실측 치수와 문틀 윤곽선을 표시할지 선택</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>가로 칸수와 세로 비율을 조합하면 완전한 정방형(正方形) 격자부터 세로로 긴 격자까지 자유롭게 조정할 수 있습니다.</span>
</div>

<h2>랜덤 패턴</h2>
<p>
    정자살 엔진에만 있는 기능으로, 균질 격자를 몬드리안풍으로 무작위 재분할합니다.
</p>
<table class="guide-table">
    <thead><tr><th>버튼</th><th>기능</th></tr></thead>
    <tbody>
        <tr><td><span class="guide-ui">랜덤 생성</span></td><td>현재 격자를 기준으로 셀을 무작위로 병합·분할해 비정형 패턴을 생성</td></tr>
        <tr><td><span class="guide-ui">초기화</span></td><td>랜덤 패턴을 걷어내고 원래의 균등 격자로 복원</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>랜덤 생성은 클릭할 때마다 다른 결과를 만듭니다. 마음에 드는 결과가 나오면 바로 저장하세요. 초기화 전에는 이전 결과가 남아있지 않습니다.</span>
</div>

<h2>제작 시방서 & 부재 목록</h2>
<p>세살과 동일한 항목(문틀 가로/세로, 외경·내경 가로/세로, 가로 칸수, 가로·세로 먹줄, 눈 크기, 반턱 너비, 울거미홈폭)이 자동 계산되어 표시됩니다. 부재 목록은 가로살·세로살, 울거미, (풍판 사용 시) 풍판, 문틀 순으로 구성됩니다.</p>

<h2>마감 · 색상</h2>
<p>
    목재·마감·부자재 선택, 울거미·살 컬러는 세살과 동일합니다.
    <strong>면 컬러 칠하기</strong> 기능도 정자살에서 그대로 사용할 수 있습니다 — 격자 칸을 클릭해 개별 면에 색을 채울 수 있습니다.
    (빗살·격자 빗살·세모 솟을살·육모 솟을살 엔진은 현재 면 칠하기 기능이 비활성화되어 있습니다.)
</p>

<h2>활용 예시</h2>
<ul>
    <li>현대 한옥의 미서기 창문</li>
    <li>카페·상업 공간의 파티션 창호</li>
    <li>랜덤 패턴을 활용한 비정형 디자인 창호</li>
</ul>

<?php include __DIR__ . '/_foot.php'; ?>
