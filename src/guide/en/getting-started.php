<?php
// 영문 본문. 한글 원문은 ../getting-started.php
$guide_current = 'getting-started.php';
$guide_title   = 'Getting Started';
$guide_prev    = ['href' => 'intro.php', 'title' => 'What is Workgroup Pyeongmok?'];
$guide_next    = ['href' => 'studio-classic.php', 'title' => 'Se-sal'];
include __DIR__ . '/../_head.php';
?>

<h1>Getting Started</h1>
<p class="guide-lead">
    A step-by-step walkthrough from creating your Workgroup Pyeongmok account to designing your first drawing.
</p>

<h2>Step 1 — Sign up &amp; log in</h2>
<ol class="guide-steps">
    <li>Click the <span class="guide-ui">Log in</span> button in the top navigation.</li>
    <li>In the login dialog, enter your <strong>email</strong> and <strong>password</strong>, or choose social login (Google / Kakao).</li>
    <li>If this is your first visit, switch to the <strong>Sign up</strong> tab and register with your email and password.</li>
    <li>Once the account icon appears at the top, you're ready to go.</li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Social login lets you start right away without setting a password.</span>
</div>

<h2>Step 2 — Choose a studio</h2>
<ol class="guide-steps">
    <li>Click <span class="guide-ui">Studio</span> in the top navigation.</li>
    <li>Pick a lattice pattern engine from the dropdown.<br>
        If this is your first time, we recommend <strong>Se-sal</strong>.</li>
    <li>When the studio opens, click the <span class="guide-ui">Door</span> tab in the menu on the left to open its panel, and adjust the parameters there.</li>
    <li>The drawing is rendered on the canvas in the center as you go.</li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Type the specs you want as a sentence into the prompt box in the studio's top navigation — something like <em>"change it to a Wanja-sal sliding door, 3 panels, 1800 wide by 1200 high"</em> — and the AI applies those parameters for you. If another engine suits the request better, it switches you to that studio automatically. Prompts are currently interpreted in Korean.</span>
</div>

<h2>Step 3 — Save your first drawing</h2>
<ol class="guide-steps">
    <li>Open the <span class="guide-ui">Drawing</span> tab in the menu on the left.</li>
    <li>Enter a name in the <span class="guide-ui">Drawing name</span> field and click <span class="guide-ui">Save</span>.</li>
    <li>Saved drawings can be reopened anytime from the <strong>My drawings</strong> list at the bottom of the Drawing tab, or from the <span class="guide-ui">My Drawings</span> page.</li>
</ol>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <span>Drawings are stored in the cloud, so you can reach the same drawing by logging in from any device.</span>
</div>

<h2>How the Interface Is Laid Out</h2>
<p>The studio screen has four main areas.</p>

<table class="guide-table">
    <thead><tr><th>Area</th><th>Location</th><th>Role</th></tr></thead>
    <tbody>
        <tr><td><strong>Menu tabs</strong></td><td>Narrow column on the far left</td><td>Collection · Drawing · Door · Motifs · Finish · Rendering · Estimate · Specs · Export. Click one to open its panel beside it.</td></tr>
        <tr><td><strong>Tab panel</strong></td><td>Next to the menu tabs</td><td>Settings for the selected tab. Click the same tab again, or the <i class="bi bi-chevron-left"></i> button on the panel's edge, to collapse or expand it.</td></tr>
        <tr><td><strong>Canvas</strong></td><td>Center</td><td>Live preview of the drawing; zoom and pan with the wheel or by dragging</td></tr>
        <tr><td><strong>Canvas toolbar</strong></td><td>Above the canvas</td><td>Left: Select, Pan, Edit lines, Shapes, Place, plus the door and frame sizes / Right: zoom out, zoom in, Fit (<a href="<?= lang_href('/guide/canvas-toolbar') ?>">details</a>)</td></tr>
    </tbody>
</table>

<?php include __DIR__ . '/../_foot.php'; ?>
