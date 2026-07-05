<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/admin_guard.php';
require_admin_role('s');
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
    <style>
        .drag-handle { cursor:grab; color:var(--text-muted); }
        .drag-handle:active { cursor:grabbing; }
        tr.dragging { opacity:.4; }
        tr.drag-over td { background:var(--accent-tint); }
        .motif-thumb { width:56px; height:56px; object-fit:contain; border-radius:4px; padding:6px;
            background-image:linear-gradient(45deg,var(--border) 25%,transparent 25%),linear-gradient(-45deg,var(--border) 25%,transparent 25%),linear-gradient(45deg,transparent 75%,var(--border) 75%),linear-gradient(-45deg,transparent 75%,var(--border) 75%);
            background-size:8px 8px; background-position:0 0,0 4px,4px -4px,-4px 0; background-color:var(--bg); }
        .motif-thumb-empty { width:56px; height:56px; border-radius:4px; background:var(--bg); display:flex; align-items:center; justify-content:center; color:var(--text-muted); font-size:16px; }
        .motif-preview { width:100%; max-height:160px; object-fit:contain; border-radius:8px; display:none; margin-bottom:8px; padding:12px;
            background-image:linear-gradient(45deg,var(--border) 25%,transparent 25%),linear-gradient(-45deg,var(--border) 25%,transparent 25%),linear-gradient(45deg,transparent 75%,var(--border) 75%),linear-gradient(-45deg,transparent 75%,var(--border) 75%);
            background-size:10px 10px; background-position:0 0,0 5px,5px -5px,-5px 0; background-color:var(--bg); }
        .motif-preview.show { display:block; }
        .motif-upload-label { display:block; padding:10px; border:1.5px dashed var(--border); border-radius:8px; text-align:center; cursor:pointer; color:var(--text-muted); font-size:13px; margin-bottom:6px; }
        .motif-upload-label:hover { border-color:var(--accent); color:var(--accent); }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="svgMotifsAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="svgMotifsPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>문양(SVG) 라이브러리</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-flower1 me-2"></i>문양(SVG) 라이브러리</h1>
        <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="openModal()">
            <i class="bi bi-plus-lg"></i> 추가
        </button>
    </div>

    <p style="font-size:12px;color:var(--text-muted);margin:-8px 0 16px;">행을 드래그해 순서를 변경할 수 있습니다. 엔진의 "문양 삽입" 라이브러리 피커에 여기 등록된 문양이 노출됩니다.</p>

    <div class="adm-table-wrap">
        <table id="svgMotifsTable">
            <thead>
                <tr>
                    <th style="width:32px;"></th>
                    <th style="width:80px;">미리보기</th>
                    <th>이름</th>
                    <th>카테고리</th>
                    <th style="width:72px;">상태</th>
                    <th style="width:160px;"></th>
                </tr>
            </thead>
            <tbody id="svgMotifsBody"></tbody>
        </table>
    </div>
</div>

<!-- 편집 모달 -->
<div class="adm-modal-overlay" id="svgMotifModalOverlay">
    <div class="adm-modal" style="max-width:440px;">
        <div class="adm-modal-head">
            <h3 id="svgMotifModalTitle">문양 추가</h3>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <input type="hidden" id="motifId">
            <div class="adm-mfield">
                <label>SVG 파일</label>
                <img id="motifPreview" class="motif-preview" src="" alt="">
                <label class="motif-upload-label" for="motifFile">
                    <i class="bi bi-upload"></i> SVG 업로드
                </label>
                <input type="file" id="motifFile" accept="image/svg+xml,.svg" style="display:none;" onchange="previewMotif(this)">
            </div>
            <div class="adm-mfield">
                <label>이름</label>
                <input id="motifName" type="text" placeholder="예: 매화 문양" maxlength="100">
            </div>
            <div class="adm-mfield">
                <label>카테고리 <span style="font-size:11px;color:var(--text-muted);font-weight:400;">(선택, 분류용)</span></label>
                <input id="motifCategory" type="text" placeholder="예: 꽃살" maxlength="50">
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" onclick="saveMotif()">저장</button>
        </div>
    </div>
</div>

<script src="/src/js/admin/svg_motifs.js"></script>
</body>
</html>
