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
const GA4_MEASUREMENT_ID = 'G-P6LFE8ECVG';

// $override로 title/description/image를 지정하면 DB의 page_meta보다 우선한다
// (예: 도면 공유 페이지에서 도면 제목/썸네일로 OG 태그를 바꿔치기할 때 사용)
function meta_tags(?array $override = null): void {
    echo '<script async src="https://www.googletagmanager.com/gtag/js?id=' . GA4_MEASUREMENT_ID . '"></script>' . "\n    ";
    echo '<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("js",new Date());gtag("config","' . GA4_MEASUREMENT_ID . '");</script>' . "\n    ";
    echo '<meta name="google-site-verification" content="lNxBKwUVRTR6ewMlMqeNWIn_DfCn3ItScYvG-l6Yxr0">' . "\n    ";
    echo '<link rel="icon" type="image/png" href="/src/assets/favicon.png">' . "\n    ";
    echo '<link rel="apple-touch-icon" href="/src/assets/apple-touch-icon.png">' . "\n    ";
    $m = page_meta();
    $title = $override['title'] ?? ($m['title'] ?: SITE_DEFAULT_TITLE);
    $desc  = $override['description'] ?? ($m['description'] ?: SITE_DEFAULT_DESC);
    $image = $override['image'] ?? ($m['og_image'] ?: SITE_DEFAULT_IMAGE);
    $path  = strtok($_SERVER['REQUEST_URI'] ?? ($_SERVER['PHP_SELF'] ?? '/'), '?');

    echo '<title>' . htmlspecialchars($title, ENT_QUOTES) . '</title>' . "\n    ";
    echo '<meta name="description" content="' . htmlspecialchars($desc, ENT_QUOTES) . '">' . "\n    ";
    if ($m['keywords']) echo '<meta name="keywords" content="' . htmlspecialchars($m['keywords'], ENT_QUOTES) . '">' . "\n    ";
    $canonical = $override['canonical'] ?? (SITE_URL . $path);
    echo '<link rel="canonical" href="' . htmlspecialchars($canonical, ENT_QUOTES) . '">' . "\n    ";
    echo '<meta property="og:title"       content="' . htmlspecialchars($title, ENT_QUOTES) . '">' . "\n    ";
    echo '<meta property="og:description" content="' . htmlspecialchars($desc, ENT_QUOTES) . '">' . "\n    ";
    echo '<meta property="og:image"       content="' . htmlspecialchars($image, ENT_QUOTES) . '">' . "\n    ";
}

// 카카오톡 공유(Kakao Share SDK)용 JavaScript 키. site_config(key_name='kakao_js_key')에서 읽어온다.
// OAuth 로그인용 REST 키와는 별개 값 — Kakao Developers 콘솔의 "카카오톡 공유" 제품에서 발급.
function kakao_js_key(): ?string {
    static $key = null;
    if ($key !== null) return $key ?: null;
    require_once __DIR__ . '/db.php';
    try {
        $row = db()->query("SELECT value FROM site_config WHERE key_name = 'kakao_js_key'")->fetch();
        $key = $row['value'] ?? '';
    } catch (Throwable $e) {
        $key = '';
    }
    return $key ?: null;
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
