<?php
$_isProduction = ($_SERVER['HTTP_HOST'] ?? '') === 'windows.pyeongmok.com';

define('DB_HOST',    $_isProduction ? '127.0.0.1'        : '211.35.72.68');
define('DB_PORT',    $_isProduction ? 3306                : 6836);
define('DB_NAME',    'windowspyeongmok');
define('DB_USER',    'webpyeongmok');
define('DB_PASS',    '@@@Chun20662782@@');
define('DB_CHARSET', 'utf8mb4');

unset($_isProduction);

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}
