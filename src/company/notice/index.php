<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/i18n.php';
require_once __DIR__ . '/../../lib/notices.php';

$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$total = 0;
$rows = [];
try {
    $pdo = db();
    notice_ensure_table($pdo);
    $total = (int)$pdo->query('SELECT COUNT(*) FROM notices WHERE is_active = 1 AND published_at <= NOW()')->fetchColumn();
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = min($page, $totalPages);
    $st = $pdo->prepare('SELECT id, title, title_en, label, label_en, body, body_en, cta_text, cta_text_en, cta_url, is_pinned, published_at FROM notices
                         WHERE is_active = 1 AND published_at <= NOW()
                         ORDER BY is_pinned DESC, published_at DESC, id DESC LIMIT ? OFFSET ?');
    $st->bindValue(1, $perPage, PDO::PARAM_INT);
    $st->bindValue(2, ($page - 1) * $perPage, PDO::PARAM_INT);
    $st->execute();
    $rows = $st->fetchAll();
} catch (Throwable $e) {
    error_log('notice list: ' . $e->getMessage());
    $totalPages = 1;
}
?>
<!DOCTYPE html>
<html lang="<?= is_en() ? 'en' : 'ko' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once __DIR__ . '/../../lib/meta.php';
    $ntPageQuery = $page > 1 ? '?page=' . $page : '';
    meta_tags([
        'title'       => t('notice_title') . ' — ' . t('bd_title_suffix'),
        'description' => t('notice_meta_desc'),
        'canonical'   => SITE_URL . lang_href('/company/notice/' . $ntPageQuery),
        'hreflang'    => '/company/notice/' . $ntPageQuery,
    ]);
    ?>
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<?php css_tag('/src/css/blog.css'); ?>
<?php css_tag('/src/css/notice.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../../components/nav.php'; ?>

<div class="bg-page">
    <div class="bg-hero">
        <div class="bg-hero-inner">
            <p class="bg-hero-label"><a href="<?= lang_href('/company/') ?>"><?= htmlspecialchars(t('nav_company')) ?></a> / Notice</p>
            <h1><?= htmlspecialchars(t('notice_title')) ?></h1>
            <p class="bg-hero-sub"><?= htmlspecialchars(t('notice_sub')) ?></p>
        </div>
    </div>

    <main class="nt-wrap">
        <?php if (!$rows): ?>
        <div class="bg-empty"><?= htmlspecialchars(t('notice_empty')) ?></div>
        <?php else: ?>
        <ul class="nt-list">
            <?php foreach ($rows as $r): ?>
            <li class="nt-item<?= $r['is_pinned'] ? ' is-pinned' : '' ?>">
                <a class="nt-link" href="<?= lang_href('/company/notice/' . (int)$r['id']) ?>" data-nt-open="<?= (int)$r['id'] ?>">
                    <?php if ($r['is_pinned']): ?><span class="nt-pin"><?= htmlspecialchars(t('notice_pinned')) ?></span><?php endif; ?>
                    <?php if (db_field($r, 'label') !== ''): ?><span class="nt-label"><?= htmlspecialchars(db_field($r, 'label')) ?></span><?php endif; ?>
                    <span class="nt-title"><?= htmlspecialchars(db_field($r, 'title')) ?></span>
                    <time class="nt-date" datetime="<?= date('Y-m-d', strtotime($r['published_at'])) ?>"><?= date('Y.m.d', strtotime($r['published_at'])) ?></time>
                </a>
                <template id="nt-tpl-<?= (int)$r['id'] ?>"><?= notice_article_html($r, true) ?></template>
            </li>
            <?php endforeach; ?>
        </ul>

        <?php if ($totalPages > 1): ?>
        <nav class="bg-pagination" aria-label="<?= htmlspecialchars(t('notice_page')) ?>">
            <a class="bg-page-arrow <?= $page <= 1 ? 'disabled' : '' ?>" href="?page=<?= max(1, $page - 1) ?>" aria-label="<?= htmlspecialchars(t('blog_prev_page')) ?>"><i class="bi bi-chevron-left"></i></a>
            <?php for ($pn = 1; $pn <= $totalPages; $pn++): ?>
            <a class="bg-page-num <?= $pn === $page ? 'active' : '' ?>" href="?page=<?= $pn ?>"><?= $pn ?></a>
            <?php endfor; ?>
            <a class="bg-page-arrow <?= $page >= $totalPages ? 'disabled' : '' ?>" href="?page=<?= min($totalPages, $page + 1) ?>" aria-label="<?= htmlspecialchars(t('blog_next_page')) ?>"><i class="bi bi-chevron-right"></i></a>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </main>
</div>
<div class="nt-modal" id="ntModal" hidden aria-hidden="true">
    <div class="nt-modal-backdrop" data-nt-close></div>
    <div class="nt-modal-card" role="dialog" aria-modal="true" aria-labelledby="ntModalTitle" tabindex="-1">
        <button type="button" class="nt-modal-close" data-nt-close aria-label="<?= htmlspecialchars(t('auth_close')) ?>"><i class="bi bi-x-lg"></i></button>
        <div class="nt-modal-content" id="ntModalContent"></div>
    </div>
</div>
<script src="/src/js/notice.js?v=<?= md5_file(__DIR__ . '/../../js/notice.js') ?>"></script>
<?php include __DIR__ . '/../../components/footer.php'; ?>
</body>
</html>
