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
    <link rel="stylesheet" href="/src/css/dashboard.css">
</head>
<body>

<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="dbPage" style="display:none;">
    <div class="db-header">
        <div class="db-tabs">
            <button class="db-tab active" id="tabDrawings" onclick="switchTab('drawings')">내 도면</button>
            <button class="db-tab" id="tabBoards" onclick="switchTab('boards')">내 보드</button>
        </div>
    </div>
    <div id="dbContent"></div>
    <div id="dbBoardsContent" style="display:none;"></div>
</div>

<!-- 보드 상세 모달 -->
<div id="dbBoardModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;width:min(90vw,720px);max-height:80vh;display:flex;flex-direction:column;overflow:hidden;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid #eee;">
            <h3 id="dbBoardModalTitle" style="margin:0;font-size:18px;font-weight:700;"></h3>
            <button onclick="document.getElementById('dbBoardModal').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:#999;">&times;</button>
        </div>
        <div id="dbBoardModalBody" style="overflow-y:auto;padding:20px;display:flex;flex-wrap:wrap;gap:16px;"></div>
    </div>
</div>

<!-- 삭제 확인 모달 -->
<div id="dbDeleteModal" class="db-delete-modal" style="display:none;" role="dialog" aria-modal="true">
    <div class="db-delete-modal-box">
        <div class="db-delete-modal-icon">
            <i class="bi bi-trash3"></i>
        </div>
        <div class="db-delete-modal-title" id="dbDeleteModalTitle">삭제하시겠습니까?</div>
        <div class="db-delete-modal-desc" id="dbDeleteModalDesc"></div>
        <div class="db-delete-modal-actions">
            <button class="db-delete-modal-cancel" id="dbDeleteModalCancel">취소</button>
            <button class="db-delete-modal-confirm" id="dbDeleteModalConfirm">삭제</button>
        </div>
    </div>
</div>

<!-- 비로그인 -->
<div class="db-page" id="dbAuthWall" style="display:none;">
    <div class="db-auth-banner">
        <p>도면을 저장하고 관리하려면 로그인이 필요합니다.</p>
        <button class="db-auth-btn" data-bs-toggle="modal" data-bs-target="#authModal">
            <i class="bi bi-person-circle"></i> 로그인
        </button>
    </div>
</div>

<script src="/src/js/dashboard.js"></script>

</body>
</html>
