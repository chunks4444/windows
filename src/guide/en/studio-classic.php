<?php
// 영문 본문. 한글 원문은 ../studio-classic.php
// 한글 본문은 리드 문단을 studio_card_description()(DB studio_cards 값)에서 가져오지만,
// DB 값이 한글이라 영문 페이지에서는 아래 문구를 직접 쓴다.
$guide_current = 'studio-classic.php';
$guide_title   = 'Se-sal';
$guide_prev    = ['href' => 'getting-started.php', 'title' => 'Getting Started'];
$guide_next    = ['href' => 'studio-square.php', 'title' => 'Jeongja-sal'];
include __DIR__ . '/../_head.php';
?>

<h1><span class="guide-h1-icon"><?= $guideEngineIcons['classic'] ?></span>Se-sal</h1>
<p class="guide-lead">This engine recreates Se-sal (細箭), a changho whose frame is filled with closely spaced
    vertical slats, crossed by only three or four horizontal slats at the top, middle and bottom.
    The name comes from 細 ("fine"), pointing to the slats' slenderness — the fine, straight grain
    created by those dense vertical slats is the face of this changho. Also called ttisal-chang,
    it was the most widely used lattice changho form of the Joseon period.
    The studio is laid out in three panels — <strong>the design sidebar on the left, the canvas,
    and the background and export sidebar on the right</strong> — and every parameter change appears
    on the canvas immediately.</p>

<h2>Screen Layout</h2>

<!-- UI 스크린샷 -->
<div class="guide-screenshot">
    <img src="/src/img/guide/studio-classic.png" alt="Se-sal studio layout — design sidebar on the left, canvas in the center, estimate, finish and rendering sidebar on the right" loading="lazy">
</div>

<h2>① Left Sidebar — Design Parameters</h2>

<h3>Door settings</h3>
<table class="guide-table">
    <thead><tr><th>Item</th><th>Options</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td>Door type</td><td>Hinged / Sliding</td><td>Hinged: hinge construction. Sliding: sliding rail construction</td></tr>
        <tr><td>Number of panels</td><td>1 – 4</td><td>The total width is divided evenly as you add panels</td></tr>
    </tbody>
</table>

<h3>Door dimensions</h3>
<table class="guide-table">
    <thead><tr><th>Item</th><th>Range</th><th>Unit</th></tr></thead>
    <tbody>
        <tr><td>Frame width</td><td>400 – 3,000</td><td>mm</td></tr>
        <tr><td>Frame height</td><td>400 – 3,000</td><td>mm</td></tr>
    </tbody>
</table>
<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>The values you enter here are the wall opening (outer frame) dimensions. The actual door panel size is calculated by automatically subtracting the frame thickness and clearance (set by the administrator), and sliding doors are further adjusted for the width where panels overlap. You can check the resulting measured dimensions under <strong>Production Specification</strong> below.</span>
</div>

<h3>Lattice settings</h3>
<table class="guide-table">
    <thead><tr><th>Item</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td>Horizontal cells</td><td>Number of horizontal divisions (slat count = cells − 1)</td></tr>
        <tr><td>Left/right stile thickness</td><td>Thickness of the left and right outer frame members (mm)</td></tr>
        <tr><td>Top/bottom rail thickness</td><td>Thickness of the top and bottom outer frame members (mm)</td></tr>
        <tr><td>Slat thickness</td><td>Cross-section thickness of the lattice slats (mm)</td></tr>
        <tr><td>Horizontal slat layout (top/middle/bottom)</td><td>Set the horizontal cell count of each of the three zones independently, e.g. 3/5/3</td></tr>
        <tr><td>Vertical ratio</td><td>Height ratio between the top, middle and bottom zones</td></tr>
        <tr><td>Use transom panel</td><td>Adds a transom panel zone at the top when checked. Its height is set separately</td></tr>
        <tr><td>Show dimensions</td><td>Displays each member's measured dimensions on the canvas when checked</td></tr>
        <tr><td>Show door frame</td><td>Displays the door frame (wall opening) outline on the canvas when checked</td></tr>
    </tbody>
</table>

<h3>Production specification</h3>
<p>Measured dimensions calculated automatically from the parameters you enter.</p>
<table class="guide-table">
    <thead><tr><th>Item</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td>Frame width / height</td><td>Wall opening dimensions (exactly as entered)</td></tr>
        <tr><td>Outer width / height</td><td>Full door panel size (including stiles and rails), calculated with frame thickness and clearance removed</td></tr>
        <tr><td>Inner width / height</td><td>Effective inner dimensions, excluding stiles and rails</td></tr>
        <tr><td>Horizontal / vertical cells</td><td>Lattice division counts</td></tr>
        <tr><td>Eye size</td><td>Inner size of a single lattice cell</td></tr>
        <tr><td>Slat list</td><td>Lengths × quantities for vertical slats, horizontal slats and frame members</td></tr>
    </tbody>
</table>

<h2>② Canvas — Preview &amp; Controls</h2>

<h3>Drawing management bar (top)</h3>
<table class="guide-table">
    <thead><tr><th>Button</th><th>Function</th></tr></thead>
    <tbody>
        <tr><td><span class="guide-ui">Enter drawing name…</span></td><td>Type a name for the drawing. It is stored in the cloud under this name when saved.</td></tr>
        <tr><td><span class="guide-ui">New drawing</span></td><td>Resets the current drawing and starts a new one</td></tr>
        <tr><td><span class="guide-ui">Drawings</span></td><td>Opens the list of saved drawings so you can switch to another one</td></tr>
        <tr><td><span class="guide-ui">Version ▾</span></td><td>Save history for the current drawing. Click to restore an earlier version from the dropdown</td></tr>
    </tbody>
</table>

<h3>Canvas toolbar (bottom)</h3>
<p>
    The bottom of the canvas gathers the view controls — zoom and pan — along with the slat
    delete/add editing modes, motif placement and shape drawing buttons.
    These are shared across all six engines; see the
    <a href="<?= lang_href('/guide/canvas-toolbar') ?>">Canvas Toolbar</a> page for the full list and how to use them.
</p>

<h2>③ Right Sidebar — Finish · Background · Saving</h2>

<h3>Save / order</h3>
<table class="guide-table">
    <thead><tr><th>Button</th><th>Function</th></tr></thead>
    <tbody>
        <tr><td><span class="guide-ui">💾 Save</span></td><td>Saves the current drawing to the cloud. Saving under the same name adds a version.</td></tr>
        <tr><td><span class="guide-ui">Order</span></td><td>Goes to the production inquiry page based on the drawing you designed</td></tr>
    </tbody>
</table>

<h3>Finish settings</h3>
<table class="guide-table">
    <thead><tr><th>Item</th><th>Options</th></tr></thead>
    <tbody>
        <tr><td>Wood</td><td>Red pine / Pine / Oak</td></tr>
        <tr><td>Inner surface</td><td>Hanji paper / Glass / Acrylic</td></tr>
        <tr><td>Frame color</td><td>Choose from the color palette</td></tr>
        <tr><td>Slat color</td><td>Choose from the color palette</td></tr>
        <tr><td>Panel color</td><td>Set directly with the HEX color picker. Use <span class="guide-ui">Fill</span> to color specific cells</td></tr>
    </tbody>
</table>

<h3>Background photo &amp; AI rendering</h3>
<p>
    Upload a photo of the site, composite it with your drawing, and render it with AI.
    See the <a href="<?= lang_href('/guide/render') ?>">Using AI Rendering</a> page for details.
</p>

<h3>Export</h3>
<table class="guide-table">
    <thead><tr><th>Button</th><th>Output</th></tr></thead>
    <tbody>
        <tr><td><span class="guide-ui">PNG</span></td><td>Downloads the current canvas view as a transparent-background PNG</td></tr>
        <tr><td><span class="guide-ui">PDF</span></td><td>Print-optimized PDF, including dimension annotations</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>PNG export does not include the background photo — only the drawing (the lattice layer) is output, on a transparent background. AI rendering results are downloaded separately from the rendering popup.</span>
</div>

<?php include __DIR__ . '/../_foot.php'; ?>
