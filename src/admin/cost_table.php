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
    <?php css_tag('/src/css/admin/cost_table.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="wtAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="wtPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>원가 테이블</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-calculator me-2"></i>원가 테이블</h1>
        <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="openModal()">
            <i class="bi bi-plus-lg"></i> 추가
        </button>
    </div>

    <!-- 탭 -->
    <div class="adm-tab-bar" id="wtTabs" style="margin-bottom:16px;">
        <button class="adm-tab-btn active" data-tab="wood">목재</button>
        <button class="adm-tab-btn" data-tab="finish">마감</button>
        <button class="adm-tab-btn" data-tab="labor">인건비</button>
        <button class="adm-tab-btn" data-tab="overhead">간접비</button>
    </div>

    <!-- 인건비 탭 — 인라인 편집 -->
    <div id="wtLaborPanel" style="display:none;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <select id="wtEngineSelect" class="form-select form-select-sm" style="width:200px;">
                <?php foreach ($studioCards as $c): ?>
                <option value="<?= htmlspecialchars($c['engine_key']) ?>"><?= htmlspecialchars($c['title']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="adm-edit-btn" id="btnLaborSave" style="height:32px;padding:0 16px;">저장</button>
        </div>
        <div class="wt-dim-grid">
            <!-- 기본 단가 (고정) -->
            <div class="wt-card">
                <div class="wt-card-title">기본 단가</div>
                <div class="adm-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>항목</th>
                                <th style="width:160px;text-align:right;">단가</th>
                                <th style="width:80px;">단위</th>
                            </tr>
                        </thead>
                        <tbody id="wtLaborBody"></tbody>
                    </table>
                </div>
            </div>
            <!-- 엔진별 작업 시간 -->
            <div class="wt-card">
                <div class="wt-card-title">엔진별 작업 시간</div>
                <p style="font-size:12px;color:var(--text-muted);margin:0 0 12px;line-height:1.6;">
                    제작비 = (교차점 × 교차점당 시간<br>
                    + 짝 × (울거미 + 다듬기) + 문틀) ÷ 60 × 시간당 공임
                </p>
                <div class="adm-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>항목</th>
                                <th style="width:100px;text-align:right;">시간 (분)</th>
                                <th style="width:40px;"></th>
                            </tr>
                        </thead>
                        <tbody id="wtLaborEngineBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 간접비 탭 — 인라인 편집 -->
    <div id="wtOverheadPanel" style="display:none;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <button class="adm-edit-btn" id="btnOverheadSave" style="height:32px;padding:0 16px;">저장</button>
        </div>
        <div class="wt-card" style="max-width:440px;">
            <div class="wt-card-title">비율 설정</div>
            <p style="font-size:12px;color:var(--text-muted);margin:0 0 12px;line-height:1.6;">
                판매가 = 원가 합계 × (1 + 간접비율 + 이익률)
            </p>
            <div class="adm-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>항목</th>
                            <th style="width:120px;text-align:right;">비율 (%)</th>
                            <th style="width:60px;"></th>
                        </tr>
                    </thead>
                    <tbody id="wtOverheadBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 나머지 탭 공용 테이블 -->
    <div id="wtTableWrap" class="adm-table-wrap">
        <table id="wtTable">
            <thead>
                <tr id="wtColHeads"></tr>
            </thead>
            <tbody id="wtBody"></tbody>
        </table>
    </div>
</div>

<!-- 편집 모달 -->
<div class="adm-modal-overlay" id="wtModalOverlay">
    <div class="adm-modal" style="max-width:420px;">
        <div class="adm-modal-head">
            <h3 id="wtModalTitle">항목 추가</h3>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <input type="hidden" id="wtId">
            <input type="hidden" id="wtCategory">
            <div class="adm-mfield">
                <label>항목</label>
                <input id="wtName" type="text" placeholder="예: 홍송" maxlength="100">
            </div>
            <div id="wtPriceRow" class="wt-price-row">
                <div class="adm-mfield wt-price-field">
                    <label>단가</label>
                    <input id="wtUnitPrice" type="number" min="0" step="1" placeholder="12000" class="num-input">
                </div>
                <div class="adm-mfield wt-unit-field">
                    <label>단위</label>
                    <input id="wtUnit" type="text" placeholder="사이, 분, %…" maxlength="30">
                </div>
                <div class="adm-mfield wt-unit-field">
                    <label>단위명</label>
                    <input id="wtUnitName" type="text" placeholder="재(才)…" maxlength="50">
                </div>
            </div>
            <div id="wtFinishRow" style="display:none;">
                <div style="display:flex;gap:8px;margin-bottom:0;">
                    <div class="adm-mfield" style="flex:1;">
                        <label>작업 시간 <small style="color:var(--text-muted);font-weight:400;">(분/㎡)</small></label>
                        <input id="wtWorkTimeMin" type="number" min="0" step="1" placeholder="10" class="num-input">
                    </div>
                    <div class="adm-mfield" style="flex:1;">
                        <label>도포 횟수 <small style="color:var(--text-muted);font-weight:400;">(회)</small></label>
                        <input id="wtCoatCount" type="number" min="1" step="1" placeholder="2" class="num-input">
                    </div>
                </div>
            </div>
            <div id="wtWeightRow" class="adm-mfield">
                <label>가중치</label>
                <input id="wtWeight" type="number" min="0" step="0.01" placeholder="1.00" class="num-input">
            </div>
            <div class="adm-mfield" id="wtEngineField" style="display:none;">
                <label>엔진 <small style="color:var(--text-muted);font-weight:400;">(비우면 공통)</small></label>
                <select id="wtEngine">
                    <option value="">— 공통 —</option>
                    <?php foreach ($studioCards as $c): ?>
                    <option value="<?= htmlspecialchars($c['engine_key']) ?>"><?= htmlspecialchars($c['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="adm-mfield">
                <label>메모</label>
                <textarea id="wtNotes" rows="3"
                    style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:var(--r-sm);background:var(--bg);font-family:inherit;font-size:13px;color:var(--text);outline:none;resize:vertical;"></textarea>
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" onclick="saveItem()">저장</button>
        </div>
    </div>
</div>

<script>
window.__pmokEngineLabels = <?= json_encode(array_column($studioCards, 'title', 'engine_key'), JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="/src/js/admin/cost_table.js"></script>
</body>
</html>
