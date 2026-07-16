<?php
$guide_current = 'render.php';
$guide_title   = 'AI 렌더링 사용법';
$guide_prev    = ['href' => 'export.php', 'title' => 'PDF / PNG 내보내기'];
$guide_next    = ['href' => 'collection.php', 'title' => '컬렉션 & 내 보드'];
include __DIR__ . '/_head.php';
?>

<h1>AI 렌더링 — 배경 사진과 도면 합성</h1>
<p class="guide-lead">
    현장 사진(배경) 위에 창호 도면을 겹쳐 AI가 실제 시공 모습으로 합성하는 기능입니다.
    <strong>배경 이미지 + 격자 도면 = AI 렌더링 결과물</strong>의 흐름으로 동작하며,
    세살 스튜디오의 <strong>오른쪽 사이드바</strong>에서 모든 과정이 이루어집니다.
</p>

<!-- 합성 원리 흐름도 -->
<div class="guide-flow">
    <div class="guide-flow-step">
        <span class="step-icon">🖼️</span>
        <div class="step-title">배경 사진 업로드</div>
        <div class="step-desc">현장·공간 사진을 캔버스 배경으로 설정</div>
    </div>
    <div class="guide-flow-arrow">＋</div>
    <div class="guide-flow-step">
        <span class="step-icon">🪟</span>
        <div class="step-title">도면 설계</div>
        <div class="step-desc">왼쪽 사이드바로 창호 격자 파라미터 조정</div>
    </div>
    <div class="guide-flow-arrow">→</div>
    <div class="guide-flow-step" style="border-color:var(--accent);background:var(--accent-tint);">
        <span class="step-icon">✨</span>
        <div class="step-title">AI 합성</div>
        <div class="step-desc">배경+도면을 AI가 자연스럽게 합성</div>
    </div>
    <div class="guide-flow-arrow">→</div>
    <div class="guide-flow-step">
        <span class="step-icon">💾</span>
        <div class="step-title">결과 저장</div>
        <div class="step-desc">PNG 다운로드 또는 히스토리 보관</div>
    </div>
</div>

<h2>스튜디오 화면 구성</h2>
<p>세살 스튜디오는 <strong>세 개의 패널</strong>로 구성됩니다. AI 렌더링은 <strong>오른쪽 사이드바</strong>에서 진행됩니다.</p>

<!-- UI 스크린샷 -->
<div class="guide-screenshot">
    <img src="/src/img/guide/render.png" alt="AI 렌더링 화면 구성 — 배경 사진 위에 도면이 합성된 캔버스와 오른쪽 배경 업로드·재질/조명 선택·렌더링 패널" loading="lazy">
</div>

<div class="guide-callout-grid">
    <div class="guid-label-callout"><div class="num">①</div><div><strong>왼쪽 사이드바</strong> — 창호 격자 파라미터 설정. 변경 즉시 캔버스에 반영됩니다.</div></div>
    <div class="guid-label-callout"><div class="num">②</div><div><strong>캔버스</strong> — 배경 사진 위에 도면이 실시간으로 겹쳐 보입니다.</div></div>
    <div class="guid-label-callout"><div class="num">③</div><div><strong>배경 사진 패널</strong> — 사진 업로드, 썸네일 선택, AI 프롬프트 입력, 렌더링 실행.</div></div>
    <div class="guid-label-callout"><div class="num">④</div><div><strong>렌더링 히스토리</strong> — 최근 생성한 결과물을 최대 9개 보관. 클릭해 다시 볼 수 있습니다.</div></div>
</div>

<h2>단계별 사용 방법</h2>

<h3>① 배경 사진 업로드</h3>
<ol class="guide-steps">
    <li>
        <strong>오른쪽 사이드바</strong>가 닫혀 있다면 캔버스 오른쪽 끝의
        <span class="guide-ui">&rsaquo;</span> 탭을 클릭해 패널을 엽니다.
    </li>
    <li>
        <span class="guide-ui">↑ 사진 추가</span> 버튼을 클릭해 이미지 파일을 선택합니다.<br>
        여러 장을 한꺼번에 업로드할 수 있습니다.
    </li>
    <li>
        업로드된 사진이 아래 <strong>썸네일 목록</strong>에 나타납니다.
        원하는 썸네일을 클릭하면 <strong>즉시 캔버스 배경으로 적용</strong>됩니다.
        선택된 썸네일은 초록 테두리로 표시됩니다.
    </li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>배경 사진을 선택하면 도면(격자살)이 사진 위에 겹쳐진 상태로 캔버스에 표시됩니다. 이 상태가 AI에 전송되는 합성 이미지입니다.</span>
</div>

<h3>② 도면 파라미터 조정</h3>
<p>
    왼쪽 사이드바에서 창호의 치수와 격자 살 설정을 조정합니다.
    배경 사진 위에 도면이 실시간으로 합성되어 미리보기되므로,
    실제 공간에 어울리는 비율과 패턴을 즉석에서 확인할 수 있습니다.
</p>
<ul>
    <li><strong>가로폭 / 세로높이</strong> — 실제 설치 공간에 맞춰 조정</li>
    <li><strong>가로살 배열 (상/중/하)</strong> — 세 구역의 칸수 비율을 독립 조정 가능</li>
    <li><strong>울거미 · 살 색상</strong> — 오른쪽 사이드바 마감 섹션에서 색상 지정</li>
</ul>

<h3>③ AI 렌더링 프롬프트 작성</h3>
<p>
    오른쪽 사이드바 하단의 텍스트 입력란에 원하는 분위기를 한 줄로 입력합니다.
    AI가 이 텍스트를 참고해 배경+도면의 질감·조명·분위기를 합성합니다.
</p>

<table class="guide-table">
    <thead><tr><th>좋은 프롬프트 예시</th><th>기대 효과</th></tr></thead>
    <tbody>
        <tr><td>오래된 참나무 결, 옻칠 마감, 흰 한지</td><td>목재 질감과 한지 배경 강조</td></tr>
        <tr><td>한옥 카페 인테리어, 따뜻한 낮 채광</td><td>밝고 따뜻한 공간감</td></tr>
        <tr><td>야간, 은은한 간접 조명, 분위기 있는 레스토랑</td><td>저녁 감성 분위기</td></tr>
        <tr><td>전통 사찰, 고즈넉한 자연 배경, 자연광</td><td>정적이고 고요한 전통 느낌</td></tr>
        <tr><td>모던 미니멀, 화이트 톤, 대형 창, 도시 전경</td><td>깔끔하고 현대적인 분위기</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span><strong>공간 유형 + 소재·질감 + 조명 조건</strong> 세 요소를 포함하면 더 정확한 결과를 얻습니다.<br>
    예) <em>"한옥 카페"</em>(공간) + <em>"참나무 결"</em>(소재) + <em>"낮 자연광"</em>(조명)</span>
</div>

<h3>④ 렌더링 실행</h3>
<ol class="guide-steps">
    <li>
        <span class="guide-ui" style="background:var(--accent);color:var(--bg);border:none;">✨ Rendering</span> 버튼을 클릭합니다.
    </li>
    <li>
        캔버스 위에 <strong>"AI 렌더링 중…"</strong> 로딩 오버레이가 표시됩니다.
        처리 시간은 보통 <strong>30~90초</strong>입니다.
    </li>
    <li>
        완료되면 결과 팝업이 자동으로 열립니다.
        <span class="guide-ui">다운로드</span>로 PNG를 저장하거나 팝업을 닫습니다.
    </li>
    <li>
        생성된 결과물은 <strong>렌더링 히스토리</strong>에 자동 저장됩니다.
        썸네일을 클릭하면 언제든 다시 볼 수 있습니다.
    </li>
</ol>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>렌더링 실행 전 반드시 배경 사진이 선택된 상태인지 확인하세요. 배경 사진 없이 실행하면 안내 메시지가 표시되고 렌더링이 시작되지 않습니다.</span>
</div>

<h2>렌더링 히스토리</h2>
<p>
    최근 렌더링 결과는 오른쪽 사이드바 하단 <strong>렌더링 히스토리</strong>에 최대 <strong>9개</strong>까지 자동 보관됩니다.
    썸네일을 클릭하면 결과 팝업에서 다시 확인하거나 다운로드할 수 있습니다.
    히스토리의 <i class="bi bi-x"></i> 버튼으로 항목을 삭제할 수도 있습니다.
</p>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <div>
        <p style="margin:0 0 4px;">렌더링 결과물은 서버에 저장되어 같은 계정이면 다른 기기·브라우저에서도 동일하게 보입니다.</p>
        <p style="margin:0;">단, 계정당 보관 가능한 렌더링은 최대 <strong>300장</strong>입니다. 한도를 초과하면 새 렌더링이 거부되고 안내 메시지가 표시되므로, 오래된 렌더링을 오른쪽 사이드바 히스토리에서 <i class="bi bi-x"></i>로 정리하거나 필요한 결과물은 <span class="guide-ui">다운로드</span>로 백업해두세요.</p>
    </div>
</div>

<h2>배경 사진 관리</h2>
<table class="guide-table">
    <thead><tr><th>동작</th><th>방법</th></tr></thead>
    <tbody>
        <tr><td>배경 사진 추가</td><td>오른쪽 사이드바 <span class="guide-ui">↑ 사진 추가</span> 버튼 또는 드래그 &amp; 드롭</td></tr>
        <tr><td>배경 사진 전환</td><td>썸네일 클릭 → 즉시 해당 사진으로 캔버스 배경 변경</td></tr>
        <tr><td>배경 사진 제거</td><td>썸네일 옆 <span class="guide-ui"><i class="bi bi-x-lg"></i></span> 버튼 클릭</td></tr>
        <tr><td>배경 없이 도면만 보기</td><td>활성화된 썸네일을 다시 클릭해 비활성화</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>배경 사진은 서버에 저장되므로 같은 도면을 다시 불러오면 이전에 업로드한 사진도 함께 복원됩니다.</span>
</div>

<?php include __DIR__ . '/_foot.php'; ?>
