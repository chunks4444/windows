const ROLE_MAP  = { s: '슈퍼', m: '관리자', a: '작가', u: '회원' };
let currentPage = 1;
let searchTimer = null;
let editingId   = null;

function token() { return localStorage.getItem('pmok_auth_token'); }

async function init() {
    const user = authGetUser();
    if (!user || user.role !== 's') { location.href = '/'; return; }
    document.getElementById('adminPage').style.display = '';
    await loadUsers(1);
}

async function loadUsers(page) {
    currentPage = page;
    const q    = document.getElementById('admSearch').value.trim();
    const res  = await fetch('/src/api/admin/users.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() },
        body: JSON.stringify({ page, q }),
    });
    const data = await res.json();
    if (!res.ok) return;

    renderTable(data.users);
    renderPagination(data.total, data.page, data.limit);
    document.getElementById('admTotal').textContent = '총 ' + data.total.toLocaleString() + '명';
}

function renderTable(users) {
    const tbody = document.getElementById('admTbody');
    if (!users.length) {
        tbody.innerHTML = '<tr><td colspan="10" style="padding:40px;text-align:center;color:var(--text-3);">검색 결과가 없습니다.</td></tr>';
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
            <td style="text-align:center;color:var(--text-3);font-size:12px;">${u.drawing_count || 0}</td>
            <td style="text-align:center;">
                ${+u.export_count > 0
                    ? `<button class="adm-edit-btn" style="height:24px;padding:0 8px;font-size:11px;" onclick="openExportModal(${u.id}, '${esc(u.email)}')">${u.export_count}</button>`
                    : `<span style="color:var(--text-3);font-size:12px;">0</span>`
                }
            </td>
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

async function openExportModal(userId, email) {
    document.getElementById('exportModalTitle').textContent = `내보내기 내역 — ${email}`;
    document.getElementById('exportModalTbody').innerHTML = '<tr><td colspan="5" style="padding:20px;text-align:center;color:var(--text-3);">로딩 중…</td></tr>';
    document.getElementById('exportModalOverlay').classList.add('open');

    const res  = await fetch('/src/api/admin/export_logs.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() },
        body: JSON.stringify({ user_id: userId, limit: 100 }),
    });
    const data = await res.json();
    const logs = data.logs || [];

    document.getElementById('exportModalTbody').innerHTML = logs.map(r => `
        <tr style="border-bottom:1px solid var(--border);">
            <td style="padding:8px 14px;color:var(--text-3);white-space:nowrap;">${r.created_at.slice(0,16)}</td>
            <td style="padding:8px 14px;"><span style="font-size:11px;padding:1px 6px;border-radius:8px;background:var(--accent-bg);color:var(--text-2);">${esc(r.engine)}</span></td>
            <td style="padding:8px 14px;font-weight:600;color:${r.format==='pdf'?'#e05218':'#3a8c82'};">${r.format.toUpperCase()}</td>
            <td style="padding:8px 14px;color:var(--text-2);">${esc(r.drawing_name || '—')}</td>
            <td style="padding:8px 14px;color:var(--text-3);font-family:monospace;">${esc(r.version || '—')}</td>
        </tr>
    `).join('') || '<tr><td colspan="5" style="padding:20px;text-align:center;color:var(--text-3);">내역 없음</td></tr>';
}

function closeExportModal() {
    document.getElementById('exportModalOverlay').classList.remove('open');
}

document.getElementById('exportModalOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeExportModal();
});

document.addEventListener('DOMContentLoaded', init);
window.addEventListener('pmokAuthChanged', init);
