<?php
header('Content-Type: application/json; charset=UTF-8');
set_exception_handler(function (Throwable $e) {
    error_log('admin notices: ' . $e->getMessage());
    if (!headers_sent()) http_response_code(500);
    echo json_encode(['error' => '처리 중 오류가 발생했습니다.']);
    exit;
});
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/notices.php';

$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') {
    http_response_code(403);
    echo json_encode(['error' => '권한이 없습니다.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$pdo    = db();
notice_ensure_table($pdo);

if ($method === 'GET') {
    $rows = $pdo->query('SELECT id, title, title_en, label, label_en, body, body_en, cta_text, cta_text_en, cta_url, is_pinned, is_active, published_at, created_at, updated_at
                         FROM notices ORDER BY is_pinned DESC, published_at DESC, id DESC')->fetchAll();
    echo json_encode(['notices' => $rows, 'now' => date('Y-m-d H:i:s')]);
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = (string)($body['action'] ?? '');

if ($action === 'save') {
    $id       = (int)($body['id'] ?? 0);
    $title    = trim((string)($body['title'] ?? ''));
    $content  = notice_sanitize_html((string)($body['body'] ?? ''));
    $titleEn  = trim((string)($body['title_en'] ?? ''));
    $contentEn = notice_sanitize_html((string)($body['body_en'] ?? ''));
    $label    = trim((string)($body['label'] ?? ''));
    $labelEn  = trim((string)($body['label_en'] ?? ''));
    $ctaText  = trim((string)($body['cta_text'] ?? ''));
    $ctaTextEn = trim((string)($body['cta_text_en'] ?? ''));
    $ctaUrl   = trim((string)($body['cta_url'] ?? ''));
    $pinned   = !empty($body['is_pinned']) ? 1 : 0;
    $active   = array_key_exists('is_active', $body) ? (!empty($body['is_active']) ? 1 : 0) : 1;

    if ($title === '' || $content === '') {
        echo json_encode(['error' => '제목과 내용을 모두 입력하세요.']);
        exit;
    }
    if (mb_strlen($title) > 200 || mb_strlen($titleEn) > 200) {
        echo json_encode(['error' => '제목은 200자 이내로 입력하세요.']);
        exit;
    }
    if (mb_strlen($label) > 30 || mb_strlen($labelEn) > 30) {
        echo json_encode(['error' => '말머리는 30자 이내로 입력하세요.']);
        exit;
    }
    if (mb_strlen($ctaText) > 60 || mb_strlen($ctaTextEn) > 60) {
        echo json_encode(['error' => '버튼 문구는 60자 이내로 입력하세요.']);
        exit;
    }
    if ($ctaText !== '' || $ctaUrl !== '') {
        $safeUrl = notice_safe_href($ctaUrl);
        if ($ctaText === '' || $safeUrl === null || mb_strlen($safeUrl) > 300) {
            echo json_encode(['error' => '버튼은 문구와 주소를 함께 입력하세요. 주소는 https://, mailto:, tel: 또는 / 로 시작해야 합니다.']);
            exit;
        }
        $ctaUrl = $safeUrl;
    } else {
        $ctaTextEn = '';
    }
    $ts = strtotime(str_replace('T', ' ', (string)($body['published_at'] ?? '')));
    $publishedAt = $ts ? date('Y-m-d H:i:s', $ts) : date('Y-m-d H:i:s');

    $titleEn   = $titleEn === '' ? null : $titleEn;
    $label     = $label === '' ? null : $label;
    $labelEn   = $labelEn === '' ? null : $labelEn;
    $ctaText   = $ctaText === '' ? null : $ctaText;
    $ctaTextEn = $ctaTextEn === '' ? null : $ctaTextEn;
    $ctaUrl    = $ctaUrl === '' ? null : $ctaUrl;
    $contentEn = $contentEn === '' ? null : $contentEn;

    if ($id) {
        $pdo->prepare('UPDATE notices SET title=?, body=?, title_en=?, body_en=?, label=?, label_en=?, cta_text=?, cta_text_en=?, cta_url=?, is_pinned=?, is_active=?, published_at=? WHERE id=?')
            ->execute([$title, $content, $titleEn, $contentEn, $label, $labelEn, $ctaText, $ctaTextEn, $ctaUrl, $pinned, $active, $publishedAt, $id]);
    } else {
        $pdo->prepare('INSERT INTO notices (title, body, title_en, body_en, label, label_en, cta_text, cta_text_en, cta_url, is_pinned, is_active, published_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([$title, $content, $titleEn, $contentEn, $label, $labelEn, $ctaText, $ctaTextEn, $ctaUrl, $pinned, $active, $publishedAt]);
        $id = (int)$pdo->lastInsertId();
    }
    echo json_encode(['ok' => true, 'id' => $id]);
    exit;
}

$id = (int)($body['id'] ?? 0);

if ($action === 'delete') {
    $pdo->prepare('DELETE FROM notices WHERE id = ?')->execute([$id]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'toggle') {
    $pdo->prepare('UPDATE notices SET is_active = 1 - is_active WHERE id = ?')->execute([$id]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'toggle_pin') {
    $pdo->prepare('UPDATE notices SET is_pinned = 1 - is_pinned WHERE id = ?')->execute([$id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action']);
