<?php
$guide_current = 'delivery.php';
$guide_title   = '배송 안내';
$guide_prev    = ['href' => 'order.php', 'title' => '주문 안내'];
$guide_next    = null;
include __DIR__ . '/_head.php';
?>

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

<?php include __DIR__ . '/_foot.php'; ?>
