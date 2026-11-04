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

function recipient_count(PDO $pdo, string $industries): int {
    $inds = array_values(array_intersect(PROMO_INDUSTRIES, array_filter(explode(',', $industries))));
    $sql  = 'SELECT COUNT(*) FROM promo_companies WHERE opt_out = 0';
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
        "SELECT c.id, c.title, c.subject, c.industries, c.attachment_name, c.attachment_size, c.status, c.scheduled_at, c.started_at, c.finished_at, c.created_at,
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
        'companies'  => $pdo->query('SELECT id, name, industry FROM promo_companies ORDER BY name')->fetchAll(),
        'templates'  => $pdo->query('SELECT id, name, subject, html_body, attachment_name, attachment_size, updated_at FROM promo_templates ORDER BY updated_at DESC, id DESC')->fetchAll(),
        'counts'     => $counts,
        'sent_24h'   => promo_sent_last_24h($pdo),
        'daily_limit'=> PROMO_DAILY_LIMIT,
    ]);
    exit;
}

const PROMO_PDF_MAX = 10 * 1024 * 1024; // 메일 첨부는 10MB 이하 — 크면 수신 서버에서 거부·스팸 처리되기 쉽다

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
    if ($f['size'] > PROMO_PDF_MAX) fail(400, 'PDF는 10MB 이하여야 합니다.');
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
        $cur = $pdo->prepare('SELECT attachment_path FROM promo_templates WHERE id = ?');
        $cur->execute([$id]);
        $oldPath = $cur->fetchColumn();
        if ($oldPath === false) fail(404, '템플릿을 찾을 수 없습니다.');
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
    if ($title === '')   fail(400, '관리용 이름을 입력하세요.');
    if ($subject === '') fail(400, '메일 제목을 입력하세요.');

    if ($id) {
        $c = load_campaign($pdo, $id);
        if (!in_array($c['status'], ['draft', 'canceled'], true)) fail(409, '발송 중이거나 완료된 캠페인은 수정할 수 없습니다. 예약은 취소 후 수정하세요.');
        $pdo->prepare("UPDATE promo_campaigns SET title=?, subject=?, html_body=?, industries=?, status='draft', scheduled_at=NULL WHERE id=?")
            ->execute([$title, $subject, $html, $inds, $id]);
    } else {
        $pdo->prepare('INSERT INTO promo_campaigns (title, subject, html_body, industries) VALUES (?,?,?,?)')
            ->execute([$title, $subject, $html, $inds]);
        $id = (int)$pdo->lastInsertId();
    }
    echo json_encode(['ok' => true, 'id' => $id]);
    exit;
}

if ($action === 'preview' || $action === 'test') {
    $html    = (string)($body['html_body'] ?? '');
    $camp    = ['html_body' => $html, 'subject' => (string)($body['subject'] ?? '')];
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
    if (recipient_count($pdo, $c['industries']) === 0) fail(400, '발송 대상 회사가 없습니다.');
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
