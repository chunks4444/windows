<?php
// $guide_current : 현재 파일명 (예: 'intro.php')
// $guide_title   : 페이지 제목
// $guide_cat     : 카테고리명

$guide_nav = [
    [
        'title' => '평목 소개', 'icon' => 'bi-info-circle',
        'bg' => '#E6F4F2', 'color' => '#3A8C82',
        'articles' => [
            ['file' => 'intro.php',           'title' => '평목 스튜디오란?'],
            ['file' => 'getting-started.php', 'title' => '시작하기'],
        ],
    ],
    [
        'title' => '스튜디오', 'icon' => 'bi-pencil-square',
        'bg' => '#FFF0EE', 'color' => '#cc2200',
        'articles' => [
            ['file' => 'studio-classic.php',  'title' => '세살',      'engine' => 'classic'],
            ['file' => 'studio-square.php',   'title' => '정자살',    'engine' => 'square'],
            ['file' => 'studio-cross.php',    'title' => '빗살',      'engine' => 'cross'],
            ['file' => 'studio-diamond.php',  'title' => '격자 빗살', 'engine' => 'diamond'],
            ['file' => 'studio-triangle.php', 'title' => '세모 솟을살', 'engine' => 'triangle'],
            ['file' => 'studio-hexagon.php',  'title' => '육모 솟을살', 'engine' => 'hexagon'],
        ],
    ],
    [
        'title' => '도면 관리', 'icon' => 'bi-folder2-open',
        'bg' => '#F5F4EE', 'color' => '#7A6B40',
        'articles' => [
            ['file' => 'drawing.php', 'title' => '도면 저장 & 불러오기'],
            ['file' => 'export.php',  'title' => 'PDF / PNG 내보내기'],
        ],
    ],
    [
        'title' => 'AI 렌더링', 'icon' => 'bi-stars',
        'bg' => '#F2F0FB', 'color' => '#5A4DB8',
        'articles' => [
            ['file' => 'render.php', 'title' => 'AI 렌더링 사용법'],
        ],
    ],
    [
        'title' => '컬렉션', 'icon' => 'bi-collection',
        'bg' => '#FFF8EE', 'color' => '#b8894a',
        'articles' => [
            ['file' => 'collection.php', 'title' => '컬렉션 & 내 보드'],
        ],
    ],
    [
        'title' => '계정 설정', 'icon' => 'bi-person-gear',
        'bg' => '#EEF3F8', 'color' => '#2A6B8C',
        'articles' => [
            ['file' => 'account.php', 'title' => '프로필 & 회사 정보'],
        ],
    ],
    [
        'title' => '주문', 'icon' => 'bi-cart-check',
        'bg' => '#FDF0E6', 'color' => '#B8662F',
        'articles' => [
            ['file' => 'order.php', 'title' => '주문 안내'],
        ],
    ],
    [
        'title' => '배송', 'icon' => 'bi-truck',
        'bg' => '#EAF3FB', 'color' => '#2E6FA8',
        'articles' => [
            ['file' => 'delivery.php', 'title' => '배송 안내'],
        ],
    ],
    [
        'title' => 'FAQ', 'icon' => 'bi-patch-question',
        'bg' => '#F0F4F0', 'color' => '#3A6B3A',
        'articles' => [
            ['file' => 'faq.php', 'title' => '자주 묻는 질문'],
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
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($guide_title ?? '가이드') ?> — 평목 가이드</title>
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../lib/meta.php'; ?>
    <link rel="icon" type="image/png" href="/src/assets/favicon.png">
    <link rel="apple-touch-icon" href="/src/assets/apple-touch-icon.png">
<?php css_tag('/src/css/common.css'); ?>
    <?php css_tag('/src/css/nav.css'); ?>
    <?php css_tag('/src/guide/guide.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="guide-wrap">

<!-- ── 사이드바 ── -->
<aside class="guide-sidebar">
    <a href="/src/guide/" class="gs-home">
        <i class="bi bi-book-half"></i> 평목 가이드
    </a>
    <nav>
    <?php foreach ($guide_nav as $sec): ?>
        <div class="gs-section">
            <div class="gs-section-hd">
                <span class="gs-section-hd-icon" style="background:<?= htmlspecialchars($sec['bg']) ?>;color:<?= htmlspecialchars($sec['color']) ?>;"><i class="bi <?= htmlspecialchars($sec['icon']) ?>"></i></span>
                <?= htmlspecialchars($sec['title']) ?>
            </div>
            <?php foreach ($sec['articles'] as $art): ?>
            <a href="/src/guide/<?= $art['file'] ?>"
               class="gs-link<?= ($art['file'] === ($guide_current ?? '')) ? ' active' : '' ?>">
                <?php if (isset($art['engine'])): ?>
                <span class="gs-link-icon"><?= $guideEngineIcons[$art['engine']] ?? '' ?></span>
                <?php endif; ?>
                <?= htmlspecialchars($art['title']) ?>
            </a>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
    </nav>
</aside>

<!-- ── 본문 ── -->
<main class="guide-main">
<div class="guide-article">

<?php if ($current_cat): ?>
<nav class="guide-breadcrumb">
    <a href="/src/guide/">가이드</a>
    <span class="sep"><i class="bi bi-chevron-right"></i></span>
    <span><?= htmlspecialchars($current_cat) ?></span>
    <span class="sep"><i class="bi bi-chevron-right"></i></span>
    <span><?= htmlspecialchars($guide_title ?? '') ?></span>
</nav>
<?php endif; ?>
