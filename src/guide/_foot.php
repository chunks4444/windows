<?php
// $guide_prev : ['href' => 'file.php', 'title' => '...'] | null
// $guide_next : ['href' => 'file.php', 'title' => '...'] | null
// $current_engine : _head.php가 채움. 엔진 가이드일 때만 하단에 엔진 바로가기 버튼을 그린다.
?>

<?php if (!empty($current_engine)): ?>
<div class="guide-engine-cta">
    <span class="guide-engine-cta-icon"><?= $guideEngineIcons[$current_engine['key']] ?? '' ?></span>
    <p class="guide-engine-cta-text"><?= htmlspecialchars(sprintf(t('guide_engine_cta_text'), $current_engine['title'])) ?></p>
    <a href="<?= lang_href('/src/engine/' . $current_engine['key'] . '/' . $current_engine['key'] . '.php') ?>" class="guide-engine-cta-btn">
        <?= htmlspecialchars(sprintf(t('guide_engine_cta_btn'), $current_engine['title'])) ?> <i class="bi bi-arrow-right"></i>
    </a>
</div>
<?php endif; ?>

<div class="guide-pager">
    <?php if (!empty($guide_prev)): ?>
    <a href="<?= lang_href('/guide/' . basename($guide_prev['href'], '.php')) ?>" class="guide-pager-btn prev">
        <span class="pager-label"><i class="bi bi-arrow-left"></i> <?= htmlspecialchars(t('guide_pager_prev')) ?></span>
        <span class="pager-title"><?= htmlspecialchars($guide_prev['title']) ?></span>
    </a>
    <?php else: ?>
    <div class="guide-pager-spacer"></div>
    <?php endif; ?>

    <?php if (!empty($guide_next)): ?>
    <a href="<?= lang_href('/guide/' . basename($guide_next['href'], '.php')) ?>" class="guide-pager-btn next">
        <span class="pager-label"><?= htmlspecialchars(t('guide_pager_next')) ?> <i class="bi bi-arrow-right"></i></span>
        <span class="pager-title"><?= htmlspecialchars($guide_next['title']) ?></span>
    </a>
    <?php endif; ?>
</div>

</div><!-- .guide-article -->
</main>
</div><!-- .guide-wrap -->

<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
