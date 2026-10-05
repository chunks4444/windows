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
    it was the most widely used lattice changho form of the Joseon period.</p>

<h2>Screen Layout</h2>

<!-- UI 스크린샷 -->
<div class="guide-screenshot">
    <img src="/src/img/guide/studio-classic-en.png" alt="Se-sal studio layout — menu tabs on the left with the door settings panel open, canvas in the center, toolbar above the canvas" loading="lazy">
</div>

<p>
    The studio is made up of <strong>menu tabs on the left, a tab panel, and the canvas</strong>.
    Click a tab in the menu to open its panel beside it; click the same tab again, or the
    <span class="guide-ui"><i class="bi bi-chevron-left"></i></span> button on the panel's right edge, to collapse the panel and widen the canvas.
    Every change you make appears on the canvas immediately.
</p>

<table class="guide-table">
    <thead><tr><th>Tab</th><th>What it does</th></tr></thead>
    <tbody>
        <tr><td><i class="bi bi-grid-3x3-gap"></i> Collection</td><td>Search and open collection drawings made with this engine</td></tr>
        <tr><td><i class="bi bi-file-earmark"></i> Drawing</td><td>Drawing name, save, new drawing, share, versions, and your drawing list</td></tr>
        <tr><td><i class="bi bi-door-closed"></i> Door</td><td>Door type and panel count, frame dimensions, lattice settings</td></tr>
        <tr><td><i class="bi bi-flower1"></i> Motifs</td><td>Motif library and SVG upload</td></tr>
        <tr><td><i class="bi bi-palette"></i> Finish</td><td>Wood, hardware, and finish colors per part</td></tr>
        <tr><td><i class="bi bi-stars"></i> Rendering</td><td>Room photos, AI rendering and saved results</td></tr>
        <tr><td><i class="bi bi-receipt"></i> Estimate</td><td>Estimated price and lead time, quote request</td></tr>
        <tr><td><i class="bi bi-rulers"></i> Specs</td><td>Production dimensions, ink lines, groove widths, member list</td></tr>
        <tr><td><i class="bi bi-box-arrow-down"></i> Export</td><td>Save as PDF · PNG · DXF</td></tr>
    </tbody>
</table>

<h2>Door Tab — Design Parameters</h2>

<h3>Door type · panels</h3>
<table class="guide-table">
    <thead><tr><th>Item</th><th>Options</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td>Door type</td><td>Hinged / Sliding</td><td>Hinged: hinge construction. Sliding: sliding rail construction</td></tr>
        <tr><td>Number of panels</td><td>Hinged 1–2 · Sliding 1–4 or 6</td><td>The total width is divided evenly as you add panels</td></tr>
    </tbody>
</table>

<h3>Frame dimensions</h3>
<table class="guide-table">
    <thead><tr><th>Item</th><th>Range</th><th>Unit</th></tr></thead>
    <tbody>
        <tr><td>Frame width</td><td>400 – 3,000</td><td>mm</td></tr>
        <tr><td>Frame height</td><td>400 – 3,000</td><td>mm</td></tr>
    </tbody>
</table>
<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>The values you enter here are the wall opening (outer frame) dimensions. The actual door panel size is calculated by automatically subtracting the frame thickness and clearance, and sliding doors are further adjusted for the width where panels overlap. The resulting door and frame sizes are always shown at the right end of the toolbar above the canvas, and the full figures are in the <strong>Specs</strong> tab.</span>
</div>

<h3>Lattice settings</h3>
<table class="guide-table">
    <thead><tr><th>Item</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td>Horizontal cells</td><td>Number of horizontal divisions (slat count = cells − 1)</td></tr>
        <tr><td>Left/right stile thickness</td><td>Thickness of the left and right outer frame members (mm)</td></tr>
        <tr><td>Top/bottom rail thickness</td><td>Thickness of the top and bottom outer frame members (mm)</td></tr>
        <tr><td>Slat thickness</td><td>Cross-section thickness of the lattice slats (mm)</td></tr>
        <tr><td>Horizontal slats (top/middle/bottom)</td><td>Set the number of horizontal slats in each of the three zones independently, e.g. 3/5/3</td></tr>
        <tr><td>Vertical ratio</td><td>Height ratio between the top, middle and bottom zones</td></tr>
        <tr><td>Use transom panel</td><td>Adds a transom panel zone at the top when checked. Its height is set separately</td></tr>
        <tr><td>Show dimensions</td><td>Displays each member's measured dimensions on the canvas when checked</td></tr>
        <tr><td>Show door frame</td><td>Displays the door frame (wall opening) outline on the canvas when checked</td></tr>
    </tbody>
</table>

<h2>Canvas — Preview &amp; Controls</h2>
<p>
    Above the canvas, the left side holds the Select, Pan, Edit lines, Shapes and Place buttons along with the door and frame sizes,
    and the right side holds zoom out, zoom in and Fit. These are shared across all seven engines; see the
    <a href="<?= lang_href('/guide/canvas-toolbar') ?>">Canvas Toolbar</a> page for the full list and how to use them.
</p>

<h2>Drawing Tab — Save · Share · Versions</h2>
<table class="guide-table">
    <thead><tr><th>Item</th><th>Function</th></tr></thead>
    <tbody>
        <tr><td>Drawing name</td><td>Type a name for the drawing. It is stored in the cloud under this name when saved.</td></tr>
        <tr><td><span class="guide-ui">Save</span></td><td>Saves the current drawing. Saving under the same name adds a new version.</td></tr>
        <tr><td><span class="guide-ui">New drawing</span></td><td>Clears the current drawing and starts a new one</td></tr>
        <tr><td><span class="guide-ui">Share</span></td><td>Creates or turns off a share link for a saved drawing</td></tr>
        <tr><td>Versions of this drawing</td><td>Open an earlier version from the list of saved versions</td></tr>
        <tr><td>My drawings</td><td>Thumbnails of your saved drawings. Click one to switch to it; use <i class="bi bi-pencil"></i> to rename it or <i class="bi bi-trash3"></i> to delete it</td></tr>
    </tbody>
</table>
<p>See <a href="<?= lang_href('/guide/drawing') ?>">Saving &amp; Loading Drawings</a> for details.</p>

<h2>Finish Tab — Wood · Color</h2>
<table class="guide-table">
    <thead><tr><th>Item</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td>Wood</td><td>The wood species used for the door</td></tr>
        <tr><td>Hardware</td><td>Hinge and handle set. Choose <strong>No hardware</strong> if you don't need any</td></tr>
        <tr><td>Part to colour</td><td>First pick the part whose color you want to change: <strong>Frame · Stile &amp; rail · Slat · Face</strong>.</td></tr>
        <tr><td>Basic</td><td><strong>No finish</strong> (bare wood) and natural oil finishes such as perilla oil. Oil is applied as a single color that keeps the wood grain.</td></tr>
        <tr><td>Water-based · oil-based stains</td><td>Color swatches per product (water-based stain AURO 560, oil-based stain AURO 930). Clicking a color also selects the finish it belongs to. Hover over a color to see the product and color name.</td></tr>
    </tbody>
</table>
<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Pick <strong>Face</strong>, choose a color, then click cells in the drawing to paint just those cells. Right-click erases. Paint mode switches off automatically when you leave the Finish tab.</span>
</div>
<p>See <a href="<?= lang_href('/guide/finish') ?>">Finishes &amp; Colour Samples</a> for every colour swatch and finish type.</p>

<h2>Rendering Tab — Room Photos &amp; AI Rendering</h2>
<p>
    Upload a photo of the site, composite it with your drawing, and render it with AI.
    See the <a href="<?= lang_href('/guide/render') ?>">Using AI Rendering</a> page for details.
</p>

<h2>Estimate Tab</h2>
<p>
    Shows the <strong>estimated price</strong>, broken down into wood, labor, hardware, finish and so on, along with the <strong>minimum lead time</strong>.
    Shipping and installation are not included, and anything you edited by hand (such as added or deleted lines) is reviewed after you request a quote before the final price is set.
    The <span class="guide-ui">Request quote</span> button at the bottom lets you ask for production right away — see the <a href="<?= lang_href('/guide/order') ?>">Ordering Guide</a>.
</p>

<h2>Specs Tab — Production Dimensions</h2>
<p>Measured dimensions calculated automatically from your parameters. (Depending on your membership level, this tab may not be shown.)</p>
<table class="guide-table">
    <thead><tr><th>Item</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td>Frame width / height</td><td>Wall opening dimensions (exactly as entered)</td></tr>
        <tr><td>Outer width / height</td><td>Full door panel size (including stiles and rails), calculated with frame thickness and clearance removed</td></tr>
        <tr><td>Inner width / height</td><td>Effective inner dimensions, excluding stiles and rails</td></tr>
        <tr><td>Horizontal / vertical cells</td><td>Lattice division counts</td></tr>
        <tr><td>Horizontal / vertical ink lines</td><td>Spacing between slat centerlines</td></tr>
        <tr><td>Half-lap width · groove widths</td><td>Machining sizes for the slat half-laps and the grooves in the stiles and rails</td></tr>
        <tr><td>Member list</td><td>Width × thickness × length and count for each stile, rail, slat and frame member</td></tr>
    </tbody>
</table>

<h2>Export Tab</h2>
<table class="guide-table">
    <thead><tr><th>Button</th><th>Output</th></tr></thead>
    <tbody>
        <tr><td><span class="guide-ui">PDF</span></td><td>Drawing for printing and the workshop (A4 landscape, with dimensions)</td></tr>
        <tr><td><span class="guide-ui">PNG</span></td><td>Drawing image for sharing</td></tr>
        <tr><td><span class="guide-ui">DXF</span></td><td>Full-scale mm drawing for editing in CAD</td></tr>
    </tbody>
</table>
<p>See <a href="<?= lang_href('/guide/export') ?>">PDF / PNG / DXF Export</a> for details.</p>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>Room photos are not included in exported files. AI rendering results are downloaded separately from the Rendering tab.</span>
</div>

<?php include __DIR__ . '/../_foot.php'; ?>
