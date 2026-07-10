// 주문 상태 한글 라벨 + 톤(색상 그룹) — 관리자(admin/orders.js)와 고객(mypage/dashboard.js)이 공유.
// src/lib/order_status.php의 ORDER_STATUSES와 값이 일치해야 한다.
const ORDER_STATUS_LABELS = {
    pending_review:     { label: '견적검토', tone: 'wait' },
    revision_requested: { label: '수정요청', tone: 'alert' },
    approved:            { label: '승인',     tone: 'progress' },
    quote_finalized:     { label: '견적확정', tone: 'progress' },
    deposit_paid:         { label: '입금완료', tone: 'progress' },
    in_production:       { label: '제작중',   tone: 'progress' },
    production_done:     { label: '제작완료', tone: 'progress' },
    shipped:              { label: '발송',     tone: 'progress' },
    delivered:            { label: '배송완료', tone: 'done' },
    cancelled:            { label: '취소',     tone: 'done' },
};

function fmtOrderDatetime(dt) {
    if (!dt) return '—';
    return dt.slice(0, 16).replace('T', ' ');
}

// 도면번호(주문 코드) 표기: {엔진약어}-{order.id}, 예) CL-1024
const ENGINE_CODE = { classic: 'CL', square: 'SQ', cross: 'CR', diamond: 'DM', triangle: 'TR', hexagon: 'HX' };
function fmtOrderCode(engine, id) {
    return (ENGINE_CODE[engine] || 'PM') + '-' + id;
}
