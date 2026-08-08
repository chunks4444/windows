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
    $rows = $pdo->query('SELECT ip, note, added_at FROM allowed_ips ORDER BY added_at DESC')->fetchAll();
    echo json_encode(['allowed' => $rows]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body   = json_decode(file_get_contents('php://input'), true) ?: [];
    $action = $body['action'] ?? '';
    $ip     = trim($body['ip'] ?? '');

    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        http_response_code(400); echo json_encode(['error' => '올바른 IP가 아닙니다.']); exit;
    }

    if ($action === 'add') {
        $note = trim($body['note'] ?? '');
        $stmt = $pdo->prepare(
            'INSERT INTO allowed_ips (ip, note, added_by) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE note = VALUES(note)'
        );
        $stmt->execute([$ip, $note, (int)$payload['sub']]);
        // 화이트리스트에 넣는 건 대개 방금 오차단된 본인 IP를 구제하려는 목적이므로,
        // 기존 차단 기록이 있으면 같이 지워 즉시 접근 가능하게 한다.
        $pdo->prepare('DELETE FROM blocked_ips WHERE ip = ?')->execute([$ip]);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'remove') {
        $stmt = $pdo->prepare('DELETE FROM allowed_ips WHERE ip = ?');
        $stmt->execute([$ip]);
        echo json_encode(['ok' => true]);
        exit;
    }

    http_response_code(400); echo json_encode(['error' => '알 수 없는 action입니다.']); exit;
}

http_response_code(405); echo json_encode(['error' => '허용되지 않는 메서드입니다.']);
