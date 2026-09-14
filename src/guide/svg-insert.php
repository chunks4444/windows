<?php
$guide_current = 'svg-insert.php';
$guide_title   = '문양 삽입 & SVG 업로드';
$guide_prev    = ['href' => 'canvas-toolbar.php', 'title' => '캔버스 툴바'];
$guide_next    = ['href' => 'drawing.php', 'title' => '도면 저장 & 불러오기'];
include __DIR__ . '/_head.php';
?>

<h1>문양 삽입 &amp; SVG 업로드</h1>
<p class="guide-lead">
    사이드바의 <strong>문양 삽입</strong> 섹션에서 미리 등록된 문양을 골라 쓰거나, 직접 만든 SVG 파일을 업로드해
    캔버스 위에 자유롭게 배치할 수 있습니다.
</p>

<h2>라이브러리에서 선택</h2>
<ol class="guide-steps">
    <li>사이드바 <span class="guide-ui">문양 삽입</span> 섹션의 <span class="guide-ui">라이브러리</span> 버튼을 클릭합니다.</li>
    <li>등록된 문양 목록에서 원하는 문양을 클릭하면 캔버스 중앙에 바로 삽입됩니다.</li>
</ol>

<h2>내 SVG 파일 업로드</h2>
<ol class="guide-steps">
    <li>사이드바 <span class="guide-ui">문양 삽입</span> 섹션의 <span class="guide-ui">업로드</span> 버튼을 클릭합니다.</li>
    <li>내 컴퓨터에 있는 <code>.svg</code> 파일을 선택합니다.</li>
    <li>업로드가 끝나면 캔버스 중앙에 자동으로 삽입됩니다.</li>
</ol>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>업로드한 SVG는 로그인한 계정에만 저장되며, 파일 크기는 최대 2MB까지 지원합니다. 배경을 꽉 채우는 단색 사각형(예: 캔버스용 배경 레이어)은 업로드 시 자동으로 제거되어 투명 배경으로 저장됩니다.</span>
</div>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>스크립트가 포함되어 있거나 형식이 올바르지 않은 SVG 파일은 업로드가 거부됩니다.</span>
</div>

<h2>삽입 후 조정</h2>
<p>
    문양을 삽입하면 자동으로 <strong>스케일/이동/변형</strong> 모드가 되며, 사이드바에 크기·회전 슬라이더와
    복제·삭제 버튼이 나타납니다.
</p>
<table class="guide-table">
    <thead><tr><th>동작</th><th>방법</th></tr></thead>
    <tbody>
        <tr><td>이동</td><td>캔버스에서 문양을 드래그</td></tr>
        <tr><td>크기·회전</td><td>모서리 핸들을 드래그하거나 사이드바 슬라이더 사용</td></tr>
        <tr><td>여러 개 선택</td><td>Shift + 클릭으로 문양을 추가·해제하며 한 번에 이동·조정</td></tr>
        <tr><td>복제</td><td>사이드바의 <span class="guide-ui">복제</span> 버튼</td></tr>
        <tr><td>삭제</td><td>사이드바의 <span class="guide-ui">삭제</span> 버튼</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>문양 삽입은 6개 엔진(세살·정자살·빗살·격자 빗살·세모 솟을살·육모 솟을살) 모두 동일하게 제공되는 공통 기능입니다.</span>
</div>

<?php include __DIR__ . '/_foot.php'; ?>
