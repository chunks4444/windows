<?php
/**
 * 시맨틱 URL slug 생성 공용 함수 (블로그, 포트폴리오 등 공유)
 */

function make_slug(string $title): string {
    $s = trim($title);
    $s = preg_replace('/[\/\?\#\&\%\s]+/u', '-', $s);
    $s = preg_replace('/[^\p{L}\p{N}\-]/u', '', $s);
    $s = preg_replace('/-+/', '-', $s);
    $s = trim($s, '-');
    $s = mb_strtolower($s, 'UTF-8');
    // 한글 등 비ASCII 문자는 URL 인코딩 시 글자당 최대 9배(UTF-8 3바이트 → %XX%XX%XX)로
    // 길이가 늘어나 URL 전체가 검색엔진 권장 길이를 훌쩍 넘길 수 있어 slug 자체를 짧게 제한한다.
    // 20자였을 때도 도메인+경로(/src/blog/) 포함 250자를 넘는 사례가 있어 14자로 축소
    // (한글 14자 * 9 = 126자 인코딩, 중복 방지 접미사(-2 등) 여유분 포함해도 충분히 여유 있음).
    $s = trim(mb_substr($s, 0, 14, 'UTF-8'), '-');
    return $s !== '' ? $s : 'post';
}

// 컬렉션 아이템(library_patterns) 화면 표시 이름. slug가 평목 컬렉션 코드 체계 v1.0
// 형식(계열-수식어-일련번호)이면 코드를 대문자로 노출하고, 코드 없는 구버전 항목이면
// name_ko(관리자 메모)로 폴백한다 — name_ko가 비어 있으면 최후 수단으로 slug를 그대로 노출.
function library_pattern_display_name(string $slug, string $nameKo): string {
    if (preg_match('/^[a-z]{3}(-[a-z]{2})?-\d{3}$/', $slug)) {
        return strtoupper($slug);
    }
    return $nameKo !== '' ? $nameKo : strtoupper($slug);
}

function make_unique_slug(PDO $pdo, string $table, string $title, ?int $excludeId = null): string {
    $base = make_slug($title);
    $slug = $base;
    $i    = 2;
    while (true) {
        if ($excludeId) {
            $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE slug = ?");
            $stmt->execute([$slug]);
        }
        if (!$stmt->fetch()) return $slug;
        $slug = $base . '-' . $i;
        $i++;
    }
}
