<?php
$guide_current = 'order.php';
$guide_title   = '주문 안내';
$guide_prev    = ['href' => 'account.php', 'title' => '프로필 & 회사 정보'];
$guide_next    = ['href' => 'delivery.php', 'title' => '배송 안내'];
include __DIR__ . '/_head.php';
?>

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
    <span>이름과 연락처가 등록되어 있지 않으면 견적요청 버튼이 동작하지 않습니다. <a href="account.php">프로필 설정 가이드</a>를 참고하세요.</span>
</div>

<h2>주문 흐름</h2>

<div class="guide-flow">
    <div class="guide-flow-step">
        <span class="step-icon"><i class="bi bi-pencil-square" style="color:var(--teal)"></i></span>
        <div class="step-title">도면 설계</div>
        <div class="step-desc">스튜디오에서 창호 도면을 완성합니다.</div>
    </div>
    <div class="guide-flow-arrow"><i class="bi bi-chevron-right"></i></div>
    <div class="guide-flow-step">
        <span class="step-icon"><i class="bi bi-cart-check" style="color:#B8662F"></i></span>
        <div class="step-title">견적요청 접수</div>
        <div class="step-desc">배송지·납기일을 입력하고 제출합니다.</div>
    </div>
    <div class="guide-flow-arrow"><i class="bi bi-chevron-right"></i></div>
    <div class="guide-flow-step">
        <span class="step-icon"><i class="bi bi-telephone" style="color:#2E6FA8"></i></span>
        <div class="step-title">담당자 연락</div>
        <div class="step-desc">담당자가 확인 후 견적서를 보내드립니다.</div>
    </div>
    <div class="guide-flow-arrow"><i class="bi bi-chevron-right"></i></div>
    <div class="guide-flow-step">
        <span class="step-icon"><i class="bi bi-tools" style="color:#7A6B40"></i></span>
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
    </div>
</div>

<h2>도면 잠금</h2>
<p>
    견적요청이 접수된 도면은 <strong>편집·저장·삭제가 제한</strong>됩니다.
    이는 요청 접수 이후 사양이 변경되어 발생할 수 있는 혼선을 방지하기 위한 조치입니다.
</p>
<p>
    도면 목록에서 해당 도면에 <strong><i class="bi bi-lock-fill"></i> 견적요청 중</strong> 배지가 표시됩니다.
    견적 처리가 완료되면 잠금이 해제됩니다.
</p>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>견적요청 후 사양 변경이 필요한 경우 담당자에게 연락해주세요. 도면을 새로 저장하여 재요청하는 방법도 안내해드립니다.</span>
</div>

<h2>담당자 응대</h2>
<p>
    접수 후 영업일 기준 1~2일 이내에 담당자가 연락드립니다.
    등록된 이메일 또는 연락처로 견적서와 함께 세부 사항을 안내해드립니다.
</p>

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

<?php include __DIR__ . '/_foot.php'; ?>
