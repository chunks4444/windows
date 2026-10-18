<?php
$_hide_topbar_notice = true;
$_engine_nav = true;
include __DIR__ . '/nav.php';
// 엔진 페이지는 비로그인 방문자도 캔버스를 보고 설계할 수 있어야 함(저장·PNG/PDF 출력·AI 렌더링 시에만 로그인 요구)
$authGuardSkip = true;
include __DIR__ . '/auth_guard.php';

// 좌측 패널 각 탭 제목 뒤에 "지금 어느 엔진인지" 보여주기 위한 이름 — nav.php가 이미 계산해둔
// $navStudioItems/$navStudioActive를 그대로 쓴다(새로 하드코딩하지 않음, studio_cards 바뀌면 자동 반영).
$navCurrentEngineTitle = '';
foreach ($navStudioItems ?? [] as $navItem) {
    if ($navStudioActive[$navItem['engine_key']] ?? false) { $navCurrentEngineTitle = $navItem['title']; break; }
}
