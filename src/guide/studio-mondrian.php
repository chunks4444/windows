<?php
require_once __DIR__ . '/../lib/studio_card_content.php';
$guide_current = 'studio-mondrian.php';
$guide_title   = '몬드리안';
$guide_prev    = ['href' => 'studio-hexagon.php', 'title' => '육모솟을살'];
$guide_next    = ['href' => 'canvas-toolbar.php', 'title' => '캔버스 툴바'];
include __DIR__ . '/_head.php';
?>

<h1><span class="guide-h1-icon"><?= $guideEngineIcons['mondrian'] ?></span>몬드리안</h1>
<p class="guide-lead"><?= studio_card_description('mondrian', "정자살 격자를 무작위로 분할해 만든 자유 패턴 엔진입니다.
    버튼 한 번으로 새 구성을 만들고, 선을 직접 그리거나 옮겨 다듬을 수 있습니다.") ?></p>

<div class="guide-screenshot">
    <img src="/src/img/guide/studio-mondrian.png" alt="몬드리안 스튜디오 화면 구성 — 왼쪽 메뉴 탭과 펼친 문 설정 패널, 중앙 캔버스, 캔버스 위 도구 막대" loading="lazy">
</div>

<p>
    가로·세로 직선 살만 쓴다는 점은 정자살과 같지만, 칸이 고르게 반복되지 않고
    <strong>크고 작은 직사각형이 비대칭으로 나뉘는</strong> 것이 특징입니다.
    새 도면을 열면 랜덤 패턴이 바로 하나 만들어져 있으니, 마음에 들 때까지 다시 생성하거나 선을 옮겨 다듬으면 됩니다.
</p>

<h2>정자살과의 차이점</h2>
<table class="guide-table">
    <thead><tr><th></th><th>몬드리안</th><th>정자살</th></tr></thead>
    <tbody>
        <tr><td>칸 구성</td><td>크기가 제각각인 직사각형 (무작위 분할)</td><td>같은 크기의 칸이 반복되는 균등 격자</td></tr>
        <tr><td>칸수 지정</td><td>없음 — 랜덤 생성과 선 편집으로 구성</td><td>세로 칸수 · 가로살 개수로 지정</td></tr>
        <tr><td>분할선 드래그 이동</td><td>있음</td><td>없음</td></tr>
        <tr><td>세로 자동 맞춤</td><td>없음</td><td>있음</td></tr>
        <tr><td>살 길이 (부재 목록)</td><td>살마다 달라 "가변"으로 표시</td><td>격자 기준 단일 길이</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>몬드리안 도면은 정자살과 별도로 저장됩니다. 정자살에서 만든 도면은 몬드리안 도면 목록에 나오지 않고, 반대도 마찬가지입니다.</span>
</div>

<h2>문설정 탭 — 주요 파라미터</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td><strong>여닫이·미서기 / 짝수</strong></td><td>여닫이 1~2짝, 미서기 1~4짝 또는 6짝. 문틀 두께·틈새가 자동 반영되어 실제 문짝 치수가 계산됩니다.</td></tr>
        <tr><td><strong>문틀 가로 / 문틀 세로</strong></td><td>벽 개구부 치수 (가로 100~10,000mm, 세로 400~3,000mm). 문틀 두께를 제외한 값이 문짝 외경으로 자동 계산됩니다.</td></tr>
        <tr><td><strong>좌우 / 상하 울거미 두께</strong></td><td>외곽 프레임 두께 (mm)</td></tr>
        <tr><td><strong>살 두께</strong></td><td>살 단면 두께 (mm)</td></tr>
        <tr><td><strong>세로 비율</strong></td><td>1.0~3.0. 랜덤 생성이 칸을 나눌 때 기준으로 삼는 칸의 가로:세로 비율입니다. 값이 클수록 세로로 긴 칸이 많아집니다.</td></tr>
        <tr><td><strong>풍판 사용</strong></td><td>체크 시 상단 풍판 구역 추가, 풍판 높이 별도 설정</td></tr>
        <tr><td><strong>치수 표기 / 문틀 표시</strong></td><td>캔버스에 실측 치수와 문틀 윤곽선을 표시할지 선택</td></tr>
    </tbody>
</table>

<h2>랜덤 패턴</h2>
<table class="guide-table">
    <thead><tr><th>버튼</th><th>기능</th></tr></thead>
    <tbody>
        <tr><td><span class="guide-ui">랜덤 생성</span></td><td>내경 전체를 무작위로 나눠 새 패턴을 만듭니다. 누를 때마다 다른 구성이 나옵니다.</td></tr>
        <tr><td><span class="guide-ui">초기화</span></td><td>랜덤 패턴을 걷어내고 균등 격자로 되돌립니다. 랜덤 패턴이 있을 때만 보입니다.</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>랜덤 생성을 다시 누르거나 초기화하면 지금 패턴은 사라집니다. 마음에 드는 결과가 나오면 먼저 저장하세요.</span>
</div>

<h2>선 다듬기</h2>
<p>랜덤으로 나온 구성을 그대로 쓰지 않고 손으로 고칠 수 있습니다.</p>
<table class="guide-table">
    <thead><tr><th>동작</th><th>방법</th></tr></thead>
    <tbody>
        <tr><td><strong>분할선 옮기기</strong></td><td>캔버스에서 살을 잡고 끌면 그 선이 수직 또는 수평으로만 움직입니다. 맞닿은 칸들의 크기가 함께 바뀌어 빈틈이나 겹침이 생기지 않습니다.</td></tr>
        <tr><td><strong>선 추가</strong></td><td>툴바 <span class="guide-ui">선 편집 ▾</span> → <span class="guide-ui">선 추가</span>를 누른 뒤 시작점과 끝점을 차례로 클릭합니다. 교점뿐 아니라 살이나 울거미 위 아무 지점에서나 시작할 수 있고, 선은 수직·수평으로만 그어집니다.</td></tr>
        <tr><td><strong>그린 선 옮기기</strong></td><td>직접 그은 선도 잡고 끌어 옮길 수 있습니다. 그 선에 붙어 있는 다른 그린 선들도 같이 따라오며, 울거미 안쪽 범위를 벗어나지 않습니다.</td></tr>
        <tr><td><strong>선 삭제</strong></td><td>툴바 <span class="guide-ui">선 편집 ▾</span> → <span class="guide-ui">선 삭제</span>를 누른 뒤 지울 살을 클릭합니다. 한 번 더 클릭하면 복구됩니다.</td></tr>
        <tr><td><strong>편집 초기화</strong></td><td>툴바 <span class="guide-ui">선 편집 ▾</span> → <span class="guide-ui">편집 초기화</span>로 추가·삭제한 선과 칠한 면색을 모두 되돌립니다.</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>분할선 드래그는 선 추가·선 삭제·이동·면컬러 칠하기 모드가 모두 꺼져 있을 때 동작합니다. 선이 안 잡히면 툴바에서 켜져 있는 모드가 없는지 확인하세요. 툴바 버튼은 <a href="/guide/canvas-toolbar">캔버스 툴바</a>에서 자세히 설명합니다.</span>
</div>

<h2>시방서 탭 — 제작 시방서 &amp; 부재 목록</h2>
<p>
    문틀 가로/세로, 외경·내경 가로/세로, 상/하 울거미, 풍판 높이, 전체 문폭(미서기는 겹침 포함)은 다른 엔진과 같이 자동 계산됩니다.
    다만 칸 크기가 제각각이라 <strong>가로·세로 칸수, 먹줄, 반턱 너비, 울거미홈폭처럼 균등 격자를 전제로 한 항목은 랜덤 패턴이 있을 때 표시되지 않습니다.</strong>
    부재 목록의 가로살·세로살 길이도 살마다 달라 하나의 값 대신 "가변"으로 표시됩니다.
</p>

<h2>마감 탭</h2>
<p>수종·부자재와 <strong>색칠할 부위</strong>(문틀·울거미·살·면)별 색 고르기는 세살과 같습니다. 부위에서 <strong>면</strong>을 고르면 도면의 칸을 클릭해 원하는 칸만 색을 채울 수 있습니다(면컬러 칠하기).</p>

<h2>활용 예시</h2>
<ul>
    <li>거실·카페의 포인트 파티션 창호</li>
    <li>아파트 중문처럼 현대 공간에 들이는 비정형 창호</li>
    <li>면컬러 칠하기와 함께 쓰는 색면 구성 창호</li>
</ul>

<?php include __DIR__ . '/_foot.php'; ?>
