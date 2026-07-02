<?php
// 에러를 화면에 띄우지 않고 로그 파일에만 남긴다 (DB 자격증명·파일 경로 노출 방지)
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/php-error.log');
error_reporting(E_ALL);

// 도메인 문자열(HTTP_HOST) 비교 대신 소켓 파일 존재 여부로 운영서버 여부를 판단한다.
// w.pyeongmok.com 같은 별칭 도메인으로 접속하면 HTTP_HOST가 'windows.pyeongmok.com'과
// 정확히 일치하지 않아 운영서버인데도 원격 TCP DB로 빠지는 문제가 있었음.
$_sock = '/var/run/mysqld/mysqld.sock';

if (@is_readable($_sock)) {
    define('DB_SOCKET',  $_sock);
    define('DB_HOST',    null);
    define('DB_PORT',    null);
} else {
    define('DB_SOCKET',  null);
    define('DB_HOST',    '211.35.72.68');
    define('DB_PORT',    6836);
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
