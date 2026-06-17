<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../lib/cors.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403); echo json_encode(['error' => '권한이 없습니다.']); exit;
}

$pdo = db();

// 테이블이 없으면 빈 결과 반환
try {
    $pdo->query('SELECT 1 FROM drawing_export_logs LIMIT 1');
} catch (Exception $e) {
    echo json_encode(['logs' => [], 'summary' => []]); exit;
}

$body  = json_decode(file_get_contents('php://input'), true) ?? [];
$limit = max(1, min(200, (int)($body['limit'] ?? 100)));

$logs = $pdo->prepare("
    SELECT el.id, el.user_id, u.email, el.drawing_id, el.engine, el.format,
           el.drawing_name, el.created_at
    FROM drawing_export_logs el
    LEFT JOIN users u ON u.id = el.user_id
    ORDER BY el.created_at DESC
    LIMIT ?
");
$logs->execute([$limit]);

$summary = $pdo->query("
    SELECT engine, format, COUNT(*) AS cnt
    FROM drawing_export_logs
    GROUP BY engine, format
    ORDER BY engine, format
")->fetchAll();

echo json_encode(['logs' => $logs->fetchAll(), 'summary' => $summary]);
