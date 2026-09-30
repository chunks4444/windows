<?php
require_once __DIR__ . '/db.php';

function get_color_groups(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    try {
        $stmt = db()->query(
            'SELECT group_name, brand, code, name, hex
             FROM color_swatches
             WHERE is_active = 1
             ORDER BY group_name, sort_order, id'
        );
        $tr = function_exists('term') ? 'term' : fn($s) => $s;
        $groups = [];
        foreach ($stmt->fetchAll() as $r) {
            $label = $r['group_name'];
            // 그룹명·색 이름은 한글 원문이 DB에 있으므로 영문 페이지에선 term()으로 치환 (i18n.php 로드된 페이지만)
            if (!isset($groups[$label])) $groups[$label] = ['label' => $tr($label), 'colors' => []];
            $groups[$label]['colors'][] = ['brand' => $r['brand'], 'code' => $r['code'], 'name' => $tr($r['name']), 'hex' => $r['hex']];
        }
        $cache = array_values($groups);
    } catch (\Throwable $e) {
        $cache = [];
    }
    return $cache;
}
