<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/i18n.php';
?>
<!DOCTYPE html>
<html lang="<?= is_en() ? 'en' : 'ko' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once __DIR__ . '/../lib/meta.php'; meta_tags(); ?>
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<?php css_tag('/src/css/common.css'); ?>
    <?php css_tag('/src/css/nav.css'); ?>
    <?php css_tag('/src/guide/guide.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="guide-landing">

    <!-- 히어로 -->
    <div class="guide-hero">
        <div class="guide-hero-inner">
            <p class="guide-hero-label">Guide</p>
            <h1><?= htmlspecialchars(t('guide_landing_title')) ?></h1>
            <p class="guide-hero-sub"><?= htmlspecialchars(t('guide_landing_sub')) ?></p>
        </div>
    </div>

    <!-- 카테고리 카드 -->
    <div class="guide-categories">

        <a href="<?= lang_href("/guide/intro") ?>" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:var(--accent-tint);color:#000;">
                <i class="bi bi-info-circle-fill"></i>
            </div>
            <div class="guide-cat-title"><?= htmlspecialchars(t("guide_art_intro")) ?></div>
            <div class="guide-cat-desc"><?= htmlspecialchars(t("guide_card_intro_desc")) ?></div>
            <div class="guide-cat-count"><?= htmlspecialchars(sprintf(t("guide_article_count"), 2)) ?></div>
        </a>

        <a href="<?= lang_href("/guide/studio-classic") ?>" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:var(--danger-tint);color:#000;">
                <svg width="22" height="22" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                    <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="23"/>
                    <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="23"/>
                    <rect fill="currentColor" x="148" y="148" width="46" height="384" rx="23"/>
                    <rect fill="currentColor" x="294" y="148" width="46" height="384" rx="23"/>
                    <rect fill="currentColor" x="486" y="148" width="46" height="384" rx="23"/>
                </svg>
            </div>
            <div class="guide-cat-title"><?= htmlspecialchars(t("guide_card_studio_title")) ?></div>
            <div class="guide-cat-desc"><?= htmlspecialchars(t("guide_card_studio_desc")) ?></div>
            <div class="guide-cat-count"><?= htmlspecialchars(sprintf(t("guide_article_count"), 6)) ?></div>
        </a>

        <a href="<?= lang_href("/guide/drawing") ?>" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:var(--accent-tint);color:#000;">
                <i class="bi bi-folder2-open"></i>
            </div>
            <div class="guide-cat-title"><?= htmlspecialchars(t("guide_sec_drawing")) ?></div>
            <div class="guide-cat-desc"><?= htmlspecialchars(t("guide_card_drawing_desc")) ?></div>
            <div class="guide-cat-count"><?= htmlspecialchars(sprintf(t("guide_article_count"), 2)) ?></div>
        </a>

        <a href="<?= lang_href("/guide/render") ?>" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:var(--accent-tint);color:#000;">
                <i class="bi bi-stars"></i>
            </div>
            <div class="guide-cat-title"><?= htmlspecialchars(t("nav_guide_render")) ?></div>
            <div class="guide-cat-desc"><?= htmlspecialchars(t("guide_card_render_desc")) ?></div>
            <div class="guide-cat-count"><?= htmlspecialchars(sprintf(t("guide_article_count"), 1)) ?></div>
        </a>

        <a href="<?= lang_href("/guide/collection") ?>" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:var(--accent-tint);color:#000;">
                <i class="bi bi-collection-fill"></i>
            </div>
            <div class="guide-cat-title"><?= htmlspecialchars(t("nav_collection")) ?></div>
            <div class="guide-cat-desc"><?= htmlspecialchars(t("guide_card_collection_desc")) ?></div>
            <div class="guide-cat-count"><?= htmlspecialchars(sprintf(t("guide_article_count"), 1)) ?></div>
        </a>

        <a href="<?= lang_href("/guide/account") ?>" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:var(--accent-tint);color:#000;">
                <i class="bi bi-person-gear"></i>
            </div>
            <div class="guide-cat-title"><?= htmlspecialchars(t("nav_guide_account")) ?></div>
            <div class="guide-cat-desc"><?= htmlspecialchars(t("guide_card_account_desc")) ?></div>
            <div class="guide-cat-count"><?= htmlspecialchars(sprintf(t("guide_article_count"), 1)) ?></div>
        </a>

        <a href="<?= lang_href("/guide/order") ?>" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:var(--accent-tint);color:#000;">
                <i class="bi bi-cart-check"></i>
            </div>
            <div class="guide-cat-title"><?= htmlspecialchars(t("nav_guide_order")) ?></div>
            <div class="guide-cat-desc"><?= htmlspecialchars(t("guide_card_order_desc")) ?></div>
            <div class="guide-cat-count"><?= htmlspecialchars(sprintf(t("guide_article_count"), 1)) ?></div>
        </a>

        <a href="<?= lang_href("/guide/delivery") ?>" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:var(--accent-tint);color:#000;">
                <i class="bi bi-truck"></i>
            </div>
            <div class="guide-cat-title"><?= htmlspecialchars(t("nav_guide_delivery")) ?></div>
            <div class="guide-cat-desc"><?= htmlspecialchars(t("guide_card_delivery_desc")) ?></div>
            <div class="guide-cat-count"><?= htmlspecialchars(sprintf(t("guide_article_count"), 1)) ?></div>
        </a>

        <a href="<?= lang_href("/guide/faq") ?>" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:var(--accent-tint);color:#000;">
                <i class="bi bi-patch-question-fill"></i>
            </div>
            <div class="guide-cat-title">FAQ</div>
            <div class="guide-cat-desc"><?= htmlspecialchars(t("guide_card_faq_desc")) ?></div>
            <div class="guide-cat-count"><?= htmlspecialchars(sprintf(t("guide_article_count"), 1)) ?></div>
        </a>

    </div>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
