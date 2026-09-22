<?php
// 영문 본문. 한글 원문은 ../studio-diamond.php
$guide_current = 'studio-diamond.php';
$guide_title   = 'Gyeokja-bit-sal';
$guide_prev    = ['href' => 'studio-cross.php', 'title' => 'Bit-sal'];
$guide_next    = ['href' => 'studio-triangle.php', 'title' => 'Semo-sotgeul-sal'];
include __DIR__ . '/../_head.php';
?>

<h1><span class="guide-h1-icon"><?= $guideEngineIcons['diamond'] ?></span>Gyeokja-bit-sal</h1>
<p class="guide-lead">This engine recreates Gyeokja-bit-sal, in which 45° diagonal slats are layered over a
    vertical-and-horizontal grid. The 井-shaped weave and the diagonal weave meet on one surface, so each
    grid cell is divided again into four small triangles.
    As in the Bit-sal engine, cells are always fixed as squares, with vertical, horizontal and diagonal
    slats all drawn over them.</p>

<h2>Slat Composition</h2>
<table class="guide-table">
    <thead><tr><th>Slat direction</th><th>Angle</th><th>Role</th></tr></thead>
    <tbody>
        <tr><td>Vertical · horizontal slats</td><td>0° / 90°</td><td>The same orthogonal base framework as Jeongja-sal</td></tr>
        <tr><td>Diagonal slats A · B</td><td>45° / 135°</td><td>Diagonal slats layered along the square cells' diagonals</td></tr>
    </tbody>
</table>

<h2>Main Parameters</h2>
<table class="guide-table">
    <thead><tr><th>Item</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td><strong>Door type / panels</strong></td><td>Hinged or sliding, 1–4 panels</td></tr>
        <tr><td><strong>Frame width / height</strong></td><td>Wall opening dimensions (400–3,000mm). The outer panel size is calculated with the frame thickness removed.</td></tr>
        <tr><td><strong>Horizontal cells</strong></td><td>2–30. As with Bit-sal, this sets the number of square cells across; the vertical count is derived automatically.</td></tr>
        <tr><td><strong>Auto-fit height</strong></td><td>When checked, the frame height is adjusted so the last row meets the bottom rail exactly</td></tr>
        <tr><td><strong>Stile / rail thickness</strong></td><td>Outer frame thickness (mm)</td></tr>
        <tr><td><strong>Slat thickness</strong></td><td>Base thickness for all slats, orthogonal and diagonal alike (mm)</td></tr>
        <tr><td><strong>Use transom panel</strong></td><td>Adds a transom panel zone at the top when checked</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>Gyeokja-bit-sal has no zoning settings like Jeongja-sal's "vertical ratio" or Se-sal's "top/middle/bottom layout." Diagonal density is not a separate setting — it follows from the horizontal cell count and slat thickness.</span>
</div>

<h2>Production Specification</h2>
<table class="guide-table">
    <thead><tr><th>Item</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td>Frame / outer / inner width and height</td><td>Automatically calculated measured dimensions</td></tr>
        <tr><td>Diagonal spacing</td><td>Spacing between the diagonal slats</td></tr>
        <tr><td>Slat layout line</td><td>Distance between orthogonal and diagonal slat intersections</td></tr>
        <tr><td>Stile groove width</td><td>Groove width, calculated as a single value rather than separately for sides and top/bottom</td></tr>
    </tbody>
</table>

<h2>Parts List</h2>
<p>Ordered as frame members → <strong>horizontal and vertical slats</strong> → <strong>diagonal slats</strong> → transom panel (if used) → door frame. That is one group more than Bit-sal, since orthogonal and diagonal slats are each tallied separately.</p>

<h2>Finish &amp; Color</h2>
<p>Wood, finish, hardware and the frame and slat colors are the same as Se-sal. <strong>Panel fill is currently disabled.</strong></p>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>Pushing the horizontal cell count too high packs the orthogonal and diagonal slats densely together, which can make the piece difficult to actually build. Export a PDF and check the measurements.</span>
</div>

<h2>Example Uses</h2>
<ul>
    <li>Variations on the aja-sal (亞字窓) patterns of palaces and temples</li>
    <li>Changho for high-end hanok guesthouses</li>
    <li>Partitions for traditional craft exhibition halls</li>
</ul>

<?php include __DIR__ . '/../_foot.php'; ?>
