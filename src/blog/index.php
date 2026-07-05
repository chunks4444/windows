<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/db.php';
try {
    $pdo = db();

    $pdo->exec("
    CREATE TABLE IF NOT EXISTS blog_posts (
        id            INT UNSIGNED      NOT NULL AUTO_INCREMENT,
        title         VARCHAR(150)      NOT NULL DEFAULT '',
        slug          VARCHAR(200)      NOT NULL DEFAULT '',
        series_id     INT UNSIGNED      NULL,
        series_order  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
        related_drawing_id INT UNSIGNED NULL,
        related_engine VARCHAR(20)      NULL,
        question      VARCHAR(200)      NOT NULL DEFAULT '',
        summary       VARCHAR(300)      NOT NULL DEFAULT '',
        cta_text      VARCHAR(200)      NOT NULL DEFAULT '',
        content       TEXT              NOT NULL,
        thumbnail_url VARCHAR(500)      NOT NULL DEFAULT '',
        sort_order    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
        is_active     TINYINT(1)        NOT NULL DEFAULT 1,
        view_count    INT UNSIGNED      NOT NULL DEFAULT 0,
        created_at    DATETIME          NOT NULL DEFAULT NOW(),
        PRIMARY KEY (id),
        UNIQUE KEY uq_blog_posts_slug (slug),
        KEY idx_blog_posts_sort (sort_order, is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS blog_series (
        id         INT UNSIGNED      NOT NULL AUTO_INCREMENT,
        name       VARCHAR(80)       NOT NULL,
        tagline    VARCHAR(200)      NOT NULL DEFAULT '',
        sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
        PRIMARY KEY (id),
        UNIQUE KEY uq_series_name (name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $allPosts = $pdo->query("
        SELECT p.id, p.title, p.slug, p.summary, p.question, p.thumbnail_url, p.created_at,
               p.series_id, p.series_order,
               s.name AS series_name, s.tagline AS series_tagline, s.sort_order AS series_sort
        FROM blog_posts p
        LEFT JOIN blog_series s ON s.id = p.series_id
        WHERE p.is_active = 1
        ORDER BY (s.sort_order IS NULL), s.sort_order, s.id, p.series_order, p.id
    ")->fetchAll();
    $total = count($allPosts);

    // 시리즈별로 묶기 (없는 글은 '기타'로)
    $seriesGroups = [];
    $noSeries     = [];
    foreach ($allPosts as $p) {
        if ($p['series_id']) {
            $sid = $p['series_id'];
            if (!isset($seriesGroups[$sid])) {
                $seriesGroups[$sid] = [
                    'name'    => $p['series_name'],
                    'tagline' => $p['series_tagline'],
                    'posts'   => [],
                ];
            }
            $seriesGroups[$sid]['posts'][] = $p;
        } else {
            $noSeries[] = $p;
        }
    }
} catch (Throwable $e) {
    $allPosts     = [];
    $total        = 0;
    $seriesGroups = [];
    $noSeries     = [];
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>창호 이야기 — 평목 공방 블로그</title>
    <meta name="description" content="평목 공방이 전하는 창호와 한옥 살창 이야기. 시공 사례와 제작 노트를 소개합니다.">
    <?php require_once __DIR__ . '/../lib/meta.php'; ?>
    <link rel="icon" type="image/png" href="/src/assets/favicon.png">
    <link rel="apple-touch-icon" href="/src/assets/apple-touch-icon.png">
    <link rel="canonical" href="<?= htmlspecialchars(SITE_URL . '/src/blog/') ?>">
    <meta property="og:title" content="창호 이야기 — 평목 공방 블로그">
    <meta property="og:description" content="평목 공방이 전하는 창호와 한옥 살창 이야기. 시공 사례와 제작 노트를 소개합니다.">
    <meta property="og:image" content="<?= htmlspecialchars(SITE_DEFAULT_IMAGE) ?>">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<?php css_tag('/src/css/blog.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="bg-page">

    <!-- ── 페이지 헤더 ── -->
    <div class="bg-hero">
        <div class="bg-hero-inner">
            <p class="bg-hero-label">Blog</p>
            <h1>창호 이야기</h1>
            <p class="bg-hero-sub">평목 공방이 전하는 창호와 한옥 살창 이야기입니다.</p>
        </div>
    </div>

    <?php if ($total === 0): ?>
    <section class="bg-list-section">
        <div class="bg-empty">아직 등록된 글이 없습니다.</div>
    </section>
    <?php else: ?>

    <!-- ── 보기 전환 ── -->
    <div class="bg-view-toggle">
        <button class="bg-view-btn active" data-view="series">시리즈로 보기</button>
        <button class="bg-view-btn" data-view="question">질문으로 찾기</button>
    </div>

    <!-- ── 시리즈 허브 ── -->
    <section class="bg-list-section bg-view-panel" id="bgViewSeries">
        <div class="bg-series-grid">
            <?php foreach ($seriesGroups as $group):
                $first = $group['posts'][0];
                $count = count($group['posts']);
            ?>
            <article class="bg-series-card" onclick="location.href='/src/blog/<?= rawurlencode($first['slug']) ?>'">
                <?php if ($first['thumbnail_url']): ?>
                <div class="bg-series-thumb">
                    <img src="<?= htmlspecialchars($first['thumbnail_url']) ?>" alt="<?= htmlspecialchars($group['name']) ?>" loading="lazy">
                </div>
                <?php endif; ?>
                <div class="bg-series-body">
                    <p class="bg-series-count">전 <?= $count ?>편 · 읽는 순서대로</p>
                    <h2 class="bg-series-name"><?= htmlspecialchars($group['name']) ?></h2>
                    <?php if ($group['tagline']): ?>
                    <p class="bg-series-tagline">"<?= htmlspecialchars($group['tagline']) ?>"</p>
                    <?php endif; ?>
                    <a class="bg-series-start" href="/src/blog/<?= rawurlencode($first['slug']) ?>">1편부터 읽기 →</a>
                </div>
            </article>
            <?php endforeach; ?>

            <?php foreach ($noSeries as $p): ?>
            <article class="bg-series-card" onclick="location.href='/src/blog/<?= rawurlencode($p['slug']) ?>'">
                <?php if ($p['thumbnail_url']): ?>
                <div class="bg-series-thumb">
                    <img src="<?= htmlspecialchars($p['thumbnail_url']) ?>" alt="<?= htmlspecialchars($p['title']) ?>" loading="lazy">
                </div>
                <?php endif; ?>
                <div class="bg-series-body">
                    <h2 class="bg-series-name"><?= htmlspecialchars($p['title']) ?></h2>
                    <?php if ($p['summary']): ?>
                    <p class="bg-series-tagline"><?= htmlspecialchars($p['summary']) ?></p>
                    <?php endif; ?>
                    <a class="bg-series-start" href="/src/blog/<?= rawurlencode($p['slug']) ?>">읽어보기 →</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ── 질문으로 찾기 ── -->
    <section class="bg-list-section bg-view-panel" id="bgViewQuestion" style="display:none;">
        <ul class="bg-question-list">
            <?php foreach ($allPosts as $p): ?>
            <li class="bg-question-item">
                <a href="/src/blog/<?= rawurlencode($p['slug']) ?>">
                    <span class="bg-question-q">Q. <?= htmlspecialchars($p['question'] ?: $p['title']) ?></span>
                    <span class="bg-question-meta">
                        <?php if ($p['series_name']): ?><?= htmlspecialchars($p['series_name']) ?> · <?php endif; ?>
                        <?= date('Y.m.d', strtotime($p['created_at'])) ?>
                    </span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <?php endif; ?>

</div>
<script>
document.querySelectorAll('.bg-view-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.bg-view-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const view = btn.dataset.view;
        document.getElementById('bgViewSeries').style.display   = view === 'series'   ? '' : 'none';
        document.getElementById('bgViewQuestion').style.display = view === 'question' ? '' : 'none';
    });
});
</script>
<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
