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
$vRatio    = max(1.0, min(3.0, (float)($_POST['vRatio'] ?? 1.0)));
$rowsManual = (($_POST['rowsManual'] ?? '0') === '1');
$rowsCount  = max(1, (int)($_POST['rows'] ?? 6));
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

$effectivePungpanInput = $pungpanOn ? $pungpanH : 0;

$innerW = $outerW - 2 * $frameW;

$cellW = $cols > 0 ? ($innerW - $slatT * ($cols - 1)) / $cols : 0;
$stepW = $cellW + $slatT;
$cellH = $cellW * $vRatio;
$stepH = $cellH + $slatT;

$availH = $outerH - 2 * $frameH - $effectivePungpanInput;

if ($rowsManual) {
    $rows  = $rowsCount;
    $cellH = ($availH - $slatT * ($rows - 1)) / $rows;
} else {
    $rows = $stepH > 0 ? max(1, (int)(($availH + $slatT) / $stepH)) : 1;
}

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
    'eye'       => number_format($cellW, 1),
    'frameHTop' => (string)round($frameHTop),
    'totalDoorW'=> (string)round($totalDoorWidth),
    'overlap'   => $doorType === 'slide' ? (string)round($overlap) : '0',
    // 정자살은 직교(90°) 격자라 세모살 같은 각도 보정 없이 살두께 그대로가 반턱/홈폭이 된다.
    'halfLapW'  => number_format($slatT, 1),
    'grooveW'   => number_format($slatT, 1),
    'grooveWH'  => number_format($slatT, 1),
];

$pungpanVisible = $pungpanOn && $effectivePungpanH > 0;
$ppPanelH = $pungpanVisible ? ($effectivePungpanH - $frameH) : 0;

$hSlatCnt = max(0, $rows - 1);
$vSlatCnt = max(0, $cols - 1);

// 몬드리안 패턴 실측값 — 브라우저(mondrian.js mondrianCostParams)가 실제 살 개수와 길이 합을 보내면
// 위의 격자 가정(숨겨진 칸수 기준 가로·세로살 전폭) 대신 이 값으로 목재량·짜임 수를 계산한다.
// 길이는 내경 대비 비율: 가로살은 innerW, 세로살은 몬드리안 내경 높이(상하 울거미를 frameH로 고정하고
// 남는 높이를 패턴에 흡수 — mondrian.js effInnerH와 같은 식) 기준.
$_moOn = isset($_POST['moHCnt'], $_POST['moVCnt'], $_POST['moHLen'], $_POST['moVLen']);
if ($_moOn) {
    $_moHCnt   = max(0, (int)$_POST['moHCnt']);
    $_moVCnt   = max(0, (int)$_POST['moVCnt']);
    $_moInnerH = $innerH + ($frameHTop - $frameH) * 2;
    $_moHLenMm = max(0, (float)$_POST['moHLen']) * $innerW;
    $_moVLenMm = max(0, (float)$_POST['moVLen']) * $_moInnerH;
}

// 목재 재수 계산 (1재 = 33×33×3600mm³, 부재별 실제 단면 사용)
$_es  = get_engine_settings('mondrian');
$_JAE = 33 * 33 * 3600;
$_wU  = (int)($_es['ulgeomiW']             ?? $_d['울거미']['width_mm'] ?? 33);
$_wS  = (int)($_es['slatW']               ?? $_d['살']['width_mm']    ?? 20);
$_pT  = (int)($_es['pungpanT']            ?? 15);
$_tMt = (int)($_es['muntolT'] ?? 30);
$_wMt = (int)$_es['muntolW'];

$_volDoor   = round($outerH + 2*$slatT) * (2*$doorCount)                * $frameW * $_wU
            + round($outerW + 2*$slatT) * (($pungpanOn?3:2)*$doorCount) * $frameH * $_wU
            + ($_moOn
                ? ($_moHLenMm + 2*$tenonDepth*$_moHCnt + $_moVLenMm + 2*$tenonDepth*$_moVCnt) * $doorCount * $slatT * $_wS
                : round($innerW + 2*$tenonDepth) * ($hSlatCnt*$doorCount) * $slatT * $_wS
                + round($innerH + 2*$tenonDepth) * ($vSlatCnt*$doorCount) * $slatT * $_wS)
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
    'hSlatLen'       => (string)round($innerW + 2 * $tenonDepth),
    'hSlatCnt'       => ($hSlatCnt * $doorCount) . '개',
    'vSlatLen'       => (string)round($innerH + 2 * $tenonDepth),
    'vSlatCnt'       => ($vSlatCnt * $doorCount) . '개',
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
    // 몬드리안 살은 교차하지 않고 양 끝이 다른 살·울거미에 맞물리므로 짜임 수 = 살 개수 × 2 (다른 엔진처럼 1짝 기준)
    'joints'         => $_moOn ? ($_moHCnt + $_moVCnt) * 2 : $cols * $rows,
];

$selection = [
    'wood'      => (string)($_POST['wood']     ?? ''),
    'hardware'  => (string)($_POST['hardware'] ?? ''),
    'finish'    => (string)($_POST['finish']   ?? ''),
    'muntolOn'  => (($_POST['muntolOn'] ?? '1') === '1'),
    'doorCount' => $doorCount,
];
$_costCfg = get_cost_config('mondrian');
$price    = compute_price_estimate($parts, $selection, $_es, $_costCfg);

echo json_encode([
    'geo'   => $geo,
    'specs' => $perms['spec']  ? $specs : null,
    'parts' => $perms['parts'] ? $parts : null,
    'price' => ['total' => $perms['price'] ? $price['total'] : null, 'leadTimeDays' => $perms['leadtime'] ? $price['leadTimeDays'] : null],
    'costBreakdown' => $perms['cost'] ? $price['breakdown'] : null,
]);
