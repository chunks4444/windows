<?php
require_once __DIR__ . '/db.php';

define('DADA_DB_NAME', 'dada_hotel');

function dada_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = DB_SOCKET
            ? 'mysql:unix_socket=' . DB_SOCKET . ';dbname=' . DADA_DB_NAME . ';charset=' . DB_CHARSET
            : 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DADA_DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

function dada_ensure_schema(PDO $pdo): void {
    $sql = preg_replace('/^--.*$/m', '', file_get_contents(__DIR__ . '/dada_schema.sql'));
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
        $pdo->exec($stmt);
    }
    $pdo->exec("INSERT IGNORE INTO room_types (code, room_count, std_ratio) VALUES
        ('STD', 0, 1.00), ('STW', 0, 1.20), ('FTD', 0, 1.54), ('FST', 0, 1.65), ('FQD', 0, 2.10)");
}
