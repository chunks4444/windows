<?php
// 클라이언트 비콘 수신 (navigator.sendBeacon)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(204); exit; }

require_once __DIR__ . '/../../lib/logger.php'; // pm_get_ip, pm_write_log 사용

$raw = file_get_contents('php://input');
if (!$raw) { http_response_code(204); exit; }

$d = json_decode($raw, true);
if (!is_array($d)) { http_response_code(204); exit; }

// 운영자 본인 방문(관리자 로그인 또는 '이 브라우저 제외' 쿠키)은 비콘도 기록하지 않는다
$jp = null;
try { require_once __DIR__ . '/../../lib/jwt.php'; $jp = jwt_from_request(); } catch (Throwable $e) {}
if (pm_is_excluded($jp)) { http_response_code(204); exit; }

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

// 이 비콘은 브라우저가 자바스크립트를 실제로 실행했을 때만 온다 — 같은 방문자(IP+UA)의 가장 최근 방문 기록에
// "JS 실행 확인" 표시를 한다. UA를 흉내 낸 크롤러는 보통 JS를 실행하지 않으므로 사람 수를 더 정확히 가늠하는 기준이 된다.
try {
    require_once __DIR__ . '/../../lib/db.php';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if ($ua !== '' && $ip !== '-') {
        db()->prepare(
            'UPDATE page_views SET js_ok = 1 WHERE ip_hash = ? AND ua_hash = ? AND js_ok = 0 AND visited_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE) ORDER BY id DESC LIMIT 1'
        )->execute([substr(md5($ip), 0, 8), substr(md5($ua), 0, 8)]);
    }
} catch (Throwable $e) {
    // 분류 칸이 없거나 DB 오류여도 비콘 응답은 그대로
}
http_response_code(204);
