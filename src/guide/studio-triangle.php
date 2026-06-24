<?php
$guide_current = 'studio-triangle.php';
$guide_title   = '세모 솟을살';
$guide_prev    = ['href' => 'studio-diamond.php', 'title' => '격자 빗살'];
$guide_next    = ['href' => 'studio-hexagon.php', 'title' => '육모 솟을살'];
include __DIR__ . '/_head.php';
?>

<h1><span class="guide-h1-icon"><?= $guideEngineIcons['triangle'] ?></span>세모 솟을살</h1>
<p class="guide-lead">
    0°·60°·120° 세 방향 살이 교차하여 삼각형 셀을 형성하는 패턴입니다.
    기하학적인 아름다움이 특징이며, 동아시아 전통 목공예에서도 유사한 패턴을 찾아볼 수 있습니다.
</p>

<h2>살 구성</h2>
<table class="guide-table">
    <thead><tr><th>살 방향</th><th>각도</th></tr></thead>
    <tbody>
        <tr><td>수직살</td><td>0° (수직)</td></tr>
        <tr><td>우사선살</td><td>60°</td></tr>
        <tr><td>좌사선살</td><td>120°</td></tr>
    </tbody>
</table>

<h2>주요 파라미터</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td><strong>격자 크기</strong></td><td>삼각형 셀 한 변의 길이 (mm)</td></tr>
        <tr><td><strong>살 두께</strong></td><td>각 살의 폭 (mm)</td></tr>
        <tr><td><strong>테두리 두께</strong></td><td>외곽 프레임 두께 (mm)</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>세모 솟을살에서 한 방향 살을 제거하면 <strong>육모 솟을살</strong> 패턴이 됩니다. 두 엔진을 비교해 보세요.</span>
</div>

<h2>활용 예시</h2>
<ul>
    <li>전통 누각 천장 격자 패턴</li>
    <li>현대 건축 파사드 스크린</li>
    <li>인테리어 천장 조명 박스</li>
</ul>

<?php include __DIR__ . '/_foot.php'; ?>
