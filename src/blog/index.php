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

    // 좌측 "시리즈로 읽기" 목록용 그룹핑 (시리즈 없는 글은 제외)
    $seriesGroups = [];
    foreach ($allPosts as $p) {
        if (!$p['series_id']) continue;
        $sid = $p['series_id'];
        if (!isset($seriesGroups[$sid])) {
            $seriesGroups[$sid] = [
                'name'    => $p['series_name'],
                'tagline' => $p['series_tagline'],
                'posts'   => [],
            ];
        }
        $seriesGroups[$sid]['posts'][] = $p;
    }
    // 우측 "전체 글" 피드는 최신순
    $feedPosts = $allPosts;
    usort($feedPosts, fn($a, $b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));
} catch (Throwable $e) {
    $allPosts     = [];
    $total        = 0;
    $seriesGroups = [];
    $feedPosts    = [];
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

    <div class="bg-layout">
        <!-- ── 좌측 1/3: 시리즈물 ── -->
        <aside class="bg-series-col">
            <h2 class="bg-col-title">시리즈로 읽기</h2>
            <?php foreach ($seriesGroups as $group):
                $first = $group['posts'][0];
                $count = count($group['posts']);
            ?>
            <a class="bg-series-mini" href="/src/blog/<?= rawurlencode($first['slug']) ?>">
                <?php if ($first['thumbnail_url']): ?>
                <div class="bg-series-mini-thumb">
                    <img src="<?= htmlspecialchars($first['thumbnail_url']) ?>" alt="<?= htmlspecialchars($group['name']) ?>" loading="lazy">
                </div>
                <?php endif; ?>
                <div class="bg-series-mini-body">
                    <p class="bg-series-mini-count">전 <?= $count ?>편</p>
                    <h3 class="bg-series-mini-name"><?= htmlspecialchars($group['name']) ?></h3>
                    <?php if ($group['tagline']): ?>
                    <p class="bg-series-mini-tagline">"<?= htmlspecialchars($group['tagline']) ?>"</p>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
            <?php if (empty($seriesGroups)): ?>
            <p class="bg-series-mini-empty">아직 등록된 시리즈가 없습니다.</p>
            <?php endif; ?>
        </aside>

        <!-- ── 우측 2/3: 각개 블로그 리스트 ── -->
        <section class="bg-posts-col">
            <h2 class="bg-col-title">전체 글</h2>
            <?php foreach ($feedPosts as $p): ?>
            <a class="bg-post-row" href="/src/blog/<?= rawurlencode($p['slug']) ?>">
                <?php if ($p['thumbnail_url']): ?>
                <div class="bg-post-row-thumb">
                    <img src="<?= htmlspecialchars($p['thumbnail_url']) ?>" alt="" loading="lazy">
                </div>
                <?php endif; ?>
                <div class="bg-post-row-body">
                    <p class="bg-post-row-meta">
                        <?php if ($p['series_name']): ?><span class="bg-post-row-series"><?= htmlspecialchars($p['series_name']) ?></span> · <?php endif; ?>
                        <?= date('Y.m.d', strtotime($p['created_at'])) ?>
                    </p>
                    <h3 class="bg-post-row-title"><?= htmlspecialchars($p['title']) ?></h3>
                    <?php if ($p['summary']): ?>
                    <p class="bg-post-row-summary"><?= htmlspecialchars($p['summary']) ?></p>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </section>
    </div>
    <?php endif; ?>

</div>
<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
