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
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        error_log("render_save: uploads/renders/{$userId} 디렉토리 생성 실패 (권한 문제?)");
        return;
    }

    $fname = time() . '_' . bin2hex(random_bytes(4)) . '.png';
    if (file_put_contents($dir . '/' . $fname, $pngBytes) === false) {
        error_log("render_save: {$dir}/{$fname} 파일 저장 실패 (권한 문제?)");
        return;
    }

    $stmt = db()->prepare('INSERT INTO renders (user_id, engine, filepath) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $engine, '/uploads/renders/' . $userId . '/' . $fname]);
}
