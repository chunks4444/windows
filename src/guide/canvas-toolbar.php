<?php
$guide_current = 'canvas-toolbar.php';
$guide_title   = '캔버스 툴바';
$guide_prev    = ['href' => 'studio-hexagon.php', 'title' => '육모 솟을살'];
$guide_next    = ['href' => 'drawing.php', 'title' => '도면 저장 & 불러오기'];
include __DIR__ . '/_head.php';
?>

<h1>캔버스 툴바</h1>
<p class="guide-lead">
    캔버스 하단에는 화면 조작·선 편집·문양 배치·도형 그리기 버튼이 모여 있는 <strong>툴바</strong>가 있습니다.
    6개 엔진(세살·정자살·빗살·격자 빗살·세모 솟을살·육모 솟을살) 모두 동일하게 제공되는 공통 기능입니다.
</p>

<h2>보기 조작</h2>
<table class="guide-table">
    <thead><tr><th>아이콘</th><th>기능</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td><i class="bi bi-zoom-in"></i> 확대</td><td>캔버스 확대</td><td>휠을 위로 굴려도 동일하게 확대됩니다.</td></tr>
        <tr><td><i class="bi bi-zoom-out"></i> 축소</td><td>캔버스 축소</td><td>휠을 아래로 굴려도 동일하게 축소됩니다.</td></tr>
        <tr><td><i class="bi bi-hand-index"></i> 이동(팬)</td><td>캔버스를 드래그로 이동</td><td>도면을 클릭·드래그하면 화면이 이동합니다.</td></tr>
        <tr><td><i class="bi bi-arrow-counterclockwise"></i> 화면 초기화</td><td>확대·이동 상태를 기본값으로 리셋</td><td>줌·팬을 처음 캔버스를 열었을 때 상태로 되돌립니다.</td></tr>
    </tbody>
</table>

<h2>선 편집</h2>
<table class="guide-table">
    <thead><tr><th>아이콘</th><th>기능</th><th>사용 방법</th></tr></thead>
    <tbody>
        <tr><td><i class="bi bi-scissors"></i> 선 삭제</td><td>알고리즘이 생성한 살(선)을 지우는 편집 모드</td><td>지울 선을 클릭 → 삭제. 삭제된 선을 다시 클릭하면 복구됩니다.</td></tr>
        <tr><td><i class="bi bi-pencil"></i> 선 추가</td><td>격자에 없는 새로운 살(선)을 그려 넣는 편집 모드</td><td>① 시작 교점 클릭 → ② 끝 교점 클릭하면 그 사이에 선이 생성됩니다.</td></tr>
        <tr><td><i class="bi bi-arrow-clockwise"></i> 편집 초기화</td><td>선 삭제·추가로 바꾼 내용을 모두 원래대로 되돌림</td><td>알고리즘이 생성한 기본 격자 상태로 리셋합니다.</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span><strong>선 삭제/추가 모드</strong>를 조합하면 알고리즘이 만든 격자에서 특정 살만 빼거나 원하는 위치에 살을 더해 완전히 자유로운 패턴을 만들 수 있습니다.</span>
</div>

<h2>문양 배치</h2>
<table class="guide-table">
    <thead><tr><th>아이콘</th><th>기능</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td><i class="bi bi-arrows-move"></i> 스케일/이동/변형</td><td>삽입한 SVG 문양·이미지의 위치·크기·회전을 조정하는 모드</td><td>문양을 캔버스에 삽입한 직후 자동으로 이 모드가 되며, 모서리 핸들을 드래그해 크기·회전을 바꿀 수 있습니다.</td></tr>
        <tr><td><i class="bi bi-arrow-repeat"></i> 배치 초기화</td><td>문양의 위치·크기·회전을 초기값으로 되돌림</td><td>문양이 삽입되어 있을 때만 나타납니다.</td></tr>
    </tbody>
</table>

<h2>도형 그리기</h2>
<table class="guide-table">
    <thead><tr><th>아이콘</th><th>기능</th><th>사용 방법</th></tr></thead>
    <tbody>
        <tr><td><i class="bi bi-cursor"></i> 선택</td><td>살·도형을 선택해 편집하는 기본 모드</td><td>살을 클릭하면 색상 변경·삭제, 도형을 클릭하면 이동·크기 조절·회전이 가능합니다.</td></tr>
        <tr><td><i class="bi bi-circle"></i> 원 그리기</td><td>캔버스에 원 도형 추가</td><td>원하는 위치를 클릭하면 원이 배치됩니다.</td></tr>
        <tr><td><i class="bi bi-slash-lg"></i> 선 그리기</td><td>캔버스에 직선 도형 추가</td><td>시작점 클릭 → 끝점 클릭으로 선이 그려집니다.</td></tr>
        <tr><td><i class="bi bi-square"></i> 사각형 그리기</td><td>캔버스에 사각형 도형 추가</td><td>원하는 위치를 클릭하면 사각형이 배치됩니다.</td></tr>
        <tr><td><i class="bi bi-fonts"></i> 텍스트 추가</td><td>캔버스에 텍스트 라벨 추가</td><td>원하는 위치를 클릭하면 텍스트 입력창이 나타납니다.</td></tr>
        <tr><td><i class="bi bi-slash-circle"></i> 도형 모두 삭제</td><td>원·선·사각형·텍스트로 추가한 도형을 전부 제거</td><td>격자 살 편집 내용에는 영향을 주지 않습니다.</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>도형 그리기 기능은 창살 격자와 별도의 오버레이 레이어에 그려집니다. 치수 표기·부재 목록 등 제작 시방서 계산에는 포함되지 않는 <strong>주석·메모 용도</strong>입니다.</span>
</div>

<?php include __DIR__ . '/_foot.php'; ?>
