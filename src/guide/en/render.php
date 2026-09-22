<?php
// 영문 본문. 한글 원문은 ../render.php
$guide_current = 'render.php';
$guide_title   = 'Using AI Rendering';
$guide_prev    = ['href' => 'export.php', 'title' => 'PDF / PNG / DXF Export'];
$guide_next    = ['href' => 'collection.php', 'title' => 'Collection & My Boards'];
include __DIR__ . '/../_head.php';
?>

<h1>AI Rendering — Compositing a Background Photo with Your Drawing</h1>
<p class="guide-lead">
    This feature layers your changho drawing over a photo of the site and has AI composite the two into a
    realistic installed view. It follows the flow
    <strong>background image + lattice drawing = AI rendering</strong>, and the whole process happens in the
    <strong>right sidebar</strong> of the Se-sal studio.
</p>

<!-- 합성 원리 흐름도 -->
<div class="guide-flow">
    <div class="guide-flow-step">
        <span class="step-icon">🖼️</span>
        <div class="step-title">Upload a background photo</div>
        <div class="step-desc">Set a photo of the site or space as the canvas background</div>
    </div>
    <div class="guide-flow-arrow">＋</div>
    <div class="guide-flow-step">
        <span class="step-icon">🪟</span>
        <div class="step-title">Design the drawing</div>
        <div class="step-desc">Adjust the lattice parameters in the left sidebar</div>
    </div>
    <div class="guide-flow-arrow">→</div>
    <div class="guide-flow-step" style="border-color:var(--accent);background:var(--accent-tint);">
        <span class="step-icon">✨</span>
        <div class="step-title">AI composite</div>
        <div class="step-desc">AI blends the background and drawing naturally</div>
    </div>
    <div class="guide-flow-arrow">→</div>
    <div class="guide-flow-step">
        <span class="step-icon">💾</span>
        <div class="step-title">Save the result</div>
        <div class="step-desc">Download as PNG or keep it in your history</div>
    </div>
</div>

<h2>Studio Layout</h2>
<p>The Se-sal studio has <strong>three panels</strong>. AI rendering takes place in the <strong>right sidebar</strong>.</p>

<!-- UI 스크린샷 -->
<div class="guide-screenshot">
    <img src="/src/img/guide/render.png" alt="AI rendering layout — the canvas with the drawing composited over a background photo, and the right panel for background upload, material and lighting choices, and rendering" loading="lazy">
</div>

<div class="guide-callout-grid">
    <div class="guid-label-callout"><div class="num">①</div><div><strong>Left sidebar</strong> — lattice parameters. Changes appear on the canvas instantly.</div></div>
    <div class="guid-label-callout"><div class="num">②</div><div><strong>Canvas</strong> — your drawing overlaid live on the background photo.</div></div>
    <div class="guid-label-callout"><div class="num">③</div><div><strong>Background photo panel</strong> — upload photos, pick a thumbnail, enter the AI prompt, run the render.</div></div>
    <div class="guid-label-callout"><div class="num">④</div><div><strong>Render history</strong> — keeps up to 9 recent results. Click one to view it again.</div></div>
</div>

<h2>Step-by-Step</h2>

<h3>① Upload a background photo</h3>
<ol class="guide-steps">
    <li>
        If the <strong>right sidebar</strong> is closed, click the
        <span class="guide-ui">&rsaquo;</span> tab at the right edge of the canvas to open it.
    </li>
    <li>
        Click the <span class="guide-ui">↑ Add photo</span> button and choose an image file.<br>
        You can upload several at once.
    </li>
    <li>
        Uploaded photos appear in the <strong>thumbnail list</strong> below.
        Clicking a thumbnail <strong>applies it as the canvas background immediately</strong>.
        The selected thumbnail is outlined in green.
    </li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Once a background photo is selected, the drawing (the lattice) is displayed over it on the canvas. That combined view is the composite image sent to the AI.</span>
</div>

<h3>② Adjust the drawing parameters</h3>
<p>
    Adjust the changho's dimensions and lattice settings in the left sidebar.
    Because the drawing is composited over the background photo live, you can judge the proportions and
    pattern against the real space on the spot.
</p>
<ul>
    <li><strong>Width / height</strong> — match the actual installation space</li>
    <li><strong>Horizontal slat layout (top/middle/bottom)</strong> — set each zone's cell ratio independently</li>
    <li><strong>Frame and slat colors</strong> — set in the finish section of the right sidebar</li>
</ul>

<h3>③ Write the AI rendering prompt</h3>
<p>
    Type the mood you want as a single line in the text field at the bottom of the right sidebar.
    The AI uses this text as a reference for the texture, lighting and atmosphere of the composite.
</p>

<table class="guide-table">
    <thead><tr><th>Good prompt examples</th><th>Expected effect</th></tr></thead>
    <tbody>
        <tr><td>Aged oak grain, lacquer finish, white hanji paper</td><td>Emphasizes wood texture and a hanji backdrop</td></tr>
        <tr><td>Hanok café interior, warm daylight</td><td>A bright, warm sense of space</td></tr>
        <tr><td>Night, soft indirect lighting, atmospheric restaurant</td><td>An evening mood</td></tr>
        <tr><td>Traditional temple, serene natural surroundings, natural light</td><td>A still, quiet traditional feel</td></tr>
        <tr><td>Modern minimal, white tones, large windows, city view</td><td>A clean, contemporary atmosphere</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Including all three elements — <strong>space type + material/texture + lighting condition</strong> — gives more accurate results.<br>
    For example: <em>"hanok café"</em> (space) + <em>"oak grain"</em> (material) + <em>"daytime natural light"</em> (lighting)</span>
</div>

<h3>④ Run the render</h3>
<ol class="guide-steps">
    <li>
        Click the <span class="guide-ui" style="background:var(--accent);color:var(--bg);border:none;">✨ Rendering</span> button.
    </li>
    <li>
        An <strong>"AI rendering…"</strong> loading overlay appears on the canvas.
        Processing usually takes <strong>30–90 seconds</strong>.
    </li>
    <li>
        When it finishes, the result popup opens automatically.
        Save the PNG with <span class="guide-ui">Download</span>, or close the popup.
    </li>
    <li>
        Results are saved to your <strong>render history</strong> automatically.
        Click a thumbnail to view it again anytime.
    </li>
</ol>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>Make sure a background photo is selected before running a render. Without one, a notice appears and rendering does not start.</span>
</div>

<h2>Render History</h2>
<p>
    Up to <strong>9</strong> recent results are kept automatically in the <strong>render history</strong> at
    the bottom of the right sidebar. Click a thumbnail to review or download it from the result popup.
    You can also remove an entry with its <i class="bi bi-x"></i> button.
</p>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <div>
        <p style="margin:0 0 4px;">Renders are stored on the server, so they appear the same on any device or browser as long as you're signed in to the same account.</p>
        <p style="margin:0;">Note that each account can keep at most <strong>300</strong> renders. Beyond that limit, new renders are refused with a notice — so clear out old ones with the <i class="bi bi-x"></i> button in the right sidebar's history, or back up the ones you need with <span class="guide-ui">Download</span>.</p>
    </div>
</div>

<h2>Managing Background Photos</h2>
<table class="guide-table">
    <thead><tr><th>Action</th><th>How</th></tr></thead>
    <tbody>
        <tr><td>Add a background photo</td><td>The <span class="guide-ui">↑ Add photo</span> button in the right sidebar, or drag &amp; drop</td></tr>
        <tr><td>Switch background photo</td><td>Click a thumbnail — the canvas background changes to that photo immediately</td></tr>
        <tr><td>Remove a background photo</td><td>Click the <span class="guide-ui"><i class="bi bi-x-lg"></i></span> button beside the thumbnail</td></tr>
        <tr><td>View the drawing without a background</td><td>Click the active thumbnail again to deactivate it</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Background photos are stored on the server, so reopening the same drawing restores the photos you uploaded earlier along with it.</span>
</div>

<?php include __DIR__ . '/../_foot.php'; ?>
