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
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
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
$check('가이드 CSS 자산 로드 (/src/guide/guide.css)', function () use ($base) {
    $r = http_call('GET', $base . '/src/guide/guide.css');
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

// ── 7. 6엔진 페이지 로드 ──
foreach (['classic', 'square', 'cross', 'diamond', 'triangle', 'hexagon'] as $engine) {
    $check("엔진 페이지 로드: $engine", function () use ($base, $engine) {
        $r = http_call('GET', "$base/src/engine/$engine/$engine.php");
        return [$r['code'] === 200, "HTTP {$r['code']}"];
    });
}

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
