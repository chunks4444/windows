<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/meta.php';
require_once __DIR__ . '/../lib/i18n.php';   // t() / term_short() / lang_href()

$engineLabels = [
    'classic'  => '세살',
    'square'   => '정자살',
    'cross'    => '빗살',
    'triangle' => '세모솟을살',
    'diamond'  => '격자빗살',
    'hexagon'  => '육모솟을살',
];

// URL엔 실제 저장 경로(/uploads/renders/{userId}/…) 대신, 파일명에 이미 박혀있는
// 랜덤 토큰만 노출한다 — 경로 구조를 감추면서도 추측 불가능성은 그대로 유지.
$token  = $_GET['r'] ?? '';
$render = null;
if (preg_match('/^\d+_[a-f0-9]{8}$/', $token)) {
    try {
        $stmt = db()->prepare("SELECT id, engine, filepath, created_at FROM renders WHERE filepath LIKE ? LIMIT 1");
        $stmt->execute(['%/' . $token . '.png']);
        $render = $stmt->fetch();
    } catch (Throwable $e) {
        $render = null;
    }
}

if (!$render) {
    http_response_code(404);
    $imageUrl = SITE_DEFAULT_IMAGE;
    $engineLabel = null;
} else {
    $imageUrl    = SITE_URL . htmlspecialchars($render['filepath'], ENT_QUOTES);
    // 살 이름은 용어집을 거친다. 공유 링크로 들어온 영문 사용자도 패턴 이름을 읽을 수 있어야 함
    $engineLabel = term_short($engineLabels[$render['engine']] ?? $render['engine']);
}
?>
<!DOCTYPE html>
<html lang="<?= is_en() ? 'en' : 'ko' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    meta_tags([
        'title'       => $render ? sprintf(t('rv_title'), $engineLabel) : t('rv_title_notfound'),
        'description' => $render ? sprintf(t('rv_desc'), $engineLabel) : t('rv_desc_notfound'),
        'image'       => $imageUrl,
    ]);
    ?>
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body { min-height: 100%; }
        body { display: flex; flex-direction: column; margin: 0; background: var(--bg, #E1DDD4); }
        .rv-page { flex: 1 1 auto; display: flex; align-items: center; justify-content: center; padding: 32px 20px; }
        .rv-wrap { max-width: 640px; width: 100%; text-align: center; }
        .rv-img { max-width: 100%; max-height: 62vh; border-radius: 12px; border: 1px solid var(--border, #D4D8DB); box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px; object-fit: contain; background: #fff; }
        .rv-label { font-size: 13px; font-weight: 700; color: var(--accent, #23262A); letter-spacing: -0.1px; margin-bottom: 6px; }
        .rv-sub { color: #888; margin-bottom: 22px; font-size: 14px; }
        .rv-btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; background: var(--accent, #23262A); color: #fff; border-radius: 999px; font-weight: 600; border: none; cursor: pointer; font-size: 15px; text-decoration: none; }
        .rv-btn:hover { opacity: 0.9; color: #fff; }
        .rv-empty { padding: 60px 20px; color: #888; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../components/nav.php'; ?>
    <div class="rv-page">
    <div class="rv-wrap">
        <?php if ($render): ?>
            <img class="rv-img" src="<?= $imageUrl ?>" alt="<?= htmlspecialchars(sprintf(t('rv_alt'), $engineLabel)) ?>">
            <p class="rv-label"><?= htmlspecialchars(sprintf(t('rv_label'), $engineLabel)) ?></p>
            <p class="rv-sub"><?= htmlspecialchars(t('rv_sub')) ?></p>
            <a class="rv-btn" href="<?= htmlspecialchars(lang_href('/src/engine/' . $render['engine'] . '/' . $render['engine'] . '.php')) ?>">
                <?= htmlspecialchars(sprintf(t('rv_cta'), $engineLabel)) ?> <i class="bi bi-arrow-right"></i>
            </a>
        <?php else: ?>
            <div class="rv-empty">
                <p><?= htmlspecialchars(t('rv_desc_notfound')) ?></p>
                <a class="rv-btn" href="<?= lang_href('/') ?>"><?= htmlspecialchars(t('rv_home')) ?></a>
            </div>
        <?php endif; ?>
    </div>
    </div>
    <?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
