<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../lib/meta.php'; ?>
    <?php css_tag('/src/css/dashboard.css'); ?>
    <?php css_tag('/src/css/users.css'); ?>
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="engineSettingsPage" style="display:none;">
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-sliders me-2"></i>엔진 기본값 관리</h1>
        <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="addSettingRow()">
            <i class="bi bi-plus-lg"></i> 항목 추가
        </button>
    </div>

    <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
        <select id="engineSelect" class="form-select form-select-sm" style="width:200px;">
            <option value="classic">Classic Lattice</option>
            <option value="square">Square Lattice</option>
            <option value="cross">Cross Lattice</option>
            <option value="diamond">Diamond Lattice</option>
            <option value="triangle">Triangle Lattice</option>
            <option value="hexagon">Hexagon Lattice</option>
        </select>
        <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="saveSettings()">
            <i class="bi bi-check-lg"></i> 저장
        </button>
    </div>

    <p style="font-size:12px;color:#888;margin:-4px 0 12px;">
        해당 엔진 페이지를 새로 열면 여기서 저장한 값이 좌측 패널 슬라이더의 기본값으로 적용됩니다.
        이미 작업 중인(저장된) 도면에는 영향을 주지 않습니다.
    </p>

    <div class="adm-table-wrap">
        <table id="settingsTable">
            <thead>
                <tr>
                    <th style="width:160px;">항목</th>
                    <th style="width:140px;">키</th>
                    <th>값</th>
                    <th style="width:60px;"></th>
                </tr>
            </thead>
            <tbody id="settingsBody"></tbody>
        </table>
    </div>
</div>

<script src="/src/js/admin/engine_settings.js"></script>
</body>
</html>
