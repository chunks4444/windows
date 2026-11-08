-- 접속 통계(page_views)에 방문자 분류 칸 추가 (2026-11-08)
-- 사람/봇 구분(visitor_type), 봇 이름(bot_name), 사람의 유입 경로(ref_source), 브라우저에서 JS가 실제로 실행됐는지(js_ok).
-- 기존 방문 기록은 모두 NULL('분류 전')로 남는다 — 로그에 User-Agent 원문이 없어 소급 분류할 수 없다.
-- 칸 추가는 INSTANT라 21만 행이어도 즉시 끝난다. 코드는 이 칸이 없어도 동작하도록(기록 실패 시 예전 방식으로 저장) 되어 있어 순서는 상관없다.
ALTER TABLE page_views
    ADD COLUMN visitor_type VARCHAR(12) NULL COMMENT 'human/search_bot/ai_bot/preview_bot/other_bot, NULL=분류 전' AFTER status_code,
    ADD COLUMN bot_name     VARCHAR(30) NULL COMMENT '봇 이름 (구글·네이버·Claude …)' AFTER visitor_type,
    ADD COLUMN ref_source   VARCHAR(12) NULL COMMENT '사람의 유입 경로 google/naver/daum/bing/other_search/sns/internal/direct/other' AFTER bot_name,
    ADD COLUMN js_ok        TINYINT(1)  NOT NULL DEFAULT 0 COMMENT '브라우저가 자바스크립트를 실행해 비콘을 보냈는지' AFTER ref_source,
    ALGORITHM=INSTANT;
