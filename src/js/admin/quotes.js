// 어드민 '견적서' 메뉴 (src/admin/quotes.php) — API: src/api/admin/quotes.php
// 전역 스코프 공유: token()/esc()는 admin/common.js, ORDER_STATUS_LABELS/fmtOrderCode/fmtOrderDatetime은 order-status-labels.js — 재선언 금지
const QT_ENGINE_NAME = { square: '정자살', classic: '완자살', cross: '아자살', diamond: '완자문살', triangle: '세모솟을살', hexagon: '육모살', mondrian: '몬드리안' };
const QT_SUPPLIER_FIELDS = [
    ['name', '상호'], ['biz_no', '사업자등록번호'], ['ceo', '대표자'], ['phone', '전화'],
    ['address', '주소', true], ['biz_type', '업태'], ['biz_item', '종목'], ['email', '이메일'],
    ['bank', '입금계좌 (예: 농협 000-0000-0000-00 워크그룹 평목)', true],
];
const QT_CUSTOMER_FIELDS = [
    ['company', '회사명 (있으면 "귀중")'], ['name', '이름/담당자'], ['biz_no', '사업자등록번호'], ['phone', '연락처'],
    ['email', '이메일 (메일 받을 주소)'], ['address', '주소'],
];
const QT_QUICK_ROWS = [
    { name: '배송비', spec: '', qty: 1, unit_price: 0 },
    { name: '설치(시공)비', spec: '', qty: 1, unit_price: 0 },
    { name: '할인', spec: '', qty: 1, unit_price: 0 },
];

let qtPage = 1, qtSearchTimer = null, qtPickerTimer = null;
let qtDraft = null;     // 작성 중인 주문의 draft 응답
let qtItems = [];       // 편집 중인 품목 [{name, spec, qty, unit_price}]

async function qtApi(body) {
    const res = await fetch('/src/api/admin/quotes.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() },
        body: JSON.stringify(body),
    });
    const data = await res.json().catch(() => ({ error: '서버 응답을 읽지 못했습니다.' }));
    if (!res.ok) throw new Error(data.error || '오류가 발생했습니다.');
    return data;
}

const qtWon = n => Math.round(Number(n || 0)).toLocaleString('ko-KR');

async function qtInit() {
    const user = authGetUser();
    if (!user || user.role !== 's') { location.href = '/'; return; }
    document.getElementById('adminPage').style.display = '';
    await loadQuotes(1);
    const sp = new URLSearchParams(location.search);
    const orderId = parseInt(sp.get('order_id') || '0', 10);
    if (orderId) openEditor(orderId);
    else if (sp.get('base_no')) openEditor({ base_no: sp.get('base_no') });
}

/* ── 발행 목록 ── */
async function loadQuotes(page) {
    qtPage = page;
    let data;
    try {
        data = await qtApi({ action: 'list', page, q: document.getElementById('qtSearch').value.trim() });
    } catch (err) {
        document.getElementById('qtTbody').innerHTML = `<tr><td colspan="8" style="padding:40px;text-align:center;color:var(--danger);">${esc(err.message)}</td></tr>`;
        return;
    }
    renderSupplierForm(data.supplier);
    document.getElementById('qtTotal').textContent = '총 ' + data.total.toLocaleString() + '건';
    const tbody = document.getElementById('qtTbody');
    if (!data.quotes.length) {
        tbody.innerHTML = '<tr><td colspan="8" style="padding:40px;text-align:center;color:var(--text-3);">발행된 견적서가 없습니다. \'새 견적서\'로 시작하세요.</td></tr>';
    } else {
        tbody.innerHTML = data.quotes.map(q => {
            const old = Number(q.revision) < Number(q.latest_revision);
            return `<tr class="${old ? 'qt-row-old' : ''}">
                <td class="adm-id">${esc(q.quote_no)}${old ? ' <span class="qt-badge-old">재발행됨</span>' : ''}</td>
                <td style="color:var(--text-3);font-size:12px;">${fmtOrderDatetime(q.issued_at)}</td>
                <td>${esc(q.company_name || q.customer_name)}${q.company_name ? `<br><span class="qt-muted">${esc(q.customer_name)}</span>` : ''}</td>
                <td>${q.order_id
                    ? `${esc(QT_ENGINE_NAME[q.engine] || q.engine)}${q.title ? ' · ' + esc(q.title) : ''}<br><span class="qt-muted">${fmtOrderCode(q.engine, q.order_id)} · ${esc(ORDER_STATUS_LABELS[q.order_status]?.label || q.order_status)}</span>`
                    : `${q.title ? esc(q.title) : '<span class="qt-muted">(건명 없음)</span>'}<br><span class="qt-muted">직접 작성 (견적요청 없음)</span>`}</td>
                <td style="text-align:right;">${qtWon(q.supply_amount)}</td>
                <td style="text-align:right;font-weight:600;">${qtWon(q.total_amount)}</td>
                <td style="font-size:12px;">${q.emailed_at ? `<span title="${esc(q.emailed_to)}">${fmtOrderDatetime(q.emailed_at)}</span>` : '<span class="qt-badge-old">미발신</span>'}</td>
                <td><div class="adm-action-cell">
                    <a class="adm-edit-btn" href="/src/quote/view.php?id=${q.id}" target="_blank" rel="noopener">보기·발신</a>
                    ${old ? '' : `<button class="adm-edit-btn" onclick='openEditor(${q.order_id ? Number(q.order_id) : JSON.stringify({ base_no: q.base_no }).replace(/'/g, '&#39;')})'>재발행</button>`}
                </div></td>
            </tr>`;
        }).join('');
    }
    qtRenderPagination(data.total, data.page, data.limit);
}

function qtRenderPagination(total, page, limit) {
    const pages = Math.ceil(total / limit), pg = document.getElementById('qtPagination');
    if (pages <= 1) { pg.innerHTML = ''; return; }
    let html = `<button class="adm-page-btn" onclick="loadQuotes(${page - 1})" ${page <= 1 ? 'disabled' : ''}><i class="bi bi-chevron-left"></i></button>`;
    for (let i = Math.max(1, page - 2); i <= Math.min(pages, page + 2); i++) {
        html += `<button class="adm-page-btn ${i === page ? 'active' : ''}" onclick="loadQuotes(${i})">${i}</button>`;
    }
    html += `<button class="adm-page-btn" onclick="loadQuotes(${page + 1})" ${page >= pages ? 'disabled' : ''}><i class="bi bi-chevron-right"></i></button>`;
    pg.innerHTML = html;
}

function onSearchInput() {
    clearTimeout(qtSearchTimer);
    qtSearchTimer = setTimeout(() => loadQuotes(1), 350);
}

/* ── 공급자 정보 ── */
function renderSupplierForm(s) {
    const box = document.getElementById('qtSupplierForm');
    if (box.dataset.ready) return; // 편집 중인 값을 목록 새로고침이 덮어쓰지 않도록 처음 한 번만
    box.dataset.ready = '1';
    box.innerHTML = QT_SUPPLIER_FIELDS.map(([k, label, wide]) =>
        `<div class="adm-mfield${wide ? ' qt-wide' : ''}"><label>${label}</label><input type="text" data-sup="${k}" value="${esc(s?.[k] || '')}" maxlength="200"></div>`
    ).join('');
}

async function saveSupplier() {
    const supplier = {};
    document.querySelectorAll('[data-sup]').forEach(el => { supplier[el.dataset.sup] = el.value.trim(); });
    const msg = document.getElementById('qtSupplierMsg');
    try {
        await qtApi({ action: 'supplier', supplier });
        msg.textContent = '저장했습니다. 다음 발행분부터 반영됩니다.';
    } catch (err) { msg.textContent = err.message; }
}

/* ── 1단계: 주문 선택 ── */
function openPicker() {
    document.getElementById('qtPickerOverlay').classList.add('open');
    document.getElementById('qtPickerSearch').value = '';
    loadPicker();
    setTimeout(() => document.getElementById('qtPickerSearch').focus(), 50);
}
function closePicker() { document.getElementById('qtPickerOverlay').classList.remove('open'); }
function onPickerInput() {
    clearTimeout(qtPickerTimer);
    qtPickerTimer = setTimeout(loadPicker, 300);
}
async function loadPicker() {
    const list = document.getElementById('qtPickerList');
    list.innerHTML = '<p class="qt-muted">불러오는 중…</p>';
    try {
        const { orders } = await qtApi({ action: 'orders', q: document.getElementById('qtPickerSearch').value.trim() });
        if (!orders.length) { list.innerHTML = '<p class="qt-muted">해당하는 주문이 없습니다. (취소된 주문은 제외)</p>'; return; }
        list.innerHTML = orders.map(o => `
            <button class="qt-picker-item" onclick="closePicker(); openEditor(${o.id})">
                <span class="adm-id">${fmtOrderCode(o.engine, o.id)}</span>
                <span class="qt-picker-main">${esc(o.company_name || o.customer_name)} · ${esc(QT_ENGINE_NAME[o.engine] || o.engine)}${o.title ? ' · ' + esc(o.title) : ''}
                    <span class="qt-muted">${o.created_at.slice(0, 10)} · ${esc(ORDER_STATUS_LABELS[o.status]?.label || o.status)}</span></span>
                <span class="qt-picker-side">${o.latest_revision ? `<span class="qt-badge">${o.latest_revision}차 발행됨</span>` : '<span class="qt-muted">미발행</span>'}</span>
            </button>`).join('');
    } catch (err) { list.innerHTML = `<p style="color:var(--danger);">${esc(err.message)}</p>`; }
}

/* ── 2단계: 작성 ── */
// target: 주문 id(숫자) 또는 {order_id} / {base_no}(직접 작성 견적서 재발행) / {}(견적요청 없이 직접 작성)
async function openEditor(target) {
    const req = typeof target === 'number' ? { order_id: target } : (target || {});
    const ov = document.getElementById('qtEditorOverlay');
    ov.classList.add('open');
    qtHideAlert();
    document.getElementById('qtOrderSummary').innerHTML = '<p class="qt-muted">불러오는 중…</p>';
    try {
        qtDraft = await qtApi({ action: 'draft', ...req });
    } catch (err) {
        qtShowAlert(err.message);
        document.getElementById('qtOrderSummary').innerHTML = '';
        return;
    }
    const d = qtDraft, o = d.order;
    const label = o ? o.code : (d.base_no || '직접 작성');
    document.getElementById('qtEditorTitle').textContent = `${label} 견적서 ${d.next_revision > 1 ? d.next_revision + '차 재발행' : '작성'}`;
    document.getElementById('qtTitle').value = d.title || '';

    const won = n => n ? qtWon(n) + '원' : '—';
    document.getElementById('qtOrderSummary').innerHTML = !o
        ? `<div class="qt-order qt-muted">견적요청(주문) 없이 직접 작성하는 견적서입니다. 수신자와 품목을 입력하세요.${d.base_no ? ` 번호: <strong>${esc(d.base_no)}-Q${d.next_revision}</strong>` : ' 번호는 발행할 때 PM-날짜-순번으로 붙습니다.'}</div>`
        : `
        <div class="qt-order">
            ${o.thumbnail ? `<img src="${o.thumbnail}" class="qt-order-thumb" alt="">` : ''}
            <div class="qt-order-body">
                <div><strong>${esc(QT_ENGINE_NAME[o.engine] || o.engine)} · ${esc(o.title || '(제목 없음)')}</strong>${o.version_label ? ' (' + esc(o.version_label) + ')' : ''}
                    <span class="qt-muted">· ${esc(ORDER_STATUS_LABELS[o.status]?.label || o.status)} · 주문 ${o.created_at.slice(0, 10)}${o.due_date ? ' · 납기희망 ' + o.due_date : ''}</span></div>
                <div class="qt-muted">주문 시점 예상가 ${won(o.estimated_price)} · 현재 확정 가격 ${won(o.final_price)}</div>
                ${o.memo ? `<div class="qt-note"><strong>고객 요청사항</strong> ${esc(o.memo)}</div>` : ''}
                ${o.price_note ? `<div class="qt-note"><strong>가격 협의 메모(내부)</strong> ${esc(o.price_note)}</div>` : ''}
            </div>
        </div>`;

    document.getElementById('qtCustomerForm').innerHTML = QT_CUSTOMER_FIELDS.map(([k, label]) =>
        `<div class="adm-mfield"><label>${label}</label><input type="text" data-cus="${k}" value="${esc(d.customer[k] || '')}" maxlength="200"></div>`
    ).join('');

    qtItems = (d.items || []).map(it => ({ name: it.name || '', spec: it.spec || '', qty: it.qty ?? 1, unit_price: it.unit_price ?? 0 }));
    if (!qtItems.length) qtItems.push({ name: '', spec: '', qty: 1, unit_price: 0 });
    renderItems();
    document.getElementById('qtQuick').innerHTML = '빠른 추가: ' + QT_QUICK_ROWS.map((r, i) =>
        `<button class="qt-chip" onclick="addItem(${i})">${r.name}</button>`).join('');

    document.getElementById('qtValidUntil').value = d.valid_until || '';
    document.getElementById('qtLeadTime').value   = d.lead_time || '';
    document.getElementById('qtNote').value       = d.note || '';
    const canFinalize = !!o && ['pending_review', 'revision_requested', 'approved'].includes(o.status);
    document.getElementById('qtSetStatusRow').style.display = canFinalize ? '' : 'none';
    document.getElementById('qtSetStatus').checked = canFinalize;

    document.getElementById('qtHistory').innerHTML = d.history.length ? `
        <div class="ord-section-title">이전 발행 <small class="qt-muted">${d.history.length}건 · 작성 창은 최신 차수 내용으로 채워짐</small></div>
        <div class="qt-history">${d.history.map(h => `
            <div><a href="/src/quote/view.php?id=${h.id}" target="_blank" rel="noopener">${esc(h.quote_no)}</a>
            <span class="qt-muted">${fmtOrderDatetime(h.issued_at)} · 합계 ${qtWon(h.total_amount)}원 · ${h.emailed_at ? '발신 ' + fmtOrderDatetime(h.emailed_at) : '미발신'}</span></div>`).join('')}
        </div>` : '';
}

function closeEditor() {
    document.getElementById('qtEditorOverlay').classList.remove('open');
    qtDraft = null;
    const sp = new URLSearchParams(location.search);
    if (sp.has('order_id') || sp.has('base_no')) history.replaceState(null, '', location.pathname);
}

function renderItems() {
    document.getElementById('qtItems').innerHTML = qtItems.map((it, i) => `
        <tr>
            <td class="qt-muted" style="text-align:center;">${i + 1}</td>
            <td><input type="text" value="${esc(it.name)}" maxlength="100" oninput="qtSet(${i},'name',this.value)"></td>
            <td><input type="text" value="${esc(it.spec)}" maxlength="300" oninput="qtSet(${i},'spec',this.value)"></td>
            <td><input type="number" value="${esc(it.qty)}" min="0" step="1" style="text-align:right;" oninput="qtSet(${i},'qty',this.value)"></td>
            <td><input type="number" value="${esc(it.unit_price)}" step="1000" style="text-align:right;" oninput="qtSet(${i},'unit_price',this.value)"></td>
            <td class="qt-amount" id="qtAmt${i}"></td>
            <td><button class="qt-del" title="행 삭제" onclick="removeItem(${i})" ${qtItems.length <= 1 ? 'disabled' : ''}>&#x2715;</button></td>
        </tr>`).join('');
    qtRecalc();
}

function qtSet(i, key, val) { qtItems[i][key] = val; qtRecalc(); }
function addItem(quickIdx) {
    qtItems.push(quickIdx != null ? { ...QT_QUICK_ROWS[quickIdx] } : { name: '', spec: '', qty: 1, unit_price: 0 });
    renderItems();
    const rows = document.querySelectorAll('#qtItems tr');
    rows[rows.length - 1]?.querySelector(quickIdx != null ? 'td:nth-child(5) input' : 'input')?.focus();
}
function removeItem(i) { qtItems.splice(i, 1); renderItems(); }

// 서버(quote_normalize_items)와 같은 규칙: 금액=수량×단가 반올림, 세액=공급가액 10% 원 미만 절사
function qtRecalc() {
    let supply = 0;
    qtItems.forEach((it, i) => {
        const amt = Math.round((parseFloat(it.qty) || 0) * Math.round(parseFloat(it.unit_price) || 0));
        supply += amt;
        const el = document.getElementById('qtAmt' + i);
        if (el) el.textContent = qtWon(amt);
    });
    const vat = Math.floor(supply * 0.1);
    document.getElementById('qtTotals').innerHTML = `
        <div><span>공급가액</span><span>${qtWon(supply)}원</span></div>
        <div><span>세액 (10%)</span><span>${qtWon(vat)}원</span></div>
        <div class="qt-grand"><span>합계 (부가세 포함)</span><span>${qtWon(supply + vat)}원</span></div>`;
}

async function issueQuote() {
    if (!qtDraft) return;
    const customer = {};
    document.querySelectorAll('[data-cus]').forEach(el => { customer[el.dataset.cus] = el.value.trim(); });
    const setStatus = document.getElementById('qtSetStatusRow').style.display !== 'none' && document.getElementById('qtSetStatus').checked;
    const total = document.querySelector('#qtTotals .qt-grand span:last-child')?.textContent || '';
    const title = document.getElementById('qtTitle').value.trim();
    const label = qtDraft.order ? qtDraft.order.code : (qtDraft.base_no || '직접 작성');
    const msg = `${label} 견적서 ${qtDraft.next_revision}차를 발행(저장)합니다.${title ? '\n건명 ' + title : ''}\n합계 ${total}`
        + (setStatus ? "\n주문 상태를 '견적확정'으로 바꿉니다." : '')
        + '\n\n메일은 나가지 않습니다. 열리는 견적서 화면에서 검토 후 발신하세요.';
    if (!confirm(msg)) return;

    const btn = document.getElementById('qtIssueBtn');
    btn.disabled = true; btn.textContent = '발행 중…';
    qtHideAlert();
    try {
        const r = await qtApi({
            action: 'issue',
            order_id: qtDraft.order ? qtDraft.order.id : null,
            base_no: qtDraft.order ? null : qtDraft.base_no,
            title,
            items: qtItems,
            customer,
            valid_until: document.getElementById('qtValidUntil').value,
            lead_time: document.getElementById('qtLeadTime').value.trim(),
            note: document.getElementById('qtNote').value.trim(),
            set_status: setStatus,
        });
        // 검토·발신 화면으로 바로 이동 (새 탭은 await 뒤라 팝업 차단될 수 있어 같은 탭으로)
        location.href = '/src/quote/view.php?id=' + r.quote_id;
    } catch (err) {
        qtShowAlert(err.message);
        document.querySelector('#qtEditorOverlay .adm-modal-body').scrollTop = 0;
    } finally {
        btn.disabled = false; btn.textContent = '발행';
    }
}

function qtShowAlert(text) {
    const el = document.getElementById('qtEditorAlert');
    el.textContent = text; el.className = 'adm-alert adm-alert-error'; el.style.display = '';
}
function qtHideAlert() { document.getElementById('qtEditorAlert').style.display = 'none'; }

['qtPickerOverlay', 'qtEditorOverlay'].forEach(id => {
    document.getElementById(id).addEventListener('click', function (e) {
        if (e.target === this) (id === 'qtPickerOverlay' ? closePicker : closeEditor)();
    });
});

document.addEventListener('DOMContentLoaded', qtInit);
window.addEventListener('pmokAuthChanged', qtInit);
