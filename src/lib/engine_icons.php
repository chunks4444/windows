<?php
/**
 * 6개 설계 엔진의 살 패턴을 표현하는 심볼 SVG.
 * .wk-icon-bar(막대)는 scale(0)→scale(1), .wk-icon-stroke(선)는
 * stroke-dashoffset 애니메이션으로 카드 호버 시 "조립되듯" 그려진다.
 * (src/css/work.css 참조)
 */

const ENGINE_LABELS = [
    'classic'   => '세살',
    'square'    => '정자살',
    'cross'     => '빗살',
    'diamond'   => '격자빗살',
    'triangle'  => '세모솟을살',
    'hexagon'   => '육모솟을살',
    'mondrian'  => '몬드리안',
];

function engine_icon_svg(string $key): string {
    $bar = fn(string $x, string $y, string $w, string $h, int $i) =>
        "<rect class=\"wk-icon-bar b{$i}\" x=\"{$x}\" y=\"{$y}\" width=\"{$w}\" height=\"{$h}\"/>";
    $rot = fn(int $deg, string $inner) => "<g transform=\"rotate({$deg} 340 340)\">{$inner}</g>";

    switch ($key) {
        case 'classic':
            $inner =
                $bar('148','204','384','46',1) . $bar('148','430','384','46',2) .
                $bar('148','148','46','384',3) . $bar('317','148','46','384',4) . $bar('486','148','46','384',5) .
                $bar('100','204','48','46',2)  . $bar('532','204','48','46',2) .
                $bar('100','430','48','46',3)  . $bar('532','430','48','46',3);
            break;
        case 'square':
            $inner = $bar('148','204','384','46',1) . $bar('148','430','384','46',2) .
                     $bar('204','148','46','384',3) . $bar('430','148','46','384',4);
            break;
        case 'cross':
            $inner = $rot(45, $bar('148','204','384','46',1) . $bar('148','430','384','46',2) .
                              $bar('204','148','46','384',3) . $bar('430','148','46','384',4));
            break;
        case 'diamond':
            $inner = $bar('317','148','46','384',1) . $bar('148','317','384','46',2) .
                     $rot(45,  $bar('317','148','46','384',3)) .
                     $rot(135, $bar('317','148','46','384',4));
            break;
        case 'triangle':
            $inner = $bar('317','148','46','384',1) .
                     $rot(60,  $bar('317','148','46','384',2)) .
                     $rot(120, $bar('317','148','46','384',3));
            break;
        case 'mondrian':
            // 다른 엔진 아이콘과 달리 색이 고정이라 wk-icon-bar(단색 accent)를 안 쓰고 직접 채움.
            // 다른 아이콘들과 같은 148~532 범위(680 기준)에 맞춰 크기를 통일 — 바깥 테두리는
            // stroke라 두께(40)의 절반만큼 안쪽으로 당겨서(168~512) 실제 보이는 바깥 경계가
            // 148~532가 되게 함.
            return '<svg viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">'
                . '<rect x="168" y="168" width="344" height="344" fill="none" stroke="#1a1a1a" stroke-width="40"/>'
                . '<rect x="148" y="148" width="160" height="192" fill="#d1232a"/>'
                . '<rect x="388" y="340" width="144" height="80" fill="#1c3f94"/>'
                . '<line x1="308" y1="148" x2="308" y2="532" stroke="#1a1a1a" stroke-width="18"/>'
                . '<line x1="148" y1="340" x2="532" y2="340" stroke="#1a1a1a" stroke-width="18"/>'
                . '<line x1="388" y1="340" x2="388" y2="532" stroke="#1a1a1a" stroke-width="18"/>'
                . '</svg>';
        case 'hexagon':
            return '<svg viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg" fill="none">'
                . '<polyline class="wk-icon-stroke s1" points="210,265 340,190 470,265"/>'
                . '<line class="wk-icon-stroke s2" x1="210" y1="265" x2="210" y2="415"/>'
                . '<line class="wk-icon-stroke s3" x1="470" y1="265" x2="470" y2="415"/>'
                . '<line class="wk-icon-stroke s4" x1="210" y1="415" x2="340" y2="490"/>'
                . '<line class="wk-icon-stroke s5" x1="470" y1="415" x2="340" y2="490"/>'
                . '</svg>';
        default:
            return '';
    }
    return '<svg viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">' . $inner . '</svg>';
}
