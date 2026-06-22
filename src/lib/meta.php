<?php
function css_url(string $path): string {
    $abs = $_SERVER['DOCUMENT_ROOT'] . $path;
    $v = file_exists($abs) ? filemtime($abs) : 0;
    return $path . '?v=' . $v;
}

function css_tag(string $path): void {
    echo '<link rel="stylesheet" href="' . css_url($path) . '">' . "\n    ";
}

function page_meta(): array {
    static $cache = null;
    if ($cache !== null) return $cache;

    require_once __DIR__ . '/db.php';
    $path = $_SERVER['PHP_SELF'] ?? '/';
    $row = null;
    try {
        $stmt = db()->prepare('SELECT title, description, keywords, og_image FROM page_meta WHERE path=? LIMIT 1');
        $stmt->execute([$path]);
        $row = $stmt->fetch();
    } catch (Throwable $e) {
        $row = null;
    }

    $cache = $row ?: ['title' => '', 'description' => '', 'keywords' => '', 'og_image' => ''];
    return $cache;
}

function meta_tags(): void {
    echo '<link rel="icon" type="image/png" href="/src/assets/favicon.png">' . "\n    ";
    echo '<link rel="apple-touch-icon" href="/src/assets/apple-touch-icon.png">' . "\n    ";
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
