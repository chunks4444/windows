<?php
// 영문 본문. 한글 원문은 ../account.php
$guide_current = 'account.php';
$guide_title   = 'Profile & Company Info';
$guide_prev    = ['href' => 'collection.php', 'title' => 'Collection & My Boards'];
$guide_next    = ['href' => 'order.php', 'title' => 'Ordering Guide'];
include __DIR__ . '/../_head.php';
?>

<h1>Profile &amp; Company Info</h1>
<p class="guide-lead">
    Manage your account and company details. Along with your profile name and contact number, entering a
    company name, business registration number and the like means they are included automatically in PDF exports.
</p>

<h2>Editing Your Profile</h2>
<ol class="guide-steps">
    <li>Click the account icon in the top navigation → <span class="guide-ui"><i class="bi bi-person me-1"></i>Profile</span>.</li>
    <li>Your login email and membership badge are shown read-only at the top.</li>
    <li>Enter your <strong>name</strong>, <strong>phone number</strong> and <strong>address</strong> (postal code, street address, detailed address).</li>
    <li>Click <span class="guide-ui">Save</span>.</li>
</ol>

<h2>Changing Your Password</h2>
<ol class="guide-steps">
    <li>Go to the <strong>Change password</strong> section of the profile page.</li>
    <li>Enter your <strong>current password</strong> and <strong>new password</strong>.</li>
    <li>Click <span class="guide-ui">Change</span>.</li>
</ol>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>Accounts registered through social login (Google or Kakao) do not show the password change menu.</span>
</div>

<h2>Entering Company Information</h2>
<ol class="guide-steps">
    <li>Click the account icon in the top navigation → <span class="guide-ui"><i class="bi bi-building me-1"></i>Company Info</span>.</li>
    <li>Fill in the fields below.</li>
</ol>

<table class="guide-table">
    <thead><tr><th>Field</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td>Company name</td><td>Your business or workshop name</td></tr>
        <tr><td>Business registration number</td><td>In 000-00-00000 format</td></tr>
        <tr><td>Business type / category</td><td>As listed on your business registration certificate</td></tr>
        <tr><td>Representative name</td><td>The company representative or sole proprietor's name</td></tr>
        <tr><td>Phone number</td><td>Main contact number</td></tr>
        <tr><td>Address</td><td>Search by postal code, then enter the street and detailed address</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>There are no separate fields for a work email or website URL. Your login account's email is used as-is.</span>
</div>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>With company information saved, your logo and company name are included automatically when you export a drawing as PDF.</span>
</div>

<h2>Price Visibility by Membership Level</h2>
<p>
    The estimate and cost information shown in the studio varies by membership level.
    For newly registered general members, items such as the estimated price, minimum lead time and
    shipping cost are hidden by default — a workshop administrator has to approve viewing access for
    each account individually before they appear.
</p>

<table class="guide-table">
    <thead><tr><th>Item</th><th>Visible by default to</th></tr></thead>
    <tbody>
        <tr><td>Estimated price</td><td>Approved members only — before approval, no price is shown in the studio at all.</td></tr>
        <tr><td>Cost breakdown (wood, labor, hardware, finish, overhead subtotals)</td><td>Workshop administrators, or members separately approved</td></tr>
        <tr><td>Minimum lead time · shipping cost</td><td>Approved members only</td></tr>
        <tr><td>Parts list · production specification</td><td>Approved members only</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>If you need viewing access, contact the workshop to request approval for your account. Quote requests and ordering themselves work regardless of viewing access.</span>
</div>

<h2>Closing Your Account</h2>
<p>
    Account closure cannot be done from the profile page.
    To close your account, please request it through <a href="<?= lang_href('/company/') ?>#contact">workshop contact</a>.
    A staff member will confirm and process it.
    Closing your account deletes all saved drawings and collection board data permanently — it cannot be recovered.
</p>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>Before closing your account, be sure to back up any important drawings by exporting them as PDF or PNG.</span>
</div>

<?php include __DIR__ . '/../_foot.php'; ?>
