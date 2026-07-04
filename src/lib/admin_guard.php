<?php
// 어드민 페이지 서버사이드 가드. 반드시 <!DOCTYPE html> 등 어떤 출력도 하기 전에
// (파일의 맨 위, header() 호출 직후에) require하고 require_admin_role()을 호출할 것.
// 기존엔 클라이언트 JS(auth_guard.php)만 권한을 검사해 크롤러·curl 요청에는
// 어드민 페이지 마크업이 그대로 200으로 노출되는 문제가 있었음.
require_once __DIR__ . '/jwt.php';

function require_admin_role(string $role = 's'): void {
    $payload = jwt_from_request();
    if (!$payload || ($payload['role'] ?? null) !== $role) {
        header('Location: /');
        exit;
    }
    header('X-Robots-Tag: noindex, nofollow');
}
