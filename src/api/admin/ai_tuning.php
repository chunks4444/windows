<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403); echo json_encode(['error' => 'forbidden']); exit;
}

$pdo  = db();
$KEYS = ['ai_engine_aliases', 'ai_extra_instructions', 'ai_param_desc', 'home_ai_sample_prompts'];

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

$paramDescDefault = <<<'EOT'
공통 파라미터 (모든 엔진):
- W: 문 너비 (mm, 300–2000)
- H: 문 높이 (mm, 500–3000)
- cols: 세로살 개수 (2–20)
- frame: 울거미 가로폭 (mm, 20–200)
- frameH: 울거미 세로폭 (mm, 20–200)
- slat: 살 두께 (mm, 5–30)
- doorType: "swing"(여닫이) | "slide"(미서기)
- doorCount: 1–4 (짝수)
- pungpanOn: 풍판 사용 여부 (true/false)
- pungpan: 풍판 높이 (mm) — pungpanOn=true일 때만 유효
- wood: 목재 종류 (예: "소나무", "느티나무", "참나무")
- finish: 마감 (예: "오일", "옻칠", "무도장")
- frameColor: 울거미 색상 (hex, 예: "#28241e", "#8B4513")
- slatColor: 살 색상 (hex, 예: "#28241e", "#c8a96e")
- showMuntol: 문틀 윤곽선 표시 (true/false)

엔진별 추가 파라미터:
- classic/square: vRatio — 세로 비율 (0.5–3.0, 기본 1.2)
- classic: pattern — "위/중/아래" 살 비율, 예: "3/5/3"
- square/cross/diamond/triangle/hexagon: shrinkH — 상하 압축 (true/false)
- triangle/hexagon: rotate — 패턴 90° 회전 (true/false)
EOT;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // studio_cards 타이틀 (메뉴·메인·가이드·AI 공통 소스)
    $engineTitles = [];
    try {
        $rows = $pdo->query('SELECT engine_key, title FROM studio_cards')->fetchAll();
        foreach ($rows as $r) $engineTitles[$r['engine_key']] = $r['title'];
    } catch (Throwable $e) {}

    $customParamDesc = cfg_get($pdo, 'ai_param_desc');
    echo json_encode([
        'ai_engine_aliases'     => cfg_get($pdo, 'ai_engine_aliases'),
        'ai_extra_instructions' => cfg_get($pdo, 'ai_extra_instructions'),
        'ai_param_desc'         => $customParamDesc,
        'ai_param_desc_default' => $paramDescDefault,
        'engine_titles'         => $engineTitles,
        'home_ai_sample_prompts' => cfg_get($pdo, 'home_ai_sample_prompts'),
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
