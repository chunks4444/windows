<?php
require_once __DIR__ . '/engine_settings.php';

// 주문 사양(spec_json)으로 그 엔진의 geometry.php를 이 요청 안에서 그대로 실행해 예상견적을 다시 계산한다 — 계산식을 복제하지 않기 위해.
// geometry.php는 요청자 JWT로 열람권한을 거르므로, 실행하는 동안만 PMOK_PERMS_OVERRIDE로 전체 권한을 켠다(고객 주문 접수 때도 원가내역이 나오도록).
// 결과에 computedAt(산출 시각)·source(order=접수 시 / admin_backfill=관리자 첫 열람 때)를 박아 주문에 고정 저장하는 용도.
// 파라미터 이름은 각 엔진 JS의 fetchGeometry()가 보내는 것과 같게 맞춘다. 원가표·엔진설정은 "지금" 값이라 주문 당시와 다를 수 있다.
// 몬드리안은 실제 살 개수·길이(moHCnt 등)를 브라우저에서만 알 수 있어 기본 격자 기준 근사값이 된다.
function order_compute_estimate(string $engine, ?string $specJson, string $source): ?array {
    if (!in_array($engine, ['classic', 'square', 'cross', 'diamond', 'triangle', 'hexagon', 'mondrian'], true)) return null;
    $p = $specJson ? json_decode($specJson, true) : null;
    if (!is_array($p) || empty($p['W']) || empty($p['H'])) return null;
    $file = __DIR__ . "/../engine/$engine/api/geometry.php";
    if (!is_file($file)) return null;

    $cfg  = get_engine_settings($engine);
    $post = [
        'cols'          => $p['cols'] ?? '',
        'frameOpeningW' => $p['W'],
        'frameOpeningH' => $p['H'],
        'frameThick'    => $cfg['frameThick'] ?? 30,
        'frameGap'      => $p['frameGap'] ?? $cfg['frameGap'] ?? 2,
        'pungpanH'      => $p['pungpan'] ?? 0,
        'pungpanOn'     => !empty($p['pungpanOn']) ? '1' : '0',
        'frameW'        => $p['frame'] ?? '',
        'frameH'        => $p['frameH'] ?? '',
        'slatT'         => $p['slat'] ?? '',
        'vRatio'        => $p['vRatio'] ?? '1.0',
        'rowsManual'    => !empty($p['rowsManual']) ? '1' : '0',
        'rows'          => $p['rows'] ?? '',
        'pattern'       => $p['pattern'] ?? '',
        'rotateOn'      => !empty($p['rotate']) ? '1' : '0',
        'doorType'      => $p['doorType'] ?? 'swing',
        'doorCount'     => $p['doorCount'] ?? 1,
        'wood'          => $p['wood'] ?? '',
        'hardware'      => $p['hardware'] ?? '',
        'finish'        => $p['finish'] ?? '',
        'muntolOn'      => ($p['showMuntol'] ?? true) === false ? '0' : '1',
    ];
    $post = array_map(fn($v) => is_scalar($v) ? (string) $v : '', $post);
    // 몬드리안: 저장된 레이아웃(mondrianLayout.lines, 비율 좌표)과 편집으로 브라우저 mondrianCostParams()와 같은 살 집계를 만든다
    if ($engine === 'mondrian' && is_array($p['mondrianLayout']['lines'] ?? null)) {
        $del = array_flip(array_filter((array)($p['deletedSegs'] ?? []), 'is_string'));
        $hC = $vC = 0; $hL = $vL = 0.0;
        foreach ($p['mondrianLayout']['lines'] as $idx => $ln) {
            if (!is_array($ln) || isset($del["0:mo:$idx"])) continue;
            $len = (float)($ln['to'] ?? 0) - (float)($ln['from'] ?? 0);
            if (($ln['axis'] ?? '') === 'h') { $hC++; $hL += $len; } else { $vC++; $vL += $len; }
        }
        foreach ((array)($p['addedLines'] ?? []) as $ln) {
            if (!is_array($ln) || isset($ln['xi1']) || !isset($ln['nx1'], $ln['nx2'], $ln['ny1'], $ln['ny2'])) continue;
            $dx = abs((float)$ln['nx2'] - (float)$ln['nx1']); $dy = abs((float)$ln['ny2'] - (float)$ln['ny1']);
            if ($dy < 0.001) { $hC++; $hL += $dx; } else { $vC++; $vL += $dy; }
        }
        $post['moHCnt'] = (string)$hC; $post['moVCnt'] = (string)$vC;
        $post['moHLen'] = number_format($hL, 4, '.', ''); $post['moVLen'] = number_format($vL, 4, '.', '');
    }
    // 삼각·육각: 저장된 편집 집계(editPieces: 직선살 개수 + 사선살 길이별 개수)를 그대로 넘긴다 — geometry.php가 범위를 검증한다
    if (in_array($engine, ['triangle', 'hexagon'], true) && !empty($p['editPieces']) && is_array($p['editPieces'])) {
        $post['editPieces'] = json_encode($p['editPieces']);
    }
    // 정자살·클래식·다이아몬드·십자: 편집(지운 살 조각·추가선)을 원가에 반영 — 배열은 위 scalar 필터에서 빠지므로 JSON 문자열로 따로 넘긴다
    if (in_array($engine, ['square', 'classic', 'diamond', 'cross'], true)) {
        if (!empty($p['deletedSegs']) && is_array($p['deletedSegs'])) $post['deletedSegs'] = json_encode(array_values($p['deletedSegs']));
        if (!empty($p['addedLines'])  && is_array($p['addedLines']))  $post['addedLines']  = json_encode(array_values($p['addedLines']));
    }

    $savedPost = $_POST;
    $savedDisplay = ini_get('display_errors');
    $savedLevel   = error_reporting();
    $_POST = $post;
    $GLOBALS['PMOK_PERMS_OVERRIDE'] = true;
    ob_start();
    try {
        (function ($__file) { include $__file; })($file);
        $out = ob_get_clean();
    } catch (Throwable $e) {
        ob_end_clean();
        $out = '';
    } finally {
        $_POST = $savedPost;
        unset($GLOBALS['PMOK_PERMS_OVERRIDE']);
        ini_set('display_errors', $savedDisplay); // geometry.php가 꺼버린 설정 복구
        error_reporting($savedLevel);
        header('Content-Type: application/json'); // geometry.php가 charset 붙여 다시 보낸 헤더를 호출한 API 기본값으로 되돌림
    }

    $geo = json_decode((string) $out, true);
    if (!is_array($geo) || empty($geo['costBreakdown'])) return null;
    $b = $geo['costBreakdown'];
    $b['leadTimeDays'] = $geo['price']['leadTimeDays'] ?? null;
    $b['computedAt']   = date('Y-m-d H:i:s');
    $b['source']       = $source;
    // 몬드리안도 저장된 레이아웃으로 살 집계를 다시 만들 수 있으면 근사값이 아니다(구버전 spec은 레이아웃이 없어 기본 격자 근사)
    if ($engine === 'mondrian' && empty($post['moHCnt'])) $b['approx'] = true;
    return ['breakdown' => $b];
}
