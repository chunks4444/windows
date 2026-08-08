<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/src/lib/db.php';
require_once __DIR__ . '/src/lib/slug.php';
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
try {
    $homeAiSamplesRaw = $pdo ? $pdo->query("SELECT value FROM site_config WHERE key_name='home_ai_sample_prompts'")->fetchColumn() : false;
    $homeAiSamples    = $homeAiSamplesRaw ? array_values(array_filter((array)json_decode($homeAiSamplesRaw, true))) : [];
} catch (Throwable $e) {
    $homeAiSamples = [];
}
if (!$homeAiSamples) $homeAiSamples = $homeAiSampleDefaults;
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
        $editorUrl  = $collectionEditorMap[$engineKey] ?? null;
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
        "SELECT p.*, s.name AS series_name
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
        SELECT s.tagline, p.slug, s.name AS series_name
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
<html lang="ko">

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
            <h1 class="visually-hidden">평목 - 나만의 한옥 살창·창호를 실시간으로 디자인하는 스튜디오</h1>
            <p class="hero-top-copy">같은 공간은 없으니까요. 치수와 빛에 맞춰 그립니다.</p>
            <p class="hero-top-subcopy">한옥 창호, 기법은 전통 — 모양은 그린 그대로 평목이 만듭니다</p>
            <!-- AI 프롬프트 -->
            <div class="container">
                <div class="idx-ai-wrap">
                    <div class="idx-ai-bar">
                        <i class="bi bi-stars idx-ai-icon"></i>
                        <input type="text" id="idxAiInput" class="idx-ai-input" autocomplete="off"
                            placeholder="원하는 창호를 말해보세요  예: 정자살 여닫이 2짝 900×2000">
                        <button id="idxAiSend" class="idx-ai-btn">설계 시작</button>
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
                    ['engine_key'=>'classic',  'title'=>'Classic Lattice',  'description'=>'가는 살대를 성기게 세로·가로로만 짜 넣은 가장 단순한 전통 문살 패턴.<br>여백이 넓어 담백하고 개방감 있는 인상을 줍니다.',          'image_url'=>''],
                    ['engine_key'=>'square',   'title'=>'Square Lattice',   'description'=>'가로살과 세로살이 촘촘하게 井(우물 정)자를 이루며 교차하는 정방형 문살 패턴.<br>단아하고 절제된 아름다움을 표현합니다.',   'image_url'=>''],
                    ['engine_key'=>'cross',    'title'=>'Cross Lattice',    'description'=>'45° 대각선으로 교차하는 마름모 문살 패턴.<br>역동적인 사선의 흐름이 공간에 긴장감을 더합니다.', 'image_url'=>''],
                    ['engine_key'=>'triangle', 'title'=>'Triangle Lattice', 'description'=>"수직살과 좌우 빗살, 세 방향의 살대가 한 점에서 만나도록 짠 세모 솟을살을 재현한 엔진입니다. '솟을'은 살이 교차점에서 겹치며 위로 솟아오르는 데서 온 이름으로, 교차점마다 살이 도드라져 짜임에 입체감이 살아 있습니다. 살들이 교차하며 정삼각형이 화면 가득 반복되어, 육모의 둥글고 넉넉한 인상과 달리 팽팽하고 긴장감 있는 느낌을 줍니다. 모든 셀이 정삼각형이 되도록 세로 칸수가 자동으로 계산되며, 세로 칸수를 직접 지정할 수는 없습니다.",'image_url'=>''],
                    ['engine_key'=>'diamond',  'title'=>'Diamond Lattice',  'description'=>'4방향 살이 대각선을 포함해 방사형으로 교차하는 패턴.<br>화려하고 입체적인 구조감을 연출합니다.',   'image_url'=>''],
                    ['engine_key'=>'hexagon',  'title'=>'Hexagon Lattice',  'description'=>"세모솟을살과 같은 세 방향 살대를 쓰되, 교차점을 한 점에 모으지 않고 어긋나게 짜 육각형이 열리도록 한 육모 솟을살을 재현한 엔진입니다. 어금육모라고도 부릅니다. '솟을'은 살이 교차점에서 겹치며 위로 솟아오르는 데서 온 이름으로, 짜임에 입체감이 살아 있습니다. 살이 만드는 벌집 모양의 여섯 각은 사각보다 원에 가까워, 같은 짜임인데도 세모의 팽팽함 대신 둥글고 넉넉한 인상을 줍니다.",'image_url'=>''],
                ];
                $renderCards = !empty($studioCards) ? $studioCards : $defaultCards;
                ?>
                <div class="engine-icon-row">
                <?php foreach ($renderCards as $sc):
                    $key = $sc['engine_key'];
                ?>
                    <a href="/src/engine/<?= htmlspecialchars($key) ?>/<?= htmlspecialchars($key) ?>.php" class="engine-icon-shortcut" aria-label="<?= htmlspecialchars($sc['title']) ?> 패턴 미리보기">
                        <span class="engine-icon-circle"><?= $svgIcons[$key] ?? '' ?></span>
                        <span class="engine-icon-label"><?= htmlspecialchars($sc['title']) ?></span>
                    </a>
                <?php endforeach; ?>
                </div>
            <!-- card -->
            <?php if (!empty($collectionCards)): ?>
            <section class="collection-strip-section">
                <div class="collection-strip-header mb-4 d-flex align-items-end justify-content-between flex-wrap gap-2">
                    <div>
                        <p class="ab-section-label">Collection</p>
                        <h2 class="ab-section-title">마음에 드는 패턴을 골라 편집해보세요.</h2>
                    </div>
                    <a href="/collection/" class="home-blog-more">컬렉션 전체 보기 <i class="bi bi-arrow-right"></i></a>
                </div>
                <!-- Collection: 사이트 폭(container)에 맞춰 정렬 -->
                <div class="collection-strip-outer">
                    <button type="button" class="collection-strip-nav collection-strip-nav-prev" aria-label="이전 패턴 보기"><i class="bi bi-chevron-left"></i></button>
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
                    <button type="button" class="collection-strip-nav collection-strip-nav-next" aria-label="다음 패턴 보기"><i class="bi bi-chevron-right"></i></button>
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
            <p class="hc-label">빛과 살</p>
            <h2 class="hc-title-compact">한옥 창호는 빛을 막지 않고<br>나누어 들입니다</h2>
            <div class="hc-prose">
              <div>
                <p>유리창은 빛을 통째로 들이고, 벽은 통째로 막습니다.
                   살은 그 사이에 있습니다. 막으면서 들이고, 들이면서 거릅니다.</p>
                <p>살 간격이 촘촘하면 빛이 잘게 부서져 방 안이 고르게 밝아집니다.
                   성기면 덩어리로 들어와 바닥에 또렷한 그림자를 남깁니다.
                   <b>같은 문양이라도 창이 앉는 방향과 시간에 따라 다르게 보입니다.</b></p>
              </div>
              <div>
                <p>그래서 옛 목수는 방마다 살을 달리 짰습니다.
                   안방과 대청이 같을 수 없고, 남향과 북향이 같을 수 없습니다.</p>
                <p>스튜디오에서 살 간격을 옮기는 일은 무늬를 고르는 일처럼 보이지만,
                   실은 그 방에 들어올 빛을 정하는 일입니다.</p>
              </div>
            </div>
          </div>
        </section>

        <section class="hc-band">
          <div class="container">
            <p class="hc-label">살의 쓰임</p>
            <h2 class="hc-title-compact">한식 창호와 목창호,<br>같은 살짜임으로 만듭니다</h2>
            <p class="hc-sub">한옥에 들어가는 창호든 현대 공간에 들어가는 목창호든,
               짜는 문법은 하나입니다. 스케일만 다릅니다.</p>
            <div class="hc-lines">
              <div class="hc-line">
                <h3>한식 창호</h3>
                <p>세살·정자살·완자살·교살·솟을살. 여닫이와 미서기, 들어열개까지.
                   살이 제 크기로 서는 자리입니다.</p>
              </div>
              <div class="hc-line">
                <h3>목창호</h3>
                <p>한옥이 아닌 공간에 들어가는 창과 문. 살은 그대로 두고
                   틀만 그 공간의 치수를 따릅니다.</p>
              </div>
              <div class="hc-line">
                <h3>파티션</h3>
                <p>벽을 세우지 않고 자리를 나눌 때. 살의 밀도가 시선이 어디까지
                   갈지를 정합니다.</p>
              </div>
              <div class="hc-line">
                <h3>가구·기물</h3>
                <p>같은 살을 손에 잡히는 크기로. 장의 문짝과 조명에서는
                   살이 훨씬 가늘어집니다.</p>
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
                        <p class="ab-section-label">사용법</p>
                        <h2 class="ab-section-title">그린 것과 나온 것이 다르지 않게</h2>
                        <p class="ab-section-body">설계와 제작 사이, 말로 옮겨 적는 단계가 없습니다. 완성한 도면 그대로 평목 공방에서 제작됩니다.</p>
                    </div>
                    <a href="/guide/" class="home-blog-more">가이드 전체 보기 <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="process-container">
                    <div class="process-step">
                        <div class="process-card-bg">
                            <span class="process-num">01</span>
                            <i class="bi bi-pencil-square process-icon"></i>
                        </div>
                        <div class="process-card-overlay">
                            <h3 class="process-title">패턴 설계</h3>
                            <p class="process-desc">평목 스튜디오는 브라우저에서 바로 사용할 수 있는 창호 설계 도구입니다. 상단 스튜디오 메뉴에서 원하는 창호 패턴을 선택하고, 문틀 크기·살 간격·패턴을 조정하며 나만의 창호를 완성해 보세요.</p>
                            <ul class="process-hints">
                                <li><i class="bi bi-check2"></i> 문틀 가로·세로 크기 입력</li>
                                <li><i class="bi bi-check2"></i> 살 간격·두께 슬라이더 조정</li>
                                <li><i class="bi bi-check2"></i> 실시간으로 결과 확인</li>
                            </ul>
                            <a href="/guide/studio-classic" class="process-guide-link">스튜디오 가이드 보기 <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="process-step">
                        <div class="process-card-bg">
                            <span class="process-num">02</span>
                            <i class="bi bi-bookmark-heart process-icon"></i>
                        </div>
                        <div class="process-card-overlay">
                            <h3 class="process-title">저장 & 탐색</h3>
                            <p class="process-desc">완성된 도면을 저장하고 컬렉션에서 영감을 찾아보세요.</p>
                            <ul class="process-hints">
                                <li><i class="bi bi-check2"></i> 도면 저장 후 내 도면에서 관리</li>
                                <li><i class="bi bi-check2"></i> 컬렉션에서 다양한 패턴 탐색</li>
                                <li><i class="bi bi-check2"></i> 보드에 마음에 드는 패턴 모으기</li>
                            </ul>
                            <a href="/guide/drawing" class="process-guide-link">도면 관리 가이드 보기 <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="process-step">
                        <div class="process-card-bg">
                            <span class="process-num">03</span>
                            <i class="bi bi-file-earmark-image process-icon"></i>
                        </div>
                        <div class="process-card-overlay">
                            <h3 class="process-title">렌더링 & 내보내기</h3>
                            <p class="process-desc">완성된 도면을 PNG·PDF로 내보내거나 AI 렌더링으로 실제 공간에 배치해 검토하세요.</p>
                            <ul class="process-hints">
                                <li><i class="bi bi-check2"></i> PNG·PDF 고해상도 내보내기</li>
                                <li><i class="bi bi-check2"></i> AI 렌더링으로 공간 시각화</li>
                                <li><i class="bi bi-check2"></i> 배경 이미지와 도면 합성 확인</li>
                            </ul>
                            <a href="/guide/render" class="process-guide-link">렌더링 가이드 보기 <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="process-step">
                        <div class="process-card-bg">
                            <span class="process-num">04</span>
                            <i class="bi bi-chat-heart process-icon"></i>
                        </div>
                        <div class="process-card-overlay">
                            <h3 class="process-title">제작 주문</h3>
                            <p class="process-desc">완성한 도면으로 주문하세요.</p>
                            <ul class="process-hints">
                                <li><i class="bi bi-check2"></i> 도면 오른쪽 상단 견적요청 버튼 클릭</li>
                                <li><i class="bi bi-check2"></i> 저장한 도면 기반으로 상담</li>
                                <li><i class="bi bi-check2"></i> 공방 검토 후 최종 견적 회신</li>
                            </ul>
                            <a href="/guide/order" class="process-guide-link">주문 안내 보기 <i class="bi bi-arrow-right"></i></a>
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
                        <p class="ab-section-label">FAQ</p>
                        <h2 class="ab-section-title">자주 묻는 질문</h2>
                    </div>
                    <a href="/guide/faq" class="home-blog-more">전체 보기 <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="faq-columns">
                    <div class="accordion faq-accordion" id="faqAccordionLeft">
                        <?php foreach ($faqLeft as $faq): ?>
                        <div class="accordion-item faq-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button faq-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $faq['id'] ?>">
                                    <?= htmlspecialchars($faq['question']) ?>
                                </button>
                            </h3>
                            <div id="faq<?= $faq['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#faqAccordionLeft">
                                <div class="accordion-body faq-body">
                                    <?= $faq['answer'] ?>
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
                                    <?= htmlspecialchars($faq['question']) ?>
                                </button>
                            </h3>
                            <div id="faq<?= $faq['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#faqAccordionRight">
                                <div class="accordion-body faq-body">
                                    <?= $faq['answer'] ?>
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
                        <p class="ab-section-label">블로그</p>
                        <h2 class="ab-section-title">창호 이야기</h2>
                        <p class="ab-section-body">평목 공방이 전하는 창호와 한옥 살창 이야기.</p>
                    </div>
                    <a href="/blog/" class="home-blog-more">전체 보기 <i class="bi bi-arrow-right"></i></a>
                </div>
                <?php if ($blogQuote): ?>
                <a href="/blog/<?= rawurlencode($blogQuote['slug']) ?>" class="home-quote-banner">
                    <p class="home-quote-text">"<?= htmlspecialchars($blogQuote['tagline']) ?>"</p>
                    <p class="home-quote-sub"><?= htmlspecialchars($blogQuote['series_name']) ?> · 1화 이야기 읽어보기 <i class="bi bi-arrow-right"></i></p>
                </a>
                <?php endif; ?>
                <div class="home-blog-grid">
                    <?php foreach ($latestPosts as $p): ?>
                    <a href="/blog/<?= rawurlencode($p['slug']) ?>" class="home-blog-card">
                        <?php if ($p['thumbnail_url']): ?>
                        <div class="home-blog-card-thumb">
                            <img src="<?= htmlspecialchars($p['thumbnail_url']) ?>" alt="<?= htmlspecialchars($p['title']) ?>" loading="lazy">
                        </div>
                        <?php endif; ?>
                        <div class="home-blog-card-body">
                            <?php if ($p['series_name']): ?>
                            <p class="home-blog-card-cat"><?= htmlspecialchars($p['series_name']) ?><?= $p['series_order'] ? ' · ' . (int)$p['series_order'] . '화' : '' ?></p>
                            <?php endif; ?>
                            <div class="home-blog-card-title"><?= htmlspecialchars($p['title']) ?></div>
                            <?php if ($p['summary']): ?>
                            <div class="home-blog-card-summary"><?= htmlspecialchars($p['summary']) ?></div>
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
                    <p class="home-contact-label">Contact</p>
                    <h2 class="home-contact-title">작은 문의도 괜찮습니다.</h2>
                    <p class="home-contact-body">설계·제작·설치 상담부터<br>협업 및 프로젝트 제안까지 모두 환영합니다.<br><br>편하게 연락해 주세요.<br>빠르게 답변드리겠습니다.</p>
                    <div class="home-contact-actions">
                        <button type="button" class="home-contact-btn home-contact-btn--primary" data-bs-toggle="modal" data-bs-target="#contactModal">
                            <i class="bi bi-envelope-fill"></i> 견적요청
                        </button>
                        <a href="tel:+827051244568" class="home-contact-btn home-contact-btn--ghost">
                            <i class="bi bi-telephone-fill"></i> 070-5124-4568
                        </a>
                    </div>
                    <p class="home-contact-hint">평일 오전 10시 – 오후 6시 운영 · 주말·공휴일 이메일 접수 가능</p>
                </div>
            </div>
        </section>

        <?php include __DIR__ . '/src/components/contact_modal.php'; ?>

        <?php include __DIR__ . '/src/components/footer.php'; ?>

    <script src="/src/js/collection-share.js?v=<?= md5_file(__DIR__ . '/src/js/collection-share.js') ?>"></script>
    <script>
    (function () {
        const ENGINE_URLS = {
            classic:  '/src/engine/classic/classic.php',
            square:   '/src/engine/square/square.php',
            cross:    '/src/engine/cross/cross.php',
            triangle: '/src/engine/triangle/triangle.php',
            diamond:  '/src/engine/diamond/diamond.php',
            hexagon:  '/src/engine/hexagon/hexagon.php',
        };
        const DEFAULT_ENGINE = 'classic';

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
            sendBtn.textContent = '생각 중…';
            resultEl.style.display = '';
            resultEl.textContent   = 'AI가 설계 조건을 분석하는 중입니다…';

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
                    resultEl.textContent = '오류: ' + data.error;
                } else {
                    const engine = data.engine || DEFAULT_ENGINE;
                    const url    = ENGINE_URLS[engine] || ENGINE_URLS[DEFAULT_ENGINE];
                    resultEl.innerHTML = (data.reply || '설계 조건을 적용했습니다.') +
                        ' <strong>스튜디오로 이동합니다…</strong>';
                    // params와 원본 프롬프트/응답을 sessionStorage에 저장 후 엔진으로 이동
                    sessionStorage.setItem('pmok_ai_params', JSON.stringify(data.params || {}));
                    sessionStorage.setItem('pmok_ai_prompt_text', msg);
                    sessionStorage.setItem('pmok_ai_reply_text', data.reply || '');
                    setTimeout(() => { location.href = url; }, 900);
                }
            } catch {
                resultEl.textContent = '네트워크 오류가 발생했습니다.';
            }
            sendBtn.disabled = false;
            sendBtn.textContent = '설계 시작';
        }

        sendBtn.addEventListener('click', send);
        inputEl.addEventListener('keydown', e => { if (e.key === 'Enter') send(); });
    })();
    </script>
    </body>

</html>