<?php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../../lib/jwt.php';
if (!jwt_from_request()) { http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit; }

$cols      = max(2,   (int)($_POST['cols']      ?? 4));
$outerW    = max(400, (int)($_POST['outerW']    ?? 600));
$outerH    = max(400, (int)($_POST['outerH']    ?? 1707));
$pungpanH  = max(0,   (int)($_POST['pungpanH']  ?? 0));
$pungpanOn = (($_POST['pungpanOn'] ?? '0') === '1');
$frameW    = max(20,  (int)($_POST['frameW']    ?? 60));
$frameH    = max(20,  (int)($_POST['frameH']    ?? 60));
$slatT     = max(8,   (int)($_POST['slatT']     ?? 12));
$rotateOn  = (($_POST['rotateOn']  ?? '0') === '1');
$doorType  = in_array($_POST['doorType'] ?? '', ['swing','slide']) ? $_POST['doorType'] : 'swing';
$doorCount = max(1, min(4, (int)($_POST['doorCount'] ?? 1)));

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
$diagEye     = number_format($rotateOn ? ($colStepR - $slatT) : ($stepX - $slatT), 1);
$tenonDepth  = $slatT;
$actualPatternH = $frameHTop + $innerH + $frameHBottom;

$overlap = $frameWActual;
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
    'stepX'             => $stepX,
    'rowH'              => $rowH,
    'outerW'            => $outerW,
    'outerH'            => $outerH,
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

$specs = [
    'outerW'    => (string)round($outerW),
    'outerH'    => (string)round($outerH),
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

// ── 육모살 단살 길이·수량 계산 ─────────────────────
// 각 육각형 변 하나의 길이 = 외접원반지름(size)
$hexSize = $rotateOn
    ? (($innerW + $slatT) / max(1, $cols * 1.5))
    : ($innerW / max(1, $cols * $SQRT3));

$hexEdgeLen = (int)round($hexSize + 2 * $slatT);

// 격자 내 육각형 수 추정 후 변 수 계산 (육각형 1개당 고유 변 ≈ 3개)
$numHex  = max(1, $cols) * max(1, $rows);
$numEdge = (int)round($numHex * 3) * $doorCount;

$parts = [
    'frVLen'         => (string)round($outerH + 2 * $slatT),
    'frVCnt'         => (2 * $doorCount) . '개',
    'frHLen'         => (string)round($outerW + 2 * $slatT),
    'frHCnt'         => (($pungpanOn ? 3 : 2) * $doorCount) . '개',
    'pungpanVisible' => $pungpanVisible,
    'pungpanCnt'     => $doorCount . '개',
    'ppHLen'         => (string)round($innerW + 2 * $slatT),
    'ppVLen'         => (string)round($ppPanelH + 2 * $slatT),
    'dirTitle'       => '육모살',
    'hSlatLen'       => (string)$hexEdgeLen,
    'hSlatCnt'       => $numEdge . '개',
    'diagList'       => [],
];

echo json_encode(['geo' => $geo, 'specs' => $specs, 'parts' => $parts]);
