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

// 엔진 페이지 UI 문구. t()와 달리 "한글 원문"을 키로 쓴다 — 이유는 src/lib/i18n/engine.php 머리말 참고.
// en 모드가 아니거나 사전에 없으면 한글 원문을 그대로 돌려주므로, 빠진 문구가 있어도 화면은 깨지지 않는다.
function te(string $korean): string {
    static $dict = null;
    if (!is_en()) return $korean;
    if ($dict === null) $dict = require __DIR__ . '/i18n/engine.php';
    return $dict[$korean] ?? $korean;
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

// 영문 블로그 글 하단 "용어 풀이" 박스용. 용어집에서 "로마자 (설명)" 형태인 항목만 골라,
// 주어진 영문 본문에 로마자 표기가 실제로 나오는 것을 본문 등장 순서대로 [로마자, 설명] 배열로 돌려준다.
// 본문마다 첫 등장에 풀이를 넣는 대신 여기 한 곳에서 모아 보여주려는 것 — 용어집(어드민)에
// 항목을 추가하면 모든 글에 자동 반영된다. 설명 없는 항목(hinged, Hanok 등)은 제외.
function glossary_terms_in(string $html): array {
    require_once __DIR__ . '/db.php';
    try {
        $rows = db()->query('SELECT english FROM i18n_terms')->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e) {
        return [];
    }
    $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $found = [];
    foreach ($rows as $english) {
        if (!preg_match('/^(.+?)\s*\((.+)\)\s*$/u', $english, $m)) continue;
        $roman = trim($m[1]);
        $key = mb_strtolower($roman);
        if (isset($found[$key])) continue;   // 격자빗살/격자밋살처럼 같은 영문을 가진 중복 항목
        // 앞뒤가 글자나 하이픈이면 다른 단어의 일부 — Gyeokja-bit-sal 안의 bit-sal을 따로 잡지 않게 한다
        if (preg_match('/(?<![\p{L}\p{N}-])' . preg_quote($roman, '/') . '(?![\p{L}\p{N}-])/iu', $text, $hit, PREG_OFFSET_CAPTURE)) {
            $found[$key] = ['term' => $roman, 'desc' => trim($m[2]), 'pos' => $hit[0][1]];
        }
    }
    usort($found, fn($a, $b) => $a['pos'] <=> $b['pos']);
    return $found;
}

// term()의 짧은 형태 — 뒤에 붙는 "(영문 설명)"을 떼고 로마자 표기만 돌려준다.
// 용어집 형식("Beomsal-jangji (Wide-bar Lattice Door)")은 본문에서 처음 나올 때 설명하려고 만든 것이라
// 엔진 사이드바의 좁은 select에 넣으면 목록이 화면을 넘칠 만큼 길어진다.
// 선택지가 전부 살 이름인 자리에서는 로마자 표기만으로 충분하다.
function term_short(string $korean): string {
    return preg_replace('/\s*\([^)]*\)\s*$/u', '', term($korean));
}

// 내부 링크에 현재 언어 접두사를 붙인다. en 모드에서 /collection/ 같은 링크를 그대로 두면
// 클릭 순간 한국어로 돌아가 버리므로, 사이트 내부 링크는 모두 이 함수를 거치게 한다.
// 외부 URL(http…)·앵커(#)·mailto/tel은 그대로 반환.
function lang_href(string $path): string {
    if (!is_en()) return $path;
    if ($path === '' || $path[0] !== '/') return $path;
    return '/en' . $path;
}

// DB 행에서 "{field}_en" 영문 컬럼을 우선 반환하는 공용 헬퍼 — FAQ·블로그 글처럼 자유서술형이라
// 용어집(term())으로 못 다루고 컬럼을 따로 둔 콘텐츠에 쓴다. en 모드가 아니거나 영문 컬럼이
// 비어있으면(어드민 미입력) 한글 원문으로 폴백한다.
function db_field(array $row, string $field): string {
    if (is_en() && !empty($row[$field . '_en'])) return $row[$field . '_en'];
    return $row[$field] ?? '';
}

// 현재 경로를 유지한 채 언어만 바꾼 URL (nav 언어 스위처용)
function lang_switch_url(string $targetLang): string {
    $uri  = $_SERVER['REQUEST_URI'] ?? '/';
    $path = preg_replace('#^/en(?=/|$)#', '', $uri);
    if ($path === '') $path = '/';
    return $targetLang === 'en' ? ('/en' . $path) : $path;
}
