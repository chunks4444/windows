<?php
header('Content-Type: application/json; charset=UTF-8');
set_exception_handler(function(Throwable $e) {
    if (!headers_sent()) http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
});
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/promo_mail.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403);
    echo json_encode(['error' => '권한이 없습니다.']);
    exit;
}

$pdo = db();
promo_ensure_tables($pdo);

function fail(int $code, string $msg): never {
    http_response_code($code);
    echo json_encode(['error' => $msg]);
    exit;
}

// 전체 HTML 문서(<!DOCTYPE>·<html>)로 짠 홍보문구인지. 이런 본문을 에디터(Jodit)의 '일반 편집' 모드로 저장하면 <head>·<style>(모바일 반응형)이
// 지워진 채 저장돼 메일이 깨진다 — 이미 전체 문서인 본문을 문서 구조 없이 덮어쓰려는 저장은 막는다 (2026-11-09).
function is_full_html_doc(string $h): bool { return (bool)preg_match('/<!DOCTYPE|<html\b/i', $h); }
const FULL_DOC_MSG = '이 홍보문구는 전체 HTML 문서(<html>·<head> 포함)로 만들어져 있어, 문서 구조가 빠진 내용으로는 저장할 수 없습니다. 일반 편집기를 쓰면 모바일 스타일이 지워집니다. 코드 입력칸에서 수정하거나, 새 템플릿으로 저장하세요.';

function load_campaign(PDO $pdo, int $id): array {
    $stmt = $pdo->prepare('SELECT * FROM promo_campaigns WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: fail(404, '캠페인을 찾을 수 없습니다.');
}

// 발송 워커를 백그라운드로 띄운다. 실패해도 cron(1분 주기)이 이어받으므로 조용히 넘어간다.
function spawn_worker(): void {
    $script = realpath(__DIR__ . '/../../../ops/promo_send_worker.php');
    if (!$script) return;
    @proc_open('nohup php ' . escapeshellarg($script) . ' >/dev/null 2>&1 &', [], $pipes);
}

function clean_list(array $vals, ?array $allowed = null): string {
    $out = [];
    foreach ($vals as $v) {
        $v = trim((string)$v);
        if ($v === '') continue;
        if ($allowed !== null && !in_array($v, $allowed, true)) continue;
        $out[] = $v;
    }
    return implode(',', array_unique($out));
}

function recipient_count(PDO $pdo, string $industries, string $companyIds = ''): int {
    $inds = array_values(array_intersect(PROMO_INDUSTRIES, array_filter(explode(',', $industries))));
    $sql  = 'SELECT COUNT(*) FROM promo_companies WHERE opt_out = 0';
    $ids  = promo_id_list($companyIds);
    if ($ids) {   // 회사를 직접 골랐으면 그 회사들만
        $stmt = $pdo->prepare($sql . ' AND id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')');
        $stmt->execute($ids);
        return (int)$stmt->fetchColumn();
    }
    if ($inds) $sql .= ' AND industry IN (' . implode(',', array_fill(0, count($inds), '?')) . ')';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($inds);
    return (int)$stmt->fetchColumn();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id'])) {
        $c = load_campaign($pdo, (int)$_GET['id']);
        $stmt = $pdo->prepare('SELECT id, company_id, name, email, status, error, sent_at FROM promo_logs WHERE campaign_id = ? ORDER BY id');
        $stmt->execute([$c['id']]);
        echo json_encode(['campaign' => $c, 'logs' => $stmt->fetchAll()]);
        exit;
    }
    $rows = $pdo->query(
        "SELECT c.id, c.title, c.subject, c.industries, c.company_ids, c.attachment_name, c.attachment_size, c.status, c.scheduled_at, c.started_at, c.finished_at, c.created_at,
                COALESCE(SUM(l.status IN ('pending','sending')), 0) AS pending,
                COALESCE(SUM(l.status = 'sent'), 0)   AS sent,
                COALESCE(SUM(l.status = 'failed'), 0) AS failed,
                COUNT(l.id) AS total
         FROM promo_campaigns c LEFT JOIN promo_logs l ON l.campaign_id = c.id
         GROUP BY c.id ORDER BY c.id DESC"
    )->fetchAll();
    $counts = [];
    foreach (array_merge([''], PROMO_INDUSTRIES) as $ind) $counts[$ind] = recipient_count($pdo, $ind);
    echo json_encode([
        'campaigns'  => $rows,
        'industries' => PROMO_INDUSTRIES,
        // 수신거부 회사는 제외. 발송 횟수·마지막 발송은 '대상 회사 직접 선택' 목록과 테스트 회사 선택에 쓴다
        'companies'  => $pdo->query("SELECT c.id, c.name, c.industry, COALESCE(s.n, 0) AS sent_count, s.last_sent_at
                                     FROM promo_companies c
                                     LEFT JOIN (SELECT company_id, COUNT(*) AS n, MAX(sent_at) AS last_sent_at FROM promo_logs WHERE status = 'sent' GROUP BY company_id) s ON s.company_id = c.id
                                     WHERE c.opt_out = 0 ORDER BY c.name")->fetchAll(),
        'templates'  => $pdo->query('SELECT id, name, subject, html_body, attachment_name, attachment_size, updated_at FROM promo_templates ORDER BY updated_at DESC, id DESC')->fetchAll(),
        'counts'     => $counts,
        'sent_24h'   => promo_sent_last_24h($pdo),
        'daily_limit'=> PROMO_DAILY_LIMIT,
    ]);
    exit;
}

const PROMO_PDF_MAX = 30 * 1024 * 1024; // 업로드 한도. 메일에 붙이는 건 PROMO_ATTACH_MAX(8MB) 이하만, 큰 파일은 다운로드 링크로만 간다

function delete_pdf_file(string $path): void {
    $root = realpath(__DIR__ . '/../../../uploads/promo');
    $file = realpath(__DIR__ . '/../../..' . $path);
    if ($root && $file && str_starts_with($file, $root . DIRECTORY_SEPARATOR)) @unlink($file);
}

// 같은 PDF 파일을 캠페인·템플릿 여러 곳이 공유하므로, 어디에서도 쓰지 않게 된 뒤에만 실제 파일을 지운다 (DB를 먼저 바꾼 다음 호출)
function release_pdf(PDO $pdo, string $path): void {
    if ($path === '') return;
    $n = $pdo->prepare('SELECT (SELECT COUNT(*) FROM promo_campaigns WHERE attachment_path = ?) + (SELECT COUNT(*) FROM promo_templates WHERE attachment_path = ?)');
    $n->execute([$path, $path]);
    if ((int)$n->fetchColumn() === 0) delete_pdf_file($path);
}

// PDF 업로드는 multipart(FormData)로 받는다
if (($_POST['action'] ?? '') === 'upload_pdf') {
    $c = load_campaign($pdo, (int)($_POST['id'] ?? 0));
    if (!in_array($c['status'], ['draft', 'canceled'], true)) fail(409, '발송 중이거나 완료된 캠페인은 수정할 수 없습니다.');
    $f = $_FILES['pdf'] ?? null;
    if (!$f || $f['error'] !== UPLOAD_ERR_OK) fail(400, 'PDF 업로드에 실패했습니다.');
    if ($f['size'] > PROMO_PDF_MAX) fail(400, 'PDF는 30MB 이하여야 합니다.');
    $head = (string)@file_get_contents($f['tmp_name'], false, null, 0, 5);
    if ($head !== '%PDF-') fail(400, 'PDF 파일만 올릴 수 있습니다.');

    $dir = __DIR__ . '/../../../uploads/promo';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $stored = bin2hex(random_bytes(16)) . '.pdf';
    if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $stored)) fail(500, '파일을 저장하지 못했습니다.');
    // 업로드 임시 파일은 0600이라, 발송 워커(cron, 다른 사용자)가 읽을 수 있게 권한을 연다
    chmod($dir . '/' . $stored, 0644);

    // 받는 사람에게 보일 파일명 — 경로·제어문자 제거, 확장자 .pdf 보장
    $name = preg_replace('/[\x00-\x1F\x7F\\\\\/:*?"<>|]/u', '', basename((string)$f['name']));
    $name = mb_substr(preg_replace('/\.pdf$/i', '', $name), 0, 100) . '.pdf';

    $pdo->prepare('UPDATE promo_campaigns SET attachment_path=?, attachment_name=?, attachment_size=? WHERE id=?')
        ->execute(['/uploads/promo/' . $stored, $name, (int)$f['size'], $c['id']]);
    release_pdf($pdo, $c['attachment_path']);
    echo json_encode(['ok' => true, 'attachment_name' => $name, 'attachment_size' => (int)$f['size']]);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? '';

// 문구 템플릿 — 제목+본문 HTML을 이름 붙여 저장해 두고 캠페인에서 불러와 계속 고쳐 쓴다
if ($action === 'save_template') {
    $id      = (int)($body['id'] ?? 0);
    $name    = trim($body['name'] ?? '');
    $subject = trim($body['subject'] ?? '');
    $html    = (string)($body['html_body'] ?? '');
    $campId  = (int)($body['campaign_id'] ?? 0);
    if ($name === '' || mb_strlen($name) > 100) fail(400, '템플릿 이름을 입력하세요. (100자 이하)');
    $dup = $pdo->prepare('SELECT id FROM promo_templates WHERE name = ? AND id <> ?');
    $dup->execute([$name, $id]);
    if ($dup->fetchColumn()) fail(409, '같은 이름의 템플릿이 이미 있습니다.');

    // 첨부 PDF: 편집 중인 캠페인(저장된 상태)의 첨부를 그대로 템플릿에 연결한다 (같은 파일 공유). campaign_id가 없으면 첨부는 건드리지 않는다.
    $att = $campId ? load_campaign($pdo, $campId) : null;
    $oldPath = '';
    if ($id) {
        $cur = $pdo->prepare('SELECT attachment_path, html_body FROM promo_templates WHERE id = ?');
        $cur->execute([$id]);
        $curRow = $cur->fetch();
        if (!$curRow) fail(404, '템플릿을 찾을 수 없습니다.');
        $oldPath = $curRow['attachment_path'];
        if (is_full_html_doc((string)$curRow['html_body']) && !is_full_html_doc($html)) fail(409, FULL_DOC_MSG);
        $pdo->prepare('UPDATE promo_templates SET name=?, subject=?, html_body=? WHERE id=?')->execute([$name, $subject, $html, $id]);
    } else {
        $pdo->prepare('INSERT INTO promo_templates (name, subject, html_body) VALUES (?,?,?)')->execute([$name, $subject, $html]);
        $id = (int)$pdo->lastInsertId();
    }
    if ($att) {
        $pdo->prepare('UPDATE promo_templates SET attachment_path=?, attachment_name=?, attachment_size=? WHERE id=?')
            ->execute([$att['attachment_path'], $att['attachment_name'], (int)$att['attachment_size'], $id]);
        release_pdf($pdo, (string)$oldPath);
    }
    echo json_encode(['ok' => true, 'id' => $id]);
    exit;
}

// 템플릿의 첨부 PDF를 캠페인에 적용 (같은 파일을 가리키게만 하므로 복사 없음)
if ($action === 'apply_template') {
    $c = load_campaign($pdo, (int)($body['campaign_id'] ?? 0));
    if (!in_array($c['status'], ['draft', 'canceled'], true)) fail(409, '발송 중이거나 완료된 캠페인은 수정할 수 없습니다.');
    $t = $pdo->prepare('SELECT attachment_path, attachment_name, attachment_size FROM promo_templates WHERE id = ?');
    $t->execute([(int)($body['template_id'] ?? 0)]);
    $t = $t->fetch() ?: fail(404, '템플릿을 찾을 수 없습니다.');
    $pdo->prepare('UPDATE promo_campaigns SET attachment_path=?, attachment_name=?, attachment_size=? WHERE id=?')
        ->execute([$t['attachment_path'], $t['attachment_name'], (int)$t['attachment_size'], $c['id']]);
    if ($c['attachment_path'] !== $t['attachment_path']) release_pdf($pdo, $c['attachment_path']);
    echo json_encode(['ok' => true, 'attachment_name' => $t['attachment_name'], 'attachment_size' => (int)$t['attachment_size']]);
    exit;
}

if ($action === 'delete_template') {
    $id  = (int)($body['id'] ?? 0);
    $cur = $pdo->prepare('SELECT attachment_path FROM promo_templates WHERE id = ?');
    $cur->execute([$id]);
    $path = (string)$cur->fetchColumn();
    $pdo->prepare('DELETE FROM promo_templates WHERE id = ?')->execute([$id]);
    release_pdf($pdo, $path);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'remove_pdf') {
    $c = load_campaign($pdo, (int)($body['id'] ?? 0));
    if (!in_array($c['status'], ['draft', 'canceled'], true)) fail(409, '발송 중이거나 완료된 캠페인은 수정할 수 없습니다.');
    $pdo->prepare("UPDATE promo_campaigns SET attachment_path='', attachment_name='', attachment_size=0 WHERE id=?")->execute([$c['id']]);
    release_pdf($pdo, $c['attachment_path']);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'save') {
    $id = (int)($body['id'] ?? 0);
    $title   = trim($body['title'] ?? '');
    $subject = trim($body['subject'] ?? '');
    $html    = (string)($body['html_body'] ?? '');
    $inds    = clean_list((array)($body['industries'] ?? []), PROMO_INDUSTRIES);
    // 직접 고른 대상 회사 — 실제로 있고 수신거부가 아닌 회사만 저장한다
    $cids = array_values(array_unique(array_filter(array_map('intval', (array)($body['company_ids'] ?? [])), fn($v) => $v > 0)));
    if ($cids) {
        $q = $pdo->prepare('SELECT id FROM promo_companies WHERE opt_out = 0 AND id IN (' . implode(',', array_fill(0, count($cids), '?')) . ')');
        $q->execute($cids);
        $cids = array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN));
    }
    $companyIds = implode(',', $cids);
    if ($title === '')   fail(400, '관리용 이름을 입력하세요.');
    if ($subject === '' && empty($body['draft'])) fail(400, '메일 제목을 입력하세요.');

    if ($id) {
        $c = load_campaign($pdo, $id);
        if (!in_array($c['status'], ['draft', 'canceled'], true)) fail(409, '발송 중이거나 완료된 캠페인은 수정할 수 없습니다. 예약은 취소 후 수정하세요.');
        if (is_full_html_doc((string)$c['html_body']) && !is_full_html_doc($html)) fail(409, FULL_DOC_MSG);
        $pdo->prepare("UPDATE promo_campaigns SET title=?, subject=?, html_body=?, industries=?, company_ids=?, status='draft', scheduled_at=NULL WHERE id=?")
            ->execute([$title, $subject, $html, $inds, $companyIds, $id]);
    } else {
        $pdo->prepare('INSERT INTO promo_campaigns (title, subject, html_body, industries, company_ids) VALUES (?,?,?,?,?)')
            ->execute([$title, $subject, $html, $inds, $companyIds]);
        $id = (int)$pdo->lastInsertId();
    }
    echo json_encode(['ok' => true, 'id' => $id]);
    exit;
}

if ($action === 'preview' || $action === 'test') {
    $html    = (string)($body['html_body'] ?? '');
    $camp    = ['html_body' => $html, 'subject' => (string)($body['subject'] ?? '')];
    // 저장된 캠페인이면 첨부 PDF 다운로드 링크({{첨부파일링크}})가 실제 파일을 가리키게 한다
    if ((int)($body['id'] ?? 0) > 0) $camp['attachment_path'] = load_campaign($pdo, (int)$body['id'])['attachment_path'];
    // 테스트는 DB에 등록된 회사를 골라 그 회사명으로 치환해 볼 수 있다. 수신거부 링크는 id 0(무효)로 두어
    // 테스트 메일의 링크를 눌러도 실제 회사가 수신거부 처리되지 않게 한다.
    $testName = '(회사명)';
    if ((int)($body['company_id'] ?? 0) > 0) {
        $co = $pdo->prepare('SELECT name FROM promo_companies WHERE id = ?');
        $co->execute([(int)$body['company_id']]);
        $testName = $co->fetchColumn() ?: $testName;
    }
    $rendered = promo_render($camp, ['id' => 0, 'name' => $testName], '');
    if ($action === 'preview') { echo json_encode(['ok' => true, 'html' => $rendered]); exit; }

    $to = trim($body['to'] ?? '');
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) fail(400, '테스트 받을 이메일 형식이 올바르지 않습니다.');
    // 저장된 캠페인이면 첨부 PDF도 같이 보내 실제 모습 그대로 확인할 수 있게 한다
    $attachments = [];
    if ((int)($body['id'] ?? 0) > 0) {
        $attachments = promo_attachments(load_campaign($pdo, (int)$body['id']));
        if ($attachments === null) fail(500, '첨부 PDF 파일을 찾을 수 없습니다. 다시 업로드하세요.');
    }
    $ok = send_html_mail($to, '[테스트] ' . promo_subject_for($camp['subject'], $testName), $rendered, '', $attachments);
    if (!$ok) fail(502, '테스트 발송에 실패했습니다. 메일 발송 설정(SMTP)을 확인하세요.');
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'send_now' || $action === 'schedule') {
    $c = load_campaign($pdo, (int)($body['id'] ?? 0));
    if (!in_array($c['status'], ['draft', 'canceled'], true)) fail(409, '이미 예약·발송된 캠페인입니다.');
    if (trim((string)$c['html_body']) === '') fail(400, '홍보문구(HTML)가 비어 있습니다.');
    if (promo_attachments($c) === null) fail(400, '첨부 PDF 파일을 찾을 수 없습니다. 다시 업로드하세요.');
    if (recipient_count($pdo, $c['industries'], (string)($c['company_ids'] ?? '')) === 0) fail(400, '발송 대상 회사가 없습니다.');
    // 한 번 발송을 시도했던(취소된) 캠페인은 이력을 비우고 새로 시작한다
    $pdo->prepare('DELETE FROM promo_logs WHERE campaign_id = ?')->execute([$c['id']]);

    if ($action === 'send_now') {
        $at = date('Y-m-d H:i:s');
    } else {
        $ts = strtotime((string)($body['scheduled_at'] ?? ''));
        if (!$ts) fail(400, '예약 시각 형식이 올바르지 않습니다.');
        if ($ts < time() + 60) fail(400, '예약 시각은 지금보다 1분 이상 뒤여야 합니다.');
        $at = date('Y-m-d H:i:s', $ts);
    }
    $pdo->prepare("UPDATE promo_campaigns SET status='scheduled', scheduled_at=?, started_at=NULL, finished_at=NULL WHERE id=?")->execute([$at, $c['id']]);
    if ($action === 'send_now') spawn_worker();
    echo json_encode(['ok' => true, 'scheduled_at' => $at]);
    exit;
}

// 이미 보낸·실패한 회사를 다시 보내기 — 해당 이력 행을 '대기'로 되돌리고 캠페인을 다시 진행시킨다.
// log_ids를 주면 그 행들만, scope='failed'면 실패한 행 전부. 수신거부 회사는 제외한다.
if ($action === 'requeue') {
    $c = load_campaign($pdo, (int)($body['id'] ?? 0));
    if ($c['status'] === 'draft') fail(409, '아직 발송하지 않은 캠페인입니다.');
    if (promo_attachments($c) === null) fail(400, '첨부 PDF 파일을 찾을 수 없습니다. 다시 업로드하세요.');

    $sql  = "SELECT l.id, l.status FROM promo_logs l LEFT JOIN promo_companies p ON p.id = l.company_id
             WHERE l.campaign_id = ? AND l.status IN ('sent','failed') AND COALESCE(p.opt_out, 0) = 0";
    $args = [$c['id']];
    $ids  = array_values(array_filter(array_map('intval', (array)($body['log_ids'] ?? []))));
    if ($ids) { $sql .= ' AND l.id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')'; $args = array_merge($args, $ids); }
    elseif (($body['scope'] ?? '') === 'failed') { $sql .= " AND l.status = 'failed'"; }
    else fail(400, '다시 보낼 회사를 선택하세요.');
    $stmt = $pdo->prepare($sql);
    $stmt->execute($args);
    $rows = $stmt->fetchAll();
    if (!$rows) fail(400, '다시 보낼 수 있는 회사가 없습니다. (수신거부 회사는 제외됩니다)');

    // 템플릿을 고르면 그 템플릿의 최신 제목·본문(·PDF)으로 캠페인 문구를 바꿔서 다시 보낸다.
    // 캠페인은 만들 때의 문구를 따로 저장해 두므로, 템플릿을 고쳐도 이미 발송한 캠페인에는 자동 반영되지 않는다.
    $tplId = (int)($body['template_id'] ?? 0);
    if ($tplId) {
        if ($c['status'] === 'sending') fail(409, '발송 중에는 문구를 바꿀 수 없습니다. 발송이 끝난 뒤(또는 취소 후) 다시 시도하세요.');
        $t = $pdo->prepare('SELECT subject, html_body, attachment_path, attachment_name, attachment_size FROM promo_templates WHERE id = ?');
        $t->execute([$tplId]);
        $t = $t->fetch() ?: fail(404, '템플릿을 찾을 수 없습니다.');
        if (trim((string)$t['html_body']) === '') fail(400, '템플릿 본문이 비어 있습니다.');
        $pdo->prepare('UPDATE promo_campaigns SET subject=?, html_body=? WHERE id=?')
            ->execute([trim((string)$t['subject']) !== '' ? $t['subject'] : $c['subject'], $t['html_body'], $c['id']]);
        if ((string)$t['attachment_path'] !== '') {
            $pdo->prepare('UPDATE promo_campaigns SET attachment_path=?, attachment_name=?, attachment_size=? WHERE id=?')
                ->execute([$t['attachment_path'], $t['attachment_name'], (int)$t['attachment_size'], $c['id']]);
            if ($c['attachment_path'] !== $t['attachment_path']) release_pdf($pdo, $c['attachment_path']);
        }
        $c = load_campaign($pdo, (int)$c['id']);
        if (promo_attachments($c) === null) fail(400, '첨부 PDF 파일을 찾을 수 없습니다. 다시 업로드하세요.');
    }

    $target = array_column($rows, 'id');
    $pdo->prepare("UPDATE promo_logs SET status='pending', error='' WHERE status IN ('sent','failed') AND id IN (" . implode(',', array_fill(0, count($target), '?')) . ')')->execute($target);
    // 끝났거나 취소된 캠페인은 지금부터 다시 진행시키고, 진행·예약 중이면 그대로 두면 이어서 보낸다
    if (in_array($c['status'], ['done', 'canceled'], true)) {
        $pdo->prepare("UPDATE promo_campaigns SET status='scheduled', scheduled_at=NOW(), finished_at=NULL WHERE id=? AND status IN ('done','canceled')")->execute([$c['id']]);
    }
    spawn_worker();
    echo json_encode(['ok' => true, 'requeued' => count($target), 'resent_sent' => count(array_filter($rows, fn($r) => $r['status'] === 'sent'))]);
    exit;
}

if ($action === 'cancel') {
    $c = load_campaign($pdo, (int)($body['id'] ?? 0));
    if (!in_array($c['status'], ['scheduled', 'sending'], true)) fail(409, '취소할 수 있는 상태가 아닙니다.');
    $pdo->prepare("UPDATE promo_campaigns SET status='canceled', finished_at=NOW() WHERE id=?")->execute([$c['id']]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'delete') {
    $c = load_campaign($pdo, (int)($body['id'] ?? 0));
    if (in_array($c['status'], ['scheduled', 'sending'], true)) fail(409, '예약·발송 중인 캠페인은 먼저 취소하세요.');
    $pdo->prepare('DELETE FROM promo_logs WHERE campaign_id = ?')->execute([$c['id']]);
    $pdo->prepare('DELETE FROM promo_campaigns WHERE id = ?')->execute([$c['id']]);
    release_pdf($pdo, $c['attachment_path']);
    echo json_encode(['ok' => true]);
    exit;
}

fail(400, 'Unknown action');
