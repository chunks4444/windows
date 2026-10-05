<?php
$guide_current = 'finish.php';
$guide_title   = '마감 & 컬러 샘플';
$guide_prev    = ['href' => 'studio-mondrian.php', 'title' => '몬드리안'];
$guide_next    = ['href' => 'canvas-toolbar.php', 'title' => '캔버스 툴바'];
include __DIR__ . '/_head.php';
?>

<h1>마감 &amp; 컬러 샘플</h1>
<p class="guide-lead">
    스튜디오 왼쪽 메뉴의 <strong>마감</strong> 탭에서 수종과 부자재를 고르고, 문틀·울거미·살·면 부위마다 마감 색을 정합니다.
    7개 엔진 모두 같은 방식이며, 고른 마감은 예상 견적에 바로 반영됩니다. 아래에서 고를 수 있는 모든 색 견본을 확인할 수 있습니다.
</p>

<div class="guide-screenshot">
    <img src="/src/img/guide/finish.png" alt="마감 탭을 연 화면 — 수종·부자재, 색칠할 부위(문틀·울거미·살·면), 기본 마감, 수성스테인(AURO 560)·유성스테인(AURO 930) 색 견본, 캔버스에는 살·울거미를 AURO 560 navy로 칠한 도면" loading="lazy">
</div>

<h2>마감 탭 사용 순서</h2>
<ol class="guide-steps">
    <li>왼쪽 메뉴의 <span class="guide-ui"><i class="bi bi-palette"></i> 마감</span> 탭을 엽니다.</li>
    <li><strong>수종</strong>과 <strong>부자재</strong>를 고릅니다. 부자재가 필요 없으면 <span class="guide-ui">부자재 없음</span>을 고릅니다.</li>
    <li><strong>색칠할 부위</strong>에서 <strong>문틀 · 울거미 · 살 · 면</strong> 중 하나를 누릅니다. 부위를 고르지 않고 색을 누르면 안내가 뜹니다.</li>
    <li>아래 <strong>기본</strong> 마감이나 스테인 색 견본에서 색을 누르면 그 부위에 바로 칠해집니다. 색을 누르면 그 색이 속한 마감 종류도 함께 선택됩니다.</li>
    <li>부위를 바꿔 가며 나머지 부위도 같은 방법으로 칠합니다. 현재 고른 부위의 색 이름은 부위 버튼 바로 아래에 표시됩니다.</li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span><strong>면</strong>을 고른 뒤 색을 누르면 <strong>면컬러 칠하기</strong>가 켜집니다. 도면의 칸을 클릭하면 그 칸만 칠해지고, 오른쪽 클릭은 지우기입니다. 마감 탭을 벗어나면 칠하기는 자동으로 꺼집니다.</span>
</div>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span><strong>마감 종류는 문 전체에 하나</strong>입니다. 부위마다 색은 다르게 칠할 수 있지만, 다른 스테인의 색을 누르면 마감 종류가 바뀌고 이미 칠한 부위도 그 스테인에서 가장 가까운 색으로 바뀝니다. 들기름·오일마감을 고르면 문틀·울거미·살이 한 가지 오일 색으로 칠해지고 면 색은 지워집니다.</span>
</div>

<h2>마감 종류</h2>
<table class="guide-table">
    <thead><tr><th>마감</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td><strong>마감 없음</strong></td><td>칠하지 않은 원목 그대로입니다.</td></tr>
        <tr><td><strong>들기름</strong></td><td>전통 천연 오일 마감. 나무결이 그대로 보이며, 한 가지 색으로 칠해집니다.</td></tr>
        <tr><td><strong>오일마감 (AURO 126)</strong></td><td>천연 오일 마감. 색은 고른 수종에 따라 정해집니다.</td></tr>
        <tr><td><strong>수성스테인 (AURO 560)</strong></td><td>나무결이 비치는 수성 착색 마감. 아래 색 견본에서 색을 고릅니다.</td></tr>
        <tr><td><strong>유성스테인 (AURO 930)</strong></td><td>나무결이 비치는 유성 착색 마감. 아래 색 견본에서 색을 고릅니다.</td></tr>
    </tbody>
</table>

<h2>컬러 샘플</h2>
<p>마감 탭에 나오는 색 전체입니다. 색 이름 아래의 코드는 공방에서 도장할 때 쓰는 제조사(AURO) 색 코드입니다.</p>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>화면의 색은 모니터에 따라 다르게 보이고, 실제 색은 수종과 나무결, 도포 횟수에 따라 달라집니다. 정확한 색이 중요하면 견적요청 때 메모로 실물 샘플을 요청해 주세요.</span>
</div>

<h3>기본</h3>
<?php $sw_mode = 'basic'; include __DIR__ . '/_swatches.php'; ?>

<?php $sw_mode = 'stain'; include __DIR__ . '/_swatches.php'; ?>

<?php include __DIR__ . '/_foot.php'; ?>
