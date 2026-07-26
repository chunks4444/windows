<?php
// 차단된 IP는 어떤 페이지든 여기서 즉시 403으로 막는다.
// .htaccess의 php_value auto_prepend_file로 등록되어 모든 PHP 요청보다 먼저 실행됨 —
// 그래서 nav.php/logger.php처럼 페이지 중간에 include되는 지점에 넣지 않고 별도 파일로 분리했다.
if (php_sapi_name() === 'cli') return;

function _pmok_block_get_ip(): string {
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '-';
}

// 이 사이트엔 실제로 존재한 적 없는 워드프레스/취약점 스캐너 전용 경로 — 매번 로그를 뒤져
// 수동으로 차단하는 부담을 줄이려고, 이런 경로를 두드리면 그 즉시 자동으로 차단 등록한다.
// "/wp-content/uploads/..."(예전 워드프레스 시절 이미지 링크)는 구글/빙 크롤러가 재크롤링하며
// 정상적으로 404를 만나는 경우라 여기 포함하지 않는다 — 실제 취약점 탐색 경로만 골랐다.
const PMOK_AUTOBLOCK_PATTERNS = [
    'wp-admin', 'wp-login', 'wp-json', 'wp-includes', 'wp-content/plugins',
    '.env', '.git/', 'phpmyadmin', 'xmlrpc.php', 'cgi-bin',
    'vendor/phpunit', 'telescope', '_ignition', 'actuator', 'wlwmanifest.xml',
];

// 구글봇은 이 도메인의 예전 워드프레스 시절 URL(예: /wp-admin)을 재크롤링하다 위 패턴에
// 그대로 걸리는 경우가 실제로 있었다(사이트 전체가 오차단됨). 구글이 공식 발표한 크롤러
// 전용 대역(66.249.64.0/19, 다른 용도로 쓰이지 않음)은 자동 차단 대상에서 제외한다 —
// 404는 정상적으로 그대로 나가고, 차단만 안 한다.
function _pmok_is_known_crawler_ip(string $ip): bool {
    $long = ip2long($ip);
    if ($long === false) return false;
    // Google: 66.249.64.0/19
    return ($long & ~0x1FFF) === ip2long('66.249.64.0');
}

// 빙(Bing)은 크롤러 IP 대역이 넓고 자주 바뀌어 고정 CIDR로 못 박을 수 없다 — MS가 공식
// 안내하는 역DNS 검증 방식을 쓴다: PTR이 *.search.msn.com으로 끝나는지 확인한 뒤,
// 그 호스트명을 다시 정방향 조회해 같은 IP로 돌아오는지 대조(PTR 위조 방지).
// 취약점 경로에 실제로 걸렸을 때만 호출되므로(드문 경로) DNS 조회 비용은 일반 트래픽에
// 영향 없다.
function _pmok_is_verified_bingbot(string $ip): bool {
    $host = @gethostbyaddr($ip);
    if (!$host || $host === $ip) return false;
    if (!preg_match('/\.search\.msn\.com$/i', rtrim($host, '.'))) return false;
    return @gethostbyname($host) === $ip;
}

$_pmokBlockIp = _pmok_block_get_ip();
if ($_pmokBlockIp === '-') return;
$_pmokIsKnownCrawler = _pmok_is_known_crawler_ip($_pmokBlockIp);

try {
    require_once __DIR__ . '/db.php';
    $pdo  = db();
    $stmt = $pdo->prepare('SELECT 1 FROM blocked_ips WHERE ip = ? LIMIT 1');
    $stmt->execute([$_pmokBlockIp]);
    if ($stmt->fetchColumn()) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'Forbidden';
        exit;
    }

    if (!$_pmokIsKnownCrawler) {
        $uri = strtolower($_SERVER['REQUEST_URI'] ?? '');
        foreach (PMOK_AUTOBLOCK_PATTERNS as $pattern) {
            if (strpos($uri, $pattern) !== false) {
                if (_pmok_is_verified_bingbot($_pmokBlockIp)) break;
                $pdo->prepare(
                    'INSERT INTO blocked_ips (ip, reason) VALUES (?, ?) ON DUPLICATE KEY UPDATE reason = VALUES(reason)'
                )->execute([$_pmokBlockIp, '자동 차단: 취약점 탐색 경로(' . $pattern . ')']);
                http_response_code(403);
                header('Content-Type: text/plain; charset=UTF-8');
                echo 'Forbidden';
                exit;
            }
        }
    }
} catch (Throwable $e) {
    // DB 오류 시엔 차단하지 않고 통과 (가용성 우선)
}
