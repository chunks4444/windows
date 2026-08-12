<?php
// 엔진 소개 문구 조회 헬퍼. /src/admin/studio_cards.php에서 관리하는 studio_cards.description을
// 회사소개 카드와 가이드 각 스튜디오 페이지의 인트로 문단이 함께 쓴다(단일 소스).
// 가이드 페이지 본문의 나머지(표·팁 박스 등)는 여전히 하드코딩.
require_once __DIR__ . '/db.php';

function studio_card_description(string $engineKey, string $default = ''): string {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            $rows = db()->query('SELECT engine_key, description FROM studio_cards WHERE is_active=1')->fetchAll();
            foreach ($rows as $row) $cache[$row['engine_key']] = $row['description'];
        } catch (Throwable $e) {
            $cache = [];
        }
    }
    $value = $cache[$engineKey] ?? '';
    return $value !== '' ? $value : $default;
}
