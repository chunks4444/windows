<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/meta.php';

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
$editorUrl  = $editorMap[$engineKey] ?? null;
$metaDesc   = $keywords ? implode(', ', $keywords) . ' — 평목 스튜디오 컬렉션' : '평목 스튜디오에서 만든 창호 격자 패턴';
// og:image는 절대 URL이어야 카톡·페이스북 공유 카드가 정상 노출됨
$metaImage  = $pattern['image_path']
    ? (strpos($pattern['image_path'], 'http') === 0 ? $pattern['image_path'] : SITE_URL . $pattern['image_path'])
    : SITE_DEFAULT_IMAGE;
$shareUrl   = SITE_URL . '/collection/detail?slug=' . rawurlencode($pattern['slug']);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pattern['name_ko']) ?> — 평목 컬렉션</title>
    <meta name="description" content="<?= htmlspecialchars($metaDesc) ?>">
    <link rel="icon" type="image/png" href="/src/assets/favicon.png">
    <link rel="apple-touch-icon" href="/src/assets/apple-touch-icon.png">
    <link rel="canonical" href="<?= htmlspecialchars($shareUrl) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($pattern['name_ko']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($metaImage) ?>">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php css_tag('/src/css/collection.css'); ?>
</head>
<body>

<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="lib-main" style="max-width:640px;margin:0 auto;padding-top:2rem;">
    <a href="/collection/" style="display:inline-flex;align-items:center;gap:4px;font-size:13px;color:var(--text-muted);text-decoration:none;margin-bottom:16px;">
        <i class="bi bi-arrow-left"></i> 컬렉션
    </a>

    <div style="border-radius:12px;overflow:hidden;aspect-ratio:1/1;background:var(--bg);">
        <?php if ($pattern['image_path']): ?>
        <img src="<?= htmlspecialchars($pattern['image_path']) ?>" alt="<?= htmlspecialchars($pattern['name_ko']) ?>" style="width:100%;height:100%;object-fit:cover;">
        <?php endif; ?>
    </div>

    <h1 style="font-size:20px;font-weight:700;margin:16px 0 4px;"><?= htmlspecialchars($pattern['name_ko']) ?></h1>
    <?php if ($keywords): ?>
    <p style="font-size:13px;color:var(--text-muted);margin:0 0 20px;"><?= htmlspecialchars(implode(' · ', $keywords)) ?></p>
    <?php endif; ?>

    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
        <?php if ($editorUrl && $pattern['drawing_id']): ?>
        <a href="<?= htmlspecialchars($editorUrl) ?>?drawing_id=<?= (int)$pattern['drawing_id'] ?>" class="lib-btn lib-btn-primary"><i class="bi bi-pencil"></i> 스튜디오에서 열기</a>
        <?php endif; ?>
    </div>

    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <button id="btnShare" class="lib-icon-btn" title="공유하기"><i class="bi bi-share"></i></button>
        <a id="btnShareX" class="lib-icon-btn" href="#" target="_blank" rel="noopener" title="X에 공유"><i class="bi bi-twitter-x"></i></a>
        <a id="btnShareThreads" class="lib-icon-btn" href="#" target="_blank" rel="noopener" title="스레드에 공유"><i class="bi bi-threads"></i></a>
    </div>
</div>

<div id="libToast" class="lib-toast"></div>

<script>
(function () {
    const shareUrl   = <?= json_encode($shareUrl) ?>;
    const shareTitle = <?= json_encode($pattern['name_ko']) ?>;

    function showToast(msg) {
        const t = document.getElementById('libToast');
        t.textContent = msg;
        t.classList.add('visible');
        setTimeout(() => t.classList.remove('visible'), 2400);
    }

    document.getElementById('btnShare').addEventListener('click', async () => {
        if (navigator.share) {
            try { await navigator.share({ title: shareTitle, text: shareTitle, url: shareUrl }); }
            catch (e) { /* 사용자가 공유 취소한 경우 등 — 무시 */ }
            return;
        }
        try {
            await navigator.clipboard.writeText(shareUrl);
            showToast('링크가 복사되었습니다.');
        } catch (e) {
            showToast('링크 복사에 실패했습니다.');
        }
    });

    document.getElementById('btnShareX').href =
        'https://twitter.com/intent/tweet?url=' + encodeURIComponent(shareUrl) + '&text=' + encodeURIComponent(shareTitle);
    document.getElementById('btnShareThreads').href =
        'https://www.threads.net/intent/post?text=' + encodeURIComponent(shareTitle + ' ' + shareUrl);
})();
</script>

<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
