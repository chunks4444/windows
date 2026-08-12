<?php
require_once __DIR__ . '/../lib/studio_card_content.php';
$guide_current = 'studio-triangle.php';
$guide_title   = '세모 솟을살';
$guide_prev    = ['href' => 'studio-diamond.php', 'title' => '격자 빗살'];
$guide_next    = ['href' => 'studio-hexagon.php', 'title' => '육모 솟을살'];
include __DIR__ . '/_head.php';
?>

<h1><span class="guide-h1-icon"><?= $guideEngineIcons['triangle'] ?></span>세모 솟을살</h1>
<p class="guide-lead"><?= studio_card_description('triangle', "수직살과 좌우 빗살, 세 방향의 살대가 한 점에서 만나도록 짠 세모 솟을살을 재현한 엔진입니다.
    '솟을'은 살이 교차점에서 겹치며 위로 솟아오르는 데서 온 이름으로, 교차점마다 살이 도드라져 짜임에 입체감이 살아 있습니다.
    살들이 교차하며 정삼각형이 화면 가득 반복되어, 육모의 둥글고 넉넉한 인상과 달리 팽팽하고 긴장감 있는 느낌을 줍니다.
    모든 셀이 정삼각형이 되도록 세로 칸수가 자동으로 계산되며, 세로 칸수를 직접 지정할 수는 없습니다.") ?></p>

<h2>살 구성</h2>
<table class="guide-table">
    <thead><tr><th>살 방향</th><th>각도</th></tr></thead>
    <tbody>
        <tr><td>수직살</td><td>0° (수직)</td></tr>
        <tr><td>우사선살</td><td>60°</td></tr>
        <tr><td>좌사선살</td><td>120°</td></tr>
    </tbody>
</table>

<h2>주요 파라미터</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td><strong>문 종류 / 문 짝수</strong></td><td>여닫이·미서기, 1~4짝</td></tr>
        <tr><td><strong>문틀 가로 / 문틀 세로</strong></td><td>벽 개구부 치수 (400~3,000mm). 문틀 두께를 제외한 값이 문짝 외경으로 자동 계산됩니다.</td></tr>
        <tr><td><strong>가로 칸수</strong></td><td>2~30, <strong>짝수 단위</strong>로만 조절됩니다(기본값 4). 이 값과 창살 두께로 정삼각형 크기가 정해지고, 세로 칸수는 자동 산출됩니다.</td></tr>
        <tr><td><strong>패턴 세로 방향</strong></td><td>기본 켜짐. 삼각 격자 전체를 90° 회전합니다. 켜짐/꺼짐 여부에 따라 부재 목록의 방향별 부재 명칭이 바뀝니다.</td></tr>
        <tr><td><strong>세로 자동 맞춤</strong></td><td>체크 시 마지막 행이 하단 울거미에 딱 맞도록 문틀 세로 값을 자동 재조정</td></tr>
        <tr><td><strong>좌우 / 상하 울거미 두께</strong></td><td>외곽 프레임 두께 (mm)</td></tr>
        <tr><td><strong>창살 두께</strong></td><td>각 살의 폭 (mm). 정삼각형 셀 크기를 함께 결정합니다.</td></tr>
        <tr><td><strong>풍판 사용</strong></td><td>체크 시 상단 풍판 구역 추가</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>세로 칸수, 세로 비율 같은 별도 설정 항목은 없습니다. 삼각형 크기를 바꾸려면 가로 칸수 또는 창살 두께를 조정하세요.</span>
</div>

<h2>제작 시방서</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td>문틀 / 외경 / 내경 가로·세로</td><td>자동 계산된 실측 치수</td></tr>
        <tr><td>세로 먹줄</td><td>삼각형 교차점 간 간격</td></tr>
        <tr><td>반턱 너비</td><td>살이 교차하는 반턱의 너비</td></tr>
        <tr><td>세로 / 가로 울거미홈폭</td><td>울거미에 살이 파고드는 홈의 폭</td></tr>
    </tbody>
</table>

<h2>부재 목록</h2>
<p>울거미 → <strong>가로부재</strong>(패턴 세로 방향 설정에 따라 명칭이 바뀜) → <strong>사선살</strong>(60°·120° 두 방향) → (풍판 사용 시) 풍판 → 문틀 순으로 구성됩니다.</p>

<h2>마감 · 색상</h2>
<p>목재·마감·부자재, 울거미·살 컬러는 세살과 동일합니다. <strong>면 컬러 칠하기 기능은 현재 비활성화</strong>되어 있습니다.</p>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>세모 솟을살에서 한 방향 살을 생략하면 <strong>육모 솟을살</strong> 패턴이 됩니다. 두 엔진은 격자 수식 자체는 동일하고 그려지는 선의 방향만 다릅니다. <a href="/guide/studio-hexagon" style="color:var(--accent);">육모 솟을살</a> 페이지에서 비교해 보세요.</span>
</div>

<h2>활용 예시</h2>
<ul>
    <li>전통 누각 천장 격자 패턴</li>
    <li>현대 건축 파사드 스크린</li>
    <li>인테리어 천장 조명 박스</li>
</ul>

<?php include __DIR__ . '/_foot.php'; ?>
