<?php
/**
 * 1회성 마이그레이션: cost_table에 engine/thickness_mm/width_mm 컬럼 추가 + 초기 grid 데이터 입력
 * 실행: 브라우저에서 /src/admin/migrate_cost_table_dims.php 접근 (슈퍼 권한 필요)
 * 완료 후 이 파일 삭제할 것.
 */
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/jwt.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403);
    echo '권한이 없습니다.';
    exit;
}

$pdo = db();
$log = [];

function run(PDO $pdo, string $sql, string $label, array &$log): void {
    try {
        $pdo->exec($sql);
        $log[] = "✅ $label";
    } catch (PDOException $e) {
        $log[] = "⚠️  $label — " . $e->getMessage();
    }
}

// 1. 컬럼 추가
run($pdo, "ALTER TABLE cost_table ADD COLUMN engine       VARCHAR(20)       NULL                COMMENT '엔진명' AFTER category",        'engine 컬럼 추가',       $log);
run($pdo, "ALTER TABLE cost_table ADD COLUMN thickness_mm SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '부재 두께 mm (문틀 전용)' AFTER weight", 'thickness_mm 컬럼 추가', $log);
run($pdo, "ALTER TABLE cost_table ADD COLUMN width_mm     SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '부재 폭 mm' AFTER thickness_mm", 'width_mm 컬럼 추가',     $log);

// 2. 인덱스
run($pdo, "ALTER TABLE cost_table ADD KEY idx_ct_engine (engine)", 'engine 인덱스 추가', $log);

// 3. 엔진별 grid 초기 데이터
//    울거미 폭 33mm / 살 폭 33mm / 문틀 두께 30mm·폭 33mm
$engines = ['classic', 'square', 'cross', 'diamond', 'triangle', 'hexagon'];
$parts = [
    // [name,         thickness_mm, width_mm]
    ['울거미', 0,  33],
    ['살',     0,  33],
    ['문틀',   30, 33],
];

$maxOrder = (int)$pdo->query('SELECT COALESCE(MAX(sort_order),0) FROM cost_table')->fetchColumn();
$stmt = $pdo->prepare(
    "INSERT IGNORE INTO cost_table (category, engine, name, unit_price, unit, unit_name, weight, thickness_mm, width_mm, sort_order, is_active)
     VALUES ('grid', ?, ?, 0, 'mm', '', 1.00, ?, ?, ?, 1)"
);

foreach ($engines as $eng) {
    foreach ($parts as [$name, $thick, $width]) {
        $maxOrder++;
        try {
            $stmt->execute([$eng, $name, $thick, $width, $maxOrder]);
            $log[] = "✅ grid/{$eng}/{$name} 삽입";
        } catch (PDOException $e) {
            $log[] = "⚠️  grid/{$eng}/{$name} — " . $e->getMessage();
        }
    }
}

header('Content-Type: text/plain; charset=UTF-8');
echo implode("\n", $log) . "\n\n완료. 이 파일을 삭제하세요: src/admin/migrate_cost_table_dims.php\n";
