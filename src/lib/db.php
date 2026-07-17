<?php
// 에러를 화면에 띄우지 않고 로그 파일에만 남긴다 (DB 자격증명·파일 경로 노출 방지)
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/php-error.log');
error_reporting(E_ALL);

// 도메인 문자열(HTTP_HOST) 비교 대신 소켓 파일 존재 여부로 운영서버 여부를 판단한다.
// w.pyeongmok.com 같은 별칭 도메인으로 접속하면 HTTP_HOST가 'studio.pyeongmok.com'과
// 정확히 일치하지 않아 운영서버인데도 원격 TCP DB로 빠지는 문제가 있었음.
$_sock = '/var/run/mysqld/mysqld.sock';

if (@is_readable($_sock)) {
    define('DB_SOCKET',  $_sock);
    define('DB_HOST',    null);
    define('DB_PORT',    null);
} else {
    // DB는 외부에 직접 노출하지 않고(MySQL bind-address 127.0.0.1), SSH 터널을 통해서만 접근한다.
    // 터널: ssh -N -p 6822 -i ~/.ssh/pyeongmok_studio -L 13306:127.0.0.1:3306 chunks@211.35.72.68
    // (ops/db_tunnel/start_tunnel.ps1로 자동 실행되도록 예약 작업 등록됨)
    define('DB_SOCKET',  null);
    define('DB_HOST',    '127.0.0.1');
    define('DB_PORT',    13306);
}

unset($_sock);

define('DB_NAME',    'windowspyeongmok');
define('DB_USER',    'webpyeongmok');
define('DB_PASS',    '@@@Chun20662782@@');
define('DB_CHARSET', 'utf8mb4');

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = DB_SOCKET
            ? 'mysql:unix_socket=' . DB_SOCKET . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET
            : 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}
