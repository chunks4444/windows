<?php
// 영문 본문. 한글 원문은 ../drawing.php
$guide_current = 'drawing.php';
$guide_title   = 'Saving & Loading Drawings';
$guide_prev    = ['href' => 'svg-insert.php', 'title' => 'Inserting Motifs & Uploading SVGs'];
$guide_next    = ['href' => 'export.php', 'title' => 'PDF / PNG / DXF Export'];
include __DIR__ . '/../_head.php';
?>

<h1>Saving &amp; Loading Drawings</h1>
<p class="guide-lead">
    Your drawings are saved to the cloud and can be reopened at any time.
    Version history is kept automatically, so you can roll back to an earlier state of your work.
</p>

<h2>Saving a Drawing</h2>
<ol class="guide-steps">
    <li>Enter a name in the <span class="guide-ui">drawing name</span> field on the canvas top bar.</li>
    <li>Click <span class="guide-ui">Save</span>.</li>
    <li>The first save creates a new drawing; saving again under the same name adds versions.</li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Naming drawings after the project or site makes them easier to find later. For example: <em>Cheongdam-dong_hanok_front-changho</em></span>
</div>

<h2>Loading a Drawing</h2>
<ol class="guide-steps">
    <li>Click the <span class="guide-ui">Drawings</span> button on the canvas top bar.</li>
    <li>Click the drawing you want from the list of saved drawings.</li>
    <li>Its parameters are restored onto the canvas.</li>
</ol>

<h2>Version Management</h2>
<p>A version is created every time you save. Version history lets you return to an earlier state.</p>
<ol class="guide-steps">
    <li>Click the version you want in the <span class="guide-ui">Version ▾</span> dropdown on the canvas top bar.</li>
    <li>That version's parameters are applied to the canvas.</li>
    <li>Once you've checked it, save again to make it the latest version.</li>
</ol>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>Up to <strong>20</strong> versions are kept per drawing. Beyond that, the oldest versions are deleted automatically.</span>
</div>

<h2>Sharing a Drawing</h2>
<p>Saved drawings can be shared with a single link, and the recipient can view the drawing without logging in.</p>
<ol class="guide-steps">
    <li>Click the <span class="guide-ui">Share</span> button on the canvas top bar. (It is disabled before you save, with a "please save first" notice.)</li>
    <li>The share panel opens with sharing switched on automatically, showing the share link, a <span class="guide-ui">Copy</span> button, and KakaoTalk, Facebook and X share buttons.</li>
    <li>You can share the same way from the share icon at the top right of each drawing card on the My Drawings page.</li>
    <li>To stop sharing, click <span class="guide-ui">Turn off sharing</span> in the panel.</li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Opening a share link loads that drawing's parameters onto the canvas as they are. If you are logged in, you can freely change the values and <strong>save under a new name</strong> — the original stays untouched and a new drawing (a copy) is created in your own account. There is no separate "copy" button.</span>
</div>

<h2>My Drawings</h2>
<p>Manage all your drawings in one place from <span class="guide-ui">Studio</span> → <span class="guide-ui">My Drawings</span> in the top navigation.</p>
<ul>
    <li>Tabs sort your drawings by engine.</li>
    <li>Thumbnails let you identify drawings at a glance.</li>
    <li>You can rename and delete drawings.</li>
    <li>The <span class="guide-ui">Copy</span> button on a drawing card duplicates your drawing so you can start a new variation.</li>
    <li>Working time is recorded.</li>
</ul>

<h2>Renaming a Drawing</h2>
<ol class="guide-steps">
    <li>Edit the name in the drawing name field on the canvas top bar.</li>
    <li>Click the <span class="guide-ui">Rename</span> button.</li>
    <li>The change appears in My Drawings immediately.</li>
</ol>

<?php include __DIR__ . '/../_foot.php'; ?>
