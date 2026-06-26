<?php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../../lib/jwt.php';
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

// 목재 재수 계산 (1재 = 33×33×3600mm, 단면 고정)
$_frVLen  = round($outerH + 2 * $slatT); $_frVCnt_n = 2 * $doorCount;
$_frHLen  = round($outerW + 2 * $slatT); $_frHCnt_n = ($pungpanOn ? 3 : 2) * $doorCount;
$_hSLen   = round($innerW + 2 * $tenonDepth); $_hSCnt_n = $hSlatCnt * $doorCount;
$_vSLen   = round($innerH + 2 * $tenonDepth); $_vSCnt_n = $vSlatCnt * $doorCount;
$_diagMm  = array_sum(array_map(fn($d) => (float)$d['len'] * $d['cnt'], $diagList));
$_totalMm = $_frVLen * $_frVCnt_n + $_frHLen * $_frHCnt_n + $_hSLen * $_hSCnt_n + $_vSLen * $_vSCnt_n + $_diagMm;
$_woodJae = $_totalMm / 3600.0;

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
    'woodTotalMm'    => (int)$_totalMm,
    'woodJae'        => round($_woodJae, 2),
];

echo json_encode(['geo' => $geo, 'specs' => $specs, 'parts' => $parts]);
