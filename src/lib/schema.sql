-- ============================================================
-- windows.pyeongmok.com 데이터베이스 스키마
-- ============================================================

-- 기존 DB에 컬럼 추가할 경우 아래 ALTER 실행
-- ALTER TABLE drawings ADD COLUMN thumbnail   MEDIUMTEXT   NULL    COMMENT '썸네일 이미지 (data:image/jpeg;base64,…)' AFTER updated_at;
-- ALTER TABLE drawings ADD COLUMN work_time_sec INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '누적 작업 시간(초)' AFTER thumbnail;
-- ============================================================

-- 사용자 테이블
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT COMMENT '사용자 고유 ID',
    email       VARCHAR(255)    NOT NULL COMMENT '이메일 (로그인 아이디)',
    password    VARCHAR(255)    NOT NULL COMMENT '비밀번호 해시 (bcrypt)',
    created_at  DATETIME        NOT NULL DEFAULT NOW() COMMENT '가입일시',
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
