<?php
// 화면 편집기(살 조각 삭제·선 추가)를 원가·부재 목록에 반영하기 위한 서버 계산 도우미 — 클래식·다이아몬드용.
// (정자살은 square/api/geometry.php 안에 같은 규칙이 직접 들어 있다.)
//
// 화면은 살을 칸 단위 조각(세로 `d:vs:i:j`, 가로 `d:hs:j:i`, 다이아몬드 사선 `d:bs|fs:row:col:half`)으로 쪼개 그리고,
// 지운 조각 키(deletedSegs)와 격자 노드 인덱스로 이은 추가선(addedLines)만 서버로 보낸다. 조각 모델이 결정적이라
// 서버가 같은 규칙으로 연속 구간(run)을 다시 묶어 실제 개수·길이를 계산한다 — 브라우저가 보낸 숫자는 믿지 않는다.
// 길이 규칙: 지운 조각과 맞닿은 끝은 교차 정사각형(살 두께)까지 마무리하고, 울거미에 닿은 끝에만 촉(tenonDepth)을 더한다.

// POST의 deletedSegs / addedLines(JSON 문자열)를 안전하게 풀어낸다. 형식·크기가 어긋나면 빈 값.
function slat_edit_read_post(): array {
    $dr = (string)($_POST['deletedSegs'] ?? '');
    $ar = (string)($_POST['addedLines']  ?? '');
    $d = ($dr !== '' && strlen($dr) <= 40000) ? json_decode($dr, true) : [];
    $a = ($ar !== '' && strlen($ar) <= 20000) ? json_decode($ar, true) : [];
    return [is_array($d) ? array_slice($d, 0, 4000) : [], is_array($a) ? array_slice($a, 0, 200) : []];
}

// 삭제 키 → [문짝][종류]["a:b[:c]"] = true. 문짝 번호가 범위를 벗어나거나 형식이 틀린 키는 버린다.
function slat_edit_index_deleted(array $keys, int $doorCount): array {
    $set = [];
    foreach ($keys as $k) {
        if (!is_string($k)) continue;
        // 십자: `d:bsa|bsb|fsa|fsb:row:col` (a=앞 반쪽, b=뒤 반쪽) → 다이아몬드와 같은 `bs|fs:row:col:half` 형식으로 통일
        if (preg_match('/^(\d+):(bs|fs)([ab]):(\d+):(\d+)$/', $k, $mm)) {
            $m = [$k, $mm[1], $mm[2], $mm[4], $mm[5], $mm[3] === 'a' ? '0' : '1'];
        } elseif (!preg_match('/^(\d+):(vs|hs|bs|fs):(\d+):(\d+)(?::([01]))?$/', $k, $m)) {
            continue;
        }
        $dd = (int)$m[1];
        if ($dd >= $doorCount) continue;
        $rest = $m[3] . ':' . $m[4] . (isset($m[5]) && $m[5] !== '' ? ':' . $m[5] : '');
        $set[$dd][$m[2]][$rest] = true;
    }
    return $set;
}

// 세로살(i=1..cols-1)·가로살 조각을 연속 구간으로 묶어 길이 목록을 만든다.
//   $vb      : 세로살 조각 경계 [[y0,y1],…] (내경 위쪽=0 기준 mm, 마지막은 innerH로 잘린 값)
//   $hKeys   : 가로살 키 j 목록 (클래식 0..n-1, 다이아몬드 1..rows-1)
//   $hKeyOff : 세로살 조각 k와 k-1 사이 교점에 걸리는 가로살 키 = k - 1 + $hKeyOff (클래식 0, 다이아몬드 1)
// 반환: ['h'=>[길이…], 'v'=>[길이…], 'lost'=>잃은 짜임 수(문짝 합계)]
function slat_edit_hv_pieces(array $del, int $doorCount, int $cols, array $vb, array $hKeys, int $hKeyOff,
                             float $stepW, float $slatT, float $tenon, float $innerW, float $innerH): array {
    $H = []; $V = []; $lost = 0;
    $nv = count($vb);
    for ($d = 0; $d < $doorCount; $d++) {
        $dv = $del[$d]['vs'] ?? []; $dh = $del[$d]['hs'] ?? [];
        for ($i = 1; $i < $cols; $i++) {
            $j0 = null;
            for ($j = 0; $j <= $nv; $j++) {
                $alive = $j < $nv && !isset($dv["$i:$j"]);
                if ($alive && $j0 === null) $j0 = $j;
                if (!$alive && $j0 !== null) {
                    $j1 = $j - 1;
                    $y0 = $j0 === 0 ? 0.0 : $vb[$j0][0] - $slatT;
                    $y1 = $j1 === $nv - 1 ? $innerH : min($innerH, $vb[$j1][1]);
                    $V[] = (int)round($y1 - $y0 + $tenon * (($j0 === 0 ? 1 : 0) + ($j1 === $nv - 1 ? 1 : 0)));
                    $j0 = null;
                }
            }
        }
        foreach ($hKeys as $hj) {
            $i0 = null;
            for ($i = 0; $i <= $cols; $i++) {
                $alive = $i < $cols && !isset($dh["$hj:$i"]);
                if ($alive && $i0 === null) $i0 = $i;
                if (!$alive && $i0 !== null) {
                    $i1 = $i - 1;
                    $x0 = $i0 === 0 ? 0.0 : $i0 * $stepW - $slatT;
                    $x1 = $i1 === $cols - 1 ? $innerW : min($innerW, ($i1 + 1) * $stepW);
                    $H[] = (int)round($x1 - $x0 + $tenon * (($i0 === 0 ? 1 : 0) + ($i1 === $cols - 1 ? 1 : 0)));
                    $i0 = null;
                }
            }
        }
        // 짜임(교차) 손실: 세로살·가로살이 둘 다 그 교점을 지나야 짜임이 선다
        for ($k = 1; $k < $nv; $k++) {
            $hj = $k - 1 + $hKeyOff;
            if (!in_array($hj, $hKeys, true)) continue;
            for ($i = 1; $i < $cols; $i++) {
                $vPass = !isset($dv["$i:" . ($k - 1)]) || !isset($dv["$i:$k"]);
                $hPass = !isset($dh["$hj:" . ($i - 1)]) || !isset($dh["$hj:$i"]);
                if (!$vPass || !$hPass) $lost++;
            }
        }
    }
    return ['h' => $H, 'v' => $V, 'lost' => $lost];
}

// 다이아몬드 사선살: `\`(bs)·`/`(fs)는 셀마다 반쪽 2개로 쪼개져 있고, 같은 대각선의 조각들이 한 줄을 이룬다.
// 지운 반쪽이 있는 줄은 원래 한 줄(전체 길이)을 빼고 남은 연속 구간들을 새 부재로 넣는다.
// 반환: ['remove'=>[칸수k => 개수], 'add'=>[길이…]]  (문짝 합계)
function slat_edit_diag_pieces(array $del, int $doorCount, int $rows, int $cols, float $stepW, float $stepH, float $slatT, bool $clipAtFrame = true): array {
    $half = hypot($stepW, $stepH) / 2;
    // 울거미 안쪽 경계에서 잘리는 끝 하나당 줄어드는 길이 (다이아몬드만 — 십자는 잘림 없이 칸수×대각선으로 센다)
    $clip = $clipAtFrame ? ($slatT / 2) * M_SQRT2 : 0.0;
    $remove = []; $add = [];
    $onFrame = fn(int $r, int $c) => ($r === 0 || $r === $rows || $c === 0 || $c === $cols);
    for ($d = 0; $d < $doorCount; $d++) {
        foreach (['bs', 'fs'] as $kind) {
            $dk = $del[$d][$kind] ?? [];
            if (!$dk) continue;
            // 대각선 시작 셀들: bs는 (0,c)와 (r,0), fs는 (0,c)와 (r,cols-1)에서 출발해 한 칸씩 내려간다
            $starts = [];
            for ($c = 0; $c < $cols; $c++) $starts[] = [0, $c];
            for ($r = 1; $r < $rows; $r++) $starts[] = [$r, $kind === 'bs' ? 0 : $cols - 1];
            foreach ($starts as [$r0, $c0]) {
                $cells = [];
                for ($q = 0; ; $q++) {
                    $r = $r0 + $q; $c = $kind === 'bs' ? $c0 + $q : $c0 - $q;
                    if ($r >= $rows || $c < 0 || $c >= $cols) break;
                    $cells[] = [$r, $c];
                }
                $kCells = count($cells);
                if ($kCells === 0) continue;
                $n = 2 * $kCells;
                $any = false;
                for ($m = 0; $m < $n; $m++) {
                    [$r, $c] = $cells[intdiv($m, 2)];
                    if (isset($dk["$r:$c:" . ($m % 2)])) { $any = true; break; }
                }
                if (!$any) continue;
                $remove[$kCells] = ($remove[$kCells] ?? 0) + 1;
                $m0 = null;
                for ($m = 0; $m <= $n; $m++) {
                    $alive = false;
                    if ($m < $n) { [$r, $c] = $cells[intdiv($m, 2)]; $alive = !isset($dk["$r:$c:" . ($m % 2)]); }
                    if ($alive && $m0 === null) $m0 = $m;
                    if (!$alive && $m0 !== null) {
                        $m1 = $m - 1;
                        // 양 끝 노드가 울거미 경계(격자 모서리)인지: 짝수 반쪽의 시작은 셀 모서리, 홀수 반쪽의 끝은 셀 모서리
                        $ends = 0;
                        if ($m0 % 2 === 0) {
                            [$r, $c] = $cells[intdiv($m0, 2)];
                            if ($onFrame($r, $kind === 'bs' ? $c : $c + 1)) $ends++;
                        }
                        if ($m1 % 2 === 1) {
                            [$r, $c] = $cells[intdiv($m1, 2)];
                            if ($onFrame($r + 1, $kind === 'bs' ? $c + 1 : $c)) $ends++;
                        }
                        $add[] = (int)round(($m1 - $m0 + 1) * $half - $ends * $clip + 2 * $slatT);
                        $m0 = null;
                    }
                }
            }
        }
    }
    return ['remove' => $remove, 'add' => $add];
}

// 추가선(노드 인덱스) 길이. $nodeFn(xi,yi,ntype) → [x,y] mm. 범위 밖·길이 0인 선은 건너뛴다.
function slat_edit_added_lengths(array $added, callable $nodeFn, float $tenon, float $maxLen): array {
    $out = [];
    foreach ($added as $ln) {
        if (!is_array($ln) || !isset($ln['xi1'], $ln['yi1'], $ln['xi2'], $ln['yi2'])) continue;
        $p1 = $nodeFn((int)$ln['xi1'], (int)$ln['yi1'], (string)($ln['ntype1'] ?? 'corner'));
        $p2 = $nodeFn((int)$ln['xi2'], (int)$ln['yi2'], (string)($ln['ntype2'] ?? 'corner'));
        if (!$p1 || !$p2) continue;
        $len = hypot($p2[0] - $p1[0], $p2[1] - $p1[1]);
        if ($len < 1e-6 || $len > $maxLen) continue;
        $out[] = ['len' => (int)round($len + 2 * $tenon), 'horiz' => abs($p2[1] - $p1[1]) < 0.01];
    }
    return $out;
}

// [길이,…] → [['len'=>,'cnt'=>],…] (긴 것부터)
function slat_edit_group_lens(array $a): array {
    $g = array_count_values($a); krsort($g);
    $o = [];
    foreach ($g as $l => $c) $o[] = ['len' => (int)$l, 'cnt' => (int)$c];
    return $o;
}

// 삼각·육각: 살이 60° 격자라 서버가 줄 단위로 다시 세기 어렵다 → 브라우저가 편집 결과(직선살 개수 + 사선살 길이별 개수)를
// 집계해 `editPieces`로 보내고, 서버는 형식·범위만 검증해서 원가에 쓴다. 정자살·클래식·다이아몬드·십자는 서버가 직접 계산한다.
// 반환: ['straight'=>int, 'diag'=>[['len'=>int,'cnt'=>int],…]] 또는 null(없음/형식 오류)
function slat_edit_client_pieces(float $maxLen, int $maxStraight = 4000): ?array {
    $raw = (string)($_POST['editPieces'] ?? '');
    if ($raw === '' || strlen($raw) > 20000) return null;
    $j = json_decode($raw, true);
    if (!is_array($j) || !isset($j['straight'], $j['diag']) || !is_array($j['diag']) || count($j['diag']) > 300) return null;
    $st = (int)$j['straight'];
    if ($st < 0 || $st > $maxStraight) return null;
    $diag = []; $total = 0;
    foreach ($j['diag'] as $it) {
        if (!is_array($it) || !isset($it['len'], $it['cnt'])) return null;
        $len = (int)$it['len']; $cnt = (int)$it['cnt'];
        if ($len < 1 || $len > $maxLen || $cnt < 1 || $cnt > 4000) return null;
        $total += $cnt;
        $diag[] = ['len' => $len, 'cnt' => $cnt];
    }
    if ($total > 8000) return null;
    usort($diag, fn($a, $b) => $b['len'] <=> $a['len']);
    return ['straight' => $st, 'diag' => $diag];
}
