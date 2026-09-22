<?php
// 영문 본문. 한글 원문은 ../studio-hexagon.php
$guide_current = 'studio-hexagon.php';
$guide_title   = 'Yukmo-sotgeul-sal';
$guide_prev    = ['href' => 'studio-triangle.php', 'title' => 'Semo-sotgeul-sal'];
$guide_next    = ['href' => 'canvas-toolbar.php', 'title' => 'Canvas Toolbar'];
include __DIR__ . '/../_head.php';
?>

<h1><span class="guide-h1-icon"><?= $guideEngineIcons['hexagon'] ?></span>Yukmo-sotgeul-sal</h1>
<p class="guide-lead">This engine recreates Yukmo-sotgeul-sal, which uses the same three-directional slats as
    Semo-sotgeul-sal but offsets the crossing points instead of gathering them at a single point, opening up
    hexagons. It is also called Eogeum-yukmo.
    <em>Sotgeul</em> means "rising," named for the way the slats overlap and rise at each crossing point,
    which gives the weave its layered quality.
    The honeycomb hexagons the slats form are closer to a circle than a square, so — even with the same
    weaving technique — the result feels round and generous rather than taut like the triangular pattern.</p>

<h2>How It Differs from Semo-sotgeul-sal</h2>
<table class="guide-table">
    <thead><tr><th></th><th>Semo-sotgeul-sal</th><th>Yukmo-sotgeul-sal</th></tr></thead>
    <tbody>
        <tr><td>Slat directions</td><td>Three directions (vertical slat plus two diagonals) meeting at one point</td><td>The same three directions, but the vertical slats are broken between crossings so hexagons open up</td></tr>
        <tr><td>Cell shape</td><td>Equilateral triangle</td><td>Hexagon</td></tr>
        <tr><td>Horizontal cell steps</td><td>Even numbers only</td><td>Odd numbers only</td></tr>
        <tr><td>Grid math</td><td>The same equilateral triangle tessellation</td><td>The same equilateral triangle tessellation (only the omitted lines differ)</td></tr>
    </tbody>
</table>

<h2>Main Parameters</h2>
<table class="guide-table">
    <thead><tr><th>Item</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td><strong>Door type / panels</strong></td><td>Hinged or sliding, 1–4 panels</td></tr>
        <tr><td><strong>Frame width / height</strong></td><td>Wall opening dimensions (400–3,000mm). The outer panel size is calculated with the frame thickness removed.</td></tr>
        <tr><td><strong>Horizontal cells</strong></td><td>1–29, adjustable in <strong>odd steps only</strong> (default 3). This and the slat thickness set the hexagon size.</td></tr>
        <tr><td><strong>Vertical pattern orientation</strong></td><td>On by default. On gives pointy-top hexagons, off gives flat-top ones.</td></tr>
        <tr><td><strong>Auto-fit height</strong></td><td>When checked, the frame height is adjusted so the last row meets the bottom rail exactly</td></tr>
        <tr><td><strong>Stile / rail thickness</strong></td><td>Outer frame thickness (mm)</td></tr>
        <tr><td><strong>Slat thickness</strong></td><td>Width of each slat (mm)</td></tr>
        <tr><td><strong>Use transom panel</strong></td><td>Adds a transom panel zone at the top when checked</td></tr>
    </tbody>
</table>

<h2>Production Specification</h2>
<table class="guide-table">
    <thead><tr><th>Item</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td>Frame / outer / inner width and height</td><td>Automatically calculated measured dimensions</td></tr>
        <tr><td>Edge spacing</td><td>Spacing between the hexagons' edges</td></tr>
        <tr><td>Diagonal layout line</td><td>Reference spacing for the diagonal slats</td></tr>
        <tr><td>Vertical stile groove spacing</td><td>Spacing between the grooves cut into the vertical frame members</td></tr>
        <tr><td>Half-lap width / vertical and horizontal groove width</td><td>Calculated the same way as Semo-sotgeul-sal</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>The other five engines show a "total door width" card in the production specification, but Yukmo-sotgeul-sal does not. If you need the overall width, use the outer width value.</span>
</div>

<h2>Parts List</h2>
<p>The same structure as Semo-sotgeul-sal — frame members → directional members (names vary with the vertical pattern orientation) → diagonal slats (two directions) → transom panel (if used) → door frame.</p>

<h2>Finish &amp; Color</h2>
<p>Wood, finish, hardware and the frame and slat colors are the same as Se-sal. <strong>Panel fill is currently disabled.</strong></p>

<h2>Example Uses</h2>
<ul>
    <li>Traditional hanok hexagonal lattice changho</li>
    <li>Lattice patterns for temple halls</li>
    <li>Partitions for upscale Korean restaurants</li>
</ul>

<?php include __DIR__ . '/../_foot.php'; ?>
