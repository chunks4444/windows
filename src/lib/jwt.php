<?php
// 시크릿은 git에 올리지 않는 config.local.php에서 읽는다. 예전엔 .htaccess SetEnv + 소스 내 기본값이었는데
// 둘 다 git에 들어 있어 2026-09-26 교체하면서 분리함. 기본값 없음 — 파일이 없으면 토큰 발급 자체가 안 되는 게 맞다.
require_once __DIR__ . '/config.local.php';
define('JWT_SECRET', PMOK_JWT_SECRET);
define('JWT_EXPIRE', 60 * 60 * 24 * 30); // 30일

function jwt_encode(array $payload): string {
    $header  = _b64u(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = _b64u(json_encode($payload));
    $sig     = _b64u(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
    return "$header.$payload.$sig";
}

function jwt_decode(string $token): ?array {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    [$header, $payload, $sig] = $parts;
    $expected = _b64u(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
    if (!hash_equals($expected, $sig)) return null;
    $data = json_decode(_b64d($payload), true);
    if (!$data) return null;
    if (isset($data['exp']) && $data['exp'] < time()) return null;
    return $data;
}

function jwt_from_request(): ?array {
    // 1) httpOnly 쿠키 (브라우저 기본 경로)
    if (!empty($_COOKIE['pmok_auth'])) {
        $decoded = jwt_decode($_COOKIE['pmok_auth']);
        if ($decoded) return $decoded;
        // 쿠키가 있지만 만료된 경우 Authorization 헤더로 폴백
    }

    // 2) Authorization 헤더 (API 클라이언트 폴백)
    $header = $_SERVER['HTTP_AUTHORIZATION']
           ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
           ?? '';
    if (!$header && function_exists('getallheaders')) {
        $all    = getallheaders();
        $header = $all['Authorization'] ?? $all['authorization'] ?? '';
    }
    if (preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
        return jwt_decode($m[1]);
    }

    return null;
}

function _b64u(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function _b64d(string $data): string {
    return base64_decode(strtr($data, '-_', '+/'));
}
