// 주문 상태 한글 라벨 + 톤(색상 그룹) — 관리자(admin/orders.js)와 고객(mypage/dashboard.js)이 공유.
// src/lib/order_status.php의 ORDER_STATUSES와 값이 일치해야 한다.
// label은 window._t()로 감싸 en 모드(고객 화면)에서만 번역되고, 어드민(/src/admin/, /en/ 경로 없음)은 항상 한글 그대로.
const _t0 = typeof _t === 'function' ? _t : function (s) { return s; };
const ORDER_STATUS_LABELS = {
    pending_review:     { label: _t0('견적검토'), tone: 'wait' },
    revision_requested: { label: _t0('수정요청'), tone: 'alert' },
    approved:            { label: _t0('승인'),     tone: 'progress' },
    quote_finalized:     { label: _t0('견적확정'), tone: 'progress' },
    deposit_paid:         { label: _t0('입금완료'), tone: 'progress' },
    in_production:       { label: _t0('제작중'),   tone: 'progress' },
    production_done:     { label: _t0('제작완료'), tone: 'progress' },
    shipped:              { label: _t0('발송'),     tone: 'progress' },
    delivered:            { label: _t0('배송완료'), tone: 'done' },
    cancelled:            { label: _t0('취소'),     tone: 'done' },
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
