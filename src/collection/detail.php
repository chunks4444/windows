<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/meta.php';
require_once __DIR__ . '/../lib/slug.php';
require_once __DIR__ . '/../lib/i18n.php';   // t() / term_short() / lang_href()

$slug = trim($_GET['slug'] ?? '');
$id   = (int)($_GET['id'] ?? 0);

$editorMap = [
    'classic'  => '/src/engine/classic/classic.php',
    'square'   => '/src/engine/square/square.php',
    'diamond'  => '/src/engine/diamond/diamond.php',
    'cross'    => '/src/engine/cross/cross.php',
    'triangle' => '/src/engine/triangle/triangle.php',
    'hexagon'  => '/src/engine/hexagon/hexagon.php',
];

$pattern = null;
try {
    $pdo = db();
    if ($slug !== '') {
        $stmt = $pdo->prepare('SELECT p.*, d.type AS engine FROM library_patterns p LEFT JOIN drawings d ON d.id = p.drawing_id WHERE p.slug=? AND p.is_active=1');
        $stmt->execute([$slug]);
        $pattern = $stmt->fetch();
    } elseif ($id) {
        $stmt = $pdo->prepare('SELECT p.*, d.type AS engine FROM library_patterns p LEFT JOIN drawings d ON d.id = p.drawing_id WHERE p.id=? AND p.is_active=1');
        $stmt->execute([$id]);
        $pattern = $stmt->fetch();
        if ($pattern && $pattern['slug']) { header('Location: /collection/detail?slug=' . rawurlencode($pattern['slug']), true, 301); exit; }
    }
    if ($pattern) {
        $kstmt = $pdo->prepare('SELECT keyword FROM library_keywords WHERE pattern_id=? ORDER BY id');
        $kstmt->execute([$pattern['id']]);
        $keywords = $kstmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        $keywords = [];
    }
} catch (Throwable $e) {
    $pattern = null;
    $keywords = [];
}
if (!$pattern) { header('Location: /collection/'); exit; }

$engineKey  = strtolower($pattern['engine'] ?? '');
$editorUrl  = isset($editorMap[$engineKey]) ? lang_href($editorMap[$engineKey]) : null;
// 키워드는 살 이름·공간 이름이라 용어집 번역이 있다 (짧은 형태로)
$keywordLabels = array_map('term_short', $keywords);
$metaDesc   = $keywordLabels ? implode(', ', $keywordLabels) . ' — ' . t('col_meta_suffix') : t('col_meta_default');
// og:image는 절대 URL이어야 카톡·페이스북 공유 카드가 정상 노출됨
$metaImage  = $pattern['image_path']
    ? (strpos($pattern['image_path'], 'http') === 0 ? $pattern['image_path'] : SITE_URL . $pattern['image_path'])
    : SITE_DEFAULT_IMAGE;
$shareUrl   = SITE_URL . lang_href('/collection/detail') . '?slug=' . rawurlencode($pattern['slug']);
$displayName = term_short(library_pattern_display_name($pattern['slug'], $pattern['name_ko']));
?>
<!DOCTYPE html>
<html lang="<?= is_en() ? 'en' : 'ko' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($displayName) ?> — <?= htmlspecialchars(t('col_title_suffix')) ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDesc) ?>">
    <meta name="robots" content="max-image-preview:large">
    <link rel="icon" type="image/svg+xml" href="/src/assets/favicon.svg">
    <link rel="alternate icon" href="/src/assets/favicon.png">
    <link rel="apple-touch-icon" href="/src/assets/apple-touch-icon.png">
    <link rel="canonical" href="<?= htmlspecialchars($shareUrl) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($displayName) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($metaImage) ?>">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php css_tag('/src/css/collection.css'); ?>
    <?php if ($kakaoJsKey = kakao_js_key()): ?>
    <script src="https://t1.kakaocdn.net/kakao_js_sdk/2.7.4/kakao.min.js"></script>
    <script>if (window.Kakao && !Kakao.isInitialized()) Kakao.init('<?= addslashes($kakaoJsKey) ?>');</script>
    <?php endif; ?>
</head>
<body>

<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="lib-main" style="max-width:640px;margin:0 auto;padding-top:2rem;">
    <a href="<?= lang_href('/collection/') ?>" style="display:inline-flex;align-items:center;gap:4px;font-size:13px;color:var(--text-muted);text-decoration:none;margin-bottom:16px;">
        <i class="bi bi-arrow-left"></i> <?= htmlspecialchars(t('nav_collection')) ?>
    </a>

    <div style="border-radius:12px;overflow:hidden;aspect-ratio:1/1;background:var(--bg);">
        <?php if ($pattern['image_path']): ?>
        <img src="<?= htmlspecialchars($pattern['image_path']) ?>" alt="<?= htmlspecialchars($displayName) ?>" style="width:100%;height:100%;object-fit:cover;">
        <?php endif; ?>
    </div>

    <h1 style="font-size:20px;font-weight:700;margin:16px 0 4px;"><?= htmlspecialchars($displayName) ?></h1>
    <?php if ($keywords): ?>
    <p style="font-size:13px;color:var(--text-muted);margin:0 0 20px;"><?= htmlspecialchars(implode(' · ', $keywordLabels)) ?></p>
    <?php endif; ?>

    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
        <?php if ($editorUrl && $pattern['drawing_id']): ?>
        <a href="<?= htmlspecialchars($editorUrl) ?>?drawing_id=<?= (int)$pattern['drawing_id'] ?>" class="lib-btn lib-btn-primary" onclick="return openCollectionEditor(event,'<?= htmlspecialchars($editorUrl, ENT_QUOTES) ?>?drawing_id=<?= (int)$pattern['drawing_id'] ?>')"><i class="bi bi-pencil"></i> <?= htmlspecialchars(t('col_open_studio')) ?></a>
        <?php endif; ?>
    </div>

    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <button id="btnShare" class="lib-icon-btn lib-share-btn" title="<?= htmlspecialchars(t('col_share')) ?>"><i class="bi bi-share"></i></button>
    </div>
</div>

<div id="libToast" class="lib-toast"></div>

<!-- 공유 모달 -->
<div id="libShareModal" class="bm-backdrop" style="display:none;">
    <div class="bm-modal">
        <div class="bm-header">
            <span class="bm-title"><?= htmlspecialchars(t('col_share')) ?></span>
            <button class="bm-close" id="libShareModalClose"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="lib-share-linkrow">
            <input type="text" id="libShareModalLink" readonly>
            <button type="button" id="libShareModalCopy"><?= htmlspecialchars(t('col_copy')) ?></button>
        </div>
        <div class="lib-share-channels">
            <button type="button" id="libShareModalKakao" title="<?= htmlspecialchars(t('col_share_kakao')) ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3C6.5 3 2 6.6 2 11c0 2.8 1.8 5.3 4.6 6.7-.2.7-.7 2.6-.8 3-.1.5.2.5.4.4.2-.1 2.6-1.8 3.6-2.5.7.1 1.4.2 2.2.2 5.5 0 10-3.6 10-8 0-4.4-4.5-7.8-10-7.8z"/></svg>
                <span><?= htmlspecialchars(t('col_kakao')) ?></span>
            </button>
            <button type="button" id="libShareModalFb" title="<?= htmlspecialchars(t('col_share_fb')) ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 21v-7.9h2.7l.4-3.1h-3.1V8.1c0-.9.3-1.5 1.6-1.5h1.7V3.8C15.9 3.7 14.8 3.6 13.6 3.6c-2.5 0-4.2 1.5-4.2 4.3v2.1H6.7v3.1h2.7V21h4.1z"/></svg>
                <span>FB</span>
            </button>
            <button type="button" id="libShareModalX" title="<?= htmlspecialchars(t('col_share_x')) ?>">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M18.9 3H22l-7.5 8.6L23 21h-6.9l-5.4-6.6L4.4 21H1.3l8-9.2L1 3h7l4.9 6.1L18.9 3zm-1.2 16h1.9L7.4 4.9H5.4L17.7 19z"/></svg>
                <span>X</span>
            </button>
            <button type="button" id="libShareModalThreads" title="<?= htmlspecialchars(t('col_share_threads')) ?>">
                <i class="bi bi-threads"></i>
                <span><?= htmlspecialchars(t('col_threads')) ?></span>
            </button>
        </div>
    </div>
</div>

<script src="/src/js/collection-share.js?v=<?= md5_file(__DIR__ . '/../js/collection-share.js') ?>"></script>
<script>
    document.getElementById('btnShare').addEventListener('click', () => {
        openCollectionShareModal(<?= json_encode($shareUrl) ?>, <?= json_encode($displayName) ?>, <?= json_encode($metaImage) ?>);
    });
</script>

<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
