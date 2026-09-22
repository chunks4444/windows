<?php
// $guide_current : 현재 파일명 (예: 'intro.php')
// $guide_title   : 페이지 제목
// $guide_cat     : 카테고리명

require_once __DIR__ . '/../lib/i18n.php';

$guide_nav = [
    [
        'title' => t('guide_sec_about'), 'icon' => 'bi-info-circle',
        'bg' => 'var(--accent-tint)', 'color' => 'var(--text)',
        'articles' => [
            ['file' => 'intro.php',           'title' => t('guide_art_intro')],
            ['file' => 'getting-started.php', 'title' => t('guide_art_getting_started')],
        ],
    ],
    [
        'title' => t('nav_studio'), 'icon' => 'bi-pencil-square',
        'bg' => 'var(--accent-tint)', 'color' => 'var(--text)',
        'articles' => [
            ['file' => 'studio-classic.php',  'title' => t('guide_art_classic'),  'engine' => 'classic'],
            ['file' => 'studio-square.php',   'title' => t('guide_art_square'),   'engine' => 'square'],
            ['file' => 'studio-cross.php',    'title' => t('guide_art_cross'),    'engine' => 'cross'],
            ['file' => 'studio-diamond.php',  'title' => t('guide_art_diamond'),  'engine' => 'diamond'],
            ['file' => 'studio-triangle.php', 'title' => t('guide_art_triangle'), 'engine' => 'triangle'],
            ['file' => 'studio-hexagon.php',  'title' => t('guide_art_hexagon'),  'engine' => 'hexagon'],
        ],
    ],
    [
        'title' => t('guide_sec_drawing'), 'icon' => 'bi-folder2-open',
        'bg' => 'var(--accent-tint)', 'color' => 'var(--text)',
        'articles' => [
            ['file' => 'canvas-toolbar.php', 'title' => t('guide_art_canvas_toolbar')],
            ['file' => 'svg-insert.php', 'title' => t('guide_art_svg_insert')],
            ['file' => 'drawing.php', 'title' => t('guide_art_drawing')],
        ],
    ],
    [
        'title' => t('guide_sec_export'), 'icon' => 'bi-download',
        'bg' => 'var(--accent-tint)', 'color' => 'var(--text)',
        'articles' => [
            ['file' => 'export.php',  'title' => t('guide_art_export')],
        ],
    ],
    [
        'title' => t('nav_guide_render'), 'icon' => 'bi-stars',
        'bg' => 'var(--accent-tint)', 'color' => 'var(--text)',
        'articles' => [
            ['file' => 'render.php', 'title' => t('guide_art_render')],
        ],
    ],
    [
        'title' => t('nav_collection'), 'icon' => 'bi-collection',
        'bg' => 'var(--accent-tint)', 'color' => 'var(--text)',
        'articles' => [
            ['file' => 'collection.php', 'title' => t('guide_art_collection')],
        ],
    ],
    [
        'title' => t('nav_guide_account'), 'icon' => 'bi-person-gear',
        'bg' => 'var(--accent-tint)', 'color' => 'var(--text)',
        'articles' => [
            ['file' => 'account.php', 'title' => t('guide_art_account')],
        ],
    ],
    [
        'title' => t('nav_guide_order'), 'icon' => 'bi-cart-check',
        'bg' => 'var(--accent-tint)', 'color' => 'var(--text)',
        'articles' => [
            ['file' => 'order.php', 'title' => t('guide_art_order')],
        ],
    ],
    [
        'title' => t('nav_guide_delivery'), 'icon' => 'bi-truck',
        'bg' => 'var(--accent-tint)', 'color' => 'var(--text)',
        'articles' => [
            ['file' => 'delivery.php', 'title' => t('guide_art_delivery')],
        ],
    ],
    [
        'title' => t('nav_guide_faq'), 'icon' => 'bi-patch-question',
        'bg' => 'var(--accent-tint)', 'color' => 'var(--text)',
        'articles' => [
            ['file' => 'faq.php', 'title' => t('home_faq_title')],
        ],
    ],
];

$guideEngineIcons = [
    'classic' => '<svg width="14" height="14" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
            <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="23"/>
            <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="23"/>
            <rect fill="currentColor" x="148" y="148" width="46" height="384" rx="23"/>
            <rect fill="currentColor" x="294" y="148" width="46" height="384" rx="23"/>
            <rect fill="currentColor" x="486" y="148" width="46" height="384" rx="23"/>
        </svg>',
    'square' => '<svg width="14" height="14" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
            <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="23"/>
            <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="23"/>
            <rect fill="currentColor" x="204" y="148" width="46" height="384" rx="23"/>
            <rect fill="currentColor" x="430" y="148" width="46" height="384" rx="23"/>
        </svg>',
    'cross' => '<svg width="14" height="14" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
            <g transform="rotate(45 340 340)">
                <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="23"/>
                <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="23"/>
                <rect fill="currentColor" x="204" y="148" width="46" height="384" rx="23"/>
                <rect fill="currentColor" x="430" y="148" width="46" height="384" rx="23"/>
            </g>
        </svg>',
    'diamond' => '<svg width="14" height="14" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
            <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/>
            <rect fill="currentColor" x="148" y="317" width="384" height="46" rx="23"/>
            <g transform="rotate(45 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
            <g transform="rotate(135 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
        </svg>',
    'triangle' => '<svg width="14" height="14" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
            <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/>
            <g transform="rotate(60 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
            <g transform="rotate(120 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
        </svg>',
    'hexagon' => '<svg width="14" height="14" viewBox="0 0 680 680" fill="none" xmlns="http://www.w3.org/2000/svg">
            <polyline points="210,265 340,190 470,265" stroke="currentColor" stroke-width="32" stroke-linejoin="round" stroke-linecap="round"/>
            <line x1="210" y1="265" x2="210" y2="415" stroke="currentColor" stroke-width="32" stroke-linecap="round"/>
            <line x1="470" y1="265" x2="470" y2="415" stroke="currentColor" stroke-width="32" stroke-linecap="round"/>
            <line x1="210" y1="415" x2="340" y2="490" stroke="currentColor" stroke-width="32" stroke-linecap="round"/>
            <line x1="470" y1="415" x2="340" y2="490" stroke="currentColor" stroke-width="32" stroke-linecap="round"/>
        </svg>',
];

// 현재 섹션 찾기
$current_cat = '';
foreach ($guide_nav as $sec) {
    foreach ($sec['articles'] as $art) {
        if ($art['file'] === ($guide_current ?? '')) {
            $current_cat = $sec['title'];
            break 2;
        }
    }
}
?>
<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html lang="<?= is_en() ? 'en' : 'ko' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once __DIR__ . '/../lib/meta.php';
    meta_tags();
    ?>
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<?php css_tag('/src/css/common.css'); ?>
    <?php css_tag('/src/css/nav.css'); ?>
    <?php css_tag('/src/guide/guide.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="guide-wrap">

<!-- ── 사이드바 ── -->
<aside class="guide-sidebar">
    <a href="<?= lang_href('/guide/') ?>" class="gs-home">
        <i class="bi bi-book-half"></i> <?= htmlspecialchars(t('guide_sidebar_home')) ?>
    </a>
    <nav>
    <?php foreach ($guide_nav as $sec):
        $secHasCurrent = false;
        foreach ($sec['articles'] as $art) {
            if ($art['file'] === ($guide_current ?? '')) { $secHasCurrent = true; break; }
        }
    ?>
        <div class="gs-section<?= $secHasCurrent ? ' open' : '' ?>">
            <button type="button" class="gs-section-hd">
                <span class="gs-section-hd-icon" style="background:<?= htmlspecialchars($sec['bg']) ?>;color:<?= htmlspecialchars($sec['color']) ?>;"><i class="bi <?= htmlspecialchars($sec['icon']) ?>"></i></span>
                <span class="gs-section-hd-title"><?= htmlspecialchars($sec['title']) ?></span>
                <i class="bi bi-chevron-down gs-section-chevron"></i>
            </button>
            <div class="gs-section-body">
                <?php foreach ($sec['articles'] as $art): ?>
                <a href="<?= lang_href('/guide/' . basename($art['file'], '.php')) ?>"
                   class="gs-link<?= ($art['file'] === ($guide_current ?? '')) ? ' active' : '' ?>">
                    <?php if (isset($art['engine'])): ?>
                    <span class="gs-link-icon"><?= $guideEngineIcons[$art['engine']] ?? '' ?></span>
                    <?php endif; ?>
                    <?= htmlspecialchars($art['title']) ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
    </nav>
    <script>
    document.querySelectorAll('.gs-section-hd').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.closest('.gs-section').classList.toggle('open');
        });
    });
    </script>
</aside>

<!-- ── 본문 ── -->
<main class="guide-main">
<div class="guide-article">

<?php if ($current_cat): ?>
<nav class="guide-breadcrumb">
    <a href="<?= lang_href('/guide/') ?>"><?= htmlspecialchars(t('guide_breadcrumb_root')) ?></a>
    <span class="sep"><i class="bi bi-chevron-right"></i></span>
    <span><?= htmlspecialchars($current_cat) ?></span>
    <span class="sep"><i class="bi bi-chevron-right"></i></span>
    <span><?= htmlspecialchars($guide_title ?? '') ?></span>
</nav>
<?php endif; ?>
