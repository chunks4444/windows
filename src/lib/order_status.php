<?php
// 주문 처리 상태의 단일 소스. 새 상태를 추가/변경할 땐 이 파일만 고치면
// 어드민 검증(src/api/admin/orders.php)과 도면 잠금 판정(src/lib/drawing.php)에 함께 반영된다.
const ORDER_STATUSES = [
    'pending_review', 'revision_requested', 'approved', 'quote_finalized',
    'deposit_paid', 'in_production', 'production_done', 'shipped', 'delivered', 'cancelled',
];

// 도면을 잠그는(편집/삭제 불가) 상태. 수정요청/배송완료/취소 상태가 되면 다시 편집 가능해진다.
const ORDER_LOCKING_STATUSES = [
    'pending_review', 'approved', 'quote_finalized', 'deposit_paid',
    'in_production', 'production_done', 'shipped',
];
