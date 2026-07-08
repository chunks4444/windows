const ENGINE_MAP = { square: '정자살', classic: '완자살', cross: '아자살', diamond: '완자문살', triangle: '세모솟을살', hexagon: '육모살' };

let currentPage = 1;
let searchTimer = null;
let editingId   = null;

function token() { return localStorage.getItem('pmok_auth_token'); }

async function init() {
    const user = authGetUser();
    if (!user || user.role !== 's') { location.href = '/'; return; }
    document.getElementById('adminPage').style.display = '';
    populateStatusOptions();
    await loadOrders(1);
}

function populateStatusOptions() {
    const filter = document.getElementById('admStatusFilter');
    const modal  = document.getElementById('admMStatus');
    for (const [value, { label }] of Object.entries(ORDER_STATUS_LABELS)) {
        filter.insertAdjacentHTML('beforeend', `<option value="${value}">${label}</option>`);
        modal.insertAdjacentHTML('beforeend', `<option value="${value}">${label}</option>`);
    }
}

async function loadOrders(page) {
    currentPage = page;
    const q      = document.getElementById('admSearch').value.trim();
    const status = document.getElementById('admStatusFilter').value;
    const res  = await fetch('/src/api/admin/orders.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() },
        body: JSON.stringify({ page, q, status }),
    });
    const data = await res.json();
    if (!res.ok) return;

    renderTable(data.orders);
    renderPagination(data.total, data.page, data.limit);
    document.getElementById('admTotal').textContent = '총 ' + data.total.toLocaleString() + '건';
}

function renderTable(orders) {
    const tbody = document.getElementById('admTbody');
    if (!orders.length) {
        tbody.innerHTML = '<tr><td colspan="8" style="padding:40px;text-align:center;color:var(--text-3);">주문이 없습니다.</td></tr>';
        return;
    }
    tbody.innerHTML = orders.map(o => `
        <tr>
            <td class="adm-id">#${o.id}</td>
            <td style="color:var(--text-3);font-size:12px;">${o.created_at ? o.created_at.slice(0, 10) : '—'}</td>
            <td>${esc(o.customer_name)}<br><span style="color:var(--text-3);font-size:12px;">${esc(o.customer_phone)}</span></td>
            <td>${o.company_name ? esc(o.company_name) : '<span class="adm-null">—</span>'}</td>
            <td>${ENGINE_MAP[o.engine] || esc(o.engine)}${o.title ? ' · ' + esc(o.title) : ''}</td>
            <td style="color:var(--text-3);font-size:12px;">${o.due_date || '—'}</td>
            <td><span class="ord-status-badge" data-status="${esc(o.status)}">${ORDER_STATUS_LABELS[o.status]?.label || o.status}</span></td>
            <td><div class="adm-action-cell">
                <button class="adm-edit-btn" onclick="openModal(${o.id})">상세</button>
            </div></td>
        </tr>
    `).join('');
}

function renderPagination(total, page, limit) {
    const totalPages = Math.ceil(total / limit);
    const pg = document.getElementById('admPagination');
    if (totalPages <= 1) { pg.innerHTML = ''; return; }

    let html = `<button class="adm-page-btn" onclick="loadOrders(${page - 1})" ${page <= 1 ? 'disabled' : ''}><i class="bi bi-chevron-left"></i></button>`;
    const start = Math.max(1, page - 2);
    const end   = Math.min(totalPages, page + 2);
    for (let i = start; i <= end; i++) {
        html += `<button class="adm-page-btn ${i === page ? 'active' : ''}" onclick="loadOrders(${i})">${i}</button>`;
    }
    html += `<button class="adm-page-btn" onclick="loadOrders(${page + 1})" ${page >= totalPages ? 'disabled' : ''}><i class="bi bi-chevron-right"></i></button>`;
    pg.innerHTML = html;
}

function onSearchInput() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadOrders(1), 350);
}

async function openModal(id) {
    editingId = id;
    document.getElementById('admModalTitle').textContent = `주문번호 #${id} 상세`;
    document.getElementById('admDetailContent').innerHTML = '<p style="color:var(--text-3);">로딩 중…</p>';
    document.getElementById('admModalAlert').style.display = 'none';
    document.getElementById('admModalOverlay').classList.add('open');

    const res  = await fetch('/src/api/admin/orders.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() },
        body: JSON.stringify({ id }),
    });
    const data = await res.json();
    if (!res.ok) { document.getElementById('admDetailContent').innerHTML = `<p style="color:var(--danger);">${esc(data.error || '불러오기 실패')}</p>`; return; }

    renderDetail(data.order);
    document.getElementById('admMStatus').value     = data.order.status;
    document.getElementById('admMNote').value        = data.order.revision_note || '';
    document.getElementById('admMCarrier').value     = data.order.tracking_carrier || '';
    document.getElementById('admMTrackNo').value     = data.order.tracking_number || '';
    document.getElementById('admMFinalPrice').value  = data.order.final_price || '';
    document.getElementById('admMPriceNote').value   = data.order.price_note || '';
    toggleConditionalFields();
}

function renderPriceBreakdown(breakdownJson) {
    if (!breakdownJson) return '<div class="full ord-detail-value">견적 산출 내역이 없습니다 (구버전 주문이거나 도면 없이 접수됨).</div>';
    let b;
    try { b = JSON.parse(breakdownJson); } catch { return '<div class="full ord-detail-value">견적 내역을 읽을 수 없습니다.</div>'; }

    const won = n => Number(n || 0).toLocaleString() + '원';
    const rows = [
        ['문',   b.door],
        ['문틀', b.muntol],
        ['목재비 합계', b.wood],
        ['가공비' + (b.craftTime ? ` (${esc(b.craftTime)})` : ''), b.craft],
        ['철물', b.hardware],
        ['마감', b.finish],
        ['부자재/간접비', b.overhead],
        ['이윤', b.profit],
    ];
    return `
        <table class="ord-price-table">
            ${rows.map(([label, val]) => `<tr><td>${label}</td><td>${won(val)}</td></tr>`).join('')}
            <tr class="ord-price-total"><td>합계</td><td>${won(b.total)}</td></tr>
        </table>
    `;
}

function renderDetail(o) {
    const shipAddr = [o.ship_zipcode, o.ship_address, o.ship_address_detail].filter(Boolean).join(' ');
    document.getElementById('admDetailContent').innerHTML = `
        ${o.thumbnail ? `<img src="${o.thumbnail}" class="ord-thumb" alt="도면 썸네일">` : ''}
        <div class="ord-detail-grid">
            <div class="full ord-detail-label">엔진/도면</div>
            <div class="full ord-detail-value">${ENGINE_MAP[o.engine] || esc(o.engine)} — ${esc(o.title || '(제목 없음)')}${o.version_label ? ' (' + esc(o.version_label) + ')' : ''}</div>

            <div class="ord-detail-label">고객명</div>
            <div class="ord-detail-label">연락처</div>
            <div class="ord-detail-value">${esc(o.customer_name)}</div>
            <div class="ord-detail-value">${esc(o.customer_phone)}</div>

            <div class="ord-detail-label">회사명</div>
            <div class="ord-detail-label">납기희망일</div>
            <div class="ord-detail-value">${o.company_name ? esc(o.company_name) : '—'}</div>
            <div class="ord-detail-value">${o.due_date || '—'}</div>

            <div class="full ord-detail-label">배송지</div>
            <div class="full ord-detail-value">${shipAddr ? esc(shipAddr) : '—'}${o.ship_phone ? ' / ' + esc(o.ship_phone) : ''}</div>

            <div class="full ord-detail-label">요청사항</div>
            <div class="full ord-detail-value">${o.memo ? esc(o.memo) : '—'}</div>

            <div class="full ord-detail-label">주문 시점 예상견적 상세 (도면 화면과 동일한 산출 내역 — 상담 참고용)</div>
            <div class="full ord-detail-value">${renderPriceBreakdown(o.price_breakdown)}</div>

            <div class="ord-detail-label">확정 가격</div>
            <div class="ord-detail-label">주문일시</div>
            <div class="ord-detail-value">${o.final_price ? Number(o.final_price).toLocaleString() + '원' : '미입력'}</div>
            <div class="ord-detail-value">${fmtOrderDatetime(o.created_at)}</div>

            <div class="ord-detail-label">최근 처리일시</div>
            <div class="ord-detail-value">${fmtOrderDatetime(o.reviewed_at)}</div>
        </div>
    `;
}

function toggleConditionalFields() {
    const status = document.getElementById('admMStatus').value;
    document.getElementById('admMNoteField').style.display          = status === 'revision_requested' ? '' : 'none';
    document.getElementById('admMTrackingField').style.display      = status === 'shipped' ? '' : 'none';
    document.getElementById('admMTrackingNoField').style.display    = status === 'shipped' ? '' : 'none';
}
document.getElementById('admMStatus').addEventListener('change', toggleConditionalFields);

function closeModal() {
    document.getElementById('admModalOverlay').classList.remove('open');
    editingId = null;
}

async function saveOrder() {
    const btn = document.getElementById('admSaveBtn');
    btn.disabled = true; btn.textContent = '저장 중…';
    try {
        const res  = await fetch('/src/api/admin/orders.php', {
            method:  'PUT',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() },
            body: JSON.stringify({
                id:                editingId,
                status:            document.getElementById('admMStatus').value,
                revision_note:     document.getElementById('admMNote').value.trim(),
                tracking_carrier:  document.getElementById('admMCarrier').value.trim(),
                tracking_number:   document.getElementById('admMTrackNo').value.trim(),
                final_price:       document.getElementById('admMFinalPrice').value.trim(),
                price_note:        document.getElementById('admMPriceNote').value.trim(),
            }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || '오류가 발생했습니다.');
        closeModal();
        await loadOrders(currentPage);
    } catch (err) {
        const el = document.getElementById('admModalAlert');
        el.textContent = err.message;
        el.className   = 'adm-alert adm-alert-error';
        el.style.display = '';
    } finally {
        btn.disabled = false; btn.textContent = '저장';
    }
}

function esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

document.getElementById('admModalOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

document.addEventListener('DOMContentLoaded', init);
window.addEventListener('pmokAuthChanged', init);
