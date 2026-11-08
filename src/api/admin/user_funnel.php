<?php
// 어드민 '가입자 활동' — 가입 → 스튜디오 → 도면 저장 → 내보내기 → 렌더링 → 견적요청 단계별로 몇 명이 남는지.
// user_milestones(회원별 "처음 한 시각")를 읽는다. 관리자(s·m) 계정은 빼고 일반 회원(u)·작가(a)만 센다.
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/cors.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload) { http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit; }

$pdo  = db();
$stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
$stmt->execute([$payload['sub']]);
$me = $stmt->fetch();
if (!$me || $me['role'] !== 's') { http_response_code(403); echo json_encode(['error' => '슈퍼 권한이 필요합니다.']); exit; }

// 표가 아직 없으면(마이그레이션 전) 화면이 안내할 수 있게 알려준다
try { $pdo->query('SELECT 1 FROM user_milestones LIMIT 1'); }
catch (Throwable $e) { echo json_encode(['no_table' => true]); exit; }

$months = max(1, min(12, (int)($_GET['months'] ?? 6)));

$steps = "COUNT(*) AS signups,
          SUM(m.first_studio_at  IS NOT NULL) AS studio,
          SUM(m.first_drawing_at IS NOT NULL) AS drawing,
          SUM(m.first_export_at  IS NOT NULL) AS export,
          SUM(m.first_render_at  IS NOT NULL) AS render,
          SUM(m.first_order_at   IS NOT NULL) AS ordered";
$base  = "FROM users u LEFT JOIN user_milestones m ON m.user_id = u.id WHERE u.role IN ('u','a')";

// 전체
$total = $pdo->query("SELECT $steps $base")->fetch();

// 가입 월별(코호트)
$cohorts = $pdo->prepare("SELECT DATE_FORMAT(u.created_at, '%Y-%m') AS ym, $steps $base
                          AND u.created_at >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL ? MONTH)
                          GROUP BY ym ORDER BY ym DESC");
$cohorts->execute([$months - 1]);

// 회원 목록 (최근 가입 순)
$members = $pdo->query("SELECT u.id, u.email, u.name, u.role, u.created_at, u.withdrawn_at,
                               m.first_studio_at, m.first_drawing_at, m.first_export_at, m.first_render_at, m.first_order_at, m.last_seen_at
                        $base ORDER BY u.created_at DESC LIMIT 300")->fetchAll();

// 데이터가 시작된 시점 — 스튜디오 열람은 page_views(1년치)가 근거라 그 이전 가입자는 알 수 없다
$pvSince = $pdo->query('SELECT MIN(visited_at) FROM page_views')->fetchColumn();

echo json_encode([
    'total'   => $total,
    'cohorts' => $cohorts->fetchAll(),
    'members' => $members,
    'pvSince' => $pvSince,
], JSON_UNESCAPED_UNICODE);
