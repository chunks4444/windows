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

echo json_encode($result);
