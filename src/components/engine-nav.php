<?php
$_hide_topbar_notice = true;
$_engine_nav = true;
include __DIR__ . '/nav.php';
// 공유 링크로 들어온 비로그인 뷰어는 로그인 모달을 띄우지 않음(캔버스는 보고 편집 가능, 저장 시에만 로그인 요구)
$authGuardSkip = !empty($_pmokIsSharedView);
include __DIR__ . '/auth_guard.php';
