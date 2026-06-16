<?php
require_once __DIR__ . '/lib/db.php';
$pdo = db();

$pdo->exec("
CREATE TABLE IF NOT EXISTS work_images (
    id         INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    work_id    INT UNSIGNED      NOT NULL,
    image_url  VARCHAR(500)      NOT NULL DEFAULT '',
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME          NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    KEY idx_wi_work (work_id, sort_order),
    CONSTRAINT fk_wi_work FOREIGN KEY (work_id) REFERENCES works(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='작품 이미지 (다중)'
");

// 기존 works.image_url → work_images 마이그레이션
$works = $pdo->query('SELECT id, image_url FROM works WHERE image_url != ""')->fetchAll();
$stmt  = $pdo->prepare('INSERT IGNORE INTO work_images (work_id, image_url, sort_order) VALUES (?,?,0)');
foreach ($works as $w) {
    $stmt->execute([$w['id'], $w['image_url']]);
}

echo "완료: work_images 테이블 생성 + 기존 이미지 이전됨";
