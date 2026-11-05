<?php
// 홍보메일 — 캠페인(문구·대상·예약) / 발송 이력 / 수신거부 토큰 / 본문 조립 / 실제 발송 루프.
// 어드민 API(api/admin/promo_campaigns.php), 수신거부(api/promo/unsubscribe.php), 발송 워커(ops/promo_send_worker.php)가 공용으로 쓴다.
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mailer.php';

const PROMO_INDUSTRIES   = ['인테리어', '건축사무소', '건설사'];
// Gmail SMTP는 하루 발송 한도가 있어(개인 약 500통) 넘기면 계정이 막힌다 — 24시간 기준 안전 한도
const PROMO_DAILY_LIMIT  = 400;
// 메일 사이 간격(초) — 2분. 짧은 시간에 몰아 보내면 스팸·블랙리스트로 분류되기 쉬워 천천히 보낸다
const PROMO_SEND_GAP_SEC = 120;
// 연속으로 이만큼 실패하면 캠페인을 멈춘다 — 인증 오류·차단 상태에서 계속 두드리면 평판이 더 나빠진다
const PROMO_MAX_CONSECUTIVE_FAIL = 5;

function promo_ensure_tables(PDO $pdo): void {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS promo_companies (
            id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
            name       VARCHAR(100) NOT NULL,
            email      VARCHAR(190) NOT NULL,
            phone      VARCHAR(30)  NOT NULL DEFAULT '',
            address    VARCHAR(255) NOT NULL DEFAULT '',
            website    VARCHAR(255) NOT NULL DEFAULT '' COMMENT '홈페이지 주소',
            industry   VARCHAR(20)  NOT NULL DEFAULT '',
            opt_out    TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '수신거부(발송 제외)',
            created_at DATETIME     NOT NULL DEFAULT NOW(),
            updated_at DATETIME     NOT NULL DEFAULT NOW() ON UPDATE NOW(),
            PRIMARY KEY (id),
            UNIQUE KEY uq_pc_email (email),
            KEY idx_pc_industry (industry)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='홍보메일 발송 대상 회사'"
    );
    // 2026-11-04 홈페이지 주소 컬럼 추가 — 이미 만들어진 테이블에는 없으므로 한 번 보강한다
    if (!$pdo->query("SHOW COLUMNS FROM promo_companies LIKE 'website'")->fetch()) {
        $pdo->exec("ALTER TABLE promo_companies ADD COLUMN website VARCHAR(255) NOT NULL DEFAULT '' COMMENT '홈페이지 주소' AFTER address");
    }
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS promo_campaigns (
            id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
            title        VARCHAR(150) NOT NULL DEFAULT '' COMMENT '관리용 이름',
            subject      VARCHAR(200) NOT NULL DEFAULT '' COMMENT '제목 ((광고) 접두사는 발송 때 자동)',
            html_body    MEDIUMTEXT   NULL COMMENT '홍보문구 HTML. {{회사명}} {{수신거부링크}} 치환',
            industries   VARCHAR(100) NOT NULL DEFAULT '' COMMENT '대상 업종(쉼표 구분), 빈 값=전체',
            work_ids     VARCHAR(255) NOT NULL DEFAULT '' COMMENT '(미사용) 예전 포트폴리오 카드 방식. 지금은 PDF 첨부를 쓴다',
            attachment_path VARCHAR(255) NOT NULL DEFAULT '' COMMENT '첨부 PDF 저장 경로(/uploads/promo/...)',
            attachment_name VARCHAR(200) NOT NULL DEFAULT '' COMMENT '받는 사람에게 보일 PDF 파일명',
            attachment_size INT UNSIGNED NOT NULL DEFAULT 0,
            status      VARCHAR(12)  NOT NULL DEFAULT 'draft' COMMENT 'draft/scheduled/sending/done/canceled',
            scheduled_at DATETIME     NULL COMMENT '발송 예정 시각 (즉시 발송은 현재 시각)',
            started_at   DATETIME     NULL,
            finished_at  DATETIME     NULL,
            created_at   DATETIME     NOT NULL DEFAULT NOW(),
            updated_at   DATETIME     NOT NULL DEFAULT NOW() ON UPDATE NOW(),
            PRIMARY KEY (id),
            KEY idx_pcamp_status (status, scheduled_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='홍보메일 캠페인'"
    );
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS promo_templates (
            id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
            name       VARCHAR(100) NOT NULL COMMENT '템플릿 이름(관리용)',
            subject    VARCHAR(200) NOT NULL DEFAULT '',
            html_body  MEDIUMTEXT   NULL,
            attachment_path VARCHAR(255) NOT NULL DEFAULT '' COMMENT '첨부 PDF(캠페인과 같은 파일을 공유)',
            attachment_name VARCHAR(200) NOT NULL DEFAULT '',
            attachment_size INT UNSIGNED NOT NULL DEFAULT 0,
            created_at DATETIME     NOT NULL DEFAULT NOW(),
            updated_at DATETIME     NOT NULL DEFAULT NOW() ON UPDATE NOW(),
            PRIMARY KEY (id),
            UNIQUE KEY uq_ptpl_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='홍보메일 문구 템플릿(제목+본문 HTML+첨부 PDF), 캠페인에서 불러와 재사용'"
    );
    if (!$pdo->query("SHOW COLUMNS FROM promo_templates LIKE 'attachment_path'")->fetch()) {
        $pdo->exec("ALTER TABLE promo_templates
            ADD COLUMN attachment_path VARCHAR(255) NOT NULL DEFAULT '' AFTER html_body,
            ADD COLUMN attachment_name VARCHAR(200) NOT NULL DEFAULT '' AFTER attachment_path,
            ADD COLUMN attachment_size INT UNSIGNED NOT NULL DEFAULT 0 AFTER attachment_name");
    }
    // 2026-11-04 PDF 첨부 컬럼 — 이미 만들어진 테이블에는 없으므로 한 번 보강한다
    if (!$pdo->query("SHOW COLUMNS FROM promo_campaigns LIKE 'attachment_path'")->fetch()) {
        $pdo->exec("ALTER TABLE promo_campaigns
            ADD COLUMN attachment_path VARCHAR(255) NOT NULL DEFAULT '' COMMENT '첨부 PDF 저장 경로(/uploads/promo/...)' AFTER work_ids,
            ADD COLUMN attachment_name VARCHAR(200) NOT NULL DEFAULT '' COMMENT '받는 사람에게 보일 PDF 파일명' AFTER attachment_path,
            ADD COLUMN attachment_size INT UNSIGNED NOT NULL DEFAULT 0 AFTER attachment_name");
    }
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS promo_logs (
            id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
            campaign_id INT UNSIGNED NOT NULL,
            company_id  INT UNSIGNED NULL,
            name        VARCHAR(100) NOT NULL DEFAULT '',
            email       VARCHAR(190) NOT NULL,
            status      VARCHAR(10)  NOT NULL DEFAULT 'pending' COMMENT 'pending/sent/failed',
            error       VARCHAR(255) NOT NULL DEFAULT '',
            sent_at     DATETIME     NULL,
            PRIMARY KEY (id),
            KEY idx_plog_campaign (campaign_id, status),
            KEY idx_plog_sent (sent_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='홍보메일 발송 이력(수신자별)'"
    );
}

// ── 수신거부 토큰: 회사 id + HMAC. 로그인 없이 링크만으로 처리하므로 위조 방지용 서명이 필요 ──
function promo_unsub_token(int $companyId): string {
    return $companyId . '.' . substr(hash_hmac('sha256', 'promo-unsub:' . $companyId, PMOK_JWT_SECRET), 0, 24);
}
function promo_unsub_verify(string $token): int {
    if (!preg_match('/^(\d+)\.([0-9a-f]{24})$/', $token, $m)) return 0;
    return hash_equals(promo_unsub_token((int)$m[1]), $token) ? (int)$m[1] : 0;
}
function promo_unsub_url(int $companyId): string {
    return SITE_URL . '/src/api/promo/unsubscribe.php?t=' . promo_unsub_token($companyId);
}

// 제목: 광고성 정보는 제목 맨 앞에 "(광고)" 표기 (정보통신망법)
function promo_subject(string $subject): string {
    $s = trim($subject);
    return str_starts_with($s, '(광고)') ? $s : '(광고) ' . $s;
}

// 받는 회사 이름이 들어갈 자리표시. 본문에 {수신처}처럼 쓰든 {{회사명}}처럼 쓰든 모두 그 회사 이름으로 바꾼다.
const PROMO_NAME_TOKENS = ['{{회사명}}', '{{수신처}}', '{회사명}', '{수신처}'];

function promo_replace_name(string $text, string $name): string {
    return strtr($text, array_fill_keys(PROMO_NAME_TOKENS, $name));
}

// 수신 회사별 제목: 자리표시를 그 회사 이름으로 바꾼다. 이름에 줄바꿈이 있으면 헤더가 깨지므로 공백으로 치환
function promo_subject_for(string $subject, string $companyName): string {
    $name = trim(preg_replace('/[\r\n]+/', ' ', $companyName));
    return promo_subject(promo_replace_name($subject, $name));
}

// 캠페인에 붙은 PDF를 메일 첨부 형식으로 읽는다. 첨부가 없으면 [], 있는데 파일이 없으면 null(발송하면 안 됨).
// 파일이 PROMO_ATTACH_MAX보다 크면 메일에는 붙이지 않는다(받는 서버가 반송·스팸 처리) — 본문의 {{첨부파일링크}} 다운로드 링크로만 전달.
const PROMO_ATTACH_MAX = 8 * 1024 * 1024;
function promo_attachments(array $c): ?array {
    if (($c['attachment_path'] ?? '') === '') return [];
    $file = realpath(__DIR__ . '/../..' . $c['attachment_path']);
    $root = realpath(__DIR__ . '/../../uploads/promo');
    if (!$file || !$root || !str_starts_with($file, $root . DIRECTORY_SEPARATOR)) return null;
    if (filesize($file) > PROMO_ATTACH_MAX) return [];
    $bin = @file_get_contents($file);
    if ($bin === false || $bin === '') return null;
    return [['name' => $c['attachment_name'] ?: 'portfolio.pdf', 'content' => $bin, 'type' => 'application/pdf']];
}

// 본문의 {{첨부파일링크}}가 바뀔 주소 — 캠페인에 올린 PDF를 바로 내려받는 공개 주소.
// 첨부 PDF가 없으면 버튼이 깨지지 않도록 온라인 포트폴리오 페이지로 보낸다. (HTML 속성 안에 들어가므로 & 는 &amp;)
function promo_attachment_link(array $c): string {
    $path = (string)($c['attachment_path'] ?? '');
    if ($path !== '' && preg_match('/^[0-9a-f]{32}\.pdf$/', basename($path))) {
        return SITE_URL . '/src/api/promo/download.php?f=' . basename($path);
    }
    return SITE_URL . '/portfolio?utm_source=b2b_mail&amp;utm_medium=email&amp;utm_campaign=partnership';
}

// 수신자 한 명에게 보낼 최종 HTML. 회사명 자리표시 치환 + 발신자 정보·수신거부 푸터 자동 부착.
// 본문이 <html>…</html> 완성 문서면 그 문서를 그대로 쓰고 푸터만 </body> 앞에 넣는다 (한 번 더 감싸면 문서가 중첩된다).
function promo_render(array $campaign, array $company, string $worksHtml): string {
    $unsub = promo_unsub_url((int)$company['id']);
    $body  = preg_replace('#<script\b.*?</script>#is', '', (string)$campaign['html_body']);
    $body  = promo_replace_name($body, htmlspecialchars($company['name'], ENT_QUOTES, 'UTF-8'));
    $body  = strtr($body, [
        '{{수신거부링크}}' => $unsub,
        '{{첨부파일링크}}' => promo_attachment_link($campaign),
        '{{첨부링크}}'     => promo_attachment_link($campaign),
    ]);
    $footer ='<div style="max-width:600px;margin:0 auto;padding:16px 20px 24px;font-family:\'Apple SD Gothic Neo\',\'Malgun Gothic\',sans-serif;">'
        . '<div style="margin-top:16px;padding-top:16px;border-top:1px solid #eee;font-size:11px;color:#999;line-height:1.6;">'
        . '이 메일은 광고성 정보입니다. 발신: ' . SITE_NAME . ' · ' . htmlspecialchars(mail_address('sales'), ENT_QUOTES, 'UTF-8') . '<br>'
        . '더 이상 받고 싶지 않으시면 <a href="' . $unsub . '" style="color:#999;">수신거부</a>를 눌러 주세요.'
        . '</div></div>';

    if (preg_match('/<html\b/i', $body)) {
        $pos = strripos($body, '</body>');
        return $pos === false ? $body . $footer : substr($body, 0, $pos) . $footer . substr($body, $pos);
    }
    return '<!DOCTYPE html><html lang="ko"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>'
        . '<body style="margin:0;padding:0;background:#fff;"><div style="max-width:600px;margin:0 auto;padding:24px 20px;font-family:\'Apple SD Gothic Neo\',\'Malgun Gothic\',sans-serif;">'
        . $body . $worksHtml . '</div>' . $footer . '</body></html>';
}

function promo_headers(int $companyId): string {
    return 'List-Unsubscribe: <' . promo_unsub_url($companyId) . ">\r\n"
         . 'List-Unsubscribe-Post: List-Unsubscribe=One-Click';
}

// 최근 24시간 발송 수 (일일 한도 계산)
function promo_sent_last_24h(PDO $pdo): int {
    return (int)$pdo->query("SELECT COUNT(*) FROM promo_logs WHERE status='sent' AND sent_at >= NOW() - INTERVAL 24 HOUR")->fetchColumn();
}

// 캠페인 시작: 대상 회사를 이력(pending)으로 확정한다. 이미 확정된 캠페인은 건드리지 않는다.
function promo_snapshot_recipients(PDO $pdo, array $c): void {
    $exists = $pdo->prepare('SELECT COUNT(*) FROM promo_logs WHERE campaign_id = ?');
    $exists->execute([$c['id']]);
    if ((int)$exists->fetchColumn() > 0) return;

    $inds = array_values(array_intersect(PROMO_INDUSTRIES, array_filter(array_map('trim', explode(',', $c['industries'])))));
    $sql  = 'SELECT id, name, email FROM promo_companies WHERE opt_out = 0';
    $args = [];
    if ($inds) { $sql .= ' AND industry IN (' . implode(',', array_fill(0, count($inds), '?')) . ')'; $args = $inds; }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($args);
    $ins = $pdo->prepare('INSERT INTO promo_logs (campaign_id, company_id, name, email) VALUES (?,?,?,?)');
    foreach ($stmt->fetchAll() as $r) $ins->execute([$c['id'], $r['id'], $r['name'], $r['email']]);
}

// 캠페인 하나를 진행한다. 일일 한도에 닿으면 멈추고 sending 상태로 남겨 다음 실행(cron)이 이어 보낸다.
function promo_run_campaign(PDO $pdo, int $campaignId): void {
    $stmt = $pdo->prepare('SELECT * FROM promo_campaigns WHERE id = ?');
    $stmt->execute([$campaignId]);
    $c = $stmt->fetch();
    if (!$c || !in_array($c['status'], ['scheduled', 'sending'], true)) return;

    // 중복 실행 방지: scheduled → sending으로 원자적으로 바꾼 쪽만 진행 (sending 재개는 락 없이 허용하되 pending 행 단위로 점유)
    if ($c['status'] === 'scheduled') {
        $own = $pdo->prepare("UPDATE promo_campaigns SET status='sending', started_at=NOW() WHERE id=? AND status='scheduled'");
        $own->execute([$campaignId]);
        if ($own->rowCount() === 0) return;
    }
    promo_snapshot_recipients($pdo, $c);

    $worksHtml = '';
    // 첨부 PDF는 한 번만 읽어 두고 모든 수신자에게 붙인다. 파일이 사라졌으면 첨부 없는 메일이 나가지 않도록 멈춘다.
    $attachments = promo_attachments($c);
    if ($attachments === null) {
        $pdo->prepare("UPDATE promo_campaigns SET status='canceled', finished_at=NOW() WHERE id=? AND status='sending'")->execute([$campaignId]);
        return;
    }
    $pick  = $pdo->prepare("SELECT l.id, l.company_id, l.name, l.email FROM promo_logs l WHERE l.campaign_id = ? AND l.status = 'pending' ORDER BY l.id LIMIT 1");
    $claim = $pdo->prepare("UPDATE promo_logs SET status='sending' WHERE id = ? AND status = 'pending'");
    $done  = $pdo->prepare('UPDATE promo_logs SET status = ?, error = ?, sent_at = NOW() WHERE id = ?');

    $failStreak = 0;
    while (true) {
        // 취소됐으면 중단
        $st = $pdo->prepare('SELECT status FROM promo_campaigns WHERE id = ?');
        $st->execute([$campaignId]);
        if ($st->fetchColumn() !== 'sending') return;
        if (promo_sent_last_24h($pdo) >= PROMO_DAILY_LIMIT) return;

        $pick->execute([$campaignId]);
        $row = $pick->fetch();
        if (!$row) break;
        $claim->execute([$row['id']]);
        if ($claim->rowCount() === 0) continue;

        // 수신거부가 그 사이 체크됐는지 최종 확인
        $oo = $pdo->prepare('SELECT opt_out FROM promo_companies WHERE id = ?');
        $oo->execute([$row['company_id']]);
        if ((int)$oo->fetchColumn() === 1) { $done->execute(['failed', '수신거부', $row['id']]); continue; }

        $html = promo_render($c, ['id' => $row['company_id'], 'name' => $row['name']], $worksHtml);
        $ok   = false;
        $subject = promo_subject_for($c['subject'], $row['name']);
        try { $ok = send_html_mail($row['email'], $subject, $html, promo_headers((int)$row['company_id']), $attachments); }
        catch (Throwable $e) { $done->execute(['failed', mb_substr($e->getMessage(), 0, 250), $row['id']]); continue; }
        $done->execute([$ok ? 'sent' : 'failed', $ok ? '' : '발송 실패(SMTP)', $row['id']]);
        $failStreak = $ok ? 0 : $failStreak + 1;
        if ($failStreak >= PROMO_MAX_CONSECUTIVE_FAIL) {
            $pdo->prepare("UPDATE promo_campaigns SET status='canceled', finished_at=NOW() WHERE id=? AND status='sending'")->execute([$campaignId]);
            $pdo->prepare("UPDATE promo_logs SET status='pending' WHERE campaign_id=? AND status='sending'")->execute([$campaignId]);
            return;
        }
        sleep(PROMO_SEND_GAP_SEC);
    }
    $pdo->prepare("UPDATE promo_campaigns SET status='done', finished_at=NOW() WHERE id=? AND status='sending'")->execute([$campaignId]);
}

// 예약 시각이 지난 캠페인과 한도 때문에 중단된 sending 캠페인을 모두 진행한다 (cron이 1분마다 호출)
function promo_run_due(PDO $pdo): void {
    // 워커가 중간에 죽어 'sending'으로 남은 행은 되돌린다 (워커는 flock으로 한 번에 하나만 돈다)
    $pdo->exec("UPDATE promo_logs SET status='pending' WHERE status='sending'");
    $ids = $pdo->query("SELECT id FROM promo_campaigns WHERE status = 'sending' OR (status = 'scheduled' AND scheduled_at <= NOW()) ORDER BY scheduled_at, id")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($ids as $id) promo_run_campaign($pdo, (int)$id);
}
