<?php
$guide_current = 'account.php';
$guide_title   = '프로필 & 회사 정보';
$guide_prev    = ['href' => 'collection.php', 'title' => '컬렉션 & 내 보드'];
$guide_next    = ['href' => 'order.php', 'title' => '주문 안내'];
include __DIR__ . '/_head.php';
?>

<h1>프로필 & 회사 정보</h1>
<p class="guide-lead">
    내 계정 정보와 회사 정보를 관리합니다. 프로필 이름·연락처와 함께 회사명·사업자번호 등을
    입력하면 PDF 내보내기에 자동으로 반영됩니다.
</p>

<h2>프로필 수정</h2>
<ol class="guide-steps">
    <li>상단 내비게이션 이메일 아이콘 → <span class="guide-ui"><i class="bi bi-person me-1"></i>프로필</span>을 클릭합니다.</li>
    <li><strong>이름</strong>과 <strong>연락처</strong>를 입력합니다.</li>
    <li><span class="guide-ui">저장</span> 버튼을 클릭합니다.</li>
</ol>

<h2>비밀번호 변경</h2>
<ol class="guide-steps">
    <li>프로필 페이지 하단의 <strong>비밀번호 변경</strong> 섹션으로 이동합니다.</li>
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
        <tr><td>대표자명</td><td>법인 대표 또는 개인사업자명</td></tr>
        <tr><td>주소</td><td>사업장 주소</td></tr>
        <tr><td>전화번호</td><td>대표 연락처</td></tr>
        <tr><td>이메일</td><td>업무용 이메일 (로그인 이메일과 별개)</td></tr>
        <tr><td>웹사이트</td><td>회사 홈페이지 URL (선택)</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>회사 정보를 입력해두면 도면 PDF 내보내기 시 로고·회사명이 자동으로 포함됩니다.</span>
</div>

<h2>계정 탈퇴</h2>
<p>
    계정 탈퇴를 원하시면 프로필 페이지 최하단의 <strong>계정 탈퇴</strong> 링크를 클릭하세요.
    탈퇴 시 저장된 도면과 컬렉션 보드 데이터가 모두 삭제되며, 복구할 수 없습니다.
</p>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>계정 탈퇴 전 중요한 도면은 반드시 PDF 또는 PNG로 내보내어 백업하세요.</span>
</div>

<?php include __DIR__ . '/_foot.php'; ?>
