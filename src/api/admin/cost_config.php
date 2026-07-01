<?php
// cost_config.php — get_cost_config() 기반 읽기 전용 API
// 쓰기는 cost_table API를 통해 관리
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/engine_settings.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403); echo json_encode(['error' => '권한이 없습니다.']); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $engine = $_GET['engine'] ?? '*';
    echo json_encode(['config' => get_cost_config($engine)]);
    exit;
}

http_response_code(405);
