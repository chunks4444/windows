<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../lib/cors.php';
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../../lib/engine_settings.php';
require_once __DIR__ . '/../../lib/i18n.php';

$categories = array_map(function (array $c): array {
    $c['name'] = term($c['name']);
    return $c;
}, get_pattern_categories());

echo json_encode(['categories' => $categories], JSON_UNESCAPED_UNICODE);
