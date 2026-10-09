<?php
header('Content-Type: application/xml; charset=UTF-8');
require_once __DIR__ . '/src/lib/db.php';

const SITEMAP_SITE_URL = 'https://pyeongmok.com';

// path(page_meta 기준) => sitemap 상의 loc. lastmod를 page_meta.updated_at에서 끌어와
// og:image 등 메타데이터를 바꿀 때마다 사이트맵에도 "최근 변경"이 자동으로 반영되게 함
// (구글이 재크롤링 우선순위를 정할 때 참고하는 신호 — Search Console에서 URL 하나씩 수동 요청하지 않아도 됨)
$staticUrls = [
    ['loc' => '/',                                  'priority' => '1.0', 'meta_path' => '/index.php'],
    ['loc' => '/company/',                           'priority' => '0.8', 'meta_path' => '/src/company/index.php'],
    ['loc' => '/portfolio/',                         'priority' => '0.8', 'meta_path' => '/src/portfolio/index.php'],
    ['loc' => '/collection/',                        'priority' => '0.8', 'meta_path' => '/src/collection/index.php'],
    ['loc' => '/blog/',                              'priority' => '0.8', 'meta_path' => '/src/blog/index.php'],
    ['loc' => '/guide/',                             'priority' => '0.6', 'meta_path' => '/src/guide/index.php'],
    ['loc' => '/src/engine/classic/classic.php',     'priority' => '0.9', 'meta_path' => '/src/engine/classic/classic.php'],
    ['loc' => '/src/engine/square/square.php',       'priority' => '0.9', 'meta_path' => '/src/engine/square/square.php'],
    ['loc' => '/src/engine/cross/cross.php',         'priority' => '0.9', 'meta_path' => '/src/engine/cross/cross.php'],
    ['loc' => '/src/engine/diamond/diamond.php',     'priority' => '0.9', 'meta_path' => '/src/engine/diamond/diamond.php'],
    ['loc' => '/src/engine/triangle/triangle.php',   'priority' => '0.9', 'meta_path' => '/src/engine/triangle/triangle.php'],
    ['loc' => '/src/engine/hexagon/hexagon.php',     'priority' => '0.9', 'meta_path' => '/src/engine/hexagon/hexagon.php'],
];

// 가이드 개별 아티클 (src/guide/_head.php의 $guide_nav와 동일한 파일 목록 — 새 아티클 추가 시 여기도 같이 추가)
foreach ([
    'intro', 'getting-started', 'canvas-toolbar', 'svg-insert',
    'studio-classic', 'studio-square', 'studio-cross', 'studio-diamond', 'studio-triangle', 'studio-hexagon', 'studio-mondrian', 'finish',
    'drawing', 'export', 'render', 'collection', 'account', 'order', 'delivery', 'faq',
] as $guideFile) {
    $staticUrls[] = ['loc' => "/guide/$guideFile", 'priority' => '0.5', 'meta_path' => "/src/guide/$guideFile.php"];
}

// DB 연결 실패 시에도 정적 URL만이라도 lastmod 없이 출력
$urls = array_map(function ($u) {
    unset($u['meta_path']);
    return $u;
}, $staticUrls);

try {
    $pdo = db();

    $metaDates = $pdo->query("SELECT path, updated_at FROM page_meta")
        ->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach ($staticUrls as $i => $u) {
        if (!empty($metaDates[$u['meta_path']])) {
            $urls[$i]['lastmod'] = date('Y-m-d', strtotime($metaDates[$u['meta_path']]));
        }
    }

    // lastmod는 본문 수정일(updated_at, 관리자 저장 시 내용이 바뀐 경우만 갱신) — 없으면 작성일.
    // 정확한 수정일을 줘야 구글이 바뀐 글만 골라 다시 가져간다 (전체를 몰아서 재크롤하는 일이 줄어듦)
    $posts = $pdo->query("SELECT slug, COALESCE(updated_at, created_at) AS modified_at, (content_en IS NOT NULL AND content_en != '') AS has_en FROM blog_posts WHERE is_active=1")->fetchAll();
    foreach ($posts as $p) {
        $urls[] = [
            'loc'     => '/blog/' . rawurlencode($p['slug']),
            'lastmod' => date('Y-m-d', strtotime($p['modified_at'])),
            'priority'=> '0.6',
        ];
        // 영문 본문이 있는 글만 /en/ 주소도 올림 — 번역 전 글의 /en/ 페이지는 한글 본문 그대로라
        // 한글 페이지와 중복 콘텐츠로 보일 수 있다. 번역을 넣으면 다음 크롤 때 자동으로 포함됨
        if ($p['has_en']) {
            $urls[] = [
                'loc'     => '/en/blog/' . rawurlencode($p['slug']),
                'lastmod' => date('Y-m-d', strtotime($p['modified_at'])),
                'priority'=> '0.5',
            ];
        }
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
    // DB 연결 실패 시 이미 채워둔 정적 URL(lastmod 없이)만 출력
}

// 정적 페이지(홈·회사소개·가이드·엔진 등)의 영문판 /en/ 주소. 이 페이지들은 화면 문구·가이드 본문이
// 전부 영문화돼 있어서 짝을 그대로 올린다. 페이지마다 hreflang 태그도 있지만, 사이트맵에 넣어 두면
// 구글이 영문 페이지를 더 빨리·빠짐없이 수집한다 (2026-10-09).
// 포트폴리오·컬렉션 개별 페이지는 본문이 DB 한글 원문이라(영문 컬럼 없음) 넣지 않음
foreach (array_keys($staticUrls) as $i) {
    $en = $urls[$i];
    $en['loc'] = '/en' . $en['loc'];
    $en['priority'] = number_format(max(0.1, (float)$en['priority'] - 0.1), 1);
    $urls[] = $en;
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
