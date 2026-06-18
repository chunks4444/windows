<?php
header('Content-Type: application/json; charset=UTF-8');
set_exception_handler(function(Throwable $e) {
    if (!headers_sent()) http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
});
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403);
    echo json_encode(['error' => '권한이 없습니다.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$pdo    = db();

// 테이블 없으면 생성
$pdo->exec("CREATE TABLE IF NOT EXISTS studio_cards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    engine_key VARCHAR(20) NOT NULL UNIQUE,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    sort_order INT DEFAULT 0,
    is_active TINYINT DEFAULT 1
)");

if ($method === 'GET') {
    $rows = $pdo->query('SELECT * FROM studio_cards ORDER BY sort_order, id')->fetchAll();
    echo json_encode(['cards' => $rows]);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? '';

if ($action === 'save') {
    $id          = (int)($body['id'] ?? 0);
    $engine_key  = trim($body['engine_key'] ?? '');
    $title       = trim($body['title'] ?? '');
    $description = trim($body['description'] ?? '');
    $is_active   = isset($body['is_active']) ? (int)$body['is_active'] : 1;

    $image_url = trim($body['image_url'] ?? '');
    if (!empty($body['image_data'])) {
        $data  = $body['image_data'];
        $data  = preg_replace('#^data:image/\w+;base64,#', '', $data);
        $bin   = base64_decode($data);
        $dir   = __DIR__ . '/../../../uploads/studio_cards';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $fname = uniqid('sc_', true) . '.jpg';
        file_put_contents($dir . '/' . $fname, $bin);
        $image_url = '/uploads/studio_cards/' . $fname;
    }

    if ($id) {
        $pdo->prepare('UPDATE studio_cards SET title=?, description=?, image_url=?, is_active=? WHERE id=?')
            ->execute([$title, $description, $image_url, $is_active, $id]);
    } else {
        $maxOrder = (int)$pdo->query('SELECT COALESCE(MAX(sort_order),0) FROM studio_cards')->fetchColumn();
        $pdo->prepare('INSERT INTO studio_cards (engine_key, title, description, image_url, sort_order, is_active) VALUES (?,?,?,?,?,?)')
            ->execute([$engine_key, $title, $description, $image_url, $maxOrder + 1, $is_active]);
        $id = $pdo->lastInsertId();
    }

    $card = $pdo->prepare('SELECT * FROM studio_cards WHERE id=?');
    $card->execute([$id]);
    echo json_encode(['ok' => true, 'card' => $card->fetch()]);
    exit;
}

if ($action === 'reorder') {
    $ids = $body['ids'] ?? [];
    $stmt = $pdo->prepare('UPDATE studio_cards SET sort_order=? WHERE id=?');
    foreach ($ids as $i => $id) $stmt->execute([$i + 1, (int)$id]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'toggle') {
    $id = (int)($body['id'] ?? 0);
    $pdo->prepare('UPDATE studio_cards SET is_active = 1 - is_active WHERE id=?')->execute([$id]);
    $row = $pdo->prepare('SELECT is_active FROM studio_cards WHERE id=?');
    $row->execute([$id]);
    echo json_encode(['ok' => true, 'is_active' => (int)$row->fetchColumn()]);
    exit;
}

if ($action === 'init_defaults') {
    $defaults = [
        ['classic',  'Classic Lattice',  '전통 창호의 기본이 되는 격자 문살 패턴.<br>균형 잡힌 비례와 절제된 구조가 특징입니다.'],
        ['square',   'Square Lattice',   '가로살과 세로살이 직각으로 교차하는 정방형 문살 패턴.<br>단아하고 절제된 아름다움을 표현합니다.'],
        ['cross',    'Cross Lattice',    '45° 대각선으로 교차하는 마름모 문살 패턴.<br>역동적인 사선의 흐름이 공간에 긴장감을 더합니다.'],
        ['triangle', 'Triangle Lattice', '세 방향의 살이 60° 각도로 교차하는 삼각형 문살 패턴.<br>역동적이고 세련된 인상을 공간에 더합니다.'],
        ['diamond',  'Diamond Lattice',  '4방향 살이 대각선을 포함해 방사형으로 교차하는 패턴.<br>화려하고 입체적인 구조감을 연출합니다.'],
        ['hexagon',  'Hexagon Lattice',  '세 방향의 살이 서로 맞물려 육각형 눈을 이루는 어금육모 패턴.<br>자연의 벌집 구조를 닮은 단정하고 견고한 전통미를 담아냅니다.'],
    ];
    $stmt = $pdo->prepare('INSERT IGNORE INTO studio_cards (engine_key, title, description, sort_order, is_active) VALUES (?,?,?,?,1)');
    foreach ($defaults as $i => [$key, $title, $desc]) {
        $stmt->execute([$key, $title, $desc, $i + 1]);
    }
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => '알 수 없는 액션']);
