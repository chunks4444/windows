<?php
// 마감 가이드(finish.php, en/finish.php)의 색 견본 — 엔진 마감 탭과 같은 DB 값(color_swatches·cost_table)을 그대로 그린다.
// 관리자 '컬러 팔레트 관리'에서 색을 바꾸면 이 페이지도 따라 바뀐다. 이름은 term()을 거쳐 영문 페이지에선 영문으로.
//   $sw_mode = 'stain' : 스테인 팔레트(AURO 560·930…) — 마감 이름의 "AURO NNN"과 그룹 이름 번호를 짝지어 제목을 붙인다
//   $sw_mode = 'basic' : 기본 마감(마감 없음·천연오일) 칩
require_once __DIR__ . '/../lib/colors.php';
require_once __DIR__ . '/../lib/engine_settings.php';

$sw_groups   = get_color_groups();
$sw_finishes = array_column(get_finish_options(), 'name');
$sw_auro     = fn($s) => preg_match('/AURO\s*(\d{3})/i', (string) $s, $m) ? $m[1] : null;
$sw_isOil    = fn($s) => (bool) preg_match('/오일|기름|유$/u', (string) $s);   // engine-common.js isOilFinish와 같은 규칙
$sw_count    = is_en() ? '%d colours' : '%d색';

if (($sw_mode ?? 'stain') === 'basic') {
    // 천연오일 색은 엔진과 같은 규칙으로 고른다: 마감 전용 색(들기름) > 수종별 색(소나무) > 자연(NO-01)
    $oil = [];
    foreach ($sw_groups as $g) if (strpos($g['key'], '천연오일') === 0) foreach ($g['colors'] as $c) $oil[$c['code']] = $c;
    $woodCode = ['소나무' => 'NO-02'];
    echo '<div class="sw-basic">';
    echo '<div class="sw-chip"><span class="sw-dot sw-none"></span><b>' . htmlspecialchars(is_en() ? 'No finish' : '마감 없음') . '</b><small>' . htmlspecialchars(is_en() ? 'Bare wood' : '원목 그대로') . '</small></div>';
    foreach ($sw_finishes as $f) {
        if (!$sw_isOil($f)) continue;
        if ($f === '들기름') {
            $c = $oil['NO-10'] ?? null;
            echo '<div class="sw-chip"><span class="sw-dot" style="background:' . htmlspecialchars($c['hex'] ?? '#c8b48a') . '"></span><b>' . htmlspecialchars(term($f)) . '</b></div>';
            continue;
        }
        // 오일마감은 수종에 따라 색이 달라 수종별로 하나씩 보여 준다
        foreach (array_column(get_wood_options(), 'name') as $w) {
            $c = $oil[$woodCode[$w] ?? 'NO-01'] ?? null;
            echo '<div class="sw-chip"><span class="sw-dot" style="background:' . htmlspecialchars($c['hex'] ?? '#c8b48a') . '"></span><b>' . htmlspecialchars(term($f)) . '</b><small>' . htmlspecialchars(term($w)) . '</small></div>';
        }
    }
    echo '</div>';
    return;
}

foreach ($sw_groups as $g) {
    $code = $sw_auro($g['key']);
    if (!$code) continue;
    $title = $g['label'];
    foreach ($sw_finishes as $f) if ($sw_auro($f) === $code && !$sw_isOil($f)) { $title = term($f); break; }
    echo '<h3>' . htmlspecialchars($title) . ' <small class="sw-count">' . htmlspecialchars(sprintf($sw_count, count($g['colors']))) . '</small></h3>';
    echo '<div class="sw-grid">';
    foreach ($g['colors'] as $c) {
        echo '<div class="sw-item"><span class="sw-dot" style="background:' . htmlspecialchars($c['hex']) . '"></span>'
           . '<span class="sw-text"><b>' . htmlspecialchars($c['name']) . '</b><small>' . htmlspecialchars(trim(($c['brand'] ?? '') . ' ' . $c['code'])) . '</small></span></div>';
    }
    echo '</div>';
}
