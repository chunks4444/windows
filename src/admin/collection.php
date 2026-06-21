<?php header('Content-Type: text/html; charset=UTF-8'); ?>
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
    
    <?php css_tag('/src/css/admin/collection.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="libAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="libPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>라이브러리 패턴</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-image me-2"></i>라이브러리 패턴</h1>
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

    <div class="adm-table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width:64px;"></th>
                    <th>이름</th>
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
            <h3 id="libModalTitle">패턴 추가</h3>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <div id="libModalAlert" style="display:none;"></div>

            <div class="adm-mfield">
                <label>이름</label>
                <input type="text" id="lpName" placeholder="예: 정자살" maxlength="80">
            </div>
            <div class="adm-mfield">
                <label>도면 <small style="color:var(--text-3);font-weight:400;">(선택)</small></label>
                <select id="lpDrawingId" style="width:100%;padding:8px 10px;border:1.5px solid var(--border);border-radius:8px;background:var(--bg-1);color:var(--text-1);font-size:14px;">
                    <option value="">— 연결 안함 —</option>
                </select>
            </div>
            <div class="adm-mfield">
                <label>대표 이미지</label>
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

<script src="/src/js/admin/collection.js"></script>
</body>
</html>
