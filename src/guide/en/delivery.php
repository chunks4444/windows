<?php
// 영문 본문. 한글 원문은 ../delivery.php
$guide_current = 'delivery.php';
$guide_title   = 'Delivery Guide';
$guide_prev    = ['href' => 'order.php', 'title' => 'Ordering Guide'];
$guide_next    = ['href' => 'faq.php', 'title' => 'Frequently Asked Questions'];
include __DIR__ . '/../_head.php';
?>

<h1>Delivery Guide</h1>
<p class="guide-lead">
    Finished changho are shipped by parcel or freight depending on their size and quantity.
    Shipping costs are paid by the customer; return shipping for defective products is covered by Pyeongmok.
</p>

<h2>Shipping Methods</h2>

<table class="guide-table">
    <thead><tr><th>Method</th><th>Applies to</th><th>Notes</th></tr></thead>
    <tbody>
        <tr>
            <td><strong>Parcel delivery</strong></td>
            <td>Small items, small orders</td>
            <td>Shipped through a standard courier service.</td>
        </tr>
        <tr>
            <td><strong>Freight delivery</strong></td>
            <td>Large items, bulk orders</td>
            <td>Shipped by freight carrier. Our staff will give you the details separately.</td>
        </tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>Our staff will confirm the shipping method once the product specifications are settled. Feel free to ask during the quote consultation.</span>
</div>

<h2>Shipping Costs</h2>
<p>Shipping is <strong>paid by the customer</strong> and varies with the shipping method and destination. The exact cost is confirmed along with your final quote.</p>

<h2>Returns &amp; Exchanges</h2>
<p>If you find a defect after receiving your order, please contact our staff right away.</p>

<table class="guide-table">
    <thead><tr><th>Reason</th><th>Who pays shipping</th></tr></thead>
    <tbody>
        <tr>
            <td>Return due to a product defect or error</td>
            <td><strong>Pyeongmok</strong></td>
        </tr>
        <tr>
            <td>Return due to a change of mind or ordering mistake</td>
            <td><strong>The customer</strong></td>
        </tr>
    </tbody>
</table>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>Please request returns and exchanges within 7 days of receiving your order. Products that have been used or modified cannot be returned.</span>
</div>

<?php include __DIR__ . '/../_foot.php'; ?>
