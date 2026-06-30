<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403); echo json_encode(['error' => 'forbidden']); exit;
}

$pdo  = db();
$KEYS = ['ai_engine_aliases', 'ai_extra_instructions', 'ai_param_desc', 'ai_engine_titles'];

function cfg_get(PDO $pdo, string $key): string {
    $row = $pdo->prepare("SELECT value FROM site_config WHERE key_name=?");
    $row->execute([$key]);
    return $row->fetchColumn() ?: '';
}

function cfg_set(PDO $pdo, string $key, string $value): void {
    $pdo->prepare("INSERT INTO site_config (key_name, value) VALUES (?,?) ON DUPLICATE KEY UPDATE value=?")
        ->execute([$key, $value, $value]);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // AI용 한글 타이틀 (site_config — studio_cards와 무관)
    $savedTitles = [];
    try {
        $raw = cfg_get($pdo, 'ai_engine_titles');
        if ($raw) $savedTitles = json_decode($raw, true) ?: [];
    } catch (Throwable $e) {}

    echo json_encode([
        'ai_engine_aliases'     => cfg_get($pdo, 'ai_engine_aliases'),
        'ai_extra_instructions' => cfg_get($pdo, 'ai_extra_instructions'),
        'ai_param_desc'         => cfg_get($pdo, 'ai_param_desc'),
        'engine_titles'         => $savedTitles,
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];

    foreach ($KEYS as $k) {
        if (array_key_exists($k, $body)) {
            cfg_set($pdo, $k, trim($body[$k]));
        }
    }

    // engine_titles → ai_engine_titles 키로 저장
    if (array_key_exists('engine_titles', $body) && is_array($body['engine_titles'])) {
        cfg_set($pdo, 'ai_engine_titles', json_encode($body['engine_titles'], JSON_UNESCAPED_UNICODE));
    }

    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405); echo json_encode(['error' => 'method not allowed']);
