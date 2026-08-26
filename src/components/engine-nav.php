<?php
$_hide_topbar_notice = true;
$_engine_nav = true;
include __DIR__ . '/nav.php';
// 엔진 페이지는 비로그인 방문자도 캔버스를 보고 설계할 수 있어야 함(저장·PNG/PDF 출력·AI 렌더링 시에만 로그인 요구)
$authGuardSkip = true;
include __DIR__ . '/auth_guard.php';
