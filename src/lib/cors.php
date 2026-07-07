<?php
$_cors_origin  = $_SERVER['HTTP_ORIGIN'] ?? '';
$_cors_allowed = [
    'https://studio.pyeongmok.com',
    'https://www.studio.pyeongmok.com',
    'http://w.pyeongmok.com',
    'http://localhost',
    'http://127.0.0.1',
];
if (in_array($_cors_origin, $_cors_allowed, true)) {
    header('Access-Control-Allow-Origin: ' . $_cors_origin);
} else {
    header('Access-Control-Allow-Origin: https://studio.pyeongmok.com');
}
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
unset($_cors_origin, $_cors_allowed);
