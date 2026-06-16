<?php
$guide_current = 'studio-classic.php';
$guide_title   = 'Classic Lattice';
$guide_prev    = ['href' => 'getting-started.php', 'title' => '시작하기'];
$guide_next    = ['href' => 'studio-square.php', 'title' => 'Square Lattice'];
include __DIR__ . '/_head.php';
?>

<h1>Classic Lattice</h1>
<p class="guide-lead">
    전통 한국 창호의 사분턱(四分턱) 구조를 재현한 엔진입니다.
    수직·수평 살이 일정 간격으로 교차하며, 문 종류·짝수·치수를 자유롭게 조합할 수 있습니다.
</p>

<h2>문 설정</h2>

<h3>문 종류</h3>
<table class="guide-table">
    <thead><tr><th>옵션</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td><strong>여닫이</strong></td><td>경첩으로 열리는 전통 여닫이문. 단일 프레임 기준으로 도면을 생성합니다.</td></tr>
        <tr><td><strong>미서기</strong></td><td>좌우로 미는 슬라이딩 문. 레일 구조를 포함한 도면을 생성합니다.</td></tr>
    </tbody>
</table>

<h3>문 짝수</h3>
<p>1짝 ~ 4짝을 선택할 수 있습니다. 짝수가 늘어날수록 전체 폭이 분할됩니다.</p>

<h2>문 치수</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>범위</th><th>단위</th></tr></thead>
    <tbody>
        <tr><td>가로폭</td><td>400 ~ 1,500</td><td>mm</td></tr>
        <tr><td>세로높이</td><td>600 ~ 2,400</td><td>mm</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>슬라이더를 드래그하거나 숫자 입력란을 직접 수정하면 더 정밀한 치수를 입력할 수 있습니다.</span>
</div>

<h2>격자 살 설정</h2>
<p>창호의 격자 패턴을 결정하는 핵심 파라미터입니다.</p>

<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td><strong>살 두께</strong></td><td>각 살(테두리·가로·세로살)의 두께 (mm)</td></tr>
        <tr><td><strong>가로살 수</strong></td><td>수평 방향 살의 개수</td></tr>
        <tr><td><strong>세로살 수</strong></td><td>수직 방향 살의 개수</td></tr>
        <tr><td><strong>테두리 두께</strong></td><td>외곽 프레임의 두께 (mm)</td></tr>
    </tbody>
</table>

<h2>색상 설정</h2>
<p>사이드바 하단의 색상 팔레트에서 살 색상을 지정합니다. 기본 색상 외에도 커스텀 HEX 값을 입력할 수 있습니다.</p>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>색상 설정은 PDF/PNG 내보내기와 AI 렌더링 합성 모두에 반영됩니다.</span>
</div>

<h2>캔버스 조작</h2>
<ul>
    <li><strong>확대/축소</strong> — 마우스 휠 또는 트랙패드 핀치</li>
    <li><strong>이동(패닝)</strong> — 스페이스바를 누른 상태에서 드래그</li>
    <li><strong>초기화</strong> — 툴바의 <span class="guide-ui">초기화</span> 버튼으로 뷰 리셋</li>
</ul>

<?php include __DIR__ . '/_foot.php'; ?>
