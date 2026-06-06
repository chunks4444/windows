<?php
header('Content-Type: application/json; charset=UTF-8');
set_exception_handler(function(Throwable $e) {
    if (!headers_sent()) http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
});
require_once __DIR__ . '/../lib/db.php';

$q   = trim($_GET['q'] ?? '');
$pdo = db();

$editorMap = [
    'sabunteok' => '/src/engine/Sabunteok/Sabunteok.php',
    'sambuntok' => '/src/engine/sambuntok/sambuntok.php',
    'square'    => '/src/engine/square/square.php',
];

if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $pdo->prepare(
        'SELECT p.id, p.name_ko, p.drawing_id, p.image_path,
                d.type AS engine,
                GROUP_CONCAT(k.keyword ORDER BY k.id SEPARATOR ",") AS keywords
         FROM library_patterns p
         LEFT JOIN drawings d ON d.id = p.drawing_id
         LEFT JOIN library_keywords k ON k.pattern_id = p.id
         WHERE p.is_active = 1
           AND (p.name_ko LIKE :q OR p.id IN (
               SELECT pattern_id FROM library_keywords WHERE keyword LIKE :q2
           ))
         GROUP BY p.id
         ORDER BY p.sort_order, p.id'
    );
    $stmt->execute([':q' => $like, ':q2' => $like]);
} else {
    $stmt = $pdo->query(
        'SELECT p.id, p.name_ko, p.drawing_id, p.image_path,
                d.type AS engine,
                GROUP_CONCAT(k.keyword ORDER BY k.id SEPARATOR ",") AS keywords
         FROM library_patterns p
         LEFT JOIN drawings d ON d.id = p.drawing_id
         LEFT JOIN library_keywords k ON k.pattern_id = p.id
         WHERE p.is_active = 1
         GROUP BY p.id
         ORDER BY p.sort_order, p.id'
    );
}

$rows = $stmt->fetchAll();
foreach ($rows as &$r) {
    $r['keywords']   = $r['keywords'] ? explode(',', $r['keywords']) : [];
    $engineKey       = strtolower($r['engine'] ?? '');
    $r['editor_url'] = $editorMap[$engineKey] ?? null;
}

echo json_encode(['patterns' => $rows]);
