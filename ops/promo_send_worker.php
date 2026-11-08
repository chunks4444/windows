<?php
// 홍보메일 발송 워커 (CLI 전용 — ops/는 웹 접근이 막혀 있다).
//   예약 시각이 지난 캠페인과, 일일 한도로 멈춘 캠페인을 이어서 발송한다.
//   즉시 발송 버튼이 백그라운드로 한 번 띄우고, 예약 발송은 cron이 1분마다 호출한다:
//   * * * * * php /data/web/studio.pyeongmok.com/ops/promo_send_worker.php >> /data/web/studio.pyeongmok.com/logs/promo_worker.log 2>&1
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

// 잠금은 이 스크립트 파일 자체에 건다 — Apache(www-data, PrivateTmp)와 cron(chunks)이 /tmp를 따로 쓰고,
// 한쪽이 만든 잠금 파일을 다른 쪽이 못 여는 문제를 피하려고 둘 다 읽을 수 있는 이 파일을 쓴다.
$lock = fopen(__FILE__, 'r');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) exit(0); // 이미 다른 워커가 돌고 있음

set_time_limit(0);
require_once __DIR__ . '/../src/lib/promo_mail.php';

$pdo = db();
promo_ensure_tables($pdo);
promo_run_due($pdo);
