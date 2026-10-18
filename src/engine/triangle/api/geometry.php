<?php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../../lib/jwt.php';
require_once __DIR__ . '/../../../lib/engine_settings.php';
require_once __DIR__ . '/../../../lib/spec_access.php';
// 도면 계산은 비로그인도 허용(설계는 자유, 저장·출력 시에만 로그인 요구). get_content_permissions()는 비로그인이면 모두 false 반환.
$perms = get_content_permissions();

$cols      = max(2,   (int)($_POST['cols']      ?? 4));
$pungpanH  = max(0,   (int)($_POST['pungpanH']  ?? 0));
$pungpanOn = (($_POST['pungpanOn'] ?? '0') === '1');
$frameW    = max(15,  (int)($_POST['frameW']    ?? 60));
$frameH    = max(15,  (int)($_POST['frameH']    ?? 60));
$slatT     = max(8,   (int)($_POST['slatT']     ?? 12));
$rotateOn  = (($_POST['rotateOn']  ?? '0') === '1');
$doorType  = in_array($_POST['doorType'] ?? '', ['swing','slide']) ? $_POST['doorType'] : 'swing';
$doorCount = max(1, min(8, (int)($_POST['doorCount'] ?? 1)));
if ($doorType === 'swing' && $doorCount > 2) $doorCount = 2;
if ($doorCount === 5) $doorCount = 4; // 5짝은 지원하지 않음(짝 구성 미정)

// 문틀(벽 개구부) 치수 → 문틀두께·갭을 양쪽에서 빼고 짝수에 따라 문 폭/높이(outerW/outerH) 역산
$frameOpeningW = max(100, (int)($_POST['frameOpeningW'] ?? 600));
$frameOpeningH = max(400, (int)($_POST['frameOpeningH'] ?? 1707));
$frameThick    = max(0,   (int)($_POST['frameThick']    ?? 30));
$frameGap      = max(0,   (int)($_POST['frameGap']      ?? 2));

$base   = $frameOpeningW - 2 * $frameThick - 2 * $frameGap;
$outerH = max(100, $frameOpeningH - 2 * $frameThick - 2 * $frameGap);

if ($doorType === 'slide') {
    $outerW = ($base + $frameW * ($doorCount - 1)) / $doorCount;
} else {
    $outerW = ($base - $frameGap * ($doorCount - 1)) / $doorCount;
}
$outerW = max(100, $outerW);

$SQRT3 = sqrt(3);
$effectivePungpanInput = $pungpanOn ? $pungpanH : 0;
$colStepR = 0.0;
$rowHR    = 0.0;

if (!$rotateOn) {
    $innerW = $outerW - 2 * $frameW;
    $stepX  = $cols > 0 ? $innerW / $cols : 0;
    $rowH   = $stepX * $SQRT3 / 2;
    $availH = $outerH - 2 * $frameH - $effectivePungpanInput;
    $rows   = $rowH > 0 ? max(2, (int)(($availH + $slatT) / $rowH) + 1) : 2;
    $innerH = ($rows - 1) * $rowH - $slatT;
    $actualPatternH0 = $frameH + $innerH + $frameH;
    $surplus = ($outerH - $effectivePungpanInput) - $actualPatternH0;
    $effectivePungpanH = $pungpanOn ? $effectivePungpanInput + $surplus : 0;
    $actualPungpanH    = $effectivePungpanH;
    $halfSurplus  = $pungpanOn ? 0 : $surplus / 2;
    $frameHTop    = $frameH + $halfSurplus;
    $frameHBottom = $pungpanOn ? $frameH : $frameH + ($surplus - $halfSurplus);
    $frameWActual = $frameW;
} else {
    $innerW   = $outerW - 2 * $frameW;
    $stepX    = $cols > 0 ? $innerW / $cols : 0;
    $rowH     = $stepX * $SQRT3 / 2;
    $colStepR = $cols > 0 ? ($innerW + $slatT) / $cols : 0;
    $rowHR    = $colStepR > 0 ? $colStepR * 2 / $SQRT3 : 0;
    $availH2  = $outerH - 2 * $frameH - $effectivePungpanInput;
    $rows     = $rowHR > 0 ? max(2, (int)($availH2 / $rowHR) + 1) : 2;
    $innerH   = ($rows - 1) * $rowHR;
    $actualPatternHR = $frameH + $innerH + $frameH;
    $surplusR = ($outerH - $effectivePungpanInput) - $actualPatternHR;
    $effectivePungpanH = $pungpanOn ? $effectivePungpanInput + $surplusR : 0;
    $actualPungpanH    = $effectivePungpanH;
    $halfSurplusR = $pungpanOn ? 0 : $surplusR / 2;
    $frameHTop    = $frameH + $halfSurplusR;
    $frameHBottom = $pungpanOn ? $frameH : $frameH + ($surplusR - $halfSurplusR);
    $frameWActual = $frameW;
}

$cellW       = $rotateOn ? $rowHR - $slatT : $stepX;
$cellH       = $rowH;
$cellSize    = $stepX;
$step        = $rotateOn ? $rowHR : $stepX;
$stepH       = $rowH + $slatT;
$diagEye     = number_format($rotateOn ? ($colStepR - $slatT) : (($innerW + $slatT) / $cols - $slatT), 1);
$tenonDepth  = $slatT;
$actualPatternH = $frameHTop + $innerH + $frameHBottom;

$overlap = $frameWActual;
if ($doorType === 'slide') {
    if      ($doorCount === 1) $totalDoorWidth = $outerW;
    elseif  ($doorCount === 2) $totalDoorWidth = ($outerW * 2) - $overlap;
    elseif  ($doorCount === 3) $totalDoorWidth = ($outerW * 3) - ($overlap * 2);
    elseif  ($doorCount === 4) $totalDoorWidth = ($outerW * 4) - ($overlap * 2);
    elseif  ($doorCount === 6) $totalDoorWidth = ($outerW * 6) - ($overlap * 4);
    else                       $totalDoorWidth = ($outerW * 8) - ($overlap * 6);
} else {
    $totalDoorWidth = $outerW * $doorCount;
}

$geo = [
    'cellSize'          => $cellSize,
    'cellW'             => $cellW,
    'cellH'             => $cellH,
    'stepX'             => $stepX,
    'rowH'              => $rowH,
    'outerW'            => $outerW,
    'outerH'            => $outerH,
    'frameOpeningW'     => $frameOpeningW,
    'frameOpeningH'     => $frameOpeningH,
    'frameThick'        => $frameThick,
    'frameGap'          => $frameGap,
    'frameW'            => $frameWActual,
    'frameH'            => $frameH,
    'frameHTop'         => $frameHTop,
    'frameHBottom'      => $frameHBottom,
    'slatT'             => $slatT,
    'slatV'             => $slatT,
    'slatH'             => $slatT,
    'step'              => $step,
    'stepH'             => $stepH,
    'cols'              => $cols,
    'rows'              => $rows,
    'rowsInt'           => $rows,
    'innerW'            => $innerW,
    'innerH'            => $innerH,
    'actualPatternH'    => $actualPatternH,
    'actualPungpanH'    => $actualPungpanH,
    'effectivePungpanH' => $effectivePungpanH,
    'diagEye'           => $diagEye,
    'tenonDepth'        => $tenonDepth,
    'totalDoorWidth'    => $totalDoorWidth,
    'colStepR'          => $colStepR,
    'rowHR'             => $rowHR,
];

$halfLapW = $slatT * 2 / $SQRT3;

$specs = [
    'outerW'    => (string)round($outerW),
    'outerH'    => (string)round($outerH),
    'frameOpeningW' => (string)round($frameOpeningW),
    'frameOpeningH' => (string)round($frameOpeningH),
    'innerW'    => (string)round($innerW),
    'innerH'    => (string)round($innerH),
    'cols'      => (string)$cols,
    'rows'      => (string)round($rows),
    'step'      => number_format(($innerW + $slatT) / $cols, 1),
    'pungpan'   => (string)round($effectivePungpanH),
    'eye'       => $rows > 0 ? number_format(($innerH + $slatT) / $rows, 1) : '0',
    'diagEye'   => $diagEye,
    'frameHTop' => (string)round($frameHTop),
    'totalDoorW'=> (string)round($totalDoorWidth),
    'overlap'   => $doorType === 'slide' ? (string)round($overlap) : '0',
    'halfLapW'  => number_format($halfLapW, 1),
    'grooveW'   => number_format($slatT * sqrt(3), 1),
    'grooveWH'  => number_format($slatT, 1),
];

$pungpanVisible = $pungpanOn && $effectivePungpanH > 0;
$ppPanelH = $pungpanVisible ? ($effectivePungpanH - $frameH) : 0;

$parts = [
    'frVLen'         => (string)round($outerH + 2 * $slatT),
    'frVCnt'         => (2 * $doorCount) . '개',
    'frHLen'         => (string)round($outerW + 2 * $slatT),
    'frHCnt'         => (($pungpanOn ? 3 : 2) * $doorCount) . '개',
    'pungpanVisible' => $pungpanVisible,
    'pungpanCnt'     => $doorCount . '개',
    'ppHLen'         => (string)round($innerW + 2 * $slatT),
    'ppVLen'         => (string)round($ppPanelH + 2 * $slatT),
    'dirTitle'       => $rotateOn ? '세로부재' : '가로부재',
    'hSlatLen'       => $rotateOn
                            ? (string)round($innerH + 2 * $tenonDepth)
                            : (string)round($innerW + 2 * $tenonDepth),
    'hSlatCnt'       => $rotateOn
                            ? (max(0, $cols - 1) * $doorCount) . '개'
                            : (max(0, $rows - 1) * $doorCount) . '개',
    'diagList'       => [],
];

// ── 사선살 길이 계산 ────────────────────────────
function calcGroups60(float $iW, float $iH, float $bStep, float $sT): array {
    $SQRT3 = sqrt(3);
    $EPS   = 0.5;
    $g     = [];
    if ($bStep <= 0) return $g;
    $bMin = ceil(-$iW * $SQRT3 / $bStep - $EPS) * $bStep;
    $bMax = floor($iH / $bStep + $EPS) * $bStep;
    for ($b = $bMin; $b <= $bMax + $EPS; $b += $bStep) {
        $x1 = $b < -$EPS ? -$b / $SQRT3 : 0.0;
        $y1 = $b < -$EPS ? 0.0 : max(0.0, $b);
        $yR = $SQRT3 * $iW + $b;
        $x2 = $yR < $iH - $EPS ? $iW : ($iH - $b) / $SQRT3;
        $y2 = $yR < $iH - $EPS ? $yR : $iH;
        if ($x1 > $iW + $EPS || $x2 < -$EPS || $x1 > $x2 + $EPS) continue;
        $seg = sqrt(($x2 - $x1) ** 2 + ($y2 - $y1) ** 2);
        if ($seg < $EPS) continue;
        $k = (string)(int)round($seg + 2 * $sT);
        $g[$k] = ($g[$k] ?? 0) + 1;
    }
    return $g;
}

function calcGroups30(float $iW, float $iH, float $bStep, float $sT): array {
    $SQRT3 = sqrt(3);
    $EPS   = 0.5;
    $g     = [];
    if ($bStep <= 0) return $g;
    $bMin = ceil(-$iW / $SQRT3 / $bStep - $EPS) * $bStep;
    $bMax = floor($iH / $bStep + $EPS) * $bStep;
    for ($b = $bMin; $b <= $bMax + $EPS; $b += $bStep) {
        $x1 = $b < -$EPS ? -$b * $SQRT3 : 0.0;
        $y1 = $b < -$EPS ? 0.0 : max(0.0, $b);
        $yR = $iW / $SQRT3 + $b;
        $x2 = $yR < $iH - $EPS ? $iW : ($iH - $b) * $SQRT3;
        $y2 = $yR < $iH - $EPS ? $yR : $iH;
        if ($x1 > $iW + $EPS || $x2 < -$EPS || $x1 > $x2 + $EPS) continue;
        $seg = sqrt(($x2 - $x1) ** 2 + ($y2 - $y1) ** 2);
        if ($seg < $EPS) continue;
        $k = (string)(int)round($seg + 2 * $sT);
        $g[$k] = ($g[$k] ?? 0) + 1;
    }
    return $g;
}

$rawGroups = $rotateOn
    ? calcGroups30((float)$innerW, (float)$innerH, (float)$rowHR,            (float)$slatT)
    : calcGroups60((float)$innerW, (float)$innerH, (float)($stepX * $SQRT3), (float)$slatT);

uksort($rawGroups, function($a, $b) { return (int)$a - (int)$b; });

$sorted = [];
foreach ($rawGroups as $len => $cnt) {
    $sorted[] = ['len' => (int)$len, 'cnt' => (int)$cnt];
}

$MERGE_TOL = 20;
$merged = [];
foreach ($sorted as $item) {
    if (!empty($merged)) {
        $last = &$merged[count($merged) - 1];
        if ($item['len'] - $last['len'] <= $MERGE_TOL) {
            $last['cnt'] += $item['cnt'];
            $last['len']  = max($last['len'], $item['len']);
            unset($last);
            continue;
        }
        unset($last);
    }
    $merged[] = $item;
}

usort($merged, function($a, $b) { return $b['len'] - $a['len']; });
$parts['diagList'] = array_map(function($item) use ($doorCount) {
    return ['len' => $item['len'], 'cnt' => $item['cnt'] * 2 * $doorCount];
}, $merged);

// 목재 재수 계산 (1재 = 30.3×30.3×3636mm³, 부재별 실제 단면 사용)
$_es  = get_engine_settings('triangle');
$_JAE = 30.3 * 30.3 * 3636;
$_wU  = (int)($_es['ulgeomiW']             ?? $_d['울거미']['width_mm'] ?? 33);
$_wS  = (int)($_es['slatW']               ?? $_d['살']['width_mm']    ?? 20);
$_pT  = (int)($_es['pungpanT']            ?? 15);
$_tMt = (int)($_es['muntolT'] ?? 30);
$_wMt = (int)$_es['muntolW'];

$_hSLen   = $rotateOn ? round($innerH + 2*$tenonDepth) : round($innerW + 2*$tenonDepth);
$_hSCnt_n = $rotateOn ? max(0, $cols-1) * $doorCount : max(0, $rows-1) * $doorCount;

$_volDoor   = round($outerH + 2*$slatT) * (2*$doorCount)                * $frameW * $_wU
            + round($outerW + 2*$slatT) * (($pungpanOn?3:2)*$doorCount) * $frameHTop * $_wU
            + $_hSLen * $_hSCnt_n * $slatT * $_wS
            + array_sum(array_map(fn($d) => (float)$d['len'] * $d['cnt'] * $slatT * $_wS, $parts['diagList']))
            + ($pungpanVisible ? (int)round($innerW) * (int)round($ppPanelH) * $_pT : 0);
$_volMuntol = $frameOpeningH * 2 * $_tMt * $_wMt
            + $frameOpeningW * 2 * $_tMt * $_wMt;
$_vol       = $_volDoor + $_volMuntol;
$parts['frT']            = $_wU;
$parts['slatW']          = $_wS;
$parts['pungpanT']       = $_pT;
$parts['mtVLen']         = (int)$frameOpeningH;
$parts['mtHLen']         = (int)$frameOpeningW;
$parts['mtFace']          = (int)$_es['muntolFace'];
$parts['mtW']            = $_wMt;
$parts['mtT']            = $_tMt;
$parts['woodVolMm3']     = (int)$_vol;
$parts['woodJae']        = round($_vol / $_JAE, 2);
$parts['woodJae_door']   = round($_volDoor / $_JAE, 2);
$parts['woodJae_muntol'] = round($_volMuntol / $_JAE, 2);
$parts['joints']         = $cols * $rows;

// 부재별 표면적·체적·무게 (문짝 1짝 기준 — 문틀은 문짝 수와 무관하게 1개) — 표면적은 각목 긴 옆면 4면
// 근사(2×(폭+두께)×길이), 양끝 단면은 무시한다(도장 면적 계산과 같은 기준). 풍판은 울거미에 합산.
// 살 = 직선살($_hSLen·$_hSCnt_n)+사선살($diagList) — 둘 다 cnt에 doorCount가 이미 곱해져 있어 나눠서 1짝분만 쓴다.
$_ulV  = round($outerH + 2*$slatT) * 2                  * $frameW * $_wU;
$_ulH  = round($outerW + 2*$slatT) * ($pungpanOn ? 3 : 2) * $frameHTop * $_wU;
$_ulVA = 2 * ($frameW + $_wU) * round($outerH + 2*$slatT) * 2;
$_ulHA = 2 * ($frameHTop + $_wU) * round($outerW + 2*$slatT) * ($pungpanOn ? 3 : 2);
$_ppV  = $pungpanVisible ? (int)round($innerW) * (int)round($ppPanelH) * $_pT : 0;
$_ppA  = $pungpanVisible ? 2 * (int)round($innerW) * (int)round($ppPanelH) : 0;
$_hSCntDoor = $doorCount > 0 ? $_hSCnt_n / $doorCount : 0;
$_slHDoor   = $_hSLen * $_hSCntDoor * $slatT * $_wS;
$_slHADoor  = 2 * ($_wS + $slatT) * $_hSLen * $_hSCntDoor;
$_diagVolDoor = 0; $_diagLenDoor = 0;
foreach ($parts['diagList'] as $d) { $cntDoor = $d['cnt'] / $doorCount; $_diagVolDoor += $d['len'] * $cntDoor * $slatT * $_wS; $_diagLenDoor += $d['len'] * $cntDoor; }
$_diagAreaDoor = 2 * ($_wS + $slatT) * $_diagLenDoor;
$_mtV  = $frameOpeningH * 2 * $_tMt * $_wMt;
$_mtH  = $frameOpeningW * 2 * $_tMt * $_wMt;
$_mtVA = 2 * ($_wMt + $_tMt) * $frameOpeningH * 2;
$_mtHA = 2 * ($_wMt + $_tMt) * $frameOpeningW * 2;

$_ulgeomiVolDoor  = $_ulV + $_ulH + $_ppV;
$_ulgeomiAreaDoor = $_ulVA + $_ulHA + $_ppA;
$_slatVolDoor     = $_slHDoor + $_diagVolDoor;
$_slatAreaDoor    = $_slHADoor + $_diagAreaDoor;
$_muntolVol       = $_mtV + $_mtH;
$_muntolArea      = $_mtVA + $_mtHA;

$_woodDensity = (float)(get_cost_table_item('wood', (string)($_POST['wood'] ?? ''))['density'] ?? 0);
$_toKg = fn(float $mm3): ?float => $_woodDensity > 0 ? round($mm3 / 1_000_000 * $_woodDensity, 3) : null;

$parts['physical'] = [
    'density'   => $_woodDensity ?: null,
    'doorCount' => $doorCount,
    'ulgeomi'   => ['volMm3' => round($_ulgeomiVolDoor), 'areaM2' => round($_ulgeomiAreaDoor / 1_000_000, 3), 'weightKg' => $_toKg($_ulgeomiVolDoor)],
    'slat'      => ['volMm3' => round($_slatVolDoor),    'areaM2' => round($_slatAreaDoor / 1_000_000, 3),    'weightKg' => $_toKg($_slatVolDoor)],
    'muntol'    => ['volMm3' => round($_muntolVol),      'areaM2' => round($_muntolArea / 1_000_000, 3),      'weightKg' => $_toKg($_muntolVol)],
];
if (!$perms['physical']) $parts['physical'] = null;

$selection = [
    'wood'      => (string)($_POST['wood']     ?? ''),
    'hardware'  => (string)($_POST['hardware'] ?? ''),
    'finish'    => (string)($_POST['finish']   ?? ''),
    'muntolOn'  => (($_POST['muntolOn'] ?? '1') === '1'),
    'doorCount' => $doorCount,
];
$_costCfg = get_cost_config('triangle');
$price    = compute_price_estimate($parts, $selection, $_es, $_costCfg);

// 면 재질(한지/유리) 무게 계산용 — 유리칸은 count × 셀면적 × 두께 × 비중으로 클라이언트가 계산한다
$_glass = get_cost_table_item('glass', '유리');

echo json_encode([
    'geo'   => $geo,
    'specs' => $perms['spec']  ? $specs : null,
    'parts' => $perms['parts'] ? $parts : null,
    'price' => ['total' => $perms['price'] ? $price['total'] : null, 'leadTimeDays' => $perms['leadtime'] ? $price['leadTimeDays'] : null],
    'costBreakdown' => $perms['cost'] ? $price['breakdown'] : null,
    'glassConfig' => $perms['physical'] ? ['density' => $_glass['density'] ?? null, 'thicknessMm' => $_glass['thickness_mm'] ?? null] : null,
]);
