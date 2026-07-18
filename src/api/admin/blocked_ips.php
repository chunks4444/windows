<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload) {
    http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit;
}

$pdo  = db();
$stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
$stmt->execute([$payload['sub']]);
$me   = $stmt->fetch();
if (!$me || $me['role'] !== 's') {
    http_response_code(403); echo json_encode(['error' => '슈퍼 권한이 필요합니다.']); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rows = $pdo->query('SELECT ip, reason, blocked_at FROM blocked_ips ORDER BY blocked_at DESC')->fetchAll();
    echo json_encode(['blocked' => $rows]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body   = json_decode(file_get_contents('php://input'), true) ?: [];
    $action = $body['action'] ?? '';
    $ip     = trim($body['ip'] ?? '');

    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        http_response_code(400); echo json_encode(['error' => '올바른 IP가 아닙니다.']); exit;
    }

    if ($action === 'block') {
        $reason = trim($body['reason'] ?? '');
        $stmt = $pdo->prepare(
            'INSERT INTO blocked_ips (ip, reason, blocked_by) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE reason = VALUES(reason)'
        );
        $stmt->execute([$ip, $reason, (int)$payload['sub']]);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'unblock') {
        $stmt = $pdo->prepare('DELETE FROM blocked_ips WHERE ip = ?');
        $stmt->execute([$ip]);
        echo json_encode(['ok' => true]);
        exit;
    }

    http_response_code(400); echo json_encode(['error' => '알 수 없는 action입니다.']); exit;
}

http_response_code(405); echo json_encode(['error' => '허용되지 않는 메서드입니다.']);
