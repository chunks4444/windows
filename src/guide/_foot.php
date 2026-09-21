<?php
// $guide_prev : ['href' => 'file.php', 'title' => '...'] | null
// $guide_next : ['href' => 'file.php', 'title' => '...'] | null
?>

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
