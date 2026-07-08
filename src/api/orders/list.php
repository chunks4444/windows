<?php
header('Content-Type: application/json; charset=UTF-8');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/db.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit;
}
$uid = (int) $payload['sub'];

$stmt = db()->prepare(
    'SELECT id, drawing_id, engine, title, version_label, thumbnail, due_date, status,
            revision_note, tracking_carrier, tracking_number,
            created_at, reviewed_at, shipped_at, delivered_at
     FROM orders
     WHERE user_id = ?
     ORDER BY created_at DESC'
);
$stmt->execute([$uid]);

echo json_encode(['orders' => $stmt->fetchAll()]);
