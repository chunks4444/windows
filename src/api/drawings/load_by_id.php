<?php
// 공개 도면 로드 — user_id 제한 없이 drawing_id로 마지막 버전만 반환.
// 두 경로 중 하나를 만족하면 공개: (1) 어드민이 컬렉션에 등록(library_patterns.is_active=1),
// (2) 소유자가 공유 링크를 켬(drawings.is_shared=1).
// 슈퍼 관리자는 예외 — 주문관리 "도면 보기"에서 고객 도면을 엔진으로 열어 확인해야 하므로 공개 여부와 무관하게 읽을 수 있다.
// (엔진은 이렇게 연 도면을 drawingId 없이 띄우므로, 관리자가 저장해도 고객 원본이 아니라 관리자 도면함에 새로 저장된다)
header('Content-Type: application/json; charset=UTF-8');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$id = (int)((json_decode(file_get_contents('php://input'),true)['id'] ?? 0));
if (!$id) { http_response_code(422); echo json_encode(['error' => 'id required']); exit; }

$viewer  = jwt_from_request();
$isSuper = $viewer && ($viewer['role'] ?? '') === 's';

$pdo  = db();
$stmt = $pdo->prepare(
    'SELECT d.type, v.params, v.saved_at
     FROM drawings d
     JOIN drawing_versions v ON v.drawing_id = d.id
     WHERE d.id = ?
       AND (
         ? = 1
         OR d.is_shared = 1
         OR EXISTS (SELECT 1 FROM library_patterns lp WHERE lp.drawing_id = d.id AND lp.is_active = 1)
       )
     ORDER BY v.saved_at DESC
     LIMIT 1'
);
$stmt->execute([$id, $isSuper ? 1 : 0]);
$row = $stmt->fetch();

if (!$row) { echo json_encode(['versions' => []]); exit; }

$wpStmt = $pdo->prepare(
    'SELECT filename, filepath AS url FROM wallpapers WHERE drawing_id = ? ORDER BY id ASC'
);
$wpStmt->execute([$id]);
$wallpapers = $wpStmt->fetchAll();

echo json_encode([
    'type'       => $row['type'],
    'versions'   => [[
        'savedAt' => (int)(strtotime($row['saved_at']) * 1000),
        'params'  => json_decode($row['params'], true),
    ]],
    'wallpapers' => $wallpapers,
]);
