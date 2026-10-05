<?php
// 내가 올린 SVG 문양 목록 — 엔진 왼쪽 '문양 라이브러리' 탭의 "내가 올린 문양" 칸이 쓴다.
// 업로드(svg_insert.php)는 DB 없이 uploads/svg_insert/{userId}/ 폴더에 파일만 저장하므로, 목록도 그 폴더를 읽어서 만든다.
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit;
}

$userId = (int)$payload['sub'];
$dir    = __DIR__ . '/../../../uploads/svg_insert/' . $userId;
$files  = is_dir($dir) ? (glob($dir . '/*.svg') ?: []) : [];

// 파일명이 "업로드시각_랜덤.svg"라 최근 것부터 보이도록 수정시각 역순
usort($files, fn($a, $b) => filemtime($b) <=> filemtime($a));

$items = array_map(fn($f) => [
    'url'        => '/uploads/svg_insert/' . $userId . '/' . basename($f),
    'name'       => basename($f),
    'created_at' => date('c', filemtime($f)),
], $files);

echo json_encode(['items' => $items]);
