<?php
require_once __DIR__ . '/../lib/studio_card_content.php';
$guide_current = 'studio-square.php';
$guide_title   = '정자살';
$guide_prev    = ['href' => 'studio-classic.php', 'title' => '세살'];
$guide_next    = ['href' => 'studio-cross.php', 'title' => '빗살'];
include __DIR__ . '/_head.php';
?>

<h1><span class="guide-h1-icon"><?= $guideEngineIcons['square'] ?></span>정자살</h1>
<p class="guide-lead"><?= studio_card_description('square', "울거미 안에 세로살과 가로살을 모두 꽉 채워 격자를 이룬 정자살(井字箭)을 재현한 엔진입니다.
    '정(井)'은 살이 짜이는 모양이 우물 정 자를 닮은 데서 온 이름으로, 빈틈없이 고른 격자가 이 창의 얼굴입니다.
    만살(滿箭)이라고도 부르며, 세살 다음으로 많이 쓰인 형식입니다.
    세살과 화면 구성(도면 · 문설정 · 문양 · 마감 · 렌더링 · 견적 · 시방서 · 내보내기 탭)을 대부분 공유하지만,
    상/중/하 3단 구획 대신 <strong>단일 비율의 균등 격자</strong>를 사용합니다.") ?></p>

<div class="guide-screenshot">
    <img src="/src/img/guide/studio-square.png" alt="정자살 스튜디오 화면 구성 — 왼쪽 메뉴 탭과 펼친 문 설정 패널, 중앙 캔버스, 캔버스 위 도구 막대" loading="lazy">
</div>

<h2>세살과의 차이점</h2>
<table class="guide-table">
    <thead><tr><th></th><th>정자살</th><th>세살</th></tr></thead>
    <tbody>
        <tr><td>구획 방식</td><td>전체 격자에 동일한 셀 비율 적용</td><td>상/중/하 3단으로 나눠 각 구역 칸수·비율을 독립 지정</td></tr>
        <tr><td>세로 비율 범위</td><td>1.0 ~ 3.0</td><td>1.0 ~ 5.0</td></tr>
        <tr><td>세로 자동 맞춤</td><td>있음</td><td>없음</td></tr>
    </tbody>
</table>

<h2>문설정 탭 — 주요 파라미터</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td><strong>여닫이·미서기 / 짝수</strong></td><td>여닫이 1~2짝, 미서기 1~4·6·8짝. 세살과 동일하게 문틀 두께·틈새가 자동 반영되어 실제 문짝 치수가 계산됩니다.</td></tr>
        <tr><td><strong>문틀 가로 / 문틀 세로</strong></td><td>벽 개구부 치수 (가로 100~10,000mm, 세로 400~3,000mm). 문틀 두께를 제외한 값이 문짝 외경으로 자동 계산됩니다.</td></tr>
        <tr><td><strong>세로 칸수</strong></td><td>2~30. 문짝 폭을 나누는 칸(세로줄) 수 — 세로살 개수는 칸수 - 1</td></tr>
        <tr><td><strong>가로살 개수 직접 지정</strong></td><td>체크하면 아래 <strong>가로살 개수</strong>(1~60)로 가로살을 직접 정합니다. 이때 세로 비율·세로 자동 맞춤은 숨겨집니다.</td></tr>
        <tr><td><strong>세로 비율</strong></td><td>1.0~3.0. 셀의 가로:세로 비율을 결정하며 이 값에 따라 가로살 개수가 자동 산출됩니다. (가로살 개수 직접 지정이 꺼져 있을 때)</td></tr>
        <tr><td><strong>세로 자동 맞춤</strong></td><td>체크 시 마지막 격자 행이 하단 울거미에 딱 맞도록 문틀 세로 값을 자동으로 재조정합니다.</td></tr>
        <tr><td><strong>좌우 / 상하 울거미 두께</strong></td><td>외곽 프레임 두께 (mm)</td></tr>
        <tr><td><strong>살 두께</strong></td><td>격자 살 단면 두께 (mm)</td></tr>
        <tr><td><strong>풍판 사용</strong></td><td>체크 시 상단 풍판 구역 추가, 풍판 높이 별도 설정</td></tr>
        <tr><td><strong>치수 표기 / 문틀 표시</strong></td><td>캔버스에 실측 치수와 문틀 윤곽선을 표시할지 선택</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>세로 칸수와 세로 비율(또는 가로살 개수)을 조합하면 완전한 정방형(正方形) 격자부터 세로로 긴 격자까지 자유롭게 조정할 수 있습니다.</span>
</div>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>격자를 무작위로 나누는 랜덤 패턴은 별도의 <a href="/guide/studio-mondrian">몬드리안</a> 엔진으로 옮겨졌습니다.</span>
</div>

<h2>시방서 탭 — 제작 시방서 &amp; 부재 목록</h2>
<p>세살과 동일한 항목(문틀 가로·세로, 외경 가로·세로, 내경 가로·세로, 가로 칸수·세로 칸수, 가로 먹줄·세로 먹줄, 반턱 너비, 세로울거미홈폭·가로울거미홈폭)이 자동 계산되어 표시됩니다. 부재 목록은 가로살·세로살, 울거미, (풍판 사용 시) 풍판, 문틀 순으로 구성됩니다.</p>

<h2>마감 탭</h2>
<p>수종·부자재와 <strong>색칠할 부위</strong>(문틀·울거미·살·면)별 색 고르기는 세살과 같습니다. 부위에서 <strong>면</strong>을 고르면 도면의 칸을 클릭해 원하는 칸만 색을 채울 수 있습니다(면컬러 칠하기).</p>

<h2>활용 예시</h2>
<ul>
    <li>현대 한옥의 미서기 창문</li>
    <li>카페·상업 공간의 파티션 창호</li>
</ul>

<?php include __DIR__ . '/_foot.php'; ?>
