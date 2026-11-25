<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/i18n.php';
require_once __DIR__ . '/../../lib/notices.php';

$id = (int)($_GET['id'] ?? 0);
$notice = null;
$prev = $next = null;
try {
    $pdo = db();
    notice_ensure_table($pdo);
    $st = $pdo->prepare('SELECT * FROM notices WHERE id = ? AND is_active = 1 AND published_at <= NOW()');
    $st->execute([$id]);
    $notice = $st->fetch() ?: null;
    if ($notice) {
        $q = $pdo->prepare('SELECT id, title, title_en FROM notices WHERE is_active = 1 AND published_at <= NOW()
                            AND (published_at < ? OR (published_at = ? AND id < ?)) ORDER BY published_at DESC, id DESC LIMIT 1');
        $q->execute([$notice['published_at'], $notice['published_at'], $notice['id']]);
        $prev = $q->fetch() ?: null;
        $q = $pdo->prepare('SELECT id, title, title_en FROM notices WHERE is_active = 1 AND published_at <= NOW()
                            AND (published_at > ? OR (published_at = ? AND id > ?)) ORDER BY published_at ASC, id ASC LIMIT 1');
        $q->execute([$notice['published_at'], $notice['published_at'], $notice['id']]);
        $next = $q->fetch() ?: null;
    }
} catch (Throwable $e) {
    error_log('notice detail: ' . $e->getMessage());
}
if (!$notice) { http_response_code(404); include __DIR__ . '/../../../404.php'; exit; }

$title = db_field($notice, 'title');
$bodyHtml = db_field($notice, 'body');
$hasEn = trim((string)($notice['title_en'] ?? '')) !== '' && trim((string)($notice['body_en'] ?? '')) !== '';
?>
<!DOCTYPE html>
<html lang="<?= is_en() ? 'en' : 'ko' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once __DIR__ . '/../../lib/meta.php';
    meta_tags([
        'title'       => $title . ' — ' . t('notice_title') . ' | ' . t('bd_title_suffix'),
        'description' => notice_excerpt($bodyHtml, 120) ?: t('notice_meta_desc'),
        'canonical'   => SITE_URL . lang_href('/company/notice/' . (int)$notice['id']),
        'hreflang'    => $hasEn ? '/company/notice/' . (int)$notice['id'] : false,
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
    <article class="nt-wrap nt-article">
        <p class="nt-crumb"><a href="<?= lang_href('/company/') ?>"><?= htmlspecialchars(t('nav_company')) ?></a> <span>/</span> <a href="<?= lang_href('/company/notice/') ?>"><?= htmlspecialchars(t('notice_title')) ?></a></p>
        <?= notice_article_html($notice) ?>

        <nav class="nt-nav">
            <?php if ($prev): ?>
            <a class="nt-nav-link" href="<?= lang_href('/company/notice/' . (int)$prev['id']) ?>"><span><?= htmlspecialchars(t('notice_prev')) ?></span><?= htmlspecialchars(db_field($prev, 'title')) ?></a>
            <?php endif; ?>
            <?php if ($next): ?>
            <a class="nt-nav-link" href="<?= lang_href('/company/notice/' . (int)$next['id']) ?>"><span><?= htmlspecialchars(t('notice_next')) ?></span><?= htmlspecialchars(db_field($next, 'title')) ?></a>
            <?php endif; ?>
        </nav>
        <p class="nt-back"><a href="<?= lang_href('/company/notice/') ?>"><i class="bi bi-list"></i> <?= htmlspecialchars(t('notice_list')) ?></a></p>
    </article>
</div>
<?php include __DIR__ . '/../../components/footer.php'; ?>
</body>
</html>
