<?php
define('JWT_SECRET', 'pmok-windows-secret-2024-!@#$%');
define('JWT_EXPIRE', 60 * 60 * 24 * 3); // 3일

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
    // 1) PHP 세션 (브라우저 환경 — Apache 헤더 문제 우회)
    if (session_status() === PHP_SESSION_NONE) @session_start();
    if (!empty($_SESSION['pmok_user_id'])) {
        return ['sub' => (int)$_SESSION['pmok_user_id'], 'email' => $_SESSION['pmok_email'] ?? ''];
    }

    // 2) Authorization 헤더 (API 클라이언트 / 프로덕션)
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

    // 3) 쿠키 폴백
    if (!empty($_COOKIE['pmok_auth'])) {
        return jwt_decode($_COOKIE['pmok_auth']);
    }

    return null;
}

function _b64u(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function _b64d(string $data): string {
    return base64_decode(strtr($data, '-_', '+/'));
}
