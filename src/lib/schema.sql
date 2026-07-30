-- ============================================================
-- studio.pyeongmok.com 데이터베이스 스키마
-- ============================================================

-- 누락 인덱스 추가 (아래 두 줄을 DB에 직접 실행)
-- ALTER TABLE drawings ADD KEY idx_drawings_user_updated (user_id, updated_at);
-- ALTER TABLE drawing_versions ADD KEY idx_dv_drawing_saved (drawing_id, saved_at);

-- 기존 DB에 컬럼 추가할 경우 아래 ALTER 실행
-- ALTER TABLE cost_table ADD COLUMN engine       VARCHAR(20)       NULL                COMMENT '엔진명 (classic/square/…, NULL=공통)' AFTER category;
-- ALTER TABLE cost_table ADD COLUMN thickness_mm SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '부재 두께 mm (문틀만 사용, 살=slatT·울거미=frameW/H)' AFTER weight;
-- ALTER TABLE cost_table ADD COLUMN width_mm     SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '부재 폭 mm (울거미·살·문틀 공통)' AFTER thickness_mm;
-- 2026-07-08 hardware(철물) 카테고리 신설 — 기존 category 값(wood/oil/finish/delivery/labor/overhead)과 나란히 사용, 컬럼 추가 없음
-- INSERT INTO cost_table (category, engine, name, unit_price, unit, unit_name, weight, thickness_mm, width_mm, work_time_min, coat_count, notes, sort_order, is_active) VALUES
--     ('hardware', '', '여닫이 기본철물', 15000, '조', '', 1, 0, 0, 0, 1, '', 0, 1),
--     ('hardware', '', '미서기 기본철물', 25000, '조', '', 1, 0, 0, 0, 1, '', 1, 1);
-- ALTER TABLE drawings ADD COLUMN thumbnail   MEDIUMTEXT   NULL    COMMENT '썸네일 이미지 (data:image/jpeg;base64,…)' AFTER updated_at;
-- ALTER TABLE drawings ADD COLUMN work_time_sec INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '누적 작업 시간(초)' AFTER thumbnail;
-- ALTER TABLE users ADD COLUMN role ENUM('s','m','a','u') NOT NULL DEFAULT 'u' COMMENT '권한: s=슈퍼, m=관리자, a=작가, u=회원' AFTER email;
-- ALTER TABLE users ADD COLUMN last_login_at DATETIME NULL COMMENT '최종 접속일시' AFTER created_at;
-- ALTER TABLE users ADD COLUMN withdrawn_at DATETIME NULL COMMENT '탈퇴일시 (NULL=정상, NOT NULL=탈퇴)' AFTER last_login_at;
-- ALTER TABLE drawings ADD COLUMN locked_at DATETIME NULL COMMENT '잠금일시 (NULL=편집가능, 견적요청 중이면 잠김)' AFTER work_time_sec;
-- 2026-07-08 locked_at 폐지 — 도면 잠금을 orders.status에서 파생시키도록 전환(Drawing::lockedAtExpr 참고), 물리 컬럼 삭제
-- ALTER TABLE drawings DROP COLUMN locked_at;
-- ALTER TABLE drawings ADD COLUMN pattern_category VARCHAR(40) NULL DEFAULT NULL COMMENT '전통 창호 패턴 분류 — pattern_categories.code 참조' AFTER title;
-- pattern_categories 최초 설계는 (code, engine) 복합키였으나 실제로는 id 단일PK로 운영됨(2026-07-08 CREATE TABLE 블록으로 정정, 아래 참고)
-- ALTER TABLE blog_posts ADD COLUMN view_count INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '조회수 (방문자 쿠키 기준 24시간 중복 방지)' AFTER is_active;
-- ALTER TABLE library_patterns ADD COLUMN pattern_category INT UNSIGNED NULL DEFAULT NULL COMMENT '컬렉션 "모양" 필터용 분류 — pattern_categories.id 참조 (연결 도면의 분류와 별개)' AFTER drawing_id;
-- CREATE TABLE blog_series (id INT UNSIGNED NOT NULL AUTO_INCREMENT, name VARCHAR(80) NOT NULL, tagline VARCHAR(200) NOT NULL DEFAULT '', sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0, PRIMARY KEY (id), UNIQUE KEY uq_series_name (name)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='블로그 시리즈';
-- ALTER TABLE blog_posts ADD COLUMN series_id INT UNSIGNED NULL DEFAULT NULL COMMENT '블로그 시리즈 FK' AFTER slug;
-- ALTER TABLE blog_posts ADD COLUMN series_order SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '시리즈 내 순서' AFTER series_id;
-- ALTER TABLE blog_posts ADD COLUMN related_drawing_id INT UNSIGNED NULL DEFAULT NULL COMMENT '연관 대표 도면 ID (drawings.id), 딥링크용' AFTER series_order;
-- ALTER TABLE blog_posts ADD COLUMN related_engine VARCHAR(20) NULL DEFAULT NULL COMMENT '연관 엔진 (classic/square/cross/diamond/triangle/hexagon)' AFTER related_drawing_id;
-- ALTER TABLE blog_posts ADD COLUMN question VARCHAR(200) NOT NULL DEFAULT '' COMMENT '질문형 인덱스용 한 줄 질문' AFTER related_engine;
-- ALTER TABLE blog_series ADD COLUMN show_on_home TINYINT(1) NOT NULL DEFAULT 1 COMMENT '홈 인용 배너 노출 여부' AFTER tagline;
-- 2026-07-20 회원가입 약관·개인정보 동의 체크박스 추가에 따른 동의일시 기록 컬럼
-- ALTER TABLE users ADD COLUMN terms_agreed_at DATETIME NULL COMMENT '이용약관·개인정보처리방침 동의일시 (이메일 회원가입 시 체크박스 동의 기록, 소셜 로그인 가입은 NULL)' AFTER created_at;
-- ALTER TABLE users ADD COLUMN view_spec  TINYINT(1) NOT NULL DEFAULT 0 COMMENT '엔진 제작 시방서 열람 허용 (role과 별개, 회원별 개별 승인)' AFTER withdrawn_at;
-- ALTER TABLE users ADD COLUMN view_parts TINYINT(1) NOT NULL DEFAULT 0 COMMENT '엔진 부재목록 열람 허용 (role과 별개, 회원별 개별 승인)' AFTER view_spec;
-- ALTER TABLE users ADD COLUMN view_cost  TINYINT(1) NOT NULL DEFAULT 0 COMMENT '엔진 예산견적 상세내역 열람 허용 (role과 별개, 회원별 개별 승인)' AFTER view_parts;
-- ALTER TABLE users ADD COLUMN view_price    TINYINT(1) NOT NULL DEFAULT 0 COMMENT '엔진 예상가격 총액 열람 허용 (role과 별개, 회원별 개별 승인)' AFTER view_cost;
-- ALTER TABLE users ADD COLUMN view_leadtime TINYINT(1) NOT NULL DEFAULT 0 COMMENT '엔진 최소 납기 열람 허용 (role과 별개, 회원별 개별 승인)' AFTER view_price;
-- ALTER TABLE users ADD COLUMN view_shipping TINYINT(1) NOT NULL DEFAULT 0 COMMENT '엔진 배송비 안내문구 열람 허용 (role과 별개, 회원별 개별 승인)' AFTER view_leadtime;
-- ALTER TABLE users ADD COLUMN view_desc     TINYINT(1) NOT NULL DEFAULT 0 COMMENT '엔진 예상견적 설명(disclaimer) 열람 허용 (role과 별개, 회원별 개별 승인)' AFTER view_shipping;
-- ALTER TABLE users MODIFY COLUMN view_spec TINYINT(1) NOT NULL DEFAULT 1 COMMENT '엔진 제작 시방서 열람 허용 (role과 별개, 회원별 개별 승인) - 임시로 기본 1 (2026-07-07~)'; -- 기존 회원 전체도 UPDATE users SET view_spec=1로 함께 적용

-- 접속 통계 (1년 rolling)
CREATE TABLE IF NOT EXISTS page_views (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    visited_at  DATETIME     NOT NULL DEFAULT NOW() COMMENT '방문일시',
    page        VARCHAR(120) NOT NULL               COMMENT '페이지 경로',
    user_id     INT UNSIGNED NULL                   COMMENT '로그인 유저 ID (비회원 NULL)',
    ip_hash     CHAR(8)      NOT NULL               COMMENT 'IP MD5 앞 8자 (UV 계산용)',
    ip          VARCHAR(45)  NULL                   COMMENT '방문자 IP',
    ua_hash     CHAR(8)      NULL                   COMMENT 'User-Agent MD5 앞 8자 (동일 IP 내 UA 로테이션 위장 크롤러 탐지용)',
    is_mobile   TINYINT(1)   NOT NULL DEFAULT 0     COMMENT '모바일 여부',
    status_code SMALLINT UNSIGNED NOT NULL DEFAULT 200 COMMENT 'HTTP 응답 코드 (404 등 — 인기 페이지 집계에서 제외용)',
    PRIMARY KEY (id),
    KEY idx_pv_visited (visited_at),
    KEY idx_pv_date_page (visited_at, page),
    KEY idx_pv_ip_hash_time (ip_hash, visited_at),
    CONSTRAINT fk_pv_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='페이지 방문 통계 (1년 rolling)';
-- ============================================================

-- 2026-07-26 짧은 시간 내 같은 IP에서 UA가 바뀌는 위장 크롤러(UA 로테이션) 탐지용
-- ALTER TABLE page_views ADD COLUMN ua_hash CHAR(8) NULL COMMENT 'User-Agent MD5 앞 8자 (동일 IP 내 UA 로테이션 위장 크롤러 탐지용)' AFTER ip;
-- ALTER TABLE page_views ADD INDEX idx_pv_ip_hash_time (ip_hash, visited_at);

-- 사용자 테이블
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT COMMENT '사용자 고유 ID',
    email         VARCHAR(255)    NOT NULL COMMENT '이메일 (로그인 아이디)',
    role          ENUM('s','m','a','u') NOT NULL DEFAULT 'u' COMMENT '권한: s=슈퍼, m=관리자, a=작가, u=회원',
    name            VARCHAR(100) NULL COMMENT '이름',
    phone           VARCHAR(30)  NULL COMMENT '연락처',
    address         VARCHAR(255) NULL COMMENT '배송지 주소',
    zipcode         VARCHAR(10)  NULL COMMENT '배송지 우편번호',
    address_detail  VARCHAR(100) NULL COMMENT '배송지 상세주소',
    company         VARCHAR(100) NULL COMMENT '(구) 회사명 필드 — company_name으로 대체됨, 하위호환용으로 남아있음',
    password_hash VARCHAR(255)    NOT NULL COMMENT '비밀번호 해시 (bcrypt)',
    created_at    DATETIME        NOT NULL DEFAULT NOW() COMMENT '가입일시',
    terms_agreed_at DATETIME      NULL COMMENT '이용약관·개인정보처리방침 동의일시 (이메일 회원가입 시 체크박스 동의 기록, 소셜 로그인 가입은 NULL)',
    last_login_at DATETIME        NULL COMMENT '최종 접속일시',
    last_login_ip VARCHAR(45)     NULL COMMENT '최종 접속 IP',
    withdrawn_at  DATETIME        NULL COMMENT '탈퇴일시 (NULL=정상, NOT NULL=탈퇴)',
    company_name            VARCHAR(100) NULL COMMENT '회사명',
    company_biz_no          VARCHAR(20)  NULL COMMENT '사업자등록번호',
    company_ceo             VARCHAR(100) NULL COMMENT '대표자명',
    company_phone           VARCHAR(30)  NULL COMMENT '대표 연락처',
    company_zipcode         VARCHAR(10)  NULL COMMENT '회사 우편번호',
    company_address         VARCHAR(255) NULL COMMENT '회사 주소',
    company_address_detail  VARCHAR(100) NULL COMMENT '회사 상세주소',
    company_biz_type        VARCHAR(100) NULL COMMENT '업태',
    company_biz_category    VARCHAR(100) NULL COMMENT '업종',
    view_spec     TINYINT(1)      NOT NULL DEFAULT 1 COMMENT '엔진 제작 시방서 열람 허용 (role과 별개, 회원별 개별 승인) - 임시로 기본 1 (2026-07-07~)',
    view_parts    TINYINT(1)      NOT NULL DEFAULT 0 COMMENT '엔진 부재목록 열람 허용 (role과 별개, 회원별 개별 승인)',
    view_cost     TINYINT(1)      NOT NULL DEFAULT 0 COMMENT '엔진 예산견적 상세내역 열람 허용 (role과 별개, 회원별 개별 승인)',
    view_price    TINYINT(1)      NOT NULL DEFAULT 0 COMMENT '엔진 예상가격 총액 열람 허용 (role과 별개, 회원별 개별 승인)',
    view_leadtime TINYINT(1)      NOT NULL DEFAULT 0 COMMENT '엔진 최소 납기 열람 허용 (role과 별개, 회원별 개별 승인)',
    view_shipping TINYINT(1)      NOT NULL DEFAULT 0 COMMENT '엔진 배송비 안내문구 열람 허용 (role과 별개, 회원별 개별 승인)',
    view_desc     TINYINT(1)      NOT NULL DEFAULT 0 COMMENT '엔진 예상견적 설명(disclaimer) 열람 허용 (role과 별개, 회원별 개별 승인)',
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='회원 정보';

-- 도면 테이블 (유저 + 타입 + 제목 단위로 1건)
-- 같은 유저가 같은 타입의 도면을 여러 제목으로 저장 가능
-- 제목(title)은 버전과 독립적으로 수정 가능 (버전 생성 없이 UPDATE)
CREATE TABLE IF NOT EXISTS drawings (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT COMMENT '도면 고유 ID',
    user_id     INT UNSIGNED    NOT NULL COMMENT '소유 사용자 ID (users.id FK)',
    type        VARCHAR(64)     NOT NULL COMMENT '도면 종류 (예: classic, square, diamond, cross, triangle)',
    title       VARCHAR(100)    NOT NULL DEFAULT '' COMMENT '도면 제목 (유저가 직접 지정, 버전과 독립 관리)',
    pattern_category VARCHAR(40) NULL DEFAULT NULL COMMENT '전통 창호 패턴 분류 — pattern_categories.id를 문자열로 저장(CAST(... AS UNSIGNED)로 조인)',
    is_shared   TINYINT(1)      NOT NULL DEFAULT 0 COMMENT '공유 링크 on/off — 켜면 로그인 없이 drawing_id로 열람 가능(원본 실시간 반영, 뷰어는 fork로만 저장 가능)',
    created_at  DATETIME        NOT NULL DEFAULT NOW() COMMENT '최초 작성일시',
    updated_at    DATETIME        NOT NULL DEFAULT NOW() ON UPDATE NOW() COMMENT '최종 저장일시',
    thumbnail     MEDIUMTEXT      NULL COMMENT '썸네일 이미지 — 실제로는 base64가 아니라 /uploads/drawing_thumbs/ 아래 저장된 정적 파일의 공개 경로 문자열 (Drawing::persistThumbnail 참고)',
    work_time_sec INT UNSIGNED    NOT NULL DEFAULT 0 COMMENT '누적 작업 시간(초)',
    PRIMARY KEY (id),
    UNIQUE KEY uq_drawings_user_type_title (user_id, type, title),
    KEY idx_drawings_user_type (user_id, type),
    CONSTRAINT fk_drawings_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='도면 메타 정보 (제목별로 독립 관리)';

-- 전통 창호 패턴 분류 (정자살/완자살 등). drawings.pattern_category(코드 문자열)가 이 테이블의 id를 참조
-- ALTER TABLE pattern_categories ADD COLUMN code CHAR(3) NULL DEFAULT NULL COMMENT '평목 컬렉션 코드 체계 v1.0 계열 코드 (예: JEO, WAN) — 한글 첫 음절 로마자, library_patterns.slug 생성에 사용. 12계열 외 관리자 자유입력 카테고리는 NULL 허용' AFTER name, ADD UNIQUE KEY uq_pattern_categories_code (code)
-- ALTER TABLE works ADD COLUMN engine_key VARCHAR(20) NULL DEFAULT NULL COMMENT '이 작품에 쓰인 엔진 (classic/square/cross/diamond/triangle/hexagon), NULL=미지정 — 포트폴리오 카드 호버 아이콘에 사용' AFTER desc_color
CREATE TABLE IF NOT EXISTS pattern_categories (
    id         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    name       VARCHAR(40)      NOT NULL,
    code       CHAR(3)          NULL     DEFAULT NULL COMMENT '평목 컬렉션 코드 체계 v1.0 계열 코드 (예: JEO, WAN) — library_patterns.slug 생성에 사용. NULL이면 코드화 안 된 자유입력 카테고리',
    sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
    is_active  TINYINT(1)       NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pattern_categories_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='전통 창호 패턴 카테고리';

-- 평목 컬렉션 코드 체계 v1.0 (2026-07-13 확정) — 계열 12종 시드. code(unique)로 매칭되므로 재실행해도 안전(중복 삽입 없음)
INSERT INTO pattern_categories (name, code, sort_order) VALUES
('띠살',      'TTI', 1),
('귀갑살',    'GWI', 2),
('정자살',    'JEO', 3),
('범살',      'BEO', 4),
('완자살',    'WAN', 5),
('솟을살',    'SOT', 6),
('아자살',    'AJA', 7),
('숫대살',    'SUT', 8),
('빗살',      'BIT', 9),
('용자살',    'YON', 10),
('꽃살',      'KOT', 11),
('자체 창작', 'PYM', 12)
ON DUPLICATE KEY UPDATE code = VALUES(code);

-- 평목 컬렉션 코드 체계 v1.0 수식어(2자) 목록 — 계열 안에서 세부 구분용 (예: JEO-SE-001).
-- library_patterns 생성 시 어드민이 이 목록 중에서만 고를 수 있다 (자유 입력 아님).
CREATE TABLE IF NOT EXISTS pattern_modifiers (
    id         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    name       VARCHAR(40)      NOT NULL,
    code       CHAR(2)          NOT NULL COMMENT '한글 첫 음절 로마자 2자 (예: SE=세모, YU=육모)',
    sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
    is_active  TINYINT(1)       NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pattern_modifiers_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='평목 컬렉션 코드 체계 v1.0 수식어 목록';

INSERT INTO pattern_modifiers (name, code, sort_order) VALUES
('세모', 'SE', 1),
('육모', 'YU', 2),
('새살', 'PM', 3),
('쇼지', 'JS', 4),
('쿠미꼬', 'JK', 5)
ON DUPLICATE KEY UPDATE code = VALUES(code);

-- 문의 메일 발송 이력 (rate limit용)
CREATE TABLE IF NOT EXISTS contact_log (
    id       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ip_hash  CHAR(8)      NOT NULL,
    name     VARCHAR(50)  NOT NULL DEFAULT '',
    email    VARCHAR(100) NOT NULL DEFAULT '',
    subject  VARCHAR(100) NOT NULL DEFAULT '',
    sent_at  DATETIME     NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    KEY idx_contact_ip_sent (ip_hash, sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='문의 메일 발송 이력';

-- blog_posts 테이블은 src/blog/index.php의 CREATE TABLE IF NOT EXISTS로 관리됨.
-- 2026-07-04 시맨틱 URL(slug) 지원 추가 (기존 DB):
-- ALTER TABLE blog_posts ADD COLUMN slug VARCHAR(200) NOT NULL DEFAULT '' AFTER title;
-- (기존 행 slug 백필 후) ALTER TABLE blog_posts ADD UNIQUE KEY uq_blog_posts_slug (slug);
-- 2026-07-04 하단 CTA 문구 (기존 DB):
-- ALTER TABLE blog_posts ADD COLUMN cta_text VARCHAR(200) NOT NULL DEFAULT '' AFTER summary;

-- 배경 이미지 테이블 (엔진별 사용자 업로드 배경)
-- 기존 DB에 컬럼 추가:
-- ALTER TABLE wallpapers ADD COLUMN version_saved_at INT UNSIGNED NULL COMMENT '소속 버전 savedAt (Unix초)' AFTER drawing_id;
-- ALTER TABLE wallpapers ADD KEY idx_wallpapers_version (drawing_id, version_saved_at);
CREATE TABLE IF NOT EXISTS wallpapers (
    id               INT UNSIGNED    NOT NULL AUTO_INCREMENT COMMENT '배경 고유 ID',
    user_id          INT UNSIGNED    NOT NULL COMMENT '소유 사용자 ID (users.id FK)',
    engine           VARCHAR(64)     NOT NULL DEFAULT '' COMMENT '엔진 구분 (예: classic, square, diamond, cross, triangle)',
    drawing_id       INT UNSIGNED    NULL COMMENT '소속 도면 ID (drawings.id FK, nullable)',
    filename         VARCHAR(255)    NOT NULL DEFAULT '' COMMENT '원본 파일명',
    filepath         VARCHAR(500)    NOT NULL DEFAULT '' COMMENT '저장 파일 경로 (/uploads/wallpapers/…)',
    created_at       DATETIME        NOT NULL DEFAULT NOW() COMMENT '업로드 일시',
    PRIMARY KEY (id),
    KEY idx_wallpapers_user_engine (user_id, engine),
    KEY idx_wallpapers_drawing (drawing_id),
    CONSTRAINT fk_wallpapers_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_wallpapers_drawing FOREIGN KEY (drawing_id) REFERENCES drawings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='엔진 배경 이미지';

CREATE TABLE IF NOT EXISTS renders (
    id         INT UNSIGNED    NOT NULL AUTO_INCREMENT COMMENT '렌더링 고유 ID',
    user_id    INT UNSIGNED    NOT NULL COMMENT '소유 사용자 ID (users.id FK)',
    engine     VARCHAR(20)     NOT NULL COMMENT '엔진 구분 (classic/square/cross/diamond/triangle/hexagon)',
    filepath   VARCHAR(500)    NOT NULL COMMENT '저장 파일 경로 (/uploads/renders/…)',
    created_at DATETIME        NOT NULL DEFAULT NOW() COMMENT '렌더링 완료 일시',
    PRIMARY KEY (id),
    KEY idx_renders_user_created (user_id, created_at),
    CONSTRAINT fk_renders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI 렌더링 결과 이미지 (사용자당 최대 300장)';

-- 페이지 SEO 메타데이터
CREATE TABLE IF NOT EXISTS page_meta (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    path        VARCHAR(120)  NOT NULL COMMENT 'URL 경로 (PHP_SELF 기준)',
    title       VARCHAR(200)  NOT NULL DEFAULT '',
    description VARCHAR(320)  NOT NULL DEFAULT '',
    keywords    VARCHAR(500)  NOT NULL DEFAULT '',
    og_image    VARCHAR(500)  NOT NULL DEFAULT '',
    updated_at  DATETIME      NOT NULL DEFAULT NOW() ON UPDATE NOW(),
    PRIMARY KEY (id),
    UNIQUE KEY uq_meta_path (path)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='페이지 SEO 메타데이터';

-- 라이브러리 패턴 카드
-- (기존 library_categories 테이블을 대체: RENAME TABLE library_categories TO library_patterns; 후 컬럼 추가)
CREATE TABLE IF NOT EXISTS library_patterns (
    id          INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    slug        VARCHAR(60)       NOT NULL DEFAULT '' COMMENT '시맨틱 URL slug. pattern_category가 코드화된 계열(pattern_categories.code)이면 평목 컬렉션 코드 체계 v1.0 형식으로 생성됨: {계열3자}(-{수식어2자})?-{일련번호3자리} 소문자 (예: jeo-001, wan-gb-002). 미분류거나 코드 없는 카테고리면 랜덤 hex 유지. 생성 시점에 확정, 이후 카테고리/수식어를 바꿔도 재생성 안 함(공유 URL 보존)',
    name_ko     VARCHAR(80)       NOT NULL DEFAULT '' COMMENT '패턴 이름 (예: 정자살)',
    drawing_id  INT UNSIGNED      NULL               COMMENT '연결 도면 (drawings.id FK)',
    pattern_category INT UNSIGNED NULL              COMMENT '컬렉션 "모양" 필터용 분류 — pattern_categories.id 참조',
    image_path  VARCHAR(500)      NOT NULL DEFAULT '' COMMENT '대표 이미지 경로 (/uploads/library/…)',
    sort_order  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active   TINYINT(1)        NOT NULL DEFAULT 1,
    created_at  DATETIME          NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    UNIQUE KEY uq_cat_slug (slug),
    KEY idx_lp_drawing (drawing_id),
    CONSTRAINT fk_lp_drawing FOREIGN KEY (drawing_id) REFERENCES drawings(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='라이브러리 패턴 카드';

-- 라이브러리 패턴 좋아요 (개인별)
CREATE TABLE IF NOT EXISTS library_likes (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    INT UNSIGNED NOT NULL,
    pattern_id INT UNSIGNED NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    UNIQUE KEY uq_like (user_id, pattern_id),
    KEY idx_like_user (user_id),
    CONSTRAINT fk_like_user    FOREIGN KEY (user_id)    REFERENCES users(id)            ON DELETE CASCADE,
    CONSTRAINT fk_like_pattern FOREIGN KEY (pattern_id) REFERENCES library_patterns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='라이브러리 패턴 좋아요';

-- 라이브러리 패턴 키워드 (검색 + 카테고리 필터 통합)
CREATE TABLE IF NOT EXISTS library_keywords (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    pattern_id  INT UNSIGNED NOT NULL COMMENT 'library_patterns.id FK',
    keyword     VARCHAR(60)  NOT NULL COMMENT '키워드 하나 (예: 중문, geosil, 현관문)',
    PRIMARY KEY (id),
    KEY idx_lkw_pattern (pattern_id),
    KEY idx_lkw_keyword (keyword),
    CONSTRAINT fk_lkw_pattern FOREIGN KEY (pattern_id) REFERENCES library_patterns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='라이브러리 패턴 검색/필터 키워드';

-- 사용자 보드 (컬렉션 패턴 모음)
CREATE TABLE IF NOT EXISTS boards (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    INT UNSIGNED NOT NULL,
    name       VARCHAR(100) NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    KEY idx_boards_user (user_id),
    CONSTRAINT fk_boards_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='사용자 보드';

-- 보드 패턴 항목
CREATE TABLE IF NOT EXISTS board_items (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    board_id   INT UNSIGNED NOT NULL,
    pattern_id INT UNSIGNED NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    UNIQUE KEY uq_board_item (board_id, pattern_id),
    KEY idx_bi_board (board_id),
    CONSTRAINT fk_bi_board   FOREIGN KEY (board_id)   REFERENCES boards(id)           ON DELETE CASCADE,
    CONSTRAINT fk_bi_pattern FOREIGN KEY (pattern_id) REFERENCES library_patterns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='보드 패턴 항목';

-- 메인 페이지 공간 카드
CREATE TABLE IF NOT EXISTS space_cards (
    id               INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    label            VARCHAR(50)       NOT NULL DEFAULT '' COMMENT '카드 라벨 (예: 중문)',
    image_url        VARCHAR(500)      NOT NULL DEFAULT '' COMMENT '이미지 URL 또는 /uploads/ 경로',
    collection_query VARCHAR(100)      NOT NULL DEFAULT '' COMMENT '컬렉션 검색어 (?q=)',
    sort_order       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active        TINYINT(1)        NOT NULL DEFAULT 1,
    created_at       DATETIME          NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    KEY idx_sc_sort (sort_order, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='메인 페이지 공간 카드';

INSERT IGNORE INTO space_cards (id, label, image_url, collection_query, sort_order) VALUES
(1,  '중문',   'https://picsum.photos/seed/sp01/600/400', '중문',   0),
(2,  '거실',   'https://picsum.photos/seed/sp02/600/400', '거실',   1),
(3,  '카페',   'https://picsum.photos/seed/sp03/600/400', '카페',   2),
(4,  '침실',   'https://picsum.photos/seed/sp04/600/400', '침실',   3),
(5,  '서재',   'https://picsum.photos/seed/sp05/600/400', '서재',   4),
(6,  '현관',   'https://picsum.photos/seed/sp06/600/400', '현관',   5),
(7,  '다실',   'https://picsum.photos/seed/sp07/600/400', '다실',   6),
(8,  '한옥',   'https://picsum.photos/seed/sp08/600/400', '한옥',   7),
(9,  '주방',   'https://picsum.photos/seed/sp09/600/400', '주방',   8),
(10, '갤러리', 'https://picsum.photos/seed/sp10/600/400', '갤러리', 9);

-- 메인 히어로 캐러셀 슬라이드
CREATE TABLE IF NOT EXISTS hero_slides (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title     VARCHAR(120) NOT NULL DEFAULT '',
    subtitle  VARCHAR(255) NOT NULL DEFAULT '',
    image_url VARCHAR(512) NOT NULL DEFAULT '',
    sort_order SMALLINT    NOT NULL DEFAULT 0,
    is_active TINYINT(1)   NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='메인 페이지 최상단 히어로 캐러셀 슬라이드';

-- 메인 페이지 "스튜디오 소개" 카드 (엔진별 1장, engine_key로 매칭)
CREATE TABLE IF NOT EXISTS studio_cards (
    id          INT          NOT NULL AUTO_INCREMENT,
    engine_key  VARCHAR(20)  NOT NULL COMMENT '엔진명 (classic/square/cross/diamond/triangle/hexagon)',
    title       VARCHAR(100) NOT NULL,
    description TEXT         NULL,
    image_url   VARCHAR(500) NULL,
    sort_order  INT          NOT NULL DEFAULT 0,
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY engine_key (engine_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='메인 페이지 "스튜디오 소개" 6개 엔진 카드 (엔진당 1행, engine_key로 고정 매칭)';

-- 컬렉션 페이지 "공간" 드롭다운 필터는 우리살/새살/일본살 3분류 체계로 대체되어 폐지됨.
-- 기존 collection_space_filters 테이블은 운영 DB에 남아있으나(수동 정리 필요 시 별도 DROP) 신규 설치 시 더는 생성하지 않음.

-- 사이트 설정 (OAuth 키 등)
CREATE TABLE IF NOT EXISTS site_config (
    key_name   VARCHAR(80) NOT NULL,
    value      TEXT        NOT NULL COMMENT 'TEXT 컬럼이라 DEFAULT 지정 불가 (MySQL 제약) — INSERT 시 항상 명시적으로 값을 넣어야 함',
    updated_at DATETIME    NOT NULL DEFAULT NOW() ON UPDATE NOW(),
    PRIMARY KEY (key_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='사이트 설정 (OAuth 키 등)';

-- 범용 key/value 사이트 설정 (site_config와 별도)
CREATE TABLE IF NOT EXISTS site_settings (
    setting_key   VARCHAR(100) NOT NULL,
    setting_value VARCHAR(500) NOT NULL DEFAULT '',
    PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='범용 key/value 사이트 설정';

-- SNS OAuth 연결 이력
CREATE TABLE IF NOT EXISTS user_oauth (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED NOT NULL,
    provider    VARCHAR(20)  NOT NULL COMMENT 'google|kakao|naver',
    created_at  DATETIME     NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    UNIQUE KEY uq_oauth (user_id, provider),
    KEY idx_oauth_user (user_id),
    CONSTRAINT fk_oauth_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='SNS OAuth 연결 이력';

-- 비밀번호 재설정 토큰
CREATE TABLE IF NOT EXISTS password_resets (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    token      CHAR(64)     NOT NULL COMMENT 'bin2hex(random_bytes(32))',
    user_id    INT UNSIGNED NOT NULL,
    expires_at DATETIME     NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    UNIQUE KEY uq_pr_token (token),
    KEY idx_pr_user (user_id),
    CONSTRAINT fk_pr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='비밀번호 재설정 토큰 (1시간 유효)';

-- 도면 버전 테이블 (한 도면의 저장 이력)
CREATE TABLE IF NOT EXISTS drawing_versions (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT COMMENT '버전 고유 ID',
    drawing_id  INT UNSIGNED    NOT NULL COMMENT '소속 도면 ID (drawings.id FK)',
    params      JSON            NOT NULL COMMENT '설계 파라미터 (JSON)',
    saved_at    DATETIME        NOT NULL DEFAULT NOW() COMMENT '버전 저장일시',
    PRIMARY KEY (id),
    KEY idx_drawing (drawing_id),
    KEY idx_dv_drawing_saved (drawing_id, saved_at),
    CONSTRAINT fk_drawing_versions_drawing FOREIGN KEY (drawing_id) REFERENCES drawings (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='도면 버전 이력';

-- 도면에 첨부되는 개별 리소스(문양/배경 등 원본 자산). type으로 종류 구분
CREATE TABLE IF NOT EXISTS drawing_assets (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    drawing_id INT UNSIGNED NOT NULL,
    type       VARCHAR(30)  NOT NULL COMMENT '자산 종류',
    src        MEDIUMTEXT   NOT NULL COMMENT '자산 데이터 (data URI 등)',
    created_at DATETIME     NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    KEY idx_drawing_type (drawing_id, type),
    CONSTRAINT drawing_assets_ibfk_1 FOREIGN KEY (drawing_id) REFERENCES drawings (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='도면에 첨부된 개별 자산(문양/배경 등) — drawing_versions.params의 JSON 스냅샷과 별개로 원본을 보관';

-- 도면 PNG/PDF 내보내기 로그 (mypage 도면관리 "내보내기 내역" 모달에서 조회)
CREATE TABLE IF NOT EXISTS drawing_export_logs (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id      INT UNSIGNED NOT NULL,
    drawing_id   INT UNSIGNED NULL,
    engine       VARCHAR(20)  NOT NULL DEFAULT '',
    format       ENUM('png','pdf') NOT NULL,
    drawing_name VARCHAR(100) NOT NULL DEFAULT '',
    version      VARCHAR(10)  NOT NULL DEFAULT '',
    created_at   DATETIME     NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    KEY idx_del_user (user_id, created_at),
    KEY idx_del_drawing (drawing_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='도면 PNG/PDF 내보내기 로그';

-- ── 컬러 스와치 ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS color_swatches (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    group_name  VARCHAR(50)  NOT NULL COMMENT '그룹명 (예: 스테인, 천연오일)',
    sort_order  SMALLINT     NOT NULL DEFAULT 0 COMMENT '그룹 내 정렬 순서',
    code        VARCHAR(20)  NOT NULL COMMENT '색상 코드 (예: 930-00)',
    name        VARCHAR(50)  NOT NULL COMMENT '색상 이름',
    hex         CHAR(7)      NOT NULL COMMENT '헥스 코드 (#rrggbb)',
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    KEY idx_color_group (group_name, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='엔진 공통 컬러 팔레트';

-- 완성 작품 갤러리
-- 2026-07-04 시맨틱 URL(slug) 지원 추가 (기존 DB):
-- ALTER TABLE works ADD COLUMN slug VARCHAR(200) NOT NULL DEFAULT '' AFTER title;
-- (기존 행 slug 백필 후) ALTER TABLE works ADD UNIQUE KEY uq_works_slug (slug);
CREATE TABLE IF NOT EXISTS works (
    id          INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    title       VARCHAR(100)      NOT NULL DEFAULT '',
    slug        VARCHAR(200)      NOT NULL DEFAULT '',
    description VARCHAR(300)      NOT NULL DEFAULT '',
    image_url   VARCHAR(500)      NOT NULL DEFAULT '',
    sort_order  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active   TINYINT(1)        NOT NULL DEFAULT 1,
    created_at  DATETIME          NOT NULL DEFAULT NOW(),
    panel_bg    VARCHAR(20)       NOT NULL DEFAULT '#111111' COMMENT '카드 배경색',
    title_color VARCHAR(20)       NOT NULL DEFAULT '#ffffff' COMMENT '카드 제목 색상',
    desc_color  VARCHAR(20)       NOT NULL DEFAULT '#888888' COMMENT '카드 설명 색상',
    engine_key  VARCHAR(20)       NULL     DEFAULT NULL COMMENT '이 작품에 쓰인 엔진 (classic/square/cross/diamond/triangle/hexagon), NULL=미지정 — 포트폴리오 카드 호버 아이콘에 사용',
    PRIMARY KEY (id),
    UNIQUE KEY uq_works_slug (slug),
    KEY idx_works_sort (sort_order, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='완성 작품 갤러리';

-- 작품 상세 이미지 (works.id 참조, FK 제약은 없음 — 애플리케이션 레벨로만 연결)
CREATE TABLE IF NOT EXISTS work_images (
    id         INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    work_id    INT UNSIGNED      NOT NULL,
    image_url  VARCHAR(500)      NOT NULL DEFAULT '',
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_work_images_work (work_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='작품 상세 페이지에 나열되는 추가 이미지 (works.image_url은 대표 이미지 1장, 이 테이블은 상세 갤러리)';

-- 작품 태그 목록. src/api/admin/work_tags.php가 자체적으로 CREATE TABLE IF NOT EXISTS를 실행해 부트스트랩하므로
-- 여기 없어도 동작하지만, DB 전체 구조 문서화를 위해 함께 기록
CREATE TABLE IF NOT EXISTS work_tags (
    id         INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    name       VARCHAR(50)       NOT NULL DEFAULT '',
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active  TINYINT(1)        NOT NULL DEFAULT 1,
    created_at DATETIME          NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    KEY idx_wt_sort (sort_order, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='작품 갤러리 필터용 태그 목록 (works와 직접 FK 연결 없이 어드민 화면 필터 UI용으로만 존재)';

-- FAQ
CREATE TABLE IF NOT EXISTS faqs (
    id          INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    question    VARCHAR(255)      NOT NULL DEFAULT '' COMMENT '질문',
    answer      TEXT              NOT NULL COMMENT '답변',
    sort_order  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active   TINYINT(1)        NOT NULL DEFAULT 1,
    show_on_main TINYINT(1)       NOT NULL DEFAULT 0 COMMENT '메인페이지 노출',
    created_at  DATETIME          NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    KEY idx_faqs_sort (sort_order, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='자주 묻는 질문';

-- 초기 데이터 (스테인)
INSERT IGNORE INTO color_swatches (group_name, sort_order, code, name, hex) VALUES
('스테인', 1,  '930-00', '투명',        '#dec898'),
('스테인', 2,  '930-01', '노랑',        '#f2aa00'),
('스테인', 3,  '930-02', '오렌지',      '#e05218'),
('스테인', 4,  '930-04', '레드브라운',  '#7a1e08'),
('스테인', 5,  '930-05', '황토브라운',  '#906020'),
('스테인', 6,  '930-06', '밤색/브라운', '#5a2e10'),
('스테인', 7,  '930-08', '녹색',        '#2c7030'),
('스테인', 8,  '930-10', '흑단',        '#222218'),
('스테인', 9,  '930-11', '회색',        '#888885'),
-- 천연오일
('천연오일', 1, 'NO-01', '자연',    '#e2c98a'),
('천연오일', 2, 'NO-02', '소나무',  '#c8952a'),
('천연오일', 3, 'NO-03', '참나무',  '#a06828'),
('천연오일', 4, 'NO-04', '느티나무','#8c4e22'),
('천연오일', 5, 'NO-05', '호두',    '#6a3518'),
('천연오일', 6, 'NO-06', '체리',    '#7a2e18'),
('천연오일', 7, 'NO-07', '황칠',    '#b8880a'),
('천연오일', 8, 'NO-08', '옻칠',    '#1c0c06'),
('천연오일', 9, 'NO-09', '먹',      '#28241e');

-- 기존 DB에 컬럼 추가할 경우 아래 ALTER 실행 (orders 테이블에 납기/배송/썸네일/버전 필드 추가)
-- ALTER TABLE orders ADD COLUMN version_label       VARCHAR(50)  NULL COMMENT '주문 시점 도면 버전 표시 텍스트' AFTER title;
-- ALTER TABLE orders ADD COLUMN thumbnail            MEDIUMTEXT   NULL COMMENT '주문 시점 도면 썸네일 (data:image/jpeg;base64,…)' AFTER version_label;
-- ALTER TABLE orders ADD COLUMN due_date             DATE         NULL COMMENT '납기 희망일' AFTER company_name;
-- ALTER TABLE orders ADD COLUMN ship_zipcode         VARCHAR(10)  NULL COMMENT '배송지 우편번호' AFTER due_date;
-- ALTER TABLE orders ADD COLUMN ship_address         VARCHAR(255) NULL COMMENT '배송지 주소' AFTER ship_zipcode;
-- ALTER TABLE orders ADD COLUMN ship_address_detail  VARCHAR(100) NULL COMMENT '배송지 상세주소' AFTER ship_address;
-- ALTER TABLE orders ADD COLUMN ship_phone           VARCHAR(30)  NULL COMMENT '배송지 연락처' AFTER ship_address_detail;
-- ALTER TABLE orders ADD COLUMN estimated_price       DECIMAL(12,2) NULL COMMENT '주문 시점 클라이언트 실시간 추정가 (참고용)' AFTER spec_json;
-- ALTER TABLE orders ADD COLUMN final_price           DECIMAL(12,2) NULL COMMENT '서버가 계산/확정한 공식 가격' AFTER estimated_price;
-- ALTER TABLE orders ADD COLUMN price_breakdown       MEDIUMTEXT    NULL COMMENT '항목별 가격 산출 내역 (JSON)' AFTER final_price;
-- ALTER TABLE orders ADD COLUMN price_formula_version VARCHAR(20)   NULL COMMENT '계산에 사용된 가격 공식 버전' AFTER price_breakdown;
-- 2026-07-08 가격 자동계산 공식 확정 전까지, 관리자가 고객과 협의해 확정한 가격을 수기로 입력하는 용도
-- ALTER TABLE orders ADD COLUMN price_note TEXT NULL COMMENT '가격 협의 메모(관리자가 고객과 협의한 확정가격 근거)' AFTER final_price;
-- 2026-07-08 주문 상태 모델 확장(견적검토~배송완료 9단계) — 도면 잠금은 status에서 파생(src/lib/drawing.php 참고)
-- ALTER TABLE orders MODIFY COLUMN status ENUM(
--     'pending_review','revision_requested','approved','quote_finalized',
--     'deposit_paid','in_production','production_done','shipped','delivered','cancelled'
-- ) NOT NULL DEFAULT 'pending_review' COMMENT '처리 상태';
-- ALTER TABLE orders ADD COLUMN reviewed_by      INT UNSIGNED NULL COMMENT '검토한 관리자 user_id' AFTER status;
-- ALTER TABLE orders ADD COLUMN reviewed_at      DATETIME     NULL COMMENT '검토 시각' AFTER reviewed_by;
-- ALTER TABLE orders ADD COLUMN revision_note    TEXT         NULL COMMENT '수정요청 사유(관리자->고객)' AFTER reviewed_at;
-- ALTER TABLE orders ADD COLUMN tracking_carrier VARCHAR(50)  NULL COMMENT '택배사' AFTER revision_note;
-- ALTER TABLE orders ADD COLUMN tracking_number  VARCHAR(50)  NULL COMMENT '운송장번호' AFTER tracking_carrier;
-- ALTER TABLE orders ADD COLUMN shipped_at       DATETIME     NULL COMMENT '발송 시각' AFTER tracking_number;
-- ALTER TABLE orders ADD COLUMN delivered_at     DATETIME     NULL COMMENT '배송완료 시각' AFTER shipped_at;
-- 2026-07-11 도면 공유 링크 기능 — 소유자가 켜면 로그인 없이 drawing_id로 열람 가능(뷰어는 캔버스 편집 가능하나 서버엔 저장 못하고, 저장하려면 로그인 후 새 도면으로 fork됨)
-- ALTER TABLE drawings ADD COLUMN is_shared TINYINT(1) NOT NULL DEFAULT 0 COMMENT '공유 링크 on/off — 켜면 로그인 없이 drawing_id로 열람 가능(원본 실시간 반영, 뷰어는 fork로만 저장 가능)' AFTER pattern_category;

-- 제작 주문 (엔진 페이지의 "주문" 버튼에서 생성)
-- customer_name/customer_phone/company_name은 주문 시점 users 테이블 값의 스냅샷
-- (이후 프로필이 바뀌어도 주문 당시 정보가 보존되도록)
CREATE TABLE IF NOT EXISTS orders (
    id                  INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '주문 고유 ID',
    user_id             INT UNSIGNED NOT NULL COMMENT '주문자 user_id (users.id FK)',
    drawing_id          INT UNSIGNED NULL COMMENT '연결된 도면 ID (drawings.id FK, 저장 전 도면이면 NULL)',
    engine              VARCHAR(64)  NOT NULL COMMENT '엔진 종류 (square/classic/cross/diamond/triangle/hexagon)',
    title               VARCHAR(100) NOT NULL DEFAULT '' COMMENT '주문 시점 도면 제목',
    version_label       VARCHAR(50)  NULL COMMENT '주문 시점 도면 버전 표시 텍스트',
    thumbnail           MEDIUMTEXT   NULL COMMENT '주문 시점 도면 썸네일 (data:image/jpeg;base64,…)',
    customer_name       VARCHAR(100) NOT NULL COMMENT '주문 시점 이름 (스냅샷)',
    customer_phone      VARCHAR(30)  NOT NULL COMMENT '주문 시점 연락처 (스냅샷)',
    company_name        VARCHAR(100) NULL COMMENT '주문 시점 회사명 (스냅샷)',
    due_date            DATE         NULL COMMENT '납기 희망일',
    ship_zipcode        VARCHAR(10)  NULL COMMENT '배송지 우편번호',
    ship_address        VARCHAR(255) NULL COMMENT '배송지 주소',
    ship_address_detail VARCHAR(100) NULL COMMENT '배송지 상세주소',
    ship_phone          VARCHAR(30)  NULL COMMENT '배송지 연락처',
    memo                TEXT         NULL COMMENT '요청사항',
    spec_json           MEDIUMTEXT   NULL COMMENT '주문 시점 도면 사양 스냅샷 (JSON)',
    estimated_price     DECIMAL(12,2) NULL COMMENT '주문 시점 클라이언트 실시간 추정가 (참고용)',
    final_price         DECIMAL(12,2) NULL COMMENT '서버가 계산/확정한 공식 가격',
    price_note          TEXT         NULL COMMENT '가격 협의 메모(관리자가 고객과 협의한 확정가격 근거)',
    price_breakdown     MEDIUMTEXT   NULL COMMENT '항목별 가격 산출 내역 (JSON)',
    price_formula_version VARCHAR(20) NULL COMMENT '계산에 사용된 가격 공식 버전',
    status              ENUM(
        'pending_review','revision_requested','approved','quote_finalized',
        'deposit_paid','in_production','production_done','shipped','delivered','cancelled'
    ) NOT NULL DEFAULT 'pending_review' COMMENT '처리 상태 (도면 잠금은 이 값에서 파생됨, src/lib/drawing.php)',
    reviewed_by         INT UNSIGNED NULL COMMENT '검토한 관리자 user_id',
    reviewed_at         DATETIME     NULL COMMENT '검토 시각',
    revision_note       TEXT         NULL COMMENT '수정요청 사유(관리자->고객)',
    tracking_carrier    VARCHAR(50)  NULL COMMENT '택배사',
    tracking_number     VARCHAR(50)  NULL COMMENT '운송장번호',
    shipped_at          DATETIME     NULL COMMENT '발송 시각',
    delivered_at        DATETIME     NULL COMMENT '배송완료 시각',
    created_at          DATETIME     NOT NULL DEFAULT NOW() COMMENT '주문 접수일시',
    PRIMARY KEY (id),
    KEY idx_orders_user (user_id),
    KEY idx_orders_status_created (status, created_at),
    CONSTRAINT fk_orders_user     FOREIGN KEY (user_id)    REFERENCES users (id)    ON DELETE CASCADE,
    CONSTRAINT fk_orders_drawing FOREIGN KEY (drawing_id) REFERENCES drawings (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='제작 주문';
-- ============================================================

-- 원가/가격 산출 기준 데이터. src/lib/engine_settings.php의 compute_price_estimate()가 참조.
-- category: wood(목재)/oil(오일마감)/finish(마감)/delivery(배송)/labor(인건비)/overhead(간접비·이윤)/hardware(철물)
-- 이 CREATE TABLE은 2026-07-08에 기존 운영 DB 구조를 그대로 문서화한 것 — 위쪽 ALTER 이력과 함께 참고
CREATE TABLE IF NOT EXISTS cost_table (
    id            INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    category      VARCHAR(50)       NOT NULL DEFAULT '' COMMENT '원가 대분류 — wood(목재)/oil(오일마감)/finish(마감)/delivery(배송)/labor(인건비)/overhead(간접비·이윤)/hardware(철물)',
    engine        VARCHAR(20)       NULL COMMENT '엔진명 (classic/square/…, NULL=공통)',
    name          VARCHAR(100)      NOT NULL DEFAULT '',
    unit_price    DECIMAL(12,2)     NOT NULL DEFAULT 0.00 COMMENT '원/사이',
    unit          VARCHAR(30)       NOT NULL DEFAULT '' COMMENT '단위',
    unit_name     VARCHAR(50)       NOT NULL DEFAULT '' COMMENT '단위명',
    weight        DECIMAL(8,4)      NOT NULL DEFAULT 1.0000 COMMENT '가중치',
    thickness_mm  SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '부재 두께 mm (문틀만 사용, 살=slatT·울거미=frameW/H)',
    width_mm      SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '부재 폭 mm (울거미·살·문틀 공통)',
    work_time_min SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    coat_count    TINYINT UNSIGNED  NOT NULL DEFAULT 1,
    notes         VARCHAR(500)      NOT NULL DEFAULT '' COMMENT '메모',
    sort_order    SMALLINT          NOT NULL DEFAULT 0,
    is_active     TINYINT(1)        NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    KEY idx_ct_engine (engine)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='목재 단가';
-- ============================================================

-- 엔진별 기본 설정값 (좌측 패널 슬라이더 기본값 + gap/basePadding 같은 레이아웃 상수)
-- 어드민에서 관리. 행이 없으면 src/lib/engine_settings.php의 하드코딩 fallback 값 사용
CREATE TABLE IF NOT EXISTS engine_settings (
    engine        VARCHAR(20)  NOT NULL COMMENT '엔진명 (classic/square/cross/diamond/triangle/hexagon)',
    setting_key   VARCHAR(40)  NOT NULL COMMENT '설정 키 (예: W, H, cols, gap, basePadding)',
    setting_value VARCHAR(255) NOT NULL COMMENT '설정 값',
    updated_at    DATETIME     NOT NULL DEFAULT NOW() ON UPDATE NOW() COMMENT '수정일시',
    PRIMARY KEY (engine, setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='엔진별 기본 설정값';


-- AI 렌더링 재질/조명 프리셋 (엔진 사이드바 선택 박스, 어드민에서 편집)
CREATE TABLE IF NOT EXISTS render_presets (
    id          INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    label       VARCHAR(50)       NOT NULL DEFAULT '' COMMENT '선택 박스에 표시될 이름 (예: 형광등 조명)',
    prompt_text VARCHAR(500)      NOT NULL DEFAULT '' COMMENT 'AI 렌더링에 실제로 전달할 프롬프트',
    sort_order  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active   TINYINT(1)        NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    KEY idx_render_presets_sort (sort_order, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI 렌더링 재질/조명 프리셋';

-- AI 대화 히스토리 + 엔진 사용 통계
CREATE TABLE IF NOT EXISTS ai_chat_history (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED    NULL     COMMENT '로그인 사용자 (NULL=비회원)',
    session_key VARCHAR(64)   NOT NULL  COMMENT '비회원용 세션 키',
    engine     VARCHAR(20)    NOT NULL  COMMENT '사용 엔진',
    message    TEXT           NOT NULL  COMMENT '사용자 입력',
    reply      TEXT           NULL      COMMENT 'AI 응답 텍스트',
    params_out JSON           NULL      COMMENT 'AI가 반환한 파라미터',
    created_at TIMESTAMP      NOT NULL  DEFAULT CURRENT_TIMESTAMP,
    KEY idx_user_created    (user_id,     created_at),
    KEY idx_session_created (session_key, created_at),
    KEY idx_engine_created  (engine,      created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI 설계 도우미 대화 히스토리';
-- ============================================================

-- 엔진 "문양 삽입" 기능에서 고르는 어드민 등록 SVG 라이브러리 (사용자 개인 업로드는 별도, uploads/svg_insert/{userId}에 파일로만 저장됨)
CREATE TABLE IF NOT EXISTS svg_motifs (
    id         INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    name       VARCHAR(100)      NOT NULL DEFAULT '' COMMENT '문양 이름 (관리자 표시용)',
    svg_url    VARCHAR(500)      NOT NULL DEFAULT '' COMMENT '/uploads/svg_motifs/ 아래 파일 경로',
    category   VARCHAR(50)       NOT NULL DEFAULT '' COMMENT '분류 태그 (예: 꽃살, 기하학)',
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active  TINYINT(1)        NOT NULL DEFAULT 1,
    created_at DATETIME          NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    KEY idx_svg_motifs_sort (sort_order, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='어드민 등록 SVG 문양 라이브러리 (엔진 문양 삽입 패널에서 선택)';

-- 블로그 시리즈 (연재 묶음). blog_posts.series_id가 이 테이블을 참조하며, 홈 화면 인용 배너에 노출할 시리즈를 show_on_home으로 고름
CREATE TABLE IF NOT EXISTS blog_series (
    id           INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    name         VARCHAR(80)       NOT NULL COMMENT '시리즈명',
    tagline      VARCHAR(200)      NOT NULL DEFAULT '' COMMENT '시리즈 한줄 소개',
    show_on_home TINYINT(1)        NOT NULL DEFAULT 1 COMMENT '홈 화면 인용 배너 노출 여부',
    is_completed TINYINT(1)        NOT NULL DEFAULT 0 COMMENT '연재 완결 여부 (0=연재중, 1=완결)',
    sort_order   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_series_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='블로그 시리즈 (연재 묶음)';

-- 2026-07-27 시리즈 연재중/완결 상태 표시
-- ALTER TABLE blog_series ADD COLUMN is_completed TINYINT(1) NOT NULL DEFAULT 0 COMMENT '연재 완결 여부 (0=연재중, 1=완결)' AFTER show_on_home;

-- 블로그 글. 시리즈에 속하거나(series_id) 독립 글일 수 있고, related_drawing_id/related_engine으로 특정 엔진·도면과 양방향 링크됨
-- (엔진 페이지 → 관련 블로그 글, 블로그 글 → "이 엔진으로 만들어보기" 딥링크)
CREATE TABLE IF NOT EXISTS blog_posts (
    id                 INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    title              VARCHAR(150)      NOT NULL DEFAULT '',
    slug               VARCHAR(200)      NULL     DEFAULT NULL COMMENT '시맨틱 URL slug (/blog/{slug}). NULL=아직 공개(보기) 전이라 slug 미생성',
    series_id          INT UNSIGNED      NULL     COMMENT '소속 시리즈 (blog_series.id FK, 독립 글이면 NULL)',
    series_order       SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '시리즈 내 순서',
    related_drawing_id INT UNSIGNED      NULL     COMMENT '연관 대표 도면 ID (drawings.id), 글 상단 썸네일/딥링크용',
    related_engine     VARCHAR(20)       NULL     COMMENT '연관 엔진 (classic/square/cross/diamond/triangle/hexagon)',
    question           VARCHAR(200)      NOT NULL DEFAULT '' COMMENT '질문형 인덱스 목록에 쓰이는 한 줄 질문 (예: "정자살과 완자살, 뭐가 다른가요?")',
    summary            VARCHAR(300)      NOT NULL DEFAULT '' COMMENT '목록/카드에 노출되는 요약',
    cta_text           VARCHAR(200)      NOT NULL DEFAULT '' COMMENT '글 하단 행동유도 문구',
    source_text        TEXT              NULL     DEFAULT NULL COMMENT '이 글이 참고한 출처 (줄바꿈으로 여러 개 구분, 있으면 하단에 노출)',
    content            TEXT              NOT NULL COMMENT '본문 (HTML)',
    thumbnail_url      VARCHAR(500)      NOT NULL DEFAULT '',
    sort_order         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active          TINYINT(1)        NOT NULL DEFAULT 1,
    is_featured        TINYINT(1)        NOT NULL DEFAULT 0 COMMENT '히어로 캐로셀 노출 여부 (관리자가 직접 선택, 날짜 무관)',
    view_count         INT UNSIGNED      NOT NULL DEFAULT 0 COMMENT '조회수 (방문자 쿠키 기준 24시간 중복 방지)',
    created_at         DATETIME          NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    UNIQUE KEY uq_blog_posts_slug (slug),
    KEY idx_blog_posts_sort (sort_order, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='블로그 글 (창호 이야기)';

-- 2026-07-22 글 출처 표기 (있으면 상세 페이지 하단에 노출)
-- ALTER TABLE blog_posts ADD COLUMN source_text VARCHAR(300) NULL DEFAULT NULL COMMENT '이 글이 참고한 출처 (있으면 하단에 노출)' AFTER cta_text;
-- 2026-07-23 출처 여러 개(줄바꿈 구분) 지원을 위해 VARCHAR(300) → TEXT로 확장
-- ALTER TABLE blog_posts MODIFY COLUMN source_text TEXT NULL DEFAULT NULL COMMENT '이 글이 참고한 출처 (줄바꿈으로 여러 개 구분, 있으면 하단에 노출)';
-- 2026-07-27 히어로 캐로셀 노출 글을 관리자가 직접 선택하도록 (기존엔 썸네일 있는 글 자동 선정)
-- ALTER TABLE blog_posts ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0 COMMENT '히어로 캐로셀 노출 여부 (관리자가 직접 선택, 날짜 무관)' AFTER is_active;

-- 2026-07-19 인기 페이지 집계에서 404(스캐너가 찌른 존재하지 않는 경로) 제외용
-- ALTER TABLE page_views ADD COLUMN status_code SMALLINT UNSIGNED NOT NULL DEFAULT 200 COMMENT 'HTTP 응답 코드 (404 등 — 인기 페이지 집계에서 제외용)';

-- 2026-07-19 어드민에서 직접 IP를 차단하는 기능 — src/lib/ip_block.php가 매 요청마다 조회해 403 처리
CREATE TABLE IF NOT EXISTS blocked_ips (
    ip         VARCHAR(45) NOT NULL,
    reason     VARCHAR(255) NOT NULL DEFAULT '',
    blocked_by INT UNSIGNED NULL,
    blocked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (ip)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='어드민이 수동으로 차단한 IP 목록';

-- 2026-07-26 블로그 전체 조회수 총합 일별 스냅샷 — 어드민에서 추이(홍보/광고 효과 측정용)로 확인
CREATE TABLE IF NOT EXISTS blog_view_snapshots (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    snapshot_date DATE         NOT NULL,
    total_views   INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '스냅샷 시점 blog_posts.view_count 총합 (is_active=1)',
    PRIMARY KEY (id),
    UNIQUE KEY uq_bvs_date (snapshot_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='블로그 전체 조회수 총합 일별 스냅샷 (추이 확인용)';

-- 2026-07-28 스냅샷 기록을 요청 트리거 방식(그날 첫 방문 때)에서 MySQL EVENT 배치로 전환.
-- 트래픽에 따라 스냅샷 시각이 들쭉날쭉해서 일별 차트 날짜 라벨이 밀려 보이는 버그가 있었음
-- (event_scheduler가 꺼져 있으면 스냅샷이 안 쌓이니 SHOW VARIABLES LIKE 'event_scheduler'로 확인)
-- CREATE EVENT IF NOT EXISTS ev_blog_view_daily_snapshot
-- ON SCHEDULE EVERY 1 DAY STARTS (DATE_ADD(CURRENT_DATE, INTERVAL 1 DAY))
-- DO
--   INSERT IGNORE INTO blog_view_snapshots (snapshot_date, total_views)
--   SELECT CURDATE(), COALESCE(SUM(view_count), 0) FROM blog_posts WHERE is_active = 1;
