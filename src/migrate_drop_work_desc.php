<?php
require_once __DIR__ . '/lib/db.php';
$pdo = db();

$cols = $pdo->query("SHOW COLUMNS FROM works LIKE 'description'")->fetchAll();
if ($cols) {
    $pdo->exec("ALTER TABLE works DROP COLUMN description");
    echo "완료: description 컬럼 삭제됨\n";
} else {
    echo "이미 삭제되어 있습니다.\n";
}
