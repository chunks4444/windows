<?php
$guide_current = 'svg-insert.php';
$guide_title   = '문양 삽입 & SVG 업로드';
$guide_prev    = ['href' => 'canvas-toolbar.php', 'title' => '캔버스 툴바'];
$guide_next    = ['href' => 'drawing.php', 'title' => '도면 저장 & 불러오기'];
include __DIR__ . '/_head.php';
?>

<h1>문양 삽입 &amp; SVG 업로드</h1>
<p class="guide-lead">
    왼쪽 메뉴의 <strong>문양</strong> 탭에서 미리 등록된 문양을 골라 쓰거나, 직접 만든 SVG 파일을 올려
    캔버스 위에 자유롭게 배치할 수 있습니다.
</p>

<h2>라이브러리에서 선택</h2>
<ol class="guide-steps">
    <li>왼쪽 메뉴의 <span class="guide-ui"><i class="bi bi-flower1"></i> 문양</span> 탭을 엽니다.</li>
    <li><strong>문양 라이브러리</strong>에서 원하는 문양을 클릭하면 캔버스 가운데에 바로 들어갑니다.</li>
    <li>문양 오른쪽 위의 <i class="bi bi-download"></i> 버튼으로 SVG 파일을 내려받을 수도 있습니다.</li>
</ol>

<h2>내 SVG 파일 업로드</h2>
<ol class="guide-steps">
    <li>문양 탭 맨 위의 <span class="guide-ui"><i class="bi bi-upload"></i> SVG 업로드</span> 버튼을 클릭합니다.</li>
    <li>내 컴퓨터에 있는 <code>.svg</code> 파일을 선택합니다.</li>
    <li>업로드가 끝나면 캔버스 가운데에 자동으로 들어가고, <strong>내가 올린 문양</strong> 목록에도 추가됩니다. 다음부터는 목록에서 클릭해 다시 쓸 수 있습니다.</li>
</ol>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>업로드한 SVG는 로그인한 계정에만 저장되며, 파일 크기는 최대 2MB까지 지원합니다. 배경을 꽉 채우는 단색 사각형(예: 캔버스용 배경 레이어)은 업로드 시 자동으로 제거되어 투명 배경으로 저장됩니다.</span>
</div>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>스크립트가 포함되어 있거나 형식이 올바르지 않은 SVG 파일은 업로드가 거부됩니다. 내가 올린 문양을 <i class="bi bi-trash3"></i>로 지우면, 그 문양을 넣어 둔 도면에서도 문양이 보이지 않게 됩니다.</span>
</div>

<h2>삽입 후 조정</h2>
<p>
    문양을 넣으면 바로 선택된 상태가 되어 테두리에 핸들이 생깁니다. 나중에 다시 고치려면 캔버스 위 툴바의
    <span class="guide-ui"><i class="bi bi-cursor"></i> 선택</span>을 켜고 문양을 클릭합니다.
</p>

<table class="guide-table">
    <thead><tr><th>동작</th><th>방법</th></tr></thead>
    <tbody>
        <tr><td>이동</td><td>문양을 드래그</td></tr>
        <tr><td>크기 조절</td><td>모서리·변의 핸들을 드래그</td></tr>
        <tr><td>회전</td><td>테두리 위쪽의 회전 핸들을 드래그</td></tr>
        <tr><td>삭제</td><td>문양을 고른 뒤 <kbd>Delete</kbd> 또는 <kbd>Backspace</kbd> 키</td></tr>
        <tr><td>모두 지우기</td><td>툴바 <span class="guide-ui">도형 ▾</span> → <span class="guide-ui">모두 삭제</span> (도형·텍스트도 함께 지워짐)</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>문양 삽입은 7개 엔진(세살·정자살·빗살·격자빗살·세모솟을살·육모솟을살·몬드리안) 모두 동일하게 제공되는 공통 기능입니다.</span>
</div>

<?php include __DIR__ . '/_foot.php'; ?>
