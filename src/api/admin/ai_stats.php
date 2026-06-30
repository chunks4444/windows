<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403); echo json_encode(['error' => 'forbidden']); exit;
}

$pdo = db();

$total = $pdo->query("SELECT COUNT(*) FROM ai_chat_history")->fetchColumn();
$today = $pdo->query("SELECT COUNT(*) FROM ai_chat_history WHERE DATE(created_at)=CURDATE()")->fetchColumn();
$users = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM ai_chat_history WHERE user_id IS NOT NULL")->fetchColumn();

$engines = $pdo->query(
    "SELECT engine, COUNT(*) AS cnt FROM ai_chat_history GROUP BY engine ORDER BY cnt DESC"
)->fetchAll(PDO::FETCH_ASSOC);

$logs = $pdo->query(
    "SELECT created_at, engine, message, reply FROM ai_chat_history ORDER BY created_at DESC LIMIT 50"
)->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(compact('total', 'today', 'users', 'engines', 'logs'), JSON_UNESCAPED_UNICODE);
