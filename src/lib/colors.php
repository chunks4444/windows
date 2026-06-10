<?php
require_once __DIR__ . '/db.php';

function get_color_groups(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    try {
        $stmt = db()->query(
            'SELECT group_name, code, name, hex
             FROM color_swatches
             WHERE is_active = 1
             ORDER BY group_name, sort_order, id'
        );
        $groups = [];
        foreach ($stmt->fetchAll() as $r) {
            $label = $r['group_name'];
            if (!isset($groups[$label])) $groups[$label] = ['label' => $label, 'colors' => []];
            $groups[$label]['colors'][] = ['code' => $r['code'], 'name' => $r['name'], 'hex' => $r['hex']];
        }
        $cache = array_values($groups);
    } catch (\Throwable $e) {
        $cache = [];
    }
    return $cache;
}
