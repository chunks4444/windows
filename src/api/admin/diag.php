<?php
// 임시 진단 파일 — 확인 후 삭제
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=UTF-8');

$result = ['php' => PHP_VERSION, 'steps' => []];

try {
    require_once __DIR__ . '/../../lib/cors.php';
    $result['steps'][] = 'cors OK';
} catch (Throwable $e) { $result['steps'][] = 'cors FAIL: ' . $e->getMessage(); }

try {
    require_once __DIR__ . '/../../lib/db.php';
    $result['steps'][] = 'db.php OK';
    db();
    $result['steps'][] = 'db() connect OK';
} catch (Throwable $e) { $result['steps'][] = 'db FAIL: ' . $e->getMessage(); }

try {
    require_once __DIR__ . '/../../lib/jwt.php';
    $result['steps'][] = 'jwt OK';
} catch (Throwable $e) { $result['steps'][] = 'jwt FAIL: ' . $e->getMessage(); }

try {
    $pdo = db();
    $pdo->query("SELECT 1 FROM site_config LIMIT 1");
    $result['steps'][] = 'site_config table OK';
} catch (Throwable $e) {
    // 테이블 없으면 생성
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS site_config (
            key_name   VARCHAR(80) NOT NULL,
            value      TEXT        NOT NULL DEFAULT '',
            updated_at DATETIME    NOT NULL DEFAULT NOW() ON UPDATE NOW(),
            PRIMARY KEY (key_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $result['steps'][] = 'site_config CREATED';
    } catch (Throwable $e2) {
        $result['steps'][] = 'site_config CREATE FAIL: ' . $e2->getMessage();
    }
}

echo json_encode($result);
