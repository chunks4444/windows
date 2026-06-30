-- ============================================================
-- windows.pyeongmok.com 데이터베이스 스키마
-- ============================================================

-- 누락 인덱스 추가 (아래 두 줄을 DB에 직접 실행)
-- ALTER TABLE drawings ADD KEY idx_drawings_user_updated (user_id, updated_at);
-- ALTER TABLE drawing_versions ADD KEY idx_dv_drawing_saved (drawing_id, saved_at);

-- 기존 DB에 컬럼 추가할 경우 아래 ALTER 실행
-- ALTER TABLE cost_table ADD COLUMN engine       VARCHAR(20)       NULL                COMMENT '엔진명 (classic/square/…, NULL=공통)' AFTER category;
-- ALTER TABLE cost_table ADD COLUMN thickness_mm SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '부재 두께 mm (문틀만 사용, 살=slatT·울거미=frameW/H)' AFTER weight;
-- ALTER TABLE cost_table ADD COLUMN width_mm     SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '부재 폭 mm (울거미·살·문틀 공통)' AFTER thickness_mm;
-- ALTER TABLE drawings ADD COLUMN thumbnail   MEDIUMTEXT   NULL    COMMENT '썸네일 이미지 (data:image/jpeg;base64,…)' AFTER updated_at;
-- ALTER TABLE drawings ADD COLUMN work_time_sec INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '누적 작업 시간(초)' AFTER thumbnail;
-- ALTER TABLE users ADD COLUMN role ENUM('s','m','a','u') NOT NULL DEFAULT 'u' COMMENT '권한: s=슈퍼, m=관리자, a=작가, u=회원' AFTER email;
-- ALTER TABLE users ADD COLUMN last_login_at DATETIME NULL COMMENT '최종 접속일시' AFTER created_at;
-- ALTER TABLE users ADD COLUMN withdrawn_at DATETIME NULL COMMENT '탈퇴일시 (NULL=정상, NOT NULL=탈퇴)' AFTER last_login_at;
-- ALTER TABLE drawings ADD COLUMN locked_at DATETIME NULL COMMENT '잠금일시 (NULL=편집가능, 견적요청 중이면 잠김)' AFTER work_time_sec;
-- ALTER TABLE drawings ADD COLUMN pattern_category VARCHAR(40) NULL DEFAULT NULL COMMENT '전통 창호 패턴 분류 — pattern_categories.code 참조' AFTER title;
-- CREATE TABLE pattern_categories (code VARCHAR(40) NOT NULL, engine VARCHAR(20) NOT NULL DEFAULT 'all', name VARCHAR(40) NOT NULL, sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0, is_active TINYINT(1) NOT NULL DEFAULT 1, PRIMARY KEY (code, engine)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 접속 통계 (6개월 rolling)
CREATE TABLE IF NOT EXISTS page_views (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    visited_at  DATETIME     NOT NULL DEFAULT NOW() COMMENT '방문일시',
    page        VARCHAR(120) NOT NULL               COMMENT '페이지 경로',
    user_id     INT UNSIGNED NULL                   COMMENT '로그인 유저 ID (비회원 NULL)',
    ip_hash     CHAR(8)      NOT NULL               COMMENT 'IP MD5 앞 8자 (UV 계산용)',
    ip          VARCHAR(45)  NULL                   COMMENT '방문자 IP',
    is_mobile   TINYINT(1)   NOT NULL DEFAULT 0     COMMENT '모바일 여부',
    PRIMARY KEY (id),
    KEY idx_pv_visited (visited_at),
    KEY idx_pv_date_page (visited_at, page),
    CONSTRAINT fk_pv_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='페이지 방문 통계 (6개월 rolling)';
-- ============================================================

-- 사용자 테이블
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT COMMENT '사용자 고유 ID',
    email         VARCHAR(255)    NOT NULL COMMENT '이메일 (로그인 아이디)',
    role          ENUM('s','m','a','u') NOT NULL DEFAULT 'u' COMMENT '권한: s=슈퍼, m=관리자, a=작가, u=회원',
    password_hash VARCHAR(255)    NOT NULL COMMENT '비밀번호 해시 (bcrypt)',
    created_at    DATETIME        NOT NULL DEFAULT NOW() COMMENT '가입일시',
    last_login_at DATETIME        NULL COMMENT '최종 접속일시',
    last_login_ip VARCHAR(45)     NULL COMMENT '최종 접속 IP',
    withdrawn_at  DATETIME        NULL COMMENT '탈퇴일시 (NULL=정상, NOT NULL=탈퇴)',
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
    created_at  DATETIME        NOT NULL DEFAULT NOW() COMMENT '최초 작성일시',
    updated_at    DATETIME        NOT NULL DEFAULT NOW() ON UPDATE NOW() COMMENT '최종 저장일시',
    thumbnail     MEDIUMTEXT      NULL COMMENT '썸네일 이미지 (data:image/jpeg;base64,…)',
    work_time_sec INT UNSIGNED    NOT NULL DEFAULT 0 COMMENT '누적 작업 시간(초)',
    locked_at     DATETIME        NULL COMMENT '잠금일시 (NULL=편집가능, 견적요청 중이면 잠김)',
    PRIMARY KEY (id),
    UNIQUE KEY uq_drawings_user_type_title (user_id, type, title),
    KEY idx_drawings_user_type (user_id, type),
    CONSTRAINT fk_drawings_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='도면 메타 정보 (제목별로 독립 관리)';

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

-- 배경 이미지 테이블 (엔진별 사용자 업로드 배경)
-- 기존 DB에 컬럼 추가:
-- ALTER TABLE wallpapers ADD COLUMN version_saved_at INT UNSIGNED NULL COMMENT '소속 버전 savedAt (Unix초)' AFTER drawing_id;
-- ALTER TABLE wallpapers ADD KEY idx_wallpapers_version (drawing_id, version_saved_at);
CREATE TABLE IF NOT EXISTS wallpapers (
    id               INT UNSIGNED    NOT NULL AUTO_INCREMENT COMMENT '배경 고유 ID',
    user_id          INT UNSIGNED    NOT NULL COMMENT '소유 사용자 ID (users.id FK)',
    engine           VARCHAR(64)     NOT NULL DEFAULT '' COMMENT '엔진 구분 (예: classic, square, diamond, cross, triangle)',
    drawing_id       INT UNSIGNED    NULL COMMENT '소속 도면 ID (drawings.id FK, nullable)',
    version_saved_at INT UNSIGNED    NULL COMMENT '소속 버전 savedAt (Unix초, nullable)',
    filename         VARCHAR(255)    NOT NULL DEFAULT '' COMMENT '원본 파일명',
    filepath         VARCHAR(500)    NOT NULL DEFAULT '' COMMENT '저장 파일 경로 (/uploads/wallpapers/…)',
    created_at       DATETIME        NOT NULL DEFAULT NOW() COMMENT '업로드 일시',
    PRIMARY KEY (id),
    KEY idx_wallpapers_user_engine (user_id, engine),
    KEY idx_wallpapers_drawing_id (drawing_id),
    KEY idx_wallpapers_version (drawing_id, version_saved_at),
    CONSTRAINT fk_wallpapers_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_wallpapers_drawing FOREIGN KEY (drawing_id) REFERENCES drawings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='엔진 배경 이미지';

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
    name_ko     VARCHAR(80)       NOT NULL DEFAULT '' COMMENT '패턴 이름 (예: 정자살)',
    drawing_id  INT UNSIGNED      NULL               COMMENT '연결 도면 (drawings.id FK)',
    image_path  VARCHAR(500)      NOT NULL DEFAULT '' COMMENT '대표 이미지 경로 (/uploads/library/…)',
    sort_order  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active   TINYINT(1)        NOT NULL DEFAULT 1,
    created_at  DATETIME          NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
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

-- 사이트 설정 (OAuth 키 등)
CREATE TABLE IF NOT EXISTS site_config (
    key_name   VARCHAR(80) NOT NULL,
    value      TEXT        NOT NULL DEFAULT '',
    updated_at DATETIME    NOT NULL DEFAULT NOW() ON UPDATE NOW(),
    PRIMARY KEY (key_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='사이트 설정 (OAuth 키 등)';

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
    KEY idx_drawing_versions_drawing_id (drawing_id),
    CONSTRAINT fk_drawing_versions_drawing FOREIGN KEY (drawing_id) REFERENCES drawings (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='도면 버전 이력';

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
CREATE TABLE IF NOT EXISTS works (
    id          INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    title       VARCHAR(100)      NOT NULL DEFAULT '',
    description VARCHAR(300)      NOT NULL DEFAULT '',
    image_url   VARCHAR(500)      NOT NULL DEFAULT '',
    sort_order  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active   TINYINT(1)        NOT NULL DEFAULT 1,
    created_at  DATETIME          NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    KEY idx_works_sort (sort_order, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='완성 작품 갤러리';

-- FAQ
CREATE TABLE IF NOT EXISTS faqs (
    id          INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    question    VARCHAR(255)      NOT NULL DEFAULT '' COMMENT '질문',
    answer      TEXT              NOT NULL COMMENT '답변',
    sort_order  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active   TINYINT(1)        NOT NULL DEFAULT 1,
    created_at  DATETIME          NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    KEY idx_faqs_sort (sort_order, is_active)
-- ALTER TABLE faqs ADD COLUMN show_on_main TINYINT(1) NOT NULL DEFAULT 0 COMMENT '메인페이지 노출' AFTER is_active; -- 2026-06-29
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
    price_breakdown     MEDIUMTEXT   NULL COMMENT '항목별 가격 산출 내역 (JSON)',
    price_formula_version VARCHAR(20) NULL COMMENT '계산에 사용된 가격 공식 버전',
    status              ENUM('pending','confirmed','done','cancelled') NOT NULL DEFAULT 'pending' COMMENT '처리 상태',
    created_at          DATETIME     NOT NULL DEFAULT NOW() COMMENT '주문 접수일시',
    PRIMARY KEY (id),
    KEY idx_orders_user (user_id),
    KEY idx_orders_status_created (status, created_at),
    CONSTRAINT fk_orders_user     FOREIGN KEY (user_id)    REFERENCES users (id)    ON DELETE CASCADE,
    CONSTRAINT fk_orders_drawing FOREIGN KEY (drawing_id) REFERENCES drawings (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='제작 주문';
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
