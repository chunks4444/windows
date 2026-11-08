const API = '/src/api/admin/promo_companies.php';
function _h() { return { 'Authorization': 'Bearer ' + token(), 'Content-Type': 'application/json' }; }

let companies = [], industries = [], curTab = '';

async function loadCompanies() {
    const res  = await fetch(API, { headers: _h() });
    const data = await res.json();
    if (!res.ok) { alert(data.error || '불러오기 실패'); return; }
    companies  = data.companies || [];
    industries = data.industries || [];
    const sel = document.getElementById('pcIndustry');
    sel.innerHTML = industries.map(i => `<option value="${esc(i)}">${esc(i)}</option>`).join('');
    render();
}

function renderTabs() {
    const count = key => key ? companies.filter(c => c.industry === key).length : companies.length;
    const tabs  = [['', '전체'], ...industries.map(i => [i, i])];
    document.getElementById('pcTabs').innerHTML = tabs.map(([key, label]) =>
        `<button class="adm-tab-btn${curTab === key ? ' active' : ''}" onclick="setTab('${esc(key)}')">${esc(label)} <span style="opacity:.6;">${count(key)}</span></button>`
    ).join('');
}

function setTab(key) { curTab = key; render(); }

function render() {
    renderTabs();
    const q = document.getElementById('pcSearch').value.trim().toLowerCase();
    const sentFilter = document.getElementById('pcSentFilter').value;
    const rows = companies.filter(c =>
        (!curTab || c.industry === curTab) &&
        (!sentFilter || (sentFilter === 'sent' ? c.sent_count > 0 : c.sent_count == 0)) &&
        (!q || [c.name, c.email, c.phone, c.address, c.website].some(v => (v || '').toLowerCase().includes(q)))
    );
    document.getElementById('pcTotal').textContent = `${rows.length}곳`;
    document.getElementById('pcBody').innerHTML = rows.length ? rows.map(c => `
        <tr>
            <td><strong>${esc(c.name)}</strong></td>
            <td>${esc(c.industry)}</td>
            <td>${esc(c.email)}</td>
            <td>${c.phone ? esc(c.phone) : '<span class="adm-null">—</span>'}</td>
            <td>${c.address ? esc(c.address) : '<span class="adm-null">—</span>'}</td>
            <td>${c.website ? `<a href="${esc(c.website)}" target="_blank" rel="noopener noreferrer">${esc(c.website.replace(/^https?:\/\//i, '').replace(/\/$/, ''))}</a>` : '<span class="adm-null">—</span>'}</td>
            <td>${c.sent_count > 0 ? `${c.sent_count}회` : '<span class="adm-null">미발송</span>'}</td>
            <td>${c.last_sent_at ? esc(c.last_sent_at.slice(0, 16)) : '<span class="adm-null">—</span>'}</td>
            <td><span class="${c.opt_out == 1 ? 'adm-withdrawn-badge' : 'adm-active-badge'}">${c.opt_out == 1 ? '수신거부' : '정상'}</span></td>
            <td>
                <div class="adm-action-cell">
                    <button class="adm-edit-btn" style="height:28px;padding:0 10px;font-size:12px;" onclick="openModal(${c.id})">수정</button>
                    <button class="adm-withdraw-btn" onclick="toggleOptOut(${c.id})">${c.opt_out == 1 ? '수신거부 해제' : '수신거부'}</button>
                    <button class="adm-withdraw-btn" style="background:#c00;color:#fff;" onclick="deleteCompany(${c.id})">삭제</button>
                </div>
            </td>
        </tr>`).join('')
        : `<tr><td colspan="10" style="text-align:center;padding:32px 0;"><span class="adm-null">등록된 회사가 없습니다.</span></td></tr>`;
}

function openModal(id) {
    const c = id ? companies.find(x => x.id == id) : null;
    document.getElementById('pcModalTitle').textContent = c ? '회사 수정' : '회사 등록';
    document.getElementById('pcId').value       = c?.id ?? '';
    document.getElementById('pcName').value     = c?.name ?? '';
    document.getElementById('pcIndustry').value = c?.industry || curTab || industries[0] || '';
    document.getElementById('pcEmail').value    = c?.email ?? '';
    document.getElementById('pcPhone').value    = c?.phone ?? '';
    document.getElementById('pcAddress').value  = c?.address ?? '';
    document.getElementById('pcWebsite').value  = c?.website ?? '';
    document.getElementById('pcOptOut').checked = c?.opt_out == 1;
    document.getElementById('pcModalOverlay').classList.add('open');
    document.getElementById('pcName').focus();
}

function closeModal() {
    document.getElementById('pcModalOverlay').classList.remove('open');
}

async function saveCompany() {
    const btn = document.getElementById('pcSaveBtn');
    btn.disabled = true;
    try {
        const body = {
            action:   'save',
            id:       parseInt(document.getElementById('pcId').value) || 0,
            name:     document.getElementById('pcName').value,
            industry: document.getElementById('pcIndustry').value,
            email:    document.getElementById('pcEmail').value,
            phone:    document.getElementById('pcPhone').value,
            address:  document.getElementById('pcAddress').value,
            website:  document.getElementById('pcWebsite').value,
            opt_out:  document.getElementById('pcOptOut').checked ? 1 : 0,
        };
        const res  = await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify(body) });
        const data = await res.json();
        if (data.ok) { closeModal(); await loadCompanies(); }
        else alert(data.error || '저장 실패');
    } catch {
        alert('서버 오류');
    } finally {
        btn.disabled = false;
    }
}

async function toggleOptOut(id) {
    await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'toggle_opt_out', id }) });
    loadCompanies();
}

async function deleteCompany(id) {
    const c = companies.find(x => x.id == id);
    if (!confirm(`"${c?.name ?? ''}"을(를) 삭제할까요?`)) return;
    await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'delete', id }) });
    loadCompanies();
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('pcModalOverlay').addEventListener('click', e => {
        if (e.target === e.currentTarget) closeModal();
    });
    const page = document.getElementById('pcPage');
    const show = () => { page.style.display = ''; loadCompanies(); };
    if (authGetToken()) show();
    window.addEventListener('pmokAuthChanged', show);
    authUpdateNav();
});
