<?php
// 기존 블로그 글의 검색용 본문(body_text·body_text_en)을 한 번에 채우는 스크립트.
// 마이그레이션(ops/migrations/2026-11-08_blog_search.sql) 직후 한 번 실행. 다시 실행해도 같은 결과(덮어씀).
// 실행: php ops/blog_search_backfill.php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require_once __DIR__ . '/../src/lib/db.php';
require_once __DIR__ . '/../src/lib/blog_search.php';

$pdo = db();
$ids = $pdo->query('SELECT id FROM blog_posts ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
foreach ($ids as $id) blog_refresh_body_text($pdo, (int)$id);
echo 'body_text 채움: ' . count($ids) . "편\n";
