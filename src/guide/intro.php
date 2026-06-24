<?php
$guide_current = 'intro.php';
$guide_title   = '평목 스튜디오란?';
$guide_prev    = null;
$guide_next    = ['href' => 'getting-started.php', 'title' => '시작하기'];
include __DIR__ . '/_head.php';
?>

<h1>평목 스튜디오란?</h1>
<p class="guide-lead">
    평목(平木)은 전통 한국 창호 도면을 브라우저에서 실시간으로 설계하고 내보낼 수 있는 온라인 스튜디오입니다.
    별도 프로그램 설치 없이 6가지 격자 패턴 엔진과 AI 렌더링 기능을 사용할 수 있습니다.
</p>

<h2>주요 기능</h2>
<p>평목이 제공하는 핵심 기능을 소개합니다.</p>

<table class="guide-table">
    <thead>
        <tr><th>기능</th><th>설명</th></tr>
    </thead>
    <tbody>
        <tr><td><strong>6가지 스튜디오</strong></td><td>세살, 정자살, 빗살, 격자 빗살, 세모 솟을살, 육모 솟을살 격자 패턴 엔진</td></tr>
        <tr><td><strong>실시간 렌더링</strong></td><td>파라미터 변경 즉시 캔버스에 반영</td></tr>
        <tr><td><strong>도면 저장</strong></td><td>클라우드 DB에 도면 & 버전 저장, 어디서든 이어서 작업</td></tr>
        <tr><td><strong>PDF / PNG 내보내기</strong></td><td>인쇄·납품용 고해상도 파일 출력</td></tr>
        <tr><td><strong>AI 렌더링</strong></td><td>배경 사진 위에 도면을 합성해 AI로 공간 시각화</td></tr>
        <tr><td><strong>컬렉션</strong></td><td>공개 라이브러리 패턴 열람 & 내 보드 저장</td></tr>
    </tbody>
</table>

<h2>6가지 격자 패턴 엔진</h2>
<p>스튜디오는 창호 살의 배열 방식에 따라 6가지 엔진으로 구분됩니다.</p>

<ul>
    <li><strong>세살</strong> — 전통 사분턱 구조. 수직·수평 살이 교차하는 정통 창호 패턴</li>
    <li><strong>정자살</strong> — 단순 정방형 격자. 수직·수평 살로 구성된 기본 패턴</li>
    <li><strong>빗살</strong> — 45° 회전 격자. 사선 살이 교차하는 대각선 패턴</li>
    <li><strong>격자 빗살</strong> — 마름모 복합 패턴. 수직·수평·사선이 결합된 고급 패턴</li>
    <li><strong>세모 솟을살</strong> — 삼각 격자. 0°·60°·120° 세 방향 살로 구성</li>
    <li><strong>육모 솟을살</strong> — 육각 격자. 삼각에서 한 방향을 제거한 육모살 패턴</li>
</ul>

<div class="guide-tip">
    <i class="bi bi-lightbulb-fill"></i>
    <span>어떤 패턴을 선택해야 할지 모르겠다면 <strong>컬렉션</strong> 페이지에서 실제 도면 예시를 먼저 살펴보세요.</span>
</div>

<h2>시스템 요구 사항</h2>
<p>평목은 웹 브라우저에서 동작하는 서비스입니다. 별도 설치 없이 아래 환경에서 사용 가능합니다.</p>
<ul>
    <li>Chrome 90 이상 (권장)</li>
    <li>Safari 15 이상</li>
    <li>Edge 90 이상</li>
    <li>Firefox 88 이상</li>
</ul>

<div class="guide-note">
    <i class="bi bi-info-circle"></i>
    <span>AI 렌더링 기능은 서버 처리 시간이 필요합니다. 안정적인 네트워크 환경에서 사용을 권장합니다.</span>
</div>

<?php include __DIR__ . '/_foot.php'; ?>
