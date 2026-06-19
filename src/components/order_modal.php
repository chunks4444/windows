<!-- 주문 모달 -->
<div class="pm-modal-backdrop" id="orderBackdrop" style="z-index:8800;">
    <div class="order-modal">
        <div class="order-modal-title">제작 주문</div>
        <div class="order-modal-body">
            <div class="order-info-row">
                <span class="order-info-label">주문자</span>
                <span class="order-info-val" id="orderCustName">—</span>
            </div>
            <div class="order-info-row">
                <span class="order-info-label">연락처</span>
                <span class="order-info-val" id="orderCustPhone">—</span>
            </div>
            <div class="order-info-row" id="orderCompanyRow" style="display:none;">
                <span class="order-info-label">회사명</span>
                <span class="order-info-val" id="orderCustCompany">—</span>
            </div>
            <div class="order-info-row">
                <span class="order-info-label">도면</span>
                <span class="order-info-val" id="orderDrawingTitle">—</span>
            </div>
            <textarea id="orderMemo" class="rp-prompt" placeholder="요청사항을 입력해주세요 (선택)" rows="3" maxlength="1000" style="margin-top:6px;"></textarea>
        </div>
        <div class="pm-modal-btns">
            <button class="pm-btn-cancel" id="orderCancelBtn">취소</button>
            <button class="pm-btn-ok" id="orderSubmitBtn">주문하기</button>
        </div>
    </div>
</div>
