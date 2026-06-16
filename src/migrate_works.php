<?php
require_once __DIR__ . '/lib/db.php';
$pdo = db();

$pdo->exec("
CREATE TABLE IF NOT EXISTS works (
    id          INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    title       VARCHAR(100)      NOT NULL DEFAULT '',
    description VARCHAR(300)      NOT NULL DEFAULT '',
    image_url   VARCHAR(500)      NOT NULL DEFAULT '',
    sort_order  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active   TINYINT(1)        NOT NULL DEFAULT 1,
    created_at  DATETIME          NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    KEY idx_works_sort (sort_order, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='완성 작품 갤러리'
");

$samples = [
    ['한옥 중문 정자살', '경기도 양평 한옥', 'https://images.unsplash.com/photo-1625240036670-b2e64a3c6b2f?w=600&q=80', 0],
    ['거실 미서기창', '서울 마포구 아파트', 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=600&q=80', 1],
    ['카페 파티션', '서울 성수동 카페', 'https://images.unsplash.com/photo-1554995207-c18c203602cb?w=600&q=80', 2],
    ['서재 창호', '경기도 용인 주택', 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=600&q=80', 3],
    ['현관 중문', '서울 강남구 주택', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&q=80', 4],
    ['다실 창호', '전남 순천 한옥마을', 'https://images.unsplash.com/photo-1614107151491-6876eecbff89?w=600&q=80', 5],
    ['침실 여닫이창', '부산 해운대 주택', 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?w=600&q=80', 6],
    ['갤러리 파티션', '서울 종로구 갤러리', 'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=600&q=80', 7],
];

$stmt = $pdo->prepare('INSERT IGNORE INTO works (title, description, image_url, sort_order) VALUES (?,?,?,?)');
foreach ($samples as $s) {
    $stmt->execute($s);
}

echo "완료: works 테이블 생성 및 샘플 데이터 삽입 성공";
