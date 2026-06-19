<!-- 견적요청 모달 -->
<div class="pm-modal-backdrop" id="orderBackdrop" style="z-index:8800;">
    <div class="order-modal">
        <div class="order-modal-title">견적요청</div>
        <div class="order-modal-body">
            <div class="order-top-row">
                <div class="order-thumb-wrap">
                    <img id="orderThumbImg" class="order-thumb-img" alt="도면 미리보기">
                </div>
                <div class="order-top-info">
                    <div class="order-info-row">
                        <span class="order-info-label">요청일</span>
                        <span class="order-info-val" id="orderDate">—</span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">도면</span>
                        <span class="order-info-val" id="orderDrawingTitle">—</span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">버전</span>
                        <span class="order-info-val" id="orderDrawingVersion">—</span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">요청자</span>
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
                </div>
            </div>

            <hr class="order-modal-divider">

            <div class="order-grid-2">
                <div class="order-field">
                    <label class="order-field-label" for="orderDueDate">납기 희망일</label>
                    <input type="date" id="orderDueDate" class="rp-prompt">
                </div>
                <div class="order-field">
                    <label class="order-field-label" for="orderShipPhone">배송지 연락처</label>
                    <input type="tel" id="orderShipPhone" class="rp-prompt" placeholder="010-0000-0000">
                </div>
            </div>

            <div class="order-field">
                <label class="order-field-label">배송지</label>
                <div class="order-zip-row">
                    <input type="text" id="orderShipZipcode" class="rp-prompt" placeholder="우편번호" readonly>
                    <button type="button" id="orderZipSearchBtn" class="order-zip-btn">검색</button>
                </div>
                <input type="text" id="orderShipAddress" class="rp-prompt" placeholder="주소" readonly style="margin-top:6px;">
                <input type="text" id="orderShipAddressDetail" class="rp-prompt" placeholder="상세주소" style="margin-top:6px;">
            </div>

            <div class="order-field">
                <label class="order-field-label" for="orderMemo">요청사항</label>
                <textarea id="orderMemo" class="rp-prompt" placeholder="요청사항을 입력해주세요 (선택)" rows="5" maxlength="1000"></textarea>
            </div>
        </div>
        <div class="pm-modal-btns">
            <button class="pm-btn-cancel" id="orderCancelBtn">취소</button>
            <button class="pm-btn-ok" id="orderSubmitBtn">견적요청하기</button>
        </div>
    </div>
</div>

<script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js" defer></script>
