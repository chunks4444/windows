<?php require_once __DIR__ . '/../lib/i18n.php'; ?>
<footer class="global-footer">
    <div class="footer-links-left">
        <p class="footer-copy">© <?= date('Y') ?> <?= htmlspecialchars(t('footer_copy')) ?></p>
        <!-- 약관·방침 본문은 한국어 원문이 법적 기준이라 링크 이름만 번역하고 한글 페이지로 보낸다 -->
        <a href="/privacy/" class="footer-link"><?= htmlspecialchars(t('auth_privacy')) ?></a>
        <a href="/terms/" class="footer-link"><?= htmlspecialchars(t('auth_terms')) ?></a>
    </div>
    <div class="footer-cta-center">
        <p class="footer-cta-sub"><?= htmlspecialchars(t('footer_cta_sub')) ?></p>
        <a href="<?= lang_href('/company/') ?>#contact" class="footer-cta-link"><?= htmlspecialchars(t('footer_cta_link')) ?> <i class="bi bi-arrow-up-right"></i></a>
    </div>
    <div class="footer-links-right">
        <a href="https://pyeongmok.com" class="footer-link">pyeongmok.com</a>
    </div>
</footer>
