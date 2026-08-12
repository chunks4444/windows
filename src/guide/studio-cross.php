<?php
require_once __DIR__ . '/../lib/studio_card_content.php';
$guide_current = 'studio-cross.php';
$guide_title   = '빗살';
$guide_prev    = ['href' => 'studio-square.php', 'title' => '정자살'];
$guide_next    = ['href' => 'studio-diamond.php', 'title' => '격자 빗살'];
include __DIR__ . '/_head.php';
?>

<h1><span class="guide-h1-icon"><?= $guideEngineIcons['cross'] ?></span>빗살</h1>
<p class="guide-lead"><?= studio_card_description('cross', "울거미 안에 살대를 45도 기울여 대각선으로 짠 빗살을 재현한 엔진입니다.
    '빗'은 살을 비스듬히 기울여 짠 데서 온 이름으로, 엇갈린 살이 만드는 마름모꼴 격자가 이 창의 얼굴입니다.
    가로 칸수만 지정하면 셀이 항상 정사각형이 되도록 세로 칸수가 자동으로 계산되며,
    각도를 조절하는 설정 항목은 따로 없습니다.") ?></p>

<h2>특징</h2>
<ul>
    <li>셀은 항상 정사각형으로 고정되며, 그 대각선을 따라 살이 배치되어 45° 사선 무늬가 만들어집니다.</li>
    <li><strong>세로 칸수는 사용자가 정할 수 없습니다.</strong> 가로 칸수·창살 두께로 정해진 정사각 셀 크기에 맞춰 세로 방향 칸 수가 자동 산출됩니다.</li>
    <li>클리핑(Clipping) 처리로 프레임 밖으로 살이 삐져나오지 않습니다.</li>
</ul>

<h2>주요 파라미터</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td><strong>문 종류 / 문 짝수</strong></td><td>여닫이·미서기, 1~4짝</td></tr>
        <tr><td><strong>문틀 가로 / 문틀 세로</strong></td><td>벽 개구부 치수 (400~3,000mm). 문틀 두께를 제외한 값이 문짝 외경으로 자동 계산됩니다.</td></tr>
        <tr><td><strong>가로 칸수</strong></td><td>2~30. 정사각 셀의 가로 방향 개수 — 이 값과 창살 두께로 셀 크기가 정해지고, 세로 칸수는 자동으로 맞춰집니다.</td></tr>
        <tr><td><strong>세로 자동 맞춤</strong></td><td>체크 시 마지막 행이 하단 울거미에 딱 맞도록 문틀 세로 값을 자동 재조정</td></tr>
        <tr><td><strong>좌우 / 상하 울거미 두께</strong></td><td>외곽 프레임 두께 (mm)</td></tr>
        <tr><td><strong>창살 두께</strong></td><td>각 살의 폭 (mm). 셀 크기(=정사각형 한 변)를 함께 결정합니다.</td></tr>
        <tr><td><strong>풍판 사용</strong></td><td>체크 시 상단 풍판 구역 추가</td></tr>
    </tbody>
</table>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>정자살·세살에 있는 "세로 비율"이나 "상/중/하 배열" 같은 구획 설정은 빗살에는 없습니다. 격자 밀도를 조절하려면 가로 칸수 또는 창살 두께를 바꾸세요.</span>
</div>

<h2>제작 시방서</h2>
<table class="guide-table">
    <thead><tr><th>항목</th><th>설명</th></tr></thead>
    <tbody>
        <tr><td>문틀 / 외경 / 내경 가로·세로</td><td>자동 계산된 실측 치수</td></tr>
        <tr><td>가로 먹줄 / 세로 먹줄</td><td>정사각 셀의 가로·세로 간격</td></tr>
        <tr><td>살 먹줄</td><td>대각선 교차점 간 거리</td></tr>
        <tr><td>반턱 너비</td><td>창살 두께와 동일하게 계산</td></tr>
        <tr><td>울거미홈폭</td><td>창살 두께 × (1+√2) — 45° 사선살이 울거미에 파고드는 홈의 폭</td></tr>
    </tbody>
</table>

<h2>부재 목록</h2>
<p>가로살·세로살 그룹 대신 <strong>사선살</strong> 그룹으로 표시됩니다. 두 대각 방향(↘·↗)별로 길이가 같은 살끼리 묶어 길이×개수로 정리됩니다. 그 아래에 울거미, (풍판 사용 시) 풍판, 문틀 부재가 이어집니다.</p>

<h2>마감 · 색상</h2>
<p>목재·마감·부자재, 울거미·살 컬러는 세살과 동일합니다. 다만 <strong>면 컬러 칠하기 기능은 현재 비활성화</strong>되어 있어 개별 칸에 색을 채우는 기능은 사용할 수 없습니다.</p>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>빗살과 정자살을 겹쳐 활용하면 더 복잡한 격자 빗살 패턴 효과를 낼 수 있습니다. 자세한 내용은 <a href="/guide/studio-diamond" style="color:var(--accent);">격자 빗살</a> 페이지를 참조하세요.</span>
</div>

<h2>활용 예시</h2>
<ul>
    <li>한옥 방등(防燈) 창살 패턴</li>
    <li>현대 인테리어 스크린 파티션</li>
    <li>전통 누각 난간 패턴</li>
</ul>

<?php include __DIR__ . '/_foot.php'; ?>
