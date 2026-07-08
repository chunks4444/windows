<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/db.php';
try {
$pdo = db();

$pdo->exec("
CREATE TABLE IF NOT EXISTS works (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL DEFAULT '',
    description VARCHAR(300) NOT NULL DEFAULT '',
    image_url VARCHAR(500) NOT NULL DEFAULT '',
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    KEY idx_works_sort (sort_order, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS work_images (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    work_id INT UNSIGNED NOT NULL,
    image_url VARCHAR(500) NOT NULL DEFAULT '',
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_work_images_work (work_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

if ((int)$pdo->query('SELECT COUNT(*) FROM works')->fetchColumn() === 0) {
    $stmt = $pdo->prepare('INSERT INTO works (title, description, image_url, sort_order) VALUES (?,?,?,?)');
    foreach ([
        ['한옥 중문 정자살', '경기도 양평',  'https://picsum.photos/seed/w1/500/700', 0],
        ['거실 미서기창',    '서울 마포',    'https://picsum.photos/seed/w2/500/680', 1],
        ['카페 파티션',      '서울 성수',    'https://picsum.photos/seed/w3/500/720', 2],
        ['서재 창호',        '경기 용인',    'https://picsum.photos/seed/w4/500/660', 3],
        ['현관 중문',        '서울 강남',    'https://picsum.photos/seed/w5/500/700', 4],
        ['다실 창호',        '전남 순천',    'https://picsum.photos/seed/w6/500/690', 5],
        ['침실 여닫이창',    '부산 해운대',  'https://picsum.photos/seed/w7/500/710', 6],
        ['갤러리 파티션',    '서울 종로',    'https://picsum.photos/seed/w8/500/680', 7],
    ] as $s) $stmt->execute($s);
}

if ((int)$pdo->query('SELECT COUNT(*) FROM work_images')->fetchColumn() === 0) {
    $works_all = $pdo->query('SELECT id, sort_order FROM works WHERE is_active=1 ORDER BY sort_order, id')->fetchAll();
    $imgStmt   = $pdo->prepare('INSERT INTO work_images (work_id, image_url, sort_order) VALUES (?,?,?)');
    $sizes = [[600,800],[800,600],[600,800],[800,500],[500,700]];
    foreach ($works_all as $w) {
        $pfx = 'wi' . $w['id'];
        foreach ($sizes as $j => [$width, $height]) {
            $imgStmt->execute([$w['id'], "https://picsum.photos/seed/{$pfx}_{$j}/{$width}/{$height}", $j]);
        }
    }
}

$works = $pdo->query('SELECT * FROM works WHERE is_active=1 ORDER BY sort_order, id')->fetchAll();
$total = count($works);

// 태그 테이블 생성 + 시드
$pdo->exec("
CREATE TABLE IF NOT EXISTS work_tags (
    id         INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    name       VARCHAR(50)       NOT NULL DEFAULT '',
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active  TINYINT(1)        NOT NULL DEFAULT 1,
    created_at DATETIME          NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    KEY idx_wt_sort (sort_order, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
if ((int)$pdo->query('SELECT COUNT(*) FROM work_tags')->fetchColumn() === 0) {
    $ts = $pdo->prepare('INSERT INTO work_tags (name, sort_order) VALUES (?,?)');
    foreach (['중문','거실','카페','서재','현관','다실','침실','갤러리','한옥','파티션'] as $i => $t)
        $ts->execute([$t, $i]);
}
$tags = array_merge(['전체'], $pdo->query('SELECT name FROM work_tags WHERE is_active=1 ORDER BY sort_order, id')->fetchAll(PDO::FETCH_COLUMN));
} catch (Throwable $e) {
    $works = [];
    $total = 0;
    $tags  = ['전체'];
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once __DIR__ . '/../lib/meta.php'; meta_tags(); ?>
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php css_tag('/src/css/work.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="wk-page">

    <!-- ── 페이지 헤더 ── -->
    <div class="wk-hero">
        <div class="wk-hero-inner">
            <p class="wk-hero-label">Portfolio</p>
            <h1>포트폴리오</h1>
            <p class="wk-hero-sub">
                평목 공방에서 완성된 창호 작품들입니다.&ensp;
                <span class="wk-count-badge"><?= $total ?>개 작품</span>
            </p>
        </div>
    </div>

    <!-- ── 필터 바 (sticky) ── -->
    <div class="wk-filter-bar">
        <div class="wk-filter-inner">
            <?php foreach ($tags as $i => $tag): ?>
            <button class="wk-tag<?= $i === 0 ? ' active' : '' ?>"
                    data-tag="<?= htmlspecialchars($tag) ?>">
                <?= htmlspecialchars($tag) ?>
            </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── 그리드 ── -->
    <section class="wk-grid-section">
        <div class="wk-grid" id="wkGrid">

            <?php foreach ($works as $i => $w):
                $desc = strip_tags($w['description']);
            ?>
            <div class="wk-card"
                 data-title="<?= htmlspecialchars($w['title']) ?>"
                 data-desc="<?= htmlspecialchars($desc) ?>"
                 role="button"
                 onclick="location.href='/portfolio/<?= rawurlencode($w['slug']) ?>'">
                <img src="<?= htmlspecialchars($w['image_url']) ?>"
                     alt="<?= htmlspecialchars($w['title']) ?>"
                     loading="lazy">
                <div class="wk-card-grad"></div>
                <div class="wk-card-num"><?= sprintf('%02d', $i + 1) ?></div>
                <div class="wk-card-body">
                    <div class="wk-card-title"><?= htmlspecialchars($w['title']) ?></div>
                    <?php if ($desc): ?>
                    <div class="wk-card-desc"><?= htmlspecialchars($desc) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="wk-empty" id="wkEmpty" style="display:none;">
                해당 카테고리의 작품이 없습니다.
            </div>

        </div>
    </section>

</div>

<script>
(function () {
    const tags  = document.querySelectorAll('.wk-tag');
    const cards = document.querySelectorAll('.wk-card');
    const emptyEl = document.getElementById('wkEmpty');

    tags.forEach(btn => {
        btn.addEventListener('click', () => {
            tags.forEach(t => t.classList.remove('active'));
            btn.classList.add('active');

            const tag = btn.dataset.tag;
            let visible = 0;
            cards.forEach(card => {
                const match = tag === '전체' ||
                    card.dataset.title.includes(tag) ||
                    card.dataset.desc.includes(tag);
                card.classList.toggle('wk-hidden', !match);
                if (match) visible++;
            });
            emptyEl.style.display = visible === 0 ? 'block' : 'none';
        });
    });
})();
</script>
<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
