-- ============================================================
-- 2026-11-08 블로그 검색 (/blog/search?q=...)
-- 실행: mysql pyeongmok < ops/migrations/2026-11-08_blog_search.sql  (1번 ALTER는 두 번째 실행 시 'Duplicate column' 오류 — MySQL 8.4엔 ADD COLUMN IF NOT EXISTS 문법이 없음. 2·3번은 IF NOT EXISTS라 안전)
-- 실행 뒤: php ops/blog_search_backfill.php  — 기존 글의 body_text를 한 번에 채운다
-- ============================================================

-- 1) 검색용 순수 텍스트 본문. content(HTML)에서 태그를 걷어낸 글자만 담는다.
--    본문 HTML을 그대로 LIKE 검색하면 태그 이름·이미지 주소에도 걸리기 때문에 따로 둔다.
--    글 저장·수정 시 src/api/admin/blog.php가 자동으로 다시 채운다(src/lib/blog_search.php의 blog_body_text()).
ALTER TABLE blog_posts
    ADD COLUMN body_text    MEDIUMTEXT NULL COMMENT '검색용 본문 — content에서 HTML 태그를 걷어낸 순수 텍스트(저장 시 자동 갱신)' AFTER content_en,
    ADD COLUMN body_text_en MEDIUMTEXT NULL COMMENT '검색용 영문 본문 — content_en에서 태그를 걷어낸 순수 텍스트(저장 시 자동 갱신)' AFTER body_text;

-- 2) 이표기 묶음. 같은 것을 다르게 쓰는 말(소슬↔솟을, 투밀이·퇴밀이↔두밀이 등)을 함께 찾기 위한 표.
--    검색어에 alias가 들어 있으면 canonical로도, canonical이 들어 있으면 그 alias들로도 함께 찾는다(양방향).
--    관리자 화면: 어드민 > 블로그 관리 > '검색 관리' (src/admin/blog_search.php)에서 추가·삭제.
--    주의: 서로 다른 것으로 확정된 말은 넣지 않는다(예: 갑창 ≠ 두껍닫이, 블로그 12화 「겹으로 다는 창」).
CREATE TABLE IF NOT EXISTS term_aliases (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    alias      VARCHAR(50)  NOT NULL COMMENT '다른 표기 (예: 소슬)',
    canonical  VARCHAR(50)  NOT NULL COMMENT '대표 표기 (예: 솟을) — 같은 대표 표기를 가진 alias끼리 한 묶음',
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_term_aliases_alias (alias),
    KEY idx_term_aliases_canonical (canonical)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='블로그 검색 이표기 묶음 (alias → canonical)';

INSERT IGNORE INTO term_aliases (alias, canonical) VALUES
    ('소슬',   '솟을'),
    ('투밀이', '두밀이'),
    ('퇴밀이', '두밀이'),
    ('띠살',   '세살'),
    ('影窓',   '映窓');

-- 3) 검색어 기록. 사람들이 무엇을 찾는지, 특히 결과 0건인 말이 무엇인지 보려는 용도.
--    관리자(슈퍼·관리자 권한)·봇의 검색은 기록하지 않는다(블로그 조회수 카운터와 같은 기준).
--    관리자 화면 '검색 관리'에 결과 0건 검색어를 횟수순으로 보여준다.
CREATE TABLE IF NOT EXISTS search_logs (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    query        VARCHAR(100) NOT NULL COMMENT '검색어 (앞뒤 공백 제거한 값)',
    result_count INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '그때 나온 결과 수 — 0이면 찾는 글이 없었다는 뜻',
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '검색 시각',
    PRIMARY KEY (id),
    KEY idx_search_logs_created (created_at),
    KEY idx_search_logs_zero (result_count, query)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='블로그 검색어 기록';
