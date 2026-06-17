<?php
require_once __DIR__ . '/lib/db.php';
$pdo = db();

$pdo->exec("
CREATE TABLE IF NOT EXISTS drawing_export_logs (
    id           INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    user_id      INT UNSIGNED      NOT NULL,
    drawing_id   INT UNSIGNED      DEFAULT NULL,
    engine       VARCHAR(20)       NOT NULL DEFAULT '',
    format       ENUM('png','pdf') NOT NULL,
    drawing_name VARCHAR(100)      NOT NULL DEFAULT '',
    created_at   DATETIME          NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    KEY idx_del_user    (user_id, created_at),
    KEY idx_del_drawing (drawing_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='도면 PNG/PDF 내보내기 로그'
");

echo "완료: drawing_export_logs 테이블 생성 성공";
