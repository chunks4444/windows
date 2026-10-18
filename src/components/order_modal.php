<?php require_once __DIR__ . '/../lib/i18n.php'; // 문구 번역 te() ?>
<!-- 견적요청 모달 -->
<div class="pm-modal-backdrop" id="orderBackdrop" style="z-index:8800;">
    <div class="order-modal">
        <div class="order-modal-title"><?= te('견적요청') ?></div>
        <div class="order-modal-body">
            <div class="order-top-row">
                <div class="order-thumb-wrap">
                    <img id="orderThumbImg" class="order-thumb-img" alt="<?= te('도면 미리보기') ?>">
                </div>
                <div class="order-top-info">
                    <div class="order-info-row">
                        <span class="order-info-label"><?= te('요청일') ?></span>
                        <span class="order-info-val" id="orderDate">—</span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label"><?= te('도면') ?></span>
                        <span class="order-info-val" id="orderDrawingTitle">—</span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label"><?= te('버전') ?></span>
                        <span class="order-info-val" id="orderDrawingVersion">—</span>
                    </div>
                </div>
            </div>

            <hr class="order-modal-divider">

            <!-- 요청자 — 프로필에 이름·연락처가 없어도 여기서 바로 입력, 요청할 때 프로필에도 저장된다 -->
            <div class="order-grid-2">
                <div class="order-field">
                    <label class="order-field-label" for="orderCustName"><?= te('요청자') ?> <span class="order-req">*</span></label>
                    <input type="text" id="orderCustName" class="rp-prompt" placeholder="<?= htmlspecialchars(t('pf_name_ph')) ?>" maxlength="100">
                </div>
                <div class="order-field">
                    <label class="order-field-label" for="orderCustPhone"><?= te('연락처') ?> <span class="order-req">*</span></label>
                    <input type="tel" id="orderCustPhone" class="rp-prompt" placeholder="010-0000-0000" maxlength="30">
                </div>
            </div>

            <!-- 회사 정보 — 마이페이지 > 회사 정보와 같은 항목. 견적서에 들어가며, 요청할 때 회사 정보에도 저장된다 -->
            <details class="order-company" id="orderCompanyBox">
                <summary><?= te('회사 정보') ?> <small><?= te('선택 · 견적서에 반영') ?></small></summary>
                <div class="order-company-body">
                    <div class="order-grid-2">
                        <div class="order-field">
                            <label class="order-field-label" for="orderCoName"><?= htmlspecialchars(t('cp_name')) ?></label>
                            <input type="text" id="orderCoName" class="rp-prompt" placeholder="<?= htmlspecialchars(t('cp_name_ph')) ?>" maxlength="100">
                        </div>
                        <div class="order-field">
                            <label class="order-field-label" for="orderCoBizNo"><?= htmlspecialchars(t('cp_biz_no')) ?></label>
                            <input type="text" id="orderCoBizNo" class="rp-prompt" placeholder="000-00-00000" maxlength="20">
                        </div>
                        <div class="order-field">
                            <label class="order-field-label" for="orderCoBizType"><?= htmlspecialchars(t('cp_biz_type')) ?></label>
                            <input type="text" id="orderCoBizType" class="rp-prompt" placeholder="<?= htmlspecialchars(t('cp_biz_type_ph')) ?>" maxlength="100">
                        </div>
                        <div class="order-field">
                            <label class="order-field-label" for="orderCoBizCat"><?= htmlspecialchars(t('cp_biz_cat')) ?></label>
                            <input type="text" id="orderCoBizCat" class="rp-prompt" placeholder="<?= htmlspecialchars(t('cp_biz_cat_ph')) ?>" maxlength="100">
                        </div>
                        <div class="order-field">
                            <label class="order-field-label" for="orderCoCeo"><?= htmlspecialchars(t('cp_ceo')) ?></label>
                            <input type="text" id="orderCoCeo" class="rp-prompt" placeholder="<?= htmlspecialchars(t('pf_name_ph')) ?>" maxlength="100">
                        </div>
                        <div class="order-field">
                            <label class="order-field-label" for="orderCoPhone"><?= htmlspecialchars(t('cp_phone')) ?></label>
                            <input type="tel" id="orderCoPhone" class="rp-prompt" placeholder="02-0000-0000" maxlength="30">
                        </div>
                    </div>
                    <div class="order-field">
                        <label class="order-field-label"><?= htmlspecialchars(t('cp_address')) ?></label>
                        <div class="order-zip-row">
                            <input type="text" id="orderCoZipcode" class="rp-prompt" placeholder="<?= htmlspecialchars(t('pf_zipcode_ph')) ?>" readonly>
                            <button type="button" id="orderCoZipSearchBtn" class="order-zip-btn"><?= te('검색') ?></button>
                        </div>
                        <input type="text" id="orderCoAddress" class="rp-prompt" placeholder="<?= htmlspecialchars(t('pf_road_address_ph')) ?>" readonly style="margin-top:6px;">
                        <input type="text" id="orderCoAddressDetail" class="rp-prompt" placeholder="<?= htmlspecialchars(t('cp_address_detail_ph')) ?>" maxlength="100" style="margin-top:6px;">
                    </div>
                </div>
            </details>
            <div class="order-save-note"><?= te('입력한 요청자·회사 정보는 프로필에도 저장되어 다음 견적요청에 자동으로 채워집니다.') ?></div>

            <hr class="order-modal-divider">

            <div class="order-grid-2">
                <div class="order-field">
                    <label class="order-field-label" for="orderDueDate"><?= te('납기 희망일') ?></label>
                    <input type="date" id="orderDueDate" class="rp-prompt">
                </div>
                <div class="order-field">
                    <label class="order-field-label" for="orderShipPhone"><?= te('배송지 연락처') ?></label>
                    <input type="tel" id="orderShipPhone" class="rp-prompt" placeholder="010-0000-0000">
                </div>
            </div>

            <div class="order-field">
                <label class="order-field-label"><?= te('배송지') ?></label>
                <div class="order-zip-row">
                    <input type="text" id="orderShipZipcode" class="rp-prompt" placeholder="<?= te('우편번호') ?>" readonly>
                    <button type="button" id="orderZipSearchBtn" class="order-zip-btn"><?= te('검색') ?></button>
                </div>
                <input type="text" id="orderShipAddress" class="rp-prompt" placeholder="<?= te('주소') ?>" readonly style="margin-top:6px;">
                <input type="text" id="orderShipAddressDetail" class="rp-prompt" placeholder="<?= te('상세주소') ?>" style="margin-top:6px;">
            </div>

            <div class="order-field">
                <label class="order-field-label" for="orderMemo"><?= te('요청사항') ?></label>
                <textarea id="orderMemo" class="rp-prompt" placeholder="<?= te('요청사항을 입력해주세요 (선택)') ?>" rows="5" maxlength="1000"></textarea>
            </div>
        </div>
        <div class="pm-modal-btns">
            <button class="pm-btn-cancel" id="orderCancelBtn"><?= te('취소') ?></button>
            <button class="pm-btn-ok" id="orderSubmitBtn"><?= te('견적요청하기') ?></button>
        </div>
    </div>
</div>

<script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js" defer></script>
