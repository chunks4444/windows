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
        .work-thumb { width:80px; height:60px; object-fit:cover; border-radius:4px; background:var(--bg); }
        .work-thumb-empty { width:80px; height:60px; border-radius:4px; background:var(--bg); display:flex; align-items:center; justify-content:center; color:var(--text-muted); font-size:18px; }
        .work-img-preview { width:100%; max-height:220px; object-fit:cover; border-radius:8px; background:var(--bg); display:none; margin-bottom:8px; }
        .work-img-preview.show { display:block; }
        .work-upload-label { display:block; padding:10px; border:1.5px dashed var(--border); border-radius:8px; text-align:center; cursor:pointer; color:var(--text-muted); font-size:13px; margin-bottom:6px; }
        .work-upload-label:hover { border-color:var(--accent); color:var(--accent); }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="worksAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="worksPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>Works 관리</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-images me-2"></i>Works 관리</h1>
        <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="openModal()">
            <i class="bi bi-plus-lg"></i> 추가
        </button>
    </div>

    <!-- 필터 태그 관리 -->
    <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:10px;padding:18px 20px;margin-bottom:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <div style="font-size:13px;font-weight:600;color:var(--text);">필터 태그 관리</div>
            <button class="adm-edit-btn" style="height:28px;padding:0 12px;font-size:12px;" onclick="openTagModal()">
                <i class="bi bi-plus-lg"></i> 태그 추가
            </button>
        </div>
        <div id="tagList" style="display:flex;flex-wrap:wrap;gap:8px;"></div>
    </div>

    <!-- 상세 페이지 패널 색상 설정 -->
    <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:10px;padding:18px 20px;margin-bottom:24px;">
        <div style="font-size:13px;font-weight:600;margin-bottom:14px;color:var(--text);">상세 페이지 패널 색상</div>
        <div style="display:flex;gap:24px;align-items:flex-end;flex-wrap:wrap;">
            <label style="font-size:12px;color:var(--text-muted);display:flex;flex-direction:column;gap:6px;">
                배경색
                <input type="color" id="settingPanelBg" style="width:52px;height:32px;border:none;padding:0;cursor:pointer;border-radius:4px;">
            </label>
            <label style="font-size:12px;color:var(--text-muted);display:flex;flex-direction:column;gap:6px;">
                제목 색상
                <input type="color" id="settingTitleColor" style="width:52px;height:32px;border:none;padding:0;cursor:pointer;border-radius:4px;">
            </label>
            <label style="font-size:12px;color:var(--text-muted);display:flex;flex-direction:column;gap:6px;">
                설명 색상
                <input type="color" id="settingDescColor" style="width:52px;height:32px;border:none;padding:0;cursor:pointer;border-radius:4px;">
            </label>
            <button class="adm-edit-btn" style="height:32px;padding:0 16px;" onclick="saveColors()">저장</button>
        </div>
    </div>

    <p style="font-size:12px;color:var(--text-muted);margin:-8px 0 16px;">행을 드래그해 순서를 변경할 수 있습니다.</p>

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
                    style="height:38px;padding:0 10px;border:1px solid var(--border);border-radius:var(--r-sm);background:var(--bg);font-family:inherit;font-size:13px;color:var(--text);outline:none;width:100%;">
            </div>
            <div class="adm-mfield">
                <label>제목</label>
                <input id="workTitle" type="text" placeholder="예: 한옥 중문 정자살" maxlength="100">
            </div>
            <div class="adm-mfield">
                <label>설명 <span style="font-size:11px;color:var(--text-muted);font-weight:400;">(카드 호버 시 표시)</span></label>
                <textarea id="workDesc" rows="2" maxlength="300" placeholder="예: 경기도 양평" style="resize:vertical;padding:8px 10px;border:1px solid var(--border);border-radius:var(--r-sm);background:var(--bg);font-family:inherit;font-size:13px;color:var(--text);outline:none;width:100%;"></textarea>
            </div>
            <div class="adm-mfield">
                <label>패널 색상</label>
                <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;">
                    <label style="font-size:11px;color:var(--text-muted);display:flex;align-items:center;gap:6px;">
                        배경
                        <input type="color" id="workPanelBg" value="#111111" style="width:40px;height:28px;border:none;padding:0;cursor:pointer;border-radius:4px;">
                    </label>
                    <label style="font-size:11px;color:var(--text-muted);display:flex;align-items:center;gap:6px;">
                        제목
                        <input type="color" id="workTitleColor" value="#ffffff" style="width:40px;height:28px;border:none;padding:0;cursor:pointer;border-radius:4px;">
                    </label>
                    <label style="font-size:11px;color:var(--text-muted);display:flex;align-items:center;gap:6px;">
                        설명
                        <input type="color" id="workDescColor" value="#888888" style="width:40px;height:28px;border:none;padding:0;cursor:pointer;border-radius:4px;">
                    </label>
                </div>
            </div>
        </div>
        <!-- 다중 이미지 (기존 작품 편집 시만 표시) -->
        <div id="multiImgSection" style="display:none;border-top:1px solid var(--border);padding:16px 24px 16px;margin:0;overflow:hidden;">
            <div style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:10px;">슬라이드 이미지</div>
            <div id="multiImgList" style="display:flex;flex-direction:column;gap:8px;margin-bottom:10px;overflow:hidden;"></div>
            <div style="display:flex;gap:8px;align-items:center;min-width:0;">
                <input type="file" id="multiImgFile" accept="image/*" style="display:none;" onchange="addImageFromFile(this)">
                <button class="adm-btn-cancel" style="height:32px;padding:0 10px;font-size:12px;flex-shrink:0;" onclick="document.getElementById('multiImgFile').click()">
                    <i class="bi bi-upload"></i> 업로드
                </button>
                <input id="multiImgUrl" type="text" placeholder="https://... URL" style="flex:1;min-width:0;height:32px;padding:0 10px;border:1px solid var(--border);border-radius:var(--r-sm);background:var(--bg);font-size:12px;color:var(--text);">
                <button class="adm-edit-btn" style="height:32px;padding:0 12px;font-size:12px;flex-shrink:0;" onclick="addImageFromUrl()">추가</button>
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" onclick="saveWork()">저장</button>
        </div>
    </div>
</div>

<script src="/src/js/admin/works.js"></script>

<!-- 태그 편집 모달 -->
<div class="adm-modal-overlay" id="tagModalOverlay">
    <div class="adm-modal" style="max-width:360px;">
        <div class="adm-modal-head">
            <h3 id="tagModalTitle">태그 추가</h3>
            <button class="adm-modal-close" onclick="closeTagModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <input type="hidden" id="tagId">
            <div class="adm-mfield">
                <label>태그 이름</label>
                <input id="tagName" type="text" placeholder="예: 중문" maxlength="50">
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeTagModal()">취소</button>
            <button class="adm-btn-save" onclick="saveTag()">저장</button>
        </div>
    </div>
</div>

<script>
const TAG_API = '/src/api/admin/work_tags.php';
let tags = [];

function _h() { return { 'Authorization': 'Bearer ' + localStorage.getItem('pmok_auth_token'), 'Content-Type': 'application/json' }; }

async function loadTags() {
    const res  = await fetch(TAG_API, { headers: _h() });
    const data = await res.json();
    tags = data.tags || [];
    renderTags();
}

function renderTags() {
    document.getElementById('tagList').innerHTML = tags.map(t => `
        <div style="display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:999px;
             border:1.5px solid ${t.is_active ? 'var(--accent)' : 'var(--border)'};
             background:${t.is_active ? 'var(--accent-tint)' : 'var(--bg)'};font-size:12px;">
            <span style="font-weight:600;color:${t.is_active ? 'var(--accent)' : 'var(--text-muted)'};">${esc(t.name)}</span>
            <button onclick="openTagModal(${t.id})" style="border:none;background:none;cursor:pointer;color:var(--text-muted);padding:0;font-size:13px;line-height:1;" title="수정">✎</button>
            <button onclick="toggleTag(${t.id})" style="border:none;background:none;cursor:pointer;color:var(--text-muted);padding:0;font-size:12px;line-height:1;" title="${t.is_active ? '숨김' : '표시'}">${t.is_active ? '●' : '○'}</button>
            <button onclick="deleteTag(${t.id})" style="border:none;background:none;cursor:pointer;color:var(--danger);padding:0;font-size:13px;line-height:1;" title="삭제">✕</button>
        </div>`).join('');
}

function esc(s) { return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function openTagModal(id) {
    const t = id ? tags.find(x => x.id == id) : null;
    document.getElementById('tagModalTitle').textContent = t ? '태그 수정' : '태그 추가';
    document.getElementById('tagId').value   = t?.id ?? '';
    document.getElementById('tagName').value = t?.name ?? '';
    document.getElementById('tagModalOverlay').classList.add('open');
    setTimeout(() => document.getElementById('tagName').focus(), 50);
}

function closeTagModal() {
    document.getElementById('tagModalOverlay').classList.remove('open');
}

async function saveTag() {
    const body = { action: 'save', id: parseInt(document.getElementById('tagId').value) || 0, name: document.getElementById('tagName').value.trim() };
    const res  = await fetch(TAG_API, { method: 'POST', headers: _h(), body: JSON.stringify(body) });
    const data = await res.json();
    if (data.ok) { closeTagModal(); loadTags(); }
    else alert(data.error || '저장 실패');
}

async function toggleTag(id) {
    await fetch(TAG_API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'toggle', id }) });
    loadTags();
}

async function deleteTag(id) {
    if (!confirm('태그를 삭제할까요?')) return;
    await fetch(TAG_API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'delete', id }) });
    loadTags();
}

document.getElementById('tagModalOverlay').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeTagModal();
});
document.getElementById('tagName').addEventListener('keydown', e => {
    if (e.key === 'Enter') saveTag();
});

document.addEventListener('DOMContentLoaded', () => {
    const user = JSON.parse(localStorage.getItem('pmok_auth_user') || 'null');
    if (user?.role === 's') loadTags();
});
</script>
<script>
// 패널 색상 로드
async function loadColors() {
    const res = await fetch('/src/api/admin/work_settings.php');
    if (!res.ok) return;
    const d = await res.json();
    document.getElementById('settingPanelBg').value    = d.work_panel_bg         || '#111111';
    document.getElementById('settingTitleColor').value = d.work_panel_title_color || '#ffffff';
    document.getElementById('settingDescColor').value  = d.work_panel_desc_color  || '#888888';
}
// 패널 색상 저장
async function saveColors() {
    const body = {
        work_panel_bg:          document.getElementById('settingPanelBg').value,
        work_panel_title_color: document.getElementById('settingTitleColor').value,
        work_panel_desc_color:  document.getElementById('settingDescColor').value,
    };
    const res = await fetch('/src/api/admin/work_settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    });
    if (res.ok) alert('저장되었습니다.');
}
loadColors();
</script>
<script>
/* ── 다중 이미지 관리 ── */
async function loadImages(workId) {
    const res = await fetch('/src/api/admin/works.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'get_images', work_id: workId })
    });
    const d = await res.json();
    renderImages(d.images || [], workId);
}

function renderImages(images, workId) {
    const list = document.getElementById('multiImgList');
    list.innerHTML = '';
    if (!images.length) {
        list.innerHTML = '<div style="font-size:12px;color:var(--text-muted);">등록된 이미지 없음</div>';
        return;
    }
    images.forEach(img => {
        const row = document.createElement('div');
        row.style.cssText = 'display:flex;align-items:center;gap:8px;';
        row.innerHTML = `
            <img src="${img.image_url}" style="width:56px;height:40px;object-fit:cover;border-radius:4px;flex-shrink:0;background:var(--bg);">
            <span style="flex:1;min-width:0;font-size:11px;color:var(--text-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${img.image_url}</span>
            <button onclick="deleteImage(${img.id},${workId})" style="border:none;background:none;color:var(--text-muted);cursor:pointer;font-size:16px;padding:0 4px;flex-shrink:0;">&#x2715;</button>
        `;
        list.appendChild(row);
    });
}

async function deleteImage(imgId, workId) {
    if (!confirm('삭제할까요?')) return;
    await fetch('/src/api/admin/works.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete_image', id: imgId })
    });
    loadImages(workId);
}

async function addImageFromUrl() {
    const url = document.getElementById('multiImgUrl').value.trim();
    const workId = parseInt(document.getElementById('workId').value);
    if (!url || !workId) return;
    await fetch('/src/api/admin/works.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'add_image', work_id: workId, image_url: url })
    });
    document.getElementById('multiImgUrl').value = '';
    loadImages(workId);
}

async function addImageFromFile(input) {
    const workId = parseInt(document.getElementById('workId').value);
    if (!input.files[0] || !workId) return;
    const reader = new FileReader();
    reader.onload = async e => {
        await fetch('/src/api/admin/works.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'add_image', work_id: workId, image_data: e.target.result })
        });
        input.value = '';
        loadImages(workId);
    };
    reader.readAsDataURL(input.files[0]);
}

// works.js의 openModal을 래핑해서 이미지 목록 로드
document.addEventListener('DOMContentLoaded', () => {
    const _orig = window.openModal;
    window.openModal = function(id) {
        _orig(id);
        const section = document.getElementById('multiImgSection');
        if (id) {
            section.style.display = 'block';
            loadImages(id);
        } else {
            section.style.display = 'none';
        }
    };
});
</script>
</body>
</html>
