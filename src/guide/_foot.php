<?php
// $guide_prev : ['href' => 'file.php', 'title' => '...'] | null
// $guide_next : ['href' => 'file.php', 'title' => '...'] | null
?>

<div class="guide-pager">
    <?php if (!empty($guide_prev)): ?>
    <a href="/src/guide/<?= $guide_prev['href'] ?>" class="guide-pager-btn prev">
        <span class="pager-label"><i class="bi bi-arrow-left"></i> 이전</span>
        <span class="pager-title"><?= htmlspecialchars($guide_prev['title']) ?></span>
    </a>
    <?php else: ?>
    <div class="guide-pager-spacer"></div>
    <?php endif; ?>

    <?php if (!empty($guide_next)): ?>
    <a href="/src/guide/<?= $guide_next['href'] ?>" class="guide-pager-btn next">
        <span class="pager-label">다음 <i class="bi bi-arrow-right"></i></span>
        <span class="pager-title"><?= htmlspecialchars($guide_next['title']) ?></span>
    </a>
    <?php endif; ?>
</div>

</div><!-- .guide-article -->
</main>
</div><!-- .guide-wrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="/src/js/visitor-log.js" defer></script>
<?php include __DIR__ . '/../components/auth_modal.php'; ?>
</body>
</html>
