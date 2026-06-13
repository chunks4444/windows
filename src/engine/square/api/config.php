<?php
define('STABILITY_API_KEY', 'sk-Z8Yab8BxghadDI1MvySbehVqY7airiotiaAn1ayYWvSkH8Un');
define('OPENAI_API_KEY',    getenv('OPENAI_API_KEY') ?: '');

$_local = __DIR__ . '/../../config.local.php';
if (file_exists($_local)) require_once $_local;
