<?php
// 블로그 검색창 — 블로그 목록 상단·사이드바에서 include. 결과는 /blog/search?q=... (src/blog/search.php)
// include 전에 $bgSearchId(입력칸 id, 한 페이지에 두 개라 구분용)를 정해 둘 것.
$bgSearchId = $bgSearchId ?? 'bgSearch';
?>
<form class="bg-search" method="get" action="<?= lang_href('/blog/search') ?>" role="search">
    <i class="bi bi-search" aria-hidden="true"></i>
    <label class="visually-hidden" for="<?= $bgSearchId ?>"><?= htmlspecialchars(t('blog_search_ph')) ?></label>
    <input type="search" id="<?= $bgSearchId ?>" name="q" placeholder="<?= htmlspecialchars(t('blog_search_ph')) ?>" maxlength="50" minlength="2" required>
    <button type="submit"><?= htmlspecialchars(t('blog_search_btn')) ?></button>
</form>
