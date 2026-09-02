<?php
/**
 * AI 렌더링 결과 이미지 서버 저장 공용 함수 (사용자당 최대 RENDER_LIMIT_PER_USER장)
 */

const RENDER_LIMIT_PER_USER = 300;

// 로그인은 필수지만 계정 하나가 스크립트로 gpt-image-1 API를 짧은 시간에 반복 호출하면
// 300장 누적 캡에 닿기 전까지 요금이 계속 나감 — 시간당 호출 빈도도 별도로 제한한다.
const RENDER_RATE_LIMIT     = 20; // 시간당 최대 렌더링 요청 수
const RENDER_RATE_WINDOW_MIN = 60;

function render_count_for_user(int $userId): int {
    require_once __DIR__ . '/db.php';
    $stmt = db()->prepare('SELECT COUNT(*) FROM renders WHERE user_id = ?');
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}

function render_count_recent_for_user(int $userId, int $minutes): int {
    require_once __DIR__ . '/db.php';
    $stmt = db()->prepare('SELECT COUNT(*) FROM renders WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)');
    $stmt->execute([$userId, $minutes]);
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
