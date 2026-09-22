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
<div class="db-page" id="profileAuthWall" style="display:none;">
    <div class="db-auth-banner">
        <p><?= htmlspecialchars(t('db_auth_wall_profile')) ?></p>
        <button class="db-auth-btn" data-bs-toggle="modal" data-bs-target="#authModal">
            <i class="bi bi-person-circle"></i> <?= htmlspecialchars(t('nav_login')) ?>
        </button>
    </div>
</div>

<!-- 프로필 폼 -->
<div class="db-page" id="profilePage" style="display:none;">
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-person me-2"></i><?= htmlspecialchars(t('pf_title')) ?></h1>
    </div>

    <div class="pf-card">
        <div class="pf-section">
            <h2 class="pf-section-title"><?= htmlspecialchars(t('pf_basic_info')) ?></h2>

            <div class="pf-email-row">
                <span class="pf-label"><?= htmlspecialchars(t('auth_email')) ?></span>
                <span class="pf-email" id="pfEmail">—</span>
                <span class="pf-role-badge" id="pfRoleBadge"></span>
            </div>
        </div>

        <div class="pf-section">
            <h2 class="pf-section-title"><?= htmlspecialchars(t('pf_pw_edit')) ?></h2>

            <div id="pfPwAlert" class="pf-alert" style="display:none;"></div>

            <form id="pfPwForm" novalidate>
                <div class="pf-columns">
                    <div class="pf-col">
                        <div class="pf-field">
                            <label class="pf-label" for="pfPwCurrent"><?= htmlspecialchars(t('pf_pw_current')) ?></label>
                            <input id="pfPwCurrent" type="password" class="pf-input pf-input--sm"
                                   placeholder="<?= htmlspecialchars(t('pf_pw_current')) ?>" autocomplete="current-password">
                        </div>
                    </div>
                    <div class="pf-col pf-col--address">
                        <div class="pf-field">
                            <label class="pf-label" for="pfPwNew"><?= htmlspecialchars(t('pf_pw_new')) ?></label>
                            <input id="pfPwNew" type="password" class="pf-input pf-input--sm"
                                   placeholder="<?= htmlspecialchars(t('auth_password_min')) ?>" autocomplete="new-password">
                        </div>
                        <div class="pf-field">
                            <label class="pf-label" for="pfPwConfirm"><?= htmlspecialchars(t('pf_pw_new_confirm')) ?></label>
                            <input id="pfPwConfirm" type="password" class="pf-input pf-input--sm"
                                   placeholder="<?= htmlspecialchars(t('pf_pw_confirm_ph')) ?>" autocomplete="new-password">
                        </div>
                    </div>
                </div>
                <div class="pf-actions">
                    <button type="submit" class="pf-btn-save" id="pfPwSaveBtn"><?= htmlspecialchars(t('pf_change')) ?></button>
                </div>
            </form>
        </div>

        <div class="pf-section">
            <h2 class="pf-section-title"><?= htmlspecialchars(t('pf_personal_info')) ?></h2>

            <div id="pfAlert" class="pf-alert" style="display:none;"></div>

            <form id="pfForm" novalidate>
                <div class="pf-columns">
                    <!-- 왼쪽: 기본 정보 -->
                    <div class="pf-col">
                        <div class="pf-field">
                            <label class="pf-label" for="pfName"><?= htmlspecialchars(t('ct_name')) ?></label>
                            <input id="pfName" name="name" type="text" class="pf-input pf-input--sm"
                                   placeholder="<?= htmlspecialchars(t('pf_name_ph')) ?>" maxlength="100">
                        </div>
                        <div class="pf-field">
                            <label class="pf-label" for="pfPhone"><?= htmlspecialchars(t('pf_phone')) ?></label>
                            <input id="pfPhone" name="phone" type="tel" class="pf-input pf-input--sm"
                                   placeholder="010-0000-0000" maxlength="30">
                        </div>
                    </div>

                    <!-- 오른쪽: 주소 -->
                    <div class="pf-col pf-col--address">
                        <div class="pf-field">
                            <label class="pf-label" for="pfZipcode"><?= htmlspecialchars(t('pf_address')) ?></label>
                            <div class="pf-zipcode-row">
                                <input id="pfZipcode" name="zipcode" type="text" class="pf-input pf-input--sm"
                                       placeholder="<?= htmlspecialchars(t('pf_zipcode_ph')) ?>" maxlength="6" readonly>
                                <button type="button" class="pf-btn-zip" onclick="openPostcode()"><?= htmlspecialchars(t('pf_zipcode_search')) ?></button>
                            </div>
                            <input id="pfAddress" name="address" type="text" class="pf-input"
                                   placeholder="<?= htmlspecialchars(t('pf_road_address_ph')) ?>" maxlength="255" readonly>
                            <input id="pfAddressDetail" name="address_detail" type="text" class="pf-input"
                                   placeholder="<?= htmlspecialchars(t('pf_address_detail_ph')) ?>" maxlength="100">
                        </div>
                    </div>
                </div>

                <div class="pf-actions">
                    <button type="submit" class="pf-btn-save" id="pfSaveBtn">
                        <?= htmlspecialchars(t('pf_save')) ?>
                    </button>
                </div>
            </form>
        </div>

        <div class="pf-section">
            <h2 class="pf-section-title"><?= htmlspecialchars(t('pf_legal')) ?></h2>
            <div class="pf-legal-links">
                <a href="/privacy/"><?= htmlspecialchars(t('auth_privacy')) ?></a>
                <a href="/terms/"><?= htmlspecialchars(t('auth_terms')) ?></a>
            </div>
        </div>
    </div>
</div>

<script src="/src/js/profile.js"></script>

<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
