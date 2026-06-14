<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/drawing.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit;
}

$limit    = 20;
$page     = max(1, (int)($_GET['page'] ?? 1));
$drawings = Drawing::list_all((int)$payload['sub'], $page, $limit + 1);
$has_more = count($drawings) > $limit;
if ($has_more) array_pop($drawings);

echo json_encode(['drawings' => $drawings, 'has_more' => $has_more]);
