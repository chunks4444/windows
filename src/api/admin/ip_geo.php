<?php
// 접속통계 화면에서 IP 옆에 국가/도시를 붙여 보여주기 위한 조회 API.
// 방문 기록(page_views)에는 저장하지 않고, 관리자가 통계 화면을 열었을 때 그 화면에
// 보이는 IP만 조회한다. 외부 무료 서비스 ip-api.com(키 불필요, 분당 15회·1회 100개 제한,
// HTTP 전용이라 브라우저가 아닌 서버에서 대신 호출)을 쓰고, 한 번 조회한 IP는 ip_geo 테이블에
// 저장해 두고 다음부터는 외부에 묻지 않는다.
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

$body = json_decode(file_get_contents('php://input'), true) ?: [];
$ips  = array_values(array_unique(array_filter(
    (array)($body['ips'] ?? []),
    fn($ip) => is_string($ip) && filter_var($ip, FILTER_VALIDATE_IP)
)));
$ips  = array_slice($ips, 0, 100);

// 조회 결과 저장 테이블 — 처음 호출될 때 자동 생성 (schema.sql에도 같은 정의 있음)
$pdo->exec("CREATE TABLE IF NOT EXISTS ip_geo (
    ip           VARCHAR(45)  NOT NULL COMMENT '방문자 IP',
    country      VARCHAR(80)  NOT NULL DEFAULT '' COMMENT '국가 (영문, ip-api.com 기준)',
    city         VARCHAR(80)  NOT NULL DEFAULT '' COMMENT '도시 (영문, 대략적 위치 — 통신사 장비 위치일 수 있음)',
    looked_up_at DATETIME     NOT NULL DEFAULT NOW() COMMENT '조회한 시각',
    PRIMARY KEY (ip)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='접속통계 화면용 IP 위치 — 한 번 조회한 IP는 다시 외부에 묻지 않음'");

$result  = [];
$missing = [];
$public  = [];
foreach ($ips as $ip) {
    // 사설·루프백 IP(로컬 개발 등)는 조회해도 의미 없음
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        $result[$ip] = ['country' => '내부망', 'city' => ''];
    } else {
        $public[] = $ip;
    }
}

if ($public) {
    $stmt = $pdo->prepare('SELECT ip, country, city FROM ip_geo WHERE ip IN (' . implode(',', array_fill(0, count($public), '?')) . ')');
    $stmt->execute($public);
    foreach ($stmt->fetchAll() as $r) {
        $result[$r['ip']] = ['country' => $r['country'], 'city' => $r['city']];
    }
    $missing = array_values(array_filter($public, fn($ip) => !isset($result[$ip])));
}

if ($missing) {
    $ch = curl_init('http://ip-api.com/batch?fields=status,country,city,query');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($missing),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    $rows = $resp ? (json_decode($resp, true) ?: []) : [];
    $ins  = $pdo->prepare('INSERT INTO ip_geo (ip, country, city) VALUES (?, ?, ?)
                           ON DUPLICATE KEY UPDATE country = VALUES(country), city = VALUES(city), looked_up_at = NOW()');
    foreach ($rows as $r) {
        if (($r['status'] ?? '') !== 'success' || empty($r['query'])) continue;
        $geo = ['country' => (string)($r['country'] ?? ''), 'city' => (string)($r['city'] ?? '')];
        $result[$r['query']] = $geo;
        $ins->execute([$r['query'], $geo['country'], $geo['city']]);
    }
}

echo json_encode(['geo' => $result], JSON_UNESCAPED_UNICODE);
