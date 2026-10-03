<?php
// 영문 본문. 한글 원문은 ../studio-mondrian.php
$guide_current = 'studio-mondrian.php';
$guide_title   = 'Mondrian';
$guide_prev    = ['href' => 'studio-hexagon.php', 'title' => 'Yukmo-sotgeul-sal'];
$guide_next    = ['href' => 'canvas-toolbar.php', 'title' => 'Canvas Toolbar'];
include __DIR__ . '/../_head.php';
?>

<h1><span class="guide-h1-icon"><?= $guideEngineIcons['mondrian'] ?></span>Mondrian</h1>
<p class="guide-lead">A free-form engine that randomly subdivides the Jeongja-sal grid.
    Generate a new layout with one click, then draw or drag lines to refine it.</p>

<div class="guide-screenshot">
    <img src="/src/img/guide/studio-mondrian-en.png" alt="Mondrian studio layout — design sidebar on the left, canvas in the center, estimate, finish and rendering sidebar on the right" loading="lazy">
</div>

<p>
    Like Jeongja-sal, it uses only straight horizontal and vertical slats — but instead of repeating even cells,
    the space is split into <strong>rectangles of different sizes, arranged asymmetrically</strong>.
    A new drawing opens with a random layout already in place, so you can regenerate until you like it, or drag lines to fine-tune it.
</p>

<h2>How It Differs from Jeongja-sal</h2>
<table class="guide-table">
    <thead><tr><th></th><th>Mondrian</th><th>Jeongja-sal</th></tr></thead>
    <tbody>
        <tr><td>Cell layout</td><td>Rectangles of varying sizes (random division)</td><td>Even grid of identical cells</td></tr>
        <tr><td>Cell count</td><td>None — shaped by random generation and line editing</td><td>Set by vertical cells and horizontal slat count</td></tr>
        <tr><td>Drag dividing lines</td><td>Yes</td><td>No</td></tr>
        <tr><td>Auto-fit height</td><td>No</td><td>Yes</td></tr>
        <tr><td>Slat length (parts list)</td><td>Differs per slat, shown as "variable"</td><td>One length based on the grid</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>Mondrian drawings are saved separately from Jeongja-sal. Drawings made in Jeongja-sal do not appear in the Mondrian drawing list, and vice versa.</span>
</div>

<h2>Main Parameters</h2>
<table class="guide-table">
    <thead><tr><th>Item</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td><strong>Door type / panels</strong></td><td>Hinged or sliding, 1–4 or 6 panels. Frame thickness and clearance are applied automatically to calculate the real panel dimensions.</td></tr>
        <tr><td><strong>Frame width / height</strong></td><td>Wall opening dimensions (mm). The outer panel size is calculated with the frame thickness removed.</td></tr>
        <tr><td><strong>Stile / rail thickness</strong></td><td>Outer frame thickness (mm)</td></tr>
        <tr><td><strong>Slat thickness</strong></td><td>Cross-section thickness of the slats (mm)</td></tr>
        <tr><td><strong>Vertical ratio</strong></td><td>1.0–3.0. The cell width-to-height ratio that random generation uses as its guide. Higher values produce more tall, narrow cells.</td></tr>
        <tr><td><strong>Use transom panel</strong></td><td>Adds a transom panel zone at the top when checked; its height is set separately</td></tr>
        <tr><td><strong>Show dimensions / door frame</strong></td><td>Choose whether measured dimensions and the frame outline appear on the canvas</td></tr>
    </tbody>
</table>

<h2>Random Pattern</h2>
<table class="guide-table">
    <thead><tr><th>Button</th><th>Function</th></tr></thead>
    <tbody>
        <tr><td><span class="guide-ui">Generate random</span></td><td>Divides the whole inner area at random to make a new pattern. Each click gives a different layout.</td></tr>
        <tr><td><span class="guide-ui">Reset</span></td><td>Clears the random pattern and returns to an even grid. Only shown while a random pattern exists.</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>Generating again or resetting discards the current pattern. Save as soon as you get one you like.</span>
</div>

<h2>Refining Lines</h2>
<p>You don't have to keep the random layout as-is — you can adjust it by hand.</p>
<table class="guide-table">
    <thead><tr><th>Action</th><th>How</th></tr></thead>
    <tbody>
        <tr><td><strong>Move a dividing line</strong></td><td>Grab a slat on the canvas and drag it; it moves only vertically or horizontally. The neighboring cells resize with it, so no gaps or overlaps appear.</td></tr>
        <tr><td><strong>Add a line</strong></td><td>Press <span class="guide-ui">Add line</span> in the toolbar, then click a start point and an end point. You can start from any point on a slat or the frame, not just intersections, and lines are drawn only vertically or horizontally.</td></tr>
        <tr><td><strong>Move a drawn line</strong></td><td>Lines you drew can be dragged too. Other drawn lines attached to it follow along, and it stays inside the frame.</td></tr>
        <tr><td><strong>Delete a line</strong></td><td>Press <span class="guide-ui">Delete line</span> in the toolbar, then click the slat to remove. Click it again to restore it.</td></tr>
        <tr><td><strong>Reset edits</strong></td><td><span class="guide-ui">Reset edits</span> in the toolbar undoes all added and deleted lines and painted panel colors.</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Dragging dividing lines works only while Add line, Delete line, Pan (hand) and Panel fill modes are all off. If a line won't move, check that no mode is switched on in the toolbar. See <a href="<?= lang_href('/guide/canvas-toolbar') ?>">Canvas Toolbar</a> for details on each button.</span>
</div>

<h2>Production Specification &amp; Parts List</h2>
<p>
    Frame width/height, outer and inner width/height, top/bottom rails, transom height and total door width (including overlap for sliding doors) are calculated automatically, as in the other engines.
    Because cell sizes vary, <strong>items that assume an even grid — cell counts, layout lines, half-lap width and stile groove width — are hidden while a random pattern is active.</strong>
    In the parts list, horizontal and vertical slat lengths also differ per slat, so they are shown as "variable" rather than a single value.
</p>

<h2>Finish &amp; Color</h2>
<p>
    Wood, finish and hardware options, along with frame and slat colors, are the same as Jeongja-sal.
    <strong>Panel fill</strong> works here too — click a cell to fill just that one with color.
</p>

<h2>Example Uses</h2>
<ul>
    <li>Accent partitions for living rooms and cafés</li>
    <li>Irregular changho for contemporary spaces, such as apartment entry doors</li>
    <li>Color-block changho combined with panel fill</li>
</ul>

<?php include __DIR__ . '/../_foot.php'; ?>
