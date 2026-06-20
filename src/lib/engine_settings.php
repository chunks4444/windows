<?php
require_once __DIR__ . '/db.php';

// 엔진별 기본값 — DB에 행이 없거나 DB 조회가 실패할 때 쓰는 fallback (기존 하드코딩 값과 동일)
function engine_setting_defaults(string $engine): array {
    $common = [
        'doorType'    => 'swing',
        'doorCount'   => '1',
        'W'           => '600',
        'H'           => '1707',
        'frame'       => '60',
        'frameH'      => '60',
        'slat'        => '12',
        'pungpanOn'   => '0',
        'pungpan'     => '0',
        'dimensionOn' => '1',
        'gap'         => '2',
        'basePadding' => '60',
    ];

    switch ($engine) {
        case 'classic':
            return $common + ['cols' => '12', 'ratio' => '1.2', 'patternTop' => '3', 'patternMid' => '5', 'patternBot' => '3'];
        case 'square':
            return $common + ['cols' => '6', 'ratio' => '1.0', 'shrinkH' => '0'];
        case 'cross':
            return $common + ['cols' => '4', 'shrinkH' => '0'];
        case 'diamond':
            return $common + ['cols' => '4', 'shrinkH' => '0'];
        case 'triangle':
            return $common + ['cols' => '4', 'shrinkH' => '0', 'rotate' => '1'];
        case 'hexagon':
            return $common + ['cols' => '3', 'shrinkH' => '0', 'rotate' => '1'];
        default:
            return $common;
    }
}

// 엔진의 현재 설정값 (DB 값으로 기본값을 덮어씀). DB 조회가 실패해도 기본값으로 동작.
function get_engine_settings(string $engine): array {
    $defaults = engine_setting_defaults($engine);
    try {
        $stmt = db()->prepare('SELECT setting_key, setting_value FROM engine_settings WHERE engine = ?');
        $stmt->execute([$engine]);
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (Throwable $e) {
        $rows = [];
    }
    return array_merge($defaults, $rows);
}

const ENGINE_SETTING_NAMES = ['classic', 'square', 'cross', 'diamond', 'triangle', 'hexagon'];
