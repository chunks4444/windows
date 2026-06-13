<?php
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../../../lib/jwt.php';
if (!jwt_from_request()) { http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit; }

$jobId  = preg_replace('/[^a-f0-9]/', '', $_GET['job'] ?? '');
if (!$jobId) { echo json_encode(['error' => '잘못된 요청']); exit; }

$jobDir     = sys_get_temp_dir() . '/pmok_render';
$resultFile = "{$jobDir}/{$jobId}.result.json";
$inputFile  = "{$jobDir}/{$jobId}.input.json";

if (file_exists($resultFile)) {
    $data = file_get_contents($resultFile);
    unlink($resultFile);
    echo $data;
} elseif (file_exists($inputFile)) {
    echo json_encode(['status' => 'processing']);
} else {
    echo json_encode(['error' => '작업을 찾을 수 없습니다.']);
}
