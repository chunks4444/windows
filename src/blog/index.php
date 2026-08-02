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
        is_featured   TINYINT(1)        NOT NULL DEFAULT 0,
        view_count    INT UNSIGNED      NOT NULL DEFAULT 0,
        created_at    DATETIME          NOT NULL DEFAULT NOW(),
        PRIMARY KEY (id),
        UNIQUE KEY uq_blog_posts_slug (slug),
        KEY idx_blog_posts_sort (sort_order, is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS blog_series (
        id           INT UNSIGNED      NOT NULL AUTO_INCREMENT,
        name         VARCHAR(80)       NOT NULL,
        tagline      VARCHAR(200)      NOT NULL DEFAULT '',
        is_completed TINYINT(1)        NOT NULL DEFAULT 0,
        sort_order   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
        PRIMARY KEY (id),
        UNIQUE KEY uq_series_name (name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS blog_view_snapshots (
        id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
        snapshot_date DATE         NOT NULL,
        total_views   INT UNSIGNED NOT NULL DEFAULT 0,
        PRIMARY KEY (id),
        UNIQUE KEY uq_bvs_date (snapshot_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $totalCount = (int) $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE is_active = 1")->fetchColumn();

    // 블로그 전체 조회수 총합 일별 스냅샷은 MySQL EVENT(ev_blog_view_daily_snapshot)가
    // 매일 자정 정각에 기록한다 (schema.sql 참고) — 방문 트래픽에 따라 스냅샷 시각이
    // 들쭉날쭉해지는 걸 막기 위해 요청 트리거 방식에서 배치 방식으로 전환함.

    $perPage    = 10;
    $totalPages = max(1, (int) ceil($totalCount / $perPage));
    $page       = max(1, min($totalPages, (int)($_GET['page'] ?? 1)));
    $offset     = ($page - 1) * $perPage;

    $stmt = $pdo->prepare("
        SELECT p.id, p.title, p.slug, p.summary, p.thumbnail_url, p.created_at, p.view_count,
               p.series_order, s.name AS series_name
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

    // 상단 피처 캐로셀 — 관리자가 직접 선택한 글만(is_featured), 날짜 무관, sort_order 순
    $featurePosts = $pdo->query("
        SELECT p.id, p.title, p.slug, p.thumbnail_url, p.series_order, s.name AS series_name
        FROM blog_posts p
        LEFT JOIN blog_series s ON s.id = p.series_id
        WHERE p.is_active = 1 AND p.is_featured = 1
        ORDER BY p.sort_order, p.id
        LIMIT 5
    ")->fetchAll();

    // 사이드바 — 시리즈별 카드 (시리즈명 + 태그라인 + 최근 글 최대 3개), 시리즈의 최신 발행일 순으로 정렬
    $seriesRows = $pdo->query("
        SELECT p.id, p.title, p.slug, p.created_at, p.series_id, p.series_order,
               s.name AS series_name, s.tagline AS series_tagline, s.sort_order AS series_sort, s.is_completed
        FROM blog_posts p
        JOIN blog_series s ON s.id = p.series_id
        WHERE p.is_active = 1
        ORDER BY p.created_at DESC
    ")->fetchAll();
    $seriesCards = [];
    foreach ($seriesRows as $r) {
        $sid = $r['series_id'];
        if (!isset($seriesCards[$sid])) {
            $seriesCards[$sid] = ['name' => $r['series_name'], 'tagline' => $r['series_tagline'], 'posts' => [], 'total' => 0, 'latest' => $r['created_at'], 'is_completed' => $r['is_completed']];
        }
        $seriesCards[$sid]['total']++;
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
    <?php
    require_once __DIR__ . '/../lib/meta.php';
    meta_tags($page > 1 ? ['canonical' => SITE_URL . '/blog/?page=' . $page] : null);
    ?>
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
                        <a href="/blog/<?= rawurlencode($fp['slug']) ?>" class="bg-feature-link">
                            <img src="<?= htmlspecialchars($fp['thumbnail_url']) ?>" class="bg-feature-img" alt="">
                            <div class="bg-feature-caption">
                                <?php if ($fp['series_name']): ?>
                                <span class="bg-feature-badge"><?= htmlspecialchars($fp['series_name']) ?><?= $fp['series_order'] ? ' · ' . (int)$fp['series_order'] . '화' : '' ?></span>
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
                <?php foreach ($pagePosts as $p): ?>
                <li class="bg-ranked-item">
                    <a class="bg-ranked-link" href="/blog/<?= rawurlencode($p['slug']) ?>">
                        <div class="bg-ranked-text">
                            <p class="bg-ranked-cat">
                                <?php if ($p['series_name']): ?><?= htmlspecialchars($p['series_name']) ?><?= $p['series_order'] ? ' · ' . (int)$p['series_order'] . '화' : '' ?> · <?php endif; ?><?= date('Y.m.d', strtotime($p['created_at'])) ?>
                            </p>
                            <h3 class="bg-ranked-title"><?= htmlspecialchars($p['title']) ?></h3>
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
                <h3 class="bg-side-card-title">
                    <?= htmlspecialchars($sc['name']) ?>
                    <span class="bg-side-card-meta">
                        <span class="bg-side-card-count">전체 <?= (int)$sc['total'] ?>화</span>
                        <span class="bg-side-card-status <?= $sc['is_completed'] ? 'is-completed' : 'is-ongoing' ?>"><?= $sc['is_completed'] ? '완결' : '연재중' ?></span>
                    </span>
                </h3>
                <?php if ($sc['tagline']): ?>
                <p class="bg-side-card-tagline">"<?= htmlspecialchars($sc['tagline']) ?>"</p>
                <?php endif; ?>
                <ul class="bg-side-card-posts">
                    <?php foreach ($sc['posts'] as $sp): ?>
                    <li>
                        <a href="/blog/<?= rawurlencode($sp['slug']) ?>"><?= $sp['series_order'] ? (int)$sp['series_order'] . '화 ' : '' ?><?= htmlspecialchars($sp['title']) ?></a>
                        <span class="bg-side-card-date"><?= date('Y.m.d', strtotime($sp['created_at'])) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
            <?php if (empty($seriesCards)): ?>
            <p class="bg-side-empty">아직 등록된 시리즈가 없습니다.</p>
            <?php else: ?>
            <button type="button" id="bgSideMoreBtn" class="bg-side-more-btn">
                더보기 <i class="bi bi-chevron-down"></i>
            </button>
            <?php endif; ?>
        </aside>
    </div>
    <?php endif; ?>

</div>
<?php if (!empty($seriesCards)): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var aside = document.querySelector('.bg-sidebar');
    var btn = document.getElementById('bgSideMoreBtn');
    if (!aside || !btn) return;
    var cards = aside.querySelectorAll('.bg-side-card');
    if (cards.length < 2) return;

    // 화면(뷰포트) 첫 화면에 다 안 들어가는 카드부터 접는다 — 첫 카드는 항상 노출
    var firstHidden = -1;
    for (var i = 1; i < cards.length; i++) {
        if (cards[i].getBoundingClientRect().bottom > window.innerHeight) { firstHidden = i; break; }
    }
    if (firstHidden === -1) return;

    for (var i = firstHidden; i < cards.length; i++) cards[i].classList.add('bg-side-card-more');
    btn.classList.add('is-visible');
    btn.addEventListener('click', function () {
        cards.forEach(function (c) { c.classList.remove('bg-side-card-more'); });
        btn.remove();
    });
});
</script>
<?php endif; ?>
<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
