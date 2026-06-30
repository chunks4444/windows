<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../lib/cors.php';
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$body       = json_decode(file_get_contents('php://input'), true) ?? [];
$engine     = trim($body['engine']      ?? 'classic');
$message    = trim($body['message']     ?? '');
$params     = $body['params']           ?? [];
$sessionKey = trim($body['session_key'] ?? '');

if (!$message) { http_response_code(422); echo json_encode(['error' => 'message 필수']); exit; }

// 사용자 식별
$userId = null;
try {
    $payload = jwt_from_request();
    if ($payload) $userId = (int)($payload['sub'] ?? $payload['user_id'] ?? 0) ?: null;
} catch (Throwable $e) {}

// API 키
$apiKey = getenv('ANTHROPIC_API_KEY') ?: '';
if (!$apiKey) {
    try {
        $row = db()->query("SELECT value FROM site_config WHERE key_name='anthropic_api_key'")->fetch();
        $apiKey = $row['value'] ?? '';
    } catch (Throwable $e) {}
}
if (!$apiKey) { http_response_code(500); echo json_encode(['error' => 'Anthropic API 키가 설정되지 않았습니다.']); exit; }

// DB 튜닝값
$dbAliases   = '';
$dbExtra     = '';
$dbParamDesc = '';
try {
    $stmt = db()->query("SELECT key_name, value FROM site_config WHERE key_name IN ('ai_engine_aliases','ai_extra_instructions','ai_param_desc')");
    foreach ($stmt->fetchAll() as $r) {
        if ($r['key_name'] === 'ai_engine_aliases')     $dbAliases   = trim($r['value']);
        if ($r['key_name'] === 'ai_extra_instructions') $dbExtra     = trim($r['value']);
        if ($r['key_name'] === 'ai_param_desc')         $dbParamDesc = trim($r['value']);
    }
} catch (Throwable $e) {}

// 엔진 이름 — studio_cards 단일 소스 (메뉴·메인·가이드·AI 공통)
$engineDesc = [];
try {
    $rows = db()->query('SELECT engine_key, title FROM studio_cards')->fetchAll();
    foreach ($rows as $r) $engineDesc[$r['engine_key']] = $r['title'];
} catch (Throwable $e) {}

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
- showMuntol: 문틀 윤곽선 표시 (true/false)

엔진별 추가 파라미터:
- classic/square: vRatio — 세로 비율 (0.5–3.0, 기본 1.2)
- classic: pattern — "위/중/아래" 살 비율, 예: "3/5/3"
- square/cross/diamond/triangle/hexagon: shrinkH — 상하 압축 (true/false)
- triangle/hexagon: rotate — 패턴 90° 회전 (true/false)
EOT;

$paramDesc = $dbParamDesc ?: $paramDescDefault;

$currentJson = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
$engLabel    = $engineDesc[$engine] ?? $engine;

// 엔진 목록 문자열 빌드 (DB 타이틀 기반)
$_engineKeys = ['classic', 'square', 'cross', 'diamond', 'triangle', 'hexagon'];
$_customAliases = [];
if ($dbAliases) { try { $_customAliases = json_decode($dbAliases, true) ?: []; } catch (Throwable $e) {} }
$engineListLines = [];
foreach ($_engineKeys as $_k) {
    $_title = $engineDesc[$_k] ?? $_k;
    $_alias = trim($_customAliases[$_k] ?? '');
    $engineListLines[] = "- $_k : $_title" . ($_alias ? " — 별칭: $_alias" : '');
}
$engineListStr = implode("\n", $engineListLines);

// 추가 지시사항 (JSON 배열 → 번호 목록)
$extraStr = '';
if ($dbExtra) {
    try {
        $extraItems = json_decode($dbExtra, true);
        if (is_array($extraItems) && count($extraItems)) {
            $lines = ["\n추가 지시사항:"];
            foreach ($extraItems as $i => $item) {
                $lines[] = ($i + 1) . '. ' . $item;
            }
            $extraStr = implode("\n", $lines);
        }
    } catch (Throwable $e) {
        $extraStr = $dbExtra ? "\n추가 지시사항:\n" . $dbExtra : '';
    }
}

// 대화 히스토리 로드 (최근 5회)
$history = [];
try {
    if ($userId) {
        $hRows = db()->prepare(
            "SELECT message, reply FROM ai_chat_history
             WHERE user_id=? ORDER BY created_at DESC LIMIT 5"
        );
        $hRows->execute([$userId]);
    } elseif ($sessionKey) {
        $hRows = db()->prepare(
            "SELECT message, reply FROM ai_chat_history
             WHERE session_key=? AND user_id IS NULL ORDER BY created_at DESC LIMIT 5"
        );
        $hRows->execute([$sessionKey]);
    }
    if (isset($hRows)) {
        foreach (array_reverse($hRows->fetchAll()) as $h) {
            $history[] = ['role' => 'user',      'content' => $h['message']];
            $history[] = ['role' => 'assistant', 'content' => $h['reply'] ?? ''];
        }
    }
} catch (Throwable $e) {}

$systemPrompt = <<<EOT
당신은 평목(平木) 한국 전통 창호 설계 스튜디오의 AI 설계 도우미입니다.
사용자의 자연어 요청을 받아 창호 파라미터를 조정해 주세요.

현재 엔진: {$engine} — {$engLabel}

엔진 종류 (사용자가 다른 패턴을 요청하면 engine 필드에 명시):
{$engineListStr}

{$paramDesc}

현재 파라미터:
{$currentJson}

규칙:
1. 변경이 필요한 파라미터만 params에 포함하세요.
2. 숫자 범위를 반드시 지키세요.
3. 사용자가 현재와 다른 패턴(엔진)을 요청하거나 추천이 현재 엔진과 다르면 engine 필드에 해당 키를 반환하세요. 같으면 engine 생략.
4. reply는 한국어 2–3문장으로 추천 이유 또는 변경 내용을 설명하세요.
5. JSON만 반환하세요. 마크다운 코드블록 없이 순수 JSON.
{$extraStr}
응답 형식:
{"engine": "cross", "params": {...}, "reply": "..."}
EOT;

// 메시지 배열: 히스토리 + 현재 요청
$messages   = $history;
$messages[] = ['role' => 'user', 'content' => $message];

// Anthropic API 호출
$payload = [
    'model'      => 'claude-haiku-4-5-20251001',
    'max_tokens' => 512,
    'system'     => $systemPrompt,
    'messages'   => $messages,
];

$ch = curl_init('https://api.anthropic.com/v1/messages');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'x-api-key: ' . $apiKey,
        'anthropic-version: 2023-06-01',
    ],
]);
$raw  = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if (!$raw || $code !== 200) {
    http_response_code(502);
    echo json_encode(['error' => 'AI 응답 오류 (HTTP ' . $code . ')']);
    exit;
}

$resp    = json_decode($raw, true);
$content = $resp['content'][0]['text'] ?? '';

// JSON 추출
$content = preg_replace('/^```[a-z]*\s*/i', '', trim($content));
$content = preg_replace('/\s*```$/', '', $content);
$parsed  = json_decode(trim($content), true);

if (!$parsed || !isset($parsed['params'])) {
    http_response_code(502);
    echo json_encode(['error' => 'AI 응답을 파싱할 수 없습니다.', 'raw' => $content]);
    exit;
}

$reply     = $parsed['reply'] ?? '';
$outEngine = $parsed['engine'] ?? null;

// 히스토리 저장
try {
    db()->prepare(
        "INSERT INTO ai_chat_history (user_id, session_key, engine, message, reply, params_out)
         VALUES (?, ?, ?, ?, ?, ?)"
    )->execute([
        $userId,
        $sessionKey ?: '',
        $engine,
        $message,
        $reply,
        json_encode($parsed['params'], JSON_UNESCAPED_UNICODE),
    ]);
} catch (Throwable $e) {}

$out = ['params' => $parsed['params'], 'reply' => $reply];
if ($outEngine) $out['engine'] = $outEngine;
echo json_encode($out);
