<?php
header('Content-Type: application/xml; charset=UTF-8');
require_once __DIR__ . '/src/lib/db.php';

const SITEMAP_SITE_URL = 'https://windows.pyeongmok.com';

$urls = [
    ['loc' => '/',                                  'priority' => '1.0'],
    ['loc' => '/src/company/',                       'priority' => '0.8'],
    ['loc' => '/src/portfolio/',                     'priority' => '0.8'],
    ['loc' => '/src/collection/',                    'priority' => '0.8'],
    ['loc' => '/src/blog/',                          'priority' => '0.8'],
    ['loc' => '/src/guide/',                         'priority' => '0.6'],
    ['loc' => '/src/engine/classic/classic.php',     'priority' => '0.9'],
    ['loc' => '/src/engine/square/square.php',       'priority' => '0.9'],
    ['loc' => '/src/engine/cross/cross.php',         'priority' => '0.9'],
    ['loc' => '/src/engine/diamond/diamond.php',     'priority' => '0.9'],
    ['loc' => '/src/engine/triangle/triangle.php',   'priority' => '0.9'],
    ['loc' => '/src/engine/hexagon/hexagon.php',     'priority' => '0.9'],
];

try {
    $pdo = db();

    $posts = $pdo->query("SELECT id, created_at FROM blog_posts WHERE is_active=1")->fetchAll();
    foreach ($posts as $p) {
        $urls[] = [
            'loc'     => '/src/blog/detail.php?id=' . $p['id'],
            'lastmod' => date('Y-m-d', strtotime($p['created_at'])),
            'priority'=> '0.6',
        ];
    }

    $works = $pdo->query("SELECT id, created_at FROM works WHERE is_active=1")->fetchAll();
    foreach ($works as $w) {
        $urls[] = [
            'loc'     => '/src/portfolio/detail.php?id=' . $w['id'],
            'lastmod' => date('Y-m-d', strtotime($w['created_at'])),
            'priority'=> '0.6',
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
