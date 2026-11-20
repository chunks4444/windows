<?php
// 포트폴리오 PDF 다운로드 기록 — 누가(회원이면 회원, 아니면 IP) 언제 몇 번 열람·내려받았는지.
// api/promo/download.php 가 기록하고, 관리자 '포트폴리오 PDF' 페이지가 집계를 보여 준다.
//
// mode : landing = 안내 화면을 열었다 / dl = 파일을 내려받았다 / view = 브라우저에서 바로 열었다
// source: company = 회사 소개 페이지의 모달 버튼 / link = 그 밖(홍보메일 링크, 주소 직접 입력 등)
// 회원이면 user_id를 남기고, 비회원은 IP로 구분한다. 봇(미리보기·검색엔진 등)도 같이 기록하되 visitor_type으로 갈라 둔다 —
// 메일 앱·메신저가 링크를 미리 열어 보는 일이 많아 집계에서는 기본으로 뺀다.
// 개인정보를 오래 쥐지 않도록 IP 칸은 1년이 지나면 비운다(행과 횟수는 남는다).

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/visitor_class.php';

function portfolio_dl_ensure_table(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS portfolio_downloads (
        id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        mode         VARCHAR(8)      NOT NULL COMMENT 'landing|dl|view',
        source       VARCHAR(10)     NOT NULL DEFAULT 'link' COMMENT 'company|link',
        user_id      INT UNSIGNED    NULL COMMENT '로그인한 회원(없으면 NULL → IP로 구분)',
        ip           VARCHAR(45)     NULL,
        ip_hash      CHAR(8)         NULL,
        visitor_type VARCHAR(16)     NULL COMMENT 'human 또는 각 봇 종류',
        user_agent   VARCHAR(255)    NULL,
        referer      VARCHAR(255)    NULL,
        file         VARCHAR(40)     NOT NULL,
        PRIMARY KEY (id),
        KEY idx_created (created_at),
        KEY idx_user (user_id),
        KEY idx_ip (ip)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function portfolio_dl_record(string $mode, string $source, string $file): void {
    try {
        require_once __DIR__ . '/jwt.php';
        $pdo = db();
        $ua  = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
        $ip  = portfolio_dl_ip();
        $p   = jwt_from_request();
        $uid = ($p && isset($p['sub']) && ctype_digit((string)$p['sub'])) ? (int)$p['sub'] : null;
        [$vType] = pm_classify_ua($ua);
        $row = [
            $mode, $source === 'company' ? 'company' : 'link', $uid,
            $ip === '-' ? null : $ip, $ip === '-' ? null : substr(md5($ip), 0, 8), $vType,
            $ua === '' ? null : substr($ua, 0, 255),
            ($r = (string)($_SERVER['HTTP_REFERER'] ?? '')) === '' ? null : substr($r, 0, 255),
            $file,
        ];
        $sql = 'INSERT INTO portfolio_downloads (mode, source, user_id, ip, ip_hash, visitor_type, user_agent, referer, file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
        try {
            $pdo->prepare($sql)->execute($row);
        } catch (Throwable $e) {
            portfolio_dl_ensure_table($pdo);
            $pdo->prepare($sql)->execute($row);
        }
        if (mt_rand(1, 100) === 1) {
            $pdo->exec("UPDATE portfolio_downloads SET ip = NULL, user_agent = NULL WHERE ip IS NOT NULL AND created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR) LIMIT 5000");
        }
    } catch (Throwable $e) {
        // 기록 실패가 다운로드를 막으면 안 된다
    }
}

/**
 * 관리자 화면용 집계. $all=false(기본)이면 봇과 관리자(s·m) 계정의 열람은 뺀다.
 * 반환: ['totals' => [...], 'people' => [사람별], 'recent' => [최근 기록]]
 */
function portfolio_dl_report(PDO $pdo, bool $all): array {
    try {
        portfolio_dl_ensure_table($pdo);
    } catch (Throwable $e) {
        return ['totals' => ['dl' => 0, 'view' => 0, 'landing' => 0, 'people' => 0, 'members' => 0, 'guests' => 0], 'people' => [], 'recent' => []];
    }
    $where = $all ? '1' : "(d.visitor_type = 'human' OR d.visitor_type IS NULL) AND (u.role IS NULL OR u.role NOT IN ('s','m'))";
    $join  = 'FROM portfolio_downloads d LEFT JOIN users u ON u.id = d.user_id';
    $key   = "COALESCE(CAST(d.user_id AS CHAR), CONCAT('ip:', COALESCE(d.ip, d.ip_hash, '-')))";

    $people = $pdo->query("SELECT MAX(d.user_id) AS user_id, MAX(u.name) AS name, MAX(u.email) AS email, MAX(u.role) AS role,
                                  MAX(d.ip) AS ip, MAX(d.ip_hash) AS ip_hash,
                                  SUM(d.mode = 'dl') AS dl, SUM(d.mode = 'view') AS view_cnt, SUM(d.mode = 'landing') AS landing,
                                  MIN(d.created_at) AS first_at, MAX(d.created_at) AS last_at,
                                  SUM(d.source = 'company') AS from_company,
                                  SUM(d.visitor_type IS NOT NULL AND d.visitor_type <> 'human') AS bot_hits
                           $join WHERE $where GROUP BY $key ORDER BY last_at DESC LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);

    $t = $pdo->query("SELECT SUM(d.mode = 'dl') AS dl, SUM(d.mode = 'view') AS view_cnt, SUM(d.mode = 'landing') AS landing,
                             COUNT(DISTINCT $key) AS people, COUNT(DISTINCT d.user_id) AS members
                      $join WHERE $where")->fetch(PDO::FETCH_ASSOC) ?: [];
    $members = (int)($t['members'] ?? 0);
    $totals = [
        'dl' => (int)($t['dl'] ?? 0), 'view' => (int)($t['view_cnt'] ?? 0), 'landing' => (int)($t['landing'] ?? 0),
        'people' => (int)($t['people'] ?? 0), 'members' => $members, 'guests' => max(0, (int)($t['people'] ?? 0) - $members),
    ];

    $recent = $pdo->query("SELECT d.created_at, d.mode, d.source, d.user_id, u.name, u.email, d.ip, d.ip_hash, d.visitor_type
                           $join WHERE $where ORDER BY d.id DESC LIMIT 60")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($people as &$p) { $p['dl'] = (int)$p['dl']; $p['view_cnt'] = (int)$p['view_cnt']; $p['landing'] = (int)$p['landing']; $p['from_company'] = (int)$p['from_company']; $p['bot_hits'] = (int)$p['bot_hits']; }
    unset($p);
    return ['totals' => $totals, 'people' => $people, 'recent' => $recent];
}

function portfolio_dl_ip(): string {
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) {
            $ip = trim(explode(',', $_SERVER[$k])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '-';
}
