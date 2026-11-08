<?php
// 블로그 검색 공용 함수 — 검색 결과 페이지(src/blog/search.php), 글 저장 API(src/api/admin/blog.php),
// 기존 글 일괄 채우기 스크립트(ops/blog_search_backfill.php)가 함께 쓴다.
//
// 방식: LIKE '%검색어%' 부분일치. FULLTEXT는 한국어 분절 문제가 있고 글이 수백 편 규모라 쓰지 않는다.
// 본문은 HTML이라 그대로 LIKE하면 태그·속성에도 걸리므로, 태그를 걷어낸 순수 텍스트를
// blog_posts.body_text / body_text_en 에 따로 저장해 두고 그 컬럼을 검색한다.
// 이표기(소슬↔솟을 등)는 term_aliases 표로 묶어 함께 찾는다.

const BLOG_SEARCH_MIN_LEN   = 2;    // 이보다 짧으면 검색하지 않음
const BLOG_SEARCH_MAX_LEN   = 50;   // 검색어 최대 글자 수
const BLOG_SEARCH_SNIPPET   = 40;   // 발췌문에서 검색어 앞뒤로 보여줄 글자 수
const BLOG_SEARCH_MAX_TERMS = 8;    // 이표기로 늘어난 검색어 최대 개수(쿼리 과대화 방지)

// HTML 본문 → 검색용 순수 텍스트. 블록 태그 자리에 공백을 넣어 앞뒤 문단 글자가 붙지 않게 한다.
function blog_body_text(?string $html): ?string {
    if ($html === null || $html === '') return $html === null ? null : '';
    $s = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', ' ', $html);
    $s = preg_replace('#<(br|/p|/div|/li|/h[1-6]|/blockquote|/tr|/td|/th|/figcaption)\b[^>]*>#i', '$0 ', $s);
    $s = strip_tags($s);
    $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $s = preg_replace('/[\s\x{00A0}\x{200B}]+/u', ' ', $s);
    return trim($s);
}

// 글 한 편의 body_text·body_text_en 다시 채우기 (저장 직후 호출)
function blog_refresh_body_text(PDO $pdo, int $id): void {
    $st = $pdo->prepare('SELECT content, content_en FROM blog_posts WHERE id=?');
    $st->execute([$id]);
    $r = $st->fetch(PDO::FETCH_ASSOC);
    if (!$r) return;
    $pdo->prepare('UPDATE blog_posts SET body_text=?, body_text_en=? WHERE id=?')
        ->execute([blog_body_text($r['content']), blog_body_text($r['content_en']), $id]);
}

// 검색어 정리: 앞뒤 공백 제거, 내부 연속 공백 하나로, 최대 길이 자르기
function blog_search_normalize(string $q): string {
    $q = trim(preg_replace('/\s+/u', ' ', $q));
    return mb_substr($q, 0, BLOG_SEARCH_MAX_LEN);
}

// 이표기 확장. 반환: ['terms' => [검색어, 이표기로 바꾼 검색어...], 'notes' => [[찾은 말, [함께 찾은 말...]], ...]]
// term_aliases 행 (alias → canonical)을 canonical 기준 묶음으로 모아, 검색어 안에 묶음의 어느
// 표기라도 들어 있으면 그 자리를 같은 묶음의 다른 표기로 바꾼 검색어를 추가한다(양방향).
// 예) 표: 투밀이→두밀이, 퇴밀이→두밀이 / 검색어 '투밀이 문살' → '두밀이 문살', '퇴밀이 문살'도 함께 검색
function blog_search_expand(PDO $pdo, string $q): array {
    $terms = [$q];
    $notes = [];
    try {
        $rows = $pdo->query('SELECT alias, canonical FROM term_aliases')->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return ['terms' => $terms, 'notes' => $notes]; // 표가 아직 없으면 이표기 없이 검색
    }
    $groups = [];
    foreach ($rows as $r) {
        $c = $r['canonical'];
        $groups[$c] ??= [$c];
        if (!in_array($r['alias'], $groups[$c], true)) $groups[$c][] = $r['alias'];
    }
    foreach ($groups as $members) {
        // 긴 표기부터 확인해 '퇴밀이'가 들어간 검색어에서 더 짧은 표기가 먼저 걸리는 일을 막는다
        usort($members, fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));
        foreach ($members as $m) {
            if ($m === '' || mb_stripos($q, $m) === false) continue;
            $others = array_values(array_filter($members, fn($x) => $x !== $m));
            foreach ($others as $o) {
                $t = str_ireplace($m, $o, $q);
                if (!in_array($t, $terms, true)) $terms[] = $t;
            }
            if ($others) $notes[] = [$m, $others];
            break;
        }
    }
    return ['terms' => array_slice($terms, 0, BLOG_SEARCH_MAX_TERMS), 'notes' => $notes];
}

// LIKE 패턴용 이스케이프 — 사용자가 %·_·\ 를 입력해도 글자 그대로 찾는다
function blog_like(string $s): string {
    return '%' . addcslashes($s, '%_\\') . '%';
}

// 검색 실행. 발행된 글(is_active=1)만, 제목에 걸린 글 → 본문에만 걸린 글, 같은 그룹 안에서는 최신순.
// 한글·영문 제목/본문을 모두 본다(영문판에서 한글 용어로 찾는 경우도 있어서).
function blog_search(PDO $pdo, array $terms, int $limit, int $offset): array {
    $titleConds = [];
    $bodyConds  = [];
    $params     = [];
    foreach (array_values($terms) as $i => $t) {
        $like = blog_like($t);
        $titleConds[] = "p.title LIKE :t{$i}a OR p.title_en LIKE :t{$i}b";
        $bodyConds[]  = "p.body_text LIKE :t{$i}c OR p.body_text_en LIKE :t{$i}d";
        $params[":t{$i}a"] = $params[":t{$i}b"] = $params[":t{$i}c"] = $params[":t{$i}d"] = $like;
    }
    $titleSql = '(' . implode(' OR ', $titleConds) . ')';
    $where    = "p.is_active = 1 AND ($titleSql OR " . implode(' OR ', $bodyConds) . ')';

    // PDO 네이티브 prepare에서는 같은 이름표를 두 번 쓸 수 없어서, 정렬용 제목 조건은 이름표를 따로 붙인다
    $orderParams = [];
    $orderSql = preg_replace_callback('/:(t\d+[ab])/', function ($m) use (&$orderParams, $params) {
        $orderParams[':o' . $m[1]] = $params[':' . $m[1]];
        return ':o' . $m[1];
    }, $titleSql);

    $cnt = $pdo->prepare("SELECT COUNT(*) FROM blog_posts p WHERE $where");
    $cnt->execute($params);
    $total = (int)$cnt->fetchColumn();

    $st = $pdo->prepare("
        SELECT p.id, p.title, p.title_en, p.slug, p.created_at, p.series_order,
               p.body_text, p.body_text_en, p.summary, p.summary_en,
               s.name AS series_name, s.name_en AS series_name_en,
               $orderSql AS title_hit
        FROM blog_posts p
        LEFT JOIN blog_series s ON s.id = p.series_id
        WHERE $where
        ORDER BY title_hit DESC, p.created_at DESC, p.id DESC
        LIMIT :lim OFFSET :off
    ");
    foreach ($params + $orderParams as $k => $v) $st->bindValue($k, $v);
    $st->bindValue(':lim', $limit, PDO::PARAM_INT);
    $st->bindValue(':off', $offset, PDO::PARAM_INT);
    $st->execute();
    return ['total' => $total, 'rows' => $st->fetchAll(PDO::FETCH_ASSOC)];
}

// 검색어가 처음 나오는 곳 앞뒤 40자 정도를 잘라 검색어를 <mark>로 강조한 HTML 한 줄.
// 본문에서 못 찾으면(제목에만 걸린 글) 요약 또는 본문 앞부분을 보여준다. 반환값은 이미 이스케이프된 HTML.
function blog_search_snippet(string $text, array $terms, string $fallback = ''): string {
    $pos = false; $hitLen = 0;
    foreach ($terms as $t) {
        $p = mb_stripos($text, $t);
        if ($p !== false && ($pos === false || $p < $pos)) { $pos = $p; $hitLen = mb_strlen($t); }
    }
    if ($pos === false) {
        $base = $fallback !== '' ? $fallback : $text;
        $cut  = mb_substr($base, 0, BLOG_SEARCH_SNIPPET * 2);
        return htmlspecialchars($cut . (mb_strlen($base) > mb_strlen($cut) ? '…' : ''));
    }
    $start = max(0, $pos - BLOG_SEARCH_SNIPPET);
    $len   = ($pos - $start) + $hitLen + BLOG_SEARCH_SNIPPET;
    $cut   = mb_substr($text, $start, $len);
    $pre   = $start > 0 ? '…' : '';
    $post  = ($start + $len) < mb_strlen($text) ? '…' : '';
    return $pre . blog_search_highlight($cut, $terms) . $post;
}

// 텍스트를 이스케이프하고 검색어(들)를 <mark>로 감싼다(대소문자 무시)
function blog_search_highlight(string $text, array $terms): string {
    $terms = array_filter($terms, fn($t) => $t !== '');
    if (!$terms) return htmlspecialchars($text);
    usort($terms, fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));
    $re = '/(' . implode('|', array_map(fn($t) => preg_quote($t, '/'), $terms)) . ')/iu';
    $parts = preg_split($re, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    $out = '';
    foreach ($parts as $i => $p) {
        $out .= $i % 2 ? '<mark>' . htmlspecialchars($p) . '</mark>' : htmlspecialchars($p);
    }
    return $out;
}

// 검색어 기록 — 관리자(슈퍼·관리자 권한)와 봇은 기록하지 않는다(블로그 조회수 카운터와 같은 기준)
function blog_search_log(PDO $pdo, string $q, int $resultCount): void {
    try {
        require_once __DIR__ . '/jwt.php';
        $payload = jwt_from_request();
        if ($payload && in_array($payload['role'] ?? '', ['s', 'm'], true)) return;
    } catch (Throwable $e) {}
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if ($ua === '' || preg_match('/bot|crawl|spider|slurp|facebookexternalhit|preview|yeti|daumoa|petalbot|semrush|ahrefs|mj12bot|dotbot|python-requests|curl|wget|headless/i', $ua)) return;
    try {
        $pdo->prepare('INSERT INTO search_logs (query, result_count) VALUES (?, ?)')->execute([$q, $resultCount]);
    } catch (Throwable $e) {}
}
