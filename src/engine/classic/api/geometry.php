<?php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../../lib/jwt.php';
if (!jwt_from_request()) { http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit; }

$cols       = max(2,   (int)($_POST['cols']      ?? 12));
$outerW     = max(400, (int)($_POST['outerW']    ?? 600));
$outerH     = max(400, (int)($_POST['outerH']    ?? 1707));
$pungpanH   = max(0,   (int)($_POST['pungpanH']  ?? 0));
$pungpanOn  = (($_POST['pungpanOn'] ?? '0') === '1');
$frameW     = max(20,  (int)($_POST['frameW']    ?? 60));
$frameH     = max(20,  (int)($_POST['frameH']    ?? 60));
$slatT      = max(8,   (int)($_POST['slatT']     ?? 12));
$vRatio     = max(1.0, min(3.0, (float)($_POST['vRatio']  ?? 1.2)));
$patternRaw = $_POST['pattern'] ?? '3/5/3';
$doorType   = in_array($_POST['doorType'] ?? '', ['swing','slide']) ? $_POST['doorType'] : 'swing';
$doorCount  = max(1, min(4, (int)($_POST['doorCount'] ?? 1)));

$effectivePungpanInput = $pungpanOn ? $pungpanH : 0;

$innerW = $outerW - 2 * $frameW;

// cellH = cellW x vRatio
$cellW = $cols > 0 ? ($innerW - $slatT * ($cols - 1)) / $cols : 0;
$cellH = $cellW * $vRatio;
$stepW = $cellW + $slatT;

// innerH fills the full available height
$innerH = max(0, $outerH - 2 * $frameH - $effectivePungpanInput);

$actualPatternH    = $frameH + $innerH + $frameH;
$surplus           = ($outerH - $effectivePungpanInput) - $actualPatternH;
$effectivePungpanH = $pungpanOn ? ($effectivePungpanInput + $surplus) : 0;
$actualPungpanH    = $effectivePungpanH;

$frameHTop    = $frameH;
$frameHBottom = $frameH;

$t = (float)$slatT;

// 패턴 파싱 (상/중/하 가로살 수)
$pp      = array_map('intval', explode('/', $patternRaw));
$topBars = max(1, $pp[0] ?? 3);
$midBars = max(1, $pp[1] ?? 5);
$botBars = max(1, $pp[2] ?? 3);

// 그룹 높이 (셀 수 = 살 수 + 1)
$topGroupH = ($topBars + 1) * $cellH + $topBars * $t;
$midGroupH = ($midBars + 1) * $cellH + $midBars * $t;
$botGroupH = ($botBars + 1) * $cellH + $botBars * $t;

// 앵커 위치 (내경 기준 y=0 = 상단 울거미 하단)
$topGroupY0 = 0.0;
$midGroupY0 = ((float)$innerH - $midGroupH) / 2.0;

// 가로살 중심 좌표 (공식: 그룹시작 + k*cellH + (k-0.5)*slatT)
$hBarYs = [];

for ($k = 1; $k <= $topBars; $k++) {
    $hBarYs[] = round($topGroupY0 + $k * $cellH + ($k - 0.5) * $t, 2);
}
for ($k = 1; $k <= $midBars; $k++) {
    $hBarYs[] = round($midGroupY0 + $k * $cellH + ($k - 0.5) * $t, 2);
}
for ($k = $botBars; $k >= 1; $k--) {
    $hBarYs[] = round((float)$innerH - $k * $cellH - ($k - 0.5) * $t, 2);
}

$hSlatCnt = $topBars + $midBars + $botBars;

// 세로살 구간 경계 (정렬 후 계산, 겹침 방지)
$sortedBars = $hBarYs;
sort($sortedBars);
$vSegBounds = [];
$prev = 0.0;
foreach ($sortedBars as $by) {
    $botEdge = $by + $t / 2.0;
    if ($botEdge > $prev + 0.5) {
        $vSegBounds[] = ['y0' => round($prev, 2), 'y1' => round($botEdge, 2)];
        $prev = $botEdge;
    }
}
if ((float)$innerH > $prev + 0.5) {
    $vSegBounds[] = ['y0' => round($prev, 2), 'y1' => (float)$innerH];
}

$step       = $stepW;
$stepH      = $cellH + $t;
$rowsInt    = count($vSegBounds);
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
    'cellSize'          => $cellW,
    'cellW'             => $cellW,
    'cellH'             => $cellH,
    'outerW'            => $outerW,
    'outerH'            => $outerH,
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
    'rows'              => $topBars + $midBars + $botBars + 3,
    'rowsInt'           => $rowsInt,
    'innerW'            => $innerW,
    'innerH'            => $innerH,
    'actualPatternH'    => $actualPatternH,
    'actualPungpanH'    => $actualPungpanH,
    'effectivePungpanH' => $effectivePungpanH,
    'tenonDepth'        => $tenonDepth,
    'totalDoorWidth'    => $totalDoorWidth,
    'hBarYs'            => $hBarYs,
    'vSegBounds'        => $vSegBounds,
];

$specs = [
    'outerW'    => (string)round($outerW),
    'outerH'    => (string)round($outerH),
    'innerW'    => (string)round($innerW),
    'innerH'    => (string)round($innerH),
    'cols'      => (string)$cols,
    'rows'      => "{$topBars}+{$midBars}+{$botBars}",
    'step'      => number_format($step, 1),
    'pungpan'   => (string)round($effectivePungpanH),
    'eye'       => number_format($cellW, 1),
    'frameHTop' => (string)round($frameHTop),
    'totalDoorW'=> (string)round($totalDoorWidth),
    'overlap'   => $doorType === 'slide' ? (string)round($overlap) : '0',
];

$pungpanVisible = $pungpanOn && $effectivePungpanH > 0;
$ppPanelH = $pungpanVisible ? ($effectivePungpanH - $frameH) : 0;

$vSlatCnt = max(0, $cols - 1);

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
];

echo json_encode(['geo' => $geo, 'specs' => $specs, 'parts' => $parts]);
