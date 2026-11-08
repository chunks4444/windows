<?php
// 회원별 활동 단계 기록 (user_milestones) — "처음 한 시각"만 남긴다. 어드민 '가입자 활동' 화면이 읽는다.
//
// 이 기록은 부가 정보라서, 표가 없거나 DB 오류가 나도 본 기능(도면 저장·주문 등)에는 영향이 없도록 모든 오류를 삼킨다.
// 호출은 각 기능이 성공한 직후에 한다: 도면 저장, 내보내기, 렌더링, 견적요청, 스튜디오 페이지 열람(logger.php).

const MS_STEPS = ['first_studio_at', 'first_drawing_at', 'first_export_at', 'first_render_at', 'first_order_at'];

// 단계 하나를 "처음 한 시각"으로 기록 (이미 있으면 그대로 두고 마지막 방문만 갱신)
function ms_mark(int $userId, string $step): void {
    if ($userId <= 0 || !in_array($step, MS_STEPS, true)) return;
    try {
        require_once __DIR__ . '/db.php';
        db()->prepare(
            "INSERT INTO user_milestones (user_id, $step, last_seen_at) VALUES (?, NOW(), NOW())
             ON DUPLICATE KEY UPDATE $step = COALESCE($step, NOW()), last_seen_at = NOW()"
        )->execute([$userId]);
    } catch (Throwable $e) {
        // 표가 아직 없거나 일시적 DB 오류 — 본 기능에 영향 주지 않는다
    }
}

// 방문만 기록 — 쓰기가 잦지 않도록 마지막 방문이 2분 넘게 지났을 때만 갱신 (어드민 '현재 접속'이 최근 10분 기준)
function ms_seen(int $userId): void {
    if ($userId <= 0) return;
    try {
        require_once __DIR__ . '/db.php';
        db()->prepare(
            "INSERT INTO user_milestones (user_id, last_seen_at) VALUES (?, NOW())
             ON DUPLICATE KEY UPDATE last_seen_at = IF(last_seen_at IS NULL OR last_seen_at < NOW() - INTERVAL 2 MINUTE, NOW(), last_seen_at)"
        )->execute([$userId]);
    } catch (Throwable $e) {
    }
}

// 스튜디오(설계 엔진) 페이지 주소인지
function ms_is_studio_page(string $page): bool {
    return (bool)preg_match('#/src/engine/[a-z]+/[a-z]+\.php#', $page);
}
