<?php
$guide_current = 'studio-hexagon.php';
$guide_title   = '육모 솟을살';
$guide_prev    = ['href' => 'studio-triangle.php', 'title' => '세모 솟을살'];
$guide_next    = ['href' => 'drawing.php', 'title' => '도면 저장 & 불러오기'];
include __DIR__ . '/_head.php';
?>

<h1><span class="guide-h1-icon"><?= $guideEngineIcons['hexagon'] ?></span>육모 솟을살</h1>
<p class="guide-lead">
    세모 솟을살에서 수직 방향(0°) 살을 제거해 육각형(육모살) 셀을 형성하는 패턴입니다.
    벌집 구조처럼 60°·120° 두 방향 사선살만으로 구성되어 독특하고 우아한 느낌을 줍니다.
</p>

<h2>세모 솟을살과의 차이</h2>
<table class="guide-table">
    <thead><tr><th></th><th>세모 솟을살</th><th>육모 솟을살</th></tr></thead>
    <tbody>
        <tr><td>살 방향 수</td><td>3가지 (0°, 60°, 120°)</td><td>2가지 (60°, 120°)</td></tr>
        <tr><td>셀 형태</td><td>삼각형</td><td>육각형</td></tr>
        <tr><td>살 밀도</td><td>높음</td><td>보통</td></tr>
    </tbody>
</table>

<h2>주요 파라미터</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td><strong>육각 크기</strong></td><td>육각형 셀의 크기를 결정하는 기준값 (mm)</td></tr>
        <tr><td><strong>살 두께</strong></td><td>각 살의 폭 (mm)</td></tr>
        <tr><td><strong>테두리 두께</strong></td><td>외곽 프레임 두께 (mm)</td></tr>
    </tbody>
</table>

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
