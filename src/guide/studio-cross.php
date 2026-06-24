<?php
$guide_current = 'studio-cross.php';
$guide_title   = '빗살';
$guide_prev    = ['href' => 'studio-square.php', 'title' => '정자살'];
$guide_next    = ['href' => 'studio-diamond.php', 'title' => '격자 빗살'];
include __DIR__ . '/_head.php';
?>

<h1><span class="guide-h1-icon"><?= $guideEngineIcons['cross'] ?></span>빗살</h1>
<p class="guide-lead">
    정자살을 45° 회전시킨 사선 격자 패턴입니다.
    대각선 방향 살이 교차하여 역동적이고 개성 있는 창호 디자인을 만들 수 있습니다.
</p>

<h2>특징</h2>
<ul>
    <li>기본 격자를 45° 회전하여 마름모꼴 셀이 형성됩니다.</li>
    <li>동일한 파라미터 체계를 정자살과 공유하므로 전환이 쉽습니다.</li>
    <li>클리핑(Clipping) 처리로 프레임 밖으로 살이 삐져나오지 않습니다.</li>
</ul>

<h2>주요 파라미터</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td><strong>격자 밀도</strong></td><td>대각선 방향 살의 간격. 숫자가 클수록 촘촘해집니다.</td></tr>
        <tr><td><strong>살 두께</strong></td><td>각 살의 폭 (mm)</td></tr>
        <tr><td><strong>회전 각도</strong></td><td>기본 45° 고정. 일부 파생 설정 가능</td></tr>
        <tr><td><strong>테두리 두께</strong></td><td>외곽 프레임 두께 (mm)</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>빗살과 정자살을 겹쳐 활용하면 더 복잡한 격자 빗살 패턴 효과를 낼 수 있습니다.</span>
</div>

<h2>활용 예시</h2>
<ul>
    <li>한옥 방등(防燈) 창살 패턴</li>
    <li>현대 인테리어 스크린 파티션</li>
    <li>전통 누각 난간 패턴</li>
</ul>

<?php include __DIR__ . '/_foot.php'; ?>
