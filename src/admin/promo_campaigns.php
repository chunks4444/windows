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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jodit/3.24.9/jodit.min.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../lib/meta.php'; ?>
    <?php meta_tags(); ?>
<?php css_tag('/src/css/dashboard.css'); ?>
    <?php css_tag('/src/css/users.css'); ?>
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>

    <?php css_tag('/src/css/admin.css'); ?>
    <style>
        .pm-chk { display:inline-flex; align-items:center; gap:6px; margin-right:14px; font-size:13px; }
        .pm-status { display:inline-block; padding:2px 8px; border-radius:999px; font-size:11px; font-weight:700; background:rgba(var(--text-rgb),.08); }
        .pm-status.sending { background:var(--accent); color:var(--bg); }
        .pm-status.scheduled { background:#fff3cd; color:#8a6d00; }
        .pm-status.done { background:#d9f2e3; color:#1c6b3a; }
        .pm-status.canceled { background:#f2d9d9; color:#a02020; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>
<?php include __DIR__ . '/../components/admin_sidenav.php'; ?>

<div class="db-page" id="pmPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>홍보메일 발송</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-envelope-paper me-2"></i>홍보메일 발송</h1>
        <button class="adm-btn-save" onclick="openEditor()"><i class="bi bi-plus-lg"></i> 새 홍보메일</button>
    </div>
    <p style="font-size:12px;color:var(--text-muted);margin:0 0 12px;">
        메일은 <strong>2분 간격</strong>으로 한 통씩 보내며, 최근 24시간 발송이 <span id="pmLimitInfo">-</span>통에 닿으면 자동으로 멈췄다가 이어서 보냅니다.
        제목에 (광고)가 자동으로 붙고, 수신거부 링크가 본문 맨 아래에 자동으로 들어갑니다.
    </p>

    <div class="adm-table-wrap">
        <table>
            <thead>
                <tr><th>이름</th><th>제목</th><th>대상</th><th>상태</th><th>발송 시각</th><th>진행</th><th></th></tr>
            </thead>
            <tbody id="pmBody"></tbody>
        </table>
    </div>
</div>

<!-- 편집 모달 -->
<div class="adm-modal-overlay" id="pmEditorOverlay">
    <div class="adm-modal" style="max-width:1100px;width:96vw;">
        <div class="adm-modal-head">
            <h3 id="pmEditorTitle">새 홍보메일</h3>
            <button class="adm-modal-close" onclick="closeEditor()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <input type="hidden" id="pmId">
            <div class="adm-mfield"><label>관리용 이름 (수신자에게는 안 보임)</label><input type="text" id="pmTitle" maxlength="150" placeholder="2026-11 인테리어 소개"></div>
            <div class="adm-mfield"><label>메일 제목 — 발송 시 앞에 (광고)가 자동으로 붙고, {{회사명}}은 받는 회사 이름으로 바뀝니다</label><input type="text" id="pmSubject" maxlength="200" placeholder="[제작 협력] {{회사명}} — 창호·파티션 제작 파트너 제안"></div>
            <div class="adm-mfield"><label>발송 대상 업종 (선택 없으면 전체)</label><div id="pmIndustries"></div></div>
            <div class="adm-mfield">
                <label>홍보문구 — <code>{{회사명}}</code>이 받는 회사 이름으로 바뀝니다. 수신거부 링크는 맨 아래 자동 삽입 (<code>{{수신거부링크}}</code>로 위치 지정도 가능)</label>
            <div style="padding:10px 12px;margin:6px 0 10px;background:rgba(var(--text-rgb),.04);border-radius:var(--r-sm);">
                <label>문구 템플릿 — 제목·본문·첨부 PDF를 저장해 두고 불러와서 계속 고쳐 쓸 수 있습니다</label>
                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
                    <select id="pmTplSelect" onchange="onTplSelect()" style="height:34px;min-width:220px;border:1px solid var(--border);border-radius:var(--r-sm);background:var(--bg);color:var(--text);padding:0 8px;font-size:13px;"></select>
                    <button type="button" class="adm-btn-cancel" id="pmTplLoad" onclick="loadTemplate()">불러오기</button>
                    <button type="button" class="adm-btn-cancel" id="pmTplUpdate" onclick="saveTemplate(false)">선택한 템플릿에 덮어쓰기</button>
                    <button type="button" class="adm-btn-cancel" onclick="saveTemplate(true)">새 템플릿으로 저장</button>
                    <button type="button" class="adm-withdraw-btn" id="pmTplDelete" onclick="deleteTemplate()">템플릿 삭제</button>
                </div>
            </div>
                <textarea id="pmHtml"></textarea>
            </div>
            <div class="adm-mfield">
                <label>포트폴리오 PDF 첨부 — 8MB 이하는 메일에 첨부되고, 그보다 크면 첨부 없이 본문의 PDF 다운로드 링크로만 전달됩니다 (PDF, 30MB 이하)</label>
                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;">
                    <input type="file" id="pmPdfFile" accept="application/pdf,.pdf" style="display:none;" onchange="uploadPdf(this)">
                    <button type="button" class="adm-btn-cancel" onclick="document.getElementById('pmPdfFile').click()"><i class="bi bi-paperclip"></i> PDF 선택</button>
                    <span id="pmPdfInfo" style="font-size:13px;"></span>
                    <button type="button" class="adm-withdraw-btn" id="pmPdfRemove" style="display:none;" onclick="removePdf()">제거</button>
                </div>
            </div>
        </div>
        <div class="adm-modal-foot" style="flex-wrap:wrap;gap:8px;">
            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-right:auto;">
                <input type="email" id="pmTestTo" placeholder="테스트 받을 이메일" style="height:34px;width:220px;border:1px solid var(--border);border-radius:var(--r-sm);padding:0 10px;font-size:13px;">
                <select id="pmTestCo" title="이 회사 이름으로 치환해서 테스트" style="height:34px;max-width:200px;border:1px solid var(--border);border-radius:var(--r-sm);background:var(--bg);color:var(--text);padding:0 8px;font-size:13px;"><option value="">회사명 샘플</option></select>
                <button class="adm-btn-cancel" onclick="sendTest()">테스트 발송</button>
                <span id="pmEditorMsg" style="font-size:12px;"></span>
            </div>
            <button class="adm-btn-cancel" onclick="closeEditor()">닫기</button>
            <button class="adm-btn-cancel" onclick="saveDraft()">임시저장</button>
            <input type="datetime-local" id="pmWhen" style="height:34px;border:1px solid var(--border);border-radius:var(--r-sm);padding:0 8px;font-size:13px;">
            <button class="adm-btn-cancel" onclick="startSend('schedule')">예약 발송</button>
            <button class="adm-btn-save" onclick="startSend('send_now')">즉시 발송</button>
        </div>
    </div>
</div>

<!-- 발송 이력 모달 -->
<div class="adm-modal-overlay" id="pmLogOverlay">
    <div class="adm-modal" style="max-width:820px;width:96vw;">
        <div class="adm-modal-head">
            <h3 id="pmLogTitle">발송 이력</h3>
            <button class="adm-modal-close" onclick="closeLog()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <div id="pmLogSummary" style="font-size:13px;margin-bottom:10px;"></div>
            <div class="adm-table-wrap" style="max-height:420px;overflow-y:auto;">
                <table>
                    <thead><tr><th>회사</th><th>이메일</th><th>결과</th><th>시각</th><th>비고</th></tr></thead>
                    <tbody id="pmLogBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jodit/3.24.9/jodit.min.js"></script>
<script src="/src/js/admin/promo_campaigns.js?v=<?= md5_file(__DIR__ . '/../js/admin/promo_campaigns.js') ?>"></script>
</body>
</html>
