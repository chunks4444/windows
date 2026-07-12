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
    <?php meta_tags(); ?>
    <?php if ($kakaoJsKey = kakao_js_key()): ?>
    <script src="https://t1.kakaocdn.net/kakao_js_sdk/2.7.4/kakao.min.js"></script>
    <script>if (window.Kakao && !Kakao.isInitialized()) Kakao.init('<?= addslashes($kakaoJsKey) ?>');</script>
    <?php endif; ?>
<?php css_tag('/src/css/dashboard.css'); ?>
</head>
<body>

<?php include __DIR__ . '/../components/nav.php'; ?>

<!-- 페이지 히어로 -->
<div class="lib-hero">
    <div class="lib-hero-inner">
        <p class="lib-hero-label">My Page</p>
        <h1>도면관리</h1>
        <p class="lib-hero-sub">
            저장한 도면과 보드를 관리하세요.&ensp;
            <span class="lib-count-badge" id="libCountBadge"></span>
        </p>
    </div>
</div>

<div class="db-page" id="dbPage" style="display:none;">
    <div class="db-header">
        <div class="db-tabs">
            <button class="db-tab active" id="tabDrawings" onclick="switchTab('drawings')">내 도면</button>
            <button class="db-tab" id="tabBoards" onclick="switchTab('boards')">내 보드</button>
            <button class="db-tab" id="tabRenders" onclick="switchTab('renders')">렌더링</button>
            <button class="db-tab" id="tabOrders" onclick="switchTab('orders')">주문내역</button>
        </div>
    </div>
    <div id="dbContent"></div>
    <div id="dbBoardsContent" style="display:none;"></div>
    <div id="dbRendersContent" style="display:none;"></div>
    <div id="dbOrdersContent" style="display:none;"></div>
</div>

<!-- 주문 상세 모달 -->
<div id="dbOrderModal" style="display:none;position:fixed;inset:0;background:rgba(var(--text-rgb), 0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:var(--bg);border-radius:16px;width:min(90vw,560px);max-height:85vh;display:flex;flex-direction:column;overflow:hidden;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border);">
            <h3 id="dbOrderModalTitle" style="margin:0;font-size:16px;font-weight:700;"></h3>
            <button onclick="document.getElementById('dbOrderModal').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--text-muted);">&times;</button>
        </div>
        <div id="dbOrderModalBody" style="overflow-y:auto;padding:20px;"></div>
    </div>
</div>

<!-- 렌더링 상세 모달 -->
<div id="dbRenderModal" style="display:none;position:fixed;inset:0;background:rgba(var(--text-rgb), 0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:var(--bg);border-radius:16px;width:min(90vw,720px);max-height:85vh;display:flex;flex-direction:column;overflow:hidden;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid var(--bg);">
            <h3 id="dbRenderModalTitle" style="margin:0;font-size:15px;font-weight:700;color:var(--text-muted,var(--text-muted));"></h3>
            <div style="display:flex;gap:8px;align-items:center;">
                <button id="dbRenderModalShare" title="공유" style="border:none;background:var(--bg);color:var(--text);border-radius:6px;padding:6px 12px;font-size:14px;display:flex;align-items:center;cursor:pointer;"><i class="bi bi-share-fill"></i></button>
                <button id="dbRenderModalDownload" style="border:none;background:var(--accent,var(--accent));color:var(--bg);border-radius:6px;padding:6px 14px;font-size:13px;font-weight:600;cursor:pointer;">다운로드</button>
                <button id="dbRenderModalDelete" style="border:none;background:var(--bg);color:var(--danger);border-radius:6px;padding:6px 14px;font-size:13px;font-weight:600;cursor:pointer;">삭제</button>
                <button onclick="document.getElementById('dbRenderModal').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--text-muted);">&times;</button>
            </div>
        </div>
        <div style="overflow:auto;padding:16px;display:flex;align-items:center;justify-content:center;background:var(--bg);">
            <img id="dbRenderModalImg" src="" style="max-width:100%;max-height:70vh;display:block;border-radius:8px;">
        </div>
    </div>
</div>

<!-- 보드 상세 모달 -->
<div id="dbBoardModal" style="display:none;position:fixed;inset:0;background:rgba(var(--text-rgb), 0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:var(--bg);border-radius:16px;width:min(90vw,720px);max-height:80vh;display:flex;flex-direction:column;overflow:hidden;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid var(--bg);">
            <h3 id="dbBoardModalTitle" style="margin:0;font-size:18px;font-weight:700;"></h3>
            <button onclick="document.getElementById('dbBoardModal').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--text-muted);">&times;</button>
        </div>
        <div id="dbBoardModalBody" style="overflow-y:auto;padding:20px;display:flex;flex-wrap:wrap;gap:16px;"></div>
    </div>
</div>

<!-- 도면 복사 모달 -->
<div id="dbCopyModal" class="db-delete-modal" style="display:none;" role="dialog" aria-modal="true">
    <div class="db-delete-modal-box">
        <button type="button" class="db-delete-modal-close" id="dbCopyModalClose" aria-label="닫기">
            <i class="bi bi-x-lg"></i>
        </button>
        <div class="db-delete-modal-icon" style="color:var(--accent);">
            <i class="bi bi-copy"></i>
        </div>
        <div class="db-delete-modal-title" id="dbCopyModalTitle">도면 복사</div>
        <div class="db-delete-modal-desc" id="dbCopyModalDesc"></div>
        <input type="text" id="dbCopyModalInput" class="db-copy-input" maxlength="40" placeholder="복사본 이름 입력…">
        <div class="db-delete-modal-actions">
            <button class="db-delete-modal-cancel" id="dbCopyModalCancel">취소</button>
            <button class="db-delete-modal-confirm" id="dbCopyModalConfirm" style="background:var(--accent);">복사</button>
        </div>
    </div>
</div>

<!-- 공유 모달 -->
<div id="dbShareModal" class="db-delete-modal" style="display:none;" role="dialog" aria-modal="true">
    <div class="db-delete-modal-box">
        <button type="button" class="db-delete-modal-close" id="dbShareModalClose" aria-label="닫기">
            <i class="bi bi-x-lg"></i>
        </button>
        <div class="db-delete-modal-icon" style="background:var(--accent-tint,#eee);color:var(--accent);">
            <i class="bi bi-share-fill"></i>
        </div>
        <div class="db-delete-modal-title" id="dbShareModalTitle">도면 공유</div>
        <div class="db-delete-modal-desc" id="dbShareModalId">도면 #—</div>
        <div class="db-share-linkrow">
            <input type="text" id="dbShareModalLink" readonly>
            <button type="button" id="dbShareModalCopy">복사</button>
        </div>
        <div class="db-share-channels">
            <button type="button" id="dbShareModalKakao" title="카카오톡 공유">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3C6.5 3 2 6.6 2 11c0 2.8 1.8 5.3 4.6 6.7-.2.7-.7 2.6-.8 3-.1.5.2.5.4.4.2-.1 2.6-1.8 3.6-2.5.7.1 1.4.2 2.2.2 5.5 0 10-3.6 10-8 0-4.4-4.5-7.8-10-7.8z"/></svg>
                <span>카카오</span>
            </button>
            <button type="button" id="dbShareModalFb" title="페이스북 공유">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 21v-7.9h2.7l.4-3.1h-3.1V8.1c0-.9.3-1.5 1.6-1.5h1.7V3.8C15.9 3.7 14.8 3.6 13.6 3.6c-2.5 0-4.2 1.5-4.2 4.3v2.1H6.7v3.1h2.7V21h4.1z"/></svg>
                <span>FB</span>
            </button>
            <button type="button" id="dbShareModalX" title="X(트위터) 공유">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M18.9 3H22l-7.5 8.6L23 21h-6.9l-5.4-6.6L4.4 21H1.3l8-9.2L1 3h7l4.9 6.1L18.9 3zm-1.2 16h1.9L7.4 4.9H5.4L17.7 19z"/></svg>
                <span>X</span>
            </button>
        </div>
        <div class="db-delete-modal-actions">
            <button class="db-delete-modal-cancel" id="dbShareModalOff">공유 끄기</button>
            <button class="db-delete-modal-confirm" id="dbShareModalDone" style="background:var(--accent);">닫기</button>
        </div>
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

<script src="/src/js/order-status-labels.js?v=<?= md5_file(__DIR__ . '/../../js/order-status-labels.js') ?>"></script>
<script src="/src/js/dashboard.js?v=<?= md5_file(__DIR__ . '/../../js/dashboard.js') ?>"></script>
<script>
if (location.hash === '#boards' || location.hash === '#orders') {
    const tab = location.hash.slice(1);
    const observer = new MutationObserver(() => {
        const page = document.getElementById('dbPage');
        if (page && page.style.display !== 'none') {
            switchTab(tab);
            observer.disconnect();
        }
    });
    observer.observe(document.getElementById('dbPage'), { attributes: true, attributeFilter: ['style'] });
}
</script>

<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
