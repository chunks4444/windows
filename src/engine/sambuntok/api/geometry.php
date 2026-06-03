<?php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json; charset=UTF-8');

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
                            ? ($cols * $doorCount) . '개'
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

$MERGE_TOL = 2 * $slatT;
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

echo json_encode(['geo' => $geo, 'specs' => $specs, 'parts' => $parts]);
