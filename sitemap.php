<?php
header('Content-Type: application/xml; charset=UTF-8');
require_once __DIR__ . '/src/lib/db.php';

const SITEMAP_SITE_URL = 'https://pyeongmok.com';

$urls = [
    ['loc' => '/',                                  'priority' => '1.0'],
    ['loc' => '/company/',                           'priority' => '0.8'],
    ['loc' => '/portfolio/',                         'priority' => '0.8'],
    ['loc' => '/collection/',                        'priority' => '0.8'],
    ['loc' => '/blog/',                              'priority' => '0.8'],
    ['loc' => '/guide/',                             'priority' => '0.6'],
    ['loc' => '/src/engine/classic/classic.php',     'priority' => '0.9'],
    ['loc' => '/src/engine/square/square.php',       'priority' => '0.9'],
    ['loc' => '/src/engine/cross/cross.php',         'priority' => '0.9'],
    ['loc' => '/src/engine/diamond/diamond.php',     'priority' => '0.9'],
    ['loc' => '/src/engine/triangle/triangle.php',   'priority' => '0.9'],
    ['loc' => '/src/engine/hexagon/hexagon.php',     'priority' => '0.9'],
];

// 가이드 개별 아티클 (src/guide/_head.php의 $guide_nav와 동일한 파일 목록 — 새 아티클 추가 시 여기도 같이 추가)
foreach ([
    'intro', 'getting-started',
    'studio-classic', 'studio-square', 'studio-cross', 'studio-diamond', 'studio-triangle', 'studio-hexagon',
    'drawing', 'export', 'render', 'collection', 'account', 'order', 'delivery', 'faq',
] as $guideFile) {
    $urls[] = ['loc' => "/guide/$guideFile", 'priority' => '0.5'];
}

try {
    $pdo = db();

    $posts = $pdo->query("SELECT slug, created_at FROM blog_posts WHERE is_active=1")->fetchAll();
    foreach ($posts as $p) {
        $urls[] = [
            'loc'     => '/blog/' . rawurlencode($p['slug']),
            'lastmod' => date('Y-m-d', strtotime($p['created_at'])),
            'priority'=> '0.6',
        ];
    }

    $works = $pdo->query("SELECT slug, created_at FROM works WHERE is_active=1")->fetchAll();
    foreach ($works as $w) {
        $urls[] = [
            'loc'     => '/portfolio/' . rawurlencode($w['slug']),
            'lastmod' => date('Y-m-d', strtotime($w['created_at'])),
            'priority'=> '0.6',
        ];
    }

    $patterns = $pdo->query("SELECT slug, created_at FROM library_patterns WHERE is_active=1 AND slug != ''")->fetchAll();
    foreach ($patterns as $p) {
        $urls[] = [
            'loc'     => '/collection/detail?slug=' . rawurlencode($p['slug']),
            'lastmod' => date('Y-m-d', strtotime($p['created_at'])),
            'priority'=> '0.5',
        ];
    }
} catch (Throwable $e) {
    // DB 연결 실패 시 정적 URL만 출력
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars(SITEMAP_SITE_URL . $u['loc'], ENT_XML1) . "</loc>\n";
    if (!empty($u['lastmod'])) echo '    <lastmod>' . $u['lastmod'] . "</lastmod>\n";
    echo '    <priority>' . $u['priority'] . "</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>' . "\n";
