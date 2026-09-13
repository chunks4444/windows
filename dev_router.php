<?php
// 로컬 개발서버(php -S) 전용 라우터.
// uploads/ 하위 파일은 .gitignore 대상이라 로컬 저장소엔 없고 운영서버에만 존재한다.
// 로컬에 없는 /uploads/ 요청은 운영서버 이미지로 302 리다이렉트해 로컬에서도 바로 보이게 한다.
// (운영서버는 Apache로 서빙되어 이 라우터를 거치지 않으므로 운영에는 영향 없음)
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path !== null && strpos($path, '/uploads/') === 0 && !is_file(__DIR__ . $path)) {
    header('Location: https://pyeongmok.com' . $path);
    exit;
}

return false;
