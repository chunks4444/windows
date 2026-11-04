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
    if ($engine === 'mondrian') $b['approx'] = true;
    return ['breakdown' => $b];
}
