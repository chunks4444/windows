<?php
// 영문 본문. 한글 원문은 ../studio-square.php
$guide_current = 'studio-square.php';
$guide_title   = 'Jeongja-sal';
$guide_prev    = ['href' => 'studio-classic.php', 'title' => 'Se-sal'];
$guide_next    = ['href' => 'studio-cross.php', 'title' => 'Bit-sal'];
include __DIR__ . '/../_head.php';
?>

<h1><span class="guide-h1-icon"><?= $guideEngineIcons['square'] ?></span>Jeongja-sal</h1>
<p class="guide-lead">This engine recreates Jeongja-sal (井字箭), where both vertical and horizontal slats
    fill the frame completely to form a grid. The name comes from 井, the character for "well," which the
    woven shape resembles — an even, gapless grid is the face of this changho. Also called man-sal (滿箭),
    it was the second most common form after Se-sal.
    It shares most of its screen layout with Se-sal (the Drawing, Door, Motifs, Finish, Rendering, Estimate,
    Specs and Export tabs), but uses a <strong>single-ratio even grid</strong> instead of three
    top/middle/bottom zones.</p>

<div class="guide-screenshot">
    <img src="/src/img/guide/studio-square-en.png" alt="Jeongja-sal studio layout — menu tabs on the left with the door settings panel open, canvas in the center, toolbar above the canvas" loading="lazy">
</div>

<h2>How It Differs from Se-sal</h2>
<table class="guide-table">
    <thead><tr><th></th><th>Jeongja-sal</th><th>Se-sal</th></tr></thead>
    <tbody>
        <tr><td>Zoning</td><td>One cell ratio applied across the whole grid</td><td>Split into top/middle/bottom, each with its own cell count and ratio</td></tr>
        <tr><td>Vertical ratio range</td><td>1.0 – 3.0</td><td>1.0 – 5.0</td></tr>
        <tr><td>Auto-fit vertically</td><td>Yes</td><td>No</td></tr>
    </tbody>
</table>

<h2>Door Tab — Main Parameters</h2>
<table class="guide-table">
    <thead><tr><th>Item</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td><strong>Hinged · Sliding / panels</strong></td><td>Hinged 1–2 panels, sliding 1–4 or 6 panels. As with Se-sal, frame thickness and clearance are applied automatically to calculate the real panel dimensions.</td></tr>
        <tr><td><strong>Frame width / height</strong></td><td>Wall opening dimensions (width 100–10,000mm, height 400–3,000mm). The outer panel size is calculated with the frame thickness removed.</td></tr>
        <tr><td><strong>Vertical cells</strong></td><td>2–30. The number of cells (columns) dividing the panel width — the vertical slat count is cells − 1</td></tr>
        <tr><td><strong>Set horizontal slat count manually</strong></td><td>When checked, you set the horizontal slats directly with <strong>Horizontal slat count</strong> (1–60), and Vertical ratio and Auto-fit vertically are hidden.</td></tr>
        <tr><td><strong>Vertical ratio</strong></td><td>1.0–3.0. Sets each cell's width-to-height ratio, from which the horizontal slat count is derived automatically (when Set horizontal slat count manually is off).</td></tr>
        <tr><td><strong>Auto-fit vertically</strong></td><td>When checked, the frame height is adjusted automatically so the last grid row meets the bottom rail exactly.</td></tr>
        <tr><td><strong>Left/right stile · top/bottom rail thickness</strong></td><td>Outer frame thickness (mm)</td></tr>
        <tr><td><strong>Slat thickness</strong></td><td>Cross-section thickness of the lattice slats (mm)</td></tr>
        <tr><td><strong>Use transom panel</strong></td><td>Adds a transom panel zone at the top when checked; its height is set separately</td></tr>
        <tr><td><strong>Show dimensions / Show door frame</strong></td><td>Choose whether measured dimensions and the frame outline appear on the canvas</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Combining the vertical cell count with the vertical ratio (or the horizontal slat count) lets you tune the grid freely — from perfectly square cells to tall, narrow ones.</span>
</div>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>The random pattern, which divides the grid at random, has moved to its own <a href="<?= lang_href('/guide/studio-mondrian') ?>">Mondrian</a> engine.</span>
</div>

<h2>Specs Tab — Production Specification &amp; Member List</h2>
<p>The same items as Se-sal are calculated and displayed automatically: frame width and height, outer width and height, inner width and height, horizontal and vertical cells, horizontal and vertical ink lines, half-lap width, and vertical and horizontal stile groove widths. The member list is ordered as horizontal and vertical slats, frame members, the transom panel (if used), and the door frame.</p>

<h2>Finish Tab</h2>
<p>Choosing the wood, hardware and colors per <strong>part to colour</strong> (frame, stile &amp; rail, slat, face) works the same as Se-sal. Pick <strong>Face</strong> as the part and you can click cells in the drawing to fill just those cells (Paint face color).</p>

<h2>Example Uses</h2>
<ul>
    <li>Sliding changho in contemporary hanok</li>
    <li>Partition changho for cafés and commercial spaces</li>
</ul>

<?php include __DIR__ . '/../_foot.php'; ?>
