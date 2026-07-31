<?php
$guide_current = 'collection.php';
$guide_title   = '컬렉션 & 내 보드';
$guide_prev    = ['href' => 'render.php', 'title' => 'AI 렌더링 사용법'];
$guide_next    = ['href' => 'account.php', 'title' => '프로필 & 회사 정보'];
include __DIR__ . '/_head.php';
?>

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

<?php include __DIR__ . '/_foot.php'; ?>
