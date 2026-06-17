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

// 아이템별 패널 색상
$panelBg    = htmlspecialchars($work['panel_bg']    ?: '#111111');
$titleColor = htmlspecialchars($work['title_color'] ?: '#ffffff');
$descColor  = htmlspecialchars($work['desc_color']  ?: '#888888');

// 이미지 목록
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

$total = count($images);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($work['title']) ?> — 평목</title>
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/src/css/work-detail.css">
</head>
<body>
<?php require_once __DIR__ . '/../components/nav.php'; ?>

<div class="wd-layout">

    <!-- 좌측: 이미지 갤러리 -->
    <div class="wd-left" id="wdLeft">
        <?php foreach ($images as $i => $img): ?>
        <div class="wd-image-slot">
            <img src="<?= htmlspecialchars($img) ?>"
                 alt="<?= htmlspecialchars($work['title']) ?> <?= $i+1 ?>">
        </div>
        <?php endforeach; ?>
    </div>

    <!-- 우측: 정보 패널 -->
    <div class="wd-right" style="background:<?= $panelBg ?>">
        <a href="/src/work/" class="wd-back">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <path d="M9 2L4 7L9 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Works
        </a>

        <div class="wd-info">
            <h1 class="wd-title" style="color:<?= $titleColor ?>"><?= htmlspecialchars($work['title']) ?></h1>
            <?php if ($work['description']): ?>
            <p class="wd-location" style="color:<?= $descColor ?>"><?= htmlspecialchars($work['description']) ?></p>
            <?php endif; ?>
            <?php if ($total > 1): ?>
            <div class="wd-counter">
                <strong id="wdCurrent">01</strong> / <?= sprintf('%02d', $total) ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($next && $next['id'] !== $work['id']): ?>
        <a class="wd-next" href="/src/work/detail.php?id=<?= $next['id'] ?>">
            <div>
                <span class="wd-next-label">next</span>
                <span class="wd-next-title"><?= htmlspecialchars($next['title']) ?></span>
            </div>
            <span class="wd-next-arrow">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M4 9H14M14 9L9 4M14 9L9 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        </a>
        <?php endif; ?>
    </div>

</div>

<?php if ($total > 1): ?>
<div class="wd-scroll-hint">
    <svg width="54" height="33" viewBox="0 0 36 22" fill="none">
        <rect x="13" y="1" width="10" height="16" rx="5" stroke="currentColor" stroke-width="1.5"/>
        <rect x="17" y="4" width="2" height="4" rx="1" fill="currentColor" class="wd-wheel"/>
        <g id="wdPrev" style="cursor:pointer">
            <rect x="0" y="0" width="12" height="22" fill="transparent"/>
            <path d="M5 11 L1 11 M1 11 L4 8 M1 11 L4 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </g>
        <g id="wdNext" style="cursor:pointer">
            <rect x="24" y="0" width="12" height="22" fill="transparent"/>
            <path d="M31 11 L35 11 M35 11 L32 8 M35 11 L32 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </g>
    </svg>
</div>
<?php endif; ?>

<script>
(function () {
    const col     = document.getElementById('wdLeft');
    const current = document.getElementById('wdCurrent');
    const slots   = col.querySelectorAll('.wd-image-slot');
    const total   = slots.length;

    function getIdx() {
        return Math.round(col.scrollLeft / col.clientWidth);
    }

    function goTo(idx) {
        idx = Math.max(0, Math.min(total - 1, idx));
        col.scrollTo({ left: slots[idx].offsetLeft, behavior: 'smooth' });
    }

    function updateActive() {
        const idx = getIdx();
        slots.forEach((s, i) => s.classList.toggle('active', i === idx));
        if (current) current.textContent = String(idx + 1).padStart(2, '0');
    }

    col.addEventListener('scroll', () => requestAnimationFrame(updateActive));
    updateActive();

    document.getElementById('wdPrev')?.addEventListener('click', () => goTo(getIdx() - 1));
    document.getElementById('wdNext')?.addEventListener('click', () => goTo(getIdx() + 1));

    window.addEventListener('keydown', e => {
        if (e.key === 'ArrowLeft')  goTo(getIdx() - 1);
        if (e.key === 'ArrowRight') goTo(getIdx() + 1);
    });

    let wheelTimer;
    col.addEventListener('wheel', e => {
        e.preventDefault();
        col.style.scrollSnapType = 'none';
        col.scrollLeft += e.deltaY + e.deltaX;
        clearTimeout(wheelTimer);
        wheelTimer = setTimeout(() => { col.style.scrollSnapType = ''; }, 80);
    }, { passive: false });

    let isDragging = false, startX, startScroll;
    col.addEventListener('mousedown', e => {
        isDragging = true; startX = e.pageX; startScroll = col.scrollLeft;
        col.classList.add('grabbing');
        col.style.scrollSnapType = 'none';
    });
    window.addEventListener('mousemove', e => {
        if (!isDragging) return;
        col.scrollLeft = startScroll - (e.pageX - startX);
    });
    window.addEventListener('mouseup', () => {
        if (!isDragging) return;
        isDragging = false;
        col.classList.remove('grabbing');
        col.style.scrollSnapType = '';
    });

})();
</script>
</body>
</html>
