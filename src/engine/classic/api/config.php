<?php
define('STABILITY_API_KEY', getenv('STABILITY_API_KEY') ?: '');
define('OPENAI_API_KEY',    getenv('OPENAI_API_KEY')    ?: '');

// 로컬 키 파일 (gitignore됨)
$_local = __DIR__ . '/../../config.local.php';
if (file_exists($_local)) require_once $_local;
