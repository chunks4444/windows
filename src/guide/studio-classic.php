<?php
$guide_current = 'studio-classic.php';
$guide_title   = '세살';
$guide_prev    = ['href' => 'getting-started.php', 'title' => '시작하기'];
$guide_next    = ['href' => 'studio-square.php', 'title' => '정자살'];
include __DIR__ . '/_head.php';
?>

<h1><span class="guide-h1-icon"><?= $guideEngineIcons['classic'] ?></span>세살</h1>
<p class="guide-lead">
    울거미 안에 세로살을 꽉 채우고, 가로살은 위·아래와 중간에 3~4가닥만 두른 창인 세살(細箭)을 재현한 엔진입니다.
    띠살창이라고도 부르며, 조선시대 살창 가운데 가장 널리 쓰인 형식입니다.
    스튜디오는 <strong>왼쪽 설계 사이드바 · 캔버스 · 오른쪽 배경·내보내기 사이드바</strong>
    세 패널로 구성되어 있으며, 파라미터를 바꾸는 즉시 캔버스에 반영됩니다.
</p>

<h2>화면 구성</h2>

<!-- UI 스크린샷 -->
<div class="guide-screenshot">
    <img src="/src/img/guide/studio-classic.png" alt="세살 스튜디오 화면 구성 — 왼쪽 설계 사이드바, 중앙 캔버스, 오른쪽 예상가격·마감·렌더링 사이드바" loading="lazy">
</div>

<h2>① 왼쪽 사이드바 — 설계 파라미터</h2>

<h3>문 설정</h3>
<table class="guide-table">
    <thead><tr><th>항목</th><th>옵션</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td>문 종류</td><td>여닫이 / 미서기</td><td>여닫이: 경첩 구조, 미서기: 슬라이딩 레일 구조</td></tr>
        <tr><td>문 짝수</td><td>1 ~ 4짝</td><td>짝이 늘수록 전체 폭을 균등 분할</td></tr>
    </tbody>
</table>

<h3>문 치수</h3>
<table class="guide-table">
    <thead><tr><th>항목</th><th>범위</th><th>단위</th></tr></thead>
    <tbody>
        <tr><td>문틀 가로</td><td>400 ~ 3,000</td><td>mm</td></tr>
        <tr><td>문틀 세로</td><td>400 ~ 3,000</td><td>mm</td></tr>
    </tbody>
</table>
<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>여기서 입력하는 값은 벽 개구부(문틀 외곽) 치수입니다. 실제 문짝 크기는 문틀 두께·틈새(문틀 두께 설정값, 관리자 설정)를 자동으로 뺀 값으로 계산되며, 미서기는 문짝이 겹치는 폭만큼 추가로 보정됩니다. 계산된 실측 치수는 아래 <strong>제작 시방서</strong>에서 확인할 수 있습니다.</span>
</div>

<h3>창살 설정</h3>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td>가로 칸수</td><td>수평 방향 분할 수 (살 개수 = 칸수 - 1)</td></tr>
        <tr><td>좌우 울거미 두께</td><td>좌·우 외곽 프레임 두께 (mm)</td></tr>
        <tr><td>상하 울거미 두께</td><td>상·하 외곽 프레임 두께 (mm)</td></tr>
        <tr><td>창살 두께</td><td>격자 살 단면 두께 (mm)</td></tr>
        <tr><td>가로살 배열 (상/중/하)</td><td>세 구역의 수평 칸수를 독립 지정. 예) 3/5/3</td></tr>
        <tr><td>세로 비율</td><td>상·중·하 구역의 세로 높이 비율</td></tr>
        <tr><td>풍판 사용</td><td>체크 시 상단 풍판 구역 추가. 풍판 높이 별도 설정</td></tr>
        <tr><td>치수 표기</td><td>체크 시 캔버스에 각 부재의 실측 치수를 함께 표시</td></tr>
        <tr><td>문틀 표시</td><td>체크 시 캔버스에 문틀(벽 개구부) 윤곽선을 함께 표시</td></tr>
    </tbody>
</table>

<h3>제작 시방서</h3>
<p>파라미터 입력 후 자동 계산되는 실측 치수 정보입니다.</p>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td>문틀 가로/세로</td><td>벽 개구부 치수 (입력값 그대로)</td></tr>
        <tr><td>외경 가로/세로</td><td>문틀 두께·틈새를 제외하고 자동 계산된 문짝(울거미 포함) 전체 치수</td></tr>
        <tr><td>내경 가로/세로</td><td>울거미 제외 내부 유효 치수</td></tr>
        <tr><td>가로 칸수 / 세로 칸수</td><td>격자 분할 수</td></tr>
        <tr><td>눈(Eye) 크기</td><td>격자 한 칸의 내부 크기</td></tr>
        <tr><td>살 목록</td><td>세로살·가로살·울거미살 길이 × 수량</td></tr>
    </tbody>
</table>

<h2>② 캔버스 — 도면 미리보기 & 조작</h2>

<h3>상단 도면 관리 바</h3>
<table class="guide-table">
    <thead><tr><th>버튼</th><th>기능</th></tr></thead>
    <tbody>
        <tr><td><span class="guide-ui">도면 이름 입력…</span></td><td>도면 이름 직접 입력. 저장 시 이 이름으로 클라우드에 보관됩니다.</td></tr>
        <tr><td><span class="guide-ui">새 도면</span></td><td>현재 도면을 초기화하고 새 도면 시작</td></tr>
        <tr><td><span class="guide-ui">도면</span></td><td>저장된 도면 목록을 열어 다른 도면으로 전환</td></tr>
        <tr><td><span class="guide-ui">버전 ▾</span></td><td>현재 도면의 저장 버전 히스토리. 클릭해 드롭다운에서 이전 버전 복원</td></tr>
    </tbody>
</table>

<h3>캔버스 툴바 (하단)</h3>
<p>
    캔버스 하단에는 확대·축소·이동 같은 화면 조작 버튼과 선 삭제/추가 편집 모드, 문양 배치, 도형 그리기 버튼이 모여 있습니다.
    6개 엔진에 공통으로 제공되는 기능으로, 전체 목록과 사용 방법은
    <a href="/guide/canvas-toolbar" style="color:var(--accent);">캔버스 툴바</a> 페이지를 참조하세요.
</p>

<h2>③ 오른쪽 사이드바 — 마감 · 배경 · 저장</h2>

<h3>저장 / 주문</h3>
<table class="guide-table">
    <thead><tr><th>버튼</th><th>기능</th></tr></thead>
    <tbody>
        <tr><td><span class="guide-ui">💾 저장</span></td><td>현재 도면을 클라우드에 저장. 동일 이름이면 버전이 추가됩니다.</td></tr>
        <tr><td><span class="guide-ui">주문</span></td><td>설계한 도면 기반으로 제작 주문 문의 페이지로 이동</td></tr>
    </tbody>
</table>

<h3>마감 설정</h3>
<table class="guide-table">
    <thead><tr><th>항목</th><th>옵션</th></tr></thead>
    <tbody>
        <tr><td>목재</td><td>홍송 / 소나무 / 참나무</td></tr>
        <tr><td>내부 마감</td><td>창호지 / 유리 / 아크릴</td></tr>
        <tr><td>울거미 색</td><td>컬러 팔레트 선택</td></tr>
        <tr><td>살 색</td><td>컬러 팔레트 선택</td></tr>
        <tr><td>면 색</td><td>HEX 컬러 피커로 직접 지정. <span class="guide-ui">칠하기</span>로 특정 칸에 색 채우기 가능</td></tr>
    </tbody>
</table>

<h3>배경 사진 & AI 렌더링</h3>
<p>
    현장 사진을 업로드해 도면과 합성한 뒤 AI로 렌더링합니다.
    자세한 내용은 <a href="/guide/render" style="color:var(--accent);">AI 렌더링 사용법</a> 페이지를 참조하세요.
</p>

<h3>내보내기</h3>
<table class="guide-table">
    <thead><tr><th>버튼</th><th>결과물</th></tr></thead>
    <tbody>
        <tr><td><span class="guide-ui">PNG</span></td><td>현재 캔버스 뷰 그대로 투명 배경 PNG 다운로드</td></tr>
        <tr><td><span class="guide-ui">PDF</span></td><td>인쇄 최적화 PDF. 치수 주석 포함</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>PNG 내보내기는 배경 사진을 포함하지 않습니다. 도면(격자살 레이어)만 투명 배경으로 출력됩니다. AI 렌더링 결과물은 렌더링 팝업에서 별도 다운로드합니다.</span>
</div>

<?php include __DIR__ . '/_foot.php'; ?>
