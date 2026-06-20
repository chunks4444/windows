<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../lib/db.php';

try {
    $rows = db()->query('SELECT id, name, svg_url, category FROM svg_motifs WHERE is_active=1 ORDER BY sort_order, id')->fetchAll();
} catch (Exception $e) {
    $rows = [];
}
echo json_encode(['motifs' => $rows]);
