<?php
header('Content-Type: application/rss+xml; charset=UTF-8');
require_once __DIR__ . '/src/lib/db.php';

const RSS_SITE_URL = 'https://studio.pyeongmok.com';

function rss_escape(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

$posts = [];
try {
    $pdo = db();
    $posts = $pdo->query("
        SELECT title, slug, summary, content, created_at
        FROM blog_posts
        WHERE is_active=1
        ORDER BY created_at DESC
        LIMIT 50
    ")->fetchAll();
} catch (Throwable $e) {
    $posts = [];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<rss version="2.0">' . "\n";
echo "  <channel>\n";
echo '    <title>' . rss_escape('평목 공방 블로그') . "</title>\n";
echo '    <link>' . rss_escape(RSS_SITE_URL . '/blog/') . "</link>\n";
echo '    <description>' . rss_escape('평목 공방이 만드는 한옥 살창·창호 이야기') . "</description>\n";
echo "    <language>ko</language>\n";

foreach ($posts as $p) {
    $link = RSS_SITE_URL . '/blog/' . rawurlencode($p['slug']);
    $desc = $p['summary'] ?: mb_substr(strip_tags($p['content']), 0, 200);
    echo "    <item>\n";
    echo '      <title>' . rss_escape($p['title']) . "</title>\n";
    echo '      <link>' . rss_escape($link) . "</link>\n";
    echo '      <guid>' . rss_escape($link) . "</guid>\n";
    echo '      <description>' . rss_escape($desc) . "</description>\n";
    echo '      <pubDate>' . date(DATE_RSS, strtotime($p['created_at'])) . "</pubDate>\n";
    echo "    </item>\n";
}

echo "  </channel>\n";
echo '</rss>' . "\n";
