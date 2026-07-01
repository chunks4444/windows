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
            return $common + ['cols' => '12', 'ratio' => '1.2', 'patternTop' => '3', 'patternMid' => '5', 'patternBot' => '3', 'min_days' => '3', 'min_work_hours' => '4'];
        case 'square':
            return $common + ['cols' => '6', 'ratio' => '1.0', 'shrinkH' => '0', 'min_days' => '3', 'min_work_hours' => '4'];
        case 'cross':
            return $common + ['cols' => '4', 'shrinkH' => '0', 'min_days' => '4', 'min_work_hours' => '6'];
        case 'diamond':
            return $common + ['cols' => '4', 'shrinkH' => '0', 'min_days' => '4', 'min_work_hours' => '6'];
        case 'triangle':
            return $common + ['cols' => '4', 'shrinkH' => '0', 'rotate' => '1', 'min_days' => '4', 'min_work_hours' => '8'];
        case 'hexagon':
            return $common + ['cols' => '3', 'shrinkH' => '0', 'rotate' => '1', 'min_days' => '5', 'min_work_hours' => '10'];
        default:
            return $common + ['min_days' => '3', 'min_work_hours' => '4'];
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

// 원가 계산용 상수 — cost_table (labor/overhead 카테고리) 기반
// $engine = 엔진명 또는 '*' (공통만)
function get_cost_config(string $engine = '*'): array {
    // 엔진별 하드코딩 fallback (cost_table에 행이 없을 때)
    $engineDefaults = [
        'classic'  => ['craft_time' => 2,  'ulgeomi_time' => 20, 'trim_time' => 30, 'muntol_time' => 60],
        'square'   => ['craft_time' => 3,  'ulgeomi_time' => 20, 'trim_time' => 40, 'muntol_time' => 60],
        'cross'    => ['craft_time' => 3,  'ulgeomi_time' => 20, 'trim_time' => 40, 'muntol_time' => 60],
        'diamond'  => ['craft_time' => 5,  'ulgeomi_time' => 25, 'trim_time' => 60, 'muntol_time' => 90],
        'triangle' => ['craft_time' => 6,  'ulgeomi_time' => 25, 'trim_time' => 60, 'muntol_time' => 90],
        'hexagon'  => ['craft_time' => 8,  'ulgeomi_time' => 30, 'trim_time' => 80, 'muntol_time' => 120],
    ];

    $defaults = [
        'hourly_rate'    => 30000,
        'finish_rate'    => 25000,
        'hardware_swing' => 15000,
        'hardware_slide' => 25000,
        'overhead_rate'  => 0.20,
        'profit_rate'    => 0.30,
        'craft_time'     => $engineDefaults[$engine]['craft_time']   ?? 3,
        'ulgeomi_time'   => $engineDefaults[$engine]['ulgeomi_time'] ?? 20,
        'trim_time'      => $engineDefaults[$engine]['trim_time']    ?? 40,
        'muntol_time'    => $engineDefaults[$engine]['muntol_time']  ?? 60,
    ];

    try {
        // 공통 행 (engine IS NULL) — labor
        $stmt = db()->prepare(
            "SELECT name, unit_price FROM cost_table
             WHERE category='labor' AND engine IS NULL AND is_active=1"
        );
        $stmt->execute();
        foreach ($stmt->fetchAll() as $r) {
            $defaults[$r['name']] = (float)$r['unit_price'];
        }

        // overhead 행: DB에 % 단위(예: 20)로 저장 → 0.0~1.0 소수로 변환
        $stmt = db()->prepare(
            "SELECT name, unit_price FROM cost_table
             WHERE category='overhead' AND engine IS NULL AND is_active=1"
        );
        $stmt->execute();
        foreach ($stmt->fetchAll() as $r) {
            $defaults[$r['name']] = (float)$r['unit_price'] / 100;
        }

        // 엔진별 행 (시간 단위 설정)
        if ($engine !== '*') {
            $stmt = db()->prepare(
                "SELECT name, unit_price FROM cost_table
                 WHERE category='labor' AND engine=? AND is_active=1"
            );
            $stmt->execute([$engine]);
            foreach ($stmt->fetchAll() as $r) {
                $defaults[$r['name']] = (float)$r['unit_price'];
            }
        }
    } catch (Throwable $e) {}

    return $defaults;
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

// cost_table의 hardware 카테고리 활성 항목 반환. 없으면 기본 fallback.
function get_hardware_options(): array {
    try {
        $stmt = db()->prepare("SELECT name, unit_price FROM cost_table WHERE category='hardware' AND is_active=1 ORDER BY sort_order, id");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) return $rows;
    } catch (Throwable $e) {}
    return [
        ['name'=>'여닫이 기본철물', 'unit_price'=>15000],
        ['name'=>'미서기 기본철물', 'unit_price'=>25000],
    ];
}

// cost_table의 finish 카테고리 활성 항목 반환. 없으면 기본 fallback.
function get_finish_options(): array {
    try {
        $stmt = db()->prepare("SELECT name, unit_price, work_time_min, coat_count FROM cost_table WHERE category='finish' AND is_active=1 ORDER BY sort_order, id");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) return $rows;
    } catch (Throwable $e) {}
    return [['name'=>'들기름','unit_price'=>8000,'work_time_min'=>10,'coat_count'=>2]];
}

// 엔진별 패턴 카테고리 목록 반환 [{code, name}] — 관리자가 name만 수정해도 반영됨
function get_pattern_categories(): array {
    try {
        $rows = db()->query("SELECT id, name FROM pattern_categories WHERE is_active = 1 ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) return $rows;
    } catch (Throwable $e) {}
    return [];
}
