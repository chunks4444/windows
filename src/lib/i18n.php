<?php
// 다국어 지원 핵심 헬퍼. .htaccess가 /en/... 요청의 접두사만 떼고 나머지 라우팅은 그대로 태우므로,
// 언어 판별은 재작성 뒤에도 원본 그대로 남는 $_SERVER['REQUEST_URI']의 /en/ 접두사로 한다.
function current_lang(): string {
    static $lang = null;
    if ($lang !== null) return $lang;
    // API 엔드포인트는 URL에 /en/ 접두사가 없어 경로로 언어를 알 수 없다.
    // 그래서 프런트가 X-Pmok-Lang 헤더로 알려주면 그걸 먼저 따른다.
    if (($_SERVER['HTTP_X_PMOK_LANG'] ?? '') === 'en') return $lang = 'en';
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $lang = preg_match('#^/en(/|$)#', $path) ? 'en' : 'ko';
    return $lang;
}

function is_en(): bool { return current_lang() === 'en'; }

// UI 문구. src/lib/i18n/{ko,en}.php의 배열에서 키를 찾는다. en에 키가 없으면 ko로, 그마저 없으면 $key 자체를 보여준다.
function t(string $key): string {
    static $dict = [];
    $lang = current_lang();
    if (!isset($dict[$lang])) {
        $file = __DIR__ . "/i18n/{$lang}.php";
        $dict[$lang] = is_file($file) ? require $file : [];
    }
    if (isset($dict[$lang][$key])) return $dict[$lang][$key];
    if ($lang !== 'ko') {
        if (!isset($dict['ko'])) $dict['ko'] = require __DIR__ . '/i18n/ko.php';
        if (isset($dict['ko'][$key])) return $dict['ko'][$key];
    }
    return $key;
}

// 창호 전문 용어. 단순 번역이 아니라 "로마자 표기 (영문 설명)" 형태의 용어집 —
// i18n_terms 테이블에서 관리(어드민 src/admin/i18n_terms.php). en 모드가 아니거나
// 용어집에 없으면 원문(한글) 그대로 반환. 요청당 한 번만 테이블 전체를 읽어 캐시한다.
function term(string $korean): string {
    static $terms = null;
    if (!is_en()) return $korean;
    if ($terms === null) {
        try {
            require_once __DIR__ . '/db.php';
            $terms = db()->query('SELECT korean, english FROM i18n_terms')->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Throwable $e) {
            $terms = [];
        }
    }
    return $terms[$korean] ?? $korean;
}

// 내부 링크에 현재 언어 접두사를 붙인다. en 모드에서 /collection/ 같은 링크를 그대로 두면
// 클릭 순간 한국어로 돌아가 버리므로, 사이트 내부 링크는 모두 이 함수를 거치게 한다.
// 외부 URL(http…)·앵커(#)·mailto/tel은 그대로 반환.
function lang_href(string $path): string {
    if (!is_en()) return $path;
    if ($path === '' || $path[0] !== '/') return $path;
    return '/en' . $path;
}

// 현재 경로를 유지한 채 언어만 바꾼 URL (nav 언어 스위처용)
function lang_switch_url(string $targetLang): string {
    $uri  = $_SERVER['REQUEST_URI'] ?? '/';
    $path = preg_replace('#^/en(?=/|$)#', '', $uri);
    if ($path === '') $path = '/';
    return $targetLang === 'en' ? ('/en' . $path) : $path;
}
