<?php
header('Content-Type: application/json; charset=UTF-8');
set_exception_handler(function(Throwable $e) {
    if (!headers_sent()) http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
});
require_once __DIR__ . '/../../lib/cors.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403); echo json_encode(['error' => '권한이 없습니다.']); exit;
}

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once __DIR__ . '/../../lib/ai_render.php';
    $rows = $pdo->query("SELECT key_name, value FROM site_config WHERE key_name IN ('render_quality','openai_api_key','anthropic_api_key','ai_chat_model','render_base_prompt')")->fetchAll();
    $cfg  = [];
    foreach ($rows as $r) $cfg[$r['key_name']] = $r['value'];
    echo json_encode([
        'render_quality'     => $cfg['render_quality']     ?? 'low',
        'openai_api_key'     => $cfg['openai_api_key']     ?? '',
        'anthropic_api_key'  => $cfg['anthropic_api_key']  ?? '',
        'ai_chat_model'      => $cfg['ai_chat_model']      ?? 'claude-sonnet-4-6',
        'render_base_prompt' => $cfg['render_base_prompt'] ?? ai_default_base_prompt_template(),
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $body    = json_decode(file_get_contents('php://input'), true) ?? [];
    $stmt    = $pdo->prepare("INSERT INTO site_config (key_name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)");

    $allowed = ['low', 'medium', 'high'];
    $quality = in_array($body['render_quality'] ?? '', $allowed) ? $body['render_quality'] : 'low';
    $stmt->execute(['render_quality', $quality]);

    if (array_key_exists('openai_api_key', $body)) {
        $stmt->execute(['openai_api_key', trim($body['openai_api_key'])]);
    }

    if (array_key_exists('anthropic_api_key', $body)) {
        $stmt->execute(['anthropic_api_key', trim($body['anthropic_api_key'])]);
    }

    $allowedModels = ['claude-sonnet-4-6', 'claude-haiku-4-5-20251001'];
    if (isset($body['ai_chat_model']) && in_array($body['ai_chat_model'], $allowedModels)) {
        $stmt->execute(['ai_chat_model', $body['ai_chat_model']]);
    }

    if (array_key_exists('render_base_prompt', $body)) {
        $stmt->execute(['render_base_prompt', trim($body['render_base_prompt'])]);
    }

    echo json_encode(['ok' => true]);
    exit;
}

// Anthropic 연결 테스트
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $row    = $pdo->query("SELECT value FROM site_config WHERE key_name='anthropic_api_key'")->fetch();
    $apiKey = $row ? $row['value'] : '';
    if (!$apiKey) { echo json_encode(['error' => 'API 키가 없습니다.']); exit; }

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'x-api-key: ' . $apiKey,
            'anthropic-version: 2023-06-01',
            'content-type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model'      => 'claude-haiku-4-5-20251001',
            'max_tokens' => 16,
            'messages'   => [['role' => 'user', 'content' => 'Hi']],
        ]),
        CURLOPT_TIMEOUT => 10,
    ]);
    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    @curl_close($ch);

    $data = json_decode($raw, true);
    if ($code === 200 && isset($data['content'])) {
        echo json_encode(['ok' => true, 'message' => '연결 성공']);
    } else {
        echo json_encode(['error' => $data['error']['message'] ?? '연결 실패 (HTTP ' . $code . ')']);
    }
    exit;
}

http_response_code(405); echo json_encode(['error' => 'Method not allowed']);
