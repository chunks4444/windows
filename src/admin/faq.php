<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../lib/meta.php'; ?>
<?php css_tag('/src/css/dashboard.css'); ?>
    <?php css_tag('/src/css/users.css'); ?>
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>
    <style>
        .drag-handle { cursor:grab; color:var(--text-3); }
        .drag-handle:active { cursor:grabbing; }
        tr.dragging { opacity:.4; }
        tr.drag-over td { background:var(--accent-bg); }
        .faq-answer-preview { font-size:12px; color:var(--text-3); max-width:400px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

        #faqEditor { height:180px; background:#fff; font-size:13px; }
        .ql-toolbar { border-color:var(--border-md) !important; border-radius:var(--r-sm) var(--r-sm) 0 0; }
        .ql-container { border-color:var(--border-md) !important; border-radius:0 0 var(--r-sm) var(--r-sm); font-family:inherit; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="faqAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="faqPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>FAQ 관리</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-question-circle me-2"></i>FAQ 관리</h1>
        <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="openModal()">
            <i class="bi bi-plus-lg"></i> 추가
        </button>
    </div>
    <p style="font-size:12px;color:var(--text-3);margin:-8px 0 16px;">행을 드래그해 순서를 변경할 수 있습니다.</p>

    <div class="adm-table-wrap">
        <table id="faqTable">
            <thead>
                <tr>
                    <th style="width:32px;"></th>
                    <th>질문</th>
                    <th>답변</th>
                    <th style="width:72px;">상태</th>
                    <th style="width:160px;"></th>
                </tr>
            </thead>
            <tbody id="faqBody"></tbody>
        </table>
    </div>
</div>

<!-- 편집 모달 -->
<div class="adm-modal-overlay" id="faqModalOverlay">
    <div class="adm-modal" style="max-width:600px;">
        <div class="adm-modal-head">
            <h3 id="faqModalTitle">FAQ 추가</h3>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <input type="hidden" id="faqId">
            <div class="adm-mfield">
                <label>질문</label>
                <input id="faqQuestion" type="text" placeholder="예: 회원가입 없이도 스튜디오를 사용할 수 있나요?" maxlength="255">
            </div>
            <div class="adm-mfield">
                <label>답변</label>
                <div id="faqEditor"></div>
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" onclick="saveFaq()">저장</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script src="/src/js/admin/faq.js"></script>
</body>
</html>
