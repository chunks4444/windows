<?php
// 사용법: include 전에 $blogEngineKey에 엔진 키(classic/square/cross/diamond/triangle/hexagon)를 설정.
// 해당 엔진을 다룬 블로그 글이 있으면 화면 좌하단에 작은 "이 살의 이야기" 링크를 띄운다.
if (!empty($blogEngineKey)) {
    $blogEngineLinkPost = null;
    try {
        require_once __DIR__ . '/../lib/db.php';
        $stmt = db()->prepare("SELECT title, slug FROM blog_posts WHERE is_active=1 AND related_engine=? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$blogEngineKey]);
        $blogEngineLinkPost = $stmt->fetch();
    } catch (Throwable $e) {
        $blogEngineLinkPost = null;
    }
    if ($blogEngineLinkPost):
?>
<a class="pm-engine-story-link" href="/src/blog/<?= rawurlencode($blogEngineLinkPost['slug']) ?>" title="<?= htmlspecialchars($blogEngineLinkPost['title']) ?>">
    <i class="bi bi-book"></i> 이 살의 이야기
</a>
<?php endif; } ?>
