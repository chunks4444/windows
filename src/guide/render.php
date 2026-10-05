<?php
$guide_current = 'render.php';
$guide_title   = 'AI 렌더링 사용법';
$guide_prev    = ['href' => 'export.php', 'title' => 'PDF / PNG / DXF 내보내기'];
$guide_next    = ['href' => 'collection.php', 'title' => '컬렉션 & 내 보드'];
include __DIR__ . '/_head.php';
?>

<h1>AI 렌더링 — 공간 사진과 도면 합성</h1>
<p class="guide-lead">
    현장 사진(배경) 위에 창호 도면을 겹쳐 AI가 실제 시공 모습으로 합성하는 기능입니다.
    <strong>공간 사진 + 격자 도면 = AI 렌더링 결과물</strong>의 흐름으로 동작하며,
    스튜디오 왼쪽 메뉴의 <strong>렌더링</strong> 탭에서 모든 과정이 이루어집니다. 7개 엔진 모두 같은 방식입니다.
</p>

<!-- 합성 원리 흐름도 -->
<div class="guide-flow">
    <div class="guide-flow-step">
        <span class="step-icon">🖼️</span>
        <div class="step-title">공간 사진 올리기</div>
        <div class="step-desc">현장·실내 사진을 캔버스 배경으로 설정</div>
    </div>
    <div class="guide-flow-arrow">＋</div>
    <div class="guide-flow-step">
        <span class="step-icon">🪟</span>
        <div class="step-title">도면 배치</div>
        <div class="step-desc">문설정 탭으로 설계하고, 배치로 사진 속 자리에 맞춤</div>
    </div>
    <div class="guide-flow-arrow">→</div>
    <div class="guide-flow-step" style="border-color:var(--accent);background:var(--accent-tint);">
        <span class="step-icon">✨</span>
        <div class="step-title">AI 합성</div>
        <div class="step-desc">사진+도면을 AI가 자연스럽게 합성</div>
    </div>
    <div class="guide-flow-arrow">→</div>
    <div class="guide-flow-step">
        <span class="step-icon">💾</span>
        <div class="step-title">결과 보관</div>
        <div class="step-desc">렌더링 결과에 자동 저장, 언제든 다운로드</div>
    </div>
</div>

<h2>렌더링 탭 구성</h2>
<p>왼쪽 메뉴에서 <span class="guide-ui"><i class="bi bi-stars"></i> 렌더링</span>을 누르면 패널이 열립니다. 위에서부터 차례로 쓰면 됩니다.</p>

<!-- UI 스크린샷 -->
<div class="guide-screenshot">
    <img src="/src/img/guide/render.png" alt="세살 스튜디오에서 렌더링 탭을 연 화면 — 패널의 공간 사진·추천 프롬프트·직접 입력·AI 렌더링 버튼·렌더링 결과, 캔버스에는 공간 사진 위에 배치 모드로 문틀 자리에 맞춰 넣은 도면" loading="lazy">
</div>

<div class="guide-callout-grid">
    <div class="guid-label-callout"><div class="num">①</div><div><strong>공간 사진</strong> — 올린 사진 목록. 제목 줄 오른쪽에 <span class="guide-ui">배경 없음</span> · <span class="guide-ui">사진 올리기</span> 버튼이 있습니다.</div></div>
    <div class="guid-label-callout"><div class="num">②</div><div><strong>추천 프롬프트</strong> — 재질·조명 분위기를 고르면 아래 입력칸이 자동으로 채워집니다.</div></div>
    <div class="guid-label-callout"><div class="num">③</div><div><strong>직접 입력</strong> — 원하는 분위기를 문장으로 쓰거나 추천 문장을 고쳐 씁니다. 바로 아래 <span class="guide-ui">AI 렌더링</span> 버튼으로 실행합니다.</div></div>
    <div class="guid-label-callout"><div class="num">④</div><div><strong>렌더링 결과</strong> — 지금까지 만든 결과물 전체. 제목 옆에 장수가 표시됩니다.</div></div>
</div>

<h2>단계별 사용 방법</h2>

<h3>① 공간 사진 올리기</h3>
<ol class="guide-steps">
    <li>왼쪽 메뉴의 <span class="guide-ui">렌더링</span> 탭을 누릅니다.</li>
    <li>
        <strong>공간 사진</strong> 제목 줄의 <span class="guide-ui"><i class="bi bi-upload"></i> 사진 올리기</span>를 눌러 이미지 파일을 고릅니다.<br>
        여러 장을 한꺼번에 올릴 수 있습니다.
    </li>
    <li>
        올린 사진이 목록에 나타납니다. 사진을 누르면 <strong>바로 캔버스 배경으로 적용</strong>되고, 고른 사진에는 테두리가 표시됩니다.
    </li>
</ol>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>사진을 고르면 도면(격자살)이 사진 위에 겹쳐 보입니다. 이 화면 그대로가 AI에 보내지는 합성 이미지입니다.</span>
</div>

<h3>② 도면을 사진에 맞추기</h3>
<p>
    <span class="guide-ui">문설정</span> 탭에서 치수와 살 설정을, <span class="guide-ui">마감</span> 탭에서 색을 정합니다.
    사진 위에서 바로 미리보기되므로 공간에 어울리는 비율과 패턴을 즉석에서 확인할 수 있습니다.
</p>
<p>
    그다음 캔버스 위 툴바의 <span class="guide-ui">배치</span>를 켜고 문 네 모서리 핸들을 끌어 사진 속 문틀 자리에 맞춥니다.
    원근에 맞게 문이 비틀어져 들어가므로, 정면이 아닌 사진에서도 자연스러운 결과를 얻을 수 있습니다.
    자세한 내용은 <a href="/guide/canvas-toolbar">캔버스 툴바</a>를 참고하세요.
</p>

<h3>③ 프롬프트 작성</h3>
<p>
    <strong>추천 프롬프트</strong>에서 분위기를 하나 고르거나, <strong>직접 입력</strong> 칸에 원하는 분위기를 한두 줄로 씁니다.
    AI가 이 문장을 참고해 질감·조명·분위기를 합성합니다.
</p>

<table class="guide-table">
    <thead><tr><th>좋은 프롬프트 예시</th><th>기대 효과</th></tr></thead>
    <tbody>
        <tr><td>오래된 참나무 결, 옻칠 마감, 흰 한지</td><td>목재 질감과 한지 배경 강조</td></tr>
        <tr><td>한옥 카페 인테리어, 따뜻한 낮 채광</td><td>밝고 따뜻한 공간감</td></tr>
        <tr><td>아파트 거실 중문, 밝은 원목 마루, 오후 햇살</td><td>현대 주거 공간에 어울리는 분위기</td></tr>
        <tr><td>야간, 은은한 간접 조명, 분위기 있는 레스토랑</td><td>저녁 감성 분위기</td></tr>
        <tr><td>모던 미니멀, 화이트 톤, 대형 창, 도시 전경</td><td>깔끔하고 현대적인 분위기</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span><strong>공간 유형 + 소재·질감 + 조명 조건</strong> 세 가지를 넣으면 더 정확한 결과를 얻습니다.<br>
    예) <em>"한옥 카페"</em>(공간) + <em>"참나무 결"</em>(소재) + <em>"낮 자연광"</em>(조명)</span>
</div>

<h3>④ 렌더링 실행</h3>
<ol class="guide-steps">
    <li>
        <span class="guide-ui" style="background:var(--accent);color:var(--bg);border:none;"><i class="bi bi-stars"></i> AI 렌더링</span> 버튼을 누릅니다. (로그인이 필요합니다.)
    </li>
    <li>
        캔버스 위에 <strong>"AI 렌더링 중…"</strong> 표시가 나타납니다.
        보통 <strong>30~90초</strong> 걸립니다.
    </li>
    <li>
        완료되면 결과 창이 자동으로 열립니다. <span class="guide-ui">다운로드</span>로 저장하거나 창을 닫습니다.
    </li>
    <li>
        결과물은 패널 아래 <strong>렌더링 결과</strong>에 자동으로 쌓입니다.
    </li>
</ol>

<div class="guide-warn">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>공간 사진을 고르지 않았거나 프롬프트가 비어 있으면 안내 메시지가 뜨고 렌더링이 시작되지 않습니다.</span>
</div>

<h2>렌더링 결과 관리</h2>
<p>
    <strong>렌더링 결과</strong>에는 지금까지 만든 결과물이 모두 보입니다.
    이미지를 누르면 결과 창에서 크게 볼 수 있고, 이미지 오른쪽 위의
    <i class="bi bi-download"></i> 버튼으로 내려받거나 <i class="bi bi-trash3"></i> 버튼으로 지울 수 있습니다.
</p>

<div class="guide-note">
    <i class="bi bi-info-circle-fill"></i>
    <div>
        <p style="margin:0 0 4px;">렌더링 결과물은 서버에 저장되어 같은 계정이면 다른 기기·브라우저에서도 똑같이 보입니다.</p>
        <p style="margin:0;">단, 계정당 보관할 수 있는 렌더링은 최대 <strong>300장</strong>입니다. 한도를 넘으면 새 렌더링이 거부되고 안내 메시지가 표시되므로, 오래된 결과는 <i class="bi bi-trash3"></i>로 정리하거나 필요한 것은 <i class="bi bi-download"></i>로 미리 받아두세요.</p>
    </div>
</div>

<h2>도면관리의 렌더링 탭</h2>
<p>
    상단 내비게이션 <span class="guide-ui">도면관리</span> 페이지의 <strong>렌더링</strong> 탭에는 모든 엔진에서 만든 렌더링 결과가 모여 있습니다.
    위쪽에 <strong>보관 장수 / 300</strong>이 표시되고, 가득 차면 안내 문구가 나타납니다.
</p>
<table class="guide-table">
    <thead><tr><th>동작</th><th>방법</th></tr></thead>
    <tbody>
        <tr><td>크게 보기 · 다운로드</td><td>이미지를 누르면 상세 창이 열리고, <span class="guide-ui">다운로드</span> 버튼으로 내려받습니다.</td></tr>
        <tr><td>공유</td><td>이미지 위 <span class="guide-ui"><i class="bi bi-share-fill"></i></span> 버튼 → 링크 복사 또는 카카오톡·페이스북·X로 공유</td></tr>
        <tr><td>삭제</td><td>이미지 위 <span class="guide-ui"><i class="bi bi-x"></i></span> 버튼, 또는 상세 창의 <span class="guide-ui">삭제</span></td></tr>
    </tbody>
</table>

<h2>공간 사진 관리</h2>
<table class="guide-table">
    <thead><tr><th>동작</th><th>방법</th></tr></thead>
    <tbody>
        <tr><td>사진 추가</td><td>공간 사진 제목 줄의 <span class="guide-ui"><i class="bi bi-upload"></i> 사진 올리기</span></td></tr>
        <tr><td>사진 바꾸기</td><td>목록에서 다른 사진 클릭 → 바로 캔버스 배경이 바뀝니다.</td></tr>
        <tr><td>배경 없이 도면만 보기</td><td>제목 줄의 <span class="guide-ui"><i class="bi bi-slash-circle"></i> 배경 없음</span> 클릭</td></tr>
        <tr><td>사진 삭제</td><td>사진 위의 <span class="guide-ui"><i class="bi bi-x-lg"></i></span> 버튼 클릭</td></tr>
    </tbody>
</table>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>공간 사진은 서버에 저장되므로 같은 도면을 다시 열면 전에 올린 사진도 함께 돌아옵니다.</span>
</div>

<?php include __DIR__ . '/_foot.php'; ?>
