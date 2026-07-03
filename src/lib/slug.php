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
    return $s !== '' ? $s : 'post';
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
