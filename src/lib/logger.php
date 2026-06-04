<?php
// ── 공용 유틸 함수 (guard 전에 정의) ────────────────────────────────
function pm_detect_os(string $ua): string {
    if (preg_match('/Windows NT 10\.0/i', $ua)) return 'Windows 10/11';
    if (preg_match('/Windows NT 6\.3/i',  $ua)) return 'Windows 8.1';
    if (preg_match('/Windows NT 6\.1/i',  $ua)) return 'Windows 7';
    if (preg_match('/Windows/i',           $ua)) return 'Windows';
    if (preg_match('/iPhone/i',            $ua)) return 'iOS (iPhone)';
    if (preg_match('/iPad/i',              $ua)) return 'iOS (iPad)';
    if (preg_match('/Android/i',           $ua)) return 'Android';
    if (preg_match('/Mac OS X/i',          $ua)) return 'macOS';
    if (preg_match('/Linux/i',             $ua)) return 'Linux';
    return 'Unknown';
}

function pm_detect_browser(string $ua): string {
    if (preg_match('/Edg\/(\d+)/i',          $ua, $m)) return 'Edge '            . $m[1];
    if (preg_match('/OPR\/(\d+)/i',          $ua, $m)) return 'Opera '           . $m[1];
    if (preg_match('/SamsungBrowser\/(\d+)/i',$ua, $m)) return 'Samsung '        . $m[1];
    if (preg_match('/Chrome\/(\d+)/i',       $ua, $m)) return 'Chrome '          . $m[1];
    if (preg_match('/Firefox\/(\d+)/i',      $ua, $m)) return 'Firefox '         . $m[1];
    if (preg_match('/Safari\/(\d+)/i',       $ua)
     && preg_match('/Version\/(\d+)/i',      $ua, $v)) return 'Safari '          . $v[1];
    return 'Unknown';
}

function pm_get_ip(): string {
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '-';
}

function pm_write_log(string $line): void {
    static $logDir = null;
    if ($logDir === null) {
        $logDir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
            @chmod($logDir, 0755);
        }
    }
    if (!is_writable($logDir)) {
        error_log('[PMOK] ' . rtrim($line));
        return;
    }
    $file = $logDir . DIRECTORY_SEPARATOR . 'access_' . date('Y-m-d') . '.log';
    if (@file_put_contents($file, $line, FILE_APPEND | LOCK_EX) === false) {
        error_log('[PMOK] write_fail | ' . rtrim($line));
    }
}

// ── 서버 자동 로그 (페이지 요청당 1회) ────────────────────────────
if (defined('VISITOR_LOGGED')) return;
define('VISITOR_LOGGED', true);

// API·beacon 엔드포인트는 자동 로그 제외 (client.php 에서 직접 호출)
if (strpos($_SERVER['REQUEST_URI'] ?? '', '/src/api/') === 0) return;

$ua      = $_SERVER['HTTP_USER_AGENT']     ?? '';
$ip      = pm_get_ip();
$os      = pm_detect_os($ua);
$browser = pm_detect_browser($ua);
$page    = $_SERVER['REQUEST_URI']         ?? '/';
$time    = date('Y-m-d H:i:s');
$country = $_SERVER['HTTP_CF_IPCOUNTRY']   ?? '-';        // Cloudflare 국가코드 (무료)
$lang    = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '-', 0, 10);
$ref     = '-';
if (!empty($_SERVER['HTTP_REFERER'])) {
    $host = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
    $ref  = $host ?: substr($_SERVER['HTTP_REFERER'], 0, 40);
}
$https  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'Y' : 'N';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// 로그인 사용자 ID (JWT, 실패해도 무시)
$userId = '-';
try {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
    if (preg_match('/Bearer\s+(.+)/i', $authHeader, $tok) && function_exists('jwt_from_request')) {
        $payload = jwt_from_request();
        if ($payload && isset($payload['sub'])) $userId = (string) $payload['sub'];
    }
} catch (Throwable $e) {}

$line = sprintf(
    "[%s] [S] IP:%-16s Country:%-4s OS:%-14s Browser:%-22s Lang:%-10s HTTPS:%s Ref:%-30s User:%-5s Page:%s\n",
    $time, $ip, $country, $os, $browser, $lang, $https, $ref, $userId, $page
);

pm_write_log($line);
