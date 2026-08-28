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
            return $common + ['cols' => '6', 'ratio' => '1.0', 'shrinkH' => '0', 'rowsManual' => '0', 'rows' => '6', 'min_days' => '3', 'min_work_hours' => '4'];
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

// 슬라이더 드래그 등으로 geometry.php가 프레임마다 재요청되므로,
// 매 요청 DB 왕복(로컬 개발환경은 원격 DB라 특히 느림)을 줄이기 위해 짧은 TTL로 파일 캐시.
// 어드민이 설정을 저장하면 engine_settings_cache_clear()로 즉시 무효화됨.
const ENGINE_SETTINGS_CACHE_TTL = 20; // seconds

function engine_settings_cache_file(string $engine): string {
    return sys_get_temp_dir() . '/pmok_engine_settings_' . preg_replace('/[^a-z]/', '', $engine) . '.json';
}

function engine_settings_cache_clear(string $engine): void {
    $f = engine_settings_cache_file($engine);
    if (is_file($f)) @unlink($f);
}

// 엔진의 현재 설정값. DB에 없는 키는 PHP 기본값으로 자동 INSERT → 이후 어드민 UI에서 관리 가능.
function get_engine_settings(string $engine): array {
    $defaults  = engine_setting_defaults($engine);
    $cacheFile = engine_settings_cache_file($engine);

    if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < ENGINE_SETTINGS_CACHE_TTL) {
        $cached = json_decode((string)file_get_contents($cacheFile), true);
        if (is_array($cached)) return array_merge($defaults, $cached);
    }

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
        file_put_contents($cacheFile, json_encode($rows), LOCK_EX);
    } catch (Throwable $e) {
        $rows = [];
    }
    return array_merge($defaults, $rows);
}

const ENGINE_SETTING_NAMES = ['classic', 'square', 'cross', 'diamond', 'triangle', 'hexagon'];

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

// cost_table에서 category+name으로 단건 조회 (선택된 목재/철물/마감의 단가 룩업용)
function get_cost_table_item(string $category, string $name): ?array {
    if ($name === '') return null;
    try {
        $stmt = db()->prepare("SELECT unit_price, weight, work_time_min, coat_count FROM cost_table WHERE category=? AND name=? AND is_active=1 LIMIT 1");
        $stmt->execute([$category, $name]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    } catch (Throwable $e) {
        return null;
    }
}

// 예상가격(총액) + 예산견적 상세 breakdown 서버사이드 계산.
// src/js/engine-common.js의 updateWoodCost()와 1:1 대응되는 공식 — 두 코드를 수정할 땐 반드시 함께 맞출 것.
// $parts = geometry.php가 계산한 부재목록 배열, $selection = 사용자가 고른 목재/철물/마감/문틀/문짝수,
// $cfg = get_engine_settings($engine), $costCfg = get_cost_config($engine).
function compute_price_estimate(array $parts, array $selection, array $cfg, array $costCfg): array {
    $leadDays = (int)($cfg['min_days'] ?? 0);

    $wood   = get_cost_table_item('wood', $selection['wood'] ?? '');
    $price  = $wood ? (float)$wood['unit_price'] : 0.0;
    $weight = $wood ? (float)$wood['weight'] : 1.0;
    if ($price <= 0) {
        return ['total' => 0, 'leadTimeDays' => $leadDays, 'breakdown' => null];
    }

    $showMuntol = !empty($selection['muntolOn']);
    $doorCost   = (int)round(($parts['woodJae_door']   ?? 0) * $weight * $price);
    $muntolCost = $showMuntol ? (int)round(($parts['woodJae_muntol'] ?? 0) * $weight * $price) : 0;
    $woodCost   = $doorCost + $muntolCost;

    $craftTime   = (float)($costCfg['craft_time']   ?? 3);
    $ulgeomiTime = (float)($costCfg['ulgeomi_time'] ?? 20);
    $trimTime    = (float)($costCfg['trim_time']    ?? 40);
    $hourlyRate  = (float)($costCfg['hourly_rate']  ?? 30000);
    $joints      = (float)($parts['joints'] ?? 0);
    $doorCount   = max(1, (int)($selection['doorCount'] ?? 1));
    $muntolTime  = $showMuntol ? (float)($costCfg['muntol_time'] ?? 60) : 0.0;
    $totalMin    = $joints * $craftTime + $doorCount * ($ulgeomiTime + $trimTime) + $muntolTime;
    $craftCost   = (int)round($totalMin / 60 * $hourlyRate);

    $hw     = get_cost_table_item('hardware', $selection['hardware'] ?? '');
    $hwRate = $hw ? (float)$hw['unit_price'] : 0.0;
    $hardwareCost = (int)round($doorCount * $hwRate);

    // 도장 면적 (1재 = 33×33×3600mm³, 육면체 4면 기준)
    $slatW     = (float)($cfg['slatW'] ?? 20);
    $slatThick = (float)($cfg['slat']  ?? 12);
    $JAE_VOL   = 33 * 33 * 3600;
    $totalLenMm = ($slatW * $slatThick) > 0 ? ($parts['woodJae_door'] ?? 0) * $JAE_VOL / ($slatW * $slatThick) : 0;
    $surfaceM2  = $totalLenMm * 2 * ($slatW + $slatThick) / 1_000_000;

    $finish = get_cost_table_item('finish', $selection['finish'] ?? '');
    $fPrice = $finish ? (float)$finish['unit_price']    : 0.0;
    $fTime  = $finish ? (float)$finish['work_time_min'] : 0.0;
    $fCoats = $finish ? (int)$finish['coat_count']      : 0;
    $finishRate      = (float)($costCfg['finish_rate'] ?? 25000);
    $finishMatCost   = $fPrice > 0 ? (int)round($fPrice * $surfaceM2 * $fCoats) : 0;
    $finishLaborCost = $fTime  > 0 ? (int)round($surfaceM2 * $fTime * $fCoats / 60 * $finishRate) : 0;
    $finishCost      = $finishMatCost + $finishLaborCost;

    $base         = $woodCost + $craftCost + $hardwareCost + $finishCost;
    $overheadRate = (float)($costCfg['overhead_rate'] ?? 0.20);
    $profitRate   = (float)($costCfg['profit_rate']   ?? 0.30);
    $overheadCost = (int)round($base * $overheadRate);
    $profitCost   = (int)round($base * $profitRate);
    $totalCost    = $base + $overheadCost + $profitCost;

    $finishTimeMin = $fTime > 0 ? $surfaceM2 * $fTime * $fCoats : 0;
    $totalWorkMin  = $totalMin + $finishTimeMin;
    $minWorkHours  = (float)($cfg['min_work_hours'] ?? 0) * 60;
    $displayMin    = max($totalWorkMin, $minWorkHours);
    $calcDays      = (int)ceil($displayMin / 60 / 8);
    $leadDays      = max($leadDays, $calcDays);

    $h = (int)floor($displayMin / 60);
    $m = (int)round(fmod($displayMin, 60));
    $craftTimeStr = $h > 0 ? "{$h}시간 {$m}분" : "{$m}분";

    $breakdown = [
        'door' => $doorCost, 'muntol' => $muntolCost, 'wood' => $woodCost,
        'craft' => $craftCost, 'craftTime' => $craftTimeStr,
        'hardware' => $hardwareCost, 'finish' => $finishCost,
        'overhead' => $overheadCost, 'profit' => $profitCost, 'total' => $totalCost,
    ];
    // 대분류 5종(wood/labor/hardware/finish/overhead) 소계 — cost_table.category와 1:1 대응.
    // 위 $breakdown에서 읽어와 파생시키므로(재계산 아님) breakdown 쪽 계산이 바뀌어도 항상 따라간다.
    // 카테고리 안의 개별 항목(오일/한지 등)이 늘어나도 이 소계 구조 자체는 안 바뀜.
    $breakdown['categories'] = [
        'wood'     => $breakdown['wood'],
        'labor'    => $breakdown['craft'],
        'hardware' => $breakdown['hardware'],
        'finish'   => $breakdown['finish'],
        'overhead' => $breakdown['overhead'] + $breakdown['profit'],
        'total'    => $breakdown['total'],
    ];

    return [
        'total' => $totalCost,
        'leadTimeDays' => $leadDays,
        'breakdown' => $breakdown,
    ];
}

// 엔진별 패턴 카테고리 목록 반환 [{id, name, code}] — 관리자가 name만 수정해도 반영됨
// code는 평목 컬렉션 코드 체계 v1.0 계열 코드(NULL이면 코드 없는 카테고리), library_patterns.slug 생성에 사용
function get_pattern_categories(): array {
    try {
        $rows = db()->query("SELECT id, name, code FROM pattern_categories WHERE is_active = 1 ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) return $rows;
    } catch (Throwable $e) {}
    return [];
}

// 평목 컬렉션 코드 체계 v1.0 수식어 목록 [{id, name, code}] — library_patterns 생성 시 select 옵션으로 사용
function get_pattern_modifiers(): array {
    try {
        $rows = db()->query("SELECT id, name, code FROM pattern_modifiers WHERE is_active = 1 ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) return $rows;
    } catch (Throwable $e) {}
    return [];
}

// AI 렌더링 재질/조명 프리셋 목록 반환 [{id, label, prompt_text}] — 관리자 페이지에서 편집
function get_render_presets(): array {
    try {
        $rows = db()->query("SELECT id, label, prompt_text FROM render_presets WHERE is_active = 1 ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) return $rows;
    } catch (Throwable $e) {}
    return [];
}
