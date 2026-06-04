<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

if (session_status() === PHP_SESSION_NONE) @session_start();
session_destroy();

echo json_encode(['message' => '로그아웃 되었습니다.']);
