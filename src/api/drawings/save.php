<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/drawing.php';
require_once __DIR__ . '/../../lib/db.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$type = $body['type'] ?? '';

if (!$type) {
    http_response_code(422); echo json_encode(['error' => 'type 필드가 필요합니다.']); exit;
}

$title = $body['title'] ?? '';
if (!$title) {
    http_response_code(422); echo json_encode(['error' => 'title 필드가 필요합니다.']); exit;
}

if (Drawing::is_locked((int) $payload['sub'], $type, $title)) {
    http_response_code(423); echo json_encode(['error' => '이 도면은 견적요청 중이라 편집할 수 없습니다.']); exit;
}

$patternCategory = isset($body['pattern_category']) ? trim($body['pattern_category']) : null;
if ($patternCategory === '') $patternCategory = null;

$drawingId = Drawing::save(
    (int) $payload['sub'],
    $type,
    $title,
    $body['created_at']   ?? null,
    $body['versions']     ?? [],
    $body['thumbnail']    ?? null,
    (int) ($body['work_time_sec'] ?? 0),
    $patternCategory
);

// 도면 저장 직후, drawing_id가 없는(NULL) 배경 이미지를 이 도면에 연결
$pdo = db();
$pdo->prepare('UPDATE wallpapers SET drawing_id = ? WHERE user_id = ? AND engine = ? AND drawing_id IS NULL')
    ->execute([$drawingId, (int)$payload['sub'], strtolower($type)]);

// 회원별 활동 단계 기록(첫 도면 저장) — 실패해도 저장 응답에 영향 없음
require_once __DIR__ . '/../../lib/milestones.php';
ms_mark((int) $payload['sub'], 'first_drawing_at');

// 저장하면 썸네일 파일이 새 이름으로 바뀌고 옛 파일은 지워진다 — 편집기 화면의 og 메타(카카오 공유 카드가 읽음)가
// 옛 썸네일을 가리키지 않도록, 방금 저장된 도면의 제목·미리보기 이미지를 함께 돌려준다 (drawing-sync.js가 화면 메타를 갱신)
require_once __DIR__ . '/../../lib/meta.php';
$mst = $pdo->prepare('SELECT title, thumbnail FROM drawings WHERE id = ?');
$mst->execute([$drawingId]);
$mrow = $mst->fetch() ?: [];
echo json_encode([
    'ok'          => true,
    'drawing_id'  => $drawingId,
    'title'       => ($mrow['title'] ?? '') !== '' ? $mrow['title'] . ' - 워크그룹 평목' : '',
    'description' => '워크그룹 평목에서 설계한 문살 도면을 확인해보세요.',
    'og_image'    => !empty($mrow['thumbnail']) ? drawing_og_image_url($mrow['thumbnail']) : '',
], JSON_UNESCAPED_UNICODE);
