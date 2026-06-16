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
            ['file' => 'studio-classic.php',  'title' => 'Classic Lattice'],
            ['file' => 'studio-square.php',   'title' => 'Square Lattice'],
            ['file' => 'studio-cross.php',    'title' => 'Cross Lattice'],
            ['file' => 'studio-diamond.php',  'title' => 'Diamond Lattice'],
            ['file' => 'studio-triangle.php', 'title' => 'Triangle Lattice'],
            ['file' => 'studio-hexagon.php',  'title' => 'Hexagon Lattice'],
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
    <link rel="stylesheet" href="/src/css/common.css">
    <link rel="stylesheet" href="/src/css/nav.css">
    <link rel="stylesheet" href="/src/guide/guide.css">
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
            <div class="gs-section-hd"><?= htmlspecialchars($sec['title']) ?></div>
            <?php foreach ($sec['articles'] as $art): ?>
            <a href="/src/guide/<?= $art['file'] ?>"
               class="gs-link<?= ($art['file'] === ($guide_current ?? '')) ? ' active' : '' ?>">
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
