<?php
// 내가 올린 SVG 문양 삭제 — 엔진 '문양 라이브러리' 탭의 "내가 올린 문양" 카드 삭제 버튼이 쓴다.
// 업로드(svg_insert.php)가 uploads/svg_insert/{userId}/ 폴더에 파일만 저장하므로 삭제도 파일만 지운다.
// 본인 폴더 안의 파일만 지울 수 있다 (파일명만 받고 경로는 서버가 로그인 사용자 기준으로 만든다).
header('Content-Type: application/json; charset=UTF-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}

require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$name = (string)($body['name'] ?? '');

// 업로드 파일명 형식(업로드시각_랜덤.svg)만 허용 — ../ 등 경로 조작 차단
if (!preg_match('/^[A-Za-z0-9_-]+\.svg$/', $name)) {
    http_response_code(422); echo json_encode(['error' => '잘못된 파일명입니다.']); exit;
}

$path = __DIR__ . '/../../../uploads/svg_insert/' . (int)$payload['sub'] . '/' . $name;
if (!is_file($path)) {
    http_response_code(404); echo json_encode(['error' => '파일을 찾을 수 없습니다.']); exit;
}
if (!unlink($path)) {
    http_response_code(500); echo json_encode(['error' => '삭제에 실패했습니다 (권한 문제).']); exit;
}

echo json_encode(['ok' => true]);
