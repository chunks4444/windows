<?php
require_once __DIR__ . '/../../../lib/oauth.php';

$provider = $_GET['provider'] ?? '';
if (!in_array($provider, ['google', 'kakao', 'naver'], true)) {
    http_response_code(400); exit('Invalid provider');
}

$cfg         = oauth_config($provider);
$state       = oauth_make_state($provider);
$redirectUri = OAUTH_CALLBACK_URL . '?provider=' . $provider;

$params = [
    'response_type' => 'code',
    'client_id'     => $cfg['client_id'],
    'redirect_uri'  => $redirectUri,
    'state'         => $state,
];
if ($cfg['scope']) $params['scope'] = $cfg['scope'];

header('Location: ' . $cfg['auth_url'] . '?' . http_build_query($params));
exit;
