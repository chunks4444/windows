<?php
// 회사소개 페이지(src/company/index.php)의 짧은 마케팅 문구 DB 조회 헬퍼.
// company_page_content에 값이 없으면 호출부에서 넘긴 $default(기존 하드코딩 문구)를 그대로 반환한다.
require_once __DIR__ . '/db.php';

function company_content(string $key, string $default = ''): string {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        $rows = db()->query('SELECT content_key, content_value FROM company_page_content')->fetchAll();
        foreach ($rows as $row) {
            $cache[$row['content_key']] = $row['content_value'];
        }
    }
    $value = $cache[$key] ?? '';
    return $value !== '' ? $value : $default;
}
