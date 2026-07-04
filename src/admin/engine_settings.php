<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/admin_guard.php';
require_admin_role('s');
require_once __DIR__ . '/../lib/db.php';
try {
    $studioCards = db()->query('SELECT engine_key, title FROM studio_cards WHERE is_active=1 ORDER BY sort_order, id')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $studioCards = [
        ['engine_key'=>'classic',  'title'=>'Classic Lattice'],
        ['engine_key'=>'square',   'title'=>'Square Lattice'],
        ['engine_key'=>'cross',    'title'=>'Cross Lattice'],
        ['engine_key'=>'diamond',  'title'=>'Diamond Lattice'],
        ['engine_key'=>'triangle', 'title'=>'Triangle Lattice'],
        ['engine_key'=>'hexagon',  'title'=>'Hexagon Lattice'],
    ];
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
    <?php css_tag('/src/css/users.css'); ?>
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="engineSettingsPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>엔진 설정</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-sliders me-2"></i>엔진 설정</h1>
        <div style="display:flex;gap:8px;align-items:center;">
            <select id="engineSelect" class="form-select form-select-sm" style="width:200px;"></select>
            <button class="adm-edit-btn" id="btnSave" style="height:32px;padding:0 16px;">
                <i class="bi bi-check-lg"></i> 저장
            </button>
        </div>
    </div>

    <div class="eng-grid" id="settingsGrid"></div>
</div>

<script>
window.__pmokEngineLabels = <?= json_encode(array_column($studioCards, 'title', 'engine_key'), JSON_UNESCAPED_UNICODE) ?>;
window.__pmokEngineKeys   = <?= json_encode(array_column($studioCards, 'engine_key'), JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="/src/js/admin/engine_settings.js"></script>
</body>
</html>
