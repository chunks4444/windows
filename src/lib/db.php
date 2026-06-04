<?php
$_host = ($_SERVER['HTTP_HOST'] ?? '');

if ($_host === 'windows.pyeongmok.com') {
    // 프로덕션: 같은 서버이므로 소켓(localhost) + 기본 포트 사용
    define('DB_HOST',    'localhost');
    define('DB_PORT',    3306);
} else {
    // 로컬 개발: 외부 IP + 커스텀 포트
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
        // localhost 는 Unix 소켓 연결 (포트 무시), 외부 IP 는 TCP 연결
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT
             . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}
