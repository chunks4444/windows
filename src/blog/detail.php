<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/meta.php';
require_once __DIR__ . '/../lib/jwt.php';

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
    $relatedDrawingJoin = 'SELECT p.*, lp.name_ko AS related_pattern_name, d.type AS related_drawing_engine
        FROM blog_posts p
        LEFT JOIN library_patterns lp ON lp.drawing_id = p.related_drawing_id AND lp.is_active = 1
        LEFT JOIN drawings d ON d.id = p.related_drawing_id';
    if ($slug !== '') {
        $stmt = $pdo->prepare($relatedDrawingJoin . ' WHERE p.slug=? AND p.is_active=1');
        $stmt->execute([$slug]);
        $post = $stmt->fetch();
    } elseif ($id) {
        // 예전 ?id= 링크 호환 — 새 슬러그 주소로 301 리다이렉트 (SEO 중복 콘텐츠 방지)
        $stmt = $pdo->prepare($relatedDrawingJoin . ' WHERE p.id=? AND p.is_active=1');
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

        $si = $pdo->prepare('SELECT id,name,tagline,sort_order,is_completed FROM blog_series WHERE id=?');
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

require_once __DIR__ . '/../lib/engine_icons.php';
$engineLabels = [];
foreach (ENGINE_LABELS as $engineKey => $engineLabel) {
    $engineLabels[$engineKey] = $engineLabel . '(' . ucfirst($engineKey) . ' Lattice)';
}

// 조회수 집계 — 방문자당 24시간에 1회만 카운트 (쿠키 기반 중복 방지), 관리자 본인 조회·봇은 제외
$viewCookie = 'blog_view_' . $post['id'];
$isAdminViewer = false;
$isSuperViewer = false; // 블로그 관리(어드민)는 role='s' 전용이라 편집 링크는 이 조건으로만 노출
try {
    $viewerPayload = jwt_from_request();
    $isAdminViewer = $viewerPayload && in_array($viewerPayload['role'] ?? '', ['s', 'm'], true);
    $isSuperViewer = $viewerPayload && ($viewerPayload['role'] ?? '') === 's';
} catch (Throwable $e) {}
$viewerUa = $_SERVER['HTTP_USER_AGENT'] ?? '';
$isBotViewer = $viewerUa === '' || preg_match(
    '/bot|crawl|spider|slurp|facebookexternalhit|preview|whatsapp|telegram|discordbot|' .
    'yeti|daumoa|petalbot|semrush|ahrefs|mj12bot|dotbot|python-requests|curl|wget|headless/i',
    $viewerUa
);
// UA 위장 크롤러 탐지: 같은 IP가 짧은 시간 안에 서로 다른 UA로 여러 번 찍히면
// (실제 사람은 한 세션 내내 브라우저가 바뀌지 않음) 봇으로 간주해 카운트에서 제외
$isUaRotationBot = false;
if (!$isAdminViewer && !$isBotViewer) {
    try {
        $viewerIp = '-';
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $k) {
            if (!empty($_SERVER[$k])) {
                $cand = trim(explode(',', $_SERVER[$k])[0]);
                if (filter_var($cand, FILTER_VALIDATE_IP)) { $viewerIp = $cand; break; }
            }
        }
        $viewerIpHash = substr(md5($viewerIp), 0, 8);
        $viewerUaHash = substr(md5($viewerUa), 0, 8);
        $otherUas = $pdo->prepare(
            'SELECT COUNT(DISTINCT ua_hash) FROM page_views
             WHERE ip_hash = ? AND ua_hash IS NOT NULL AND ua_hash <> ?
               AND visited_at > DATE_SUB(NOW(), INTERVAL 30 SECOND)'
        );
        $otherUas->execute([$viewerIpHash, $viewerUaHash]);
        $isUaRotationBot = (int)$otherUas->fetchColumn() >= 1;
    } catch (Throwable $e) {}
}

// UA 지문 스크래퍼 탐지: 로테이팅 프록시로 IP만 바꿔가며 같은 UA로 같은 글을 반복 조회하는
// 패턴(예: 매번 다른 IP지만 전부 "macOS Chrome 142"로 동일) — IP당 1회뿐이라 위 로테이션
// 탐지로는 못 잡으므로, 같은 글에 같은 UA가 서로 다른 IP 3개 이상에서 찍히면 봇으로 간주
$isUaFingerprintBot = false;
if (!$isAdminViewer && !$isBotViewer && !$isUaRotationBot) {
    try {
        $currentPage = $_SERVER['REQUEST_URI'] ?? '';
        $fpStmt = $pdo->prepare(
            'SELECT COUNT(DISTINCT ip_hash) FROM page_views
             WHERE page = ? AND ua_hash = ?
               AND visited_at > DATE_SUB(NOW(), INTERVAL 1 DAY)'
        );
        $fpStmt->execute([$currentPage, $viewerUaHash]);
        $isUaFingerprintBot = (int)$fpStmt->fetchColumn() >= 10;
    } catch (Throwable $e) {}
}

if (!$isAdminViewer && !$isBotViewer && !$isUaRotationBot && !$isUaFingerprintBot && empty($_COOKIE[$viewCookie])) {
    try {
        $pdo->prepare('UPDATE blog_posts SET view_count = view_count + 1 WHERE id=?')->execute([$post['id']]);
    } catch (Throwable $e) {}
    setcookie($viewCookie, '1', time() + 86400, '/');
}

// summary가 비어 있으면 본문 첫 문단(<p>)을 메타 설명으로 씀 — 문단 경계 없이 전체를 자르면
// 여러 문단이 뒤섞여 문장이 끊길 수 있어 첫 문단만 뽑는다
$metaDesc = $post['summary'];
if (!$metaDesc) {
    $metaDesc = '';
    if (preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $post['content'], $bdParaMatches)) {
        foreach ($bdParaMatches[1] as $bdPara) {
            // 문단 전체가 <strong>...</strong>로만 감싸진 경우는 소제목이라 건너뜀
            if (preg_match('/^<strong[^>]*>.*<\/strong>$/is', trim($bdPara))) continue;
            $bdParaText = trim(html_entity_decode(strip_tags($bdPara), ENT_QUOTES, 'UTF-8'));
            if ($bdParaText !== '') { $metaDesc = $bdParaText; break; }
        }
    }
    if ($metaDesc === '') $metaDesc = strip_tags($post['content']);
    $metaDesc = mb_substr($metaDesc, 0, 120);
}
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
    <meta name="robots" content="max-image-preview:large">
    <?php require_once __DIR__ . '/../lib/meta.php'; ?>
    <link rel="icon" type="image/svg+xml" href="/src/assets/favicon.svg">
    <link rel="alternate icon" href="/src/assets/favicon.png">
    <link rel="apple-touch-icon" href="/src/assets/apple-touch-icon.png">
    <link rel="canonical" href="<?= htmlspecialchars(SITE_URL . '/blog/' . rawurlencode($post['slug'])) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($post['title']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($metaImage) ?>">
    <?php article_jsonld($post, SITE_URL . '/blog/' . rawurlencode($post['slug']), $metaImage, $metaDesc); ?>
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php if ($kakaoJsKey = kakao_js_key()): ?>
    <script src="https://t1.kakaocdn.net/kakao_js_sdk/2.7.4/kakao.min.js"></script>
    <script>if (window.Kakao && !Kakao.isInitialized()) Kakao.init('<?= addslashes($kakaoJsKey) ?>');</script>
    <?php endif; ?>
<?php css_tag('/src/css/blog-detail.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="bd-page">
    <article class="bd-article">

        <header class="bd-header">
            <a href="/blog/" class="bd-back"><i class="bi bi-arrow-left"></i> 블로그</a>
            <h1 class="bd-title"><?= htmlspecialchars($post['title']) ?></h1>
            <time class="bd-date" datetime="<?= date('Y-m-d', strtotime($post['created_at'])) ?>">
                <?= date('Y.m.d', strtotime($post['created_at'])) ?>
            </time>
            <div class="bd-share-row">
                <button id="btnShare" type="button" class="bd-share-btn" title="공유하기"><i class="bi bi-share"></i></button>
                <button id="btnShareKakao" type="button" class="bd-share-btn" title="카카오톡 공유">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3C6.5 3 2 6.6 2 11c0 2.8 1.8 5.3 4.6 6.7-.2.7-.7 2.6-.8 3-.1.5.2.5.4.4.2-.1 2.6-1.8 3.6-2.5.7.1 1.4.2 2.2.2 5.5 0 10-3.6 10-8 0-4.4-4.5-7.8-10-7.8z"/></svg>
                </button>
                <button id="btnShareFb" type="button" class="bd-share-btn" title="페이스북 공유">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 21v-7.9h2.7l.4-3.1h-3.1V8.1c0-.9.3-1.5 1.6-1.5h1.7V3.8C15.9 3.7 14.8 3.6 13.6 3.6c-2.5 0-4.2 1.5-4.2 4.3v2.1H6.7v3.1h2.7V21h4.1z"/></svg>
                </button>
                <a id="btnShareX" class="bd-share-btn" href="#" target="_blank" rel="noopener" title="X에 공유"><i class="bi bi-twitter-x"></i></a>
                <a id="btnShareThreads" class="bd-share-btn" href="#" target="_blank" rel="noopener" title="스레드에 공유"><i class="bi bi-threads"></i></a>
                <?php if ($isSuperViewer): ?>
                <a class="bd-share-btn" href="/src/admin/blog.php?edit=<?= (int)$post['id'] ?>" title="이 글 편집"><i class="bi bi-pencil-fill"></i></a>
                <?php endif; ?>
            </div>
        </header>

        <?php if ($seriesInfo): ?>
        <div class="bd-series-box">
            <p class="bd-series-box-label">이 시리즈 · <?= htmlspecialchars($seriesInfo['name']) ?><?= $post['series_order'] ? ' · ' . (int)$post['series_order'] . '화' : '' ?> <span class="bd-series-box-total">(전체 <?= count($seriesEpisodes) ?>화)</span> <span class="bd-series-box-status <?= $seriesInfo['is_completed'] ? 'is-completed' : 'is-ongoing' ?>"><?= $seriesInfo['is_completed'] ? '완결' : '연재중' ?></span></p>
            <?php if ($seriesInfo['tagline']): ?>
            <p class="bd-series-box-tagline">"<?= htmlspecialchars($seriesInfo['tagline']) ?>"</p>
            <?php endif; ?>
            <ol class="bd-series-box-list">
                <?php foreach ($seriesEpisodes as $ep): ?>
                <li class="<?= $ep['id'] === $post['id'] ? 'current' : '' ?>">
                    <?php if ($ep['id'] === $post['id']): ?>
                    <span><?= $ep['series_order'] ?>화 <?= htmlspecialchars($ep['title']) ?></span>
                    <?php else: ?>
                    <a href="/blog/<?= rawurlencode($ep['slug']) ?>"><?= $ep['series_order'] ?>화 <?= htmlspecialchars($ep['title']) ?></a>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ol>
        </div>
        <?php endif; ?>

        <hr class="bd-divider">

        <div class="bd-body"><?= $post['content'] ?></div>

        <?php
        $bdSourceLines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $post['source_text'] ?? ''))));
        ?>
        <div class="bd-source-notice">
            <?php if (count($bdSourceLines) === 1): ?>
            <p class="bd-source-text">출처: <?= htmlspecialchars($bdSourceLines[0]) ?></p>
            <hr class="bd-divider bd-divider-source">
            <?php elseif (count($bdSourceLines) > 1): ?>
            <p class="bd-source-text">출처</p>
            <ul class="bd-source-list">
                <?php foreach ($bdSourceLines as $bdSourceLine): ?>
                <li><?= htmlspecialchars($bdSourceLine) ?></li>
                <?php endforeach; ?>
            </ul>
            <hr class="bd-divider bd-divider-source">
            <?php endif; ?>
            <p class="bd-source-license">평목 블로그의 글과 기록은 출처(pyeongmok.com)를 밝히고 자유롭게 인용 및 발췌하실 수 있습니다.</p>
        </div>

        <?php
        // 연관 엔진을 관리자가 비워두고 연관 도면만 지정한 경우, 도면 자체의 타입으로 보완
        $bdEngineKey = $post['related_engine'] ?: $post['related_drawing_engine'];
        ?>
        <?php if ($bdEngineKey && isset($engineLabels[$bdEngineKey])): ?>
        <div class="bd-engine-box">
            <p class="bd-engine-box-title">이 살의 이야기, 직접 만들어보세요</p>
            <p class="bd-engine-box-desc">글에서 다룬 <?= htmlspecialchars($engineLabels[$bdEngineKey]) ?><?= $post['related_pattern_name'] ? ' · ' . htmlspecialchars($post['related_pattern_name']) : '' ?> 패턴을 스튜디오에서 바로 조작해볼 수 있습니다.</p>
            <?php
            $bdEngineUrl = '/src/engine/' . $bdEngineKey . '/' . $bdEngineKey . '.php'
                . ($post['related_drawing_id'] ? '?drawing_id=' . (int)$post['related_drawing_id'] : '');
            ?>
            <a class="bd-engine-box-btn"
               href="<?= htmlspecialchars($bdEngineUrl) ?>"
               <?= $post['related_drawing_id'] ? "onclick=\"return openCollectionEditor(event,'" . htmlspecialchars($bdEngineUrl, ENT_QUOTES) . "')\"" : '' ?>>
                <?= htmlspecialchars($engineLabels[$bdEngineKey]) ?> 스튜디오 열기 <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <?php endif; ?>

        <div class="bd-cta">
            <p class="bd-cta-title"><?= htmlspecialchars($post['cta_text'] ?: '평목 스튜디오의 다양한 패턴 디자인 보러가기') ?></p>
            <a href="/collection/" class="bd-cta-btn">컬렉션 보러가기 <i class="bi bi-arrow-right"></i></a>
        </div>

    </article>

    <nav class="bd-pager">
        <div class="bd-pager-grid">
            <?php if ($prev): ?>
            <a class="bd-pager-link bd-pager-prev" href="/blog/<?= rawurlencode($prev['slug']) ?>">
                <span class="bd-pager-label">이전 편</span>
                <span class="bd-pager-title"><?= htmlspecialchars($prev['title']) ?></span>
            </a>
            <?php else: ?><span></span><?php endif; ?>
            <a href="/blog/" class="bd-pager-link bd-pager-list">
                <span class="bd-pager-label">블로그</span>
                <span class="bd-pager-title">전체 목록 보기</span>
            </a>
            <?php if ($next): ?>
            <a class="bd-pager-link bd-pager-next" href="/blog/<?= rawurlencode($next['slug']) ?>">
                <span class="bd-pager-label">다음 편</span>
                <span class="bd-pager-title"><?= htmlspecialchars($next['title']) ?></span>
            </a>
            <?php elseif ($nextSeries): ?>
            <a class="bd-pager-link bd-pager-next" href="/blog/<?= rawurlencode($nextSeries['slug']) ?>">
                <span class="bd-pager-label">다음 시리즈 · <?= htmlspecialchars($nextSeries['series_name']) ?> · 1화</span>
                <span class="bd-pager-title"><?= htmlspecialchars($nextSeries['title']) ?></span>
            </a>
            <?php else: ?><span></span><?php endif; ?>
        </div>
    </nav>
</div>

<div id="bdToast" class="bd-toast"></div>

<script>
(function () {
    const shareUrl   = <?= json_encode(SITE_URL . '/blog/' . rawurlencode($post['slug'])) ?>;
    const shareTitle = <?= json_encode($post['title']) ?>;
    const shareImage = <?= json_encode($metaImage) ?>;

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

    document.getElementById('btnShareKakao').addEventListener('click', () => {
        if (!window.Kakao?.isInitialized?.()) {
            navigator.clipboard?.writeText(shareUrl).then(() => showToast('링크가 복사되었습니다.'));
            return;
        }
        Kakao.Share.sendDefault({
            objectType: 'feed',
            content: {
                title: shareTitle,
                description: '평목 블로그에서 이 글을 확인해보세요.',
                imageUrl: shareImage,
                link: { mobileWebUrl: shareUrl, webUrl: shareUrl },
            },
        });
    });
    document.getElementById('btnShareFb').addEventListener('click', () => {
        window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(shareUrl), '_blank', 'noopener,width=600,height=500');
    });
})();
</script>

<script src="/src/js/collection-share.js?v=<?= md5_file(__DIR__ . '/../js/collection-share.js') ?>"></script>

<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
