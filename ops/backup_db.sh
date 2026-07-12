#!/bin/bash
# 평목 DB(windowspyeongmok) 일일 백업 스크립트.
# 서버(studio.pyeongmok.com)에 직접 설치해서 cron으로 돌리는 용도 — 이 저장소 배포 대상 아님.
#
# 설치 순서:
#   1. 백업 전용 읽기전용 계정을 새로 만든다 (앱이 쓰는 webpyeongmok 계정은 쓰기/DDL 권한이 넓어서
#      재사용하면 .cnf 파일 하나 털렸을 때 데이터 수정·삭제까지 노출됨 — 반드시 분리할 것).
#      MySQL에서 한 번만 실행:
#        CREATE USER 'pyeongmok_backup'@'localhost' IDENTIFIED BY '강력한_새_비밀번호';
#        GRANT SELECT, LOCK TABLES, SHOW VIEW, TRIGGER, EVENT ON windowspyeongmok.* TO 'pyeongmok_backup'@'localhost';
#        FLUSH PRIVILEGES;
#   2. 이 서버에 자격증명 전용 파일을 만든다.
#        sudo install -m 600 /dev/null /root/.pyeongmok_db.cnf
#        sudo nano /root/.pyeongmok_db.cnf
#      내용:
#        [client]
#        user=pyeongmok_backup
#        password=위에서 정한 강력한_새_비밀번호
#   3. 이 스크립트를 서버에 올리고 실행권한 부여
#        sudo cp backup_db.sh /usr/local/bin/pyeongmok_backup_db.sh
#        sudo chmod 700 /usr/local/bin/pyeongmok_backup_db.sh
#   4. crontab -e (root)로 매일 새벽 실행 등록
#        0 3 * * * /usr/local/bin/pyeongmok_backup_db.sh >> /var/log/pyeongmok_db_backup.log 2>&1
#
# 주의: BACKUP_DIR은 반드시 웹 문서 루트(/var/www/html 등) 밖이어야 한다.
#       문서 루트 안에 두면 .sql.gz가 그대로 공개 다운로드될 수 있다.

set -euo pipefail

DB_NAME="windowspyeongmok"
DEFAULTS_FILE="/root/.pyeongmok_db.cnf"
BACKUP_DIR="/var/backups/pyeongmok-db"
RETENTION_DAYS=30

if [ ! -f "$DEFAULTS_FILE" ]; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] 자격증명 파일 없음: $DEFAULTS_FILE (설치 안내 참고)" >&2
    exit 1
fi

mkdir -p "$BACKUP_DIR"

TIMESTAMP=$(date +%Y%m%d_%H%M%S)
OUT_FILE="$BACKUP_DIR/${DB_NAME}_${TIMESTAMP}.sql.gz"
TMP_FILE="${OUT_FILE}.tmp"

mysqldump --defaults-extra-file="$DEFAULTS_FILE" \
    --single-transaction --quick --routines --triggers --hex-blob \
    "$DB_NAME" | gzip > "$TMP_FILE"

mv "$TMP_FILE" "$OUT_FILE"
echo "[$(date '+%Y-%m-%d %H:%M:%S')] 백업 완료: $OUT_FILE ($(du -h "$OUT_FILE" | cut -f1))"

# 오래된 백업 정리
find "$BACKUP_DIR" -name "${DB_NAME}_*.sql.gz" -mtime "+${RETENTION_DAYS}" -print -delete
