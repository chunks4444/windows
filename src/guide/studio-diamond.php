<?php
$guide_current = 'studio-diamond.php';
$guide_title   = '격자 빗살';
$guide_prev    = ['href' => 'studio-cross.php', 'title' => '빗살'];
$guide_next    = ['href' => 'studio-triangle.php', 'title' => '세모 솟을살'];
include __DIR__ . '/_head.php';
?>

<h1><span class="guide-h1-icon"><?= $guideEngineIcons['diamond'] ?></span>격자 빗살</h1>
<p class="guide-lead">
    수직·수평·대각선(±45°) 살을 모두 결합한 복합 격자 패턴입니다.
    마름모 셀 안에 작은 격자가 형성되어 세밀하고 정교한 전통 창호 느낌을 구현합니다.
</p>

<h2>살 구성</h2>
<table class="guide-table">
    <thead><tr><th>살 방향</th><th>각도</th><th>역할</th></tr></thead>
    <tbody>
        <tr><td>수직살</td><td>90°</td><td>세로 방향 기본 골격</td></tr>
        <tr><td>수평살</td><td>0°</td><td>가로 방향 기본 골격</td></tr>
        <tr><td>사선살 A</td><td>45°</td><td>우상향 대각선</td></tr>
        <tr><td>사선살 B</td><td>135°</td><td>좌상향 대각선</td></tr>
    </tbody>
</table>

<h2>주요 파라미터</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td><strong>격자 간격</strong></td><td>수직·수평살 사이의 간격 (mm)</td></tr>
        <tr><td><strong>사선 밀도</strong></td><td>대각선 살의 간격. 간격이 좁을수록 복잡해집니다.</td></tr>
        <tr><td><strong>살 두께</strong></td><td>전체 살의 기본 두께 (mm)</td></tr>
        <tr><td><strong>테두리 두께</strong></td><td>외곽 프레임 두께 (mm)</td></tr>
    </tbody>
</table>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>사선 밀도를 지나치게 높이면 살이 겹쳐 실제 제작이 어려울 수 있습니다. PDF로 출력해 실측을 확인하세요.</span>
</div>

<h2>활용 예시</h2>
<ul>
    <li>궁궐·사찰의 아자살(亞字窓) 변형 패턴</li>
    <li>고급 한옥 펜션 창호</li>
    <li>전통 공예 전시관 파티션</li>
</ul>

<?php include __DIR__ . '/_foot.php'; ?>
