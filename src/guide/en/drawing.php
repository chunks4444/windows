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
    <li>Open the <span class="guide-ui">Drawing</span> tab in the left-hand menu.</li>
    <li>Enter a name in the <span class="guide-ui">Drawing name</span> field and click <span class="guide-ui">Save</span>.</li>
    <li>The first save creates a new drawing; saving again under the same name adds versions.</li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Naming drawings after the project or site makes them easier to find later. For example: <em>Cheongdam-dong_hanok_front-changho</em></span>
</div>

<h2>Loading a Drawing</h2>
<ol class="guide-steps">
    <li>Open the <span class="guide-ui">Drawing</span> tab in the left-hand menu.</li>
    <li>Click the drawing you want among the <strong>My drawings</strong> thumbnails. The buttons at a thumbnail's top right let you rename it (<i class="bi bi-pencil"></i>) or delete it (<i class="bi bi-trash3"></i>).</li>
    <li>Its parameters are restored onto the canvas.</li>
</ol>

<h2>Version Management</h2>
<p>A version is created every time you save. Version history lets you return to an earlier state.</p>
<ol class="guide-steps">
    <li>Open the <strong>Versions of this drawing</strong> dropdown in the Drawing tab and click the version you want.</li>
    <li>That version's parameters are applied to the canvas.</li>
    <li>Once you've checked it, save again to make it the latest version.</li>
</ol>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>Up to <strong>20</strong> versions are kept per drawing. Beyond that, the oldest versions are deleted automatically.</span>
</div>

<h2>Sharing a Drawing</h2>
<p>Saved drawings can be shared with a single link, and the recipient can view the drawing without logging in.</p>
<ol class="guide-steps">
    <li>Click the <span class="guide-ui">Share</span> button in the Drawing tab. (It is disabled before you save, with a "please save first" notice.)</li>
    <li>The share panel opens with sharing switched on automatically, showing the share link, a <span class="guide-ui">Copy</span> button, and KakaoTalk, Facebook and X share buttons.</li>
    <li>You can share the same way from the share icon at the top right of each drawing card on the My Drawings page.</li>
    <li>To stop sharing, click <span class="guide-ui">Turn off sharing</span> in the panel.</li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Opening a share link loads that drawing's parameters onto the canvas as they are. If you are logged in, you can freely change the values and <strong>save under a new name</strong> — the original stays untouched and a new drawing (a copy) is created in your own account. There is no separate "copy" button.</span>
</div>

<h2>My Drawings</h2>
<p>Manage your saved drawings in one place from <span class="guide-ui">Studio</span> → <span class="guide-ui">My Drawings</span> in the top navigation (or <span class="guide-ui">My Drawings</span> in the user menu). The page has four tabs: <strong>Drawings · Boards · Renders · Orders</strong>.</p>
<ul>
    <li><strong>Drawings</strong> — drawings from every engine, shown as thumbnail cards. Use the search box at the top to find one by name. Click a card to open it in its engine.</li>
    <li>Each card shows the engine icon and drawing name, its <strong>pattern category</strong>, version count (ver), last modified date and working time. You can change the pattern category right on the card.</li>
    <li>The buttons at a card's top right let you <span class="guide-ui"><i class="bi bi-copy"></i> Copy</span> the drawing under a new name, turn its share link on or off with <span class="guide-ui"><i class="bi bi-share-fill"></i> Share</span>, or <span class="guide-ui"><i class="bi bi-trash"></i> Delete</span> it.</li>
    <li>Drawings with a quote request in progress show a <i class="bi bi-lock-fill"></i> badge with the order status and cannot be deleted.</li>
    <li>The <strong>Renders</strong> tab gathers AI rendering results from every engine for viewing, sharing and deleting — see <a href="<?= lang_href('/guide/render') ?>">Using AI Rendering</a>.</li>
</ul>

<h2>Renaming a Drawing</h2>
<ol class="guide-steps">
    <li>Open the <span class="guide-ui">Drawing</span> tab in the left-hand menu and hover over the drawing's thumbnail under <strong>My drawings</strong>.</li>
    <li>Click the <span class="guide-ui"><i class="bi bi-pencil"></i></span> button at its top right, type the new name and click <span class="guide-ui">Rename</span>.</li>
    <li>Versions and share settings are kept, and the change appears on the My Drawings page right away. Drawings with a quote request in progress cannot be renamed.</li>
</ol>

<?php include __DIR__ . '/../_foot.php'; ?>
