<?php
$guide_current = 'getting-started.php';
$guide_title   = '시작하기';
$guide_prev    = ['href' => 'intro.php', 'title' => '평목이란?'];
$guide_next    = ['href' => 'studio-classic.php', 'title' => '세살'];
include __DIR__ . '/_head.php';
?>

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
    <li>상단 내비게이션에서 <span class="guide-ui">Studio</span>를 클릭합니다.</li>
    <li>드롭다운 메뉴에서 원하는 격자 패턴 엔진을 선택합니다.<br>
        처음이라면 <strong>세살</strong>을 권장합니다.</li>
    <li>스튜디오 화면이 열리면 왼쪽 사이드바에서 파라미터를 조정합니다.</li>
    <li>오른쪽 캔버스에 실시간으로 도면이 그려집니다.</li>
</ol>

<h2>3단계 — 첫 도면 저장</h2>
<ol class="guide-steps">
    <li>사이드바 상단의 <span class="guide-ui">도면 이름</span> 입력란에 이름을 입력합니다.</li>
    <li><span class="guide-ui">저장</span> 버튼 또는 <kbd>Ctrl</kbd>+<kbd>S</kbd>를 눌러 저장합니다.</li>
    <li>저장된 도면은 <span class="guide-ui">Dashboard</span> 페이지에서 언제든 불러올 수 있습니다.</li>
</ol>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>도면은 클라우드에 저장되므로, 다른 기기에서 로그인해도 동일한 도면에 접근할 수 있습니다.</span>
</div>

<h2>인터페이스 구성</h2>
<p>스튜디오 화면은 크게 세 영역으로 구성됩니다.</p>

<table class="guide-table">
    <thead><tr><th>영역</th><th>위치</th><th>역할</th></tr></thead>
    <tbody>
        <tr><td><strong>사이드바</strong></td><td>왼쪽</td><td>문 종류, 치수, 격자 살 설정 등 모든 파라미터 입력</td></tr>
        <tr><td><strong>캔버스</strong></td><td>중앙</td><td>도면 실시간 미리보기, 핀치/드래그로 확대·이동</td></tr>
        <tr><td><strong>툴바</strong></td><td>상단</td><td>저장, 내보내기(PDF/PNG), 렌더링 버튼</td></tr>
    </tbody>
</table>

<?php include __DIR__ . '/_foot.php'; ?>
