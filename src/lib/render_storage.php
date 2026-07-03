<?php
/**
 * AI 렌더링 결과 이미지 서버 저장 공용 함수 (사용자당 최대 RENDER_LIMIT_PER_USER장)
 */

const RENDER_LIMIT_PER_USER = 300;

function render_count_for_user(int $userId): int {
    require_once __DIR__ . '/db.php';
    $stmt = db()->prepare('SELECT COUNT(*) FROM renders WHERE user_id = ?');
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}

function render_save(int $userId, string $engine, string $pngBytes): void {
    require_once __DIR__ . '/db.php';
    $dir = __DIR__ . '/../../uploads/renders/' . $userId;
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) return;

    $fname = time() . '_' . bin2hex(random_bytes(4)) . '.png';
    if (file_put_contents($dir . '/' . $fname, $pngBytes) === false) return;

    $stmt = db()->prepare('INSERT INTO renders (user_id, engine, filepath) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $engine, '/uploads/renders/' . $userId . '/' . $fname]);
}
