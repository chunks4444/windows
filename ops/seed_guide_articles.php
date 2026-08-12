<?php
// 1회성 시드 스크립트: 기존 src/guide/*.php에 하드코딩돼 있던 본문 HTML을
// guide_articles 테이블로 옮긴다. CLI에서 한 번 실행하고 나면 다시 실행할 필요 없음
// (slug UNIQUE 키로 upsert하므로 재실행해도 안전하게 덮어쓴다).
// 실행: php ops/seed_guide_articles.php
require_once __DIR__ . '/../src/lib/db.php';

$svgIcons = [
    'classic' => '<svg width="14" height="14" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
            <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="23"/>
            <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="23"/>
            <rect fill="currentColor" x="148" y="148" width="46" height="384" rx="23"/>
            <rect fill="currentColor" x="294" y="148" width="46" height="384" rx="23"/>
            <rect fill="currentColor" x="486" y="148" width="46" height="384" rx="23"/>
        </svg>',
    'square' => '<svg width="14" height="14" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
            <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="23"/>
            <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="23"/>
            <rect fill="currentColor" x="204" y="148" width="46" height="384" rx="23"/>
            <rect fill="currentColor" x="430" y="148" width="46" height="384" rx="23"/>
        </svg>',
    'cross' => '<svg width="14" height="14" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
            <g transform="rotate(45 340 340)">
                <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="23"/>
                <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="23"/>
                <rect fill="currentColor" x="204" y="148" width="46" height="384" rx="23"/>
                <rect fill="currentColor" x="430" y="148" width="46" height="384" rx="23"/>
            </g>
        </svg>',
    'diamond' => '<svg width="14" height="14" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
            <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/>
            <rect fill="currentColor" x="148" y="317" width="384" height="46" rx="23"/>
            <g transform="rotate(45 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
            <g transform="rotate(135 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
        </svg>',
    'triangle' => '<svg width="14" height="14" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
            <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/>
            <g transform="rotate(60 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
            <g transform="rotate(120 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
        </svg>',
    'hexagon' => '<svg width="14" height="14" viewBox="0 0 680 680" fill="none" xmlns="http://www.w3.org/2000/svg">
            <polyline points="210,265 340,190 470,265" stroke="currentColor" stroke-width="32" stroke-linejoin="round" stroke-linecap="round"/>
            <line x1="210" y1="265" x2="210" y2="415" stroke="currentColor" stroke-width="32" stroke-linecap="round"/>
            <line x1="470" y1="265" x2="470" y2="415" stroke="currentColor" stroke-width="32" stroke-linecap="round"/>
            <line x1="210" y1="415" x2="340" y2="490" stroke="currentColor" stroke-width="32" stroke-linecap="round"/>
            <line x1="470" y1="415" x2="340" y2="490" stroke="currentColor" stroke-width="32" stroke-linecap="round"/>
        </svg>',
];

$articles = [];

$articles['intro'] = ['title' => '평목 스튜디오란?', 'body' => <<<'HTML'
<h1>평목 스튜디오란?</h1>
<p class="guide-lead">
    평목(平木)은 전통 한국 창호 도면을 브라우저에서 실시간으로 설계하고 내보낼 수 있는 온라인 스튜디오입니다.
    별도 프로그램 설치 없이 6가지 격자 패턴 엔진과 AI 렌더링 기능을 사용할 수 있습니다.
</p>

<h2>주요 기능</h2>
<p>평목이 제공하는 핵심 기능을 소개합니다.</p>

<table class="guide-table">
    <thead>
        <tr><th>기능</th><th>설명</th></tr>
    </thead>
    <tbody>
        <tr><td><strong>6가지 스튜디오</strong></td><td>세살, 정자살, 빗살, 격자 빗살, 세모 솟을살, 육모 솟을살 격자 패턴 엔진</td></tr>
        <tr><td><strong>실시간 렌더링</strong></td><td>파라미터 변경 즉시 캔버스에 반영</td></tr>
        <tr><td><strong>도면 저장</strong></td><td>클라우드 DB에 도면 & 버전 저장, 어디서든 이어서 작업</td></tr>
        <tr><td><strong>PDF / PNG 내보내기</strong></td><td>인쇄·납품용 고해상도 파일 출력</td></tr>
        <tr><td><strong>AI 렌더링</strong></td><td>배경 사진 위에 도면을 합성해 AI로 공간 시각화</td></tr>
        <tr><td><strong>컬렉션</strong></td><td>공개 라이브러리 패턴 열람 & 내 보드 저장</td></tr>
        <tr><td><strong>AI 챗봇 설계</strong></td><td>상단 프롬프트 입력창에 원하는 사양을 문장으로 입력하면 파라미터 자동 반영</td></tr>
    </tbody>
</table>

<h2>6가지 격자 패턴 엔진</h2>
<p>스튜디오는 창호 살의 배열 방식에 따라 6가지 엔진으로 구분됩니다.</p>

<ul>
    <li><strong>세살</strong> — 전통 사분턱 구조. 수직·수평 살이 교차하는 정통 창호 패턴</li>
    <li><strong>정자살</strong> — 단순 정방형 격자. 수직·수평 살로 구성된 기본 패턴</li>
    <li><strong>빗살</strong> — 45° 회전 격자. 사선 살이 교차하는 대각선 패턴</li>
    <li><strong>격자 빗살</strong> — 마름모 복합 패턴. 수직·수평·사선이 결합된 고급 패턴</li>
    <li><strong>세모 솟을살</strong> — 삼각 격자. 0°·60°·120° 세 방향 살로 구성</li>
    <li><strong>육모 솟을살</strong> — 육각 격자. 삼각에서 한 방향을 제거한 육모살 패턴</li>
</ul>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>어떤 패턴을 선택해야 할지 모르겠다면 <strong>컬렉션</strong> 페이지에서 실제 도면 예시를 먼저 살펴보세요.</span>
</div>

<h2>시스템 요구 사항</h2>
<p>평목은 웹 브라우저에서 동작하는 서비스입니다. 별도 설치 없이 아래 환경에서 사용 가능합니다.</p>
<ul>
    <li>Chrome 90 이상 (권장)</li>
    <li>Safari 15 이상</li>
    <li>Edge 90 이상</li>
    <li>Firefox 88 이상</li>
</ul>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>AI 렌더링 기능은 서버 처리 시간이 필요합니다. 안정적인 네트워크 환경에서 사용을 권장합니다.</span>
</div>
HTML
];

$articles['getting-started'] = ['title' => '시작하기', 'body' => <<<'HTML'
<h1>시작하기</h1>
<p class="guide-lead">
    평목 계정을 만들고 첫 번째 도면을 설계하기까지의 과정을 단계별로 안내합니다.
</p>

<h2>1단계 — 회원가입 & 로그인</h2>
<ol class="guide-steps">
    <li>상단 내비게이션의 <span class="guide-ui">로그인</span> 버튼을 클릭합니다.</li>
    <li>로그인 모달에서 <strong>이메일</strong>과 <strong>비밀번호</strong>를 입력하거나 소셜 로그인(Google / Kakao)을 선택합니다.</li>
    <li>처음 방문이라면 <strong>회원가입</strong> 탭으로 전환해 이메일·비밀번호를 입력 후 가입합니다.</li>
    <li>로그인 후 상단에 이메일 아이콘이 표시되면 준비 완료입니다.</li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>소셜 로그인을 이용하면 비밀번호 없이 빠르게 시작할 수 있습니다.</span>
</div>

<h2>2단계 — 스튜디오 선택</h2>
<ol class="guide-steps">
    <li>상단 내비게이션에서 <span class="guide-ui">스튜디오</span>를 클릭합니다.</li>
    <li>드롭다운 메뉴에서 원하는 격자 패턴 엔진을 선택합니다.<br>
        처음이라면 <strong>세살</strong>을 권장합니다.</li>
    <li>스튜디오 화면이 열리면 왼쪽 사이드바에서 파라미터를 조정합니다.</li>
    <li>가운데 캔버스에 실시간으로 도면이 그려집니다.</li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>스튜디오 상단 내비게이션의 프롬프트 입력창에 <em>"완자살 미서기문 3짝, 가로 1800 세로 1200으로 바꿔줘"</em>처럼 원하는 사양을 문장으로 입력하면 AI가 해당 파라미터를 자동으로 반영해줍니다. 다른 엔진이 더 적합하면 자동으로 그 스튜디오로 이동합니다.</span>
</div>

<h2>3단계 — 첫 도면 저장</h2>
<ol class="guide-steps">
    <li>캔버스 상단 바의 <span class="guide-ui">도면 이름</span> 입력란에 이름을 입력합니다.</li>
    <li><span class="guide-ui">저장</span> 버튼을 클릭합니다.</li>
    <li>저장된 도면은 <span class="guide-ui">도면 관리</span> 페이지에서 언제든 불러올 수 있습니다.</li>
</ol>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>도면은 클라우드에 저장되므로, 다른 기기에서 로그인해도 동일한 도면에 접근할 수 있습니다.</span>
</div>

<h2>인터페이스 구성</h2>
<p>스튜디오 화면은 크게 네 영역으로 구성됩니다.</p>

<table class="guide-table">
    <thead><tr><th>영역</th><th>위치</th><th>역할</th></tr></thead>
    <tbody>
        <tr><td><strong>왼쪽 사이드바</strong></td><td>왼쪽</td><td>문 종류, 치수, 격자 살 설정 등 설계 파라미터 입력</td></tr>
        <tr><td><strong>캔버스 상단 바</strong></td><td>캔버스 위</td><td>도면 이름, 새 도면·도면 목록·버전 관리, 저장·공유 버튼</td></tr>
        <tr><td><strong>캔버스</strong></td><td>중앙</td><td>도면 실시간 미리보기, 휠/드래그로 확대·이동, 선 삭제·추가 편집</td></tr>
        <tr><td><strong>오른쪽 사이드바</strong></td><td>오른쪽</td><td>마감·색상, 배경 사진·AI 렌더링, PNG/PDF 내보내기, 견적요청</td></tr>
    </tbody>
</table>
HTML
];

$articles['canvas-toolbar'] = ['title' => '캔버스 툴바', 'body' => <<<'HTML'
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
HTML
];

$articles['drawing'] = ['title' => '도면 저장 & 불러오기', 'body' => <<<'HTML'
<h1>도면 저장 & 불러오기</h1>
<p class="guide-lead">
    작업한 도면은 클라우드에 저장되어 언제든 다시 불러올 수 있습니다.
    버전 히스토리도 자동으로 관리되어 이전 작업 상태로 되돌릴 수 있습니다.
</p>

<h2>도면 저장하기</h2>
<ol class="guide-steps">
    <li>캔버스 상단 바 <span class="guide-ui">도면 이름</span> 입력란에 도면 이름을 입력합니다.</li>
    <li><span class="guide-ui">저장</span> 버튼을 클릭합니다.</li>
    <li>처음 저장 시 새 도면이 생성되고, 이후 동일 이름으로 저장하면 버전이 쌓입니다.</li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>도면 이름은 프로젝트명·현장명으로 관리하면 도면관리에서 찾기 쉽습니다. 예: <em>청담동_한옥_정면창</em></span>
</div>

<h2>도면 불러오기</h2>
<ol class="guide-steps">
    <li>캔버스 상단 바의 <span class="guide-ui">도면</span> 버튼을 클릭합니다.</li>
    <li>저장된 도면 목록에서 불러올 도면을 클릭합니다.</li>
    <li>도면의 파라미터가 캔버스에 복원됩니다.</li>
</ol>

<h2>버전 관리</h2>
<p>도면을 저장할 때마다 버전이 생성됩니다. 버전 히스토리를 통해 이전 상태로 되돌릴 수 있습니다.</p>
<ol class="guide-steps">
    <li>캔버스 상단 바의 <span class="guide-ui">버전 ▾</span> 드롭다운에서 되돌리고 싶은 버전을 클릭합니다.</li>
    <li>해당 버전의 파라미터가 캔버스에 반영됩니다.</li>
    <li>확인 후 현재 버전으로 다시 저장하면 최신 버전이 됩니다.</li>
</ol>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>버전은 도면당 최대 <strong>20개</strong>까지 보관되며, 초과하면 가장 오래된 버전부터 자동으로 삭제됩니다.</span>
</div>

<h2>도면 공유하기</h2>
<p>저장한 도면은 링크 하나로 다른 사람에게 공유할 수 있습니다. 받는 사람은 로그인 없이도 도면을 확인할 수 있습니다.</p>
<ol class="guide-steps">
    <li>캔버스 상단 바의 <span class="guide-ui">공유</span> 버튼을 클릭합니다. (저장 전에는 비활성화되어 있으며, "먼저 저장해주세요" 안내가 표시됩니다.)</li>
    <li>공유 패널이 열리면서 자동으로 공유가 켜지고, 공유 링크와 <span class="guide-ui">복사</span> 버튼, 카카오톡·페이스북·X 공유 버튼이 표시됩니다.</li>
    <li>도면관리(마이페이지)의 도면 카드에서도 우측 상단 공유 아이콘으로 동일하게 공유할 수 있습니다.</li>
    <li>공유를 중단하려면 패널의 <span class="guide-ui">공유 끄기</span> 버튼을 클릭합니다.</li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>공유 링크를 열면 해당 도면의 파라미터가 캔버스에 그대로 불러와집니다. 로그인한 상태라면 자유롭게 값을 수정한 뒤 <strong>새 이름으로 저장</strong>하면 원본은 그대로 두고 내 계정에 새 도면(사본)이 생성됩니다. 별도의 "복사" 버튼은 없습니다.</span>
</div>

<h2>도면관리</h2>
<p>상단 내비게이션 <span class="guide-ui">스튜디오</span> → <span class="guide-ui">도면관리</span>에서 모든 도면을 한눈에 관리할 수 있습니다.</p>
<ul>
    <li>엔진별 탭으로 도면을 분류해 볼 수 있습니다.</li>
    <li>썸네일 이미지로 도면을 빠르게 식별할 수 있습니다.</li>
    <li>도면 이름 변경, 삭제가 가능합니다.</li>
    <li>도면 카드의 <span class="guide-ui">복사</span> 버튼으로 내 도면을 그대로 복제해 새 버전을 만들 수 있습니다.</li>
    <li>작업 시간이 기록됩니다.</li>
</ul>

<h2>도면 이름 변경</h2>
<ol class="guide-steps">
    <li>캔버스 상단 바의 도면 이름 입력란에서 이름을 수정합니다.</li>
    <li><span class="guide-ui">이름 변경</span> 버튼을 클릭합니다.</li>
    <li>도면관리에도 즉시 반영됩니다.</li>
</ol>
HTML
];

$articles['export'] = ['title' => 'PDF / PNG 내보내기', 'body' => <<<'HTML'
<h1>PDF / PNG 내보내기</h1>
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

<h2>내보내기 설정 팁</h2>
<table class="guide-table">
    <thead><tr><th>용도</th><th>권장 형식</th><th>설정</th></tr></thead>
    <tbody>
        <tr><td>납품·인쇄용</td><td>PDF</td><td>A3 이상, 가로 방향</td></tr>
        <tr><td>디지털 공유·검토</td><td>PNG</td><td>기본 해상도</td></tr>
        <tr><td>AI 렌더링 합성</td><td>PNG</td><td>배경 투명 유지</td></tr>
        <tr><td>프레젠테이션</td><td>PDF</td><td>A4, 세로 방향</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>PDF 내보내기는 브라우저의 jsPDF 라이브러리를 사용합니다. 대용량 도면은 생성에 수 초가 걸릴 수 있습니다.</span>
</div>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>배경 이미지(AI 렌더링용 사진)는 PDF 내보내기에 포함되지 않습니다. 도면 살(격자) 레이어만 출력됩니다.</span>
</div>
HTML
];

$articles['render'] = ['title' => 'AI 렌더링 사용법', 'body' => <<<'HTML'
<h1>AI 렌더링 — 배경 사진과 도면 합성</h1>
<p class="guide-lead">
    현장 사진(배경) 위에 창호 도면을 겹쳐 AI가 실제 시공 모습으로 합성하는 기능입니다.
    <strong>배경 이미지 + 격자 도면 = AI 렌더링 결과물</strong>의 흐름으로 동작하며,
    세살 스튜디오의 <strong>오른쪽 사이드바</strong>에서 모든 과정이 이루어집니다.
</p>

<!-- 합성 원리 흐름도 -->
<div class="guide-flow">
    <div class="guide-flow-step">
        <span class="step-icon">🖼️</span>
        <div class="step-title">배경 사진 업로드</div>
        <div class="step-desc">현장·공간 사진을 캔버스 배경으로 설정</div>
    </div>
    <div class="guide-flow-arrow">＋</div>
    <div class="guide-flow-step">
        <span class="step-icon">🪟</span>
        <div class="step-title">도면 설계</div>
        <div class="step-desc">왼쪽 사이드바로 창호 격자 파라미터 조정</div>
    </div>
    <div class="guide-flow-arrow">→</div>
    <div class="guide-flow-step" style="border-color:var(--accent);background:var(--accent-tint);">
        <span class="step-icon">✨</span>
        <div class="step-title">AI 합성</div>
        <div class="step-desc">배경+도면을 AI가 자연스럽게 합성</div>
    </div>
    <div class="guide-flow-arrow">→</div>
    <div class="guide-flow-step">
        <span class="step-icon">💾</span>
        <div class="step-title">결과 저장</div>
        <div class="step-desc">PNG 다운로드 또는 히스토리 보관</div>
    </div>
</div>

<h2>스튜디오 화면 구성</h2>
<p>세살 스튜디오는 <strong>세 개의 패널</strong>로 구성됩니다. AI 렌더링은 <strong>오른쪽 사이드바</strong>에서 진행됩니다.</p>

<!-- UI 스크린샷 -->
<div class="guide-screenshot">
    <img src="/src/img/guide/render.png" alt="AI 렌더링 화면 구성 — 배경 사진 위에 도면이 합성된 캔버스와 오른쪽 배경 업로드·재질/조명 선택·렌더링 패널" loading="lazy">
</div>

<div class="guide-callout-grid">
    <div class="guid-label-callout"><div class="num">①</div><div><strong>왼쪽 사이드바</strong> — 창호 격자 파라미터 설정. 변경 즉시 캔버스에 반영됩니다.</div></div>
    <div class="guid-label-callout"><div class="num">②</div><div><strong>캔버스</strong> — 배경 사진 위에 도면이 실시간으로 겹쳐 보입니다.</div></div>
    <div class="guid-label-callout"><div class="num">③</div><div><strong>배경 사진 패널</strong> — 사진 업로드, 썸네일 선택, AI 프롬프트 입력, 렌더링 실행.</div></div>
    <div class="guid-label-callout"><div class="num">④</div><div><strong>렌더링 히스토리</strong> — 최근 생성한 결과물을 최대 9개 보관. 클릭해 다시 볼 수 있습니다.</div></div>
</div>

<h2>단계별 사용 방법</h2>

<h3>① 배경 사진 업로드</h3>
<ol class="guide-steps">
    <li>
        <strong>오른쪽 사이드바</strong>가 닫혀 있다면 캔버스 오른쪽 끝의
        <span class="guide-ui">&rsaquo;</span> 탭을 클릭해 패널을 엽니다.
    </li>
    <li>
        <span class="guide-ui">↑ 사진 추가</span> 버튼을 클릭해 이미지 파일을 선택합니다.<br>
        여러 장을 한꺼번에 업로드할 수 있습니다.
    </li>
    <li>
        업로드된 사진이 아래 <strong>썸네일 목록</strong>에 나타납니다.
        원하는 썸네일을 클릭하면 <strong>즉시 캔버스 배경으로 적용</strong>됩니다.
        선택된 썸네일은 초록 테두리로 표시됩니다.
    </li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>배경 사진을 선택하면 도면(격자살)이 사진 위에 겹쳐진 상태로 캔버스에 표시됩니다. 이 상태가 AI에 전송되는 합성 이미지입니다.</span>
</div>

<h3>② 도면 파라미터 조정</h3>
<p>
    왼쪽 사이드바에서 창호의 치수와 격자 살 설정을 조정합니다.
    배경 사진 위에 도면이 실시간으로 합성되어 미리보기되므로,
    실제 공간에 어울리는 비율과 패턴을 즉석에서 확인할 수 있습니다.
</p>
<ul>
    <li><strong>가로폭 / 세로높이</strong> — 실제 설치 공간에 맞춰 조정</li>
    <li><strong>가로살 배열 (상/중/하)</strong> — 세 구역의 칸수 비율을 독립 조정 가능</li>
    <li><strong>울거미 · 살 색상</strong> — 오른쪽 사이드바 마감 섹션에서 색상 지정</li>
</ul>

<h3>③ AI 렌더링 프롬프트 작성</h3>
<p>
    오른쪽 사이드바 하단의 텍스트 입력란에 원하는 분위기를 한 줄로 입력합니다.
    AI가 이 텍스트를 참고해 배경+도면의 질감·조명·분위기를 합성합니다.
</p>

<table class="guide-table">
    <thead><tr><th>좋은 프롬프트 예시</th><th>기대 효과</th></tr></thead>
    <tbody>
        <tr><td>오래된 참나무 결, 옻칠 마감, 흰 한지</td><td>목재 질감과 한지 배경 강조</td></tr>
        <tr><td>한옥 카페 인테리어, 따뜻한 낮 채광</td><td>밝고 따뜻한 공간감</td></tr>
        <tr><td>야간, 은은한 간접 조명, 분위기 있는 레스토랑</td><td>저녁 감성 분위기</td></tr>
        <tr><td>전통 사찰, 고즈넉한 자연 배경, 자연광</td><td>정적이고 고요한 전통 느낌</td></tr>
        <tr><td>모던 미니멀, 화이트 톤, 대형 창, 도시 전경</td><td>깔끔하고 현대적인 분위기</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span><strong>공간 유형 + 소재·질감 + 조명 조건</strong> 세 요소를 포함하면 더 정확한 결과를 얻습니다.<br>
    예) <em>"한옥 카페"</em>(공간) + <em>"참나무 결"</em>(소재) + <em>"낮 자연광"</em>(조명)</span>
</div>

<h3>④ 렌더링 실행</h3>
<ol class="guide-steps">
    <li>
        <span class="guide-ui" style="background:var(--accent);color:var(--bg);border:none;">✨ Rendering</span> 버튼을 클릭합니다.
    </li>
    <li>
        캔버스 위에 <strong>"AI 렌더링 중…"</strong> 로딩 오버레이가 표시됩니다.
        처리 시간은 보통 <strong>30~90초</strong>입니다.
    </li>
    <li>
        완료되면 결과 팝업이 자동으로 열립니다.
        <span class="guide-ui">다운로드</span>로 PNG를 저장하거나 팝업을 닫습니다.
    </li>
    <li>
        생성된 결과물은 <strong>렌더링 히스토리</strong>에 자동 저장됩니다.
        썸네일을 클릭하면 언제든 다시 볼 수 있습니다.
    </li>
</ol>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>렌더링 실행 전 반드시 배경 사진이 선택된 상태인지 확인하세요. 배경 사진 없이 실행하면 안내 메시지가 표시되고 렌더링이 시작되지 않습니다.</span>
</div>

<h2>렌더링 히스토리</h2>
<p>
    최근 렌더링 결과는 오른쪽 사이드바 하단 <strong>렌더링 히스토리</strong>에 최대 <strong>9개</strong>까지 자동 보관됩니다.
    썸네일을 클릭하면 결과 팝업에서 다시 확인하거나 다운로드할 수 있습니다.
    히스토리의 <i class="bi bi-x"></i> 버튼으로 항목을 삭제할 수도 있습니다.
</p>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <div>
        <p style="margin:0 0 4px;">렌더링 결과물은 서버에 저장되어 같은 계정이면 다른 기기·브라우저에서도 동일하게 보입니다.</p>
        <p style="margin:0;">단, 계정당 보관 가능한 렌더링은 최대 <strong>300장</strong>입니다. 한도를 초과하면 새 렌더링이 거부되고 안내 메시지가 표시되므로, 오래된 렌더링을 오른쪽 사이드바 히스토리에서 <i class="bi bi-x"></i>로 정리하거나 필요한 결과물은 <span class="guide-ui">다운로드</span>로 백업해두세요.</p>
    </div>
</div>

<h2>배경 사진 관리</h2>
<table class="guide-table">
    <thead><tr><th>동작</th><th>방법</th></tr></thead>
    <tbody>
        <tr><td>배경 사진 추가</td><td>오른쪽 사이드바 <span class="guide-ui">↑ 사진 추가</span> 버튼 또는 드래그 &amp; 드롭</td></tr>
        <tr><td>배경 사진 전환</td><td>썸네일 클릭 → 즉시 해당 사진으로 캔버스 배경 변경</td></tr>
        <tr><td>배경 사진 제거</td><td>썸네일 옆 <span class="guide-ui"><i class="bi bi-x-lg"></i></span> 버튼 클릭</td></tr>
        <tr><td>배경 없이 도면만 보기</td><td>활성화된 썸네일을 다시 클릭해 비활성화</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>배경 사진은 서버에 저장되므로 같은 도면을 다시 불러오면 이전에 업로드한 사진도 함께 복원됩니다.</span>
</div>
HTML
];

$articles['collection'] = ['title' => '컬렉션 & 내 보드', 'body' => <<<'HTML'
<h1>컬렉션 & 내 보드</h1>
<p class="guide-lead">
    컬렉션은 평목 팀이 엄선한 공개 창호 도면 라이브러리입니다.
    마음에 드는 패턴을 스튜디오로 바로 불러오거나, 좋아요·내 보드에 저장해 영감으로 활용하세요.
</p>

<h2>컬렉션 둘러보기</h2>
<ol class="guide-steps">
    <li>상단 내비게이션의 <span class="guide-ui">컬렉션</span>을 클릭합니다.</li>
    <li>격자 형태로 공개 도면 패턴이 표시됩니다.</li>
    <li>카드를 클릭하면 도면 상세 페이지로 이동합니다.</li>
    <li>상세 페이지에서 <span class="guide-ui">스튜디오에서 열기</span>를 클릭하면 해당 파라미터로 스튜디오가 열립니다.</li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>컬렉션 도면은 로그인 없이도 열람할 수 있지만, 스튜디오에서 열기·좋아요·보드 저장은 로그인이 필요합니다.</span>
</div>

<h2>패턴 이름 표기</h2>
<p>
    최근에 등록된 컬렉션 패턴은 <strong>JEO-SE-001</strong>과 같은 코드 형식으로 이름이 표시됩니다.
    앞부분은 계열(문양 대분류), 가운데는 세부 수식어, 마지막 숫자는 일련번호를 뜻합니다.
    코드가 아직 없는 예전 패턴은 기존처럼 한글 이름으로 표시됩니다.
</p>

<h2>컬렉션 필터</h2>
<p>
    컬렉션 상단에는 <strong>우리살</strong>·<strong>새살</strong>·<strong>일본살</strong> 드롭다운 3개와 검색창, 좋아요 토글이 있습니다.
</p>
<ul>
    <li><strong>우리살</strong> — 정자살·아자살·완자살·숫대살·세살·빗살·격자빗살·귀갑살·범살·솟을살 등 전통 계열. 드롭다운을 열면 하위에 각 계열이 나타나 세부 필터링도 가능합니다.</li>
    <li><strong>새살</strong> — 전통 계열에 속하지 않는 새로 디자인된 패턴</li>
    <li><strong>일본살</strong> — 일본식 격자 패턴. 드롭다운을 열면 하위에 <strong>쇼지</strong>·<strong>쿠미꼬</strong> 세부 옵션이 나타납니다.</li>
    <li><strong>패턴 검색</strong> — 이름·코드로 검색</li>
    <li><strong>좋아요</strong> — 내가 좋아요한 패턴만 표시 (로그인 필요)</li>
</ul>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>필터는 <strong>동시에 조합되지 않습니다.</strong> 하나를 적용하면 나머지는 자동으로 초기화되고, 방금 적용한 조건 하나만으로 검색됩니다. 예를 들어 검색어를 입력한 뒤 우리살을 선택하면 검색어는 지워지고 우리살 필터만 적용됩니다. 별다른 필터 없이 컬렉션에 처음 들어오면 기본으로 <strong>우리살</strong> 패턴만 표시됩니다.</span>
</div>

<h2>좋아요</h2>
<p>
    마음에 드는 패턴은 카드 또는 상세 페이지의 <i class="bi bi-heart"></i> 아이콘을 클릭해 좋아요를 남길 수 있습니다.
    상단 필터의 <span class="guide-ui">좋아요</span> 토글을 켜면 내가 좋아요한 패턴만 모아볼 수 있습니다.
</p>

<h2>스튜디오에서 열기</h2>
<p>
    컬렉션 도면을 스튜디오에서 열면 해당 파라미터가 캔버스에 복원됩니다.
    이 상태에서 자유롭게 수정한 뒤 <strong>새 이름으로 저장</strong>해 내 도면으로 만들 수 있습니다.
</p>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>컬렉션에서 불러온 도면은 원본을 덮어쓰지 않습니다. 저장 시 반드시 새 이름을 입력해 내 도면으로 저장하세요.</span>
</div>

<h2>패턴 공유하기</h2>
<p>마음에 드는 컬렉션 패턴을 다른 사람에게 링크로 공유할 수 있습니다.</p>
<ol class="guide-steps">
    <li>컬렉션 상세 페이지의 <span class="guide-ui"><i class="bi bi-share"></i></span> 공유 아이콘을 클릭합니다.</li>
    <li>공유 링크와 <span class="guide-ui">복사</span> 버튼, 카카오톡·페이스북·X·스레드 공유 버튼이 있는 팝업이 열립니다.</li>
    <li>이 링크는 컬렉션 패턴 페이지로 연결되며, 로그인 없이 누구나 볼 수 있습니다.</li>
</ol>

<h2>내 보드</h2>
<p>마음에 드는 컬렉션 도면을 내 보드에 저장해 나만의 레퍼런스 라이브러리를 만들 수 있습니다.</p>
<ol class="guide-steps">
    <li>컬렉션 <strong>카드</strong>의 <span class="guide-ui"><i class="bi bi-collection"></i></span> 보드 아이콘을 클릭합니다. (상세 페이지에는 이 버튼이 없으므로, 목록 화면에서 저장해야 합니다.)</li>
    <li>저장할 보드를 선택하거나 <span class="guide-ui">+ 새 보드</span>로 보드를 생성합니다.</li>
    <li>내 보드는 상단 내비게이션 사용자 메뉴의 <strong>내 보드</strong> 섹션과, 도면 관리 페이지의 <strong>Boards</strong> 탭에서 확인할 수 있습니다.</li>
</ol>
HTML
];

$articles['account'] = ['title' => '프로필 & 회사 정보', 'body' => <<<'HTML'
<h1>프로필 & 회사 정보</h1>
<p class="guide-lead">
    내 계정 정보와 회사 정보를 관리합니다. 프로필 이름·연락처와 함께 회사명·사업자번호 등을
    입력하면 PDF 내보내기에 자동으로 반영됩니다.
</p>

<h2>프로필 수정</h2>
<ol class="guide-steps">
    <li>상단 내비게이션 이메일 아이콘 → <span class="guide-ui"><i class="bi bi-person me-1"></i>프로필</span>을 클릭합니다.</li>
    <li>로그인 이메일과 회원 등급 배지는 상단에 읽기 전용으로 표시됩니다.</li>
    <li><strong>이름</strong>, <strong>연락처</strong>, <strong>주소</strong>(우편번호·도로명주소·상세주소)를 입력합니다.</li>
    <li><span class="guide-ui">저장</span> 버튼을 클릭합니다.</li>
</ol>

<h2>비밀번호 변경</h2>
<ol class="guide-steps">
    <li>프로필 페이지의 <strong>비밀번호 변경</strong> 섹션으로 이동합니다.</li>
    <li><strong>현재 비밀번호</strong>와 <strong>새 비밀번호</strong>를 입력합니다.</li>
    <li><span class="guide-ui">변경</span> 버튼을 클릭합니다.</li>
</ol>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>소셜 로그인(Google·Kakao)으로 가입한 계정은 비밀번호 변경 메뉴가 표시되지 않습니다.</span>
</div>

<h2>회사 정보 입력</h2>
<ol class="guide-steps">
    <li>상단 내비게이션 이메일 아이콘 → <span class="guide-ui"><i class="bi bi-building me-1"></i>회사 정보</span>를 클릭합니다.</li>
    <li>아래 항목을 입력합니다.</li>
</ol>

<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td>회사명</td><td>사업체 또는 공방 이름</td></tr>
        <tr><td>사업자 번호</td><td>000-00-00000 형식</td></tr>
        <tr><td>업태 / 업종</td><td>사업자등록증 기준 업태·업종</td></tr>
        <tr><td>대표자명</td><td>법인 대표 또는 개인사업자명</td></tr>
        <tr><td>전화번호</td><td>대표 연락처</td></tr>
        <tr><td>주소</td><td>우편번호 검색 후 도로명주소·상세주소 입력</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>업무용 이메일·홈페이지 URL 입력란은 따로 없습니다. 이메일은 로그인 계정의 이메일이 그대로 사용됩니다.</span>
</div>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>회사 정보를 입력해두면 도면 PDF 내보내기 시 로고·회사명이 자동으로 포함됩니다.</span>
</div>

<h2>회원 등급별 가격 정보 열람 권한</h2>
<p>
    스튜디오에서 보이는 예상가격·원가 관련 정보는 회원 등급에 따라 다르게 표시됩니다.
    처음 가입한 일반 회원은 기본적으로 예상가격·최소납기·배송비 등의 항목이 보이지 않으며,
    공방 관리자가 계정별로 열람 권한을 개별 승인해야 표시됩니다.
</p>

<table class="guide-table">
    <thead><tr><th>항목</th><th>기본 노출 대상</th></tr></thead>
    <tbody>
        <tr><td>예상가격</td><td>승인된 회원만 — 승인 전에는 스튜디오에 가격 자체가 표시되지 않습니다.</td></tr>
        <tr><td>원가 상세(목재·인건비·부자재·마감·경비 소계)</td><td>공방 관리자, 또는 별도로 승인받은 회원</td></tr>
        <tr><td>최소 납기 · 배송비 안내</td><td>승인된 회원만</td></tr>
        <tr><td>부재 목록 · 제작 시방서</td><td>승인된 회원만</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>열람 권한이 필요하다면 공방 담당자에게 문의해 계정 승인을 요청해주세요. 견적요청·주문 자체는 열람 권한과 무관하게 이용할 수 있습니다.</span>
</div>

<h2>계정 탈퇴</h2>
<p>
    계정 탈퇴는 프로필 페이지에서 직접 처리할 수 없습니다.
    탈퇴를 원하시면 <a href="/company/#contact">공방 문의</a>로 요청해주세요. 담당자가 확인 후 처리해드립니다.
    탈퇴 시 저장된 도면과 컬렉션 보드 데이터가 모두 삭제되며, 복구할 수 없습니다.
</p>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>계정 탈퇴 전 중요한 도면은 반드시 PDF 또는 PNG로 내보내어 백업하세요.</span>
</div>
HTML
];

$articles['order'] = ['title' => '주문 안내', 'body' => <<<'HTML'
<h1>주문 안내</h1>
<p class="guide-lead">
    스튜디오에서 설계한 도면을 바탕으로 제작을 의뢰합니다.
    견적요청을 접수하면 담당자가 확인 후 연락드립니다.
</p>

<h2>주문 전 준비사항</h2>
<p>견적요청 전 아래 두 가지가 완료되어 있어야 합니다.</p>

<table class="guide-table">
    <thead><tr><th>항목</th><th>확인 방법</th></tr></thead>
    <tbody>
        <tr>
            <td><strong>로그인</strong></td>
            <td>상단 내비게이션에서 로그인 여부를 확인합니다.</td>
        </tr>
        <tr>
            <td><strong>프로필 이름·연락처</strong></td>
            <td>내비게이션 이메일 아이콘 → <span class="guide-ui">프로필</span>에서 이름과 연락처를 입력합니다.</td>
        </tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>이름과 연락처가 등록되어 있지 않으면 견적요청 버튼이 동작하지 않습니다. <a href="/guide/account">프로필 설정 가이드</a>를 참고하세요.</span>
</div>

<h2>주문 흐름</h2>

<div class="guide-flow">
    <div class="guide-flow-step">
        <span class="step-icon"><i class="bi bi-pencil-square" style="color:var(--accent)"></i></span>
        <div class="step-title">도면 설계</div>
        <div class="step-desc">스튜디오에서 창호 도면을 완성합니다.</div>
    </div>
    <div class="guide-flow-arrow"><i class="bi bi-chevron-right"></i></div>
    <div class="guide-flow-step">
        <span class="step-icon"><i class="bi bi-cart-check" style="color:var(--accent)"></i></span>
        <div class="step-title">견적요청 접수</div>
        <div class="step-desc">배송지·납기일을 입력하고 제출합니다.</div>
    </div>
    <div class="guide-flow-arrow"><i class="bi bi-chevron-right"></i></div>
    <div class="guide-flow-step">
        <span class="step-icon"><i class="bi bi-telephone" style="color:var(--accent)"></i></span>
        <div class="step-title">담당자 연락</div>
        <div class="step-desc">담당자가 확인 후 견적서를 보내드립니다.</div>
    </div>
    <div class="guide-flow-arrow"><i class="bi bi-chevron-right"></i></div>
    <div class="guide-flow-step">
        <span class="step-icon"><i class="bi bi-tools" style="color:var(--accent)"></i></span>
        <div class="step-title">제작 & 배송</div>
        <div class="step-desc">결제 확인 후 제작에 착수합니다.</div>
    </div>
</div>

<h2>견적요청하기</h2>
<ol class="guide-steps">
    <li>스튜디오 상단 툴바의 <span class="guide-ui"><i class="bi bi-cart-check me-1"></i>견적요청</span> 버튼을 클릭합니다.</li>
    <li>요청 정보를 확인하고 입력합니다.
        <table class="guide-table" style="margin-top:12px">
            <thead><tr><th>항목</th><th>설명</th></tr></thead>
            <tbody>
                <tr><td>주문자 정보</td><td>프로필에 등록된 이름·연락처·회사명이 자동으로 채워집니다.</td></tr>
                <tr><td>도면 정보</td><td>현재 열려 있는 도면 제목·버전과 썸네일이 자동으로 첨부됩니다.</td></tr>
                <tr><td>납기 희망일</td><td>제작 완료를 원하는 날짜를 선택합니다.</td></tr>
                <tr><td>배송지</td><td>프로필 주소가 기본으로 채워지며, <span class="guide-ui">주소 검색</span>으로 변경할 수 있습니다.</td></tr>
                <tr><td>요청 메모</td><td>마감 처리, 색상 등 추가 요청사항을 자유롭게 입력합니다. (선택)</td></tr>
            </tbody>
        </table>
    </li>
    <li><span class="guide-ui">견적요청 접수</span> 버튼을 클릭합니다.</li>
    <li>접수가 완료되면 확인 메시지가 표시되고, 담당자에게 알림 메일이 발송됩니다.</li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>견적요청 전에 도면을 반드시 저장해두세요. 저장된 도면으로 요청하면 담당자가 정확한 사양을 확인할 수 있습니다.</span>
</div>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <div>
        <div>※ 배송비·시공비 제외</div>
        <div>※ 도면에 보이는 금액은 예상금액입니다. 사용자 편집 내용을 검토한 후 최종 견적이 확정됩니다.</div>
        <div>※ 예상가격·원가 상세는 회원 등급에 따라 표시 여부가 다릅니다. 자세한 내용은 <a href="/guide/account">프로필 & 회사 정보 가이드</a>의 "회원 등급별 가격 정보 열람 권한"을 참고하세요.</div>
    </div>
</div>

<h2>도면 잠금</h2>
<p>
    견적요청이 접수된 도면은 진행 상태에 따라 <strong>편집·저장·삭제가 제한</strong>됩니다.
    이는 요청 접수 이후 사양이 변경되어 발생할 수 있는 혼선을 방지하기 위한 조치입니다.
</p>
<p>
    도면 목록에서 잠긴 도면에는 <strong><i class="bi bi-lock-fill"></i></strong> 배지가 현재 주문 상태 이름(예: <em>제작중</em>)으로 표시됩니다.
    잠금이 걸리는 상태는 <strong>견적검토 · 승인 · 견적확정 · 입금완료 · 제작중 · 제작완료 · 발송</strong>입니다.
</p>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>담당자가 <strong>수정요청</strong>으로 상태를 바꾸면 잠금이 풀려 도면을 다시 편집·저장할 수 있습니다. 배송완료 또는 주문취소 후에도 잠금은 해제됩니다.</span>
</div>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>잠긴 상태에서 사양 변경이 필요한 경우 담당자에게 연락해주세요. 담당자가 수정요청 상태로 전환하면 도면을 수정한 뒤 재요청할 수 있습니다.</span>
</div>

<h2>담당자 응대</h2>
<p>
    접수 후 영업일 기준 1~2일 이내에 담당자가 연락드립니다.
    등록된 이메일 또는 연락처로 견적서와 함께 세부 사항을 안내해드립니다.
</p>

<h2>주문 상태 확인</h2>
<p>
    견적요청 접수 시 <strong>"주문번호 #N"</strong>이 발급되며, 이후 진행 상황은 상단 내비게이션
    <span class="guide-ui">마이페이지</span> → <span class="guide-ui">도면관리</span> →
    <span class="guide-ui">주문내역</span> 탭에서 확인할 수 있습니다.
</p>
<p>각 주문은 아래 순서로 상태가 변경됩니다.</p>
<table class="guide-table">
    <thead><tr><th>상태</th><th>의미</th></tr></thead>
    <tbody>
        <tr><td>견적검토</td><td>접수된 요청을 담당자가 검토 중입니다.</td></tr>
        <tr><td>수정요청</td><td>담당자가 도면 사양에 대해 수정을 요청한 상태입니다.</td></tr>
        <tr><td>승인</td><td>사양이 확정되어 견적 산출을 진행합니다.</td></tr>
        <tr><td>견적확정</td><td>최종 견적이 확정되었습니다.</td></tr>
        <tr><td>입금완료</td><td>결제(입금)가 확인되었습니다.</td></tr>
        <tr><td>제작중</td><td>공방에서 제작이 진행 중입니다.</td></tr>
        <tr><td>제작완료</td><td>제작이 완료되어 발송 준비 중입니다.</td></tr>
        <tr><td>발송</td><td>제품이 발송되었습니다.</td></tr>
        <tr><td>배송완료</td><td>배송이 완료되었습니다.</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span><strong>취소</strong>는 이 흐름과 별개인 종결 상태입니다. 고객이 직접 취소하거나 담당자가 취소 처리하면 해당 상태로 바뀌며, 이후 단계로 진행되지 않습니다.</span>
</div>

<p>주문내역 목록의 각 행을 클릭하면 상세 모달에서 다음 정보를 확인할 수 있습니다.</p>
<ul>
    <li>현재 상태 배지, 요청사항 메모</li>
    <li>수정요청 상태인 경우 담당자가 남긴 수정요청 사유</li>
    <li>발송·배송완료 상태인 경우 배송 정보(택배사·운송장번호)</li>
    <li>주문일 · 납기희망일 · 최근 처리일</li>
    <li>예상견적 및 확정 가격</li>
    <li><span class="guide-ui">도면 보기</span> 버튼으로 해당 도면 바로 이동</li>
</ul>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>상태가 <strong>견적검토</strong> 또는 <strong>수정요청</strong>일 때만 주문내역에서 직접 <span class="guide-ui">주문취소</span>가 가능합니다. 이후 단계는 담당자에게 연락해주세요.</span>
</div>

<h2>자주 묻는 질문</h2>

<h3>견적요청 버튼이 보이지 않아요.</h3>
<p>
    로그인하지 않은 상태에서는 버튼이 표시되지 않습니다.
    로그인 후 다시 시도해주세요.
</p>

<h3>배송지를 프로필과 다르게 입력하고 싶어요.</h3>
<p>
    견적요청 모달 안에서 <span class="guide-ui">주소 검색</span> 버튼으로 배송지를 별도로 입력할 수 있습니다.
    해당 주문에만 적용되며 프로필 주소는 변경되지 않습니다.
</p>

<h3>여러 도면을 한 번에 주문하고 싶어요.</h3>
<p>
    현재는 도면별로 개별 접수하셔야 합니다.
    요청 메모에 함께 주문하는 도면을 명시해주시면 담당자가 묶어서 처리해드립니다.
</p>
HTML
];

$articles['delivery'] = ['title' => '배송 안내', 'body' => <<<'HTML'
<h1>배송 안내</h1>
<p class="guide-lead">
    제작이 완료된 창호는 제품 크기와 수량에 따라 택배 또는 화물로 배송됩니다.
    배송비는 주문자 부담이며, 제품 하자로 인한 반품 배송비는 평목이 부담합니다.
</p>

<h2>배송 방법</h2>

<table class="guide-table">
    <thead><tr><th>구분</th><th>대상</th><th>비고</th></tr></thead>
    <tbody>
        <tr>
            <td><strong>택배 배송</strong></td>
            <td>소형 제품, 소량 주문</td>
            <td>일반 택배사를 통해 배송됩니다.</td>
        </tr>
        <tr>
            <td><strong>화물 배송</strong></td>
            <td>대형 제품, 다량 주문</td>
            <td>화물 운송을 통해 배송됩니다. 담당자가 별도 안내드립니다.</td>
        </tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>배송 방법은 제품 사양 확정 후 담당자가 안내드립니다. 견적 상담 시 문의해주세요.</span>
</div>

<h2>배송비</h2>
<p>배송비는 <strong>주문자 부담</strong>이며, 배송 방법과 배송지에 따라 달라질 수 있습니다. 정확한 배송비는 견적 확정 시 안내드립니다.</p>

<h2>반품 및 교환</h2>
<p>제품 수령 후 하자가 발견된 경우 담당자에게 즉시 연락해주세요.</p>

<table class="guide-table">
    <thead><tr><th>구분</th><th>배송비 부담</th></tr></thead>
    <tbody>
        <tr>
            <td>제품 하자·오류로 인한 반품</td>
            <td><strong>평목 부담</strong></td>
        </tr>
        <tr>
            <td>단순 변심·주문 오류로 인한 반품</td>
            <td><strong>주문자 부담</strong></td>
        </tr>
    </tbody>
</table>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>반품·교환은 제품 수령일로부터 7일 이내에 요청해주세요. 사용 또는 가공된 제품은 반품이 불가합니다.</span>
</div>
HTML
];

$articles['faq'] = ['title' => '자주 묻는 질문 (FAQ)', 'body' => <<<'HTML'
<p class="guide-lead">
    평목 스튜디오 사용 중 자주 나오는 질문들을 모았습니다.
    원하는 답변을 찾지 못하셨다면 <a href="/company/#contact">공방 문의</a>를 이용해 주세요.
</p>
HTML
];

$articles['studio-classic'] = ['title' => '세살', 'body' => <<<HTML
<h1><span class="guide-h1-icon">{$svgIcons['classic']}</span>세살</h1>
<p class="guide-lead">
    울거미 안에 세로살을 꽉 채우고, 가로살은 위·아래와 중간에 3~4가닥만 두른 창인 세살(細箭)을 재현한 엔진입니다.
    '세(細)'는 살이 가늘다는 뜻에서 온 이름으로, 촘촘한 세로살이 만드는 가늘고 곧은 결이 이 창의 얼굴입니다.
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
HTML
];

$articles['studio-square'] = ['title' => '정자살', 'body' => <<<HTML
<h1><span class="guide-h1-icon">{$svgIcons['square']}</span>정자살</h1>
<p class="guide-lead">
    울거미 안에 세로살과 가로살을 모두 꽉 채워 격자를 이룬 정자살(井字箭)을 재현한 엔진입니다.
    '정(井)'은 살이 짜이는 모양이 우물 정 자를 닮은 데서 온 이름으로, 빈틈없이 고른 격자가 이 창의 얼굴입니다.
    만살(滿箭)이라고도 부르며, 세살 다음으로 많이 쓰인 형식입니다.
    세살과 사이드바 구조(문 설정 · 문 치수 · 창살 설정 · 마감 · 배경 · 내보내기)를 대부분 공유하지만,
    상/중/하 3단 구획 대신 <strong>단일 비율의 균등 격자</strong>를 사용하고, 격자를 무작위로 재구성하는
    <strong>랜덤 패턴</strong> 기능이 추가로 있습니다.
</p>

<h2>세살과의 차이점</h2>
<table class="guide-table">
    <thead><tr><th></th><th>정자살</th><th>세살</th></tr></thead>
    <tbody>
        <tr><td>구획 방식</td><td>전체 격자에 동일한 셀 비율 적용</td><td>상/중/하 3단으로 나눠 각 구역 칸수·비율을 독립 지정</td></tr>
        <tr><td>세로 비율 범위</td><td>1.0 ~ 3.0</td><td>1.0 ~ 5.0</td></tr>
        <tr><td>랜덤 패턴</td><td>있음 (몬드리안풍 무작위 분할)</td><td>없음</td></tr>
        <tr><td>세로 자동 맞춤</td><td>있음</td><td>없음</td></tr>
    </tbody>
</table>

<h2>주요 파라미터</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td><strong>문 종류 / 문 짝수</strong></td><td>여닫이·미서기, 1~4짝. 세살과 동일하게 문틀 두께·틈새가 자동 반영되어 실제 문짝 치수가 계산됩니다.</td></tr>
        <tr><td><strong>문틀 가로 / 문틀 세로</strong></td><td>벽 개구부 치수 (400~3,000mm). 문틀 두께를 제외한 값이 문짝 외경으로 자동 계산됩니다.</td></tr>
        <tr><td><strong>가로 칸수</strong></td><td>수평 방향 격자 분할 수</td></tr>
        <tr><td><strong>세로 비율</strong></td><td>1.0~3.0. 셀의 가로:세로 비율을 결정하며 이 값에 따라 세로 칸수가 자동 산출됩니다.</td></tr>
        <tr><td><strong>세로 자동 맞춤</strong></td><td>체크 시 마지막 격자 행이 하단 울거미에 딱 맞도록 문틀 세로 값을 자동으로 재조정합니다.</td></tr>
        <tr><td><strong>좌우 / 상하 울거미 두께</strong></td><td>외곽 프레임 두께 (mm)</td></tr>
        <tr><td><strong>창살 두께</strong></td><td>격자 살 단면 두께 (mm)</td></tr>
        <tr><td><strong>풍판 사용</strong></td><td>체크 시 상단 풍판 구역 추가, 풍판 높이 별도 설정</td></tr>
        <tr><td><strong>치수 표기 / 문틀 표시</strong></td><td>캔버스에 실측 치수와 문틀 윤곽선을 표시할지 선택</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>가로 칸수와 세로 비율을 조합하면 완전한 정방형(正方形) 격자부터 세로로 긴 격자까지 자유롭게 조정할 수 있습니다.</span>
</div>

<h2>랜덤 패턴</h2>
<p>
    정자살 엔진에만 있는 기능으로, 균질 격자를 몬드리안풍으로 무작위 재분할합니다.
</p>
<table class="guide-table">
    <thead><tr><th>버튼</th><th>기능</th></tr></thead>
    <tbody>
        <tr><td><span class="guide-ui">랜덤 생성</span></td><td>현재 격자를 기준으로 셀을 무작위로 병합·분할해 비정형 패턴을 생성</td></tr>
        <tr><td><span class="guide-ui">초기화</span></td><td>랜덤 패턴을 걷어내고 원래의 균등 격자로 복원</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>랜덤 생성은 클릭할 때마다 다른 결과를 만듭니다. 마음에 드는 결과가 나오면 바로 저장하세요. 초기화 전에는 이전 결과가 남아있지 않습니다.</span>
</div>

<h2>제작 시방서 & 부재 목록</h2>
<p>세살과 동일한 항목(문틀 가로/세로, 외경·내경 가로/세로, 가로 칸수, 가로·세로 먹줄, 눈 크기, 반턱 너비, 울거미홈폭)이 자동 계산되어 표시됩니다. 부재 목록은 가로살·세로살, 울거미, (풍판 사용 시) 풍판, 문틀 순으로 구성됩니다.</p>

<h2>마감 · 색상</h2>
<p>
    목재·마감·부자재 선택, 울거미·살 컬러는 세살과 동일합니다.
    <strong>면 컬러 칠하기</strong> 기능도 정자살에서 그대로 사용할 수 있습니다 — 격자 칸을 클릭해 개별 면에 색을 채울 수 있습니다.
    (빗살·격자 빗살·세모 솟을살·육모 솟을살 엔진은 현재 면 칠하기 기능이 비활성화되어 있습니다.)
</p>

<h2>활용 예시</h2>
<ul>
    <li>현대 한옥의 미서기 창문</li>
    <li>카페·상업 공간의 파티션 창호</li>
    <li>랜덤 패턴을 활용한 비정형 디자인 창호</li>
</ul>
HTML
];

$articles['studio-cross'] = ['title' => '빗살', 'body' => <<<HTML
<h1><span class="guide-h1-icon">{$svgIcons['cross']}</span>빗살</h1>
<p class="guide-lead">
    울거미 안에 살대를 45도 기울여 대각선으로 짠 빗살을 재현한 엔진입니다.
    '빗'은 살을 비스듬히 기울여 짠 데서 온 이름으로, 엇갈린 살이 만드는 마름모꼴 격자가 이 창의 얼굴입니다.
    가로 칸수만 지정하면 셀이 항상 정사각형이 되도록 세로 칸수가 자동으로 계산되며,
    각도를 조절하는 설정 항목은 따로 없습니다.
</p>

<h2>특징</h2>
<ul>
    <li>셀은 항상 정사각형으로 고정되며, 그 대각선을 따라 살이 배치되어 45° 사선 무늬가 만들어집니다.</li>
    <li><strong>세로 칸수는 사용자가 정할 수 없습니다.</strong> 가로 칸수·창살 두께로 정해진 정사각 셀 크기에 맞춰 세로 방향 칸 수가 자동 산출됩니다.</li>
    <li>클리핑(Clipping) 처리로 프레임 밖으로 살이 삐져나오지 않습니다.</li>
</ul>

<h2>주요 파라미터</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td><strong>문 종류 / 문 짝수</strong></td><td>여닫이·미서기, 1~4짝</td></tr>
        <tr><td><strong>문틀 가로 / 문틀 세로</strong></td><td>벽 개구부 치수 (400~3,000mm). 문틀 두께를 제외한 값이 문짝 외경으로 자동 계산됩니다.</td></tr>
        <tr><td><strong>가로 칸수</strong></td><td>2~30. 정사각 셀의 가로 방향 개수 — 이 값과 창살 두께로 셀 크기가 정해지고, 세로 칸수는 자동으로 맞춰집니다.</td></tr>
        <tr><td><strong>세로 자동 맞춤</strong></td><td>체크 시 마지막 행이 하단 울거미에 딱 맞도록 문틀 세로 값을 자동 재조정</td></tr>
        <tr><td><strong>좌우 / 상하 울거미 두께</strong></td><td>외곽 프레임 두께 (mm)</td></tr>
        <tr><td><strong>창살 두께</strong></td><td>각 살의 폭 (mm). 셀 크기(=정사각형 한 변)를 함께 결정합니다.</td></tr>
        <tr><td><strong>풍판 사용</strong></td><td>체크 시 상단 풍판 구역 추가</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>정자살·세살에 있는 "세로 비율"이나 "상/중/하 배열" 같은 구획 설정은 빗살에는 없습니다. 격자 밀도를 조절하려면 가로 칸수 또는 창살 두께를 바꾸세요.</span>
</div>

<h2>제작 시방서</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td>문틀 / 외경 / 내경 가로·세로</td><td>자동 계산된 실측 치수</td></tr>
        <tr><td>가로 먹줄 / 세로 먹줄</td><td>정사각 셀의 가로·세로 간격</td></tr>
        <tr><td>살 먹줄</td><td>대각선 교차점 간 거리</td></tr>
        <tr><td>반턱 너비</td><td>창살 두께와 동일하게 계산</td></tr>
        <tr><td>울거미홈폭</td><td>창살 두께 × (1+√2) — 45° 사선살이 울거미에 파고드는 홈의 폭</td></tr>
    </tbody>
</table>

<h2>부재 목록</h2>
<p>가로살·세로살 그룹 대신 <strong>사선살</strong> 그룹으로 표시됩니다. 두 대각 방향(↘·↗)별로 길이가 같은 살끼리 묶어 길이×개수로 정리됩니다. 그 아래에 울거미, (풍판 사용 시) 풍판, 문틀 부재가 이어집니다.</p>

<h2>마감 · 색상</h2>
<p>목재·마감·부자재, 울거미·살 컬러는 세살과 동일합니다. 다만 <strong>면 컬러 칠하기 기능은 현재 비활성화</strong>되어 있어 개별 칸에 색을 채우는 기능은 사용할 수 없습니다.</p>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>빗살과 정자살을 겹쳐 활용하면 더 복잡한 격자 빗살 패턴 효과를 낼 수 있습니다. 자세한 내용은 <a href="/guide/studio-diamond" style="color:var(--accent);">격자 빗살</a> 페이지를 참조하세요.</span>
</div>

<h2>활용 예시</h2>
<ul>
    <li>한옥 방등(防燈) 창살 패턴</li>
    <li>현대 인테리어 스크린 파티션</li>
    <li>전통 누각 난간 패턴</li>
</ul>
HTML
];

$articles['studio-diamond'] = ['title' => '격자 빗살', 'body' => <<<HTML
<h1><span class="guide-h1-icon">{$svgIcons['diamond']}</span>격자 빗살</h1>
<p class="guide-lead">
    가로세로 격자 위에 45도 빗살을 겹쳐 짠 격자빗살을 재현한 엔진입니다.
    정(井)자 짜임과 대각선 빗살 짜임이 한 면에서 만나 격자 한 칸이 다시 네 개의 작은 삼각으로 나뉩니다.
    빗살 엔진과 마찬가지로 셀은 항상 정사각형으로 고정되며, 그 위에 가로살·세로살과 사선살이 모두 그려집니다.
</p>

<h2>살 구성</h2>
<table class="guide-table">
    <thead><tr><th>살 방향</th><th>각도</th><th>역할</th></tr></thead>
    <tbody>
        <tr><td>수직살 · 수평살</td><td>0° / 90°</td><td>정자살과 동일한 직교 기본 골격</td></tr>
        <tr><td>사선살 A · B</td><td>45° / 135°</td><td>정사각 셀의 대각선을 따라 겹쳐지는 빗살</td></tr>
    </tbody>
</table>

<h2>주요 파라미터</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td><strong>문 종류 / 문 짝수</strong></td><td>여닫이·미서기, 1~4짝</td></tr>
        <tr><td><strong>문틀 가로 / 문틀 세로</strong></td><td>벽 개구부 치수 (400~3,000mm). 문틀 두께를 제외한 값이 문짝 외경으로 자동 계산됩니다.</td></tr>
        <tr><td><strong>가로 칸수</strong></td><td>2~30. 빗살과 마찬가지로 정사각 셀의 가로 개수를 정하며, 세로 칸수는 자동 산출됩니다.</td></tr>
        <tr><td><strong>세로 자동 맞춤</strong></td><td>체크 시 마지막 행이 하단 울거미에 딱 맞도록 문틀 세로 값을 자동 재조정</td></tr>
        <tr><td><strong>좌우 / 상하 울거미 두께</strong></td><td>외곽 프레임 두께 (mm)</td></tr>
        <tr><td><strong>창살 두께</strong></td><td>전체 살(직교·사선 공통)의 기본 두께 (mm)</td></tr>
        <tr><td><strong>풍판 사용</strong></td><td>체크 시 상단 풍판 구역 추가</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>정자살의 "세로 비율"이나 세살의 "상/중/하 배열" 같은 구획 설정은 격자 빗살에는 없습니다. 사선 밀도는 별도 항목이 아니라 가로 칸수·창살 두께에 종속됩니다.</span>
</div>

<h2>제작 시방서</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td>문틀 / 외경 / 내경 가로·세로</td><td>자동 계산된 실측 치수</td></tr>
        <tr><td>사선 간격</td><td>대각선 살 사이의 간격</td></tr>
        <tr><td>살 먹줄</td><td>직교살·사선살 교차점 간 거리</td></tr>
        <tr><td>울거미홈폭</td><td>좌우·상하 구분 없이 단일값으로 계산되는 홈 폭</td></tr>
    </tbody>
</table>

<h2>부재 목록</h2>
<p>울거미 → <strong>가로살·세로살</strong> → <strong>사선살</strong> → (풍판 사용 시) 풍판 → 문틀 순서로, 빗살보다 그룹이 하나 더 있습니다(직교살과 사선살이 모두 별도 목록으로 집계).</p>

<h2>마감 · 색상</h2>
<p>목재·마감·부자재, 울거미·살 컬러는 세살과 동일합니다. <strong>면 컬러 칠하기 기능은 현재 비활성화</strong>되어 있습니다.</p>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>가로 칸수를 지나치게 높이면 직교살·사선살이 촘촘히 겹쳐 실제 제작이 어려울 수 있습니다. PDF로 출력해 실측을 확인하세요.</span>
</div>

<h2>활용 예시</h2>
<ul>
    <li>궁궐·사찰의 아자살(亞字窓) 변형 패턴</li>
    <li>고급 한옥 펜션 창호</li>
    <li>전통 공예 전시관 파티션</li>
</ul>
HTML
];

$articles['studio-triangle'] = ['title' => '세모 솟을살', 'body' => <<<HTML
<h1><span class="guide-h1-icon">{$svgIcons['triangle']}</span>세모 솟을살</h1>
<p class="guide-lead">
    수직살과 좌우 빗살, 세 방향의 살대가 한 점에서 만나도록 짠 세모 솟을살을 재현한 엔진입니다.
    '솟을'은 살이 교차점에서 겹치며 위로 솟아오르는 데서 온 이름으로, 교차점마다 살이 도드라져 짜임에 입체감이 살아 있습니다.
    살들이 교차하며 정삼각형이 화면 가득 반복되어, 육모의 둥글고 넉넉한 인상과 달리 팽팽하고 긴장감 있는 느낌을 줍니다.
    모든 셀이 정삼각형이 되도록 세로 칸수가 자동으로 계산되며, 세로 칸수를 직접 지정할 수는 없습니다.
</p>

<h2>살 구성</h2>
<table class="guide-table">
    <thead><tr><th>살 방향</th><th>각도</th></tr></thead>
    <tbody>
        <tr><td>수직살</td><td>0° (수직)</td></tr>
        <tr><td>우사선살</td><td>60°</td></tr>
        <tr><td>좌사선살</td><td>120°</td></tr>
    </tbody>
</table>

<h2>주요 파라미터</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td><strong>문 종류 / 문 짝수</strong></td><td>여닫이·미서기, 1~4짝</td></tr>
        <tr><td><strong>문틀 가로 / 문틀 세로</strong></td><td>벽 개구부 치수 (400~3,000mm). 문틀 두께를 제외한 값이 문짝 외경으로 자동 계산됩니다.</td></tr>
        <tr><td><strong>가로 칸수</strong></td><td>2~30, <strong>짝수 단위</strong>로만 조절됩니다(기본값 4). 이 값과 창살 두께로 정삼각형 크기가 정해지고, 세로 칸수는 자동 산출됩니다.</td></tr>
        <tr><td><strong>패턴 세로 방향</strong></td><td>기본 켜짐. 삼각 격자 전체를 90° 회전합니다. 켜짐/꺼짐 여부에 따라 부재 목록의 방향별 부재 명칭이 바뀝니다.</td></tr>
        <tr><td><strong>세로 자동 맞춤</strong></td><td>체크 시 마지막 행이 하단 울거미에 딱 맞도록 문틀 세로 값을 자동 재조정</td></tr>
        <tr><td><strong>좌우 / 상하 울거미 두께</strong></td><td>외곽 프레임 두께 (mm)</td></tr>
        <tr><td><strong>창살 두께</strong></td><td>각 살의 폭 (mm). 정삼각형 셀 크기를 함께 결정합니다.</td></tr>
        <tr><td><strong>풍판 사용</strong></td><td>체크 시 상단 풍판 구역 추가</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>세로 칸수, 세로 비율 같은 별도 설정 항목은 없습니다. 삼각형 크기를 바꾸려면 가로 칸수 또는 창살 두께를 조정하세요.</span>
</div>

<h2>제작 시방서</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td>문틀 / 외경 / 내경 가로·세로</td><td>자동 계산된 실측 치수</td></tr>
        <tr><td>세로 먹줄</td><td>삼각형 교차점 간 간격</td></tr>
        <tr><td>반턱 너비</td><td>살이 교차하는 반턱의 너비</td></tr>
        <tr><td>세로 / 가로 울거미홈폭</td><td>울거미에 살이 파고드는 홈의 폭</td></tr>
    </tbody>
</table>

<h2>부재 목록</h2>
<p>울거미 → <strong>가로부재</strong>(패턴 세로 방향 설정에 따라 명칭이 바뀜) → <strong>사선살</strong>(60°·120° 두 방향) → (풍판 사용 시) 풍판 → 문틀 순으로 구성됩니다.</p>

<h2>마감 · 색상</h2>
<p>목재·마감·부자재, 울거미·살 컬러는 세살과 동일합니다. <strong>면 컬러 칠하기 기능은 현재 비활성화</strong>되어 있습니다.</p>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>세모 솟을살에서 한 방향 살을 생략하면 <strong>육모 솟을살</strong> 패턴이 됩니다. 두 엔진은 격자 수식 자체는 동일하고 그려지는 선의 방향만 다릅니다. <a href="/guide/studio-hexagon" style="color:var(--accent);">육모 솟을살</a> 페이지에서 비교해 보세요.</span>
</div>

<h2>활용 예시</h2>
<ul>
    <li>전통 누각 천장 격자 패턴</li>
    <li>현대 건축 파사드 스크린</li>
    <li>인테리어 천장 조명 박스</li>
</ul>
HTML
];

$articles['studio-hexagon'] = ['title' => '육모 솟을살', 'body' => <<<HTML
<h1><span class="guide-h1-icon">{$svgIcons['hexagon']}</span>육모 솟을살</h1>
<p class="guide-lead">
    세모솟을살과 같은 세 방향 살대를 쓰되, 교차점을 한 점에 모으지 않고 어긋나게 짜 육각형이 열리도록 한 육모 솟을살을 재현한 엔진입니다.
    어금육모라고도 부릅니다.
    '솟을'은 살이 교차점에서 겹치며 위로 솟아오르는 데서 온 이름으로, 짜임에 입체감이 살아 있습니다.
    살이 만드는 벌집 모양의 여섯 각은 사각보다 원에 가까워, 같은 짜임인데도 세모의 팽팽함 대신 둥글고 넉넉한 인상을 줍니다.
</p>

<h2>세모 솟을살과의 차이</h2>
<table class="guide-table">
    <thead><tr><th></th><th>세모 솟을살</th><th>육모 솟을살</th></tr></thead>
    <tbody>
        <tr><td>그려지는 살 방향 수</td><td>3가지 (0°, 60°, 120°)</td><td>2가지 (60°, 120°)</td></tr>
        <tr><td>셀 형태</td><td>정삼각형</td><td>육각형</td></tr>
        <tr><td>가로 칸수 단위</td><td>짝수만</td><td>홀수만</td></tr>
        <tr><td>격자 계산 수식</td><td>동일한 정삼각형 테셀레이션</td><td>동일한 정삼각형 테셀레이션 (선 생략만 다름)</td></tr>
    </tbody>
</table>

<h2>주요 파라미터</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td><strong>문 종류 / 문 짝수</strong></td><td>여닫이·미서기, 1~4짝</td></tr>
        <tr><td><strong>문틀 가로 / 문틀 세로</strong></td><td>벽 개구부 치수 (400~3,000mm). 문틀 두께를 제외한 값이 문짝 외경으로 자동 계산됩니다.</td></tr>
        <tr><td><strong>가로 칸수</strong></td><td>1~29, <strong>홀수 단위</strong>로만 조절됩니다(기본값 3). 이 값과 창살 두께로 육각형 크기가 정해집니다.</td></tr>
        <tr><td><strong>패턴 세로 방향</strong></td><td>기본 켜짐. 켜짐(세로형·pointy-top)/꺼짐(가로형·flat-top)으로 육각형 방향이 바뀝니다.</td></tr>
        <tr><td><strong>세로 자동 맞춤</strong></td><td>체크 시 마지막 행이 하단 울거미에 딱 맞도록 문틀 세로 값을 자동 재조정</td></tr>
        <tr><td><strong>좌우 / 상하 울거미 두께</strong></td><td>외곽 프레임 두께 (mm)</td></tr>
        <tr><td><strong>창살 두께</strong></td><td>각 살의 폭 (mm)</td></tr>
        <tr><td><strong>풍판 사용</strong></td><td>체크 시 상단 풍판 구역 추가</td></tr>
    </tbody>
</table>

<h2>제작 시방서</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td>문틀 / 외경 / 내경 가로·세로</td><td>자동 계산된 실측 치수</td></tr>
        <tr><td>변 간격</td><td>육각형 한 변 사이의 간격</td></tr>
        <tr><td>사선 먹줄</td><td>사선살의 기준 간격</td></tr>
        <tr><td>세로울거미홈간격</td><td>세로 울거미에 파이는 홈들 사이의 간격</td></tr>
        <tr><td>반턱 너비 / 세로·가로 울거미홈폭</td><td>세모 솟을살과 동일한 방식으로 계산</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>다른 5개 엔진의 제작 시방서에는 "전체 문폭" 카드가 있지만, 육모 솟을살에는 이 항목이 없습니다. 전체 폭이 필요하면 외경 가로 값을 참고하세요.</span>
</div>

<h2>부재 목록</h2>
<p>세모 솟을살과 동일한 구조입니다 — 울거미 → 방향별 부재(패턴 세로 방향에 따라 명칭 가변) → 사선살(두 방향) → (풍판 사용 시) 풍판 → 문틀.</p>

<h2>마감 · 색상</h2>
<p>목재·마감·부자재, 울거미·살 컬러는 세살과 동일합니다. <strong>면 컬러 칠하기 기능은 현재 비활성화</strong>되어 있습니다.</p>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>육모살(六毛살)은 한국 전통 목공에서 육각형 창살 패턴을 뜻하며, 고급 창호 제작에 자주 사용됩니다.</span>
</div>

<h2>활용 예시</h2>
<ul>
    <li>전통 한옥 육모살 창호</li>
    <li>사찰 법당 창살 패턴</li>
    <li>고급 한식 레스토랑 파티션</li>
</ul>
HTML
];

$pdo = db();
$stmt = $pdo->prepare('INSERT INTO guide_articles (slug, title, body_html) VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE title = VALUES(title), body_html = VALUES(body_html)');

foreach ($articles as $slug => $data) {
    $stmt->execute([$slug, $data['title'], $data['body']]);
    echo "seeded: {$slug}\n";
}

echo "done. " . count($articles) . " articles seeded.\n";
