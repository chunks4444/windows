<?php
// 다국어 지원 핵심 헬퍼. .htaccess가 /en/... 요청의 접두사만 떼고 나머지 라우팅은 그대로 태우므로,
// 언어 판별은 재작성 뒤에도 원본 그대로 남는 $_SERVER['REQUEST_URI']의 /en/ 접두사로 한다.
function current_lang(): string {
    static $lang = null;
    if ($lang !== null) return $lang;
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
// src/lib/i18n/terms.php에서 관리. en 모드가 아니거나 용어집에 없으면 원문(한글) 그대로 반환.
function term(string $korean): string {
    static $terms = null;
    if (!is_en()) return $korean;
    if ($terms === null) $terms = require __DIR__ . '/i18n/terms.php';
    return $terms[$korean] ?? $korean;
}

// 현재 경로를 유지한 채 언어만 바꾼 URL (nav 언어 스위처용)
function lang_switch_url(string $targetLang): string {
    $uri  = $_SERVER['REQUEST_URI'] ?? '/';
    $path = preg_replace('#^/en(?=/|$)#', '', $uri);
    if ($path === '') $path = '/';
    return $targetLang === 'en' ? ('/en' . $path) : $path;
}
