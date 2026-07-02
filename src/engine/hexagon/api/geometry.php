<?php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../../lib/jwt.php';
require_once __DIR__ . '/../../../lib/engine_settings.php';
if (!jwt_from_request()) { http_response_code(401); echo json_encode(['error' => '인증이 필요합니다.']); exit; }

$cols      = max(1,   (int)($_POST['cols']      ?? 3));
$pungpanH  = max(0,   (int)($_POST['pungpanH']  ?? 0));
$pungpanOn = (($_POST['pungpanOn'] ?? '0') === '1');
$frameW    = max(20,  (int)($_POST['frameW']    ?? 60));
$frameH    = max(20,  (int)($_POST['frameH']    ?? 60));
$slatT     = max(8,   (int)($_POST['slatT']     ?? 12));
$rotateOn  = (($_POST['rotateOn']  ?? '0') === '1');
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
    // 세로살이 좌우 울거미 안쪽면과 겹쳐 그려지던 문제 수정: 스퀘어/클래식처럼 칸 사이에
    // 살두께를 미리 빼놓고, colStepR(중심-중심)는 거기에 slatT를 더해 되돌린다.
    $cellWHexR = $cols > 0 ? ($innerW - $slatT * ($cols - 1)) / $cols : 0;
    $colStepR  = $cellWHexR + $slatT;
    $rowHR     = $cellWHexR > 0 ? $cellWHexR * 2 / $SQRT3 : 0;
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
    // 육모살은 세모솟을살과 같은 60° 기하구조라 반턱/홈폭 공식을 그대로 재사용 (사용자 확인, 2026-07-02)
    'halfLapW'  => number_format($slatT * 2 / $SQRT3, 1),
    'grooveW'   => number_format($slatT * $SQRT3, 1),
    'grooveWH'  => number_format($slatT, 1),
    // 세로 울거미에 홈을 파야 하는 세로 방향 간격(피치)
    'grooveGapV' => number_format($rowHR, 1),
];

$pungpanVisible = $pungpanOn && $effectivePungpanH > 0;
$ppPanelH = $pungpanVisible ? ($effectivePungpanH - $frameH) : 0;

// ── 육모살 세로살·사선살 수량 계산 ──────────────────────────
// JS hexagon.js: rotateOn=true 고정 (pointy-top), size = (iW-slatT*(cols-1))/cols/√3, bStep = 2*size
// colStepR와 동일하게 칸 사이 살두께를 미리 빼놓은 순수 크기 사용 (경계 울거미 겹침 버그 수정)
$hexSize = $cols > 0 ? ((($innerW - $slatT * ($cols - 1)) / $cols) / $SQRT3) : 0;

function calcGroupsHexDR(float $iW, float $iH, float $hexSize, float $sT): array {
    $S3    = sqrt(3);
    $bStep = 2.0 * $hexSize;
    $phase = -$hexSize / 2.0;
    $g     = [];
    $k0    = (int)floor((-$iW / $S3 - $phase) / $bStep);
    for ($k = $k0; ; $k++) {
        $y0 = $phase + $k * $bStep;
        if ($y0 > $iH + 0.5) break;
        $x1 = $y0 < 0.0 ? -$y0 * $S3 : 0.0;
        $y1 = $y0 < 0.0 ? 0.0         : $y0;
        $yR = $y0 + $iW / $S3;
        if ($yR <= $iH + 0.5) { $x2 = $iW; $y2 = $yR; }
        else                   { $x2 = ($iH - $y0) * $S3; $y2 = $iH; }
        if ($x1 > $iW + 0.5 || $x2 < -0.5 || $x1 > $x2 + 0.5) continue;
        $seg = sqrt(($x2 - $x1) ** 2 + ($y2 - $y1) ** 2);
        if ($seg < 0.5) continue;
        $k2 = (string)(int)round($seg + 2 * $sT);
        $g[$k2] = ($g[$k2] ?? 0) + 1;
    }
    return $g;
}

function calcGroupsHexUR(float $iW, float $iH, float $hexSize, float $sT): array {
    $S3    = sqrt(3);
    $bStep = 2.0 * $hexSize;
    $phase = $hexSize / 2.0;
    $g     = [];
    $k0    = (int)floor(-$phase / $bStep);
    for ($k = $k0; ; $k++) {
        $y0 = $phase + $k * $bStep;
        if ($y0 <= 0.5) continue;
        if ($y0 - $iW / $S3 >= $iH - 0.5) break;
        $x1 = $y0 >= $iH - 0.5 ? ($y0 - $iH) * $S3 : 0.0;
        $y1 = $y0 >= $iH - 0.5 ? $iH              : $y0;
        $yAtRight = $y0 - $iW / $S3;
        if ($yAtRight >= 0.5) { $x2 = $iW; $y2 = $yAtRight; }
        else                   { $x2 = $y0 * $S3; $y2 = 0.0; }
        if ($x1 > $iW + 0.5 || $x2 < -0.5) continue;
        $seg = sqrt(($x2 - $x1) ** 2 + ($y2 - $y1) ** 2);
        if ($seg < 0.5) continue;
        $k2 = (string)(int)round($seg + 2 * $sT);
        $g[$k2] = ($g[$k2] ?? 0) + 1;
    }
    return $g;
}

$rawGroups = [];
foreach ([
    calcGroupsHexDR((float)$innerW, (float)$innerH, (float)$hexSize, (float)$slatT),
    calcGroupsHexUR((float)$innerW, (float)$innerH, (float)$hexSize, (float)$slatT),
] as $gMap) {
    foreach ($gMap as $len => $cnt) {
        $rawGroups[$len] = ($rawGroups[$len] ?? 0) + $cnt;
    }
}

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
$diagList = array_map(function($item) use ($doorCount) {
    return ['len' => $item['len'], 'cnt' => $item['cnt'] * $doorCount];
}, $merged);

// 목재 재수 계산 (1재 = 33×33×3600mm³, 부재별 실제 단면 사용)
$_es  = get_engine_settings('hexagon');
$_JAE = 33 * 33 * 3600;
$_wU  = (int)($_es['ulgeomiW']             ?? $_d['울거미']['width_mm'] ?? 33);
$_wS  = (int)($_es['slatW']               ?? $_d['살']['width_mm']    ?? 20);
$_pT  = (int)($_es['pungpanT']            ?? 15);
$_tMt = (int)($_es['muntolT'] ?? 30);
$_wMt = (int)$_es['muntolW'];

$_volDoor   = round($outerH + 2*$slatT) * (2*$doorCount)                    * $frameW * $_wU
            + round($outerW + 2*$slatT) * (($pungpanOn?3:2)*$doorCount)     * $frameH * $_wU
            + round($innerH + 2*$tenonDepth) * (max(0,$cols-1)*$doorCount)  * $slatT  * $_wS
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
    'dirTitle'       => '세로부재',
    'hSlatLen'       => (string)round($innerH + 2 * $tenonDepth),
    'hSlatCnt'       => (max(0, $cols - 1) * $doorCount) . '개',
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

echo json_encode(['geo' => $geo, 'specs' => $specs, 'parts' => $parts]);
