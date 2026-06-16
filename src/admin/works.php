<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/src/css/dashboard.css">
    <link rel="stylesheet" href="/src/css/users.css">
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>
    <style>
        .drag-handle { cursor:grab; color:var(--text-3); }
        .drag-handle:active { cursor:grabbing; }
        tr.dragging { opacity:.4; }
        tr.drag-over td { background:var(--accent-bg); }
        .work-thumb { width:80px; height:60px; object-fit:cover; border-radius:4px; background:var(--input-bg); }
        .work-thumb-empty { width:80px; height:60px; border-radius:4px; background:var(--input-bg); display:flex; align-items:center; justify-content:center; color:var(--text-3); font-size:18px; }
        .work-img-preview { width:100%; max-height:220px; object-fit:cover; border-radius:8px; background:var(--input-bg); display:none; margin-bottom:8px; }
        .work-img-preview.show { display:block; }
        .work-upload-label { display:block; padding:10px; border:1.5px dashed var(--border-md); border-radius:8px; text-align:center; cursor:pointer; color:var(--text-3); font-size:13px; margin-bottom:6px; }
        .work-upload-label:hover { border-color:var(--teal); color:var(--teal); }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="worksAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="worksPage" style="display:none;">
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-images me-2"></i>Works 관리</h1>
        <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="openModal()">
            <i class="bi bi-plus-lg"></i> 추가
        </button>
    </div>
    <p style="font-size:12px;color:var(--text-3);margin:-8px 0 16px;">행을 드래그해 순서를 변경할 수 있습니다.</p>

    <div class="adm-table-wrap">
        <table id="worksTable">
            <thead>
                <tr>
                    <th style="width:32px;"></th>
                    <th style="width:96px;">이미지</th>
                    <th>제목</th>
                    <th>설명</th>
                    <th style="width:72px;">상태</th>
                    <th style="width:160px;"></th>
                </tr>
            </thead>
            <tbody id="worksBody"></tbody>
        </table>
    </div>
</div>

<!-- 편집 모달 -->
<div class="adm-modal-overlay" id="worksModalOverlay">
    <div class="adm-modal" style="max-width:520px;">
        <div class="adm-modal-head">
            <h3 id="worksModalTitle">작품 추가</h3>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <input type="hidden" id="workId">
            <div class="adm-mfield">
                <label>이미지</label>
                <img id="workImgPreview" class="work-img-preview" src="" alt="">
                <label class="work-upload-label" for="workImgFile">
                    <i class="bi bi-upload"></i> 이미지 업로드
                </label>
                <input type="file" id="workImgFile" accept="image/*" style="display:none;" onchange="previewImage(this)">
                <input id="workImageUrl" type="text" placeholder="또는 https://... URL 직접 입력"
                    style="height:38px;padding:0 10px;border:1px solid var(--border-md);border-radius:var(--r-sm);background:var(--input-bg);font-family:inherit;font-size:13px;color:var(--text-1);outline:none;width:100%;">
            </div>
            <div class="adm-mfield">
                <label>제목</label>
                <input id="workTitle" type="text" placeholder="예: 한옥 중문 정자살" maxlength="100">
            </div>
            <div class="adm-mfield">
                <label>설명</label>
                <input id="workDescription" type="text" placeholder="예: 경기도 양평 한옥 중문" maxlength="300">
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" onclick="saveWork()">저장</button>
        </div>
    </div>
</div>

<script src="/src/js/admin/works.js"></script>
</body>
</html>
