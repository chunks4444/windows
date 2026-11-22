-- 다다호텔 매니저 전용 DB (평목 DB와 분리). 최초 1회 root로 아래 두 줄 실행 후 관리자 페이지에서 테이블 자동 생성:
--   CREATE DATABASE IF NOT EXISTS dada_hotel CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
--   GRANT ALL ON dada_hotel.* TO 'webpyeongmok'@'localhost';

CREATE TABLE IF NOT EXISTS raw_bookings (
    row_key       VARCHAR(60)  NOT NULL PRIMARY KEY,
    acct          VARCHAR(20)  NOT NULL,
    ns            VARCHAR(10)  NULL,
    created_at    DATETIME     NULL,
    book_date     DATE         NULL,
    ota_no        VARCHAR(40)  NULL,
    room_no       VARCHAR(10)  NULL,
    room_type     VARCHAR(20)  NULL,
    rooms         INT          NULL,
    checkin       DATE         NULL,
    nights        INT          NULL,
    checkout      DATE         NULL,
    adults        INT          NULL,
    children      INT          NULL,
    channel       VARCHAR(60)  NULL,
    dc_pct        VARCHAR(20)  NULL,
    avg_rate      DECIMAL(12,0) NULL,
    daily_rate    DECIMAL(12,0) NULL,
    total_amt     DECIMAL(12,0) NULL,
    extra_amt     DECIMAL(12,0) NULL,
    sum_amt       DECIMAL(12,0) NULL,
    status        VARCHAR(30)  NULL,
    memo          TEXT         NULL,
    group_memo    TEXT         NULL,
    amenity       VARCHAR(255) NULL,
    trace         TEXT         NULL,
    modified_raw  VARCHAR(40)  NULL,
    imported_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_acct (acct),
    KEY idx_checkin (checkin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS room_types (
    code        VARCHAR(20) NOT NULL PRIMARY KEY,
    room_count  INT         NOT NULL DEFAULT 0,
    std_ratio   DECIMAL(4,2) NOT NULL DEFAULT 1.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bookings (
    row_key     VARCHAR(60)  NOT NULL PRIMARY KEY,
    acct        VARCHAR(20)  NOT NULL,
    ota_no      VARCHAR(40)  NULL,
    created_at  DATETIME     NULL,
    book_date   DATE         NULL,
    room_no     VARCHAR(10)  NULL,
    room_type   VARCHAR(20)  NULL,
    checkin     DATE         NULL,
    checkout    DATE         NULL,
    nights      INT          NULL,
    adults      INT          NULL,
    children    INT          NULL,
    channel     VARCHAR(60)  NULL,
    daily_rate  DECIMAL(12,0) NULL,
    total_amt   DECIMAL(12,0) NULL,
    status      VARCHAR(30)  NULL,
    lead_days   INT          NULL,
    is_over     TINYINT(1)   NOT NULL DEFAULT 0,
    is_zero_amt TINYINT(1)   NOT NULL DEFAULT 0,
    is_zero_night TINYINT(1) NOT NULL DEFAULT 0,
    is_overlap  TINYINT(1)   NOT NULL DEFAULT 0,
    excluded    TINYINT(1)   NOT NULL DEFAULT 0,
    KEY idx_acct (acct),
    KEY idx_checkin (checkin),
    KEY idx_room (room_no, checkin),
    KEY idx_excluded (excluded)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rate_history (
    stay_date   DATE         NOT NULL,
    room_type   VARCHAR(20)  NOT NULL,
    rate        DECIMAL(12,0) NOT NULL,
    set_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (stay_date, room_type, set_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
