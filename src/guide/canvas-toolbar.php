<?php
$guide_current = 'canvas-toolbar.php';
$guide_title   = '캔버스 툴바';
$guide_prev    = ['href' => 'studio-mondrian.php', 'title' => '몬드리안'];
$guide_next    = ['href' => 'svg-insert.php', 'title' => '문양 삽입 & SVG 업로드'];
include __DIR__ . '/_head.php';
?>

<h1>캔버스 툴바</h1>
<p class="guide-lead">
    캔버스 위쪽에는 도면을 다루는 버튼이 두 묶음으로 떠 있습니다.
    <strong>왼쪽 위</strong>에는 선택·이동·선 편집·도형·배치 버튼과 현재 문짝·문틀 치수가,
    <strong>오른쪽 위</strong>에는 확대·축소와 화면 맞춤 버튼이 있습니다.
    7개 엔진(세살·정자살·빗살·격자빗살·세모솟을살·육모솟을살·몬드리안) 모두 같은 툴바를 씁니다.
</p>

<h2>왼쪽 위 — 도구</h2>
<p>
    버튼마다 아이콘 아래 이름이 붙어 있고, 켜진 모드는 <strong>검은색</strong>으로 표시됩니다.
    모드는 한 번에 하나만 켜지며, 다른 버튼을 누르면 앞의 모드는 자동으로 꺼집니다. 켜진 버튼을 다시 누르면 꺼집니다.
</p>
<table class="guide-table">
    <thead><tr><th>버튼</th><th>기능</th><th>사용 방법</th></tr></thead>
    <tbody>
        <tr><td><i class="bi bi-cursor"></i> 선택</td><td>살·도형·문양을 골라 편집</td><td>살을 클릭하면 위쪽에 작은 막대가 떠서 그 살만 색을 바꾸거나 지울 수 있습니다. 도형·문양을 클릭하면 테두리 핸들이 생겨 이동·크기 조절·회전이 가능합니다. 고른 것은 <kbd>Delete</kbd> 키로도 지울 수 있습니다.</td></tr>
        <tr><td><i class="bi bi-hand-index"></i> 이동</td><td>화면 이동(팬)</td><td>켠 상태에서 캔버스를 드래그하면 화면이 움직입니다.</td></tr>
        <tr><td><i class="bi bi-scissors"></i> 선 편집 <i class="bi bi-chevron-down"></i></td><td>살을 지우거나 더하는 메뉴</td><td>누르면 아래로 <strong>선 삭제 · 선 추가 · 편집 초기화</strong> 메뉴가 펼쳐집니다. 아래 <a href="#line-edit">선 편집</a> 참고.</td></tr>
        <tr><td><i class="bi bi-bounding-box"></i> 도형 <i class="bi bi-chevron-down"></i></td><td>메모용 도형·글자 추가 메뉴</td><td>누르면 <strong>원형 · 선 · 사각형 · 텍스트 · 모두 삭제</strong> 메뉴가 펼쳐집니다. 아래 <a href="#shapes">도형</a> 참고.</td></tr>
        <tr><td><i class="bi bi-aspect-ratio"></i> 배치</td><td>배경 사진 위에 문을 맞춰 넣기</td><td>문 네 모서리에 핸들이 생깁니다. 모서리를 하나씩 끌어 사진 속 문틀 자리에 맞추면 원근에 맞게 문이 비틀어져 들어갑니다. AI 렌더링 전에 쓰면 결과가 훨씬 자연스럽습니다.</td></tr>
        <tr><td><i class="bi bi-arrow-counterclockwise"></i> 배치 초기화</td><td>모서리 위치를 처음 상태로</td><td>배치로 모서리를 옮긴 뒤에만 나타납니다.</td></tr>
    </tbody>
</table>
<p>
    도구 묶음 오른쪽 끝에는 <strong>문짝</strong>(실제 문 크기)과 <strong>문틀</strong>(벽 개구부) 치수가 두 줄로 표시되어,
    값을 바꿀 때마다 바로 확인할 수 있습니다.
</p>

<h2 id="line-edit">선 편집</h2>
<table class="guide-table">
    <thead><tr><th>메뉴</th><th>기능</th><th>사용 방법</th></tr></thead>
    <tbody>
        <tr><td>선 삭제</td><td>자동으로 그려진 살을 지우는 모드</td><td>지울 살을 클릭 → 삭제. 지운 자리를 다시 클릭하면 복구됩니다.</td></tr>
        <tr><td>선 추가</td><td>격자에 없는 살을 새로 긋는 모드</td><td>① 시작 교점 클릭 → ② 끝 교점 클릭하면 그 사이에 살이 생깁니다.</td></tr>
        <tr><td>편집 초기화</td><td>선 삭제·추가로 바꾼 내용을 모두 되돌림</td><td>처음 자동으로 그려진 격자 상태로 돌아갑니다.</td></tr>
    </tbody>
</table>
<p>선 삭제나 선 추가가 켜져 있으면 <strong>선 편집</strong> 버튼 자체도 검은색으로 보여, 메뉴를 닫아도 어떤 모드인지 알 수 있습니다.</p>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>선 삭제·추가를 섞어 쓰면 자동으로 만들어진 격자에서 특정 살만 빼거나 원하는 자리에 살을 더해 나만의 패턴을 만들 수 있습니다. 편집한 살은 예상 견적에 자동으로 반영되지 않으며, 견적요청 후 담당자가 확인해 최종 견적을 정합니다.</span>
</div>

<h2 id="shapes">도형</h2>
<table class="guide-table">
    <thead><tr><th>메뉴</th><th>기능</th><th>사용 방법</th></tr></thead>
    <tbody>
        <tr><td>원형</td><td>원 추가</td><td>원하는 위치를 클릭하면 원이 놓입니다.</td></tr>
        <tr><td>선</td><td>직선 추가</td><td>시작점 클릭 → 끝점 클릭.</td></tr>
        <tr><td>사각형</td><td>사각형 추가</td><td>원하는 위치를 클릭하면 사각형이 놓입니다.</td></tr>
        <tr><td>텍스트</td><td>글자 추가</td><td>원하는 위치를 클릭하면 입력칸이 나타납니다. 나중에 고치려면 글자를 더블클릭합니다.</td></tr>
        <tr><td>모두 삭제</td><td>도형·텍스트·삽입한 문양을 한꺼번에 지움</td><td>살 편집 내용에는 영향을 주지 않습니다.</td></tr>
    </tbody>
</table>
<p>도형을 고르면 캔버스 위쪽에 <strong>선 색 · 채우기 색 · 두께 · 투명도</strong>를 바꾸는 막대가 나타납니다.</p>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>도형은 창살과 별도의 층에 그려지는 <strong>주석·메모용</strong>입니다. 견적·제작 시방서 계산에는 들어가지 않습니다.</span>
</div>

<h2>오른쪽 위 — 확대·축소</h2>
<table class="guide-table">
    <thead><tr><th>버튼</th><th>기능</th></tr></thead>
    <tbody>
        <tr><td><i class="bi bi-dash-lg"></i> / <i class="bi bi-plus-lg"></i></td><td>축소 / 확대. 마우스 휠, 트랙패드·터치 화면의 두 손가락 벌리기로도 됩니다.</td></tr>
        <tr><td><strong>100%</strong></td><td>현재 배율 표시. 누르면 화면 맞춤과 같이 동작합니다.</td></tr>
        <tr><td><i class="bi bi-arrow-repeat"></i> 화면 맞춤</td><td>확대·이동한 화면을 도면 전체가 보이는 처음 상태로 되돌립니다.</td></tr>
    </tbody>
</table>

<?php include __DIR__ . '/_foot.php'; ?>
