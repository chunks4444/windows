<?php
// 영문 본문. 한글 원문은 ../studio-triangle.php
$guide_current = 'studio-triangle.php';
$guide_title   = 'Semo-sotgeul-sal';
$guide_prev    = ['href' => 'studio-diamond.php', 'title' => 'Gyeokja-bit-sal'];
$guide_next    = ['href' => 'studio-hexagon.php', 'title' => 'Yukmo-sotgeul-sal'];
include __DIR__ . '/../_head.php';
?>

<h1><span class="guide-h1-icon"><?= $guideEngineIcons['triangle'] ?></span>Semo-sotgeul-sal</h1>
<p class="guide-lead">This engine recreates Semo-sotgeul-sal, where a vertical slat and two diagonal slats
    meet at a single point from three directions. <em>Sotgeul</em> means "rising," named for the way the slats
    overlap and rise at each crossing point, giving the weave a layered, three-dimensional feel.
    Because the crossing slats repeat as equilateral triangles across the whole surface, it creates a taut,
    tense impression — quite different from the round, generous feel of the hexagonal pattern.
    The vertical cell count is calculated automatically so that every cell forms an equilateral triangle,
    and cannot be set manually.</p>

<h2>Slat Composition</h2>
<table class="guide-table">
    <thead><tr><th>Slat direction</th><th>Angle</th></tr></thead>
    <tbody>
        <tr><td>Vertical slat</td><td>0° (vertical)</td></tr>
        <tr><td>Right diagonal slat</td><td>60°</td></tr>
        <tr><td>Left diagonal slat</td><td>120°</td></tr>
    </tbody>
</table>

<h2>Main Parameters</h2>
<table class="guide-table">
    <thead><tr><th>Item</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td><strong>Door type / panels</strong></td><td>Hinged or sliding, 1–4 panels</td></tr>
        <tr><td><strong>Frame width / height</strong></td><td>Wall opening dimensions (400–3,000mm). The outer panel size is calculated with the frame thickness removed.</td></tr>
        <tr><td><strong>Horizontal cells</strong></td><td>2–30, adjustable in <strong>even steps only</strong> (default 4). This and the slat thickness set the triangle size, and the vertical count is derived automatically.</td></tr>
        <tr><td><strong>Vertical pattern orientation</strong></td><td>On by default. Rotates the entire triangular grid 90°. The directional part names in the parts list change depending on whether this is on or off.</td></tr>
        <tr><td><strong>Auto-fit height</strong></td><td>When checked, the frame height is adjusted so the last row meets the bottom rail exactly</td></tr>
        <tr><td><strong>Stile / rail thickness</strong></td><td>Outer frame thickness (mm)</td></tr>
        <tr><td><strong>Slat thickness</strong></td><td>Width of each slat (mm). It also determines the equilateral triangle cell size.</td></tr>
        <tr><td><strong>Use transom panel</strong></td><td>Adds a transom panel zone at the top when checked</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>There are no separate settings for vertical cell count or vertical ratio. To change the triangle size, adjust the horizontal cell count or the slat thickness.</span>
</div>

<h2>Production Specification</h2>
<table class="guide-table">
    <thead><tr><th>Item</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td>Frame / outer / inner width and height</td><td>Automatically calculated measured dimensions</td></tr>
        <tr><td>Vertical layout line</td><td>Spacing between triangle intersections</td></tr>
        <tr><td>Half-lap width</td><td>Width of the half-lap joint where slats cross</td></tr>
        <tr><td>Vertical / horizontal stile groove width</td><td>Width of the groove the slats cut into the frame</td></tr>
    </tbody>
</table>

<h2>Parts List</h2>
<p>Ordered as frame members → <strong>horizontal members</strong> (whose name changes with the vertical pattern orientation setting) → <strong>diagonal slats</strong> (the 60° and 120° directions) → transom panel (if used) → door frame.</p>

<h2>Finish &amp; Color</h2>
<p>Wood, finish, hardware and the frame and slat colors are the same as Se-sal. <strong>Panel fill is currently disabled.</strong></p>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Omitting one direction of slats from Semo-sotgeul-sal produces the <strong>Yukmo-sotgeul-sal</strong> pattern. The two engines share the same grid math and differ only in the direction of the lines drawn. Compare them on the <a href="<?= lang_href('/guide/studio-hexagon') ?>">Yukmo-sotgeul-sal</a> page.</span>
</div>

<h2>Example Uses</h2>
<ul>
    <li>Ceiling lattice patterns in traditional pavilions</li>
    <li>Facade screens in contemporary architecture</li>
    <li>Interior ceiling light boxes</li>
</ul>

<?php include __DIR__ . '/../_foot.php'; ?>
