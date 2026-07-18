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

$_pmokBlockIp = _pmok_block_get_ip();
if ($_pmokBlockIp === '-') return;

try {
    require_once __DIR__ . '/db.php';
    $stmt = db()->prepare('SELECT 1 FROM blocked_ips WHERE ip = ? LIMIT 1');
    $stmt->execute([$_pmokBlockIp]);
    if ($stmt->fetchColumn()) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'Forbidden';
        exit;
    }
} catch (Throwable $e) {
    // DB 오류 시엔 차단하지 않고 통과 (가용성 우선)
}
