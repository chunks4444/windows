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
        'frameThick'  => '30',
        'frameGap'    => '2',
        'muntolFace'  => '30',   // 문틀 정면 보이는 폭 (mm) — 부재 표시·도면 기준
        'muntolT'     => '38',   // 문틀 두께 (mm) — 실제 목재 두께 (정면 30mm + 턱 8mm)
        'muntolW'     => '80',   // 문틀 폭 (mm) — 벽 안으로 들어가는 설치 깊이
        'slat'        => '12',
        'ulgeomiW'    => '33',   // 울거미 폭 (mm) — 원가 계산용
        'slatW'       => '20',   // 살 폭 (mm) — 원가 계산용
        'pungpanOn'   => '0',
        'pungpan'     => '0',
        'pungpanT'    => '15',   // 풍판 판재 두께 (mm) — 원가 계산용
        'dimensionOn' => '1',
        'gap'         => '2',
        'basePadding' => '60',
    ];

    switch ($engine) {
        case 'classic':
            return $common + ['cols' => '12', 'ratio' => '1.2', 'patternTop' => '3', 'patternMid' => '5', 'patternBot' => '3', 'min_days' => '3'];
        case 'square':
            return $common + ['cols' => '6', 'ratio' => '1.0', 'shrinkH' => '0', 'min_days' => '3'];
        case 'cross':
            return $common + ['cols' => '4', 'shrinkH' => '0', 'min_days' => '4'];
        case 'diamond':
            return $common + ['cols' => '4', 'shrinkH' => '0', 'min_days' => '4'];
        case 'triangle':
            return $common + ['cols' => '4', 'shrinkH' => '0', 'rotate' => '1', 'min_days' => '4'];
        case 'hexagon':
            return $common + ['cols' => '3', 'shrinkH' => '0', 'rotate' => '1', 'min_days' => '5'];
        default:
            return $common + ['min_days' => '3'];
    }
}

// 엔진의 현재 설정값. DB에 없는 키는 PHP 기본값으로 자동 INSERT → 이후 어드민 UI에서 관리 가능.
function get_engine_settings(string $engine): array {
    $defaults = engine_setting_defaults($engine);
    try {
        $pdo  = db();
        $stmt = $pdo->prepare('SELECT setting_key, setting_value FROM engine_settings WHERE engine = ?');
        $stmt->execute([$engine]);
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $missing = array_diff_key($defaults, $rows);
        if ($missing) {
            $ins = $pdo->prepare('INSERT IGNORE INTO engine_settings (engine, setting_key, setting_value) VALUES (?, ?, ?)');
            foreach ($missing as $k => $v) {
                $ins->execute([$engine, $k, $v]);
            }
        }
    } catch (Throwable $e) {
        $rows = [];
    }
    return array_merge($defaults, $rows);
}

const ENGINE_SETTING_NAMES = ['classic', 'square', 'cross', 'diamond', 'triangle', 'hexagon'];

/**
 * 엔진별 부재 치수 및 기술난이도 조회 (category='grid', engine 일치 행).
 * 반환 예: [
 *   '울거미'    => ['thickness_mm'=>0, 'width_mm'=>33],
 *   '살'        => ['thickness_mm'=>0, 'width_mm'=>33],
 *   '문틀'      => ['thickness_mm'=>30, 'width_mm'=>33],
 *   '기술난이도' => ['weight'=>1.2],
 * ]
 */
function get_engine_part_dims(string $engine): array {
    try {
        $stmt = db()->prepare(
            "SELECT name, thickness_mm, width_mm, weight FROM cost_table
             WHERE category='grid' AND engine=? AND is_active=1"
        );
        $stmt->execute([$engine]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $r) {
            $result[$r['name']] = [
                'thickness_mm' => (int)$r['thickness_mm'],
                'width_mm'     => (int)$r['width_mm'],
                'weight'       => (float)$r['weight'],
            ];
        }
        return $result;
    } catch (Throwable $e) {
        return [];
    }
}

// cost_table의 wood 카테고리 활성 항목 반환. 없으면 기본 fallback.
function get_wood_options(): array {
    try {
        $stmt = db()->prepare("SELECT name, unit_price, weight FROM cost_table WHERE category='wood' AND is_active=1 ORDER BY sort_order, id");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) return $rows;
    } catch (Throwable $e) {}
    return [['name'=>'소나무','unit_price'=>6000,'weight'=>1.35]];
}

// cost_table의 finish 카테고리 활성 항목 반환. 없으면 기본 2개 fallback.
function get_finish_options(): array {
    try {
        $stmt = db()->prepare("SELECT name FROM cost_table WHERE category='finish' AND is_active=1 ORDER BY sort_order, id");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if ($rows) return $rows;
    } catch (Throwable $e) {}
    return ['창호지', '유리'];
}

// 엔진별 패턴 카테고리 목록 반환 [{code, name}] — 관리자가 name만 수정해도 반영됨
function get_pattern_categories(): array {
    try {
        $rows = db()->query("SELECT id, name FROM pattern_categories WHERE is_active = 1 ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) return $rows;
    } catch (Throwable $e) {}
    return [];
}
