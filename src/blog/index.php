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

    $totalCount = (int) $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE is_active = 1")->fetchColumn();
    $perPage    = 10;
    $totalPages = max(1, (int) ceil($totalCount / $perPage));
    $page       = max(1, min($totalPages, (int)($_GET['page'] ?? 1)));
    $offset     = ($page - 1) * $perPage;

    $stmt = $pdo->prepare("
        SELECT p.id, p.title, p.slug, p.summary, p.thumbnail_url, p.created_at, p.view_count,
               s.name AS series_name
        FROM blog_posts p
        LEFT JOIN blog_series s ON s.id = p.series_id
        WHERE p.is_active = 1
        ORDER BY p.sort_order, p.id
        LIMIT :lim OFFSET :off
    ");
    $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $pagePosts = $stmt->fetchAll();

    // 상단 피처 캐로셀 — 현재 페이지의 글 중 썸네일 있는 것 위주로 최대 5개
    $featurePosts = [];
    foreach ($pagePosts as $p) {
        if ($p['thumbnail_url']) $featurePosts[] = $p;
        if (count($featurePosts) >= 5) break;
    }

    // 사이드바 — 시리즈별 카드 (시리즈명 + 태그라인 + 최근 글 최대 3개)
    $seriesRows = $pdo->query("
        SELECT p.id, p.title, p.slug, p.created_at, p.series_id,
               s.name AS series_name, s.tagline AS series_tagline, s.sort_order AS series_sort
        FROM blog_posts p
        JOIN blog_series s ON s.id = p.series_id
        WHERE p.is_active = 1
        ORDER BY s.sort_order, s.id, p.created_at DESC
    ")->fetchAll();
    $seriesCards = [];
    foreach ($seriesRows as $r) {
        $sid = $r['series_id'];
        if (!isset($seriesCards[$sid])) {
            $seriesCards[$sid] = ['name' => $r['series_name'], 'tagline' => $r['series_tagline'], 'posts' => []];
        }
        if (count($seriesCards[$sid]['posts']) < 3) $seriesCards[$sid]['posts'][] = $r;
    }
} catch (Throwable $e) {
    $totalCount   = 0;
    $totalPages   = 1;
    $page         = 1;
    $pagePosts    = [];
    $featurePosts = [];
    $seriesCards  = [];
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
    <link rel="canonical" href="<?= htmlspecialchars(SITE_URL . '/src/blog/' . ($page > 1 ? '?page=' . $page : '')) ?>">
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

    <?php if ($totalCount === 0): ?>
    <section class="bg-list-section">
        <div class="bg-empty">아직 등록된 글이 없습니다.</div>
    </section>
    <?php else: ?>

    <div class="bg-tistory-layout">
        <!-- ── 메인: 피처 캐로셀 + 순위 목록 + 페이지네이션 ── -->
        <main class="bg-main">

            <?php if ($featurePosts): ?>
            <div id="blogFeature" class="carousel slide bg-feature" data-bs-ride="carousel" data-bs-interval="5000">
                <div class="carousel-indicators">
                    <?php foreach ($featurePosts as $i => $fp): ?>
                    <button type="button" data-bs-target="#blogFeature" data-bs-slide-to="<?= $i ?>"
                        <?= $i === 0 ? 'class="active" aria-current="true"' : '' ?>
                        aria-label="Slide <?= $i + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>
                <div class="carousel-inner">
                    <?php foreach ($featurePosts as $i => $fp): ?>
                    <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                        <a href="/src/blog/<?= rawurlencode($fp['slug']) ?>" class="bg-feature-link">
                            <img src="<?= htmlspecialchars($fp['thumbnail_url']) ?>" class="bg-feature-img" alt="<?= htmlspecialchars($fp['title']) ?>">
                            <div class="bg-feature-caption">
                                <?php if ($fp['series_name']): ?>
                                <span class="bg-feature-badge"><?= htmlspecialchars($fp['series_name']) ?></span>
                                <?php endif; ?>
                                <h2 class="bg-feature-title">"<?= htmlspecialchars($fp['title']) ?>"</h2>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#blogFeature" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#blogFeature" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
            </div>
            <?php endif; ?>

            <ol class="bg-ranked-list" start="<?= $offset + 1 ?>">
                <?php foreach ($pagePosts as $i => $p): ?>
                <li class="bg-ranked-item">
                    <a class="bg-ranked-link" href="/src/blog/<?= rawurlencode($p['slug']) ?>">
                        <div class="bg-ranked-text">
                            <?php if ($p['series_name']): ?>
                            <p class="bg-ranked-cat"><?= htmlspecialchars($p['series_name']) ?></p>
                            <?php endif; ?>
                            <h3 class="bg-ranked-title"><span class="bg-ranked-num"><?= $offset + $i + 1 ?></span><?= htmlspecialchars($p['title']) ?></h3>
                            <?php if ($p['summary']): ?>
                            <p class="bg-ranked-summary"><?= htmlspecialchars($p['summary']) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if ($p['thumbnail_url']): ?>
                        <div class="bg-ranked-thumb">
                            <img src="<?= htmlspecialchars($p['thumbnail_url']) ?>" alt="" loading="lazy">
                        </div>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ol>

            <?php if ($totalPages > 1): ?>
            <nav class="bg-pagination" aria-label="페이지">
                <a class="bg-page-arrow <?= $page <= 1 ? 'disabled' : '' ?>"
                   href="?page=<?= max(1, $page - 1) ?>" aria-label="이전 페이지"><i class="bi bi-chevron-left"></i></a>
                <?php for ($pn = 1; $pn <= $totalPages; $pn++): ?>
                <a class="bg-page-num <?= $pn === $page ? 'active' : '' ?>" href="?page=<?= $pn ?>"><?= $pn ?></a>
                <?php endfor; ?>
                <a class="bg-page-arrow <?= $page >= $totalPages ? 'disabled' : '' ?>"
                   href="?page=<?= min($totalPages, $page + 1) ?>" aria-label="다음 페이지"><i class="bi bi-chevron-right"></i></a>
            </nav>
            <?php endif; ?>
        </main>

        <!-- ── 사이드바: 시리즈 카드 ── -->
        <aside class="bg-sidebar">
            <?php foreach ($seriesCards as $sc): ?>
            <div class="bg-side-card">
                <h3 class="bg-side-card-title"><?= htmlspecialchars($sc['name']) ?></h3>
                <?php if ($sc['tagline']): ?>
                <p class="bg-side-card-tagline">"<?= htmlspecialchars($sc['tagline']) ?>"</p>
                <?php endif; ?>
                <ul class="bg-side-card-posts">
                    <?php foreach ($sc['posts'] as $sp): ?>
                    <li>
                        <a href="/src/blog/<?= rawurlencode($sp['slug']) ?>"><?= htmlspecialchars($sp['title']) ?></a>
                        <span class="bg-side-card-date"><?= date('Y.m.d', strtotime($sp['created_at'])) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
            <?php if (empty($seriesCards)): ?>
            <p class="bg-side-empty">아직 등록된 시리즈가 없습니다.</p>
            <?php endif; ?>
        </aside>
    </div>
    <?php endif; ?>

</div>
<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
