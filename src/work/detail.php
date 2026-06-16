<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/db.php';
$pdo = db();

$id   = (int)($_GET['id'] ?? 0);
$work = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM works WHERE id=? AND is_active=1');
    $stmt->execute([$id]);
    $work = $stmt->fetch();
}
if (!$work) { header('Location: /src/work/'); exit; }

// 이미지 목록 (work_images 우선, 없으면 works.image_url fallback)
try {
    $imgStmt = $pdo->prepare('SELECT image_url FROM work_images WHERE work_id=? ORDER BY sort_order, id');
    $imgStmt->execute([$id]);
    $images = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) { $images = []; }
if (empty($images) && $work['image_url']) $images = [$work['image_url']];

// 다음 작품
$next = $pdo->prepare('SELECT id,title FROM works WHERE is_active=1 AND (sort_order > ? OR (sort_order=? AND id>?)) ORDER BY sort_order ASC, id ASC LIMIT 1');
$next->execute([$work['sort_order'], $work['sort_order'], $work['id']]);
$next = $next->fetch();
if (!$next) $next = $pdo->query('SELECT id,title FROM works WHERE is_active=1 ORDER BY sort_order ASC, id ASC LIMIT 1')->fetch();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($work['title']) ?> — 평목</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/src/css/work-detail.css">
</head>
<body>

<!-- Works 로고 -->
<header class="wd-header">
    <a href="/src/work/" class="wd-header-logo">Works</a>
</header>

<div class="wd-layout">

    <!-- 좌측: 가로 스크롤 이미지 -->
    <div class="wd-left-col" id="wdLeftCol">
        <?php foreach ($images as $i => $img): ?>
        <div class="wd-image-slot">
            <img src="<?= htmlspecialchars($img) ?>"
                 alt="<?= htmlspecialchars($work['title']) ?> <?= $i+1 ?>">
        </div>
        <?php endforeach; ?>
        <?php if ($next && $next['id'] !== $work['id']): ?>
        <a class="wd-next-slot" href="/src/work/detail.php?id=<?= $next['id'] ?>">
            <span class="wd-next-slot__label">next</span>
            <span class="wd-next-slot__title"><?= htmlspecialchars($next['title']) ?></span>
        </a>
        <?php endif; ?>
    </div>

    <!-- 우측: 고정 레드 패널 -->
    <div class="wd-right-col">
        <div class="wd-right-inner">
            <h1 class="wd-title"><?= htmlspecialchars($work['title']) ?></h1>
            <?php if ($work['description']): ?>
            <p class="wd-sub"><?= htmlspecialchars($work['description']) ?></p>
            <?php endif; ?>
            <!-- 이미지 카운터 -->
            <?php if (count($images) > 1): ?>
            <div class="wd-counter">
                <span id="wdCurrent">1</span> / <span><?= count($images) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
(function () {
    const col     = document.getElementById('wdLeftCol');
    const counter = document.getElementById('wdCurrent');
    const slots   = col.querySelectorAll('.wd-image-slot');

    /* ── 드래그 스크롤 ── */
    let isDragging = false, startX, startScroll, dragMoved = false;
    col.addEventListener('mousedown', e => {
        isDragging = true; dragMoved = false;
        startX = e.pageX; startScroll = col.scrollLeft;
        col.style.cursor = 'grabbing';
    });
    window.addEventListener('mousemove', e => {
        if (!isDragging) return;
        if (Math.abs(e.pageX - startX) > 5) dragMoved = true;
        col.scrollLeft = startScroll - (e.pageX - startX);
    });
    window.addEventListener('mouseup', () => {
        isDragging = false;
        col.style.cursor = 'grab';
    });

    /* ── 카운터 업데이트 ── */
    if (counter && slots.length) {
        col.addEventListener('scroll', () => {
            const mid = col.scrollLeft + col.clientWidth / 2;
            let closest = 0, minDist = Infinity;
            slots.forEach((s, i) => {
                const d = Math.abs(s.offsetLeft + s.offsetWidth / 2 - mid);
                if (d < minDist) { minDist = d; closest = i; }
            });
            counter.textContent = closest + 1;
        });
    }
})();
</script>
</body>
</html>
