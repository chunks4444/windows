<?php
/**
 * SVG에서 배경 역할을 하는 <rect>를 제거해 투명 배경으로 만든다.
 * 제거 대상: SVG 루트 직계 자식(또는 첫 <g> 직계 자식) <rect> 중
 *   - fill이 none/transparent가 아닌 단색이고
 *   - 전체 뷰포트를 덮는 크기(width/height 100% 또는 viewBox 치수와 동일)
 */
function svg_strip_background(string $svg): string {
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    if (!$dom->loadXML($svg)) return $svg;
    libxml_clear_errors();

    $root = $dom->documentElement;
    if (!$root || strtolower($root->localName) !== 'svg') return $svg;

    // viewBox 또는 width/height로 캔버스 크기 파악
    $vb = $root->getAttribute('viewBox');
    $vbW = $vbH = null;
    if ($vb) {
        $parts = preg_split('/[\s,]+/', trim($vb));
        if (count($parts) === 4) { $vbW = (float)$parts[2]; $vbH = (float)$parts[3]; }
    }
    $attrW = $root->getAttribute('width');
    $attrH = $root->getAttribute('height');
    $svgW  = $vbW ?? (float)$attrW ?: null;
    $svgH  = $vbH ?? (float)$attrH ?: null;

    _strip_bg_rects($dom, $root, $svgW, $svgH, true);

    $out = $dom->saveXML($root);
    return $out !== false ? $out : $svg;
}

function _strip_bg_rects(DOMDocument $dom, DOMElement $parent, ?float $svgW, ?float $svgH, bool $isRoot): void {
    $toRemove = [];
    foreach ($parent->childNodes as $node) {
        if (!($node instanceof DOMElement)) continue;
        $tag = strtolower($node->localName);

        if ($tag === 'rect') {
            if (_is_background_rect($node, $svgW, $svgH)) {
                $toRemove[] = $node;
            }
        } elseif ($tag === 'g' && $isRoot) {
            // 루트 바로 아래 첫 번째 <g>만 재귀 탐색
            _strip_bg_rects($dom, $node, $svgW, $svgH, false);
        }
    }
    foreach ($toRemove as $n) $parent->removeChild($n);
}

function _is_background_rect(DOMElement $rect, ?float $svgW, ?float $svgH): bool {
    $fill = strtolower(trim($rect->getAttribute('fill') ?: $rect->getAttribute('style')));
    // fill이 명시적으로 없거나 none/transparent면 배경이 아님
    if ($fill === '' || $fill === 'none' || $fill === 'transparent') return false;

    // opacity가 0이면 이미 투명
    $opacity = $rect->getAttribute('fill-opacity') ?: $rect->getAttribute('opacity') ?: '1';
    if ((float)$opacity === 0.0) return false;

    $x = (float)($rect->getAttribute('x') ?: '0');
    $y = (float)($rect->getAttribute('y') ?: '0');
    $w = $rect->getAttribute('width');
    $h = $rect->getAttribute('height');

    // 100% 크기는 뷰포트 전체 덮기
    if ($w === '100%' && $h === '100%' && $x === 0.0 && $y === 0.0) return true;

    if ($svgW !== null && $svgH !== null) {
        $rw = (float)$w;
        $rh = (float)$h;
        // viewBox 크기의 95% 이상 덮으면 배경으로 간주
        if ($x === 0.0 && $y === 0.0 && $rw >= $svgW * 0.95 && $rh >= $svgH * 0.95) return true;
    }

    return false;
}
