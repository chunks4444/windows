<?php
// 영문 본문. 한글 원문은 ../svg-insert.php
$guide_current = 'svg-insert.php';
$guide_title   = 'Inserting Motifs & Uploading SVGs';
$guide_prev    = ['href' => 'canvas-toolbar.php', 'title' => 'Canvas Toolbar'];
$guide_next    = ['href' => 'drawing.php', 'title' => 'Saving & Loading Drawings'];
include __DIR__ . '/../_head.php';
?>

<h1>Inserting Motifs &amp; Uploading SVGs</h1>
<p class="guide-lead">
    From the <strong>Insert motif</strong> section of the sidebar you can pick a pre-registered motif, or
    upload an SVG file of your own, and place it freely on the canvas.
</p>

<h2>Choosing from the Library</h2>
<ol class="guide-steps">
    <li>Click the <span class="guide-ui">Library</span> button in the sidebar's <span class="guide-ui">Insert motif</span> section.</li>
    <li>Click any motif in the list and it is inserted at the center of the canvas right away.</li>
</ol>

<h2>Uploading Your Own SVG</h2>
<ol class="guide-steps">
    <li>Click the <span class="guide-ui">Upload</span> button in the sidebar's <span class="guide-ui">Insert motif</span> section.</li>
    <li>Choose a <code>.svg</code> file from your computer.</li>
    <li>Once the upload finishes, it is inserted at the center of the canvas automatically.</li>
</ol>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>Uploaded SVGs are saved to your account only, up to a maximum file size of 2MB. A solid-color rectangle filling the whole background (a canvas background layer, for example) is removed automatically on upload, so the motif is stored with a transparent background.</span>
</div>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>SVG files containing scripts, or files in an invalid format, are rejected.</span>
</div>

<h2>Adjusting After Insertion</h2>
<p>
    Inserting a motif automatically switches to <strong>scale / move / transform</strong> mode, and the
    sidebar shows size and rotation sliders along with duplicate and delete buttons.
</p>
<table class="guide-table">
    <thead><tr><th>Action</th><th>How</th></tr></thead>
    <tbody>
        <tr><td>Move</td><td>Drag the motif on the canvas</td></tr>
        <tr><td>Resize / rotate</td><td>Drag the corner handles, or use the sidebar sliders</td></tr>
        <tr><td>Select several</td><td>Shift + click to add or remove motifs from the selection and move or adjust them together</td></tr>
        <tr><td>Duplicate</td><td>The <span class="guide-ui">Duplicate</span> button in the sidebar</td></tr>
        <tr><td>Delete</td><td>The <span class="guide-ui">Delete</span> button in the sidebar</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Motif insertion is a shared feature, available identically in all six engines (Se-sal, Jeongja-sal, Bit-sal, Gyeokja-bit-sal, Semo-sotgeul-sal and Yukmo-sotgeul-sal).</span>
</div>

<?php include __DIR__ . '/../_foot.php'; ?>
