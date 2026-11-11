-- 회원별 활동 단계 기록 (2026-11-09) — 어드민 '가입자 활동'(가입 → 스튜디오 → 도면 저장 → 내보내기 → 렌더링 → 견적요청) 용.
-- 회원마다 한 줄. 각 단계를 "처음 한 시각"만 남기고(COALESCE로 한 번만 채움), 마지막 방문 시각을 갱신한다.
-- page_views는 1년이 지나면 지워지지만 이 표는 남는다. 새로 모으는 개인정보는 없다(이미 회원 번호와 연결된 활동의 날짜 요약).
-- 과거 분은 ops/backfill_user_milestones.php가 기존 page_views·drawings·drawing_export_logs·renders·orders에서 채운다.
CREATE TABLE IF NOT EXISTS user_milestones (
    user_id          INT UNSIGNED NOT NULL COMMENT '회원 ID (users.id)',
    first_studio_at  DATETIME NULL COMMENT '처음 스튜디오(설계 엔진 페이지)를 연 시각',
    first_drawing_at DATETIME NULL COMMENT '처음 도면을 저장한 시각',
    first_export_at  DATETIME NULL COMMENT '처음 PNG/PDF로 내보낸 시각',
    first_render_at  DATETIME NULL COMMENT '처음 AI 렌더링을 한 시각',
    first_order_at   DATETIME NULL COMMENT '처음 견적요청(주문)을 한 시각',
    last_seen_at     DATETIME NULL COMMENT '마지막 접속 시각 (2분 단위로 갱신, 관리자 포함)',
    updated_at       DATETIME NOT NULL DEFAULT NOW() ON UPDATE NOW(),
    PRIMARY KEY (user_id),
    KEY idx_um_last_seen (last_seen_at),
    CONSTRAINT fk_um_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='회원별 활동 단계(처음 한 시각) — 어드민 가입자 활동 화면';
