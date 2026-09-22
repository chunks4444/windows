<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/i18n.php';
?>
<!DOCTYPE html>
<html lang="<?= is_en() ? 'en' : 'ko' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../lib/meta.php'; ?>
    <?php meta_tags(); ?>
<?php css_tag('/src/css/dashboard.css'); ?>
    <?php css_tag('/src/css/profile.css'); ?>
    <script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js" defer></script>
    <?php include __DIR__ . '/../components/auth_guard.php'; ?>
</head>
<body>

<?php include __DIR__ . '/../components/nav.php'; ?>

<!-- 로그인 필요 -->
<div class="db-page" id="pageAuthWall" style="display:none;">
    <div class="db-auth-banner">
        <p><?= htmlspecialchars(t('db_auth_wall_company')) ?></p>
        <button class="db-auth-btn" data-bs-toggle="modal" data-bs-target="#authModal">
            <i class="bi bi-person-circle"></i> <?= htmlspecialchars(t('nav_login')) ?>
        </button>
    </div>
</div>

<!-- 회사정보 폼 -->
<div class="db-page" id="companyPage" style="display:none;">
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-building me-2"></i><?= htmlspecialchars(t('cp_title')) ?></h1>
    </div>

    <div class="pf-card">
        <div class="pf-section">
            <h2 class="pf-section-title"><?= htmlspecialchars(t('pf_basic_info')) ?></h2>
            <div class="pf-email-row">
                <span class="pf-label"><?= htmlspecialchars(t('auth_email')) ?></span>
                <span class="pf-email" id="cpEmail">—</span>
            </div>
        </div>

        <div class="pf-section">
            <h2 class="pf-section-title"><?= htmlspecialchars(t('cp_info')) ?></h2>

            <div id="cpAlert" class="pf-alert" style="display:none;"></div>

            <form id="cpForm" novalidate>
                <div class="pf-columns">
                    <!-- 왼쪽 -->
                    <div class="pf-col">
                        <div class="pf-row">
                            <div class="pf-field">
                                <label class="pf-label" for="cpName"><?= htmlspecialchars(t('cp_name')) ?></label>
                                <input id="cpName" name="company_name" type="text" class="pf-input"
                                       placeholder="<?= htmlspecialchars(t('cp_name_ph')) ?>" maxlength="100">
                            </div>
                            <div class="pf-field">
                                <label class="pf-label" for="cpBizNo"><?= htmlspecialchars(t('cp_biz_no')) ?></label>
                                <input id="cpBizNo" name="company_biz_no" type="text" class="pf-input"
                                       placeholder="000-00-00000" maxlength="20">
                            </div>
                        </div>
                        <div class="pf-row" style="margin-top:12px;">
                            <div class="pf-field">
                                <label class="pf-label" for="cpBizType"><?= htmlspecialchars(t('cp_biz_type')) ?></label>
                                <input id="cpBizType" name="company_biz_type" type="text" class="pf-input"
                                       placeholder="<?= htmlspecialchars(t('cp_biz_type_ph')) ?>" maxlength="100">
                            </div>
                            <div class="pf-field">
                                <label class="pf-label" for="cpBizCat"><?= htmlspecialchars(t('cp_biz_cat')) ?></label>
                                <input id="cpBizCat" name="company_biz_category" type="text" class="pf-input"
                                       placeholder="<?= htmlspecialchars(t('cp_biz_cat_ph')) ?>" maxlength="100">
                            </div>
                        </div>
                        <div class="pf-row" style="margin-top:12px;">
                            <div class="pf-field">
                                <label class="pf-label" for="cpCeo"><?= htmlspecialchars(t('cp_ceo')) ?></label>
                                <input id="cpCeo" name="company_ceo" type="text" class="pf-input"
                                       placeholder="<?= htmlspecialchars(t('pf_name_ph')) ?>" maxlength="100">
                            </div>
                            <div class="pf-field">
                                <label class="pf-label" for="cpPhone"><?= htmlspecialchars(t('cp_phone')) ?></label>
                                <input id="cpPhone" name="company_phone" type="tel" class="pf-input"
                                       placeholder="02-0000-0000" maxlength="30">
                            </div>
                        </div>
                    </div>

                    <!-- 오른쪽: 주소 -->
                    <div class="pf-col pf-col--address">
                        <div class="pf-field">
                            <label class="pf-label" for="cpZipcode"><?= htmlspecialchars(t('cp_address')) ?></label>
                            <div class="pf-zipcode-row">
                                <input id="cpZipcode" name="company_zipcode" type="text" class="pf-input pf-input--sm"
                                       placeholder="<?= htmlspecialchars(t('pf_zipcode_ph')) ?>" maxlength="6" readonly>
                                <button type="button" class="pf-btn-zip" onclick="openPostcode()"><?= htmlspecialchars(t('pf_zipcode_search')) ?></button>
                            </div>
                            <input id="cpAddress" name="company_address" type="text" class="pf-input"
                                   placeholder="<?= htmlspecialchars(t('pf_road_address_ph')) ?>" maxlength="255" readonly>
                            <input id="cpAddressDetail" name="company_address_detail" type="text" class="pf-input"
                                   placeholder="<?= htmlspecialchars(t('cp_address_detail_ph')) ?>" maxlength="100">
                        </div>
                    </div>
                </div>

                <div class="pf-actions">
                    <button type="submit" class="pf-btn-save" id="cpSaveBtn"><?= htmlspecialchars(t('pf_save')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="/src/js/company.js"></script>

<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
