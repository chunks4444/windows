<?php
if (!defined('OAUTH_CALLBACK_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'windows.pyeongmok.com';
    define('OAUTH_CALLBACK_URL', $scheme . '://' . $host . '/src/api/auth/oauth/callback.php');
}

// ── DB에서 OAuth 키 로드 ──────────────────────────────────────
function oauth_config(string $provider): array {
    require_once __DIR__ . '/db.php';
    static $cfg = [];
    if (!$cfg) {
        $rows = db()->query("SELECT key_name, value FROM site_config WHERE key_name LIKE 'oauth_%'")->fetchAll();
        foreach ($rows as $r) $cfg[$r['key_name']] = $r['value'];
    }
    switch ($provider) {
        case 'google':
            return [
                'auth_url'      => 'https://accounts.google.com/o/oauth2/v2/auth',
                'token_url'     => 'https://oauth2.googleapis.com/token',
                'userinfo_url'  => 'https://www.googleapis.com/oauth2/v3/userinfo',
                'client_id'     => $cfg['oauth_google_client_id']     ?? '',
                'client_secret' => $cfg['oauth_google_client_secret'] ?? '',
                'scope'         => 'openid email profile',
            ];
        case 'kakao':
            return [
                'auth_url'      => 'https://kauth.kakao.com/oauth/authorize',
                'token_url'     => 'https://kauth.kakao.com/oauth/token',
                'userinfo_url'  => 'https://kapi.kakao.com/v2/user/me',
                'client_id'     => $cfg['oauth_kakao_client_id']     ?? '',
                'client_secret' => $cfg['oauth_kakao_client_secret'] ?? '',
                'scope'         => 'account_email profile_nickname',
            ];
        case 'naver':
            return [
                'auth_url'      => 'https://nid.naver.com/oauth2.0/authorize',
                'token_url'     => 'https://nid.naver.com/oauth2.0/token',
                'userinfo_url'  => 'https://openapi.naver.com/v1/nid/me',
                'client_id'     => $cfg['oauth_naver_client_id']     ?? '',
                'client_secret' => $cfg['oauth_naver_client_secret'] ?? '',
                'scope'         => '',
            ];
        default:
            throw new InvalidArgumentException("Unknown provider: $provider");
    }
}

// ── state CSRF 토큰 ───────────────────────────────────────────
function oauth_make_state(string $provider): string {
    $state  = bin2hex(random_bytes(16));
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    setcookie('_oauth_st', $state . ':' . $provider, [
        'expires'  => time() + 600,
        'path'     => '/',
        'httponly' => true,
        'secure'   => $secure,
        'samesite' => 'Lax',
    ]);
    return $state;
}

function oauth_verify_state(string $state, string $provider): bool {
    $cookie = $_COOKIE['_oauth_st'] ?? '';
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    setcookie('_oauth_st', '', ['expires' => time() - 3600, 'path' => '/', 'secure' => $secure, 'samesite' => 'Lax']);
    if (!$cookie) return false;
    [$s, $p] = explode(':', $cookie, 2) + ['', ''];
    return $p === $provider && hash_equals($s, $state);
}

// ── Access Token 교환 ─────────────────────────────────────────
function oauth_get_token(array $cfg, string $code, string $provider, ?string &$error = null): ?string {
    $params = [
        'grant_type'   => 'authorization_code',
        'client_id'    => $cfg['client_id'],
        'redirect_uri' => OAUTH_CALLBACK_URL . '?provider=' . $provider,
        'code'         => $code,
    ];
    if ($cfg['client_secret']) $params['client_secret'] = $cfg['client_secret'];

    $ch = curl_init($cfg['token_url']);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($params),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT        => 10,
    ]);
    $res  = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($res, true);
    if (!isset($data['access_token'])) {
        $error = ($data['error_description'] ?? $data['error'] ?? 'unknown');
    }
    return $data['access_token'] ?? null;
}

// ── 사용자 이메일 조회 ─────────────────────────────────────────
function oauth_get_email(string $provider, array $cfg, string $access_token): ?string {
    $ch = curl_init($cfg['userinfo_url']);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $access_token],
        CURLOPT_TIMEOUT        => 10,
    ]);
    $res  = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($res, true);

    switch ($provider) {
        case 'google': return $data['email'] ?? null;
        case 'kakao':  return $data['kakao_account']['email'] ?? null;
        case 'naver':  return $data['response']['email'] ?? null;
        default:       return null;
    }
}
