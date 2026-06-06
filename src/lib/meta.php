<?php
function page_meta(): array {
    static $cache = null;
    if ($cache !== null) return $cache;

    require_once __DIR__ . '/db.php';
    $path = $_SERVER['PHP_SELF'] ?? '/';
    $stmt = db()->prepare('SELECT title, description, keywords, og_image FROM page_meta WHERE path=? LIMIT 1');
    $stmt->execute([$path]);
    $row = $stmt->fetch();

    $cache = $row ?: ['title' => '', 'description' => '', 'keywords' => '', 'og_image' => ''];
    return $cache;
}

function meta_tags(): void {
    $m = page_meta();
    if ($m['title'])       echo '<title>' . htmlspecialchars($m['title'], ENT_QUOTES) . '</title>' . "\n    ";
    if ($m['description']) echo '<meta name="description" content="' . htmlspecialchars($m['description'], ENT_QUOTES) . '">' . "\n    ";
    if ($m['keywords'])    echo '<meta name="keywords"    content="' . htmlspecialchars($m['keywords'],    ENT_QUOTES) . '">' . "\n    ";
    $ogTitle = $m['title']       ?: '평목';
    $ogDesc  = $m['description'] ?: '';
    echo '<meta property="og:title"       content="' . htmlspecialchars($ogTitle, ENT_QUOTES) . '">' . "\n    ";
    if ($ogDesc) echo '<meta property="og:description" content="' . htmlspecialchars($ogDesc, ENT_QUOTES) . '">' . "\n    ";
    if ($m['og_image']) echo '<meta property="og:image" content="' . htmlspecialchars($m['og_image'], ENT_QUOTES) . '">' . "\n    ";
}
