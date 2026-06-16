<?php
$guide_current = 'drawing.php';
$guide_title   = '도면 저장 & 불러오기';
$guide_prev    = ['href' => 'studio-hexagon.php', 'title' => 'Hexagon Lattice'];
$guide_next    = ['href' => 'export.php', 'title' => 'PDF / PNG 내보내기'];
include __DIR__ . '/_head.php';
?>

<h1>도면 저장 & 불러오기</h1>
<p class="guide-lead">
    작업한 도면은 클라우드에 저장되어 언제든 다시 불러올 수 있습니다.
    버전 히스토리도 자동으로 관리되어 이전 작업 상태로 되돌릴 수 있습니다.
</p>

<h2>도면 저장하기</h2>
<ol class="guide-steps">
    <li>사이드바 상단 <span class="guide-ui">도면 이름</span> 입력란에 도면 이름을 입력합니다.</li>
    <li>툴바의 <span class="guide-ui">저장</span> 버튼을 클릭하거나 <kbd>Ctrl</kbd>+<kbd>S</kbd> (<kbd>⌘S</kbd>)를 누릅니다.</li>
    <li>처음 저장 시 새 도면이 생성되고, 이후 동일 이름으로 저장하면 버전이 쌓입니다.</li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>도면 이름은 프로젝트명·현장명으로 관리하면 대시보드에서 찾기 쉽습니다. 예: <em>청담동_한옥_정면창</em></span>
</div>

<h2>도면 불러오기</h2>
<ol class="guide-steps">
    <li>스튜디오 사이드바에서 <span class="guide-ui">도면 목록</span> 버튼을 클릭합니다.</li>
    <li>저장된 도면 목록에서 불러올 도면을 클릭합니다.</li>
    <li>도면의 파라미터가 캔버스에 복원됩니다.</li>
</ol>

<h2>버전 관리</h2>
<p>도면을 저장할 때마다 버전이 생성됩니다. 버전 히스토리를 통해 이전 상태로 되돌릴 수 있습니다.</p>
<ol class="guide-steps">
    <li>사이드바 버전 목록에서 되돌리고 싶은 버전을 클릭합니다.</li>
    <li>해당 버전의 파라미터가 캔버스에 반영됩니다.</li>
    <li>확인 후 현재 버전으로 다시 저장하면 최신 버전이 됩니다.</li>
</ol>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>버전은 도면당 최대 저장 횟수까지 보관됩니다. 오래된 버전부터 자동으로 정리됩니다.</span>
</div>

<h2>대시보드에서 관리</h2>
<p>상단 내비게이션 <span class="guide-ui">Studio</span> → <span class="guide-ui">Dashboard</span>에서 모든 도면을 한눈에 관리할 수 있습니다.</p>
<ul>
    <li>엔진별 탭으로 도면을 분류해 볼 수 있습니다.</li>
    <li>썸네일 이미지로 도면을 빠르게 식별할 수 있습니다.</li>
    <li>도면 이름 변경, 삭제가 가능합니다.</li>
    <li>작업 시간이 기록됩니다.</li>
</ul>

<h2>도면 이름 변경</h2>
<ol class="guide-steps">
    <li>사이드바의 도면 이름 입력란에서 이름을 수정합니다.</li>
    <li><span class="guide-ui">이름 변경</span> 버튼을 클릭합니다.</li>
    <li>대시보드에도 즉시 반영됩니다.</li>
</ol>

<?php include __DIR__ . '/_foot.php'; ?>
