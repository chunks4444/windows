<?php
$guide_current = 'export.php';
$guide_title   = 'PDF / PNG / DXF 내보내기';
$guide_prev    = ['href' => 'drawing.php', 'title' => '도면 저장 & 불러오기'];
$guide_next    = ['href' => 'render.php', 'title' => 'AI 렌더링 사용법'];
include __DIR__ . '/_head.php';
?>

<h1>PDF / PNG / DXF 내보내기</h1>
<p class="guide-lead">
    완성된 도면을 PDF 또는 PNG 파일로 내보내 납품·인쇄·협업에 활용할 수 있습니다.
    캔버스에 보이는 그대로 고해상도로 출력됩니다.
</p>

<h2>PNG 내보내기</h2>
<ol class="guide-steps">
    <li>툴바 우측의 <span class="guide-ui">PNG 저장</span> 버튼을 클릭합니다.</li>
    <li>현재 캔버스 뷰를 기준으로 PNG 파일이 즉시 다운로드됩니다.</li>
    <li>파일명은 <em>도면이름_날짜.png</em> 형식으로 자동 생성됩니다.</li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>PNG는 투명 배경을 지원하므로 다른 도구에서 합성·편집에 활용하기 좋습니다.</span>
</div>

<h2>PDF 내보내기</h2>
<ol class="guide-steps">
    <li>툴바의 <span class="guide-ui">PDF 저장</span> 버튼을 클릭합니다.</li>
    <li>용지 방향(가로/세로)과 용지 크기(A4·A3 등)를 선택합니다.</li>
    <li><span class="guide-ui">PDF 생성</span>을 클릭하면 파일이 다운로드됩니다.</li>
</ol>

<h2>DXF 내보내기</h2>
<p>
    도면을 CAD 프로그램(AutoCAD 등)에서 바로 열 수 있는 DXF 파일로 내보낼 수 있습니다.
    실측 mm 단위 좌표로 저장되어 CAD에서 별도 스케일 조정 없이 바로 치수를 확인할 수 있습니다.
</p>
<ol class="guide-steps">
    <li>툴바의 <span class="guide-ui">내보내기</span> 버튼을 클릭합니다.</li>
    <li>메뉴에서 <span class="guide-ui">DXF</span>를 선택합니다.</li>
    <li>DXF 파일이 즉시 다운로드됩니다.</li>
</ol>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>살(격자) 교차선과 울거미 외곽선이 각 부재 폭 그대로의 사각형 폴리라인으로 나갑니다. 촉(사개) 돌출부와 풍판 내부 채움판은 아직 포함되지 않으며, 살 교차부는 홈 형상 없이 단순 겹침으로 표현되는 형상 참고용 1차 버전입니다.</span>
</div>

<h2>내보내기 설정 팁</h2>
<table class="guide-table">
    <thead><tr><th>용도</th><th>권장 형식</th><th>설정</th></tr></thead>
    <tbody>
        <tr><td>납품·인쇄용</td><td>PDF</td><td>A3 이상, 가로 방향</td></tr>
        <tr><td>디지털 공유·검토</td><td>PNG</td><td>기본 해상도</td></tr>
        <tr><td>AI 렌더링 합성</td><td>PNG</td><td>배경 투명 유지</td></tr>
        <tr><td>프레젠테이션</td><td>PDF</td><td>A4, 세로 방향</td></tr>
        <tr><td>CAD 작업·정밀 치수 확인</td><td>DXF</td><td>실측 mm 좌표</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>PDF 내보내기는 브라우저의 jsPDF 라이브러리를 사용합니다. 대용량 도면은 생성에 수 초가 걸릴 수 있습니다.</span>
</div>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>배경 이미지(AI 렌더링용 사진)는 PDF 내보내기에 포함되지 않습니다. 도면 살(격자) 레이어만 출력됩니다.</span>
</div>

<?php include __DIR__ . '/_foot.php'; ?>
