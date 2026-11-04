const ENGINE_MAP = { square: '정자살', classic: '완자살', cross: '아자살', diamond: '완자문살', triangle: '세모솟을살', hexagon: '육모살', mondrian: '몬드리안' };

let currentPage = 1;
let searchTimer = null;
let editingId   = null;

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
            <td class="adm-id">${fmtOrderCode(o.engine, o.id)}</td>
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

    document.getElementById('admModalTitle').textContent = `${fmtOrderCode(data.order.engine, id)} 상세`;
    renderDetail(data.order, data.history || [], data.spec, data.wood, data.drawingEstimate, data.quotes || []);
    document.getElementById('admMStatus').value     = data.order.status;
    document.getElementById('admMNote').value        = data.order.revision_note || '';
    document.getElementById('admMCarrier').value     = data.order.tracking_carrier || '';
    document.getElementById('admMTrackNo').value     = data.order.tracking_number || '';
    document.getElementById('admMFinalPrice').value  = data.order.final_price || '';
    document.getElementById('admMPriceNote').value   = data.order.price_note || '';
    toggleConditionalFields();
}

// 견적이 언제 값인지 — 주문에 고정 저장된 시점(computedAt). 도면·원가표가 나중에 바뀌어도 이 값은 그대로다.
// computedAt 도입 전 주문은 화면 값을 접수 때 저장했으므로 주문일시가 곧 산출 시각.
function estimateStamp(b, orderCreatedAt) {
    const at = fmtOrderDatetime(b.computedAt || orderCreatedAt);
    if (b.source === 'admin_backfill') return `<strong>${at}</strong> 산출·고정 <span class="ord-spec-sub">— 주문 기록에 견적이 없어 관리자 첫 열람 때 그 시점 원가표로 계산</span>`;
    return `<strong>${at}</strong> 기준 (주문 접수 시점 고정)${b.approx ? ' <span class="ord-spec-sub">· 몬드리안 실측 살 미반영 근사값</span>' : ''}`;
}

// 엔진 "예상 견적" 패널(src/engine/*/*.php .sb-price-box)과 같은 항목명·순서·구분선으로 그린다 — 고객이 본 화면 그대로.
// 값은 주문 시점 price_breakdown 스냅샷이고, 마감만 0원이면 '–'(엔진 applyPriceFromServer와 같은 규칙).
function renderPriceBreakdown(breakdownJson, orderCreatedAt) {
    if (!breakdownJson) return '<div class="ord-detail-value">견적 산출 내역이 없습니다 (구버전 주문이거나 도면 없이 접수됨).</div>';
    let b;
    try { b = JSON.parse(breakdownJson); } catch { return '<div class="ord-detail-value">견적 내역을 읽을 수 없습니다.</div>'; }

    const won = n => Math.round(Number(n || 0)).toLocaleString('ko-KR') + '원';
    const row = (label, val, cls = '') => `<div class="ord-est-row ${cls}"><span>${label}</span><span>${val}</span></div>`;
    const div = '<div class="ord-est-divider"></div>';
    return `
        <div class="ord-est-box">
            <div class="ord-est-head"><span>예상가격</span><span class="ord-est-amount">${won(b.total)}</span></div>
            <div class="ord-est-break">
                ${row('문(창호) 목재', won(b.door))}
                ${row('문틀 목재', won(b.muntol))}
                ${row('목재비', won(b.wood), 'key')}
                ${div}
                ${row(`제작비 <small>${esc(b.craftTime || '')}</small>`, won(b.craft), 'key')}
                ${row('부자재', won(b.hardware), 'key')}
                ${row('마감', b.finish > 0 ? won(b.finish) : '–', 'key')}
                ${div}
                ${row('간접비', won(b.overhead), 'key')}
                ${row('이익', won(b.profit), 'key')}
                ${div}
                ${row('판매가', won(b.total), 'key total')}
                ${b.leadTimeDays != null ? row('최소 납기', `<strong>${Number(b.leadTimeDays)}</strong>일`) : ''}
            </div>
            <div class="ord-est-stamp">${estimateStamp(b, orderCreatedAt)}</div>
            <div class="ord-est-note">계산 공식 ${b.formulaVersion ? 'v' + esc(b.formulaVersion) : 'v1.0 (버전 기록 전)'} · ※ 배송비·시공비 제외</div>
        </div>
    `;
}

function renderHistory(history, engine) {
    return `
            <div class="ord-history-list">
                ${history.map(h => `
                    <div class="ord-history-item">
                        ${h.thumbnail ? `<img src="${h.thumbnail}" class="ord-history-thumb" alt="이전 도면 썸네일">` : ''}
                        <div class="ord-history-body">
                            <div>
                                <span class="ord-status-badge" data-status="${esc(h.status)}">${ORDER_STATUS_LABELS[h.status]?.label || h.status}</span>
                                <span style="color:var(--text-3);font-size:12px;">${fmtOrderCode(engine, h.id)} · ${fmtOrderDatetime(h.created_at)}</span>
                            </div>
                            ${h.title ? `<div>${esc(h.title)}${h.version_label ? ' (' + esc(h.version_label) + ')' : ''}</div>` : ''}
                            ${h.memo ? `<div class="ord-history-note"><strong>요청사항</strong> ${esc(h.memo)}</div>` : ''}
                            ${h.revision_note ? `<div class="ord-history-note"><strong>수정요청 사유</strong> ${esc(h.revision_note)}</div>` : ''}
                            ${h.price_note ? `<div class="ord-history-note"><strong>가격 협의 메모</strong> ${esc(h.price_note)}</div>` : ''}
                        </div>
                    </div>
                `).join('')}
            </div>
    `;
}

// 주문 시점 도면 사양 — 서버(order_spec_summary)가 spec_json을 요약해 내려준 값
function renderSpec(spec, wood) {
    if (!spec) return null;
    const s = spec.size, f = spec.finish;
    const mm = n => Number(n).toLocaleString() + 'mm';
    const sw = c => c ? `<span class="ord-swatch" style="background:${esc(c.hex)}"></span>${c.label ? esc(c.label) : esc(c.hex)}` : '—';
    const doorKind = (s.doorType === 'slide' ? '미서기' : '여닫이') + ' ' + s.doorCount + '짝';
    const sizeRows = [
        ['문틀(개구부)', `${mm(s.openingW)} × ${mm(s.openingH)}`],
        ['문 종류', doorKind],
        ['문 1짝 크기', `${mm(s.doorW)} × ${mm(s.doorH)} <span class="ord-spec-sub">(문틀두께 ${s.frameThick} · 갭 ${s.frameGap} 기준 계산)</span>`],
        ['울거미 나비', `좌우 ${mm(s.frameW)} · 상하 ${mm(s.frameH)}`],
        ['살 나비', s.slat != null ? mm(s.slat) : '—'],
        ['칸수', [s.cols != null ? `세로 ${s.cols}칸` : '', s.rows ? `가로살 ${s.rows}개` : '', s.vRatio && s.vRatio !== 1 ? `세로비율 ${s.vRatio}` : ''].filter(Boolean).join(' · ') || '—'],
        ['풍판', s.pungpan != null ? mm(s.pungpan) : '없음'],
    ];
    if (s.rotate) sizeRows.push(['회전', '90° 회전']);
    const finishRows = [
        ['목재', f.wood ? esc(f.wood) : '—'],
        ['마감', f.finish ? esc(f.finish) : '마감 없음'],
        ['부자재', f.hardware ? esc(f.hardware) : '없음'],
        ['울거미 색', sw(f.frameColor)],
        ['살 색', sw(f.slatColor)],
        ['문틀', s.showMuntol ? sw(f.muntolColor) : '표시 안 함'],
        ['면 배지(문 전체)', f.backing ? sw(f.backing) : '없음'],
    ];
    if (wood) {
        const jae = n => Number(n).toLocaleString(undefined, { maximumFractionDigits: 2 }) + '재';
        finishRows.splice(1, 0, ['목재 사용량', `<strong>${jae(wood.totalJae)}</strong> (${wood.totalM3}㎥)`
            + ` <span class="ord-spec-sub">문 ${jae(wood.doorJae)}${wood.muntolJae > 0 ? ' · 문틀 ' + jae(wood.muntolJae) : ''} · 1재=30.3×30.3×3636mm, 주문 당시 목재비÷현재 단가로 역산</span>`]);
    }
    if (f.faceCells.length) finishRows.push(['칸별 면 재질', f.faceCells.map(c => `${sw(c)} — ${c.cells}칸`).join('<br>')]);
    const table = rows => `<table class="ord-price-table ord-spec-table">${rows.map(([k, v]) => `<tr><td>${k}</td><td>${v}</td></tr>`).join('')}</table>`;
    return { size: table(sizeRows), finish: table(finishRows) };
}

// 상세 모달의 정보 묶음 — 접고 편 상태는 묶음 key별로 이 브라우저에 기억(다음 주문을 열어도 유지)
const ORD_GROUP_KEY = 'pmok_admin_order_groups';
function ordGroupState() {
    try { return JSON.parse(localStorage.getItem(ORD_GROUP_KEY) || '{}'); } catch { return {}; }
}
function ordGroup(key, title, body, defaultOpen = true) {
    const st = ordGroupState();
    const open = key in st ? st[key] : defaultOpen;
    return `<details class="ord-group" data-group="${key}"${open ? ' open' : ''}>
        <summary class="ord-group-title">${title}</summary>
        <div class="ord-group-body">${body}</div>
    </details>`;
}
document.addEventListener('toggle', e => {
    const d = e.target;
    if (!(d instanceof HTMLDetailsElement) || !d.classList.contains('ord-group')) return;
    try {
        const st = ordGroupState();
        st[d.dataset.group] = d.open;
        localStorage.setItem(ORD_GROUP_KEY, JSON.stringify(st));
    } catch {}
}, true);

function drawingLink(o) {
    if (!o.drawing_id) return '<span class="ord-spec-sub">(저장 전 도면으로 접수 — 연결된 도면 없음)</span>';
    const e = encodeURIComponent(o.engine);
    return `<a href="/src/engine/${e}/${e}.php?drawing_id=${o.drawing_id}" target="_blank" rel="noopener" class="ord-drawing-link">엔진에서 도면 열기 ↗</a>`;
}

// 도면 최신 저장본의 예상가(params.estimatedPrice) — 주문 뒤 고객이 도면을 고쳤다면 주문 시점 판매가와 다르다
function renderDrawingEstimate(o, de) {
    if (!de || !(Number(de.price) > 0)) return '';
    const won = n => Math.round(Number(n)).toLocaleString('ko-KR') + '원';
    const base = JSON.parse(o.price_breakdown || 'null')?.total ?? o.estimated_price;
    const diff = Number(base) > 0 ? Math.round(de.price - base) : 0;
    return `<div class="ord-est-current">현재 도면 저장본 예상가 <strong>${won(de.price)}</strong>`
        + (diff ? ` <span class="ord-estimate-diff">(주문 시점보다 ${diff > 0 ? '+' : '−'}${Math.abs(diff).toLocaleString()}원)</span>` : ' <span class="ord-spec-sub">(주문 시점과 같음)</span>')
        + ` <span class="ord-spec-sub">· ${fmtOrderDatetime(de.saved_at)} 저장</span></div>`;
}

// 이 주문의 견적서 발행 이력 + 작성 버튼 — 작성·발행은 어드민 '견적서' 메뉴(src/admin/quotes.php)에서 한다
function renderQuotes(o, quotes) {
    const won = n => Math.round(Number(n)).toLocaleString('ko-KR') + '원';
    const list = quotes.length ? `<div class="ord-quote-list">${quotes.map((q, i) => `
        <div><a href="/src/quote/view.php?id=${q.id}" target="_blank" rel="noopener">${esc(q.quote_no)}</a>${i ? ' <span class="ord-spec-sub">(재발행됨)</span>' : ''}
        <span class="ord-spec-sub">· ${fmtOrderDatetime(q.issued_at)} · 공급가액 ${won(q.supply_amount)} / 합계 ${won(q.total_amount)} · ${q.emailed_at ? '발신 ' + fmtOrderDatetime(q.emailed_at) : '미발신'}</span></div>`).join('')}</div>`
        : '<div class="ord-detail-value" style="margin-bottom:10px;">아직 발행한 견적서가 없습니다.</div>';
    const btn = o.status === 'cancelled' ? '' :
        `<a class="adm-btn-save" style="display:inline-flex;align-items:center;text-decoration:none;" href="/src/admin/quotes.php?order_id=${o.id}">${quotes.length ? '견적서 재발행' : '견적서 작성'}</a>`;
    return list + btn;
}

function renderDetail(o, history = [], spec = null, wood = null, drawingEstimate = null, quotes = []) {
    const shipAddr = [o.ship_zipcode, o.ship_address, o.ship_address_detail].filter(Boolean).join(' ');
    const specHtml = renderSpec(spec, wood);
    const noSpec   = '<div class="ord-detail-value">도면 사양 기록이 없습니다 (구버전 주문).</div>';
    document.getElementById('admDetailContent').innerHTML = `
        ${ordGroup('drawing', '도면', `
            ${o.thumbnail ? `<img src="${o.thumbnail}" class="ord-thumb" alt="도면 썸네일">` : ''}
            <div class="ord-detail-value">${ENGINE_MAP[o.engine] || esc(o.engine)} — ${esc(o.title || '(제목 없음)')}${o.version_label ? ' (' + esc(o.version_label) + ')' : ''} ${drawingLink(o)}</div>
        `)}

        ${ordGroup('customer', '주문·고객', `
            <div class="ord-detail-grid">
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

                <div class="ord-detail-label">확정 가격</div>
                <div class="ord-detail-label">주문일시</div>
                <div class="ord-detail-value">${o.final_price ? Number(o.final_price).toLocaleString() + '원' : '미입력'}</div>
                <div class="ord-detail-value">${fmtOrderDatetime(o.created_at)}</div>

                <div class="ord-detail-label">최근 처리일시</div>
                <div class="ord-detail-label"></div>
                <div class="ord-detail-value">${fmtOrderDatetime(o.reviewed_at)}</div>
            </div>
        `)}

        ${ordGroup('size', '상세 크기', specHtml ? specHtml.size : noSpec)}
        ${ordGroup('finish', '마감', specHtml ? specHtml.finish : noSpec)}
        ${ordGroup('estimate', '예상 견적 <small>도면 화면 그대로 · 시점 고정</small>', renderPriceBreakdown(o.price_breakdown, o.created_at) + renderDrawingEstimate(o, drawingEstimate))}
        ${ordGroup('quotes', `견적서 <small>${quotes.length ? quotes.length + '건 발행' : '미발행'}</small>`, renderQuotes(o, quotes))}
        ${history.length ? ordGroup('history', `같은 도면의 이전 주문 <small>${history.length}건</small>`, renderHistory(history, o.engine), false) : ''}
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

document.getElementById('admModalOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

document.addEventListener('DOMContentLoaded', init);
window.addEventListener('pmokAuthChanged', init);
