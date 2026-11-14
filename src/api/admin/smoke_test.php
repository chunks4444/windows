<?php
// 배포 직후 관리자가 클릭 한 번으로 핵심 기능이 살아있는지 확인하는 스모크 테스트.
// 실제 사이트 자신에게 HTTP 요청을 보내 라우팅(.htaccess)까지 포함해 검증한다 —
// 함수 직접 호출로는 못 잡는 라우팅/리다이렉트 버그(예: 2026-07-08 가이드 CSS 404)를 잡기 위함.
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/meta.php'; // SITE_URL 상수

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403); echo json_encode(['error' => '슈퍼 권한이 필요합니다.']); exit;
}

// 관리자 본인의 신원(sub/role)으로 새 토큰을 발급해 내부 API 호출에 사용 — 쿠키/헤더 어느 쪽으로
// 로그인했든 상관없이 동작하고, 별도 테스트 계정도 필요 없음
$authHeader = 'Bearer ' . jwt_encode(['sub' => $payload['sub'], 'role' => $payload['role']]);

function http_call(string $method, string $url, ?array $body = null, ?string $auth = null): array {
    $ch = curl_init($url);
    $headers = ['Content-Type: application/json'];
    if ($auth) $headers[] = 'Authorization: ' . $auth;
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 10,
        // 빈 UA·curl UA는 .htaccess에서 403 처리되므로 전용 UA를 붙여야 내부 호출이 통과함
        CURLOPT_USERAGENT      => 'PyeongmokSmokeTest/1.0',
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    return ['code' => $code, 'body' => $raw, 'json' => $raw ? json_decode($raw, true) : null, 'curl_error' => $err];
}

$base = SITE_URL;
$results = [];
$check = function (string $name, callable $fn) use (&$results) {
    $start = microtime(true);
    try {
        [$pass, $detail] = $fn();
    } catch (Throwable $e) {
        $pass = false; $detail = 'Exception: ' . $e->getMessage();
    }
    $results[] = ['name' => $name, 'pass' => $pass, 'detail' => $detail, 'ms' => round((microtime(true) - $start) * 1000)];
};

// ── 1. 공개 페이지 200 확인 (라우팅 회귀 검출) ──
foreach ([
    '/'            => '홈',
    '/guide/'      => '가이드 홈',
    '/guide/order' => '가이드 개별 페이지',
    '/blog/'       => '블로그',
    '/portfolio/'  => '포트폴리오',
    '/collection/' => '컬렉션',
    '/company/'    => '평목소개',
] as $path => $label) {
    $check("페이지 로드: $label ($path)", function () use ($base, $path) {
        $r = http_call('GET', $base . $path);
        return [$r['code'] === 200, "HTTP {$r['code']}"];
    });
}

// ── 2. 섹션 폴더 안에 섞인 정적 자산이 라우팅 규칙에 안 잡아먹히는지 (2026-07-08 가이드 CSS 404 재발 방지) ──
$check('가이드 CSS 자산 로드 (/src/css/guide.css)', function () use ($base) {
    $r = http_call('GET', $base . '/src/css/guide.css');
    return [$r['code'] === 200, "HTTP {$r['code']}"];
});

// ── 3. sitemap / robots ──
$check('sitemap.xml', function () use ($base) {
    $r = http_call('GET', $base . '/sitemap.xml');
    return [$r['code'] === 200 && strpos((string)$r['body'], '<?xml') === 0, "HTTP {$r['code']}"];
});
$check('robots.txt', function () use ($base) {
    $r = http_call('GET', $base . '/robots.txt');
    return [$r['code'] === 200, "HTTP {$r['code']}"];
});

// ── 4. 인증 흐름 ──
$check('로그인 프로필 조회 (/src/api/auth/profile.php)', function () use ($base, $authHeader) {
    $r = http_call('GET', $base . '/src/api/auth/profile.php', null, $authHeader);
    $ok = $r['code'] === 200 && !empty($r['json']['user']['email'] ?? null);
    return [$ok, "HTTP {$r['code']}"];
});
$check('마이페이지 대시보드 (/mypage/dashboard)', function () use ($base, $authHeader) {
    // 쿠키 기반 페이지라 Authorization 헤더만으론 로그인 화면이 뜰 수 있음 — 200 여부만 확인(라우팅 검증 목적)
    $r = http_call('GET', $base . '/mypage/dashboard', null, $authHeader);
    return [$r['code'] === 200, "HTTP {$r['code']}"];
});

// ── 5. 주문 생성→취소 실제 플로우 (2026-07-08 status enum 하드코딩 버그 재발 방지) ──
$orderId = null;
$check('주문 생성 (/src/api/orders/create.php)', function () use ($base, $authHeader, &$orderId) {
    $r = http_call('POST', $base . '/src/api/orders/create.php', [
        'engine'       => 'square',
        'title'        => 'SMOKE_TEST_' . date('YmdHis'),
        'due_date'     => date('Y-m-d', strtotime('+14 days')),
        'ship_address' => '스모크테스트 주소',
        'ship_phone'   => '010-0000-0000',
        'memo'         => '관리자 스모크 테스트로 생성됨 — 자동 취소됨',
    ], $authHeader);
    $ok = $r['code'] === 200 && !empty($r['json']['ok']) && !empty($r['json']['order_id']);
    if ($ok) $orderId = $r['json']['order_id'];
    return [$ok, $ok ? "order_id={$orderId}" : "HTTP {$r['code']} / " . ($r['body'] ?: $r['curl_error'])];
});
$check('주문 취소 (정리, /src/api/orders/cancel.php)', function () use ($base, $authHeader, &$orderId) {
    if (!$orderId) return [false, '이전 단계(주문 생성)가 실패해 건너뜀'];
    $r = http_call('POST', $base . '/src/api/orders/cancel.php', ['id' => $orderId], $authHeader);
    $ok = $r['code'] === 200 && !empty($r['json']['ok']);
    return [$ok, $ok ? "order_id={$orderId} 취소됨" : "HTTP {$r['code']}"];
});

// ── 6. 주문 목록 조회 (고객/관리자 양쪽) ──
$check('내 주문내역 조회 (/src/api/orders/list.php)', function () use ($base, $authHeader) {
    $r = http_call('GET', $base . '/src/api/orders/list.php', null, $authHeader);
    return [$r['code'] === 200 && isset($r['json']['orders']), "HTTP {$r['code']}"];
});
$check('관리자 주문 목록 조회 (/src/api/admin/orders.php)', function () use ($base, $authHeader) {
    $r = http_call('POST', $base . '/src/api/admin/orders.php', ['page' => 1], $authHeader);
    return [$r['code'] === 200 && isset($r['json']['orders']), "HTTP {$r['code']}"];
});

// ── 7. 7엔진 페이지 로드 ──
foreach (['classic', 'square', 'cross', 'diamond', 'triangle', 'hexagon', 'mondrian'] as $engine) {
    $check("엔진 페이지 로드: $engine", function () use ($base, $engine) {
        $r = http_call('GET', "$base/src/engine/$engine/$engine.php");
        return [$r['code'] === 200, "HTTP {$r['code']}"];
    });
}

// ═════ 8~11. 최근 실제로 터졌던 문제들의 재발 방지 검사 (2026-11-14) ═════

// 응답 앞부분(헤더 + 처음 N바이트)만 읽는다 — 큰 PDF를 통째로 받지 않으려는 용도
function http_peek(string $url, int $bytes = 1024): array {
    $ch = curl_init($url);
    $hdr = ''; $buf = '';
    curl_setopt_array($ch, [
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_USERAGENT      => 'PyeongmokSmokeTest/1.0',
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HEADERFUNCTION => function ($c, $line) use (&$hdr) { $hdr .= $line; return strlen($line); },
        // 필요한 만큼 받으면 0을 돌려줘 전송을 끊는다
        CURLOPT_WRITEFUNCTION  => function ($c, $data) use (&$buf, $bytes) { $buf .= $data; return strlen($buf) >= $bytes ? 0 : strlen($data); },
    ]);
    @curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return ['code' => $code, 'headers' => $hdr, 'body' => $buf];   // curl_close()는 PHP 8.0부터 효과가 없고 8.5에서 폐지 예정이라 부르지 않는다
}
// <meta property="og:xxx" content="..."> 값 꺼내기 (없으면 null)
function og_meta(string $html, string $prop): ?string {
    return preg_match('~<meta property="' . preg_quote($prop, '~') . '"\s+content="([^"]*)"~', $html, $m) ? html_entity_decode($m[1], ENT_QUOTES) : null;
}
function has_text(?string $body, string $needle): bool { return $body !== null && strpos($body, $needle) !== false; }

$pdo = db();

// ── 8. 회사 소개 포트폴리오 모달 (2026-11-14: 다운로드 링크의 data-bs-dismiss가 부트스트랩에서 링크 기본 동작을 취소해 PDF가 안 받아짐) ──
$companyHtml = null; $pdfDlHref = null; $pdfCover = null;
$check('회사 소개: 포트폴리오 모달 마크업', function () use ($base, &$companyHtml, &$pdfDlHref, &$pdfCover) {
    $r = http_call('GET', $base . '/company/');
    if ($r['code'] !== 200) return [false, "HTTP {$r['code']}"];
    $companyHtml = (string)$r['body'];
    if (strpos($companyHtml, 'id="portfolioPdfModal"') === false) return [true, '공개 중인 포트폴리오 PDF가 없어 모달이 없음 — 건너뜀'];
    if (!preg_match('~<a\b[^>]*id="abPdfDl"[^>]*>~', $companyHtml, $m)) return [false, '모달에 다운로드 링크(#abPdfDl)가 없음'];
    $tag = $m[0];
    if (strpos($tag, 'data-bs-dismiss') !== false) return [false, '다운로드 링크에 data-bs-dismiss가 있음 → 부트스트랩이 다운로드를 취소함'];
    if (strpos($tag, ' download') === false) return [false, '다운로드 링크에 download 속성이 없음'];
    if (!preg_match('~href="([^"]*&amp;dl=1)"~', $tag, $h)) return [false, '다운로드 링크 주소에 &dl=1이 없음'];
    $pdfDlHref = html_entity_decode($h[1], ENT_QUOTES);
    if (preg_match('~class="ab-pdf-cover" src="([^"]+)"~', $companyHtml, $c)) $pdfCover = html_entity_decode($c[1], ENT_QUOTES);
    return [true, '링크에 data-bs-dismiss 없음, download·&dl=1 있음'];
});
$check('회사 소개: PDF 실제 다운로드 (파일로 받아지고 %PDF 시작)', function () use ($base, &$pdfDlHref) {
    if (!$pdfDlHref) return [true, '모달 없음/링크 없음 — 건너뜀'];
    $r = http_peek($base . $pdfDlHref, 1024);
    $okType = stripos($r['headers'], 'content-type: application/pdf') !== false;
    $okDisp = stripos($r['headers'], 'content-disposition: attachment') !== false;
    $ok = $r['code'] === 200 && $okType && $okDisp && substr($r['body'], 0, 5) === '%PDF-';
    return [$ok, "HTTP {$r['code']} · PDF형식 " . ($okType ? 'O' : 'X') . ' · attachment ' . ($okDisp ? 'O' : 'X') . ' · %PDF ' . (substr($r['body'], 0, 5) === '%PDF-' ? 'O' : 'X')];
});
$check('회사 소개: PDF 표지 미리보기 이미지', function () use ($base, &$pdfCover) {
    if (!$pdfCover) return [true, '표지 이미지 없음(gs 없음 등) — 건너뜀'];
    $r = http_peek($base . $pdfCover, 4096);
    $isJpeg = substr($r['body'], 0, 2) === "\xFF\xD8";
    return [$r['code'] === 200 && $isJpeg, "HTTP {$r['code']}" . ($isJpeg ? ' · JPEG' : ' · 이미지 아님')];
});
$check('관리자 포트폴리오 PDF API (권한 O → 200 / 권한 X → 401·403)', function () use ($base, $authHeader) {
    $r = http_call('GET', $base . '/src/api/admin/portfolio_pdf.php', null, $authHeader);
    $anon = http_call('GET', $base . '/src/api/admin/portfolio_pdf.php');
    $ok = $r['code'] === 200 && !empty($r['json']['ok']) && in_array($anon['code'], [401, 403], true);
    return [$ok, "관리자 HTTP {$r['code']} · 비로그인 HTTP {$anon['code']}"];
});

// ── 9. 도면 공유 미리보기 (2026-11-14: 공유 전 도면을 편집기로 열면 엔진 기본 이미지(井 아이콘)가 나가고, 주소가 늘 같아 카카오가 옛 미리보기를 재사용) ──
$sharedRow = $pdo->query("SELECT id, type, title, thumbnail FROM drawings WHERE is_shared = 1 AND thumbnail LIKE '/uploads/drawing_thumbs/%' ORDER BY updated_at DESC LIMIT 1")->fetch();
$check('공유 도면: og:image가 엔진 기본 이미지가 아니라 도면 미리보기', function () use ($base, $sharedRow) {
    if (!$sharedRow) return [true, '공유 중인 도면이 없어 건너뜀'];
    $url = "$base/src/engine/{$sharedRow['type']}/{$sharedRow['type']}.php?drawing_id={$sharedRow['id']}";
    $r = http_call('GET', $url);
    if ($r['code'] !== 200) return [false, "HTTP {$r['code']}"];
    $img = og_meta((string)$r['body'], 'og:image');
    $ok = $img !== null && strpos($img, '/src/api/drawings/og_image.php?t=') !== false;
    return [$ok, "도면 #{$sharedRow['id']} og:image = " . ($img ?? '(없음)')];
});
$check('공유 도면: og:url이 도면 주소 (canonical은 엔진 주소 유지)', function () use ($base, $sharedRow) {
    if (!$sharedRow) return [true, '공유 중인 도면이 없어 건너뜀'];
    $url = "$base/src/engine/{$sharedRow['type']}/{$sharedRow['type']}.php?drawing_id={$sharedRow['id']}";
    $html = (string)http_call('GET', $url)['body'];
    $ogUrl = og_meta($html, 'og:url');
    $canon = preg_match('~<link rel="canonical" href="([^"]*)"~', $html, $m) ? $m[1] : '';
    $ok = $ogUrl !== null && strpos($ogUrl, 'drawing_id=' . $sharedRow['id']) !== false && strpos($canon, 'drawing_id') === false;
    return [$ok, 'og:url = ' . ($ogUrl ?? '(없음)') . ' · canonical = ' . ($canon ?: '(없음)')];
});
$check('공유 도면: 미리보기 이미지가 1200×630 JPEG로 만들어짐', function () use ($base, $sharedRow) {
    if (!$sharedRow) return [true, '공유 중인 도면이 없어 건너뜀'];
    if (!preg_match('~/(\d+_[0-9a-f]{8})~', $sharedRow['thumbnail'], $m)) return [false, '썸네일 이름 형식이 이상함'];
    $r = http_call('GET', $base . '/src/api/drawings/og_image.php?t=' . $m[1]);
    $info = $r['body'] ? @getimagesizefromstring($r['body']) : false;
    $ok = $r['code'] === 200 && $info && $info['mime'] === 'image/jpeg' && $info[0] === 1200 && $info[1] === 630;
    return [$ok, "HTTP {$r['code']} · " . ($info ? "{$info['mime']} {$info[0]}×{$info[1]}" : '이미지 아님')];
});
$check('공유 도면: 주소의 도면 버전(&v=) 처리 (정상 값은 반영, 잘못된 값은 무시)', function () use ($base, $sharedRow) {
    if (!$sharedRow) return [true, '공유 중인 도면이 없어 건너뜀'];
    $url = "$base/src/engine/{$sharedRow['type']}/{$sharedRow['type']}.php?drawing_id={$sharedRow['id']}";
    $good = og_meta((string)http_call('GET', $url . '&v=1700000000_abcdef12')['body'], 'og:url');
    $bad  = og_meta((string)http_call('GET', $url . '&v=' . rawurlencode('"><script>x</script>'))['body'], 'og:url');
    $ok = $good !== null && strpos($good, 'v=1700000000_abcdef12') !== false && $bad !== null && strpos($bad, '<script') === false && strpos($bad, 'v=') === false;
    return [$ok, '정상 값 → ' . ($good ?? '(없음)') . ' · 잘못된 값 → ' . ($bad ?? '(없음)')];
});
$privRow = $pdo->query("SELECT d.id, d.type FROM drawings d WHERE d.is_shared = 0 AND NOT EXISTS (SELECT 1 FROM library_patterns lp WHERE lp.drawing_id = d.id AND lp.is_active = 1) ORDER BY d.updated_at DESC LIMIT 1")->fetch();
$check('비공유 도면: 도면 정보가 공유 메타로 새지 않음 (og:url 없음, 도면 이미지 아님)', function () use ($base, $privRow) {
    if (!$privRow) return [true, '비공유 도면이 없어 건너뜀'];
    $html = (string)http_call('GET', "$base/src/engine/{$privRow['type']}/{$privRow['type']}.php?drawing_id={$privRow['id']}")['body'];
    $img = og_meta($html, 'og:image');
    $ok = og_meta($html, 'og:url') === null && ($img === null || strpos($img, 'og_image.php') === false);
    return [$ok, "도면 #{$privRow['id']} og:url " . (og_meta($html, 'og:url') === null ? '없음' : '있음 ← 문제') . ' · og:image ' . ($img ?? '(없음)')];
});
// 편집기 쪽 공유 코드가 사라지지 않았는지 (다른 기기의 옛 파일이 덮어쓰는 사고 방지)
$check('공유 코드: drawing-sync.js (메타 갱신·버전 값)', function () use ($base) {
    $r = http_call('GET', $base . '/src/js/drawing-sync.js');
    $ok = $r['code'] === 200 && has_text($r['body'], '_applyShareMeta') && has_text($r['body'], 'shareVersion');
    return [$ok, "HTTP {$r['code']}"];
});
foreach (['classic', 'square', 'cross', 'diamond', 'triangle', 'hexagon', 'mondrian'] as $engine) {
    $check("공유 코드: 엔진 $engine 공유 주소에 도면 버전", function () use ($base, $engine) {
        $r = http_call('GET', "$base/src/js/$engine.js");
        return [$r['code'] === 200 && has_text($r['body'], 'shareVersion'), "HTTP {$r['code']}"];
    });
}
$check('공유 코드: 대시보드 공유 주소에 도면 버전', function () use ($base) {
    $r = http_call('GET', $base . '/src/js/dashboard.js');
    return [$r['code'] === 200 && has_text($r['body'], "'&v=' + ver[1]"), "HTTP {$r['code']}"];
});

// ── 10. 블로그 글 상세 (함께 읽으면 좋은 글, 제목 아래 시리즈명) ──
$postRow = $pdo->query("SELECT slug, series_id FROM blog_posts WHERE is_active = 1 AND slug <> '' ORDER BY id DESC LIMIT 1")->fetch();
$check('블로그 글 상세: 함께 읽으면 좋은 글·시리즈명', function () use ($base, $postRow) {
    if (!$postRow) return [true, '공개된 글이 없어 건너뜀'];
    $r = http_call('GET', $base . '/blog/' . rawurlencode($postRow['slug']));
    if ($r['code'] !== 200) return [false, "HTTP {$r['code']}"];
    $cards = substr_count((string)$r['body'], 'class="bd-related-card"');
    $kicker = strpos((string)$r['body'], 'class="bd-series-kicker"') !== false;
    $ok = $cards >= 1 && (!$postRow['series_id'] || $kicker);
    return [$ok, "추천 글 카드 {$cards}개 · 시리즈명 " . ($kicker ? '표시됨' : ($postRow['series_id'] ? '없음 ← 문제' : '시리즈 없는 글')) ];
});
$check('블로그 이미지 업로드: 투명 PNG 흰 바탕 처리 코드', function () use ($base) {
    $r = http_call('GET', $base . '/src/js/admin/blog.js');
    return [$r['code'] === 200 && has_text($r['body'], "ctx.fillStyle = '#ffffff'") && has_text($r['body'], 'ctx.fillRect(0, 0, w, h)'), "HTTP {$r['code']}"];
});

// ── 11. 관리자 메뉴 구성 ──
$check('관리자 메뉴: "홍보" 그룹에 포트폴리오 PDF', function () {
    $menu = require __DIR__ . '/../../lib/admin_sidenav_data.php';
    foreach ($menu as $g) {
        if ($g['title'] !== '홍보') continue;
        foreach ($g['items'] as $it) if ($it[0] === 'portfolio_pdf.php') return [true, '홍보 그룹: ' . implode(' · ', array_map(fn($i) => $i[2], $g['items']))];
    }
    return [false, '"홍보" 그룹에 portfolio_pdf.php 가 없음'];
});

// ── 12. 보안 기본 점검 ──
// 설정·라이브러리 파일을 주소로 직접 열었을 때 내용이 나가면 안 된다. 200이어도 본문이 비어 있으면(PHP가 실행만 됨) 통과,
// 소스가 보이거나 비어 있지 않은 본문이 나오면 실패 — PHP 처리가 깨졌을 때 비밀번호가 노출되는 사고를 잡는다.
foreach (['/src/lib/config.local.php', '/src/engine/config.local.php'] as $cfgPath) {
    $check("설정 파일 직접 접근 시 내용 비노출 ($cfgPath)", function () use ($base, $cfgPath) {
        $r = http_call('GET', $base . $cfgPath);
        $body = (string)$r['body'];
        $leak = strpos($body, '<?php') !== false || stripos($body, 'PMOK_') !== false || stripos($body, 'API_KEY') !== false || stripos($body, 'define(') !== false;
        $ok = !$leak && ($r['code'] !== 200 || $body === '');
        return [$ok, "HTTP {$r['code']} · 본문 " . strlen($body) . '바이트' . ($leak ? ' ← 소스가 보임!' : '')];
    });
}
$check('민감 API(다른 사용자로 로그인) 비로그인 거부 (/src/api/admin/impersonate.php)', function () use ($base) {
    $r = http_call('POST', $base . '/src/api/admin/impersonate.php', []);
    return [in_array($r['code'], [401, 403], true), "HTTP {$r['code']}"];
});
$check('관리자 페이지 비로그인 접근 시 로그인으로 이동 (/src/admin/portfolio_pdf.php)', function () use ($base) {
    $ch = curl_init($base . '/src/admin/portfolio_pdf.php');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 10, CURLOPT_USERAGENT => 'PyeongmokSmokeTest/1.0']);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return [in_array($code, [301, 302, 401, 403], true), "HTTP {$code}"];
});

$passCount = count(array_filter($results, fn($r) => $r['pass']));
echo json_encode([
    'total'    => count($results),
    'pass'     => $passCount,
    'fail'     => count($results) - $passCount,
    'all_pass' => $passCount === count($results),
    'checks'   => $results,
    'base_url' => $base,
    'ran_at'   => date('Y-m-d H:i:s'),
], JSON_UNESCAPED_UNICODE);
