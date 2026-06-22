<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/db.php';
$perPage = 10;
try {
    $pdo = db();

    $pdo->exec("
    CREATE TABLE IF NOT EXISTS blog_posts (
        id            INT UNSIGNED      NOT NULL AUTO_INCREMENT,
        title         VARCHAR(150)      NOT NULL DEFAULT '',
        summary       VARCHAR(300)      NOT NULL DEFAULT '',
        content       TEXT              NOT NULL,
        thumbnail_url VARCHAR(500)      NOT NULL DEFAULT '',
        sort_order    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
        is_active     TINYINT(1)        NOT NULL DEFAULT 1,
        created_at    DATETIME          NOT NULL DEFAULT NOW(),
        PRIMARY KEY (id),
        KEY idx_blog_posts_sort (sort_order, is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $totalCount = (int)$pdo->query('SELECT COUNT(*) FROM blog_posts WHERE is_active=1')->fetchColumn();
    $totalPages = max(1, (int)ceil($totalCount / $perPage));
    $page       = max(1, min($totalPages, (int)($_GET['page'] ?? 1)));
    $offset     = ($page - 1) * $perPage;

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
    $page       = 1;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>창호 이야기 — 평목 공방 블로그</title>
    <meta name="description" content="평목 공방이 전하는 창호와 한옥 살창 이야기. 시공 사례와 제작 노트를 소개합니다.">
    <meta property="og:title" content="창호 이야기 — 평목 공방 블로그">
    <meta property="og:description" content="평목 공방이 전하는 창호와 한옥 살창 이야기. 시공 사례와 제작 노트를 소개합니다.">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../lib/meta.php'; ?>
<?php css_tag('/src/css/blog.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="bg-page">

    <!-- ── 페이지 헤더 ── -->
    <div class="bg-hero">
        <div class="bg-hero-inner">
            <p class="bg-hero-label">Blog</p>
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
            <article class="bg-card" onclick="location.href='/src/blog/detail.php?id=<?= $p['id'] ?>'">
                <?php if ($p['thumbnail_url']): ?>
                <div class="bg-card-thumb">
                    <img src="<?= htmlspecialchars($p['thumbnail_url']) ?>"
                         alt="<?= htmlspecialchars($p['title']) ?>" loading="lazy">
                </div>
                <?php endif; ?>
                <div class="bg-card-body">
                    <h2 class="bg-card-title">
                        <a href="/src/blog/detail.php?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['title']) ?></a>
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
            <a class="bg-page-link <?= $page <= 1 ? 'disabled' : '' ?>"
               href="?page=<?= max(1, $page - 1) ?>">‹</a>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a class="bg-page-link <?= $i === $page ? 'active' : '' ?>" href="?page=<?= $i ?>"><?= $i ?></a>
            <?php endfor; ?>
            <a class="bg-page-link <?= $page >= $totalPages ? 'disabled' : '' ?>"
               href="?page=<?= min($totalPages, $page + 1) ?>">›</a>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </section>

</div>
<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
