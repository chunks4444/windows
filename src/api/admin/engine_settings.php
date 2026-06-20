<?php
header('Content-Type: application/json; charset=UTF-8');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/engine_settings.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403); echo json_encode(['error' => '권한이 없습니다.']); exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$pdo    = db();

if ($method === 'GET') {
    $engine = $_GET['engine'] ?? '';
    if (!in_array($engine, ENGINE_SETTING_NAMES, true)) {
        http_response_code(400); echo json_encode(['error' => '알 수 없는 엔진입니다.']); exit;
    }
    echo json_encode(['settings' => get_engine_settings($engine)]);
    exit;
}

if ($method === 'POST') {
    $body     = json_decode(file_get_contents('php://input'), true) ?? [];
    $engine   = $body['engine'] ?? '';
    $settings = $body['settings'] ?? [];
    if (!in_array($engine, ENGINE_SETTING_NAMES, true) || !is_array($settings)) {
        http_response_code(400); echo json_encode(['error' => '잘못된 요청입니다.']); exit;
    }

    $pdo->beginTransaction();
    try {
        $pdo->prepare('DELETE FROM engine_settings WHERE engine = ?')->execute([$engine]);
        $stmt = $pdo->prepare('INSERT INTO engine_settings (engine, setting_key, setting_value) VALUES (?, ?, ?)');
        foreach ($settings as $key => $value) {
            $key = trim((string)$key);
            if ($key === '') continue;
            $stmt->execute([$engine, substr($key, 0, 40), substr((string)$value, 0, 255)]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        http_response_code(500); echo json_encode(['error' => '저장에 실패했습니다.']); exit;
    }

    echo json_encode(['ok' => true, 'settings' => get_engine_settings($engine)]);
    exit;
}

http_response_code(405);
