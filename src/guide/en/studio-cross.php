<?php
// 영문 본문. 한글 원문은 ../studio-cross.php
$guide_current = 'studio-cross.php';
$guide_title   = 'Bit-sal';
$guide_prev    = ['href' => 'studio-square.php', 'title' => 'Jeongja-sal'];
$guide_next    = ['href' => 'studio-diamond.php', 'title' => 'Gyeokja-bit-sal'];
include __DIR__ . '/../_head.php';
?>

<h1><span class="guide-h1-icon"><?= $guideEngineIcons['cross'] ?></span>Bit-sal</h1>
<p class="guide-lead">This engine recreates Bit-sal, where the slats are tilted 45° and woven
    diagonally inside the frame. The name comes from <em>bit</em>, "slanted," after the way the slats are
    set at an angle — the diamond grid formed by those crossing slats is the face of this changho.
    You set only the horizontal cell count; the vertical count is calculated automatically so the cells
    stay perfectly square, and there is no separate setting for the angle.</p>

<h2>Characteristics</h2>
<ul>
    <li>Cells are always fixed as squares, with slats laid along their diagonals to create the 45° pattern.</li>
    <li><strong>You cannot set the vertical cell count.</strong> It is derived automatically from the square cell size determined by the horizontal cell count and slat thickness.</li>
    <li>Clipping keeps the slats from protruding outside the frame.</li>
</ul>

<h2>Main Parameters</h2>
<table class="guide-table">
    <thead><tr><th>Item</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td><strong>Door type / panels</strong></td><td>Hinged or sliding, 1–4 panels</td></tr>
        <tr><td><strong>Frame width / height</strong></td><td>Wall opening dimensions (400–3,000mm). The outer panel size is calculated with the frame thickness removed.</td></tr>
        <tr><td><strong>Horizontal cells</strong></td><td>2–30. The number of square cells across — this and the slat thickness set the cell size, and the vertical count follows automatically.</td></tr>
        <tr><td><strong>Auto-fit height</strong></td><td>When checked, the frame height is adjusted so the last row meets the bottom rail exactly</td></tr>
        <tr><td><strong>Stile / rail thickness</strong></td><td>Outer frame thickness (mm)</td></tr>
        <tr><td><strong>Slat thickness</strong></td><td>Width of each slat (mm). It also determines the cell size (one side of the square).</td></tr>
        <tr><td><strong>Use transom panel</strong></td><td>Adds a transom panel zone at the top when checked</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>Bit-sal has no zoning settings like the "vertical ratio" or "top/middle/bottom layout" found in Jeongja-sal and Se-sal. To change the grid density, adjust the horizontal cell count or the slat thickness.</span>
</div>

<h2>Production Specification</h2>
<table class="guide-table">
    <thead><tr><th>Item</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td>Frame / outer / inner width and height</td><td>Automatically calculated measured dimensions</td></tr>
        <tr><td>Horizontal / vertical layout lines</td><td>Spacing of the square cells, horizontally and vertically</td></tr>
        <tr><td>Slat layout line</td><td>Distance between diagonal intersections</td></tr>
        <tr><td>Half-lap width</td><td>Calculated equal to the slat thickness</td></tr>
        <tr><td>Stile groove width</td><td>Slat thickness × (1 + √2) — the width of the groove the 45° diagonal slats cut into the frame</td></tr>
    </tbody>
</table>

<h2>Parts List</h2>
<p>Instead of horizontal and vertical slat groups, parts are listed as a <strong>diagonal slat</strong> group. Slats of equal length are bundled by each diagonal direction (↘ and ↗) and listed as length × quantity. Frame members, the transom panel (if used) and the door frame follow below.</p>

<h2>Finish &amp; Color</h2>
<p>Wood, finish, hardware and the frame and slat colors are the same as Se-sal. Note that <strong>panel fill is currently disabled</strong>, so individual cells cannot be filled with color.</p>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Layering Bit-sal with Jeongja-sal produces the more complex Gyeokja-bit-sal effect. See the <a href="<?= lang_href('/guide/studio-diamond') ?>">Gyeokja-bit-sal</a> page for details.</span>
</div>

<h2>Example Uses</h2>
<ul>
    <li>Lattice patterns for hanok lantern screens (bangdeung)</li>
    <li>Contemporary interior screen partitions</li>
    <li>Railing patterns for traditional pavilions</li>
</ul>

<?php include __DIR__ . '/../_foot.php'; ?>
