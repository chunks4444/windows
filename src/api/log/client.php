<?php
// 클라이언트 비콘 수신 (navigator.sendBeacon)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(204); exit; }

require_once __DIR__ . '/../../lib/logger.php'; // pm_get_ip, pm_write_log 사용

$raw = file_get_contents('php://input');
if (!$raw) { http_response_code(204); exit; }

$d = json_decode($raw, true);
if (!is_array($d)) { http_response_code(204); exit; }

$ip   = pm_get_ip();
$time = date('Y-m-d H:i:s');

// 입력값 sanitize
$screen   = preg_replace('/[^0-9x]/',          '', (string)($d['screen']   ?? '')) ?: '-';
$viewport = preg_replace('/[^0-9x]/',          '', (string)($d['viewport'] ?? '')) ?: '-';
$tz       = preg_replace('/[^A-Za-z\/\-_]/',   '', (string)($d['tz']       ?? '')) ?: '-';
$conn     = preg_replace('/[^A-Za-z0-9\-]/',   '', (string)($d['conn']     ?? '')) ?: '-';
$touch    = empty($d['touch']) ? 'N' : 'Y';
$dark     = empty($d['dark'])  ? 'N' : 'Y';
$dpr      = number_format(min(max((float)($d['dpr'] ?? 1), 0.5), 5.0), 1);
$mem      = isset($d['mem'])  ? (int)$d['mem']  . 'GB' : '-';
$cpu      = isset($d['cpu'])  ? (int)$d['cpu']  . '코어' : '-';
$page     = substr(preg_replace('/[^A-Za-z0-9\-_\/\.\?=&%#]/', '', (string)($d['page'] ?? '')), 0, 120) ?: '-';

$line = sprintf(
    "[%s] [C] IP:%-16s Screen:%-12s Viewport:%-12s TZ:%-25s Touch:%s Dark:%s Conn:%-6s DPR:%-4s Mem:%-6s CPU:%-8s Page:%s\n",
    $time, $ip, $screen, $viewport, $tz, $touch, $dark, $conn, $dpr, $mem, $cpu, $page
);

pm_write_log($line);
http_response_code(204);
