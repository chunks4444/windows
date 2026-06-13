<?php
header('Content-Type: text/plain; charset=UTF-8');
require_once __DIR__ . '/../../lib/jwt.php';
$p = jwt_from_request();
if (!$p || ($p['role'] ?? '') !== 's') { http_response_code(403); exit; }

$log = sys_get_temp_dir() . '/pmok_render_debug.log';
echo file_exists($log) ? file_get_contents($log) : '(로그 없음)';
