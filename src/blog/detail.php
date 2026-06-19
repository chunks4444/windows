<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/db.php';
$pdo = db();

$id   = (int)($_GET['id'] ?? 0);
$post = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE id=? AND is_active=1');
    $stmt->execute([$id]);
    $post = $stmt->fetch();
}
if (!$post) { header('Location: /src/blog/'); exit; }

// 이전 / 다음 글
$prev = $pdo->prepare('SELECT id,title FROM blog_posts WHERE is_active=1 AND (sort_order < ? OR (sort_order=? AND id<?)) ORDER BY sort_order DESC, id DESC LIMIT 1');
$prev->execute([$post['sort_order'], $post['sort_order'], $post['id']]);
$prev = $prev->fetch();

$next = $pdo->prepare('SELECT id,title FROM blog_posts WHERE is_active=1 AND (sort_order > ? OR (sort_order=? AND id>?)) ORDER BY sort_order ASC, id ASC LIMIT 1');
$next->execute([$post['sort_order'], $post['sort_order'], $post['id']]);
$next = $next->fetch();

$metaDesc = $post['summary'] ?: mb_substr(strip_tags($post['content']), 0, 120);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> — 평목 공방 블로그</title>
    <meta name="description" content="<?= htmlspecialchars($metaDesc) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($post['title']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>">
    <?php if ($post['thumbnail_url']): ?>
    <meta property="og:image" content="<?= htmlspecialchars($post['thumbnail_url']) ?>">
    <?php endif; ?>
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../lib/meta.php'; ?>
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

    </article>

    <?php if ($prev || $next): ?>
    <nav class="bd-pager">
        <?php if ($prev): ?>
        <a class="bd-pager-link bd-pager-prev" href="/src/blog/detail.php?id=<?= $prev['id'] ?>">
            <span class="bd-pager-label">이전 글</span>
            <span class="bd-pager-title"><?= htmlspecialchars($prev['title']) ?></span>
        </a>
        <?php else: ?><span></span><?php endif; ?>
        <?php if ($next): ?>
        <a class="bd-pager-link bd-pager-next" href="/src/blog/detail.php?id=<?= $next['id'] ?>">
            <span class="bd-pager-label">다음 글</span>
            <span class="bd-pager-title"><?= htmlspecialchars($next['title']) ?></span>
        </a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
</div>
</body>
</html>
