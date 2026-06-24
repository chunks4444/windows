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
    마음에 드는 패턴을 스튜디오로 바로 불러오거나 내 보드에 저장해 영감으로 활용하세요.
</p>

<h2>컬렉션 둘러보기</h2>
<ol class="guide-steps">
    <li>상단 내비게이션의 <span class="guide-ui">Collection</span>을 클릭합니다.</li>
    <li>격자 형태로 공개 도면 패턴이 표시됩니다.</li>
    <li>카드를 클릭하면 도면 상세 페이지로 이동합니다.</li>
    <li>상세 페이지에서 <span class="guide-ui">스튜디오에서 열기</span>를 클릭하면 해당 파라미터로 스튜디오가 열립니다.</li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>컬렉션 도면은 로그인 없이도 열람할 수 있지만, 스튜디오에서 열기 및 보드 저장은 로그인이 필요합니다.</span>
</div>

<h2>컬렉션 필터</h2>
<p>상단 필터 탭으로 패턴 유형별로 필터링할 수 있습니다.</p>
<ul>
    <li><strong>전체</strong> — 모든 컬렉션 표시</li>
    <li><strong>세살 / 정자살 / 빗살 / 격자 빗살 / 세모 솟을살 / 육모 솟을살</strong> — 엔진별 분류</li>
</ul>

<h2>스튜디오에서 열기</h2>
<p>
    컬렉션 도면을 스튜디오에서 열면 해당 파라미터가 캔버스에 복원됩니다.
    이 상태에서 자유롭게 수정한 뒤 <strong>새 이름으로 저장</strong>해 내 도면으로 만들 수 있습니다.
</p>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>컬렉션에서 불러온 도면은 원본을 덮어쓰지 않습니다. 저장 시 반드시 새 이름을 입력해 내 도면으로 저장하세요.</span>
</div>

<h2>내 보드</h2>
<p>마음에 드는 컬렉션 도면을 내 보드에 저장해 나만의 레퍼런스 라이브러리를 만들 수 있습니다.</p>
<ol class="guide-steps">
    <li>컬렉션 카드의 <span class="guide-ui"><i class="bi bi-bookmark"></i></span> 아이콘을 클릭하거나, 상세 페이지의 <span class="guide-ui">보드에 저장</span>을 클릭합니다.</li>
    <li>저장할 보드를 선택하거나 <span class="guide-ui">+ 새 보드</span>로 보드를 생성합니다.</li>
    <li>내 보드는 상단 내비게이션 <span class="guide-ui">Collection</span> 드롭다운의 <strong>내 보드</strong> 섹션에서 확인할 수 있습니다.</li>
</ol>

<?php include __DIR__ . '/_foot.php'; ?>
