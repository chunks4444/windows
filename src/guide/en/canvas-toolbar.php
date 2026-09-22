<?php
// 영문 본문. 한글 원문은 ../canvas-toolbar.php
$guide_current = 'canvas-toolbar.php';
$guide_title   = 'Canvas Toolbar';
$guide_prev    = ['href' => 'studio-hexagon.php', 'title' => 'Yukmo-sotgeul-sal'];
$guide_next    = ['href' => 'svg-insert.php', 'title' => 'Inserting Motifs & Uploading SVGs'];
include __DIR__ . '/../_head.php';
?>

<h1>Canvas Toolbar</h1>
<p class="guide-lead">
    At the bottom of the canvas sits a <strong>toolbar</strong> gathering the view controls, slat editing,
    motif placement and shape drawing buttons.
    These are shared features, available identically in all six engines (Se-sal, Jeongja-sal, Bit-sal,
    Gyeokja-bit-sal, Semo-sotgeul-sal and Yukmo-sotgeul-sal).
</p>

<h2>View Controls</h2>
<table class="guide-table">
    <thead><tr><th>Icon</th><th>Function</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td><i class="bi bi-zoom-in"></i> Zoom in</td><td>Zooms the canvas in</td><td>Scrolling the wheel up does the same.</td></tr>
        <tr><td><i class="bi bi-zoom-out"></i> Zoom out</td><td>Zooms the canvas out</td><td>Scrolling the wheel down does the same.</td></tr>
        <tr><td><i class="bi bi-hand-index"></i> Pan</td><td>Moves the canvas by dragging</td><td>Click and drag the drawing to move the view.</td></tr>
        <tr><td><i class="bi bi-arrow-counterclockwise"></i> Reset view</td><td>Resets zoom and pan to their defaults</td><td>Returns the view to how it looked when you first opened the canvas.</td></tr>
    </tbody>
</table>

<h2>Slat Editing</h2>
<table class="guide-table">
    <thead><tr><th>Icon</th><th>Function</th><th>How to use</th></tr></thead>
    <tbody>
        <tr><td><i class="bi bi-scissors"></i> Delete slat</td><td>Editing mode for removing slats the algorithm generated</td><td>Click a slat to delete it. Clicking a deleted slat again restores it.</td></tr>
        <tr><td><i class="bi bi-pencil"></i> Add slat</td><td>Editing mode for drawing in new slats that aren't part of the grid</td><td>① Click the starting intersection, ② click the ending intersection, and a slat is created between them.</td></tr>
        <tr><td><i class="bi bi-arrow-clockwise"></i> Reset edits</td><td>Reverts everything changed by deleting and adding slats</td><td>Resets to the default grid the algorithm generated.</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Combining the <strong>delete and add slat modes</strong> lets you remove specific slats from the generated grid and add slats wherever you want — giving you completely free patterns.</span>
</div>

<h2>Motif Placement</h2>
<table class="guide-table">
    <thead><tr><th>Icon</th><th>Function</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td><i class="bi bi-arrows-move"></i> Scale / move / transform</td><td>Mode for adjusting the position, size and rotation of inserted SVG motifs and images</td><td>This mode activates automatically right after you insert a motif; drag the corner handles to change size and rotation.</td></tr>
        <tr><td><i class="bi bi-arrow-repeat"></i> Reset placement</td><td>Returns the motif's position, size and rotation to their initial values</td><td>Only appears when a motif has been inserted.</td></tr>
    </tbody>
</table>

<h2>Shape Drawing</h2>
<table class="guide-table">
    <thead><tr><th>Icon</th><th>Function</th><th>How to use</th></tr></thead>
    <tbody>
        <tr><td><i class="bi bi-cursor"></i> Select</td><td>The default mode for selecting and editing slats and shapes</td><td>Click a slat to change its color or delete it; click a shape to move, resize or rotate it.</td></tr>
        <tr><td><i class="bi bi-circle"></i> Draw circle</td><td>Adds a circle to the canvas</td><td>Click where you want it and the circle is placed.</td></tr>
        <tr><td><i class="bi bi-slash-lg"></i> Draw line</td><td>Adds a straight line to the canvas</td><td>Click the start point, then the end point, and the line is drawn.</td></tr>
        <tr><td><i class="bi bi-square"></i> Draw rectangle</td><td>Adds a rectangle to the canvas</td><td>Click where you want it and the rectangle is placed.</td></tr>
        <tr><td><i class="bi bi-fonts"></i> Add text</td><td>Adds a text label to the canvas</td><td>Click where you want it and a text input appears.</td></tr>
        <tr><td><i class="bi bi-slash-circle"></i> Delete all shapes</td><td>Removes every circle, line, rectangle and text you added</td><td>Does not affect your lattice slat edits.</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>Shapes are drawn on an overlay layer, separate from the lattice itself. They are meant for <strong>annotations and notes</strong> — they are not included in production specification calculations such as dimension labels or the parts list.</span>
</div>

<?php include __DIR__ . '/../_foot.php'; ?>
