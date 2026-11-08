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
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '-';
}

function pm_record_pageview(string $page, string $ip, string $ua, string $userId, string $refHost = '-'): void {
    try {
        require_once __DIR__ . '/db.php';
        require_once __DIR__ . '/visitor_class.php';
        $pdo        = db();
        $ipHash     = substr(md5($ip), 0, 8);
        $uaHash     = $ua !== '' ? substr(md5($ua), 0, 8) : null;
        $isMobile   = (int)(bool)preg_match('/iPhone|Android|iPad|Mobile/i', $ua);
        $uid        = ($userId !== '-' && ctype_digit($userId)) ? (int)$userId : null;
        $statusCode = http_response_code() ?: 200;

        // 사람/봇 분류와 유입 경로 (사람일 때만 유입 경로를 남긴다 — 봇의 Referer는 의미가 없다)
        [$vType, $botName] = pm_classify_ua($ua);
        $refSource = $vType === VISITOR_HUMAN ? pm_ref_source($refHost) : null;

        try {
            $pdo->prepare(
                'INSERT INTO page_views (page, user_id, ip_hash, ip, ua_hash, is_mobile, status_code, visitor_type, bot_name, ref_source) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            )->execute([$page, $uid, $ipHash, $ip, $uaHash, $isMobile, $statusCode, $vType, $botName, $refSource]);
        } catch (Throwable $e) {
            // 분류 칸이 아직 없는 DB(마이그레이션 전)여도 방문 기록은 빠뜨리지 않는다
            $pdo->prepare(
                'INSERT INTO page_views (page, user_id, ip_hash, ip, ua_hash, is_mobile, status_code) VALUES (?, ?, ?, ?, ?, ?, ?)'
            )->execute([$page, $uid, $ipHash, $ip, $uaHash, $isMobile, $statusCode]);
        }

        // 로그인한 회원이면 활동 단계 기록: 스튜디오(엔진) 페이지를 처음 열었는지, 마지막 방문 시각
        if ($uid && $vType === VISITOR_HUMAN) {
            require_once __DIR__ . '/milestones.php';
            if (ms_is_studio_page($page)) ms_mark($uid, 'first_studio_at');
        }

        // 1% 확률로 1년 초과분 자동 삭제
        if (mt_rand(0, 99) === 0) {
            $pdo->exec("DELETE FROM page_views WHERE visited_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)");
        }
    } catch (Throwable $e) {
        // 통계 기록 실패는 무시
    }
}

// 방문 기록(로그 파일·접속 통계·JS 비콘)에서 뺄 방문인지.
//  - 관리자(s 슈퍼 · m 관리자) 계정으로 로그인한 방문 — 운영자가 자기 사이트를 보는 것은 방문자 통계가 아니다.
//  - '이 브라우저 제외' 쿠키(pm_nolog=1)가 있는 브라우저 — 로그아웃 상태나 다른 기기에서 보는 운영자용 (접속 통계 화면에서 켠다).
// 관리자가 회원으로 대리 로그인한 세션(imp)은 권한 판단에서 뺀다: 이 경우 JWT의 role은 그 회원의 것이고,
// 대리 로그인은 감사용으로 로그 파일에 [IMPERSONATED]로 남기는 기존 동작을 그대로 둔다.
function pm_is_excluded(?array $payload): bool {
    if (($_COOKIE['pm_nolog'] ?? '') === '1') return true;
    if (!$payload || isset($payload['imp'])) return false;
    return in_array($payload['role'] ?? '', ['s', 'm'], true);
}

function pm_cleanup_old_logs(string $logDir, int $days = 365): void {
    $cutoff = time() - $days * 86400;
    foreach (glob($logDir . DIRECTORY_SEPARATOR . 'access_*.log') ?: [] as $file) {
        if (@filemtime($file) < $cutoff) @unlink($file);
    }
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

    // 1% 확률로 30일 지난 로그 파일 자동 삭제
    if (mt_rand(0, 99) === 0) {
        pm_cleanup_old_logs($logDir);
    }
}

// ── 서버 자동 로그 (페이지 요청당 1회) ────────────────────────────
if (defined('VISITOR_LOGGED')) return;
define('VISITOR_LOGGED', true);

// API·beacon 엔드포인트는 자동 로그 제외 (client.php 에서 직접 호출)
if (strpos($_SERVER['REQUEST_URI'] ?? '', '/src/api/') === 0) return;

// 아래 변수들은 이 파일이 nav.php를 통해 각 페이지 스크립트의 전역 스코프에
// require_once 되므로, 포함하는 페이지가 이미 쓰고 있는 변수명과 충돌하지
// 않도록 반드시 pm 접두어를 붙인다 (예전에 $page를 썼다가 블로그 페이지네이션의
// $page를 덮어써 버린 적이 있음).
$pmUa      = $_SERVER['HTTP_USER_AGENT']     ?? '';
$pmIp      = pm_get_ip();
$pmOs      = pm_detect_os($pmUa);
$pmBrowser = pm_detect_browser($pmUa);
$pmPage    = $_SERVER['REQUEST_URI']         ?? '/';
$pmTime    = date('Y-m-d H:i:s');
$pmCountry = $_SERVER['HTTP_CF_IPCOUNTRY']   ?? '-';        // Cloudflare 국가코드 (무료)
$pmLang    = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '-', 0, 10);
$pmRef     = '-';
if (!empty($_SERVER['HTTP_REFERER'])) {
    $pmHost = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
    $pmRef  = $pmHost ?: substr($_SERVER['HTTP_REFERER'], 0, 40);
}
$pmHttps  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'Y' : 'N';
$pmMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// 로그인 사용자 ID (JWT — 헤더·쿠키·세션 순으로 시도)
$pmUserId = '-';
$pmIsImpersonating = false;
$pmPayload = null;
try {
    require_once __DIR__ . '/jwt.php';
    $pmPayload = jwt_from_request();
    if ($pmPayload && isset($pmPayload['sub'])) $pmUserId = (string) $pmPayload['sub'];
    if ($pmPayload && isset($pmPayload['imp'])) $pmIsImpersonating = true;
} catch (Throwable $e) {}

// 로그인한 회원(관리자 포함)의 '마지막 접속' 갱신 — 어드민 '가입자 활동'의 현재 접속 표시용. 방문 통계와는 별개이고
// 대리 로그인 세션은 제외한다. 2분에 한 번만 쓴다(ms_seen).
if ($pmPayload && empty($pmPayload['imp']) && ctype_digit((string)($pmPayload['sub'] ?? ''))) {
    require_once __DIR__ . '/milestones.php';
    ms_seen((int)$pmPayload['sub']);
}

// 운영자 본인 방문은 로그 파일에도 접속 통계에도 남기지 않는다
if (pm_is_excluded($pmPayload)) return;

$pmLine = sprintf(
    "[%s] [S] IP:%-16s Country:%-4s OS:%-14s Browser:%-22s Lang:%-10s HTTPS:%s Ref:%-30s User:%-5s Page:%s%s\n",
    $pmTime, $pmIp, $pmCountry, $pmOs, $pmBrowser, $pmLang, $pmHttps, $pmRef, $pmUserId, $pmPage,
    $pmIsImpersonating ? ' [IMPERSONATED]' : ''
);

pm_write_log($pmLine);
// 관리자가 대리 로그인 중인 세션은 회원 본인의 접속 통계(page_views)에 반영하지 않는다.
if (!$pmIsImpersonating) {
    pm_record_pageview($pmPage, $pmIp, $pmUa, $pmUserId, $pmRef);
}
