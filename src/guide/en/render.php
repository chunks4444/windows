<?php
// 영문 본문. 한글 원문은 ../render.php
$guide_current = 'render.php';
$guide_title   = 'Using AI Rendering';
$guide_prev    = ['href' => 'export.php', 'title' => 'PDF / PNG / DXF Export'];
$guide_next    = ['href' => 'collection.php', 'title' => 'Collection & My Boards'];
include __DIR__ . '/../_head.php';
?>

<h1>AI Rendering — Compositing a Room Photo with Your Drawing</h1>
<p class="guide-lead">
    This feature overlays your changho drawing on a site photo (the background) and has AI composite it into a realistic installed look.
    It works as <strong>room photo + lattice drawing = AI rendering result</strong>,
    and everything happens in the <strong>Rendering</strong> tab of the studio's left-hand menu. All seven engines work the same way.
</p>

<!-- 합성 원리 흐름도 -->
<div class="guide-flow">
    <div class="guide-flow-step">
        <span class="step-icon">🖼️</span>
        <div class="step-title">Upload a room photo</div>
        <div class="step-desc">Set a site or interior photo as the canvas background</div>
    </div>
    <div class="guide-flow-arrow">＋</div>
    <div class="guide-flow-step">
        <span class="step-icon">🪟</span>
        <div class="step-title">Place the drawing</div>
        <div class="step-desc">Design in the Door tab, then use Place to fit it into the photo</div>
    </div>
    <div class="guide-flow-arrow">→</div>
    <div class="guide-flow-step" style="border-color:var(--accent);background:var(--accent-tint);">
        <span class="step-icon">✨</span>
        <div class="step-title">AI compositing</div>
        <div class="step-desc">AI blends the photo and drawing naturally</div>
    </div>
    <div class="guide-flow-arrow">→</div>
    <div class="guide-flow-step">
        <span class="step-icon">💾</span>
        <div class="step-title">Keep the result</div>
        <div class="step-desc">Saved to Rendered images automatically, downloadable anytime</div>
    </div>
</div>

<h2>The Rendering Tab</h2>
<p>Click <span class="guide-ui"><i class="bi bi-stars"></i> Rendering</span> in the left-hand menu to open the panel. Work through it from top to bottom.</p>

<!-- UI 스크린샷 -->
<div class="guide-screenshot">
    <img src="/src/img/guide/render-en.png" alt="Rendering tab open in the Se-sal studio — Room photo, Suggested prompts, Describe it, the AI rendering button and Rendered images in the panel, with the drawing fitted into the room photo in Place mode on the canvas" loading="lazy">
</div>

<div class="guide-callout-grid">
    <div class="guid-label-callout"><div class="num">①</div><div><strong>Room photo</strong> — the photos you've uploaded. The <span class="guide-ui">No background</span> and <span class="guide-ui">Upload photo</span> buttons sit at the right of its heading.</div></div>
    <div class="guid-label-callout"><div class="num">②</div><div><strong>Suggested prompts</strong> — pick a material and lighting mood and the text box below is filled in for you.</div></div>
    <div class="guid-label-callout"><div class="num">③</div><div><strong>Describe it</strong> — write the mood you want, or edit a suggested one. Run it with the <span class="guide-ui">AI rendering</span> button right below.</div></div>
    <div class="guid-label-callout"><div class="num">④</div><div><strong>Rendered images</strong> — every result you've made, with the count shown beside the heading.</div></div>
</div>

<h2>Step by Step</h2>

<h3>① Upload a room photo</h3>
<ol class="guide-steps">
    <li>Click the <span class="guide-ui">Rendering</span> tab in the left-hand menu.</li>
    <li>
        Click <span class="guide-ui"><i class="bi bi-upload"></i> Upload photo</span> on the <strong>Room photo</strong> heading and choose an image file.<br>
        You can upload several at once.
    </li>
    <li>
        Uploaded photos appear in the list. Click one and it is <strong>applied as the canvas background right away</strong>; the selected photo gets a border.
    </li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Once a photo is selected, the drawing (lattice) is shown on top of it. Exactly what you see is the composite image sent to the AI.</span>
</div>

<h3>② Fit the drawing to the photo</h3>
<p>
    Set the dimensions and lattice in the <span class="guide-ui">Door</span> tab and the colors in the <span class="guide-ui">Finish</span> tab.
    The drawing previews live on top of the photo, so you can judge on the spot which proportions and pattern suit the space.
</p>
<p>
    Then turn on <span class="guide-ui">Place</span> in the toolbar above the canvas and drag the door's four corner handles onto the door opening in the photo.
    The door is warped to match the perspective, so you get a natural result even from a photo taken at an angle.
    See <a href="<?= lang_href('/guide/canvas-toolbar') ?>">Canvas Toolbar</a> for details.
</p>

<h3>③ Write a prompt</h3>
<p>
    Pick a mood from <strong>Suggested prompts</strong>, or write the mood you want in a line or two under <strong>Describe it</strong>.
    The AI uses this text to composite texture, lighting and atmosphere.
</p>

<table class="guide-table">
    <thead><tr><th>Good prompt examples</th><th>Expected effect</th></tr></thead>
    <tbody>
        <tr><td>Aged oak grain, lacquer finish, white hanji paper</td><td>Emphasizes wood texture and a hanji backdrop</td></tr>
        <tr><td>Hanok café interior, warm daylight</td><td>A bright, warm sense of space</td></tr>
        <tr><td>Apartment living room entry door, light wood floor, afternoon sun</td><td>A mood that suits a modern home</td></tr>
        <tr><td>Night, soft indirect lighting, atmospheric restaurant</td><td>An evening mood</td></tr>
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
        Click the <span class="guide-ui" style="background:var(--accent);color:var(--bg);border:none;"><i class="bi bi-stars"></i> AI rendering</span> button. (You need to be logged in.)
    </li>
    <li>
        <strong>"AI rendering…"</strong> appears over the canvas.
        It usually takes <strong>30–90 seconds</strong>.
    </li>
    <li>
        When it finishes, the result window opens automatically. Save it with <span class="guide-ui">Download</span> or close the window.
    </li>
    <li>
        The result is added to <strong>Rendered images</strong> at the bottom of the panel automatically.
    </li>
</ol>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>If no room photo is selected or the prompt is empty, a notice appears and rendering does not start.</span>
</div>

<h2>Managing Rendered Images</h2>
<p>
    <strong>Rendered images</strong> shows every result you've made.
    Click an image to view it large in the result window; use the <i class="bi bi-download"></i> button at its top right to download it,
    or <i class="bi bi-trash3"></i> to delete it.
</p>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <div>
        <p style="margin:0 0 4px;">Rendered images are stored on the server, so they look the same from any device or browser signed in to the same account.</p>
        <p style="margin:0;">Note that each account can keep at most <strong>300</strong> renders. Beyond that limit, new renders are refused with a notice — so clear out old ones with <i class="bi bi-trash3"></i>, or download the ones you need with <i class="bi bi-download"></i> beforehand.</p>
    </div>
</div>

<h2>The Renders Tab in My Drawings</h2>
<p>
    The <strong>Renders</strong> tab of the <span class="guide-ui">My Drawings</span> page in the top navigation gathers the rendering results from every engine.
    The top shows <strong>stored count / 300</strong>, and a notice appears when it's full.
</p>
<table class="guide-table">
    <thead><tr><th>Action</th><th>How</th></tr></thead>
    <tbody>
        <tr><td>View large · download</td><td>Click an image to open the detail window, then use <span class="guide-ui">Download</span>.</td></tr>
        <tr><td>Share</td><td>The <span class="guide-ui"><i class="bi bi-share-fill"></i></span> button on the image → copy the link or share to KakaoTalk, Facebook or X</td></tr>
        <tr><td>Delete</td><td>The <span class="guide-ui"><i class="bi bi-x"></i></span> button on the image, or <span class="guide-ui">Delete</span> in the detail window</td></tr>
    </tbody>
</table>

<h2>Managing Room Photos</h2>
<table class="guide-table">
    <thead><tr><th>Action</th><th>How</th></tr></thead>
    <tbody>
        <tr><td>Add a photo</td><td><span class="guide-ui"><i class="bi bi-upload"></i> Upload photo</span> on the Room photo heading</td></tr>
        <tr><td>Switch photos</td><td>Click another photo in the list — the canvas background changes right away.</td></tr>
        <tr><td>View the drawing without a background</td><td>Click <span class="guide-ui"><i class="bi bi-slash-circle"></i> No background</span> on the heading</td></tr>
        <tr><td>Delete a photo</td><td>Click the <span class="guide-ui"><i class="bi bi-x-lg"></i></span> button on the photo</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>Room photos are stored on the server, so when you reopen the same drawing, the photos you uploaded earlier come back too.</span>
</div>

<?php include __DIR__ . '/../_foot.php'; ?>
