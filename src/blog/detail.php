<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/meta.php';

// 제목 개정/슬러그 길이 제한(20자→14자) 축소로 슬러그가 바뀐 글의 구주소 → 신주소 301 리다이렉트 (SEO 보존)
$legacySlugRedirects = [
    '벽사辟邪-1-인간의-안녕을-향한-거대한-투쟁' => '벽사辟邪-1-귀신을-발명한',
    // 2026-07-08 슬러그 길이 제한 20자 → 14자 축소에 따른 일괄 단축
    '공간-신분학-2-문짝-하나에-담긴-시대의-욕망' => '공간-신분학-2-문짝-하나',
    '표기와-의미-솟을창인가-소슬창인가-올바른-표기와-어원' => '표기와-의미-솟을창인가-소',
    '공간-신분학-1-조선이-비단-벽지와-이중문을-단속한-이유' => '공간-신분학-1-조선이-비',
    '벽사辟邪-1-귀신을-발명한-뇌' => '벽사辟邪-1-귀신을-발명한',
    '벽사辟邪-2-위태로운-선線-문짝-안전과-공포가-교차하는-경계' => '벽사辟邪-2-위태로운-선線',
    '벽사辟邪-3-요괴-도깨비-그리고-치우천황' => '벽사辟邪-3-요괴-도깨비',
    '벽사辟邪-4-사방에-깔린-결계-그리고-현대의-벽사' => '벽사辟邪-4-사방에-깔린',
    '한일-창호-5-한일-공간을-다루는-방식' => '한일-창호-5-한일-공간을',
    '한일-창호-4-문살의-여유로운-비례-vs-선과-면의-엄격한-분할' => '한일-창호-4-문살의-여유',
    '한일-창호-3-쿠미코組子에-대한-오해' => '한일-창호-3-쿠미코組子에',
    '한일-창호-2-한일-창호의-구조적-반전과-기후-환경' => '한일-창호-2-한일-창호의',
    '한일-창호-1-일본의-문을-열기-전-우리가-와비사비를-먼저-말해야-하는-이유' => '한일-창호-1-일본의-문을',
    '오늘도-짭니다-1-부처님-손바닥' => '오늘도-짭니다-1-부처님',
    '오늘도-짭니다-2-오늘도-짭니다' => '오늘도-짭니다-2-오늘도',
    '표기와-의미-2-도깨비는-뿔이-없다' => '표기와-의미-도깨비는-뿔이',
];

$id   = (int)($_GET['id'] ?? 0);
$slug = trim($_GET['slug'] ?? '');
if (isset($legacySlugRedirects[$slug])) {
    header('Location: /blog/' . rawurlencode($legacySlugRedirects[$slug]), true, 301);
    exit;
}
$post = null;
$prev = null;
$next = null;
$nextSeries     = null;
$seriesInfo     = null;
$seriesEpisodes = [];
try {
    $pdo = db();
    if ($slug !== '') {
        $stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE slug=? AND is_active=1');
        $stmt->execute([$slug]);
        $post = $stmt->fetch();
    } elseif ($id) {
        // 예전 ?id= 링크 호환 — 새 슬러그 주소로 301 리다이렉트 (SEO 중복 콘텐츠 방지)
        $stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE id=? AND is_active=1');
        $stmt->execute([$id]);
        $post = $stmt->fetch();
        if ($post) { header('Location: /blog/' . rawurlencode($post['slug']), true, 301); exit; }
    }
    if ($post && $post['series_id']) {
        // 시리즈 내 이전/다음 (시간순이 아니라 읽는 순서 기준)
        $prev = $pdo->prepare('SELECT id,title,slug FROM blog_posts WHERE is_active=1 AND series_id=? AND series_order<? ORDER BY series_order DESC LIMIT 1');
        $prev->execute([$post['series_id'], $post['series_order']]);
        $prev = $prev->fetch();

        $next = $pdo->prepare('SELECT id,title,slug FROM blog_posts WHERE is_active=1 AND series_id=? AND series_order>? ORDER BY series_order ASC LIMIT 1');
        $next->execute([$post['series_id'], $post['series_order']]);
        $next = $next->fetch();

        $si = $pdo->prepare('SELECT id,name,tagline,sort_order FROM blog_series WHERE id=?');
        $si->execute([$post['series_id']]);
        $seriesInfo = $si->fetch();

        $eps = $pdo->prepare('SELECT id,title,slug,series_order FROM blog_posts WHERE is_active=1 AND series_id=? ORDER BY series_order');
        $eps->execute([$post['series_id']]);
        $seriesEpisodes = $eps->fetchAll();

        if (!$next && $seriesInfo) {
            // 마지막 편이면 다음 시리즈의 1편으로 안내
            $ns = $pdo->prepare("
                SELECT p.id, p.title, p.slug, s.name AS series_name
                FROM blog_posts p JOIN blog_series s ON s.id = p.series_id
                WHERE p.is_active=1 AND p.series_order=1 AND s.sort_order > ?
                ORDER BY s.sort_order LIMIT 1
            ");
            $ns->execute([$seriesInfo['sort_order']]);
            $nextSeries = $ns->fetch();
        }
    } elseif ($post) {
        // 시리즈 미지정 글 — 기존 시간순 방식 유지
        $prev = $pdo->prepare('SELECT id,title,slug FROM blog_posts WHERE is_active=1 AND (sort_order < ? OR (sort_order=? AND id<?)) ORDER BY sort_order DESC, id DESC LIMIT 1');
        $prev->execute([$post['sort_order'], $post['sort_order'], $post['id']]);
        $prev = $prev->fetch();

        $next = $pdo->prepare('SELECT id,title,slug FROM blog_posts WHERE is_active=1 AND (sort_order > ? OR (sort_order=? AND id>?)) ORDER BY sort_order ASC, id ASC LIMIT 1');
        $next->execute([$post['sort_order'], $post['sort_order'], $post['id']]);
        $next = $next->fetch();
    }
} catch (Throwable $e) {
    $post = null;
}
if (!$post) { header('Location: /blog/'); exit; }

$engineLabels = [
    'classic'  => '정자살(Classic Lattice)',
    'square'   => '완자살(Square Lattice)',
    'cross'    => '교살(Cross Lattice)',
    'triangle' => '세모 솟을살(Triangle Lattice)',
    'diamond'  => '마름모살(Diamond Lattice)',
    'hexagon'  => '육모 솟을살(Hexagon Lattice)',
];

// 조회수 집계 — 방문자당 24시간에 1회만 카운트 (쿠키 기반 중복 방지)
$viewCookie = 'blog_view_' . $post['id'];
if (empty($_COOKIE[$viewCookie])) {
    try {
        $pdo->prepare('UPDATE blog_posts SET view_count = view_count + 1 WHERE id=?')->execute([$post['id']]);
    } catch (Throwable $e) {}
    setcookie($viewCookie, '1', time() + 86400, '/');
}

$metaDesc = $post['summary'] ?: mb_substr(strip_tags($post['content']), 0, 120);
// og:image는 절대 URL이어야 카톡·페이스북 공유 카드가 정상 노출됨 (thumbnail_url은 /uploads/... 상대경로로 저장됨)
$metaImage = $post['thumbnail_url']
    ? (strpos($post['thumbnail_url'], 'http') === 0 ? $post['thumbnail_url'] : SITE_URL . $post['thumbnail_url'])
    : SITE_DEFAULT_IMAGE;

// SEO 키워드: 글마다 DB에 별도로 입력받지 않고, 이미 있는 필드(제목·질문·시리즈명·연관엔진)에서
// 자동으로 뽑아서 "이 글 고유 문화 키워드"와 "평목 상품 브릿지 키워드"를 항상 함께 노출한다.
// 문화 콘텐츠로 유입된 검색엔진 트래픽이 제품 키워드와도 매칭되도록 하기 위함 — 새 글을 써도 자동 적용됨.
$metaKeywords = implode(', ', array_unique(array_filter([
    preg_replace('/^\[.*?\]\s*/', '', $post['title']),
    $seriesInfo['name'] ?? null,
    $post['question'] ?: null,
    ($post['related_engine'] && isset($engineLabels[$post['related_engine']])) ? $engineLabels[$post['related_engine']] : null,
    '평목', '전통창호 제작', '맞춤 창호 디자인', '격자무늬 창호',
])));
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> — 평목 공방 블로그</title>
    <meta name="description" content="<?= htmlspecialchars($metaDesc) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($metaKeywords) ?>">
    <?php require_once __DIR__ . '/../lib/meta.php'; ?>
    <link rel="icon" type="image/png" href="/src/assets/favicon.png">
    <link rel="apple-touch-icon" href="/src/assets/apple-touch-icon.png">
    <link rel="canonical" href="<?= htmlspecialchars(SITE_URL . '/blog/' . rawurlencode($post['slug'])) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($post['title']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($metaImage) ?>">
    <?php article_jsonld($post, SITE_URL . '/blog/' . rawurlencode($post['slug']), $metaImage, $metaDesc); ?>
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<?php css_tag('/src/css/blog-detail.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="bd-page">
    <article class="bd-article">

        <header class="bd-header">
            <a href="/blog/" class="bd-back">
                <svg width="13" height="13" viewBox="0 0 14 14" fill="none">
                    <path d="M9 2L4 7L9 12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                블로그
            </a>
            <h1 class="bd-title"><?= htmlspecialchars($post['title']) ?></h1>
            <time class="bd-date" datetime="<?= date('Y-m-d', strtotime($post['created_at'])) ?>">
                <?= date('Y.m.d', strtotime($post['created_at'])) ?>
            </time>
            <div class="bd-share-row">
                <button id="btnShare" type="button" class="bd-share-btn" title="공유하기"><i class="bi bi-share"></i></button>
                <a id="btnShareX" class="bd-share-btn" href="#" target="_blank" rel="noopener" title="X에 공유"><i class="bi bi-twitter-x"></i></a>
                <a id="btnShareThreads" class="bd-share-btn" href="#" target="_blank" rel="noopener" title="스레드에 공유"><i class="bi bi-threads"></i></a>
            </div>
        </header>

        <?php if ($seriesInfo): ?>
        <div class="bd-series-box">
            <p class="bd-series-box-label">이 시리즈 · <?= htmlspecialchars($seriesInfo['name']) ?></p>
            <?php if ($seriesInfo['tagline']): ?>
            <p class="bd-series-box-tagline">"<?= htmlspecialchars($seriesInfo['tagline']) ?>"</p>
            <?php endif; ?>
            <ol class="bd-series-box-list">
                <?php foreach ($seriesEpisodes as $ep): ?>
                <li class="<?= $ep['id'] === $post['id'] ? 'current' : '' ?>">
                    <?php if ($ep['id'] === $post['id']): ?>
                    <span><?= $ep['series_order'] ?>. <?= htmlspecialchars($ep['title']) ?></span>
                    <?php else: ?>
                    <a href="/blog/<?= rawurlencode($ep['slug']) ?>"><?= $ep['series_order'] ?>. <?= htmlspecialchars($ep['title']) ?></a>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ol>
        </div>
        <?php endif; ?>

        <hr class="bd-divider">

        <div class="bd-body"><?= $post['content'] ?></div>

        <?php if ($post['related_engine'] && isset($engineLabels[$post['related_engine']])): ?>
        <div class="bd-engine-box">
            <p class="bd-engine-box-title">이 살의 이야기, 직접 만들어보세요</p>
            <p class="bd-engine-box-desc">글에서 다룬 <?= htmlspecialchars($engineLabels[$post['related_engine']]) ?> 패턴을 스튜디오에서 바로 조작해볼 수 있습니다.</p>
            <a class="bd-engine-box-btn"
               href="/src/engine/<?= htmlspecialchars($post['related_engine']) ?>/<?= htmlspecialchars($post['related_engine']) ?>.php<?= $post['related_drawing_id'] ? '?drawing_id=' . (int)$post['related_drawing_id'] : '' ?>">
                <?= htmlspecialchars($engineLabels[$post['related_engine']]) ?> 스튜디오 열기 <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <?php endif; ?>

        <div class="bd-cta">
            <p class="bd-cta-title"><?= htmlspecialchars($post['cta_text'] ?: '평목 스튜디오의 다양한 패턴 디자인 보러가기') ?></p>
            <a href="/collection/" class="bd-cta-btn">컬렉션 보러가기 <i class="bi bi-arrow-right"></i></a>
        </div>

    </article>

    <?php if ($prev || $next || $nextSeries): ?>
    <nav class="bd-pager">
        <?php if ($prev): ?>
        <a class="bd-pager-link bd-pager-prev" href="/blog/<?= rawurlencode($prev['slug']) ?>">
            <span class="bd-pager-label">이전 편</span>
            <span class="bd-pager-title"><?= htmlspecialchars($prev['title']) ?></span>
        </a>
        <?php else: ?><span></span><?php endif; ?>
        <?php if ($next): ?>
        <a class="bd-pager-link bd-pager-next" href="/blog/<?= rawurlencode($next['slug']) ?>">
            <span class="bd-pager-label">다음 편</span>
            <span class="bd-pager-title"><?= htmlspecialchars($next['title']) ?></span>
        </a>
        <?php elseif ($nextSeries): ?>
        <a class="bd-pager-link bd-pager-next" href="/blog/<?= rawurlencode($nextSeries['slug']) ?>">
            <span class="bd-pager-label">다음 시리즈 · <?= htmlspecialchars($nextSeries['series_name']) ?> 1편</span>
            <span class="bd-pager-title"><?= htmlspecialchars($nextSeries['title']) ?></span>
        </a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
</div>

<div id="bdToast" class="bd-toast"></div>

<script>
(function () {
    const shareUrl   = <?= json_encode(SITE_URL . '/blog/' . rawurlencode($post['slug'])) ?>;
    const shareTitle = <?= json_encode($post['title']) ?>;

    function showToast(msg) {
        const t = document.getElementById('bdToast');
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
