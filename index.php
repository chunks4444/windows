<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/src/lib/db.php';
require_once __DIR__ . '/src/lib/slug.php';
require_once __DIR__ . '/src/lib/i18n.php';
try {
    $pdo        = db();
    $spaceCards = $pdo->query('SELECT label, image_url, collection_query FROM space_cards WHERE is_active=1 ORDER BY sort_order, id')->fetchAll();
    $faqs          = $pdo->query('SELECT * FROM faqs WHERE is_active=1 AND show_on_main=1 ORDER BY sort_order, id')->fetchAll();
    $faqVisible    = $pdo->query("SELECT value FROM site_config WHERE key_name='faq_section_visible'")->fetchColumn();
    $faqVisible    = ($faqVisible === false || $faqVisible !== '0');
} catch (Throwable $e) {
    $pdo        = null;
    $spaceCards = [];
    $faqs       = [];
    $faqVisible = true;
}
// 홈 AI 프롬프트 샘플 문구 (어드민 > AI 튜닝에서 편집, 값 없으면 기본값 사용)
// 이건 화면에 보여주는 글이 아니라 "누르면 그대로 AI에 보내지는 입력값"이라 단순 번역이 아니다 —
// en 모드에서는 영문 예시를 따로 둬야 영문 사용자가 누른 문장이 영문 그대로 AI에 전달된다.
// 어드민이 편집한 값은 site_config에 언어별 키로 나눠 저장한다.
$homeAiSampleDefaults = [
    '정자살 여닫이 2짝 900×2000',
    '완자살 미서기 3짝 1200×2100',
    '교살 여닫이 1짝 700×1800 흰색',
    '세모솟을살 미서기 2짝 1500×2200',
    '마름모살 여닫이 4짝 2000×2100',
    '육모솟을살 미서기 3짝 1800×2000',
    '완자살 작은 창 600×900',
    '정자살 넓은 통창 2400×2200',
    '교살 미닫이 2짝 어두운 원목톤',
    '마름모살 현관 중문 1000×2100',
];
$homeAiSampleDefaultsEn = [
    'Jeongja-sal, hinged, 2 panels, 900×2000',
    'Wanja-sal, sliding, 3 panels, 1200×2100',
    'Gyo-sal, hinged, 1 panel, 700×1800, white',
    'Semo-sotgeul-sal, sliding, 2 panels, 1500×2200',
    'Mareummo-sal, hinged, 4 panels, 2000×2100',
    'Yukmo-sotgeul-sal, sliding, 3 panels, 1800×2000',
    'Wanja-sal, small window, 600×900',
    'Jeongja-sal, wide picture window, 2400×2200',
    'Gyo-sal, sliding, 2 panels, dark wood tone',
    'Mareummo-sal, entry partition door, 1000×2100',
];
$homeAiSampleKey = is_en() ? 'home_ai_sample_prompts_en' : 'home_ai_sample_prompts';
try {
    $stmt = $pdo ? $pdo->prepare("SELECT value FROM site_config WHERE key_name=?") : null;
    if ($stmt) { $stmt->execute([$homeAiSampleKey]); $homeAiSamplesRaw = $stmt->fetchColumn(); } else { $homeAiSamplesRaw = false; }
    $homeAiSamples    = $homeAiSamplesRaw ? array_values(array_filter((array)json_decode($homeAiSamplesRaw, true))) : [];
} catch (Throwable $e) {
    $homeAiSamples = [];
}
if (!$homeAiSamples) $homeAiSamples = is_en() ? $homeAiSampleDefaultsEn : $homeAiSampleDefaults;
// 스튜디오 카드 (테이블 없으면 빈 배열)
try {
    $studioCards = $pdo ? $pdo->query('SELECT * FROM studio_cards WHERE is_active=1 ORDER BY sort_order, id')->fetchAll() : [];
} catch (Throwable $e) {
    $studioCards = [];
}
// 컬렉션 슬라이드 카드 (최근 등록 패턴) — 클릭 시 바로 엔진 에디터로 열림
try {
    $collectionEditorMap = [
        'classic'  => '/src/engine/classic/classic.php',
        'square'   => '/src/engine/square/square.php',
        'diamond'  => '/src/engine/diamond/diamond.php',
        'cross'    => '/src/engine/cross/cross.php',
        'triangle' => '/src/engine/triangle/triangle.php',
        'hexagon'  => '/src/engine/hexagon/hexagon.php',
    ];
    $collectionCards = $pdo ? $pdo->query(
        "SELECT p.slug, p.name_ko, p.image_path, p.drawing_id, d.type AS engine
         FROM library_patterns p
         LEFT JOIN drawings d ON d.id = p.drawing_id
         WHERE p.is_active = 1 AND p.image_path <> ''
         ORDER BY p.sort_order, p.id LIMIT 14"
    )->fetchAll() : [];
    foreach ($collectionCards as &$cc) {
        $engineKey  = strtolower($cc['engine'] ?? '');
        $editorUrl  = isset($collectionEditorMap[$engineKey]) ? lang_href($collectionEditorMap[$engineKey]) : null;
        $cc['is_editor_link'] = (bool)($editorUrl && $cc['drawing_id']);
        $cc['href'] = $cc['is_editor_link']
            ? $editorUrl . '?drawing_id=' . (int)$cc['drawing_id']
            : '/collection/detail?slug=' . urlencode($cc['slug']);
        $cc['display_name'] = library_pattern_display_name($cc['slug'], $cc['name_ko']);
    }
    unset($cc);
} catch (Throwable $e) {
    $collectionCards = [];
}
// 블로그 글 3개 — 블로그 메인 히어로와 동일하게, 관리자가 직접 고른 글만(is_featured), 날짜 무관
try {
    $latestPosts = $pdo ? $pdo->query(
        // series_name_en까지 같이 가져와야 db_field()가 en 모드에서 영문 시리즈명을 고를 수 있다
        "SELECT p.*, s.name AS series_name, s.name_en AS series_name_en
         FROM blog_posts p
         LEFT JOIN blog_series s ON s.id = p.series_id
         WHERE p.is_active=1 AND p.is_featured=1 ORDER BY p.sort_order, p.id LIMIT 3"
    )->fetchAll() : [];
} catch (Throwable $e) {
    $latestPosts = [];
}
// 블로그 시리즈 명제 배너 — 썸네일 카드보다 문장이 이 블로그의 자산이라 순환 인용 배너로 노출
try {
    $blogQuotes = $pdo ? $pdo->query("
        SELECT s.tagline, s.tagline_en, p.slug, s.name AS series_name, s.name_en AS series_name_en
        FROM blog_series s
        JOIN blog_posts p ON p.series_id = s.id AND p.series_order = 1 AND p.is_active = 1
        WHERE s.tagline <> '' AND s.show_on_home = 1
    ")->fetchAll() : [];
} catch (Throwable $e) {
    $blogQuotes = [];
}
$blogQuote = $blogQuotes ? $blogQuotes[array_rand($blogQuotes)] : null;
?>
<!DOCTYPE html>
<html lang="<?= is_en() ? 'en' : 'ko' ?>">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php require_once __DIR__ . '/src/lib/meta.php'; meta_tags(); organization_jsonld(); faq_jsonld($faqs); ?>
        <?php define('BOOTSTRAP_LOADED', true); ?>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="/src/css/index.css?v=<?= md5_file(__DIR__ . '/src/css/index.css') ?>">
    </head>
    <body>
        <?php include __DIR__ . '/src/components/nav.php'; ?>
        <div class="home-wrapper">
            <h1 class="visually-hidden"><?= htmlspecialchars(t('home_h1')) ?></h1>
            <p class="hero-top-copy"><?= htmlspecialchars(t('home_hero_top')) ?></p>
            <p class="hero-top-subcopy"><?= htmlspecialchars(t('home_hero_sub')) ?></p>
            <!-- AI 프롬프트 -->
            <div class="container">
                <div class="idx-ai-wrap">
                    <div class="idx-ai-bar">
                        <i class="bi bi-stars idx-ai-icon"></i>
                        <input type="text" id="idxAiInput" class="idx-ai-input" autocomplete="off"
                            placeholder="<?= htmlspecialchars(t('home_ai_placeholder')) ?>">
                        <button id="idxAiSend" class="idx-ai-btn"><?= htmlspecialchars(t('home_ai_send')) ?></button>
                    </div>
                    <div class="idx-ai-samples" id="idxAiSamples">
                        <div class="idx-ai-samples-list">
                            <?php foreach ($homeAiSamples as $homeAiSample): ?>
                            <button type="button" class="idx-ai-sample"><?= htmlspecialchars($homeAiSample) ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div id="idxAiResult" class="idx-ai-result" style="display:none;"></div>
                </div>
            </div>
            <!-- -->
            <div class="container">
                <?php
                $svgIcons = [
                    'classic' => '<svg width="120" height="120" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg"><rect class="pm-symbol-bar" x="148" y="204" width="384" height="46" rx="0"/><rect class="pm-symbol-bar" x="148" y="430" width="384" height="46" rx="0"/><rect class="pm-symbol-bar" x="148" y="148" width="46" height="384" rx="0"/><rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/><rect class="pm-symbol-bar" x="486" y="148" width="46" height="384" rx="0"/><rect class="pm-symbol-bar" x="100" y="204" width="48" height="46" rx="0"/><rect class="pm-symbol-bar" x="532" y="204" width="48" height="46" rx="0"/><rect class="pm-symbol-bar" x="100" y="430" width="48" height="46" rx="0"/><rect class="pm-symbol-bar" x="532" y="430" width="48" height="46" rx="0"/></svg>',
                    'square'  => '<svg width="120" height="120" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg"><rect class="pm-symbol-bar" x="148" y="204" width="384" height="46" rx="0"/><rect class="pm-symbol-bar" x="148" y="430" width="384" height="46" rx="0"/><rect class="pm-symbol-bar" x="204" y="148" width="46" height="384" rx="0"/><rect class="pm-symbol-bar" x="430" y="148" width="46" height="384" rx="0"/></svg>',
                    'cross'   => '<svg width="120" height="120" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg"><g transform="rotate(45 340 340)"><rect class="pm-symbol-bar" x="148" y="204" width="384" height="46" rx="0"/><rect class="pm-symbol-bar" x="148" y="430" width="384" height="46" rx="0"/><rect class="pm-symbol-bar" x="204" y="148" width="46" height="384" rx="0"/><rect class="pm-symbol-bar" x="430" y="148" width="46" height="384" rx="0"/></g></svg>',
                    'triangle'=> '<svg width="120" height="120" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg"><rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/><g transform="rotate(60 340 340)"><rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/></g><g transform="rotate(120 340 340)"><rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/></g></svg>',
                    'diamond' => '<svg width="120" height="120" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg"><rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/><rect class="pm-symbol-bar" x="148" y="317" width="384" height="46" rx="0"/><g transform="rotate(45 340 340)"><rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/></g><g transform="rotate(135 340 340)"><rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/></g></svg>',
                    'hexagon' => '<svg width="120" height="120" viewBox="0 0 680 680" fill="none" xmlns="http://www.w3.org/2000/svg"><polyline points="210,265 340,190 470,265" stroke-width="46" stroke-linejoin="round" stroke-linecap="round" class="pm-symbol-stroke"/><line x1="210" y1="265" x2="210" y2="415" stroke-width="46" stroke-linecap="round" class="pm-symbol-stroke"/><line x1="470" y1="265" x2="470" y2="415" stroke-width="46" stroke-linecap="round" class="pm-symbol-stroke"/><line x1="210" y1="415" x2="340" y2="490" stroke-width="46" stroke-linecap="round" class="pm-symbol-stroke"/><line x1="470" y1="415" x2="340" y2="490" stroke-width="46" stroke-linecap="round" class="pm-symbol-stroke"/></svg>',
                ];
                // DB 데이터를 engine_key로 인덱싱
                $cardsByKey = [];
                foreach ($studioCards as $sc) $cardsByKey[$sc['engine_key']] = $sc;
                // 기본값 (DB 없을 때)
                $defaultCards = [
                    ['engine_key'=>'classic',  'title'=>'Classic Lattice',  'description'=>t('home_engine_desc_classic'),  'image_url'=>''],
                    ['engine_key'=>'square',   'title'=>'Square Lattice',   'description'=>t('home_engine_desc_square'),   'image_url'=>''],
                    ['engine_key'=>'cross',    'title'=>'Cross Lattice',    'description'=>t('home_engine_desc_cross'),    'image_url'=>''],
                    ['engine_key'=>'triangle', 'title'=>'Triangle Lattice', 'description'=>t('home_engine_desc_triangle'), 'image_url'=>''],
                    ['engine_key'=>'diamond',  'title'=>'Diamond Lattice',  'description'=>t('home_engine_desc_diamond'),  'image_url'=>''],
                    ['engine_key'=>'hexagon',  'title'=>'Hexagon Lattice',  'description'=>t('home_engine_desc_hexagon'),  'image_url'=>''],
                ];
                $renderCards = !empty($studioCards) ? $studioCards : $defaultCards;
                ?>
                <div class="engine-icon-row">
                <?php foreach ($renderCards as $sc):
                    $key   = $sc['engine_key'];
                    $title = (is_en() && !empty($sc['title_en'])) ? $sc['title_en'] : $sc['title'];
                ?>
                    <a href="/src/engine/<?= htmlspecialchars($key) ?>/<?= htmlspecialchars($key) ?>.php" class="engine-icon-shortcut" aria-label="<?= htmlspecialchars(sprintf(t('home_engine_alt'), $title)) ?>">
                        <span class="engine-icon-circle"><?= $svgIcons[$key] ?? '' ?></span>
                        <span class="engine-icon-label"><?= htmlspecialchars($title) ?></span>
                    </a>
                <?php endforeach; ?>
                </div>
            <!-- card -->
            <?php if (!empty($collectionCards)): ?>
            <section class="collection-strip-section">
                <div class="collection-strip-header mb-4 d-flex align-items-end justify-content-between flex-wrap gap-2">
                    <div>
                        <p class="ab-section-label">Collection</p>
                        <h2 class="ab-section-title"><?= htmlspecialchars(t('home_collection_title')) ?></h2>
                    </div>
                    <a href="/collection/" class="home-blog-more"><?= htmlspecialchars(t('home_collection_more')) ?> <i class="bi bi-arrow-right"></i></a>
                </div>
                <!-- Collection: 사이트 폭(container)에 맞춰 정렬 -->
                <div class="collection-strip-outer">
                    <button type="button" class="collection-strip-nav collection-strip-nav-prev" aria-label="<?= htmlspecialchars(t('home_collection_prev')) ?>"><i class="bi bi-chevron-left"></i></button>
                    <div class="collection-strip-viewport">
                        <div class="collection-strip-track" id="collectionStripTrack">
                            <?php
                            $collectionCols = array_chunk($collectionCards, 2);
                            foreach (array_merge($collectionCols, $collectionCols) as $col):
                            ?>
                            <div class="collection-strip-col">
                                <?php foreach ($col as $cc): ?>
                                <a class="collection-strip-card" href="<?= htmlspecialchars($cc['href']) ?>"<?= $cc['is_editor_link'] ? " onclick=\"return openCollectionEditor(event,'" . htmlspecialchars($cc['href'], ENT_QUOTES) . "')\"" : '' ?>>
                                    <img src="<?= htmlspecialchars($cc['image_path']) ?>" alt="<?= htmlspecialchars($cc['display_name']) ?>" loading="lazy">
                                    <div class="collection-strip-label"><?= htmlspecialchars($cc['display_name']) ?></div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button type="button" class="collection-strip-nav collection-strip-nav-next" aria-label="<?= htmlspecialchars(t('home_collection_next')) ?>"><i class="bi bi-chevron-right"></i></button>
                </div>
            </section>
            <script>
            (function () {
                var track = document.getElementById('collectionStripTrack');
                if (!track) return;
                var outer    = track.closest('.collection-strip-outer');
                var viewport = outer.querySelector('.collection-strip-viewport');
                var prevBtn  = outer.querySelector('.collection-strip-nav-prev');
                var nextBtn  = outer.querySelector('.collection-strip-nav-next');
                var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                var gap = 16;
                var pos = 0, half = 0, step = 0, paused = false, autoTimer = null;
                var cols = track.querySelectorAll('.collection-strip-col');
                var totalCount = cols.length / 2; // 무한 루프용으로 두 벌 렌더링된 원본 컬럼 개수

                // 뷰포트 너비를 컬럼 최소 폭(160px) 기준으로 나눠 보여줄 컬럼 수를 정하고, 그만큼 등분해 컬럼 폭을 계산 (잘린 카드가 보이지 않게 함)
                var minCardWidth = 250;
                function measure() {
                    var vw = viewport.clientWidth;
                    var fit = Math.floor((vw + gap) / (minCardWidth + gap));
                    var count = Math.max(1, Math.min(fit, totalCount));
                    var cardWidth = (vw - (count - 1) * gap) / count;
                    cols.forEach(function (el) { el.style.width = cardWidth + 'px'; });
                    step = cardWidth + gap;
                    half = step * totalCount;
                    pos = Math.round(pos / step) * step;
                    apply(false);
                }

                function apply(withTransition) {
                    track.style.transition = withTransition && !reduceMotion ? 'transform 0.45s ease' : 'none';
                    track.style.transform = 'translateX(' + pos + 'px)';
                }

                function move(direction) {
                    pos -= direction * step;
                    if (pos <= -half) pos += half;
                    if (pos > 0) pos -= half;
                    apply(true);
                }

                function startAuto() {
                    stopAuto();
                    if (reduceMotion) return;
                    autoTimer = setInterval(function () {
                        if (!paused) move(1);
                    }, 3200);
                }
                function stopAuto() { if (autoTimer) clearInterval(autoTimer); }

                measure();
                window.addEventListener('resize', measure);
                startAuto();

                outer.addEventListener('mouseenter', function () { paused = true; });
                outer.addEventListener('mouseleave', function () { paused = false; });

                prevBtn.addEventListener('click', function () { move(-1); });
                nextBtn.addEventListener('click', function () { move(1); });
            })();
            </script>
            <?php endif; ?>
            <?php if (false): // 2026-07-23 메인 페이지 복잡도/큐레이션 품질 이슈로 임시 비활성화 ?>
            <section class="values-section mt-5 ">
                <!-- Curation Header -->
                <hr class="curation-divider">
                <div class="collection-strip-header mt-5 mb-4">
                    <div>
                        <p class="ab-section-label">큐레이션</p>
                        <h2 class="ab-section-title">공간별 창호 디자인을 찾아보세요.</h2>
                    </div>
                </div>
                <!-- Space Cards (DB) — 사이트 폭(container)에 맞춰 정렬 -->
                <?php if (!empty($spaceCards)): ?>
                <div class="space-cards-wrapper">
                    <?php foreach ($spaceCards as $sc): ?>
                    <a class="space-card" href="/collection/?q=<?= urlencode($sc['collection_query']) ?>">
                        <img src="<?= htmlspecialchars($sc['image_url']) ?>" alt="<?= htmlspecialchars($sc['label']) ?>">
                        <div class="space-card-overlay"><span class="space-card-label"><?= htmlspecialchars($sc['label']) ?></span></div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </section>
            <?php endif; ?>
            <!-- card -->
        </div>
        <!-- home-wrapper -->

        <div class="hc">
        <section class="hc-band">
          <div class="container">
            <p class="hc-label"><?= htmlspecialchars(t('home_usage2_label')) ?></p>
            <h2 class="hc-title-compact"><?= t('home_usage2_title') ?></h2>
            <p class="hc-sub"><?= htmlspecialchars(t('home_usage2_sub')) ?></p>
            <div class="hc-lines">
              <div class="hc-line">
                <h3><?= htmlspecialchars(t('home_line1_title')) ?></h3>
                <p><?= htmlspecialchars(t('home_line1_body')) ?></p>
              </div>
              <div class="hc-line">
                <h3><?= htmlspecialchars(t('home_line2_title')) ?></h3>
                <p><?= htmlspecialchars(t('home_line2_body')) ?></p>
              </div>
              <div class="hc-line">
                <h3><?= htmlspecialchars(t('home_line3_title')) ?></h3>
                <p><?= htmlspecialchars(t('home_line3_body')) ?></p>
              </div>
              <div class="hc-line">
                <h3><?= htmlspecialchars(t('home_line4_title')) ?></h3>
                <p><?= htmlspecialchars(t('home_line4_body')) ?></p>
              </div>
            </div>
          </div>
        </section>
        </div>

        <!-- Process -->
        <section class="process-section">
            <div class="container">
                <div class="mb-4 d-flex align-items-end justify-content-between flex-wrap gap-2">
                    <div>
                        <p class="ab-section-label"><?= htmlspecialchars(t('home_process_label')) ?></p>
                        <h2 class="ab-section-title"><?= htmlspecialchars(t('home_process_title')) ?></h2>
                        <p class="ab-section-body"><?= htmlspecialchars(t('home_process_body')) ?></p>
                    </div>
                    <a href="/guide/" class="home-blog-more"><?= htmlspecialchars(t('home_guide_more')) ?> <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="process-container">
                    <div class="process-step">
                        <div class="process-card-bg">
                            <span class="process-num">01</span>
                            <i class="bi bi-pencil-square process-icon"></i>
                        </div>
                        <div class="process-card-overlay">
                            <h3 class="process-title"><?= htmlspecialchars(t('home_step1_title')) ?></h3>
                            <p class="process-desc"><?= htmlspecialchars(t('home_step1_desc')) ?></p>
                            <ul class="process-hints">
                                <li><i class="bi bi-check2"></i> <?= htmlspecialchars(t('home_step1_hint1')) ?></li>
                                <li><i class="bi bi-check2"></i> <?= htmlspecialchars(t('home_step1_hint2')) ?></li>
                                <li><i class="bi bi-check2"></i> <?= htmlspecialchars(t('home_step1_hint3')) ?></li>
                            </ul>
                            <a href="/guide/studio-classic" class="process-guide-link"><?= htmlspecialchars(t('home_step1_link')) ?> <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="process-step">
                        <div class="process-card-bg">
                            <span class="process-num">02</span>
                            <i class="bi bi-bookmark-heart process-icon"></i>
                        </div>
                        <div class="process-card-overlay">
                            <h3 class="process-title"><?= htmlspecialchars(t('home_step2_title')) ?></h3>
                            <p class="process-desc"><?= htmlspecialchars(t('home_step2_desc')) ?></p>
                            <ul class="process-hints">
                                <li><i class="bi bi-check2"></i> <?= htmlspecialchars(t('home_step2_hint1')) ?></li>
                                <li><i class="bi bi-check2"></i> <?= htmlspecialchars(t('home_step2_hint2')) ?></li>
                                <li><i class="bi bi-check2"></i> <?= htmlspecialchars(t('home_step2_hint3')) ?></li>
                            </ul>
                            <a href="/guide/drawing" class="process-guide-link"><?= htmlspecialchars(t('home_step2_link')) ?> <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="process-step">
                        <div class="process-card-bg">
                            <span class="process-num">03</span>
                            <i class="bi bi-file-earmark-image process-icon"></i>
                        </div>
                        <div class="process-card-overlay">
                            <h3 class="process-title"><?= htmlspecialchars(t('home_step3_title')) ?></h3>
                            <p class="process-desc"><?= htmlspecialchars(t('home_step3_desc')) ?></p>
                            <ul class="process-hints">
                                <li><i class="bi bi-check2"></i> <?= htmlspecialchars(t('home_step3_hint1')) ?></li>
                                <li><i class="bi bi-check2"></i> <?= htmlspecialchars(t('home_step3_hint2')) ?></li>
                                <li><i class="bi bi-check2"></i> <?= htmlspecialchars(t('home_step3_hint3')) ?></li>
                                <li><i class="bi bi-check2"></i> <?= htmlspecialchars(t('home_step3_hint4')) ?></li>
                            </ul>
                            <a href="/guide/render" class="process-guide-link"><?= htmlspecialchars(t('home_step3_link')) ?> <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="process-step">
                        <div class="process-card-bg">
                            <span class="process-num">04</span>
                            <i class="bi bi-chat-heart process-icon"></i>
                        </div>
                        <div class="process-card-overlay">
                            <h3 class="process-title"><?= htmlspecialchars(t('home_step4_title')) ?></h3>
                            <p class="process-desc"><?= htmlspecialchars(t('home_step4_desc')) ?></p>
                            <ul class="process-hints">
                                <li><i class="bi bi-check2"></i> <?= htmlspecialchars(t('home_step4_hint1')) ?></li>
                                <li><i class="bi bi-check2"></i> <?= htmlspecialchars(t('home_step4_hint2')) ?></li>
                                <li><i class="bi bi-check2"></i> <?= htmlspecialchars(t('home_step4_hint3')) ?></li>
                            </ul>
                            <div class="process-cta-group">
                                <button type="button" class="process-cta-btn" data-bs-toggle="modal" data-bs-target="#contactModal">
                                    <i class="bi bi-envelope-fill"></i> <?= htmlspecialchars(t('home_step4_cta')) ?>
                                </button>
                                <a href="/guide/order" class="process-guide-link"><?= htmlspecialchars(t('home_step4_link')) ?> <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ (사용법 바로 아래, 리스트는 좌우 2열) -->
        <?php if ($faqVisible && !empty($faqs)):
            $faqHalf  = (int)ceil(count($faqs) / 2);
            $faqLeft  = array_slice($faqs, 0, $faqHalf);
            $faqRight = array_slice($faqs, $faqHalf);
        ?>
        <section class="faq-section">
            <div class="container">
                <div class="mb-4 d-flex align-items-end justify-content-between flex-wrap gap-2">
                    <div>
                        <p class="ab-section-label"><?= htmlspecialchars(t('home_faq_label')) ?></p>
                        <h2 class="ab-section-title"><?= htmlspecialchars(t('home_faq_title')) ?></h2>
                    </div>
                    <a href="/guide/faq" class="home-blog-more"><?= htmlspecialchars(t('home_faq_more')) ?> <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="faq-columns">
                    <div class="accordion faq-accordion" id="faqAccordionLeft">
                        <?php foreach ($faqLeft as $faq): ?>
                        <div class="accordion-item faq-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button faq-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $faq['id'] ?>">
                                    <?= htmlspecialchars(db_field($faq, 'question')) ?>
                                </button>
                            </h3>
                            <div id="faq<?= $faq['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#faqAccordionLeft">
                                <div class="accordion-body faq-body">
                                    <?= db_field($faq, 'answer') ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="accordion faq-accordion" id="faqAccordionRight">
                        <?php foreach ($faqRight as $faq): ?>
                        <div class="accordion-item faq-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button faq-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $faq['id'] ?>">
                                    <?= htmlspecialchars(db_field($faq, 'question')) ?>
                                </button>
                            </h3>
                            <div id="faq<?= $faq['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#faqAccordionRight">
                                <div class="accordion-body faq-body">
                                    <?= db_field($faq, 'answer') ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Blog -->
        <?php if ($latestPosts): ?>
        <section class="home-blog-section">
            <div class="container">
                <div class="mb-4 d-flex align-items-end justify-content-between flex-wrap gap-2">
                    <div>
                        <p class="ab-section-label"><?= htmlspecialchars(t('home_blog_label')) ?></p>
                        <h2 class="ab-section-title"><?= htmlspecialchars(t('home_blog_title')) ?></h2>
                        <p class="ab-section-body"><?= htmlspecialchars(t('home_blog_body')) ?></p>
                    </div>
                    <a href="<?= lang_href('/blog/') ?>" class="home-blog-more"><?= htmlspecialchars(t('home_blog_more')) ?> <i class="bi bi-arrow-right"></i></a>
                </div>
                <?php if ($blogQuote): ?>
                <a href="<?= lang_href('/blog/' . rawurlencode($blogQuote['slug'])) ?>" class="home-quote-banner">
                    <p class="home-quote-text">"<?= htmlspecialchars(db_field($blogQuote, 'tagline')) ?>"</p>
                    <p class="home-quote-sub"><?= htmlspecialchars(db_field($blogQuote, 'series_name')) ?> · <?= htmlspecialchars(sprintf(t('home_blog_episode'), 1)) ?> <?= htmlspecialchars(t('home_blog_quote_read')) ?> <i class="bi bi-arrow-right"></i></p>
                </a>
                <?php endif; ?>
                <div class="home-blog-grid">
                    <?php foreach ($latestPosts as $p): ?>
                    <a href="<?= lang_href('/blog/' . rawurlencode($p['slug'])) ?>" class="home-blog-card">
                        <?php if ($p['thumbnail_url']): ?>
                        <div class="home-blog-card-thumb">
                            <img src="<?= htmlspecialchars($p['thumbnail_url']) ?>" alt="<?= htmlspecialchars(db_field($p, 'title')) ?>" loading="lazy">
                        </div>
                        <?php endif; ?>
                        <div class="home-blog-card-body">
                            <?php if ($p['series_name']): ?>
                            <p class="home-blog-card-cat"><?= htmlspecialchars(db_field($p, 'series_name')) ?><?= $p['series_order'] ? ' · ' . htmlspecialchars(sprintf(t('home_blog_episode'), (int)$p['series_order'])) : '' ?></p>
                            <?php endif; ?>
                            <div class="home-blog-card-title"><?= htmlspecialchars(db_field($p, 'title')) ?></div>
                            <?php if ($p['summary']): ?>
                            <div class="home-blog-card-summary"><?= htmlspecialchars(db_field($p, 'summary')) ?></div>
                            <?php endif; ?>
                            <time class="home-blog-card-date"><?= date('Y.m.d', strtotime($p['created_at'])) ?></time>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- CONTACT CTA -->
        <section class="home-contact-section">
            <div class="container">
                <div class="home-contact-inner">
                    <p class="home-contact-label"><?= htmlspecialchars(t('home_contact_label')) ?></p>
                    <h2 class="home-contact-title"><?= htmlspecialchars(t('home_contact_title')) ?></h2>
                    <p class="home-contact-body"><?= t('home_contact_body') ?></p>
                    <div class="home-contact-actions">
                        <button type="button" class="home-contact-btn home-contact-btn--primary" data-bs-toggle="modal" data-bs-target="#contactModal">
                            <i class="bi bi-envelope-fill"></i> <?= htmlspecialchars(t('home_contact_email_btn')) ?>
                        </button>
                        <a href="tel:+827051244568" class="home-contact-btn home-contact-btn--ghost">
                            <i class="bi bi-telephone-fill"></i> 070-5124-4568
                        </a>
                    </div>
                    <p class="home-contact-hint"><?= htmlspecialchars(t('home_contact_hint')) ?></p>
                </div>
            </div>
        </section>

        <?php include __DIR__ . '/src/components/contact_modal.php'; ?>

        <?php include __DIR__ . '/src/components/footer.php'; ?>

    <script src="/src/js/collection-share.js?v=<?= md5_file(__DIR__ . '/src/js/collection-share.js') ?>"></script>
    <script>
    (function () {
        // en 모드에서는 /en/ 접두사가 붙은 주소로 이동해야 엔진 페이지도 영문으로 열린다
        const ENGINE_URLS = {
            classic:  '<?= lang_href('/src/engine/classic/classic.php') ?>',
            square:   '<?= lang_href('/src/engine/square/square.php') ?>',
            cross:    '<?= lang_href('/src/engine/cross/cross.php') ?>',
            triangle: '<?= lang_href('/src/engine/triangle/triangle.php') ?>',
            diamond:  '<?= lang_href('/src/engine/diamond/diamond.php') ?>',
            hexagon:  '<?= lang_href('/src/engine/hexagon/hexagon.php') ?>',
        };
        const DEFAULT_ENGINE = 'classic';
        // JS에서 쓰는 문구도 PHP 사전에서 주입 (en 모드에서 버튼·상태 메시지가 한글로 남지 않도록)
        const T = <?= json_encode([
            'send'           => t('home_ai_send'),
            'thinking'       => t('home_ai_thinking'),
            'analyzing'      => t('home_ai_analyzing'),
            'errorPrefix'    => t('home_ai_error_prefix'),
            'applied'        => t('home_ai_applied'),
            'movingToStudio' => t('home_ai_moving'),
            'networkError'   => t('home_ai_network_error'),
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

        const inputEl   = document.getElementById('idxAiInput');
        const sendBtn   = document.getElementById('idxAiSend');
        const resultEl  = document.getElementById('idxAiResult');
        const samplesEl = document.getElementById('idxAiSamples');

        // 입력한 프롬프트를 기억해뒀다가 홈에 다시 올 때 그대로 복원
        const DRAFT_KEY = 'pmok_home_ai_draft';
        const draft = localStorage.getItem(DRAFT_KEY);
        if (draft) inputEl.value = draft;
        inputEl.addEventListener('input', () => localStorage.setItem(DRAFT_KEY, inputEl.value));

        // 입력창 포커스 시 추천 검색어 목록이 입력창 아래로 떨어지듯 열림
        inputEl.addEventListener('focus', () => samplesEl.classList.add('idx-ai-samples-open'));
        inputEl.addEventListener('blur',  () => samplesEl.classList.remove('idx-ai-samples-open'));
        // 목록 안을 클릭해도 입력창 포커스가 풀리지 않게(=목록이 닫히지 않게) 처리
        samplesEl.addEventListener('mousedown', e => e.preventDefault());

        // 샘플 버튼 클릭 시, 버튼 복제본이 중력을 받아 입력창으로 '떨어지는' 연출
        function dropSampleIntoInput(btn) {
            const startRect = btn.getBoundingClientRect();
            const endRect   = inputEl.getBoundingClientRect();

            const clone = btn.cloneNode(true);
            clone.classList.add('idx-ai-sample-drop');
            clone.style.position      = 'fixed';
            clone.style.left          = startRect.left + 'px';
            clone.style.top           = startRect.top + 'px';
            clone.style.width         = startRect.width + 'px';
            clone.style.margin        = '0';
            clone.style.pointerEvents = 'none';
            document.body.appendChild(clone);

            btn.classList.add('idx-ai-sample-launched');

            const dx = endRect.left + 16 - startRect.left;
            const dy = endRect.top + (endRect.height - startRect.height) / 2 - startRect.top;

            requestAnimationFrame(() => {
                clone.style.transform = `translate(${dx}px, ${dy}px) scale(.6) rotate(10deg)`;
                clone.style.opacity   = '0';
            });

            setTimeout(() => {
                clone.remove();
                btn.classList.remove('idx-ai-sample-launched');
                inputEl.value = btn.textContent;
                localStorage.setItem(DRAFT_KEY, inputEl.value);
                samplesEl.classList.remove('idx-ai-samples-open');
                inputEl.classList.add('idx-ai-input-landed');
                setTimeout(() => inputEl.classList.remove('idx-ai-input-landed'), 300);
                send(); // 셀렉터처럼 추천 검색어를 고르면 바로 설계 시작
            }, 550);
        }

        document.querySelectorAll('.idx-ai-sample').forEach(btn => {
            btn.addEventListener('click', () => dropSampleIntoInput(btn));
        });

        async function send() {
            const msg = inputEl.value.trim();
            if (!msg) return;
            sendBtn.disabled = true;
            sendBtn.textContent = T.thinking;
            resultEl.style.display = '';
            resultEl.textContent   = T.analyzing;

            try {
                let sessionKey = sessionStorage.getItem('pmok_ai_session');
                if (!sessionKey) { sessionKey = Math.random().toString(36).slice(2) + Date.now().toString(36); sessionStorage.setItem('pmok_ai_session', sessionKey); }

                const res  = await fetch('/src/api/ai/chat.php', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body:    JSON.stringify({ engine: DEFAULT_ENGINE, message: msg, params: {}, session_key: sessionKey }),
                });
                const data = await res.json();
                if (data.error) {
                    resultEl.textContent = T.errorPrefix + data.error;
                } else {
                    const engine = data.engine || DEFAULT_ENGINE;
                    const url    = ENGINE_URLS[engine] || ENGINE_URLS[DEFAULT_ENGINE];
                    resultEl.innerHTML = (data.reply || T.applied) +
                        ' <strong>' + T.movingToStudio + '</strong>';
                    // params와 원본 프롬프트/응답을 sessionStorage에 저장 후 엔진으로 이동
                    sessionStorage.setItem('pmok_ai_params', JSON.stringify(data.params || {}));
                    sessionStorage.setItem('pmok_ai_prompt_text', msg);
                    sessionStorage.setItem('pmok_ai_reply_text', data.reply || '');
                    setTimeout(() => { location.href = url; }, 900);
                }
            } catch {
                resultEl.textContent = T.networkError;
            }
            sendBtn.disabled = false;
            sendBtn.textContent = T.send;
        }

        sendBtn.addEventListener('click', send);
        inputEl.addEventListener('keydown', e => { if (e.key === 'Enter') send(); });
    })();
    </script>
    </body>

</html>