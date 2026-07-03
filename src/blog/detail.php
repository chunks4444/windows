<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/db.php';

$id   = (int)($_GET['id'] ?? 0);
$slug = trim($_GET['slug'] ?? '');
$post = null;
$prev = null;
$next = null;
try {
    $pdo = db();
    if ($slug !== '') {
        $stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE slug=? AND is_active=1');
        $stmt->execute([$slug]);
        $post = $stmt->fetch();
    } elseif ($id) {
        // 예전 ?id= 링크 호환 — 새 슬러그 주소로 301 리다이렉트 (SEO 중복 콘텐츠 방지)
        $stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE id=? AND is_active=1');
        $stmt->execute([$id]);
        $post = $stmt->fetch();
        if ($post) { header('Location: /src/blog/' . rawurlencode($post['slug']), true, 301); exit; }
    }
    if ($post) {
        // 이전 / 다음 글
        $prev = $pdo->prepare('SELECT id,title,slug FROM blog_posts WHERE is_active=1 AND (sort_order < ? OR (sort_order=? AND id<?)) ORDER BY sort_order DESC, id DESC LIMIT 1');
        $prev->execute([$post['sort_order'], $post['sort_order'], $post['id']]);
        $prev = $prev->fetch();

        $next = $pdo->prepare('SELECT id,title,slug FROM blog_posts WHERE is_active=1 AND (sort_order > ? OR (sort_order=? AND id>?)) ORDER BY sort_order ASC, id ASC LIMIT 1');
        $next->execute([$post['sort_order'], $post['sort_order'], $post['id']]);
        $next = $next->fetch();
    }
} catch (Throwable $e) {
    $post = null;
}
if (!$post) { header('Location: /src/blog/'); exit; }

$metaDesc = $post['summary'] ?: mb_substr(strip_tags($post['content']), 0, 120);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> — 평목 공방 블로그</title>
    <meta name="description" content="<?= htmlspecialchars($metaDesc) ?>">
    <?php require_once __DIR__ . '/../lib/meta.php'; ?>
    <link rel="icon" type="image/png" href="/src/assets/favicon.png">
    <link rel="apple-touch-icon" href="/src/assets/apple-touch-icon.png">
    <link rel="canonical" href="<?= htmlspecialchars(SITE_URL . '/src/blog/' . rawurlencode($post['slug'])) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($post['title']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($post['thumbnail_url'] ?: SITE_DEFAULT_IMAGE) ?>">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<?php css_tag('/src/css/blog-detail.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="bd-page">
    <article class="bd-article">

        <header class="bd-header">
            <a href="/src/blog/" class="bd-back">
                <svg width="13" height="13" viewBox="0 0 14 14" fill="none">
                    <path d="M9 2L4 7L9 12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                블로그
            </a>
            <h1 class="bd-title"><?= htmlspecialchars($post['title']) ?></h1>
            <time class="bd-date" datetime="<?= date('Y-m-d', strtotime($post['created_at'])) ?>">
                <?= date('Y.m.d', strtotime($post['created_at'])) ?>
            </time>
        </header>

        <hr class="bd-divider">

        <div class="bd-body"><?= $post['content'] ?></div>

        <div class="bd-cta">
            <p class="bd-cta-title"><?= htmlspecialchars($post['cta_text'] ?: '평목 스튜디오의 다양한 패턴 디자인 보러가기') ?></p>
            <a href="/src/collection/" class="bd-cta-btn">컬렉션 보러가기 <i class="bi bi-arrow-right"></i></a>
        </div>

    </article>

    <?php if ($prev || $next): ?>
    <nav class="bd-pager">
        <?php if ($prev): ?>
        <a class="bd-pager-link bd-pager-prev" href="/src/blog/<?= rawurlencode($prev['slug']) ?>">
            <span class="bd-pager-label">이전 글</span>
            <span class="bd-pager-title"><?= htmlspecialchars($prev['title']) ?></span>
        </a>
        <?php else: ?><span></span><?php endif; ?>
        <?php if ($next): ?>
        <a class="bd-pager-link bd-pager-next" href="/src/blog/<?= rawurlencode($next['slug']) ?>">
            <span class="bd-pager-label">다음 글</span>
            <span class="bd-pager-title"><?= htmlspecialchars($next['title']) ?></span>
        </a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
