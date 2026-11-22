<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/admin_guard.php';
require_admin_role('s');
if ((jwt_from_request()['via'] ?? null) !== 'pw') {
    http_response_code(403);
    exit('이메일 계정으로 로그인해야 접근할 수 있습니다. SNS 로그인은 사용할 수 없습니다. 로그아웃 후 이메일·비밀번호로 다시 로그인하세요.');
}
require_once __DIR__ . '/../lib/dada_import.php';

$message = null;
$error = null;
$stats = null;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $origin = parse_url($_SERVER['HTTP_ORIGIN'] ?? '', PHP_URL_HOST);
        if ($origin !== null && $origin !== ($_SERVER['HTTP_HOST'] ? explode(':', $_SERVER['HTTP_HOST'])[0] : '')) {
            throw new RuntimeException('잘못된 요청입니다.');
        }
        $f = $_FILES['xlsx'] ?? null;
        if (!$f || $f['error'] !== UPLOAD_ERR_OK || !preg_match('/\.xlsx$/i', $f['name'])) {
            throw new RuntimeException('.xlsx 파일을 선택하세요.');
        }
        $res = dada_import_xlsx($f['tmp_name']);
        $message = "{$res['loaded']}건 적재 완료 (합계·빈 행 {$res['skipped']}건 제외)";
    }
    $pdo = dada_db();
    dada_ensure_schema($pdo);
    $stats = dada_stats($pdo);
} catch (Throwable $e) {
    error_log('dada_bookings: ' . $e->getMessage());
    $error = $e instanceof RuntimeException ? $e->getMessage() : 'DB 오류가 발생했습니다. dada_hotel DB 생성·권한을 확인하세요. (로그 참조)';
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../lib/meta.php'; ?>
    <?php meta_tags(); ?>
<?php css_tag('/src/css/dashboard.css'); ?>
    <?php css_tag('/src/css/admin.css'); ?>
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>
<?php include __DIR__ . '/../components/admin_sidenav.php'; ?>

<div class="db-page">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>다다호텔 예약 데이터</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-building me-2"></i>다다호텔 예약 데이터</h1>
    </div>

    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="mb-4" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <input type="file" name="xlsx" accept=".xlsx" class="form-control" style="max-width:360px;" required>
        <button class="adm-edit-btn" style="height:36px;padding:0 16px;"><i class="bi bi-upload"></i> 예약 조회 엑셀 올리기</button>
    </form>

    <?php if ($stats): ?>
    <div class="adm-table-wrap mb-4">
        <table>
            <tbody>
                <tr><th style="width:220px;">원본(raw_bookings)</th><td><?= number_format($stats['raw']) ?>건</td></tr>
                <tr><th>정제(bookings)</th><td><?= number_format($stats['bookings']) ?>건</td></tr>
                <tr><th>예측에 사용 가능</th><td><?= number_format($stats['usable']) ?>건</td></tr>
                <tr><th>제외: OVER 객실(8888·9999)</th><td><?= number_format($stats['over']) ?>건</td></tr>
                <tr><th>제외: 0원 예약</th><td><?= number_format($stats['zero_amt']) ?>건</td></tr>
                <tr><th>제외: 0박 예약</th><td><?= number_format($stats['zero_night']) ?>건</td></tr>
                <tr><th>표시: 같은 방 기간 겹침</th><td><?= number_format($stats['overlap']) ?>건</td></tr>
                <tr><th>입실일 범위</th><td><?= htmlspecialchars(($stats['range']['a'] ?? '-') . ' ~ ' . ($stats['range']['b'] ?? '-')) ?></td></tr>
            </tbody>
        </table>
    </div>

    <div class="adm-table-wrap" style="max-width:360px;">
        <table>
            <thead><tr><th>상태값</th><th style="width:100px;">건수</th></tr></thead>
            <tbody>
            <?php foreach ($stats['status'] as $s): ?>
                <tr><td><?= htmlspecialchars((string)$s['status']) ?></td><td><?= number_format((int)$s['n']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
