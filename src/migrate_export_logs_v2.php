<?php
require_once __DIR__ . '/lib/db.php';
$pdo = db();

$pdo->exec("
    ALTER TABLE drawing_export_logs
    ADD COLUMN version VARCHAR(10) NOT NULL DEFAULT '' AFTER drawing_name
");

echo "완료: version 컬럼 추가 성공";
