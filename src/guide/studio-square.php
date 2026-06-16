<?php
$guide_current = 'studio-square.php';
$guide_title   = 'Square Lattice';
$guide_prev    = ['href' => 'studio-classic.php', 'title' => 'Classic Lattice'];
$guide_next    = ['href' => 'studio-cross.php', 'title' => 'Cross Lattice'];
include __DIR__ . '/_head.php';
?>

<h1>Square Lattice</h1>
<p class="guide-lead">
    수직·수평 살만으로 구성된 가장 단순한 정방형 격자 패턴입니다.
    Classic과 달리 테두리 안의 살 배열이 균일하게 분할되어, 현대적이고 정제된 창호 디자인에 적합합니다.
</p>

<h2>Classic과의 차이점</h2>
<table class="guide-table">
    <thead><tr><th></th><th>Square Lattice</th><th>Classic Lattice</th></tr></thead>
    <tbody>
        <tr><td>살 방향</td><td>수직 + 수평</td><td>수직 + 수평</td></tr>
        <tr><td>구조</td><td>균등 분할 격자</td><td>사분턱 전통 구조</td></tr>
        <tr><td>용도</td><td>현대 미니멀 창호</td><td>전통 한옥 창호</td></tr>
    </tbody>
</table>

<h2>주요 파라미터</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td><strong>가로 분할 수</strong></td><td>수평 방향으로 격자를 나누는 칸 수</td></tr>
        <tr><td><strong>세로 분할 수</strong></td><td>수직 방향으로 격자를 나누는 칸 수</td></tr>
        <tr><td><strong>살 두께</strong></td><td>각 살의 폭 (mm)</td></tr>
        <tr><td><strong>테두리 두께</strong></td><td>외곽 프레임 두께 (mm)</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>가로·세로 분할 수를 동일하게 설정하면 완전한 정방형(正方形) 격자가 만들어집니다.</span>
</div>

<h2>활용 예시</h2>
<ul>
    <li>현대 한옥의 미서기 창문</li>
    <li>카페·상업 공간의 파티션 창호</li>
    <li>심플한 장지문 패턴</li>
</ul>

<?php include __DIR__ . '/_foot.php'; ?>
