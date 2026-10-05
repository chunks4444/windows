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
    From the <strong>Motifs</strong> tab in the left-hand menu you can pick a pre-registered motif, or
    upload an SVG file of your own, and place it freely on the canvas.
</p>

<h2>Choosing from the Library</h2>
<ol class="guide-steps">
    <li>Open the <span class="guide-ui"><i class="bi bi-flower1"></i> Motifs</span> tab in the left-hand menu.</li>
    <li>Click any motif in the <strong>Motif library</strong> and it is inserted at the center of the canvas right away.</li>
    <li>You can also download a motif as an SVG file with the <i class="bi bi-download"></i> button at its top right.</li>
</ol>

<h2>Uploading Your Own SVG</h2>
<ol class="guide-steps">
    <li>Click the <span class="guide-ui"><i class="bi bi-upload"></i> Upload SVG</span> button at the top of the Motifs tab.</li>
    <li>Choose a <code>.svg</code> file from your computer.</li>
    <li>Once the upload finishes, it is inserted at the center of the canvas automatically and added to <strong>My uploaded motifs</strong>, so you can click it there to reuse it later.</li>
</ol>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>Uploaded SVGs are saved to your account only, up to a maximum file size of 2MB. A solid-color rectangle filling the whole background (a canvas background layer, for example) is removed automatically on upload, so the motif is stored with a transparent background.</span>
</div>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>SVG files containing scripts, or files in an invalid format, are rejected. If you delete one of your uploaded motifs with <i class="bi bi-trash3"></i>, it also disappears from any drawing it was placed in.</span>
</div>

<h2>Adjusting After Insertion</h2>
<p>
    A newly inserted motif is selected right away, with handles around its edge. To adjust it again later, turn on
    <span class="guide-ui"><i class="bi bi-cursor"></i> Select</span> in the toolbar above the canvas and click the motif.
</p>
<table class="guide-table">
    <thead><tr><th>Action</th><th>How</th></tr></thead>
    <tbody>
        <tr><td>Move</td><td>Drag the motif</td></tr>
        <tr><td>Resize</td><td>Drag a corner or side handle</td></tr>
        <tr><td>Rotate</td><td>Drag the rotation handle above the frame</td></tr>
        <tr><td>Delete</td><td>Select the motif and press <kbd>Delete</kbd> or <kbd>Backspace</kbd></td></tr>
        <tr><td>Remove everything</td><td>Toolbar <span class="guide-ui">Shapes ▾</span> → <span class="guide-ui">Clear all</span> (shapes and text are removed too)</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Motif insertion is a shared feature, available identically in all seven engines (Se-sal, Jeongja-sal, Bit-sal, Gyeokja-bit-sal, Semo-sotgeul-sal, Yukmo-sotgeul-sal and Mondrian).</span>
</div>

<?php include __DIR__ . '/../_foot.php'; ?>
