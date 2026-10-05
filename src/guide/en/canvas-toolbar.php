<?php
// 영문 본문. 한글 원문은 ../canvas-toolbar.php
$guide_current = 'canvas-toolbar.php';
$guide_title   = 'Canvas Toolbar';
$guide_prev    = ['href' => 'studio-mondrian.php', 'title' => 'Mondrian'];
$guide_next    = ['href' => 'svg-insert.php', 'title' => 'Inserting Motifs & Uploading SVGs'];
include __DIR__ . '/../_head.php';
?>

<h1>Canvas Toolbar</h1>
<p class="guide-lead">
    Two groups of buttons float above the canvas.
    The <strong>top left</strong> holds Select, Pan, Edit lines, Shapes and Place, together with the current door and frame sizes;
    the <strong>top right</strong> holds zoom out, zoom in and Fit.
    All seven engines (Se-sal, Jeongja-sal, Bit-sal, Gyeokja-bit-sal, Semo-sotgeul-sal, Yukmo-sotgeul-sal and Mondrian) share the same toolbar.
</p>

<h2>Top Left — Tools</h2>
<p>
    Each button has its name under the icon, and the mode that is on is shown in <strong>black</strong>.
    Only one mode is on at a time — pressing another button switches the previous one off. Press an active button again to turn it off.
</p>
<table class="guide-table">
    <thead><tr><th>Button</th><th>Function</th><th>How to use</th></tr></thead>
    <tbody>
        <tr><td><i class="bi bi-cursor"></i> Select</td><td>Pick slats, shapes and motifs to edit</td><td>Click a slat and a small bar appears above the canvas to recolor or delete just that slat. Click a shape or motif to get handles for moving, resizing and rotating. The <kbd>Delete</kbd> key also removes what you picked.</td></tr>
        <tr><td><i class="bi bi-hand-index"></i> Pan</td><td>Pan the view</td><td>While on, drag the canvas to move the view.</td></tr>
        <tr><td><i class="bi bi-scissors"></i> Edit lines <i class="bi bi-chevron-down"></i></td><td>Menu for removing or adding slats</td><td>Opens a menu with <strong>Delete line · Add line · Reset edits</strong>. See <a href="#line-edit">Edit lines</a> below.</td></tr>
        <tr><td><i class="bi bi-bounding-box"></i> Shapes <i class="bi bi-chevron-down"></i></td><td>Menu for adding note shapes and text</td><td>Opens a menu with <strong>Circle · Line · Rectangle · Text · Clear all</strong>. See <a href="#shapes">Shapes</a> below.</td></tr>
        <tr><td><i class="bi bi-aspect-ratio"></i> Place</td><td>Fit the door into a background photo</td><td>Handles appear on the door's four corners. Drag each corner onto the door opening in the photo and the door is warped to match the perspective. Using it before AI rendering makes the result far more natural.</td></tr>
        <tr><td><i class="bi bi-arrow-counterclockwise"></i> Reset placement</td><td>Return the corners to where they started</td><td>Appears only after you have moved corners with Place.</td></tr>
    </tbody>
</table>
<p>
    At the right end of the tool group, the <strong>Door</strong> (actual door size) and <strong>Frame</strong> (wall opening) dimensions are shown on two lines,
    so you can check them every time you change a value.
</p>

<h2 id="line-edit">Edit Lines</h2>
<table class="guide-table">
    <thead><tr><th>Menu item</th><th>Function</th><th>How to use</th></tr></thead>
    <tbody>
        <tr><td>Delete line</td><td>Mode for removing automatically drawn slats</td><td>Click a slat to delete it. Click the same spot again to restore it.</td></tr>
        <tr><td>Add line</td><td>Mode for drawing a new slat that isn't in the grid</td><td>① Click a start intersection → ② click an end intersection, and a slat is created between them.</td></tr>
        <tr><td>Reset edits</td><td>Undo all line deletions and additions</td><td>Returns to the grid exactly as it was first drawn.</td></tr>
    </tbody>
</table>
<p>While Delete line or Add line is on, the <strong>Edit lines</strong> button itself also turns black, so you can tell which mode you're in even with the menu closed.</p>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Combine Delete line and Add line to take out particular slats from the generated grid or add slats wherever you like, building a pattern of your own. Edited slats are not reflected in the estimate automatically — after you request a quote, our staff review them to set the final price.</span>
</div>

<h2 id="shapes">Shapes</h2>
<table class="guide-table">
    <thead><tr><th>Menu item</th><th>Function</th><th>How to use</th></tr></thead>
    <tbody>
        <tr><td>Circle</td><td>Add a circle</td><td>Click where you want it.</td></tr>
        <tr><td>Line</td><td>Add a straight line</td><td>Click a start point, then an end point.</td></tr>
        <tr><td>Rectangle</td><td>Add a rectangle</td><td>Click where you want it.</td></tr>
        <tr><td>Text</td><td>Add text</td><td>Click where you want it and a text box appears. Double-click the text later to edit it.</td></tr>
        <tr><td>Clear all</td><td>Remove all shapes, text and inserted motifs at once</td><td>Slat edits are not affected.</td></tr>
    </tbody>
</table>
<p>When a shape is selected, a bar appears above the canvas for changing its <strong>stroke color, fill color, thickness and opacity</strong>.</p>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>Shapes are drawn on a layer separate from the lattice and are meant for <strong>notes and annotations</strong>. They are not included in the estimate or the production specification.</span>
</div>

<h2>Top Right — Zoom</h2>
<table class="guide-table">
    <thead><tr><th>Button</th><th>Function</th></tr></thead>
    <tbody>
        <tr><td><i class="bi bi-dash-lg"></i> / <i class="bi bi-plus-lg"></i></td><td>Zoom out / zoom in. The mouse wheel and a two-finger pinch on a trackpad or touch screen work too.</td></tr>
        <tr><td><strong>100%</strong></td><td>Shows the current zoom level. Clicking it does the same as Fit.</td></tr>
        <tr><td><i class="bi bi-arrow-repeat"></i> Fit</td><td>Returns a zoomed or panned view to the starting view with the whole drawing visible.</td></tr>
    </tbody>
</table>

<?php include __DIR__ . '/../_foot.php'; ?>
