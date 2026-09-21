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
    woven shape resembles — an even, gapless grid is the face of this window. Also called man-sal (滿箭),
    it was the second most common form after Se-sal.
    It shares most of its sidebar structure with Se-sal (door settings, dimensions, lattice settings,
    finish, background, export), but uses a <strong>single-ratio even grid</strong> instead of three
    top/middle/bottom zones, and adds a <strong>random pattern</strong> feature that reshuffles the grid.</p>

<h2>How It Differs from Se-sal</h2>
<table class="guide-table">
    <thead><tr><th></th><th>Jeongja-sal</th><th>Se-sal</th></tr></thead>
    <tbody>
        <tr><td>Zoning</td><td>One cell ratio applied across the whole grid</td><td>Split into top/middle/bottom, each with its own cell count and ratio</td></tr>
        <tr><td>Vertical ratio range</td><td>1.0 – 3.0</td><td>1.0 – 5.0</td></tr>
        <tr><td>Random pattern</td><td>Yes (Mondrian-style random division)</td><td>No</td></tr>
        <tr><td>Auto-fit height</td><td>Yes</td><td>No</td></tr>
    </tbody>
</table>

<h2>Main Parameters</h2>
<table class="guide-table">
    <thead><tr><th>Item</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td><strong>Door type / panels</strong></td><td>Hinged or sliding, 1–4 panels. As with Se-sal, frame thickness and clearance are applied automatically to calculate the real panel dimensions.</td></tr>
        <tr><td><strong>Frame width / height</strong></td><td>Wall opening dimensions (400–3,000mm). The outer panel size is calculated with the frame thickness removed.</td></tr>
        <tr><td><strong>Horizontal cells</strong></td><td>Number of horizontal grid divisions</td></tr>
        <tr><td><strong>Vertical ratio</strong></td><td>1.0–3.0. Sets each cell's width-to-height ratio, from which the vertical cell count is derived automatically.</td></tr>
        <tr><td><strong>Auto-fit height</strong></td><td>When checked, the frame height is adjusted automatically so the last grid row meets the bottom rail exactly.</td></tr>
        <tr><td><strong>Stile / rail thickness</strong></td><td>Outer frame thickness (mm)</td></tr>
        <tr><td><strong>Slat thickness</strong></td><td>Cross-section thickness of the lattice slats (mm)</td></tr>
        <tr><td><strong>Use transom panel</strong></td><td>Adds a transom panel zone at the top when checked; its height is set separately</td></tr>
        <tr><td><strong>Show dimensions / door frame</strong></td><td>Choose whether measured dimensions and the frame outline appear on the canvas</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Combining the horizontal cell count with the vertical ratio lets you tune the grid freely — from perfectly square cells to tall, narrow ones.</span>
</div>

<h2>Random Pattern</h2>
<p>
    Unique to the Jeongja-sal engine, this re-divides the even grid at random, Mondrian style.
</p>
<table class="guide-table">
    <thead><tr><th>Button</th><th>Function</th></tr></thead>
    <tbody>
        <tr><td><span class="guide-ui">Generate random</span></td><td>Randomly merges and splits cells from the current grid to create an irregular pattern</td></tr>
        <tr><td><span class="guide-ui">Reset</span></td><td>Clears the random pattern and restores the original even grid</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>Each click produces a different result. Save as soon as you get one you like — previous results are not kept, and reset discards the current one.</span>
</div>

<h2>Production Specification &amp; Parts List</h2>
<p>The same items as Se-sal are calculated and displayed automatically: frame width/height, outer and inner width/height, horizontal cell count, horizontal and vertical layout lines, eye size, half-lap width and stile groove width. The parts list is ordered as horizontal and vertical slats, frame members, the transom panel (if used), and the door frame.</p>

<h2>Finish &amp; Color</h2>
<p>
    Wood, finish and hardware options, along with frame and slat colors, are the same as Se-sal.
    <strong>Panel fill</strong> also works here — click a grid cell to fill that individual panel with color.
    (Panel fill is currently disabled in the Bit-sal, Gyeokja-bit-sal, Semo-sotgeul-sal and Yukmo-sotgeul-sal engines.)
</p>

<h2>Example Uses</h2>
<ul>
    <li>Sliding windows in contemporary hanok</li>
    <li>Partition windows for cafés and commercial spaces</li>
    <li>Irregular window designs made with the random pattern</li>
</ul>

<?php include __DIR__ . '/../_foot.php'; ?>
