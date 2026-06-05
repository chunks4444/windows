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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/src/css/dashboard.css">
    <style>
    .adm-table-wrap {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--r);
        overflow: hidden;
    }
    .adm-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
    }
    .adm-search {
        display: flex;
        align-items: center;
        gap: 8px;
        height: 34px;
        padding: 0 12px;
        border: 1px solid var(--border-md);
        border-radius: var(--r-sm);
        background: var(--input-bg);
        width: 260px;
    }
    .adm-search input {
        border: none; background: none; outline: none;
        font-family: inherit; font-size: 13px; color: var(--text-1);
        width: 100%;
    }
    .adm-search input::placeholder { color: var(--text-3); }
    .adm-total { font-size: 12px; color: var(--text-3); }

    table { width: 100%; border-collapse: collapse; }
    thead th {
        padding: 11px 16px;
        font-size: 11px; font-weight: 700; color: var(--text-2);
        letter-spacing: 0.04em; text-transform: uppercase;
        background: var(--input-bg);
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }
    tbody tr { border-bottom: 1px solid var(--border); }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: var(--accent-bg); }
    tbody td {
        padding: 11px 16px;
        font-size: 13px; color: var(--text-1);
        vertical-align: middle;
    }
    .adm-id { color: var(--text-3); font-size: 12px; }
    .adm-email { font-weight: 500; }
    .adm-null { color: var(--text-3); font-size: 12px; }

    .role-badge {
        display: inline-block; padding: 2px 8px;
        border-radius: 99px; font-size: 11px; font-weight: 700;
        background: var(--border); color: var(--text-2);
    }
    .role-badge[data-role="s"] { background: #FEF3C7; color: #B45309; }
    .role-badge[data-role="m"] { background: #EDE9FE; color: #6D28D9; }
    .role-badge[data-role="a"] { background: #DCFCE7; color: #15803D; }
    .role-badge[data-role="u"] { background: var(--border); color: var(--text-2); }

    .adm-edit-btn {
        height: 28px; padding: 0 12px;
        background: #fff; border: 1px solid var(--border-md);
        border-radius: var(--r-sm);
        font-family: inherit; font-size: 12px; font-weight: 600;
        color: var(--text-2); cursor: pointer;
        transition: border-color 0.15s, color 0.15s;
    }
    .adm-edit-btn:hover { border-color: var(--teal); color: var(--teal); }

    .adm-pagination {
        display: flex; align-items: center; justify-content: center;
        gap: 4px; padding: 16px;
    }
    .adm-page-btn {
        min-width: 32px; height: 32px; padding: 0 8px;
        background: #fff; border: 1px solid var(--border);
        border-radius: var(--r-sm);
        font-family: inherit; font-size: 12px; font-weight: 500;
        color: var(--text-2); cursor: pointer;
        transition: border-color 0.15s, color 0.15s, background 0.15s;
    }
    .adm-page-btn:hover:not(:disabled) { border-color: var(--teal); color: var(--teal); }
    .adm-page-btn.active { background: var(--teal); border-color: var(--teal); color: #fff; font-weight: 700; }
    .adm-page-btn:disabled { opacity: 0.35; cursor: not-allowed; }

    /* 모달 */
    .adm-modal-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.35); z-index: 1050;
        align-items: center; justify-content: center;
    }
    .adm-modal-overlay.open { display: flex; }
    .adm-modal {
        background: #fff; border-radius: var(--r);
        width: 100%; max-width: 440px;
        box-shadow: 0 8px 40px rgba(0,0,0,0.16);
        overflow: hidden;
    }
    .adm-modal-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 18px 24px 14px;
        border-bottom: 1px solid var(--border);
    }
    .adm-modal-head h3 { font-size: 15px; font-weight: 700; }
    .adm-modal-close {
        background: none; border: none; cursor: pointer;
        color: var(--text-3); font-size: 18px; line-height: 1;
        padding: 0;
    }
    .adm-modal-body { padding: 20px 24px 24px; }
    .adm-mfield { display: flex; flex-direction: column; gap: 5px; margin-bottom: 14px; }
    .adm-mfield label { font-size: 11px; font-weight: 700; color: var(--text-2); letter-spacing: 0.04em; text-transform: uppercase; }
    .adm-mfield input, .adm-mfield select {
        height: 38px; padding: 0 10px;
        border: 1px solid var(--border-md); border-radius: var(--r-sm);
        background: var(--input-bg); font-family: inherit; font-size: 13px;
        color: var(--text-1); outline: none;
        transition: border-color 0.15s;
    }
    .adm-mfield input:focus, .adm-mfield select:focus { border-color: var(--teal); background: #fff; }
    .adm-mfield input[readonly] { background: var(--page-bg); color: var(--text-2); }
    .adm-modal-foot {
        display: flex; justify-content: flex-end; gap: 8px;
        padding: 14px 24px; border-top: 1px solid var(--border);
    }
    .adm-btn-cancel {
        height: 36px; padding: 0 16px; background: #fff;
        border: 1px solid var(--border-md); border-radius: var(--r-sm);
        font-family: inherit; font-size: 13px; font-weight: 600;
        color: var(--text-2); cursor: pointer;
    }
    .adm-btn-save {
        height: 36px; padding: 0 20px; background: var(--teal);
        border: none; border-radius: var(--r-sm);
        font-family: inherit; font-size: 13px; font-weight: 600;
        color: #fff; cursor: pointer; transition: background 0.12s;
    }
    .adm-btn-save:hover:not(:disabled) { background: #2F7169; }
    .adm-btn-save:disabled { opacity: 0.6; cursor: not-allowed; }
    .adm-alert { padding: 8px 12px; border-radius: var(--r-sm); font-size: 12px; margin-bottom: 12px; }
    .adm-alert-error { background: #FEE2E2; color: #DC2626; }
    .adm-alert-success { background: var(--teal-pale); color: var(--teal); }
    .adm-active-badge    { display:inline-block; padding:2px 8px; border-radius:99px; font-size:11px; font-weight:700; background:#DCFCE7; color:#15803D; }
    .adm-withdrawn-badge { display:inline-block; padding:2px 8px; border-radius:99px; font-size:11px; font-weight:700; background:#FEE2E2; color:#DC2626; }
    .adm-action-cell     { display:flex; gap:6px; align-items:center; }
    .adm-withdraw-btn {
        height: 28px; padding: 0 10px;
        background: #fff; border: 1px solid #FECACA;
        border-radius: var(--r-sm);
        font-family: inherit; font-size: 12px; font-weight: 600;
        color: #DC2626; cursor: pointer;
        transition: background 0.15s, border-color 0.15s;
    }
    .adm-withdraw-btn:hover { background: #FEE2E2; }
    .adm-restore-btn {
        height: 28px; padding: 0 10px;
        background: #fff; border: 1px solid #BBF7D0;
        border-radius: var(--r-sm);
        font-family: inherit; font-size: 12px; font-weight: 600;
        color: #15803D; cursor: pointer;
        transition: background 0.15s, border-color 0.15s;
    }
    .adm-restore-btn:hover { background: #DCFCE7; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<!-- 비로그인 -->
<div class="db-page" id="adminAuthWall" style="display:none;">
    <div class="db-auth-banner">
        <p>관리자 페이지에 접근하려면 로그인이 필요합니다.</p>
        <button class="db-auth-btn" data-bs-toggle="modal" data-bs-target="#authModal">
            <i class="bi bi-person-circle"></i> 로그인
        </button>
    </div>
</div>

<!-- 권한 없음 -->
<div class="db-page" id="adminForbidden" style="display:none;">
    <div class="db-auth-banner">
        <p>슈퍼 권한이 필요합니다.</p>
    </div>
</div>

<!-- 회원 관리 페이지 -->
<div class="db-page" id="adminPage" style="display:none;">
    <div class="db-header">
        <h1 class="db-title">회원 관리</h1>
    </div>

    <div class="adm-table-wrap">
        <div class="adm-toolbar">
            <div class="adm-search">
                <i class="bi bi-search" style="color:var(--text-3);font-size:12px;flex-shrink:0;"></i>
                <input type="text" id="admSearch" placeholder="이메일 또는 이름 검색" oninput="onSearchInput()">
            </div>
            <span class="adm-total" id="admTotal"></span>
        </div>

        <div id="admTableBody">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>이메일</th>
                        <th>권한</th>
                        <th>이름</th>
                        <th>연락처</th>
                        <th>가입일</th>
                        <th>최종 접속</th>
                        <th>접속 IP</th>
                        <th>상태</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="admTbody"></tbody>
            </table>
        </div>

        <div class="adm-pagination" id="admPagination"></div>
    </div>
</div>

<!-- 수정 모달 -->
<div class="adm-modal-overlay" id="admModalOverlay">
    <div class="adm-modal">
        <div class="adm-modal-head">
            <h3>회원 정보 수정</h3>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <div id="admModalAlert" style="display:none;"></div>
            <div class="adm-mfield">
                <label>이메일</label>
                <input type="text" id="admMEmail" readonly>
            </div>
            <div class="adm-mfield">
                <label>권한</label>
                <select id="admMRole">
                    <option value="s">슈퍼 (s)</option>
                    <option value="m">관리자 (m)</option>
                    <option value="a">작가 (a)</option>
                    <option value="u">회원 (u)</option>
                </select>
            </div>
            <div class="adm-mfield">
                <label>이름</label>
                <input type="text" id="admMName" placeholder="이름" maxlength="100">
            </div>
            <div class="adm-mfield">
                <label>연락처</label>
                <input type="text" id="admMPhone" placeholder="010-0000-0000" maxlength="30">
            </div>
            <div class="adm-mfield" style="margin-bottom:0;">
                <label>계정 상태</label>
                <select id="admMWithdrawn">
                    <option value="0">정상</option>
                    <option value="1">탈퇴</option>
                </select>
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" id="admSaveBtn" onclick="saveUser()">저장</button>
        </div>
    </div>
</div>

<script>
const ROLE_MAP  = { s: '슈퍼', m: '관리자', a: '작가', u: '회원' };
let currentPage = 1;
let searchTimer = null;
let editingId   = null;

function token() { return localStorage.getItem('pmok_auth_token'); }

async function init() {
    const user = authGetUser();
    if (!user) {
        document.getElementById('adminAuthWall').style.display = '';
        return;
    }
    if (user.role !== 's') {
        document.getElementById('adminForbidden').style.display = '';
        return;
    }
    document.getElementById('adminPage').style.display = '';
    await loadUsers(1);
}

async function loadUsers(page) {
    currentPage = page;
    const q    = document.getElementById('admSearch').value.trim();
    const url  = '/src/api/admin/users.php?page=' + page + (q ? '&q=' + encodeURIComponent(q) : '');
    const res  = await fetch(url, { headers: { 'Authorization': 'Bearer ' + token() } });
    const data = await res.json();
    if (!res.ok) return;

    renderTable(data.users);
    renderPagination(data.total, data.page, data.limit);
    document.getElementById('admTotal').textContent = '총 ' + data.total.toLocaleString() + '명';
}

function renderTable(users) {
    const tbody = document.getElementById('admTbody');
    if (!users.length) {
        tbody.innerHTML = '<tr><td colspan="9" style="padding:40px;text-align:center;color:var(--text-3);">검색 결과가 없습니다.</td></tr>';
        return;
    }
    tbody.innerHTML = users.map(u => `
        <tr>
            <td class="adm-id">${u.id}</td>
            <td class="adm-email">${esc(u.email)}</td>
            <td><span class="role-badge" data-role="${esc(u.role)}">${ROLE_MAP[u.role] || u.role}</span></td>
            <td>${u.name  ? esc(u.name)  : '<span class="adm-null">—</span>'}</td>
            <td>${u.phone ? esc(u.phone) : '<span class="adm-null">—</span>'}</td>
            <td style="color:var(--text-3);font-size:12px;">${u.created_at ? u.created_at.slice(0,10) : '—'}</td>
            <td style="color:var(--text-3);font-size:12px;">${fmtDatetime(u.last_login_at)}</td>
            <td style="color:var(--text-3);font-size:12px;font-family:monospace;">${u.last_login_ip ? esc(u.last_login_ip) : '<span class="adm-null">—</span>'}</td>
            <td>${u.withdrawn_at ? '<span class="adm-withdrawn-badge">탈퇴</span>' : '<span class="adm-active-badge">정상</span>'}</td>
            <td><div class="adm-action-cell">
                <button class="adm-edit-btn" onclick='openModal(${u.id}, ${JSON.stringify(u)})'>수정</button>
                ${u.withdrawn_at
                    ? `<button class="adm-restore-btn"  onclick="toggleWithdraw(${u.id}, false, '${esc(u.email)}')">복구</button>`
                    : `<button class="adm-withdraw-btn" onclick="toggleWithdraw(${u.id}, true,  '${esc(u.email)}')">탈퇴</button>`
                }
            </div></td>
        </tr>
    `).join('');
}

function renderPagination(total, page, limit) {
    const totalPages = Math.ceil(total / limit);
    const pg = document.getElementById('admPagination');
    if (totalPages <= 1) { pg.innerHTML = ''; return; }

    let html = `<button class="adm-page-btn" onclick="loadUsers(${page - 1})" ${page <= 1 ? 'disabled' : ''}><i class="bi bi-chevron-left"></i></button>`;
    const start = Math.max(1, page - 2);
    const end   = Math.min(totalPages, page + 2);
    for (let i = start; i <= end; i++) {
        html += `<button class="adm-page-btn ${i === page ? 'active' : ''}" onclick="loadUsers(${i})">${i}</button>`;
    }
    html += `<button class="adm-page-btn" onclick="loadUsers(${page + 1})" ${page >= totalPages ? 'disabled' : ''}><i class="bi bi-chevron-right"></i></button>`;
    pg.innerHTML = html;
}

function onSearchInput() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadUsers(1), 350);
}

function openModal(id, u) {
    editingId = id;
    document.getElementById('admMEmail').value     = u.email  || '';
    document.getElementById('admMRole').value      = u.role   || 'u';
    document.getElementById('admMName').value      = u.name   || '';
    document.getElementById('admMPhone').value     = u.phone  || '';
    document.getElementById('admMWithdrawn').value = u.withdrawn_at ? '1' : '0';
    document.getElementById('admModalAlert').style.display = 'none';
    document.getElementById('admModalOverlay').classList.add('open');
}

function closeModal() {
    document.getElementById('admModalOverlay').classList.remove('open');
    editingId = null;
}

async function toggleWithdraw(id, withdraw, email) {
    const msg = withdraw
        ? `${email} 회원을 탈퇴 처리하시겠습니까?`
        : `${email} 회원을 복구하시겠습니까?`;
    if (!confirm(msg)) return;

    const res  = await fetch('/src/api/admin/users.php', {
        method:  'PUT',
        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() },
        body: JSON.stringify({ id, withdrawn: withdraw }),
    });
    const data = await res.json();
    if (!res.ok) { alert(data.error || '오류가 발생했습니다.'); return; }
    await loadUsers(currentPage);
}

async function saveUser() {
    const btn = document.getElementById('admSaveBtn');
    btn.disabled = true; btn.textContent = '저장 중…';
    try {
        const res  = await fetch('/src/api/admin/users.php', {
            method:  'PUT',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() },
            body: JSON.stringify({
                id:        editingId,
                role:      document.getElementById('admMRole').value,
                name:      document.getElementById('admMName').value.trim(),
                phone:     document.getElementById('admMPhone').value.trim(),
                withdrawn: document.getElementById('admMWithdrawn').value === '1',
            }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || '오류가 발생했습니다.');
        closeModal();
        await loadUsers(currentPage);
    } catch (err) {
        const el = document.getElementById('admModalAlert');
        el.textContent = err.message;
        el.className   = 'adm-alert adm-alert-error';
        el.style.display = '';
    } finally {
        btn.disabled = false; btn.textContent = '저장';
    }
}

function fmtDatetime(dt) {
    if (!dt) return '<span class="adm-null">—</span>';
    return dt.slice(0, 16).replace('T', ' ');
}

function esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// 오버레이 클릭 시 닫기
document.getElementById('admModalOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

document.addEventListener('DOMContentLoaded', init);
window.addEventListener('pmokAuthChanged', init);
</script>
</body>
</html>
