<?php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../../lib/jwt.php';
require_once __DIR__ . '/../../../lib/engine_settings.php';
if (!jwt_from_request()) { http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit; }

$cols      = max(2,   (int)($_POST['cols']      ?? 4));
$pungpanH  = max(0,   (int)($_POST['pungpanH']  ?? 0));
$pungpanOn = (($_POST['pungpanOn'] ?? '0') === '1');
$frameW    = max(20,  (int)($_POST['frameW']    ?? 60));
$frameH    = max(20,  (int)($_POST['frameH']    ?? 60));
$slatT     = max(8,   (int)($_POST['slatT']     ?? 12));
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

// 내경 가로
$innerW = $outerW - 2 * $frameW;

// 가로 균등 분할
$cellW = $cols > 0 ? ($innerW - $slatT * ($cols - 1)) / $cols : 0;
$stepW = $cellW + $slatT;

// 세로: 사용 가능 높이
$availH = $outerH - 2 * $frameH - $effectivePungpanInput;
$rows   = $stepW > 0 ? max(1, (int)(($availH + $slatT) / $stepW)) : 1;

// 정사각형 셀
$cellH   = $cellW;
$innerH  = $rows * $cellW + ($rows - 1) * $slatT;

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
$diagEye  = number_format(($step * sqrt(2)) - ($slatT * 2), 1);
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
    'pungpan'   => (string)round($effectivePungpanH),
    'eye'       => number_format($cellW, 1),
    'diagEye'   => $diagEye,
    'frameHTop' => (string)round($frameHTop),
    'totalDoorW'=> (string)round($totalDoorWidth),
    'overlap'   => $doorType === 'slide' ? (string)round($overlap) : '0',
];

$pungpanVisible = $pungpanOn && $effectivePungpanH > 0;
$ppPanelH = $pungpanVisible ? ($effectivePungpanH - $frameH) : 0;

$hSlatCnt = max(0, $rows - 1);
$vSlatCnt = max(0, $cols - 1);

// 사선살 계산
$diagList = [];
$diagN = min($rows, $cols);
for ($k = $diagN; $k >= 1; $k--) {
    $diagLen = number_format((($k - 1) * $stepW + $cellW) * sqrt(2), 1);
    if ($k === $diagN) {
        $diagCnt = ($rows === $cols) ? 2 : 2 * (abs($rows - $cols) + 1);
    } else {
        $diagCnt = 4;
    }
    $diagList[] = ['len' => $diagLen, 'cnt' => $diagCnt * $doorCount];
}

// 목재 재수 계산 (1재 = 33×33×3600mm³, 부재별 실제 단면 사용)
$_d   = get_engine_part_dims('diamond');
$_es  = get_engine_settings('diamond');
$_JAE = 33 * 33 * 3600;
$_wU  = (int)($_es['ulgeomiW']             ?? $_d['울거미']['width_mm'] ?? 33);
$_wS  = (int)($_es['slatW']               ?? $_d['살']['width_mm']    ?? 20);
$_pT  = (int)($_es['pungpanT']            ?? 15);
$_tMt = $frameThick;
$_wMt = (int)($_es['muntolW'] ?? $_d['문틀']['width_mm'] ?? 33);

$_volDoor   = round($outerH + 2*$slatT) * (2*$doorCount)                * $frameW * $_wU
            + round($outerW + 2*$slatT) * (($pungpanOn?3:2)*$doorCount) * $frameH * $_wU
            + round($innerW + 2*$tenonDepth) * ($hSlatCnt*$doorCount)   * $slatT  * $_wS
            + round($innerH + 2*$tenonDepth) * ($vSlatCnt*$doorCount)   * $slatT  * $_wS
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
    'hSlatLen'       => (string)round($innerW + 2 * $tenonDepth),
    'hSlatCnt'       => ($hSlatCnt * $doorCount) . '개',
    'vSlatLen'       => (string)round($innerH + 2 * $tenonDepth),
    'vSlatCnt'       => ($vSlatCnt * $doorCount) . '개',
    'diagList'       => $diagList,
    'frT'            => $_wU,
    'slatW'          => $_wS,
    'pungpanT'       => $_pT,
    'mtVLen'         => (int)$frameOpeningH,
    'mtHLen'         => (int)$frameOpeningW,
    'mtW'            => $_wMt,
    'mtT'            => $_tMt,
    'woodVolMm3'     => (int)$_vol,
    'woodJae'        => round($_woodJae, 2),
    'woodJae_door'   => round($_volDoor / $_JAE, 2),
    'woodJae_muntol' => round($_volMuntol / $_JAE, 2),
    'techWeight'     => (float)($_d['기술난이도']['weight'] ?? 1.0),
];

echo json_encode(['geo' => $geo, 'specs' => $specs, 'parts' => $parts]);
