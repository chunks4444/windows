<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../lib/db.php';

$rows = db()->query(
    'SELECT slug, name_ko FROM library_categories WHERE is_active=1 ORDER BY sort_order, id'
)->fetchAll();

echo json_encode(['categories' => $rows]);
