<?php
$guide_current = 'render.php';
$guide_title   = 'AI 렌더링 사용법';
$guide_prev    = ['href' => 'export.php', 'title' => 'PDF / PNG 내보내기'];
$guide_next    = ['href' => 'collection.php', 'title' => '컬렉션 & 내 보드'];
include __DIR__ . '/_head.php';
?>

<h1>AI 렌더링 사용법</h1>
<p class="guide-lead">
    현장 사진 위에 도면을 합성해 AI로 실제 시공 모습을 시각화하는 기능입니다.
    배경 이미지를 업로드하고 프롬프트를 입력하면 AI가 자동으로 렌더링을 생성합니다.
</p>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>AI 렌더링 기능은 <strong>Classic Lattice</strong> 스튜디오에서 사용 가능합니다. 로그인이 필요합니다.</span>
</div>

<h2>사용 방법</h2>
<ol class="guide-steps">
    <li>
        <strong>배경 이미지 업로드</strong><br>
        사이드바 하단의 <span class="guide-ui">사진 업로드</span> 버튼을 클릭하거나,
        이미지 파일을 캔버스 영역에 드래그 & 드롭합니다.
    </li>
    <li>
        <strong>배경 이미지 선택</strong><br>
        업로드된 이미지가 썸네일 목록에 표시됩니다. 사용할 이미지를 클릭해 활성화하면
        캔버스 배경으로 적용됩니다.
    </li>
    <li>
        <strong>도면 파라미터 조정</strong><br>
        사이드바에서 창호 격자 살의 치수·색상을 원하는 대로 조정합니다.
        배경 위에 격자가 실시간으로 합성되어 미리보기됩니다.
    </li>
    <li>
        <strong>렌더링 프롬프트 입력</strong><br>
        사이드바의 <span class="guide-ui">렌더링 프롬프트</span> 입력란에 원하는 분위기를 입력합니다.<br>
        예: <em>"한옥 카페 인테리어, 따뜻한 나무 질감, 낮 자연광"</em>
    </li>
    <li>
        <strong>렌더링 실행</strong><br>
        <span class="guide-ui">AI 렌더링</span> 버튼을 클릭합니다. 처리 시간은 통상 30~90초입니다.
    </li>
    <li>
        <strong>결과 확인 & 저장</strong><br>
        렌더링이 완료되면 결과 팝업이 열립니다. <span class="guide-ui">다운로드</span>로 PNG를 저장하거나,
        사이드바 렌더링 히스토리에서 다시 불러볼 수 있습니다.
    </li>
</ol>

<h2>프롬프트 작성 팁</h2>
<table class="guide-table">
    <thead><tr><th>좋은 프롬프트 예시</th><th>효과</th></tr></thead>
    <tbody>
        <tr><td>한옥 카페, 원목 바닥, 따뜻한 조명</td><td>자연스러운 목재 질감 강조</td></tr>
        <tr><td>현대 미니멀 인테리어, 화이트 톤, 낮 채광</td><td>깔끔하고 밝은 공간감</td></tr>
        <tr><td>야간, 간접 조명, 분위기 있는 레스토랑</td><td>따뜻한 저녁 분위기</td></tr>
        <tr><td>전통 사찰, 고즈넉한 자연 배경</td><td>전통적 고요한 느낌</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>프롬프트는 <strong>공간 유형 + 소재·톤 + 조명 조건</strong> 세 요소를 포함하면 더 정확한 결과를 얻을 수 있습니다.</span>
</div>

<h2>렌더링 히스토리</h2>
<p>
    최근 생성한 렌더링 결과는 사이드바 하단 <strong>Rendering 히스토리</strong>에 최대 9개까지 저장됩니다.
    썸네일을 클릭하면 결과 팝업에서 다시 확인하거나 다운로드할 수 있습니다.
</p>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>렌더링 히스토리는 브라우저 로컬에 저장됩니다. 브라우저 캐시 초기화 시 삭제될 수 있으므로 중요한 결과물은 즉시 다운로드하세요.</span>
</div>

<?php include __DIR__ . '/_foot.php'; ?>
