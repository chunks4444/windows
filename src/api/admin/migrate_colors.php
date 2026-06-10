<?php
// 1회성 마이그레이션: color_swatches 테이블 생성 + 초기 데이터
// 실행 후 이 파일 삭제 권장
header('Content-Type: text/plain; charset=UTF-8');

require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/db.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403); echo '권한이 없습니다.'; exit;
}

$pdo = db();

// 테이블 생성
$pdo->exec("
CREATE TABLE IF NOT EXISTS color_swatches (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    group_name  VARCHAR(50)  NOT NULL COMMENT '그룹명',
    sort_order  SMALLINT     NOT NULL DEFAULT 0,
    code        VARCHAR(20)  NOT NULL COMMENT '색상 코드',
    name        VARCHAR(50)  NOT NULL COMMENT '색상 이름',
    hex         CHAR(7)      NOT NULL COMMENT '헥스 코드',
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    KEY idx_color_group (group_name, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='엔진 공통 컬러 팔레트'
");
echo "✓ color_swatches 테이블 생성\n";

// 초기 데이터 (중복 무시)
$seeds = [
    ['스테인', 1,  '930-00', '투명',        '#dec898'],
    ['스테인', 2,  '930-01', '노랑',        '#f2aa00'],
    ['스테인', 3,  '930-02', '오렌지',      '#e05218'],
    ['스테인', 4,  '930-04', '레드브라운',  '#7a1e08'],
    ['스테인', 5,  '930-05', '황토브라운',  '#906020'],
    ['스테인', 6,  '930-06', '밤색/브라운', '#5a2e10'],
    ['스테인', 7,  '930-08', '녹색',        '#2c7030'],
    ['스테인', 8,  '930-10', '흑단',        '#222218'],
    ['스테인', 9,  '930-11', '회색',        '#888885'],
    ['천연오일', 1, 'NO-01', '자연',    '#e2c98a'],
    ['천연오일', 2, 'NO-02', '소나무',  '#c8952a'],
    ['천연오일', 3, 'NO-03', '참나무',  '#a06828'],
    ['천연오일', 4, 'NO-04', '느티나무','#8c4e22'],
    ['천연오일', 5, 'NO-05', '호두',    '#6a3518'],
    ['천연오일', 6, 'NO-06', '체리',    '#7a2e18'],
    ['천연오일', 7, 'NO-07', '황칠',    '#b8880a'],
    ['천연오일', 8, 'NO-08', '옻칠',    '#1c0c06'],
    ['천연오일', 9, 'NO-09', '먹',      '#28241e'],
];

$stmt = $pdo->prepare(
    'INSERT IGNORE INTO color_swatches (group_name, sort_order, code, name, hex) VALUES (?, ?, ?, ?, ?)'
);
$inserted = 0;
foreach ($seeds as $s) {
    $stmt->execute($s);
    $inserted += $stmt->rowCount();
}
echo "✓ 초기 데이터 {$inserted}개 삽입\n";
echo "\n완료. 이 파일을 삭제하세요: /src/api/admin/migrate_colors.php\n";
