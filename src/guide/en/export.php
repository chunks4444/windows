<?php
// 영문 본문. 한글 원문은 ../export.php
$guide_current = 'export.php';
$guide_title   = 'PDF / PNG / DXF Export';
$guide_prev    = ['href' => 'drawing.php', 'title' => 'Saving & Loading Drawings'];
$guide_next    = ['href' => 'render.php', 'title' => 'Using AI Rendering'];
include __DIR__ . '/../_head.php';
?>

<h1>PDF / PNG / DXF Export</h1>
<p class="guide-lead">
    Export your finished drawing as a PDF or PNG file for delivery, printing or collaboration.
    Output is high resolution, exactly as it appears on the canvas.
</p>

<h2>PNG Export</h2>
<ol class="guide-steps">
    <li>Click the <span class="guide-ui">Save PNG</span> button at the right of the toolbar.</li>
    <li>A PNG of the current canvas view downloads immediately.</li>
    <li>The filename is generated automatically as <em>drawingname_date.png</em>.</li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>PNG supports transparent backgrounds, which makes it convenient for compositing and editing in other tools.</span>
</div>

<h2>PDF Export</h2>
<ol class="guide-steps">
    <li>Click the <span class="guide-ui">Save PDF</span> button on the toolbar.</li>
    <li>Choose the orientation (landscape or portrait) and paper size (A4, A3, etc.).</li>
    <li>Click <span class="guide-ui">Generate PDF</span> and the file downloads.</li>
</ol>

<h2>DXF Export</h2>
<p>
    You can export the drawing as a DXF file that opens directly in CAD programs such as AutoCAD.
    Coordinates are saved in real millimeters, so you can check dimensions in CAD without rescaling.
</p>
<ol class="guide-steps">
    <li>Click the <span class="guide-ui">Export</span> button on the toolbar.</li>
    <li>Choose <span class="guide-ui">DXF</span> from the menu.</li>
    <li>The DXF file downloads immediately.</li>
</ol>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>Lattice intersections and the outer frame are exported as rectangular polylines at each member's actual width. Tenon projections and the transom panel's inner fill board are not included yet, and slat intersections are represented as simple overlaps without groove geometry — this is a first version, intended as a shape reference.</span>
</div>

<h2>Export Setting Tips</h2>
<table class="guide-table">
    <thead><tr><th>Purpose</th><th>Recommended format</th><th>Settings</th></tr></thead>
    <tbody>
        <tr><td>Delivery and printing</td><td>PDF</td><td>A3 or larger, landscape</td></tr>
        <tr><td>Digital sharing and review</td><td>PNG</td><td>Default resolution</td></tr>
        <tr><td>AI rendering composites</td><td>PNG</td><td>Keep the background transparent</td></tr>
        <tr><td>Presentations</td><td>PDF</td><td>A4, portrait</td></tr>
        <tr><td>CAD work and precise dimensions</td><td>DXF</td><td>Real millimeter coordinates</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>PDF export uses the jsPDF library in your browser. Large drawings may take several seconds to generate.</span>
</div>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>The background image (the photo used for AI rendering) is not included in PDF export. Only the lattice layer is output.</span>
</div>

<?php include __DIR__ . '/../_foot.php'; ?>
