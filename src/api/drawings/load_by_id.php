<?php
// 컬랙션 도면 공개 로드 — user_id 제한 없이 drawing_id로 마지막 버전만 반환
header('Content-Type: application/json; charset=UTF-8');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { http_response_code(422); echo json_encode(['error' => 'id required']); exit; }

$pdo  = db();
$stmt = $pdo->prepare(
    'SELECT d.type, v.params, v.saved_at
     FROM drawings d
     JOIN drawing_versions v ON v.drawing_id = d.id
     WHERE d.id = ?
     ORDER BY v.saved_at DESC
     LIMIT 1'
);
$stmt->execute([$id]);
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
