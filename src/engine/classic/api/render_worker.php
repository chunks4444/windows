<?php
// 백그라운드에서 실행: php render_worker.php <jobId>
set_time_limit(0);
ini_set('memory_limit', '256M');
ini_set('display_errors', 0);
error_reporting(0);

$jobId = $argv[1] ?? '';
if (!$jobId) exit(1);

$jobDir = sys_get_temp_dir() . '/pmok_render';
$input  = json_decode(file_get_contents("{$jobDir}/{$jobId}.input.json"), true);
if (!$input) exit(1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../../../lib/ai_render.php';

$result = ai_render_openai($input['image'], $input['prompt']);
file_put_contents("{$jobDir}/{$jobId}.result.json", json_encode($result));
unlink("{$jobDir}/{$jobId}.input.json");
