<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/db.php';
$perPage = 9;
try {
    $pdo = db();

    $pdo->exec("
    CREATE TABLE IF NOT EXISTS blog_posts (
        id            INT UNSIGNED      NOT NULL AUTO_INCREMENT,
        title         VARCHAR(150)      NOT NULL DEFAULT '',
        slug          VARCHAR(200)      NOT NULL DEFAULT '',
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

    $totalCount = (int)$pdo->query('SELECT COUNT(*) FROM blog_posts WHERE is_active=1')->fetchColumn();
    $totalPages = max(1, (int)ceil($totalCount / $perPage));
    $pageNum    = max(1, min($totalPages, (int)($_GET['page'] ?? 1)));
    $offset     = ($pageNum - 1) * $perPage;

    $stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE is_active=1 ORDER BY sort_order, id LIMIT :limit OFFSET :offset');
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll();
    $total = count($posts);
} catch (Throwable $e) {
    $posts      = [];
    $total      = 0;
    $totalPages = 1;
    $pageNum    = 1;
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

    <!-- ── 글 목록 ── -->
    <section class="bg-list-section">
        <?php if ($total === 0): ?>
        <div class="bg-empty">아직 등록된 글이 없습니다.</div>
        <?php else: ?>
        <div class="bg-list">
            <?php foreach ($posts as $p): ?>
            <article class="bg-card" onclick="location.href='/src/blog/<?= rawurlencode($p['slug']) ?>'">
                <?php if ($p['thumbnail_url']): ?>
                <div class="bg-card-thumb">
                    <img src="<?= htmlspecialchars($p['thumbnail_url']) ?>"
                         alt="<?= htmlspecialchars($p['title']) ?>" loading="lazy">
                </div>
                <?php endif; ?>
                <div class="bg-card-body">
                    <h2 class="bg-card-title">
                        <a href="/src/blog/<?= rawurlencode($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a>
                    </h2>
                    <?php if ($p['summary']): ?>
                    <p class="bg-card-summary"><?= htmlspecialchars($p['summary']) ?></p>
                    <?php endif; ?>
                    <time class="bg-card-date" datetime="<?= date('Y-m-d', strtotime($p['created_at'])) ?>">
                        <?= date('Y.m.d', strtotime($p['created_at'])) ?>
                    </time>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav class="bg-pagination">
            <a class="bg-page-link bg-page-nav <?= $pageNum <= 1 ? 'disabled' : '' ?>"
               href="?page=<?= max(1, $pageNum - 1) ?>">‹ 이전</a>
            <span class="bg-page-indicator"><?= $pageNum ?> / <?= $totalPages ?></span>
            <a class="bg-page-link bg-page-nav <?= $pageNum >= $totalPages ? 'disabled' : '' ?>"
               href="?page=<?= min($totalPages, $pageNum + 1) ?>">다음 ›</a>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </section>

</div>
<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
