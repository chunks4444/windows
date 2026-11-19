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
$slatT     = max(4.5, (float)($_POST['slatT']   ?? 12));
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

// 문 높이(outerH)·문 종류로 울거미 두께·나비 단계값을 찾아 최소치로 강제한다 — "최소치만 막기"/"덮어쓰기"
// 정책 차이는 클라이언트가 슬라이더를 어떻게 보여주느냐일 뿐, 서버는 정책과 무관하게 이 하한을 항상 지킨다.
$frameSizeStep = get_frame_size_step($doorType, (int)round($outerH));
if ($frameSizeStep) {
    $frameW = max($frameW, (int)$frameSizeStep['frame_width']);
    $frameH = max($frameH, (int)$frameSizeStep['frame_width']);
}

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
// frameHTop·frameHBottom은 수식상 항상 같은 값(둘 다 surplus를 반씩 받거나, 둘 다 안 받거나) —
// 세로 자동 맞춤이 꺼져 있을 때 칸이 안 맞아떨어져 남는 높이를 상하 울거미가 함께 흡수한 실제 두께.
// 부재목록·목재량 계산은 입력값 frameH가 아니라 이 실제 두께를 써야 한다(안 그러면 치수가 안 맞음).

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

// ── 편집(지운 살 조각·추가선) 반영 ──────────────────────────────────────────
// 화면 편집기는 살을 칸 단위 조각(세로 `d:vs:i:j`, 가로 `d:hs:j:i`)으로 쪼개 두고, 지운 조각 키(deletedSegs)와
// 격자 노드 인덱스로 이은 추가선(addedLines)을 보낸다. 조각 모델이 결정적이라 서버가 같은 규칙으로
// 연속 구간(run)을 다시 묶어 실제 부재 개수·길이를 계산한다(브라우저 값은 믿지 않고 범위만 검증).
// 길이 규칙: 지운 조각과 맞닿은 끝은 교차 정사각형(살 두께)까지 마무리, 울거미에 닿은 끝에만 촉(tenonDepth)을 더한다.
$_delRaw = ((string)($_POST['deletedSegs'] ?? '') !== '' && strlen((string)$_POST['deletedSegs']) <= 20000) ? json_decode((string)$_POST['deletedSegs'], true) : [];
$_addRaw = ((string)($_POST['addedLines']  ?? '') !== '' && strlen((string)$_POST['addedLines'])  <= 20000) ? json_decode((string)$_POST['addedLines'],  true) : [];
$_delSet = [];   // [door][kind] => ["a:b" => true]
if (is_array($_delRaw)) {
    foreach ($_delRaw as $k) {
        if (!is_string($k) || !preg_match('/^(\d+):(vs|hs):(\d+):(\d+)$/', $k, $m)) continue;
        $dd = (int)$m[1]; $a = (int)$m[3]; $b = (int)$m[4];
        if ($dd >= $doorCount) continue;
        if ($m[2] === 'vs' && ($a < 1 || $a >= $cols || $b >= $rows)) continue;
        if ($m[2] === 'hs' && ($a < 1 || $a >= $rows || $b >= $cols)) continue;
        $_delSet[$dd][$m[2]]["$a:$b"] = true;
    }
}
$_nodeX = [0.0]; for ($q = 1; $q < $cols; $q++) $_nodeX[] = $q * $stepW - $slatT / 2; $_nodeX[] = $innerW;
$_nodeY = [0.0]; for ($q = 1; $q < $rows; $q++) $_nodeY[] = $q * $stepH - $slatT / 2; $_nodeY[] = $innerH;
$_addLines = [];
if (is_array($_addRaw)) {
    foreach (array_slice($_addRaw, 0, 200) as $ln) {
        if (!is_array($ln) || !isset($ln['xi1'], $ln['yi1'], $ln['xi2'], $ln['yi2'])) continue;
        [$x1, $y1, $x2, $y2] = [(int)$ln['xi1'], (int)$ln['yi1'], (int)$ln['xi2'], (int)$ln['yi2']];
        if ($x1 < 0 || $x2 < 0 || $x1 > $cols || $x2 > $cols || $y1 < 0 || $y2 < 0 || $y1 > $rows || $y2 > $rows) continue;
        if ($x1 === $x2 && $y1 === $y2) continue;
        $len = hypot($_nodeX[$x2] - $_nodeX[$x1], $_nodeY[$y2] - $_nodeY[$y1]);
        $onFrame = fn($xi, $yi) => ($xi === 0 || $xi === $cols || $yi === 0 || $yi === $rows) ? $tenonDepth : 0;
        $_addLines[] = ['len' => $len + $onFrame($x1, $y1) + $onFrame($x2, $y2), 'horiz' => $y1 === $y2];
    }
}
$_hLens = []; $_vLens = []; $_lostJoints = 0;
for ($dd = 0; $dd < $doorCount; $dd++) {
    $dv = $_delSet[$dd]['vs'] ?? []; $dh = $_delSet[$dd]['hs'] ?? [];
    for ($i = 1; $i < $cols; $i++) {
        $j0 = null;
        for ($j = 0; $j <= $rows; $j++) {
            $alive = $j < $rows && !isset($dv["$i:$j"]);
            if ($alive && $j0 === null) $j0 = $j;
            if (!$alive && $j0 !== null) {
                $j1 = $j - 1;
                $y0 = $j0 === 0 ? 0 : $j0 * $stepH - $slatT;
                $y1 = $j1 === $rows - 1 ? $innerH : ($j1 + 1) * $stepH;
                $_vLens[] = (int)round($y1 - $y0 + $tenonDepth * (($j0 === 0 ? 1 : 0) + ($j1 === $rows - 1 ? 1 : 0)));
                $j0 = null;
            }
        }
    }
    for ($j = 1; $j < $rows; $j++) {
        $i0 = null;
        for ($i = 0; $i <= $cols; $i++) {
            $alive = $i < $cols && !isset($dh["$j:$i"]);
            if ($alive && $i0 === null) $i0 = $i;
            if (!$alive && $i0 !== null) {
                $i1 = $i - 1;
                $x0 = $i0 === 0 ? 0 : $i0 * $stepW - $slatT;
                $x1 = $i1 === $cols - 1 ? $innerW : ($i1 + 1) * $stepW;
                $_hLens[] = (int)round($x1 - $x0 + $tenonDepth * (($i0 === 0 ? 1 : 0) + ($i1 === $cols - 1 ? 1 : 0)));
                $i0 = null;
            }
        }
    }
    // 짜임(교차) 손실: 세로살·가로살이 둘 다 그 교점을 지나야 짜임이 선다
    for ($i = 1; $i < $cols; $i++) for ($j = 1; $j < $rows; $j++) {
        $vPass = !isset($dv["$i:" . ($j - 1)]) || !isset($dv["$i:$j"]);
        $hPass = !isset($dh["$j:" . ($i - 1)]) || !isset($dh["$j:$i"]);
        if (!$vPass || !$hPass) $_lostJoints++;
    }
}
foreach ($_addLines as $al) {
    for ($dd = 0; $dd < $doorCount; $dd++) { if ($al['horiz']) $_hLens[] = (int)round($al['len']); else $_vLens[] = (int)round($al['len']); }
}
$_edited   = !empty($_delSet) || !empty($_addLines);
$hSlatCntAll = count($_hLens);
$vSlatCntAll = count($_vLens);
$_hSumLen = array_sum($_hLens);
$_vSumLen = array_sum($_vLens);
$_jointsAdj = max(0, $cols * $rows - (int)round($_lostJoints / max(1, $doorCount)) + 2 * count($_addLines));
$_groupLens = function (array $a): array { $g = array_count_values($a); krsort($g); $o = []; foreach ($g as $l => $c) $o[] = ['len' => (int)$l, 'cnt' => (int)round($c)]; return $o; };

// 목재 재수 계산 (1재 = 30.3×30.3×3636mm³, 부재별 실제 단면 사용)
$_es  = get_engine_settings('square');
$_JAE = 30.3 * 30.3 * 3636;
$_wU  = (int)($frameSizeStep['frame_thickness'] ?? $_es['ulgeomiW'] ?? $_d['울거미']['width_mm'] ?? 33);
$_wS  = (int)($_es['slatW']               ?? $_d['살']['width_mm']    ?? 20);
$_pT  = (int)($_es['pungpanT']            ?? 15);
$_tMt = (int)($_es['muntolT'] ?? 30);
$_wMt = (int)$_es['muntolW'];

$_volDoor   = round($outerH + 2*$slatT) * (2*$doorCount)                * $frameW * $_wU
            + round($outerW + 2*$slatT) * (($pungpanOn?3:2)*$doorCount) * $frameHTop * $_wU
            + $_hSumLen * $slatT * $_wS
            + $_vSumLen * $slatT * $_wS
            + ($pungpanVisible ? (int)round($innerW) * (int)round($ppPanelH) * $_pT : 0);
$_volMuntol = $frameOpeningH * 2 * $_tMt * $_wMt
            + $frameOpeningW * 2 * $_tMt * $_wMt;
$_vol       = $_volDoor + $_volMuntol;
$_woodJae   = $_vol / $_JAE;

// 부재별 표면적·체적·무게 (문짝 1짝 기준 — 문틀은 문짝 수와 무관하게 1개) — 표면적은 각목 긴 옆면 4면
// 근사(2×(폭+두께)×길이), 양끝 단면은 무시한다(도장 면적 계산과 같은 기준). 풍판은 울거미에 합산.
$_ulV  = round($outerH + 2*$slatT) * 2                  * $frameW * $_wU;
$_ulH  = round($outerW + 2*$slatT) * ($pungpanOn ? 3 : 2) * $frameHTop * $_wU;
$_ulVA = 2 * ($frameW + $_wU) * round($outerH + 2*$slatT) * 2;
$_ulHA = 2 * ($frameHTop + $_wU) * round($outerW + 2*$slatT) * ($pungpanOn ? 3 : 2);
$_ppV  = $pungpanVisible ? (int)round($innerW) * (int)round($ppPanelH) * $_pT : 0;
$_ppA  = $pungpanVisible ? 2 * (int)round($innerW) * (int)round($ppPanelH) : 0;
$_slH  = $_hSumLen / $doorCount * $slatT * $_wS;
$_slV  = $_vSumLen / $doorCount * $slatT * $_wS;
$_slHA = 2 * ($_wS + $slatT) * $_hSumLen / $doorCount;
$_slVA = 2 * ($_wS + $slatT) * $_vSumLen / $doorCount;
$_mtV  = $frameOpeningH * 2 * $_tMt * $_wMt;
$_mtH  = $frameOpeningW * 2 * $_tMt * $_wMt;
$_mtVA = 2 * ($_wMt + $_tMt) * $frameOpeningH * 2;
$_mtHA = 2 * ($_wMt + $_tMt) * $frameOpeningW * 2;

$_ulgeomiVolDoor  = $_ulV + $_ulH + $_ppV;
$_ulgeomiAreaDoor = $_ulVA + $_ulHA + $_ppA;
$_slatVolDoor     = $_slH + $_slV;
$_slatAreaDoor    = $_slHA + $_slVA;
$_muntolVol       = $_mtV + $_mtH;
$_muntolArea      = $_mtVA + $_mtHA;

$_woodDensity = (float)(get_cost_table_item('wood', (string)($_POST['wood'] ?? ''))['density'] ?? 0);
// mm³ → cm³(÷1000) × 비중(g/cm³) = g, ÷1000 = kg
$_toKg = fn(float $mm3): ?float => $_woodDensity > 0 ? round($mm3 / 1_000_000 * $_woodDensity, 3) : null;

$physical = [
    'density'   => $_woodDensity ?: null,
    'doorCount' => $doorCount,
    'ulgeomi'   => ['volMm3' => round($_ulgeomiVolDoor), 'areaM2' => round($_ulgeomiAreaDoor / 1_000_000, 3), 'weightKg' => $_toKg($_ulgeomiVolDoor)],
    'slat'      => ['volMm3' => round($_slatVolDoor),    'areaM2' => round($_slatAreaDoor / 1_000_000, 3),    'weightKg' => $_toKg($_slatVolDoor)],
    'muntol'    => ['volMm3' => round($_muntolVol),      'areaM2' => round($_muntolArea / 1_000_000, 3),      'weightKg' => $_toKg($_muntolVol)],
];

$parts = [
    'frVLen'         => (string)round($outerH + 2 * $slatT),
    'frVCnt'         => (2 * $doorCount) . '개',
    'frHLen'         => (string)round($outerW + 2 * $slatT),
    'frHCnt'         => (($pungpanOn ? 3 : 2) * $doorCount) . '개',
    'pungpanVisible' => $pungpanVisible,
    'pungpanCnt'     => $doorCount . '개',
    'ppHLen'         => (string)round($innerW + 2 * $slatT),
    'ppVLen'         => (string)round($ppPanelH + 2 * $slatT),
    'hSlatLen'       => (string)($_hLens ? max($_hLens) : round($innerW + 2 * $tenonDepth)),
    'hSlatCnt'       => $hSlatCntAll . '개',
    'vSlatLen'       => (string)($_vLens ? max($_vLens) : round($innerH + 2 * $tenonDepth)),
    'vSlatCnt'       => $vSlatCntAll . '개',
    'slatPieces'     => ['edited' => $_edited, 'h' => $_groupLens($_hLens), 'v' => $_groupLens($_vLens), 'perDoor' => $doorCount],
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
    'joints'         => $_jointsAdj,
    'physical'       => $perms['physical'] ? $physical : null,
];

$selection = [
    'wood'      => (string)($_POST['wood']     ?? ''),
    'hardware'  => (string)($_POST['hardware'] ?? ''),
    'finish'    => (string)($_POST['finish']   ?? ''),
    'muntolOn'  => (($_POST['muntolOn'] ?? '1') === '1'),
    'doorCount' => $doorCount,
];
$_costCfg = get_cost_config('square');
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
    'frameSizeStep'   => $frameSizeStep,
    'frameSizeSteps'  => get_frame_size_steps_all(),
    'frameSizePolicy' => $_es['frameSizePolicy'] ?? 'min_only',
]);
