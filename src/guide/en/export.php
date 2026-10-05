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
    Export your finished drawing as a PDF, PNG or DXF file for printing, sending to the workshop or CAD work.
    All exports are done from the <span class="guide-ui"><i class="bi bi-box-arrow-down"></i> Export</span> tab in the left-hand menu, and require you to be logged in.
</p>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>Files are named automatically as <em>drawingname_version.extension</em> (e.g. <em>Cheongdam_front_v3.pdf</em>). A drawing that hasn't been saved is exported as <em>changho-drawing</em>.</span>
</div>

<h2>PDF Export</h2>
<ol class="guide-steps">
    <li>Click the <span class="guide-ui">PDF</span> button in the Export tab.</li>
    <li>A PDF with the drawing fitted to an A4 landscape page downloads immediately.</li>
</ol>
<p>Handy for printing or sending the drawing to the workshop.</p>

<h2>PNG Export</h2>
<ol class="guide-steps">
    <li>Click the <span class="guide-ui">PNG</span> button in the Export tab.</li>
    <li>A high-resolution drawing image on a white background downloads immediately.</li>
</ol>
<p>Convenient for sharing the drawing by messenger or email, or sending it for review.</p>

<h2>DXF Export</h2>
<p>
    You can export the drawing as a DXF file that opens directly in CAD programs such as AutoCAD.
    Coordinates are saved in real millimeters, so you can check dimensions in CAD without rescaling.
</p>
<ol class="guide-steps">
    <li>Click the <span class="guide-ui">DXF</span> button in the Export tab.</li>
    <li>The DXF file downloads immediately.</li>
</ol>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>Lattice intersections and the outer frame are exported as rectangular polylines at each member's actual width. Tenon projections and the transom panel's inner fill board are not included yet, and slat intersections are represented as simple overlaps without groove geometry — this is a first version, intended as a shape reference.</span>
</div>

<h2>Which Format to Use</h2>
<table class="guide-table">
    <thead><tr><th>Purpose</th><th>Recommended format</th></tr></thead>
    <tbody>
        <tr><td>Printing, sending to the workshop</td><td>PDF</td></tr>
        <tr><td>Sharing by messenger or email, review</td><td>PNG</td></tr>
        <tr><td>CAD work and precise dimensions</td><td>DXF</td></tr>
        <tr><td>Seeing it in a real space</td><td><a href="<?= lang_href('/guide/render') ?>">AI rendering</a></td></tr>
    </tbody>
</table>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>Room photos uploaded in the Rendering tab are not included in exported files. Only the drawing is output.</span>
</div>

<?php include __DIR__ . '/../_foot.php'; ?>
