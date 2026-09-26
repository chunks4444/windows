<?php
// 영문 본문. 한글 원문은 ../collection.php
$guide_current = 'collection.php';
$guide_title   = 'Collection & My Boards';
$guide_prev    = ['href' => 'render.php', 'title' => 'Using AI Rendering'];
$guide_next    = ['href' => 'account.php', 'title' => 'Profile & Company Info'];
include __DIR__ . '/../_head.php';
?>

<h1>Collection &amp; My Boards</h1>
<p class="guide-lead">
    The Collection is a public library of changho drawings curated by Workgroup Pyeongmok.
    Open a pattern you like directly in the studio, or save it with a like or to your boards to keep as inspiration.
</p>

<h2>Browsing the Collection</h2>
<ol class="guide-steps">
    <li>Click <span class="guide-ui">Collection</span> in the top navigation.</li>
    <li>Public drawing patterns are displayed in a grid.</li>
    <li>Click a card to go to that drawing's detail page.</li>
    <li>On the detail page, click <span class="guide-ui">Open in studio</span> and the studio opens with those parameters.</li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Collection drawings can be browsed without logging in, but opening them in the studio, liking them and saving to boards all require an account.</span>
</div>

<h2>How Pattern Names Work</h2>
<p>
    Recently added collection patterns are named in a code format such as <strong>JEO-SE-001</strong>.
    The first part is the family (the broad motif category), the middle is a more specific qualifier, and
    the final number is a serial number.
    Older patterns that don't have a code yet are still shown with their Korean names.
</p>

<h2>Collection Filters</h2>
<p>
    At the top of the Collection are three dropdowns — <strong>Uri-sal</strong> (Korean traditional),
    <strong>Sae-sal</strong> (new designs) and <strong>Ilbon-sal</strong> (Japanese) — plus a search box and a likes toggle.
</p>
<ul>
    <li><strong>Uri-sal</strong> — the Korean traditional families: Jeongja-sal, Aja-sal, Wanja-sal, Sutdae-sal, Se-sal, Bit-sal, Gyeokja-bit-sal, Gwigap-sal, Beom-sal, Sotgeul-sal and more. Opening the dropdown reveals each family for finer filtering.</li>
    <li><strong>Sae-sal</strong> — newly designed patterns that don't belong to a traditional family</li>
    <li><strong>Ilbon-sal</strong> — Japanese lattice patterns. Opening the dropdown reveals <strong>Shoji</strong> and <strong>Kumiko</strong> sub-options.</li>
    <li><strong>Pattern search</strong> — search by name or code</li>
    <li><strong>Likes</strong> — show only patterns you've liked (requires login)</li>
</ul>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>Filters <strong>do not combine.</strong> Applying one automatically clears the others, so only the condition you just applied is used. For example, if you type a search term and then choose Uri-sal, the search term is cleared and only the Uri-sal filter applies. Arriving at the Collection with no filter set shows <strong>Uri-sal</strong> patterns by default.</span>
</div>

<h2>Likes</h2>
<p>
    Like a pattern by clicking the <i class="bi bi-heart"></i> icon on its card or detail page.
    Turning on the <span class="guide-ui">Likes</span> toggle in the filters collects just the patterns you've liked.
</p>

<h2>Opening in the Studio</h2>
<p>
    Opening a collection drawing in the studio restores its parameters onto the canvas.
    From there you can change anything you like and <strong>save under a new name</strong> to make it your own drawing.
</p>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>Drawings loaded from the Collection never overwrite the original. Be sure to enter a new name when saving so it becomes your own drawing.</span>
</div>

<h2>Sharing a Pattern</h2>
<p>You can share a collection pattern you like with anyone via a link.</p>
<ol class="guide-steps">
    <li>Click the <span class="guide-ui"><i class="bi bi-share"></i></span> share icon on the collection detail page.</li>
    <li>A popup opens with the share link, a <span class="guide-ui">Copy</span> button, and KakaoTalk, Facebook, X and Threads share buttons.</li>
    <li>The link points to the collection pattern page and is viewable by anyone, without logging in.</li>
</ol>

<h2>My Boards</h2>
<p>Save collection drawings to your boards to build your own reference library.</p>
<ol class="guide-steps">
    <li>Click the <span class="guide-ui"><i class="bi bi-collection"></i></span> board icon on a collection <strong>card</strong>. (The detail page doesn't have this button, so save from the list view.)</li>
    <li>Choose a board to save to, or create one with <span class="guide-ui">+ New board</span>.</li>
    <li>Find your boards in the <strong>My Boards</strong> section of the user menu in the top navigation, and on the <strong>Boards</strong> tab of the My Drawings page.</li>
</ol>

<?php include __DIR__ . '/../_foot.php'; ?>
