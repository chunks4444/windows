<?php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../../lib/jwt.php';
require_once __DIR__ . '/../../../lib/engine_settings.php';
require_once __DIR__ . '/../../../lib/spec_access.php';
if (!jwt_from_request()) { http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit; }
$perms = get_content_permissions();

$cols      = max(2,   (int)($_POST['cols']      ?? 4));
$pungpanH  = max(0,   (int)($_POST['pungpanH']  ?? 0));
$pungpanOn = (($_POST['pungpanOn'] ?? '0') === '1');
$frameW    = max(20,  (int)($_POST['frameW']    ?? 60));
$frameH    = max(20,  (int)($_POST['frameH']    ?? 60));
$slatT     = max(8,   (int)($_POST['slatT']     ?? 12));
$vRatio    = max(1.0, min(3.0, (float)($_POST['vRatio'] ?? 1.0)));
$doorType  = in_array($_POST['doorType'] ?? '', ['swing','slide']) ? $_POST['doorType'] : 'swing';
$doorCount = max(1, min(4, (int)($_POST['doorCount'] ?? 1)));

// 문틀(벽 개구부) 치수 → 문틀두께·갭을 양쪽에서 빼고 짝수에 따라 문 폭/높이(outerW/outerH) 역산
$frameOpeningW = max(400, (int)($_POST['frameOpeningW'] ?? 600));
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

$effectivePungpanInput = $pungpanOn ? $pungpanH : 0;

$innerW = $outerW - 2 * $frameW;

$cellW = $cols > 0 ? ($innerW - $slatT * ($cols - 1)) / $cols : 0;
$stepW = $cellW + $slatT;
$cellH = $cellW * $vRatio;
$stepH = $cellH + $slatT;

$availH = $outerH - 2 * $frameH - $effectivePungpanInput;
$rows   = $stepH > 0 ? max(1, (int)(($availH + $slatT) / $stepH)) : 1;

$innerH = $rows * $cellH + ($rows - 1) * $slatT;

$actualPatternH = $frameH + $innerH + $frameH;
$surplus = ($outerH - $effectivePungpanInput) - $actualPatternH;

$effectivePungpanH = $pungpanOn ? $effectivePungpanInput + $surplus : 0;
$actualPungpanH    = $effectivePungpanH;

$halfSurplus  = $pungpanOn ? 0 : $surplus / 2;
$frameHTop    = $frameH + $halfSurplus;
$frameHBottom = $pungpanOn ? $frameH : $frameH + ($surplus - $halfSurplus);

$step     = $stepW;
$stepH    = $cellH + $slatT;
$cellSize = $cellW;
$tenonDepth = $slatT;

$overlap = $frameW;
if ($doorType === 'slide') {
    if      ($doorCount === 1) $totalDoorWidth = $outerW;
    elseif  ($doorCount === 2) $totalDoorWidth = ($outerW * 2) - $overlap;
    elseif  ($doorCount === 3) $totalDoorWidth = ($outerW * 3) - ($overlap * 2);
    else                       $totalDoorWidth = ($outerW * 4) - ($overlap * 2);
} else {
    $totalDoorWidth = $outerW * $doorCount;
}

$geo = [
    'cellSize'          => $cellSize,
    'cellW'             => $cellW,
    'cellH'             => $cellH,
    'outerW'            => $outerW,
    'outerH'            => $outerH,
    'frameOpeningW'     => $frameOpeningW,
    'frameOpeningH'     => $frameOpeningH,
    'frameThick'        => $frameThick,
    'frameGap'          => $frameGap,
    'frameW'            => $frameW,
    'frameH'            => $frameH,
    'frameHTop'         => $frameHTop,
    'frameHBottom'      => $frameHBottom,
    'slatT'             => $slatT,
    'slatV'             => $slatT,
    'slatH'             => $slatT,
    'step'              => $step,
    'stepH'             => $stepH,
    'vRatio'            => $vRatio,
    'cols'              => $cols,
    'rows'              => $rows,
    'rowsInt'           => $rows,
    'innerW'            => $innerW,
    'innerH'            => $innerH,
    'actualPatternH'    => $actualPatternH,
    'actualPungpanH'    => $actualPungpanH,
    'effectivePungpanH' => $effectivePungpanH,
    'tenonDepth'        => $tenonDepth,
    'totalDoorWidth'    => $totalDoorWidth,
];

$specs = [
    'outerW'    => (string)round($outerW),
    'outerH'    => (string)round($outerH),
    'frameOpeningW' => (string)round($frameOpeningW),
    'frameOpeningH' => (string)round($frameOpeningH),
    'innerW'    => (string)round($innerW),
    'innerH'    => (string)round($innerH),
    'cols'      => (string)$cols,
    'rows'      => (string)round($rows),
    'step'      => number_format($step, 1),
    'stepV'     => number_format($stepH, 1),
    'pungpan'   => (string)round($effectivePungpanH),
    // 살 먹줄: 대각으로 놓인 살 위에서 교차점 중심-중심 거리 (살두께 포함)
    'eye'       => number_format(sqrt(pow($step, 2) + pow($stepH, 2)), 1),
    'frameHTop' => (string)round($frameHTop),
    'totalDoorW'=> (string)round($totalDoorWidth),
    'overlap'   => $doorType === 'slide' ? (string)round($overlap) : '0',
    // 빗살은 45° 대각 격자 — 두 대각선 살끼리는 90°로 교차하므로 반턱은 slatT 그대로,
    // 울거미 홈폭은 slatT × (1 + √2) (현장 확인값, 2026-07-02)
    'halfLapW'  => number_format($slatT, 1),
    'grooveW'   => number_format($slatT * (1 + sqrt(2)), 1),
    'grooveWH'  => number_format($slatT * (1 + sqrt(2)), 1),
];

$pungpanVisible = $pungpanOn && $effectivePungpanH > 0;
$ppPanelH = $pungpanVisible ? ($effectivePungpanH - $frameH) : 0;

// 셀 하나의 대각 길이 (촉 제외)
$cellDiag = sqrt(pow($stepW, 2) + pow($stepH, 2));

// ↘ 방향: r-c = d, ↗ 방향: r+c = s 로 그룹핑
$groups = [];
for ($r = 0; $r < $rows; $r++) {
    for ($c = 0; $c < $cols; $c++) {
        $bs = $r - $c; // ↘
        $fs = $r + $c; // ↗
        $groups['bs'][$bs] = ($groups['bs'][$bs] ?? 0) + 1;
        $groups['fs'][$fs] = ($groups['fs'][$fs] ?? 0) + 1;
    }
}

// 방향별 그룹을 길이 기준으로 합산
$lenMap = [];
foreach (['bs', 'fs'] as $dir) {
    foreach ($groups[$dir] as $k) {
        $len = (int)round($k * $cellDiag + 2 * $slatT);
        $lenMap[$len] = ($lenMap[$len] ?? 0) + 1;
    }
}

// doorCount 곱하고 길이 내림차순 정렬
$diagList = [];
krsort($lenMap);
foreach ($lenMap as $len => $cnt) {
    $diagList[] = ['len' => $len, 'cnt' => $cnt * $doorCount];
}

// 목재 재수 계산 (1재 = 33×33×3600mm³, 부재별 실제 단면 사용)
$_es  = get_engine_settings('cross');
$_JAE = 33 * 33 * 3600;
$_wU  = (int)($_es['ulgeomiW']             ?? $_d['울거미']['width_mm'] ?? 33);
$_wS  = (int)($_es['slatW']               ?? $_d['살']['width_mm']    ?? 20);
$_pT  = (int)($_es['pungpanT']            ?? 15);
$_tMt = (int)($_es['muntolT'] ?? 30);
$_wMt = (int)$_es['muntolW'];

$_volDoor   = round($outerH + 2*$slatT) * (2*$doorCount)                * $frameW * $_wU
            + round($outerW + 2*$slatT) * (($pungpanOn?3:2)*$doorCount) * $frameH * $_wU
            + array_sum(array_map(fn($d) => (float)$d['len'] * $d['cnt'] * $slatT * $_wS, $diagList))
            + ($pungpanVisible ? (int)round($innerW) * (int)round($ppPanelH) * $_pT : 0);
$_volMuntol = $frameOpeningH * 2 * $_tMt * $_wMt
            + $frameOpeningW * 2 * $_tMt * $_wMt;
$_vol       = $_volDoor + $_volMuntol;
$_woodJae   = $_vol / $_JAE;

$parts = [
    'frVLen'         => (string)round($outerH + 2 * $slatT),
    'frVCnt'         => (2 * $doorCount) . '개',
    'frHLen'         => (string)round($outerW + 2 * $slatT),
    'frHCnt'         => (($pungpanOn ? 3 : 2) * $doorCount) . '개',
    'pungpanVisible' => $pungpanVisible,
    'pungpanCnt'     => $doorCount . '개',
    'ppHLen'         => (string)round($innerW + 2 * $slatT),
    'ppVLen'         => (string)round($ppPanelH + 2 * $slatT),
    'hSlatLen'       => '',
    'hSlatCnt'       => '',
    'vSlatLen'       => '',
    'vSlatCnt'       => '',
    'diagList'       => $diagList,
    'frT'            => $_wU,
    'slatW'          => $_wS,
    'pungpanT'       => $_pT,
    'mtVLen'         => (int)$frameOpeningH,
    'mtHLen'         => (int)$frameOpeningW,
    'mtFace'          => (int)$_es['muntolFace'],
    'mtW'            => $_wMt,
    'mtT'            => $_tMt,
    'woodVolMm3'     => (int)$_vol,
    'woodJae'        => round($_woodJae, 2),
    'woodJae_door'   => round($_volDoor / $_JAE, 2),
    'woodJae_muntol' => round($_volMuntol / $_JAE, 2),
    'joints'         => $cols * $rows,
];

$selection = [
    'wood'      => (string)($_POST['wood']     ?? ''),
    'hardware'  => (string)($_POST['hardware'] ?? ''),
    'finish'    => (string)($_POST['finish']   ?? ''),
    'muntolOn'  => (($_POST['muntolOn'] ?? '1') === '1'),
    'doorCount' => $doorCount,
];
$_costCfg = get_cost_config('cross');
$price    = compute_price_estimate($parts, $selection, $_es, $_costCfg);

echo json_encode([
    'geo'   => $geo,
    'specs' => $perms['spec']  ? $specs : null,
    'parts' => $perms['parts'] ? $parts : null,
    'price' => ['total' => $perms['price'] ? $price['total'] : null, 'leadTimeDays' => $perms['leadtime'] ? $price['leadTimeDays'] : null],
    'costBreakdown' => $perms['cost'] ? $price['breakdown'] : null,
]);
