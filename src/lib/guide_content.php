<?php
// 가이드 아티클(제목/본문) DB 조회 헬퍼. 카테고리·메뉴 구조는 src/guide/_head.php의
// $guide_nav에 그대로 남아있고, 이 파일은 slug당 1행인 guide_articles 테이블만 다룬다.
require_once __DIR__ . '/db.php';

// 단건 조회. 없으면 null.
function guide_article(string $slug): ?array {
    $stmt = db()->prepare('SELECT slug, title, body_html FROM guide_articles WHERE slug = ?');
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    return $row ?: null;
}

// 사이드바용 slug => title 맵 (요청당 1쿼리로 캐시 — 사이드바·prev/next 계산에서 반복 호출됨)
function guide_titles_map(): array {
    static $map = null;
    if ($map === null) {
        $map = [];
        $rows = db()->query('SELECT slug, title FROM guide_articles')->fetchAll();
        foreach ($rows as $row) {
            $map[$row['slug']] = $row['title'];
        }
    }
    return $map;
}
