-- ============================================================
-- windows.pyeongmok.com 데이터베이스 스키마
-- ============================================================

-- 기존 DB에 컬럼 추가할 경우 아래 ALTER 실행
-- ALTER TABLE drawings ADD COLUMN thumbnail   MEDIUMTEXT   NULL    COMMENT '썸네일 이미지 (data:image/jpeg;base64,…)' AFTER updated_at;
-- ALTER TABLE drawings ADD COLUMN work_time_sec INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '누적 작업 시간(초)' AFTER thumbnail;
-- ALTER TABLE users ADD COLUMN role ENUM('s','m','a','u') NOT NULL DEFAULT 'u' COMMENT '권한: s=슈퍼, m=관리자, a=작가, u=회원' AFTER email;
-- ALTER TABLE users ADD COLUMN last_login_at DATETIME NULL COMMENT '최종 접속일시' AFTER created_at;
-- ALTER TABLE users ADD COLUMN withdrawn_at DATETIME NULL COMMENT '탈퇴일시 (NULL=정상, NOT NULL=탈퇴)' AFTER last_login_at;

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
    type        VARCHAR(64)     NOT NULL COMMENT '도면 종류 (예: sambuntok)',
    title       VARCHAR(100)    NOT NULL DEFAULT '' COMMENT '도면 제목 (유저가 직접 지정, 버전과 독립 관리)',
    created_at  DATETIME        NOT NULL DEFAULT NOW() COMMENT '최초 작성일시',
    updated_at    DATETIME        NOT NULL DEFAULT NOW() ON UPDATE NOW() COMMENT '최종 저장일시',
    thumbnail     MEDIUMTEXT      NULL COMMENT '썸네일 이미지 (data:image/jpeg;base64,…)',
    work_time_sec INT UNSIGNED    NOT NULL DEFAULT 0 COMMENT '누적 작업 시간(초)',
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
CREATE TABLE IF NOT EXISTS wallpapers (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT COMMENT '배경 고유 ID',
    user_id     INT UNSIGNED    NOT NULL COMMENT '소유 사용자 ID (users.id FK)',
    engine      VARCHAR(64)     NOT NULL DEFAULT '' COMMENT '엔진 구분 (예: sabunteok)',
    filename    VARCHAR(255)    NOT NULL DEFAULT '' COMMENT '원본 파일명',
    filepath    VARCHAR(500)    NOT NULL DEFAULT '' COMMENT '저장 파일 경로 (/uploads/wallpapers/…)',
    created_at  DATETIME        NOT NULL DEFAULT NOW() COMMENT '업로드 일시',
    PRIMARY KEY (id),
    KEY idx_wallpapers_user_engine (user_id, engine),
    CONSTRAINT fk_wallpapers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
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

-- 라이브러리 카테고리
CREATE TABLE IF NOT EXISTS library_categories (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    slug        VARCHAR(60)     NOT NULL COMMENT '필터 key (영문)',
    name_ko     VARCHAR(80)     NOT NULL COMMENT '한국어 표시명',
    sort_order  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active   TINYINT(1)      NOT NULL DEFAULT 1,
    created_at  DATETIME        NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    UNIQUE KEY uq_cat_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='라이브러리 카테고리';

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
