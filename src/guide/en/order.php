<?php
// 영문 본문. 한글 원문은 ../order.php
$guide_current = 'order.php';
$guide_title   = 'Ordering Guide';
$guide_prev    = ['href' => 'account.php', 'title' => 'Profile & Company Info'];
$guide_next    = ['href' => 'delivery.php', 'title' => 'Delivery Guide'];
include __DIR__ . '/../_head.php';
?>

<h1>Ordering Guide</h1>
<p class="guide-lead">
    Commission production based on the drawing you designed in the studio.
    Once your quote request is received, a staff member reviews it and gets in touch.
</p>

<h2>Before You Order</h2>
<p>Two things need to be in place before requesting a quote.</p>

<table class="guide-table">
    <thead><tr><th>Requirement</th><th>How to check</th></tr></thead>
    <tbody>
        <tr>
            <td><strong>Logged in</strong></td>
            <td>Check your login status in the top navigation.</td>
        </tr>
        <tr>
            <td><strong>Profile name and phone number</strong></td>
            <td>Enter your name and phone number under the account icon → <span class="guide-ui">Profile</span>.</td>
        </tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>The quote request button won't work until your name and phone number are saved. See the <a href="<?= lang_href('/guide/account') ?>">profile settings guide</a>.</span>
</div>

<h2>The Ordering Flow</h2>

<div class="guide-flow">
    <div class="guide-flow-step">
        <span class="step-icon"><i class="bi bi-pencil-square" style="color:var(--accent)"></i></span>
        <div class="step-title">Design the drawing</div>
        <div class="step-desc">Complete your changho drawing in the studio.</div>
    </div>
    <div class="guide-flow-arrow"><i class="bi bi-chevron-right"></i></div>
    <div class="guide-flow-step">
        <span class="step-icon"><i class="bi bi-cart-check" style="color:var(--accent)"></i></span>
        <div class="step-title">Submit a quote request</div>
        <div class="step-desc">Enter the delivery address and requested date, then submit.</div>
    </div>
    <div class="guide-flow-arrow"><i class="bi bi-chevron-right"></i></div>
    <div class="guide-flow-step">
        <span class="step-icon"><i class="bi bi-telephone" style="color:var(--accent)"></i></span>
        <div class="step-title">We contact you</div>
        <div class="step-desc">A staff member reviews it and sends a quote.</div>
    </div>
    <div class="guide-flow-arrow"><i class="bi bi-chevron-right"></i></div>
    <div class="guide-flow-step">
        <span class="step-icon"><i class="bi bi-tools" style="color:var(--accent)"></i></span>
        <div class="step-title">Production &amp; delivery</div>
        <div class="step-desc">Production begins once payment is confirmed.</div>
    </div>
</div>

<h2>Requesting a Quote</h2>
<ol class="guide-steps">
    <li>Click the <span class="guide-ui"><i class="bi bi-cart-check me-1"></i>Request quote</span> button on the studio's top toolbar.</li>
    <li>Review and fill in the request details.
        <table class="guide-table" style="margin-top:12px">
            <thead><tr><th>Field</th><th>Description</th></tr></thead>
            <tbody>
                <tr><td>Your details</td><td>The name, phone number and company name from your profile are filled in automatically.</td></tr>
                <tr><td>Drawing details</td><td>The currently open drawing's title, version and thumbnail are attached automatically.</td></tr>
                <tr><td>Requested completion date</td><td>Choose the date you'd like production finished by.</td></tr>
                <tr><td>Delivery address</td><td>Your profile address is filled in by default; change it with <span class="guide-ui">Search address</span>.</td></tr>
                <tr><td>Request notes</td><td>Add any extra requests, such as finish treatment or color. (Optional)</td></tr>
            </tbody>
        </table>
    </li>
    <li>Click <span class="guide-ui">Submit quote request</span>.</li>
    <li>A confirmation message appears once it's received, and a notification email is sent to our staff.</li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Always save your drawing before requesting a quote. Requesting from a saved drawing lets our staff confirm the exact specifications.</span>
</div>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <div>
        <div>※ Shipping and installation costs are not included.</div>
        <div>※ The amount shown on the drawing is an estimate. The final quote is confirmed after we review your edits.</div>
        <div>※ Whether estimates and cost breakdowns are visible depends on your membership level. See "Price Visibility by Membership Level" in the <a href="<?= lang_href('/guide/account') ?>">Profile &amp; Company Info guide</a> for details.</div>
    </div>
</div>

<h2>Drawing Locks</h2>
<p>
    Once a quote request has been submitted, that drawing becomes <strong>restricted from editing, saving
    and deletion</strong> depending on the order's progress.
    This prevents confusion that could arise if specifications changed after the request was received.
</p>
<p>
    In the drawing list, a locked drawing shows a <strong><i class="bi bi-lock-fill"></i></strong> badge
    labeled with the current order status (for example, <em>In production</em>).
    The statuses that lock a drawing are
    <strong>Quote review · Approved · Quote confirmed · Payment received · In production · Production complete · Shipped</strong>.
</p>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>If a staff member changes the status to <strong>Revision requested</strong>, the lock is released and you can edit and save the drawing again. Locks are also released after delivery is complete or the order is canceled.</span>
</div>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>If you need to change specifications while a drawing is locked, please contact our staff. Once they switch the order to revision-requested status, you can edit the drawing and submit the request again.</span>
</div>

<h2>How We Respond</h2>
<p>
    A staff member will contact you within one to two business days of your request.
    We'll send the quote along with the details to your registered email or phone number.
</p>

<h2>Checking Order Status</h2>
<p>
    An <strong>"Order #N"</strong> is issued when your quote request is received, and you can follow its
    progress from the top navigation under
    <span class="guide-ui">My Page</span> → <span class="guide-ui">My Drawings</span> →
    the <span class="guide-ui">Order History</span> tab.
</p>
<p>Each order moves through these statuses in order.</p>
<table class="guide-table">
    <thead><tr><th>Status</th><th>Meaning</th></tr></thead>
    <tbody>
        <tr><td>Quote review</td><td>Our staff are reviewing your request.</td></tr>
        <tr><td>Revision requested</td><td>Our staff have asked for changes to the drawing's specifications.</td></tr>
        <tr><td>Approved</td><td>The specifications are settled and we're calculating the quote.</td></tr>
        <tr><td>Quote confirmed</td><td>The final quote has been confirmed.</td></tr>
        <tr><td>Payment received</td><td>Your payment has been confirmed.</td></tr>
        <tr><td>In production</td><td>The piece is being made at the workshop.</td></tr>
        <tr><td>Production complete</td><td>Production is finished and we're preparing to ship.</td></tr>
        <tr><td>Shipped</td><td>The piece has been dispatched.</td></tr>
        <tr><td>Delivered</td><td>Delivery is complete.</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span><strong>Canceled</strong> is a terminal status outside this flow. It applies when you cancel the order yourself or our staff cancel it, and no further steps follow.</span>
</div>

<p>Clicking any row in the order history opens a detail dialog showing:</p>
<ul>
    <li>The current status badge and your request notes</li>
    <li>For revision-requested orders, the reason our staff gave</li>
    <li>For shipped or delivered orders, the delivery details (courier and tracking number)</li>
    <li>Order date · requested completion date · last update</li>
    <li>The estimate and the confirmed price</li>
    <li>A <span class="guide-ui">View drawing</span> button that takes you straight to the drawing</li>
</ul>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>You can <span class="guide-ui">cancel an order</span> directly from the order history only while its status is <strong>Quote review</strong> or <strong>Revision requested</strong>. For later stages, please contact our staff.</span>
</div>

<h2>Frequently Asked Questions</h2>

<h3>I don't see the quote request button.</h3>
<p>
    The button isn't shown when you're not logged in.
    Please log in and try again.
</p>

<h3>I want a delivery address different from my profile.</h3>
<p>
    You can enter a separate delivery address with the <span class="guide-ui">Search address</span> button
    inside the quote request dialog. It applies to that order only and doesn't change your profile address.
</p>

<h3>I want to order several drawings at once.</h3>
<p>
    For now, each drawing has to be submitted separately.
    Note the drawings you're ordering together in the request notes and our staff will handle them as one batch.
</p>

<?php include __DIR__ . '/../_foot.php'; ?>
