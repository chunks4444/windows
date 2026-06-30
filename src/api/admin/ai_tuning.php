<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403); echo json_encode(['error' => 'forbidden']); exit;
}

$pdo  = db();
$KEYS = ['ai_engine_aliases', 'ai_extra_instructions', 'ai_param_desc'];

// ai_engine_titles는 studio_cards로 통합됨 — 잔여 행 정리
try { $pdo->exec("DELETE FROM site_config WHERE key_name='ai_engine_titles'"); } catch (Throwable $e) {}

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
    // studio_cards 타이틀 (메뉴·메인·가이드·AI 공통 소스)
    $engineTitles = [];
    try {
        $rows = $pdo->query('SELECT engine_key, title FROM studio_cards')->fetchAll();
        foreach ($rows as $r) $engineTitles[$r['engine_key']] = $r['title'];
    } catch (Throwable $e) {}

    echo json_encode([
        'ai_engine_aliases'     => cfg_get($pdo, 'ai_engine_aliases'),
        'ai_extra_instructions' => cfg_get($pdo, 'ai_extra_instructions'),
        'ai_param_desc'         => cfg_get($pdo, 'ai_param_desc'),
        'engine_titles'         => $engineTitles,
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

    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405); echo json_encode(['error' => 'method not allowed']);
