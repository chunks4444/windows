<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/admin_guard.php';
require_admin_role('s');
require_once __DIR__ . '/../lib/engine_settings.php';
$libPatternCats = get_pattern_categories();
$libPatternMods = get_pattern_modifiers();
$pymCategoryId  = 0;
foreach ($libPatternCats as $c) {
    if (($c['code'] ?? '') === 'PYM') { $pymCategoryId = (int)$c['id']; break; }
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
    
    <?php css_tag('/src/css/admin/collection.css'); ?>
    <style>
        .lib-admin-filter-select {
            height: 32px; padding: 0 10px;
            background: var(--bg); border: 1px solid var(--border);
            border-radius: var(--r-sm);
            font-family: inherit; font-size: var(--fs-13); font-weight: 600;
            color: var(--text); cursor: pointer;
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="libAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="libPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>컬렉션</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-image me-2"></i>컬렉션</h1>
        <div style="display:flex;gap:8px;align-items:center;">
            <div style="display:flex;gap:4px;">
                <button class="adm-edit-btn lib-status-btn" data-status="all"     style="height:32px;padding:0 12px;" onclick="setStatusFilter('all')">전체</button>
                <button class="adm-edit-btn lib-status-btn" data-status="active"   style="height:32px;padding:0 12px;" onclick="setStatusFilter('active')">활성</button>
                <button class="adm-edit-btn lib-status-btn" data-status="inactive" style="height:32px;padding:0 12px;" onclick="setStatusFilter('inactive')">비활성</button>
            </div>
            <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="openAddModal()">
                <i class="bi bi-plus-lg"></i> 추가
            </button>
        </div>
    </div>

    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin:0 0 12px;">
        <select id="admKrSelect" class="lib-admin-filter-select" onchange="setGroupFilter('kr', this.value)">
            <option value="" disabled selected hidden>우리살</option>
            <option value="kr">우리살</option>
            <?php foreach ($libPatternCats as $c): if (in_array($c['code'] ?? '', ['PYM', 'ETC'], true)) continue; ?>
            <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select id="admNewSelect" class="lib-admin-filter-select" onchange="setGroupFilter('new', this.value)">
            <option value="" disabled selected hidden>새살</option>
            <option value="new">새살</option>
        </select>
        <select id="admJpSelect" class="lib-admin-filter-select" onchange="setGroupFilter('jp', this.value)">
            <option value="" disabled selected hidden>일본살</option>
            <option value="jp">일본살</option>
            <option value="jp-shoji">쇼지</option>
            <option value="jp-kumiko">쿠미꼬</option>
        </select>
    </div>

    <p style="font-size:12px;color:var(--text);margin:-8px 0 16px;">정렬 순서 열의 그립 아이콘을 드래그해 순서를 변경할 수 있습니다.</p>

    <div class="adm-table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width:32px;"></th>
                    <th style="width:168px;"></th>
                    <th style="width:90px;">코드</th>
                    <th>메모</th>
                    <th style="width:120px;">분류</th>
                    <th>키워드</th>
                    <th style="width:80px;">도면 ID</th>
                    <th style="width:56px;">순서</th>
                    <th style="width:72px;">상태</th>
                    <th style="width:120px;"></th>
                </tr>
            </thead>
            <tbody id="libTbody"></tbody>
        </table>
    </div>
</div>

<!-- 추가/수정 모달 -->
<div class="adm-modal-overlay" id="libModalOverlay">
    <div class="adm-modal" style="max-width:520px;">
        <div class="adm-modal-head">
            <div>
                <h3 id="libModalTitle">패턴 추가</h3>
                <div id="libModalCode" style="font-family:monospace;font-size:13px;font-weight:700;color:var(--accent);margin-top:2px;">모양을 선택하면 코드가 표시됩니다</div>
            </div>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <div id="libModalAlert" style="display:none;"></div>

            <div class="adm-mfield">
                <label>도면 <small style="color: var(--text);font-weight:400;">(선택)</small></label>
                <input type="hidden" id="lpDrawingId" value="">
                <button type="button" id="lpDrawingPickerBtn" class="lib-drawing-picker-btn" onclick="openDrawingPicker()">
                    <span id="lpDrawingPickerThumb" class="lib-drawing-picker-thumb"><i class="bi bi-image"></i></span>
                    <span id="lpDrawingPickerLabel">— 연결 안함 —</span>
                    <i class="bi bi-chevron-down" style="margin-left:auto;color:var(--text);"></i>
                </button>
            </div>
            <div class="adm-mfield">
                <label>모양 <small style="color: var(--text);font-weight:400;">컬렉션 페이지 "모양" 필터용 — 연결 도면의 분류와 별개</small></label>
                <select id="lpCategory" style="width:100%;padding:8px 10px;border:1.5px solid var(--border);border-radius:8px;background:var(--bg-1);color:var(--text);font-size:14px;">
                    <option value="">— 분류 없음 —</option>
                    <?php foreach ($libPatternCats as $c): ?>
                    <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?><?= $c['code'] ? ' (' . htmlspecialchars($c['code']) . ')' : '' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="adm-mfield" id="lpModifierField">
                <label>수식어 <small style="color: var(--text);font-weight:400;">코드 세부 구분용, 선택 — 모양·수식어를 바꾸면 저장 시 코드(공유 URL)가 새로 생성됩니다</small></label>
                <select id="lpModifier" style="width:100%;padding:8px 10px;border:1.5px solid var(--border);border-radius:8px;background:var(--bg-1);color:var(--text);font-size:14px;">
                    <option value="">— 기본형(수식어 없음) —</option>
                    <?php foreach ($libPatternMods as $m): ?>
                    <option value="<?= htmlspecialchars($m['code']) ?>"><?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($m['code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="adm-mfield">
                <label>메모 <small style="color: var(--text);font-weight:400;">(선택) 관리자 참고용 — 코드가 있는 계열이면 컬렉션 화면에는 코드가 노출되고, 코드가 없으면 이 메모가 노출됩니다</small></label>
                <input type="text" id="lpName" placeholder="예: 정자살 3분합" maxlength="80">
            </div>
            <div class="adm-mfield">
                <label>대표 이미지 <small style="color: var(--text);font-weight:400;">업로드 안 하면 연결된 도면의 썸네일을 사용합니다</small></label>
                <img id="lpImgPreview" class="lib-img-preview" src="" alt="">
                <label class="lib-upload-label" for="lpImgFile">
                    <i class="bi bi-upload"></i> 이미지 선택 (최대 10MB · 1024px)
                </label>
                <input type="file" id="lpImgFile" accept="image/*" style="display:none;">
            </div>
            <div class="adm-mfield">
                <label>정렬 순서</label>
                <input type="number" id="lpOrder" value="0" min="0" max="9999">
            </div>
            <div class="adm-mfield" style="margin-bottom:0;">
                <label>키워드</label>
                <div class="kw-list" id="kwList"></div>
                <div class="kw-input-row">
                    <input type="text" id="kwInput" placeholder="키워드 입력 후 Enter" maxlength="60">
                    <button class="adm-edit-btn" style="height:36px;padding:0 12px;" onclick="addKeyword()">추가</button>
                </div>
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" id="lpSaveBtn" onclick="savePattern()">저장</button>
        </div>
    </div>
</div>

<!-- 도면 선택 피커 -->
<div class="adm-modal-overlay" id="drawingPickerOverlay">
    <div class="adm-modal" style="max-width:640px;max-height:80vh;">
        <div class="adm-modal-head">
            <h3>도면 선택</h3>
            <button class="adm-modal-close" onclick="closeDrawingPicker()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <input type="text" id="drawingPickerSearch" placeholder="도면명 검색" style="width:100%;padding:8px 10px;border:1.5px solid var(--border);border-radius:8px;background:var(--bg-1);color:var(--text);font-size:14px;margin-bottom:14px;">
            <div class="lib-drawing-picker-none" onclick="selectDrawing(null)">
                <i class="bi bi-slash-circle"></i> — 연결 안함 —
            </div>
            <div class="lib-drawing-picker-grid" id="drawingPickerGrid"></div>
        </div>
    </div>
</div>

<script>const PYM_CATEGORY_ID = <?= (int)$pymCategoryId ?>;</script>
<script src="/src/js/admin/collection.js"></script>
</body>
</html>
