<?php
// 영문 본문. 한글 원문은 ../finish.php
$guide_current = 'finish.php';
$guide_title   = 'Finishes & Colour Samples';
$guide_prev    = ['href' => 'studio-mondrian.php', 'title' => 'Mondrian'];
$guide_next    = ['href' => 'canvas-toolbar.php', 'title' => 'Canvas Toolbar'];
include __DIR__ . '/../_head.php';
?>

<h1>Finishes &amp; Colour Samples</h1>
<p class="guide-lead">
    In the <strong>Finish</strong> tab of the studio's left-hand menu you choose the wood and hardware, and set a finish colour for each part — frame, stiles &amp; rails, slats and faces.
    It works the same in all seven engines, and the finish you choose is reflected in the estimate right away. Every colour you can pick is shown below.
</p>

<div class="guide-screenshot">
    <img src="/src/img/guide/finish-en.png" alt="The Finish tab — Wood and Hardware, Part to colour (Frame, Stile &amp; rail, Slat, Face), Basic finishes, and the water-based (AURO 560) and oil-based (AURO 930) stain swatches, with the slats and stiles painted AURO 560 navy on the canvas" loading="lazy">
</div>

<h2>Using the Finish Tab</h2>
<ol class="guide-steps">
    <li>Open the <span class="guide-ui"><i class="bi bi-palette"></i> Finish</span> tab in the left-hand menu.</li>
    <li>Choose the <strong>Wood</strong> and <strong>Hardware</strong>. If you don't need hardware, choose <span class="guide-ui">No hardware</span>.</li>
    <li>Under <strong>Part to colour</strong>, click one of <strong>Frame · Stile &amp; rail · Slat · Face</strong>. If you click a colour without picking a part first, a hint appears.</li>
    <li>Click a colour among the <strong>Basic</strong> finishes or the stain swatches and that part is painted right away. Clicking a colour also selects the finish it belongs to.</li>
    <li>Switch parts and paint the rest the same way. The name of the selected part's colour is shown just below the part buttons.</li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Pick <strong>Face</strong> and click a colour to turn on <strong>Paint face color</strong>. Click cells in the drawing to paint just those cells; right-click erases. Painting switches off automatically when you leave the Finish tab.</span>
</div>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span><strong>A door has one finish type.</strong> Each part can have its own colour, but clicking a colour from a different stain switches the finish type, and parts you've already painted change to the nearest colour in that stain. Choosing perilla oil or the oil finish paints the frame, stiles &amp; rails and slats in a single oil colour and clears face colours.</span>
</div>

<h2>Finish Types</h2>
<table class="guide-table">
    <thead><tr><th>Finish</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td><strong>No finish</strong></td><td>Bare, unfinished wood.</td></tr>
        <tr><td><strong>Perilla oil</strong></td><td>A traditional natural oil finish. The grain shows through, in a single colour.</td></tr>
        <tr><td><strong>Oil finish (AURO 126)</strong></td><td>A natural oil finish. Its colour depends on the wood you choose.</td></tr>
        <tr><td><strong>Water-based stain (AURO 560)</strong></td><td>A water-based tinted finish that lets the grain show. Choose a colour from the swatches below.</td></tr>
        <tr><td><strong>Oil-based stain (AURO 930)</strong></td><td>An oil-based tinted finish that lets the grain show. Choose a colour from the swatches below.</td></tr>
    </tbody>
</table>

<h2>Colour Samples</h2>
<p>All the colours offered in the Finish tab. The code under each colour name is the manufacturer's (AURO) colour code the workshop uses when finishing.</p>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>Colours look different from screen to screen, and the real colour varies with the wood, its grain and the number of coats. If exact colour matters, ask for a physical sample in the notes of your quote request.</span>
</div>

<h3>Basic</h3>
<?php $sw_mode = 'basic'; include __DIR__ . '/../_swatches.php'; ?>

<?php $sw_mode = 'stain'; include __DIR__ . '/../_swatches.php'; ?>

<?php include __DIR__ . '/../_foot.php'; ?>
