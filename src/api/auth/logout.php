<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

// JWT는 stateless라 서버에서 무효화 불가 — 클라이언트가 토큰을 삭제하면 됨
echo json_encode(['message' => '로그아웃 되었습니다.']);
