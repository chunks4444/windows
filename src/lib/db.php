<?php
// 에러를 화면에 띄우지 않고 로그 파일에만 남긴다 (DB 자격증명·파일 경로 노출 방지)
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/php-error.log');
error_reporting(E_ALL);

$_host = ($_SERVER['HTTP_HOST'] ?? '');

if ($_host === 'windows.pyeongmok.com') {
    define('DB_SOCKET',  '/var/run/mysqld/mysqld.sock');
    define('DB_HOST',    null);
    define('DB_PORT',    null);
} else {
    define('DB_SOCKET',  null);
    define('DB_HOST',    '211.35.72.68');
    define('DB_PORT',    6836);
}

define('DB_NAME',    'windowspyeongmok');
define('DB_USER',    'webpyeongmok');
define('DB_PASS',    '@@@Chun20662782@@');
define('DB_CHARSET', 'utf8mb4');

unset($_host);

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
