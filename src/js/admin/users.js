const ROLE_MAP  = { s: '슈퍼', m: '관리자', a: '작가', u: '회원' };
let currentPage = 1;
let searchTimer = null;
let editingId   = null;
let companyId   = null;

function token() { return localStorage.getItem('pmok_auth_token'); }

async function init() {
    const user = authGetUser();
    if (!user || user.role !== 's') { location.href = '/'; return; }
    document.getElementById('adminPage').style.display = '';
    await loadUsers(1);
}

const PERM_LABELS = {
    view_parts:    '부재목록',
    view_cost:     '예산견적 상세',
    view_price:    '예상가격',
    view_leadtime: '최소 납기',
    view_shipping: '배송비',
    view_desc:     '설명',
};

async function loadUsers(page) {
    currentPage = page;
    const q    = document.getElementById('admSearch').value.trim();
    const perm = document.getElementById('admPermFilter').value;
    const res  = await fetch('/src/api/admin/users.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() },
        body: JSON.stringify({ page, q, perm }),
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
        tbody.innerHTML = '<tr><td colspan="11" style="padding:40px;text-align:center;color:var(--text-3);">검색 결과가 없습니다.</td></tr>';
        return;
    }
    tbody.innerHTML = users.map(u => `
        <tr>
            <td class="adm-id">${u.id}</td>
            <td class="adm-email">${esc(u.email)}</td>
            <td class="adm-role-cell">
                <span class="role-badge" data-role="${esc(u.role)}">${ROLE_MAP[u.role] || u.role}</span>
                <span class="perm-hover-badge" title="${esc(permTooltip(u))}"><i class="bi bi-shield-lock"></i> ${permCount(u)}/6</span>
            </td>
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
            <td style="text-align:center;">
                <button class="adm-edit-btn" style="height:24px;padding:0 8px;font-size:11px;" onclick='openCompanyModal(${u.id}, ${JSON.stringify(u)})'>${u.company_name ? esc(u.company_name) : '입력'}</button>
            </td>
            <td><div class="adm-action-cell">
                <button class="adm-edit-btn" onclick='openModal(${u.id}, ${JSON.stringify(u)})'>수정</button>
                ${u.role !== 's' && !u.withdrawn_at
                    ? `<button class="adm-edit-btn" title="이 회원 계정으로 로그인" onclick="impersonateUser(${u.id}, '${esc(u.email)}')"><i class="bi bi-box-arrow-in-right"></i></button>`
                    : ''
                }
                ${u.withdrawn_at
                    ? `<button class="adm-restore-btn"  onclick="toggleWithdraw(${u.id}, false, '${esc(u.email)}')">복구</button>`
                    : `<button class="adm-withdraw-btn" onclick="toggleWithdraw(${u.id}, true,  '${esc(u.email)}')">탈퇴</button>`
                }
            </div></td>
        </tr>
    `).join('');
}

function permCount(u) {
    return Object.keys(PERM_LABELS).filter(k => Number(u[k]) === 1).length;
}

function permTooltip(u) {
    return '열람 권한\n' + Object.entries(PERM_LABELS)
        .map(([k, label]) => `${Number(u[k]) === 1 ? '✓' : '✗'} ${label}`)
        .join('\n');
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
    document.getElementById('admMViewParts').checked    = Number(u.view_parts)    === 1;
    document.getElementById('admMViewCost').checked     = Number(u.view_cost)     === 1;
    document.getElementById('admMViewPrice').checked    = Number(u.view_price)    === 1;
    document.getElementById('admMViewLeadtime').checked = Number(u.view_leadtime) === 1;
    document.getElementById('admMViewShipping').checked = Number(u.view_shipping) === 1;
    document.getElementById('admMViewDesc').checked     = Number(u.view_desc)     === 1;
    document.getElementById('admModalAlert').style.display = 'none';
    document.getElementById('admModalOverlay').classList.add('open');
}

function closeModal() {
    document.getElementById('admModalOverlay').classList.remove('open');
    editingId = null;
}

async function impersonateUser(id, email) {
    if (!confirm(`${email} 회원 계정으로 로그인할까요?\n(관리자 화면에는 "관리자로 복귀" 배너가 뜨고, 해당 회원 쪽에는 아무 흔적도 남지 않습니다.)`)) return;
    const res  = await fetch('/src/api/admin/impersonate.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() },
        body: JSON.stringify({ user_id: id }),
    });
    const data = await res.json();
    if (!res.ok) { alert(data.error || '오류가 발생했습니다.'); return; }
    startImpersonation(data.token, data.user);
    location.href = '/mypage/dashboard';
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
                view_parts:    document.getElementById('admMViewParts').checked,
                view_cost:     document.getElementById('admMViewCost').checked,
                view_price:    document.getElementById('admMViewPrice').checked,
                view_leadtime: document.getElementById('admMViewLeadtime').checked,
                view_shipping: document.getElementById('admMViewShipping').checked,
                view_desc:     document.getElementById('admMViewDesc').checked,
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

function openCompanyModal(id, u) {
    companyId = id;
    document.getElementById('cmName').value          = u.company_name           || '';
    document.getElementById('cmBizNo').value         = u.company_biz_no         || '';
    document.getElementById('cmBizType').value       = u.company_biz_type       || '';
    document.getElementById('cmBizCat').value        = u.company_biz_category   || '';
    document.getElementById('cmCeo').value           = u.company_ceo           || '';
    document.getElementById('cmPhone').value          = u.company_phone         || '';
    document.getElementById('cmZipcode').value        = u.company_zipcode       || '';
    document.getElementById('cmAddress').value        = u.company_address       || '';
    document.getElementById('cmAddressDetail').value  = u.company_address_detail || '';
    document.getElementById('companyModalAlert').style.display = 'none';
    document.getElementById('companyModalOverlay').classList.add('open');
}

function closeCompanyModal() {
    document.getElementById('companyModalOverlay').classList.remove('open');
    companyId = null;
}

async function saveCompany() {
    const btn = document.getElementById('companySaveBtn');
    btn.disabled = true; btn.textContent = '저장 중…';
    try {
        const res  = await fetch('/src/api/admin/users.php', {
            method:  'PUT',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() },
            body: JSON.stringify({
                id:                     companyId,
                company_name:           document.getElementById('cmName').value.trim(),
                company_biz_no:         document.getElementById('cmBizNo').value.trim(),
                company_biz_type:       document.getElementById('cmBizType').value.trim(),
                company_biz_category:   document.getElementById('cmBizCat').value.trim(),
                company_ceo:            document.getElementById('cmCeo').value.trim(),
                company_phone:          document.getElementById('cmPhone').value.trim(),
                company_zipcode:        document.getElementById('cmZipcode').value.trim(),
                company_address:        document.getElementById('cmAddress').value.trim(),
                company_address_detail: document.getElementById('cmAddressDetail').value.trim(),
            }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || '오류가 발생했습니다.');
        closeCompanyModal();
        await loadUsers(currentPage);
    } catch (err) {
        const el = document.getElementById('companyModalAlert');
        el.textContent = err.message;
        el.className   = 'adm-alert adm-alert-error';
        el.style.display = '';
    } finally {
        btn.disabled = false; btn.textContent = '저장';
    }
}

document.getElementById('companyModalOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeCompanyModal();
});

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
