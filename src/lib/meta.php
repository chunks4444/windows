<?php
function css_url(string $path): string {
    $abs = $_SERVER['DOCUMENT_ROOT'] . $path;
    $v = file_exists($abs) ? filemtime($abs) : 0;
    return $path . '?v=' . $v;
}

function css_tag(string $path): void {
    echo '<link rel="stylesheet" href="' . css_url($path) . '">' . "\n    ";
}

const SITE_META_DEFAULT_PATH = '__default__';

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
        if (!$row) {
            $stmt->execute([SITE_META_DEFAULT_PATH]);
            $row = $stmt->fetch();
        }
    } catch (Throwable $e) {
        $row = null;
    }

    $cache = $row ?: ['title' => '', 'description' => '', 'keywords' => '', 'og_image' => ''];
    return $cache;
}

const SITE_URL          = 'https://studio.pyeongmok.com';
const SITE_DEFAULT_TITLE = '평목 - DESIGN IN REAL TIME';
const SITE_DEFAULT_DESC  = '평목 공방이 만드는 한옥 살창·창호 디자인 스튜디오. 나만의 문살 패턴을 직접 설계하고 주문하세요.';
const SITE_DEFAULT_IMAGE = SITE_URL . '/src/assets/logo.png';

function meta_tags(): void {
    echo '<meta name="google-site-verification" content="lNxBKwUVRTR6ewMlMqeNWIn_DfCn3ItScYvG-l6Yxr0">' . "\n    ";
    echo '<link rel="icon" type="image/png" href="/src/assets/favicon.png">' . "\n    ";
    echo '<link rel="apple-touch-icon" href="/src/assets/apple-touch-icon.png">' . "\n    ";
    $m = page_meta();
    $title = $m['title'] ?: SITE_DEFAULT_TITLE;
    $desc  = $m['description'] ?: SITE_DEFAULT_DESC;
    $image = $m['og_image'] ?: SITE_DEFAULT_IMAGE;
    $path  = strtok($_SERVER['REQUEST_URI'] ?? ($_SERVER['PHP_SELF'] ?? '/'), '?');

    echo '<title>' . htmlspecialchars($title, ENT_QUOTES) . '</title>' . "\n    ";
    echo '<meta name="description" content="' . htmlspecialchars($desc, ENT_QUOTES) . '">' . "\n    ";
    if ($m['keywords']) echo '<meta name="keywords" content="' . htmlspecialchars($m['keywords'], ENT_QUOTES) . '">' . "\n    ";
    echo '<link rel="canonical" href="' . htmlspecialchars(SITE_URL . $path, ENT_QUOTES) . '">' . "\n    ";
    echo '<meta property="og:title"       content="' . htmlspecialchars($title, ENT_QUOTES) . '">' . "\n    ";
    echo '<meta property="og:description" content="' . htmlspecialchars($desc, ENT_QUOTES) . '">' . "\n    ";
    echo '<meta property="og:image"       content="' . htmlspecialchars($image, ENT_QUOTES) . '">' . "\n    ";
}

// 조직 정보 구조화 데이터(JSON-LD) — 홈페이지 등 대표 페이지에서 1회 출력
function organization_jsonld(): void {
    echo '<script type="application/ld+json">' . json_encode([
        '@context'    => 'https://schema.org',
        '@type'       => 'Organization',
        'name'        => '평목',
        'url'         => SITE_URL,
        'logo'        => SITE_DEFAULT_IMAGE,
        'description' => SITE_DEFAULT_DESC,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n    ";
}

// 블로그 글 구조화 데이터(JSON-LD) — blog/detail.php에서 글마다 호출
function article_jsonld(array $post, string $url, string $image, string $description): void {
    echo '<script type="application/ld+json">' . json_encode([
        '@context'      => 'https://schema.org',
        '@type'         => 'Article',
        'headline'      => $post['title'],
        'description'   => $description,
        'image'         => $image,
        'datePublished' => date('c', strtotime($post['created_at'])),
        'url'           => $url,
        'author'        => ['@type' => 'Organization', 'name' => '평목'],
        'publisher'     => [
            '@type' => 'Organization',
            'name'  => '평목',
            'logo'  => ['@type' => 'ImageObject', 'url' => SITE_DEFAULT_IMAGE],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n    ";
}
