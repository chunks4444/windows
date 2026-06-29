<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/db.php';

$id     = (int)($_GET['id'] ?? 0);
$work   = null;
$images = [];
$next   = null;
try {
    $pdo = db();
    if ($id) {
        $stmt = $pdo->prepare('SELECT * FROM works WHERE id=? AND is_active=1');
        $stmt->execute([$id]);
        $work = $stmt->fetch();
    }
    if ($work) {
        // 이미지 목록
        $imgStmt = $pdo->prepare('SELECT image_url FROM work_images WHERE work_id=? ORDER BY sort_order, id');
        $imgStmt->execute([$id]);
        $images = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
        if (empty($images) && $work['image_url']) $images = [$work['image_url']];

        // 다음 작품
        $next = $pdo->prepare('SELECT id,title FROM works WHERE is_active=1 AND (sort_order > ? OR (sort_order=? AND id>?)) ORDER BY sort_order ASC, id ASC LIMIT 1');
        $next->execute([$work['sort_order'], $work['sort_order'], $work['id']]);
        $next = $next->fetch();
        if (!$next) $next = $pdo->query('SELECT id,title FROM works WHERE is_active=1 ORDER BY sort_order ASC, id ASC LIMIT 1')->fetch();
    }
} catch (Throwable $e) {
    $work = null;
}
if (!$work) { header('Location: /src/portfolio/'); exit; }

$total = count($images);
$desc  = strip_tags($work['description'] ?? '');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($work['title']) ?> — 평목</title>
    <meta name="description" content="<?= htmlspecialchars($desc) ?>">
    <?php require_once __DIR__ . '/../lib/meta.php'; ?>
    <link rel="icon" type="image/png" href="/src/assets/favicon.png">
    <link rel="apple-touch-icon" href="/src/assets/apple-touch-icon.png">
    <link rel="canonical" href="<?= htmlspecialchars(SITE_URL . '/src/portfolio/detail.php?id=' . $work['id']) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($work['title']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($desc) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($images[0] ?? SITE_DEFAULT_IMAGE) ?>">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<?php css_tag('/src/css/work-detail.css'); ?>
</head>
<body>
<?php require_once __DIR__ . '/../components/nav.php'; ?>

<div class="wd-page">

    <!-- ── 메인 뷰어 ── -->
    <div class="wd-viewer">
        <div class="wd-viewer-inner">

            <!-- 이전 버튼 -->
            <button class="wd-arrow wd-arrow-prev" id="wdPrev" <?= $total <= 1 ? 'disabled' : '' ?>>
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M10 3L5 8L10 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

            <!-- 메인 이미지 -->
            <img id="wdMainImg"
                 class="wd-main-img"
                 src="<?= htmlspecialchars($images[0]) ?>"
                 alt="<?= htmlspecialchars($work['title']) ?>">

            <!-- 다음 버튼 -->
            <button class="wd-arrow wd-arrow-next" id="wdNext" <?= $total <= 1 ? 'disabled' : '' ?>>
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M6 3L11 8L6 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

        </div>

        <?php if ($total > 1): ?>
        <!-- 카운터 -->
        <div class="wd-counter">
            <strong id="wdCurNum">01</strong> / <?= sprintf('%02d', $total) ?>
        </div>

        <!-- 썸네일 스트립 -->
        <div class="wd-strip" id="wdStrip">
            <div class="wd-thumbs">
                <?php foreach ($images as $i => $img): ?>
                <div class="wd-thumb <?= $i === 0 ? 'active' : '' ?>" data-idx="<?= $i ?>">
                    <img src="<?= htmlspecialchars($img) ?>"
                         alt="<?= htmlspecialchars($work['title']) ?> <?= $i + 1 ?>">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- ── 정보 바 ── -->
    <div class="wd-info-bar">
      <div class="wd-info-bar-inner">
        <div class="wd-info-left">
            <a href="/src/portfolio/" class="wd-back">
                <svg width="13" height="13" viewBox="0 0 14 14" fill="none">
                    <path d="M9 2L4 7L9 12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Portfolio
            </a>
        </div>

        <div class="wd-info-center">
            <h1 class="wd-title"><?= htmlspecialchars($work['title']) ?></h1>
        </div>

        <?php if ($next && $next['id'] !== $work['id']): ?>
        <a class="wd-next-link" href="/src/portfolio/detail.php?id=<?= $next['id'] ?>">
            <span class="wd-next-label">next</span>
            <?= htmlspecialchars($next['title']) ?>
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <path d="M3 7H11M11 7L7 3M11 7L7 11" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
        <?php endif; ?>
      </div>
    </div>

</div>

<script>
(function () {
    const mainImg = document.getElementById('wdMainImg');
    const prevBtn = document.getElementById('wdPrev');
    const nextBtn = document.getElementById('wdNext');
    const curNum  = document.getElementById('wdCurNum');
    const thumbs  = document.querySelectorAll('.wd-thumb');
    const images  = <?= json_encode(array_values($images)) ?>;
    const total   = images.length;
    let current   = 0;

    function goTo(idx) {
        if (idx < 0 || idx >= total) return;
        current = idx;

        mainImg.classList.add('fading');
        setTimeout(() => {
            mainImg.src = images[current];
            mainImg.classList.remove('fading');
        }, 200);

        if (curNum) curNum.textContent = String(current + 1).padStart(2, '0');

        thumbs.forEach((t, i) => t.classList.toggle('active', i === current));

        // 활성 썸네일이 보이도록 스크롤
        const activeThumb = document.querySelector('.wd-thumb.active');
        if (activeThumb) {
            activeThumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
    }

    prevBtn?.addEventListener('click', () => goTo(current - 1));
    nextBtn?.addEventListener('click', () => goTo(current + 1));

    thumbs.forEach(t => {
        t.addEventListener('click', () => goTo(parseInt(t.dataset.idx)));
    });

    window.addEventListener('keydown', e => {
        if (e.key === 'ArrowLeft')  goTo(current - 1);
        if (e.key === 'ArrowRight') goTo(current + 1);
    });

    // 마우스 휠로 이미지 전환
    mainImg?.addEventListener('wheel', e => {
        e.preventDefault();
        goTo(e.deltaY > 0 ? current + 1 : current - 1);
    }, { passive: false });
})();
</script>
<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
