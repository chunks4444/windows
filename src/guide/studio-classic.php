<?php
require_once __DIR__ . '/../lib/studio_card_content.php';
$guide_current = 'studio-classic.php';
$guide_title   = '세살';
$guide_prev    = ['href' => 'getting-started.php', 'title' => '시작하기'];
$guide_next    = ['href' => 'studio-square.php', 'title' => '정자살'];
include __DIR__ . '/_head.php';
?>

<h1><span class="guide-h1-icon"><?= $guideEngineIcons['classic'] ?></span>세살</h1>
<p class="guide-lead"><?= studio_card_description('classic', "울거미 안에 세로살을 꽉 채우고, 가로살은 위·아래와 중간에 3~4가닥만 두른 창인 세살(細箭)을 재현한 엔진입니다.
    '세(細)'는 살이 가늘다는 뜻에서 온 이름으로, 촘촘한 세로살이 만드는 가늘고 곧은 결이 이 창의 얼굴입니다.
    띠살창이라고도 부르며, 조선시대 살창 가운데 가장 널리 쓰인 형식입니다.") ?></p>

<h2>화면 구성</h2>

<!-- UI 스크린샷 -->
<div class="guide-screenshot">
    <img src="/src/img/guide/studio-classic.png" alt="세살 스튜디오 화면 구성 — 왼쪽 메뉴 탭과 펼친 문 설정 패널, 중앙 캔버스, 캔버스 위 도구 막대" loading="lazy">
</div>

<p>
    스튜디오는 <strong>왼쪽 메뉴 탭 · 탭 패널 · 캔버스</strong>로 이루어져 있습니다.
    왼쪽 메뉴에서 탭을 누르면 그 옆에 해당 패널이 열리고, 같은 탭을 한 번 더 누르거나 패널 오른쪽 가장자리의
    <span class="guide-ui"><i class="bi bi-chevron-left"></i></span> 버튼을 누르면 패널이 접혀 캔버스가 넓어집니다.
    값을 바꾸면 캔버스에 바로 반영됩니다.
</p>

<table class="guide-table">
    <thead><tr><th>탭</th><th>하는 일</th></tr></thead>
    <tbody>
        <tr><td><i class="bi bi-grid-3x3-gap"></i> 컬렉션</td><td>이 엔진으로 만든 컬렉션 도면을 검색해 열기</td></tr>
        <tr><td><i class="bi bi-file-earmark"></i> 도면</td><td>도면 이름·저장·새 도면·공유·버전, 내 도면 목록</td></tr>
        <tr><td><i class="bi bi-door-closed"></i> 문설정</td><td>문 종류·짝수, 문틀 치수, 창살 설정</td></tr>
        <tr><td><i class="bi bi-flower1"></i> 문양</td><td>문양 라이브러리·SVG 업로드</td></tr>
        <tr><td><i class="bi bi-palette"></i> 마감</td><td>수종·부자재, 부위별 마감 색</td></tr>
        <tr><td><i class="bi bi-stars"></i> 렌더링</td><td>공간 사진 올리기·AI 렌더링·결과 보관</td></tr>
        <tr><td><i class="bi bi-receipt"></i> 견적</td><td>예상 가격·납기 확인, 견적요청</td></tr>
        <tr><td><i class="bi bi-rulers"></i> 시방서</td><td>제작 치수·먹줄·홈폭·부재 목록</td></tr>
        <tr><td><i class="bi bi-box-arrow-down"></i> 내보내기</td><td>PDF · PNG · DXF 파일 저장</td></tr>
    </tbody>
</table>

<h2>문설정 탭 — 설계 파라미터</h2>

<h3>문 종류 · 짝수</h3>
<table class="guide-table">
    <thead><tr><th>항목</th><th>옵션</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td>문 종류</td><td>여닫이 / 미서기</td><td>여닫이: 경첩 구조, 미서기: 슬라이딩 레일 구조</td></tr>
        <tr><td>짝수</td><td>여닫이 1~2짝 · 미서기 1~4·6·8짝</td><td>짝이 늘수록 전체 폭을 균등 분할</td></tr>
    </tbody>
</table>

<h3>문틀 치수</h3>
<table class="guide-table">
    <thead><tr><th>항목</th><th>범위</th><th>단위</th></tr></thead>
    <tbody>
        <tr><td>문틀 가로</td><td>400 ~ 3,000</td><td>mm</td></tr>
        <tr><td>문틀 세로</td><td>400 ~ 3,000</td><td>mm</td></tr>
    </tbody>
</table>
<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>여기서 입력하는 값은 벽 개구부(문틀 외곽) 치수입니다. 실제 문짝 크기는 문틀 두께·틈새를 자동으로 뺀 값으로 계산되며, 미서기는 문짝이 겹치는 폭만큼 추가로 보정됩니다. 계산된 문짝·문틀 치수는 캔버스 위 툴바 오른쪽 끝에 늘 표시되고, 자세한 값은 <strong>시방서</strong> 탭에서 볼 수 있습니다.</span>
</div>

<h3>창살 설정</h3>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td>가로 칸수</td><td>수평 방향 분할 수 (살 개수 = 칸수 - 1)</td></tr>
        <tr><td>좌우 울거미 두께</td><td>좌·우 외곽 프레임 두께 (mm)</td></tr>
        <tr><td>상하 울거미 두께</td><td>상·하 외곽 프레임 두께 (mm)</td></tr>
        <tr><td>살 두께</td><td>격자 살 단면 두께 (mm)</td></tr>
        <tr><td>가로살 (상단/중단/하단)</td><td>세 구역의 가로살 개수를 따로 지정. 예) 3/5/3</td></tr>
        <tr><td>세로 비율</td><td>상·중·하 구역의 세로 높이 비율</td></tr>
        <tr><td>풍판 사용</td><td>체크 시 상단 풍판 구역 추가. 풍판 높이 별도 설정</td></tr>
        <tr><td>치수 표기</td><td>체크 시 캔버스에 각 부재의 실측 치수를 함께 표시</td></tr>
        <tr><td>문틀 표시</td><td>체크 시 캔버스에 문틀(벽 개구부) 윤곽선을 함께 표시</td></tr>
    </tbody>
</table>

<h2>캔버스 — 도면 미리보기 & 조작</h2>
<p>
    캔버스 위 왼쪽에는 선택·이동·선 편집·도형·배치 버튼과 문짝·문틀 치수가, 오른쪽에는 확대·축소와 화면 맞춤 버튼이 있습니다.
    7개 엔진에 공통으로 제공되는 기능으로, 전체 목록과 사용 방법은
    <a href="/guide/canvas-toolbar">캔버스 툴바</a> 페이지를 참조하세요.
</p>

<h2>도면 탭 — 저장 · 공유 · 버전</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>기능</th></tr></thead>
    <tbody>
        <tr><td>도면 이름</td><td>도면 이름 입력. 저장하면 이 이름으로 클라우드에 보관됩니다.</td></tr>
        <tr><td><span class="guide-ui">저장</span></td><td>현재 도면을 저장. 같은 이름이면 새 버전으로 쌓입니다.</td></tr>
        <tr><td><span class="guide-ui">새 도면</span></td><td>현재 도면을 비우고 새로 시작</td></tr>
        <tr><td><span class="guide-ui">공유</span></td><td>저장한 도면의 공유 링크 만들기·끄기</td></tr>
        <tr><td>현재 도면 버전</td><td>저장된 버전 목록에서 이전 버전 열기</td></tr>
        <tr><td>내 도면</td><td>저장한 도면 썸네일. 누르면 그 도면으로 전환, <i class="bi bi-pencil"></i>로 이름 변경, <i class="bi bi-trash3"></i>로 삭제</td></tr>
    </tbody>
</table>
<p>자세한 사용법은 <a href="/guide/drawing">도면 저장 &amp; 불러오기</a>를 참고하세요.</p>

<h2>마감 탭 — 수종 · 색</h2>

<div class="guide-screenshot">
    <img src="/src/img/guide/finish.png" alt="마감 탭을 연 화면 — 수종·부자재, 색칠할 부위(문틀·울거미·살·면), 기본 마감(마감 없음·들기름·오일마감), 수성스테인(AURO 560)·유성스테인(AURO 930) 색 견본" loading="lazy">
</div>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td>수종</td><td>문에 쓸 목재 종류</td></tr>
        <tr><td>부자재</td><td>경첩·손잡이 등 부자재 구성. 필요 없으면 <strong>부자재 없음</strong></td></tr>
        <tr><td>색칠할 부위</td><td><strong>문틀 · 울거미 · 살 · 면</strong> 중 색을 바꿀 부위를 먼저 고릅니다.</td></tr>
        <tr><td>기본</td><td><strong>마감 없음</strong>(원목 그대로)과 들기름·오일마감 같은 천연 오일 마감. 오일은 나무결 그대로 한 가지 색으로 칠해집니다.</td></tr>
        <tr><td>수성스테인 · 유성스테인</td><td>제품별 색 견본(수성스테인 AURO 560, 유성스테인 AURO 930). 색을 누르면 그 색이 속한 마감이 함께 선택됩니다. 색에 마우스를 올리면 제품명·색 이름이 보입니다.</td></tr>
    </tbody>
</table>
<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span><strong>면</strong>을 고르고 색을 누른 뒤 도면의 칸을 클릭하면 그 칸만 색이 칠해집니다. 오른쪽 클릭은 지우기입니다. 마감 탭을 벗어나면 칠하기 모드는 자동으로 꺼집니다.</span>
</div>
<p>전체 색 견본과 마감 종류는 <a href="/guide/finish">마감 &amp; 컬러 샘플</a>에서 볼 수 있습니다.</p>

<h2>렌더링 탭 — 공간 사진 & AI 렌더링</h2>
<p>
    현장 사진을 올려 도면과 합성한 뒤 AI로 렌더링합니다.
    자세한 내용은 <a href="/guide/render">AI 렌더링 사용법</a> 페이지를 참조하세요.
</p>

<h2>견적 탭</h2>
<p>
    목재비·제작비·부자재·마감 등으로 나눈 <strong>예상 가격</strong>과 <strong>최소 납기</strong>를 보여줍니다.
    배송비·시공비는 빠져 있으며, 선 편집 등 직접 손댄 내용은 견적요청 후 담당자가 확인해 최종 견적을 정합니다.
    아래 <span class="guide-ui">견적요청</span> 버튼으로 바로 제작을 문의할 수 있습니다 — <a href="/guide/order">주문 안내</a> 참고.
</p>

<h2>시방서 탭 — 제작 치수</h2>
<p>파라미터를 입력하면 자동으로 계산되는 실측 치수입니다. (회원 등급에 따라 보이지 않을 수 있습니다.)</p>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td>문틀 가로/세로</td><td>벽 개구부 치수 (입력값 그대로)</td></tr>
        <tr><td>외경 가로/세로</td><td>문틀 두께·틈새를 제외하고 자동 계산된 문짝(울거미 포함) 전체 치수</td></tr>
        <tr><td>내경 가로/세로</td><td>울거미 제외 내부 유효 치수</td></tr>
        <tr><td>가로 칸수 / 세로 칸수</td><td>격자 분할 수</td></tr>
        <tr><td>가로·세로 먹줄</td><td>살 중심 사이 간격</td></tr>
        <tr><td>반턱 너비 · 홈폭</td><td>살 교차부 반턱과 울거미 홈 가공 치수</td></tr>
        <tr><td>부재 목록</td><td>울거미·살·문틀 부재의 폭×두께×길이와 개수</td></tr>
    </tbody>
</table>

<h2>내보내기 탭</h2>
<table class="guide-table">
    <thead><tr><th>버튼</th><th>결과물</th></tr></thead>
    <tbody>
        <tr><td><span class="guide-ui">PDF</span></td><td>인쇄·공방 전달용 도면 (A4 가로, 치수 포함)</td></tr>
        <tr><td><span class="guide-ui">PNG</span></td><td>이미지 공유용 도면</td></tr>
        <tr><td><span class="guide-ui">DXF</span></td><td>CAD 편집용 실측 mm 도면</td></tr>
    </tbody>
</table>
<p>자세한 내용은 <a href="/guide/export">PDF / PNG / DXF 내보내기</a>를 참고하세요.</p>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>내보내기 파일에는 공간 사진이 들어가지 않습니다. AI 렌더링 결과물은 렌더링 탭에서 따로 내려받습니다.</span>
</div>

<?php include __DIR__ . '/_foot.php'; ?>
