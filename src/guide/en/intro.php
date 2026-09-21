<?php
// 영문 본문. 한글 원문은 ../intro.php — 구조를 같이 맞춰 두면 나중에 대조가 쉽다.
$guide_current = 'intro.php';
$guide_title   = 'What is Pyeongmok Studio?';
$guide_prev    = null;
$guide_next    = ['href' => 'getting-started.php', 'title' => 'Getting Started'];
include __DIR__ . '/../_head.php';
?>

<h1>What is Pyeongmok Studio?</h1>
<p class="guide-lead">
    Pyeongmok (平木) is an online studio where you can design and export traditional Korean window
    (changho) drawings in real time, right in your browser. Six lattice pattern engines and AI
    rendering are available with nothing to install.
</p>

<h2>Key Features</h2>
<p>Here is what Pyeongmok gives you.</p>

<table class="guide-table">
    <thead>
        <tr><th>Feature</th><th>Description</th></tr>
    </thead>
    <tbody>
        <tr><td><strong>Six studios</strong></td><td>Lattice pattern engines for Se-sal, Jeongja-sal, Bit-sal, Gyeokja-bit-sal, Semo-sotgeul-sal and Yukmo-sotgeul-sal</td></tr>
        <tr><td><strong>Real-time rendering</strong></td><td>Every parameter change is reflected on the canvas instantly</td></tr>
        <tr><td><strong>Drawing storage</strong></td><td>Drawings and versions are saved to the cloud, so you can pick up where you left off anywhere</td></tr>
        <tr><td><strong>PDF / PNG / DXF export</strong></td><td>Output files for printing, delivery, or CAD work</td></tr>
        <tr><td><strong>AI rendering</strong></td><td>Composite your drawing over a background photo to visualize the space with AI</td></tr>
        <tr><td><strong>Collection</strong></td><td>Browse the public pattern library and save patterns to your boards</td></tr>
        <tr><td><strong>AI chat design</strong></td><td>Describe the specs you want in the prompt box at the top and the parameters are applied automatically</td></tr>
    </tbody>
</table>

<h2>The Six Lattice Pattern Engines</h2>
<p>The studio is divided into six engines according to how the lattice slats are arranged.</p>

<ul>
    <li><strong>Se-sal</strong> — The traditional quarter-lap structure. The classic window pattern of crossing vertical and horizontal slats</li>
    <li><strong>Jeongja-sal</strong> — A simple square grid. The basic pattern built from vertical and horizontal slats</li>
    <li><strong>Bit-sal</strong> — A grid rotated 45°. A diagonal pattern of crossing angled slats</li>
    <li><strong>Gyeokja-bit-sal</strong> — A composite diamond pattern. An advanced pattern combining vertical, horizontal and diagonal slats</li>
    <li><strong>Semo-sotgeul-sal</strong> — A triangular grid, built from slats running in three directions at 0°, 60° and 120°</li>
    <li><strong>Yukmo-sotgeul-sal</strong> — A hexagonal grid, the triangular pattern with one direction removed</li>
</ul>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Not sure which pattern to pick? Browse real drawing examples on the <strong>Collection</strong> page first.</span>
</div>

<h2>System Requirements</h2>
<p>Pyeongmok runs in a web browser. Nothing to install — these environments are supported.</p>
<ul>
    <li>Chrome 90 or later (recommended)</li>
    <li>Safari 15 or later</li>
    <li>Edge 90 or later</li>
    <li>Firefox 88 or later</li>
</ul>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>AI rendering needs server processing time. A stable network connection is recommended.</span>
</div>

<?php include __DIR__ . '/../_foot.php'; ?>
