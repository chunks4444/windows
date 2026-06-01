<?php
if (defined('VISITOR_LOGGED')) return;
define('VISITOR_LOGGED', true);

function pm_detect_os(string $ua): string {
    if (preg_match('/Windows NT 10\.0/i', $ua))  return 'Windows 10/11';
    if (preg_match('/Windows NT 6\.3/i', $ua))   return 'Windows 8.1';
    if (preg_match('/Windows NT 6\.1/i', $ua))   return 'Windows 7';
    if (preg_match('/Windows/i', $ua))            return 'Windows';
    if (preg_match('/iPhone/i', $ua))             return 'iOS (iPhone)';
    if (preg_match('/iPad/i', $ua))               return 'iOS (iPad)';
    if (preg_match('/Android/i', $ua))            return 'Android';
    if (preg_match('/Mac OS X/i', $ua))           return 'macOS';
    if (preg_match('/Linux/i', $ua))              return 'Linux';
    return 'Unknown OS';
}

function pm_detect_browser(string $ua): string {
    if (preg_match('/Edg\/(\d+)/i', $ua, $m))          return 'Edge ' . $m[1];
    if (preg_match('/OPR\/(\d+)/i', $ua, $m))          return 'Opera ' . $m[1];
    if (preg_match('/SamsungBrowser\/(\d+)/i', $ua, $m)) return 'Samsung Browser ' . $m[1];
    if (preg_match('/Chrome\/(\d+)/i', $ua, $m))       return 'Chrome ' . $m[1];
    if (preg_match('/Firefox\/(\d+)/i', $ua, $m))      return 'Firefox ' . $m[1];
    if (preg_match('/Safari\/(\d+)/i', $ua, $m) && preg_match('/Version\/(\d+)/i', $ua, $v))
                                                        return 'Safari ' . $v[1];
    return 'Unknown Browser';
}

function pm_get_ip(): string {
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return 'Unknown IP';
}

$ua      = $_SERVER['HTTP_USER_AGENT'] ?? '';
$ip      = pm_get_ip();
$os      = pm_detect_os($ua);
$browser = pm_detect_browser($ua);
$page    = $_SERVER['REQUEST_URI'] ?? '/';
$time    = date('Y-m-d H:i:s');

$line = sprintf("[%s] IP: %-16s | OS: %-16s | Browser: %-20s | Page: %s\n",
    $time, $ip, $os, $browser, $page);

$logFile = __DIR__ . '/../../logs/access_' . date('Y-m-d') . '.log';
file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
