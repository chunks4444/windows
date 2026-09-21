<?php
// 로컬 개발서버(php -S) 전용 라우터.
// 운영은 Apache + .htaccess가 라우팅하므로 이 파일을 거치지 않는다. 로컬 php -S는 .htaccess를
// 읽지 못해(존재하지 않는 경로를 모두 루트 index.php로 넘겨버림) 클린 URL과 /en/ 접두사가
// 동작하지 않으므로, .htaccess의 라우팅 규칙을 여기서 같은 순서로 재현한다.
$uri  = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH) ?? '/';

// uploads/ 하위 파일은 .gitignore 대상이라 로컬 저장소엔 없고 운영서버에만 존재한다.
// 로컬에 없는 /uploads/ 요청은 운영서버 이미지로 302 리다이렉트해 로컬에서도 바로 보이게 한다.
if (strpos($path, '/uploads/') === 0 && !is_file(__DIR__ . $path)) {
    header('Location: https://pyeongmok.com' . $path);
    return true;
}

// 실제 파일이면 그대로 서빙 (css/js/img, /src/... 직접 접근 등)
if ($path !== '/' && is_file(__DIR__ . $path)) return false;

// 다국어: /en 접두사를 떼고 아래 규칙을 그대로 적용한다.
// 언어 판별(current_lang())은 $_SERVER['REQUEST_URI']를 보므로 REQUEST_URI는 건드리지 않는다.
$route = preg_replace('#^/en(?=/|$)#', '', $path);
if ($route === '') $route = '/';

$serve = static function (string $file, array $query = []): bool {
    $_GET = $query + $_GET;
    require __DIR__ . '/' . $file;
    return true;
};

// 섹션 홈
$sectionHomes = [
    '/'            => 'index.php',
    '/blog/'       => 'src/blog/index.php',
    '/portfolio/'  => 'src/portfolio/index.php',
    '/collection/' => 'src/collection/index.php',
    '/guide/'      => 'src/guide/index.php',
    '/company/'    => 'src/company/index.php',
    '/privacy'     => 'src/legal/privacy.php',
    '/terms'       => 'src/legal/terms.php',
];
$normalized = rtrim($route, '/') === '' ? '/' : rtrim($route, '/') . '/';
if (isset($sectionHomes[$normalized])) return $serve($sectionHomes[$normalized]);
if (isset($sectionHomes[rtrim($route, '/')])) return $serve($sectionHomes[rtrim($route, '/')]);

// 상세 페이지 / 하위 페이지
if (preg_match('#^/blog/([^/]+)/?$#', $route, $m))      return $serve('src/blog/detail.php', ['slug' => urldecode($m[1])]);
if (preg_match('#^/portfolio/detail/?$#', $route))       return $serve('src/portfolio/detail.php');
if (preg_match('#^/portfolio/([^/]+)/?$#', $route, $m))  return $serve('src/portfolio/detail.php', ['slug' => urldecode($m[1])]);

foreach (['collection', 'guide', 'mypage'] as $section) {
    if (preg_match('#^/' . $section . '/([a-zA-Z][a-zA-Z0-9_-]*)/?$#', $route, $m)) {
        $file = "src/{$section}/{$m[1]}.php";
        if (is_file(__DIR__ . '/' . $file)) return $serve($file);
    }
}

if ($route === '/sitemap.xml') return $serve('sitemap.php');
if ($route === '/rss.xml')     return $serve('rss.php');

// /en/... 인데 위 규칙에 안 걸리면 접두사 뗀 실제 파일을 찾아본다 (예: /en/src/engine/classic/classic.php)
if ($route !== $path && is_file(__DIR__ . $route)) return $serve(ltrim($route, '/'));

return false;
