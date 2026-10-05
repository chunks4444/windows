<?php
$guide_current = 'drawing.php';
$guide_title   = '도면 저장 & 불러오기';
$guide_prev    = ['href' => 'svg-insert.php', 'title' => '문양 삽입 & SVG 업로드'];
$guide_next    = ['href' => 'export.php', 'title' => 'PDF / PNG / DXF 내보내기'];
include __DIR__ . '/_head.php';
?>

<h1>도면 저장 & 불러오기</h1>
<p class="guide-lead">
    작업한 도면은 클라우드에 저장되어 언제든 다시 불러올 수 있습니다.
    버전 히스토리도 자동으로 관리되어 이전 작업 상태로 되돌릴 수 있습니다.
</p>

<h2>도면 저장하기</h2>
<ol class="guide-steps">
    <li>왼쪽 메뉴의 <span class="guide-ui">도면</span> 탭을 엽니다.</li>
    <li><span class="guide-ui">도면 이름</span> 칸에 도면 이름을 입력하고 <span class="guide-ui">저장</span> 버튼을 클릭합니다.</li>
    <li>처음 저장 시 새 도면이 생성되고, 이후 동일 이름으로 저장하면 버전이 쌓입니다.</li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>도면 이름은 프로젝트명·현장명으로 관리하면 도면관리에서 찾기 쉽습니다. 예: <em>청담동_한옥_정면창</em></span>
</div>

<h2>도면 불러오기</h2>
<ol class="guide-steps">
    <li>왼쪽 메뉴의 <span class="guide-ui">도면</span> 탭을 엽니다.</li>
    <li>아래 <strong>내 도면</strong> 썸네일에서 불러올 도면을 클릭합니다. 썸네일 오른쪽 위의 <i class="bi bi-pencil"></i> 버튼으로 이름을 바꾸고, <i class="bi bi-trash3"></i> 버튼으로 지울 수 있습니다.</li>
    <li>도면의 파라미터가 캔버스에 복원됩니다.</li>
</ol>

<h2>버전 관리</h2>
<p>도면을 저장할 때마다 버전이 생성됩니다. 버전 히스토리를 통해 이전 상태로 되돌릴 수 있습니다.</p>
<ol class="guide-steps">
    <li>도면 탭의 <strong>현재 도면 버전</strong> 드롭다운을 열고 되돌리고 싶은 버전을 클릭합니다.</li>
    <li>해당 버전의 파라미터가 캔버스에 반영됩니다.</li>
    <li>확인 후 현재 버전으로 다시 저장하면 최신 버전이 됩니다.</li>
</ol>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>버전은 도면당 최대 <strong>20개</strong>까지 보관되며, 초과하면 가장 오래된 버전부터 자동으로 삭제됩니다.</span>
</div>

<h2>도면 공유하기</h2>
<p>저장한 도면은 링크 하나로 다른 사람에게 공유할 수 있습니다. 받는 사람은 로그인 없이도 도면을 확인할 수 있습니다.</p>
<ol class="guide-steps">
    <li>도면 탭의 <span class="guide-ui">공유</span> 버튼을 클릭합니다. (저장 전에는 비활성화되어 있으며, "먼저 저장해주세요" 안내가 표시됩니다.)</li>
    <li>공유 패널이 열리면서 자동으로 공유가 켜지고, 공유 링크와 <span class="guide-ui">복사</span> 버튼, 카카오톡·페이스북·X 공유 버튼이 표시됩니다.</li>
    <li>도면관리 페이지의 도면 카드에서도 우측 상단 공유 아이콘으로 동일하게 공유할 수 있습니다.</li>
    <li>공유를 중단하려면 패널의 <span class="guide-ui">공유 끄기</span> 버튼을 클릭합니다.</li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>공유 링크를 열면 해당 도면의 파라미터가 캔버스에 그대로 불러와집니다. 로그인한 상태라면 자유롭게 값을 수정한 뒤 <strong>새 이름으로 저장</strong>하면 원본은 그대로 두고 내 계정에 새 도면(사본)이 생성됩니다. 별도의 "복사" 버튼은 없습니다.</span>
</div>

<h2>도면관리</h2>
<p>상단 내비게이션 <span class="guide-ui">스튜디오</span> → <span class="guide-ui">도면관리</span>(또는 사용자 메뉴의 <span class="guide-ui">도면관리</span>)에서 저장한 도면을 한곳에서 관리할 수 있습니다. 페이지는 <strong>내 도면 · 내 보드 · 렌더링 · 주문내역</strong> 네 탭으로 나뉩니다.</p>
<ul>
    <li><strong>내 도면</strong> — 모든 엔진의 도면이 썸네일 카드로 모여 있고, 위쪽 검색칸에서 도면 이름으로 찾을 수 있습니다. 카드를 누르면 해당 엔진에서 바로 열립니다.</li>
    <li>카드에는 엔진 아이콘과 도면 이름, <strong>패턴 분류</strong>, 버전 수(ver), 마지막 수정일, 작업 시간이 표시됩니다. 패턴 분류는 카드에서 바로 바꿀 수 있습니다.</li>
    <li>카드 오른쪽 위의 <span class="guide-ui"><i class="bi bi-copy"></i> 복사</span>로 도면을 새 이름의 사본으로 복제하고, <span class="guide-ui"><i class="bi bi-share-fill"></i> 공유</span>로 공유 링크를 켜고 끄며, <span class="guide-ui"><i class="bi bi-trash"></i> 삭제</span>로 지웁니다.</li>
    <li>견적요청 중인 도면에는 <i class="bi bi-lock-fill"></i> 배지와 주문 상태가 표시되고 삭제할 수 없습니다.</li>
    <li><strong>렌더링</strong> 탭에서는 모든 엔진의 AI 렌더링 결과를 모아 보고 공유·삭제할 수 있습니다 — <a href="/guide/render">AI 렌더링 사용법</a> 참고.</li>
</ul>

<h2>도면 이름 변경</h2>
<ol class="guide-steps">
    <li>왼쪽 메뉴의 <span class="guide-ui">도면</span> 탭을 열고, 아래 <strong>내 도면</strong>에서 이름을 바꿀 도면 썸네일에 마우스를 올립니다.</li>
    <li>오른쪽 위의 <span class="guide-ui"><i class="bi bi-pencil"></i></span> 버튼을 누르고 새 이름을 입력한 뒤 <span class="guide-ui">변경</span>을 클릭합니다.</li>
    <li>버전·공유 설정은 그대로 유지되고, 도면관리 페이지에도 바로 반영됩니다. 견적요청 중인 도면은 이름을 바꿀 수 없습니다.</li>
</ol>

<?php include __DIR__ . '/_foot.php'; ?>
