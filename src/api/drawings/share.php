<?php
// 도면 공유 링크 on/off 토글. 소유자 본인만 가능.
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../lib/cors.php';
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/meta.php';

$payload = jwt_from_request();
if (!$payload) { http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit; }

$body      = json_decode(file_get_contents('php://input'), true) ?? [];
$drawingId = (int)($body['id'] ?? 0);
$shared    = !empty($body['shared']) ? 1 : 0;

if (!$drawingId) { http_response_code(422); echo json_encode(['error' => 'id 필수']); exit; }

$pdo  = db();
$uid  = (int)$payload['sub'];

$stmt = $pdo->prepare('SELECT type, user_id, title, thumbnail FROM drawings WHERE id = ?');
$stmt->execute([$drawingId]);
$row = $stmt->fetch();

if (!$row) { http_response_code(404); echo json_encode(['error' => '도면을 찾을 수 없습니다.']); exit; }
if ((int)$row['user_id'] !== $uid) { http_response_code(403); echo json_encode(['error' => '본인 도면만 공유할 수 있습니다.']); exit; }

$pdo->prepare('UPDATE drawings SET is_shared = ? WHERE id = ?')->execute([$shared, $drawingId]);

$shareUrl = SITE_URL . '/src/engine/' . $row['type'] . '/' . $row['type'] . '.php?drawing_id=' . $drawingId;

// 공유를 켠 직후 편집기 화면이 카카오 공유 카드를 만들 때 쓸 미리보기 정보.
// 아직 공유 전이던 도면을 편집기로 열면 화면의 og 메타가 엔진 기본값(정자살 페이지 이미지·제목)이라, 이 값으로 화면 메타를 갱신한다.
// (공유 페이지가 직접 내보내는 메타와 같은 값: src/engine/*/*.php 의 $shareMeta)
require_once __DIR__ . '/../../lib/og_image.php';
echo json_encode([
    'ok'          => true,
    'is_shared'   => (bool)$shared,
    'share_url'   => $shareUrl,
    'title'       => ($row['title'] ?: '평목 도면') . ' - 워크그룹 평목',
    'description' => '워크그룹 평목에서 설계한 문살 도면을 확인해보세요.',
    'og_image'    => !empty($row['thumbnail']) ? drawing_og_image_url($row['thumbnail']) : '',
], JSON_UNESCAPED_UNICODE);
