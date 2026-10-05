<?php
$guide_current = 'export.php';
$guide_title   = 'PDF / PNG / DXF 내보내기';
$guide_prev    = ['href' => 'drawing.php', 'title' => '도면 저장 & 불러오기'];
$guide_next    = ['href' => 'render.php', 'title' => 'AI 렌더링 사용법'];
include __DIR__ . '/_head.php';
?>

<h1>PDF / PNG / DXF 내보내기</h1>
<p class="guide-lead">
    완성된 도면을 PDF·PNG·DXF 파일로 내보내 인쇄·공방 전달·CAD 작업에 활용할 수 있습니다.
    모든 내보내기는 왼쪽 메뉴의 <span class="guide-ui"><i class="bi bi-box-arrow-down"></i> 내보내기</span> 탭에서 하며, 로그인이 필요합니다.
</p>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>파일명은 <em>도면이름_버전.확장자</em>(예: <em>청담동_정면창_v3.pdf</em>)로 자동으로 붙습니다. 저장하지 않은 도면은 <em>창호도면</em>이라는 이름으로 나갑니다.</span>
</div>

<h2>PDF 내보내기</h2>
<ol class="guide-steps">
    <li>내보내기 탭의 <span class="guide-ui">PDF</span> 버튼을 클릭합니다.</li>
    <li>A4 가로 용지에 도면이 맞춰진 PDF 파일이 바로 다운로드됩니다.</li>
</ol>
<p>인쇄하거나 공방에 도면을 전달할 때 쓰기 좋습니다.</p>

<h2>PNG 내보내기</h2>
<ol class="guide-steps">
    <li>내보내기 탭의 <span class="guide-ui">PNG</span> 버튼을 클릭합니다.</li>
    <li>흰 바탕의 고해상도 도면 이미지가 바로 다운로드됩니다.</li>
</ol>
<p>메신저·이메일로 도면을 공유하거나 검토용으로 보낼 때 편리합니다.</p>

<h2>DXF 내보내기</h2>
<p>
    도면을 CAD 프로그램(AutoCAD 등)에서 바로 열 수 있는 DXF 파일로 내보낼 수 있습니다.
    실측 mm 단위 좌표로 저장되어 CAD에서 별도 스케일 조정 없이 바로 치수를 확인할 수 있습니다.
</p>
<ol class="guide-steps">
    <li>내보내기 탭의 <span class="guide-ui">DXF</span> 버튼을 클릭합니다.</li>
    <li>DXF 파일이 바로 다운로드됩니다.</li>
</ol>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>살(격자) 교차선과 울거미 외곽선이 각 부재 폭 그대로의 사각형 폴리라인으로 나갑니다. 촉(사개) 돌출부와 풍판 내부 채움판은 아직 포함되지 않으며, 살 교차부는 홈 형상 없이 단순 겹침으로 표현되는 형상 참고용 1차 버전입니다.</span>
</div>

<h2>용도별 추천 형식</h2>
<table class="guide-table">
    <thead><tr><th>용도</th><th>권장 형식</th></tr></thead>
    <tbody>
        <tr><td>인쇄·공방 전달</td><td>PDF</td></tr>
        <tr><td>메신저·이메일 공유, 검토</td><td>PNG</td></tr>
        <tr><td>CAD 작업·정밀 치수 확인</td><td>DXF</td></tr>
        <tr><td>실제 공간에 넣은 모습 보기</td><td><a href="/guide/render">AI 렌더링</a></td></tr>
    </tbody>
</table>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>렌더링 탭에서 올린 공간 사진은 내보내기 파일에 포함되지 않습니다. 도면만 출력됩니다.</span>
</div>

<?php include __DIR__ . '/_foot.php'; ?>
