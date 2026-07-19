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
        $cc['href'] = ($editorUrl && $cc['drawing_id'])
            ? $editorUrl . '?drawing_id=' . (int)$cc['drawing_id']
            : '/collection/detail?slug=' . urlencode($cc['slug']);
        $cc['display_name'] = library_pattern_display_name($cc['slug'], $cc['name_ko']);
    }
    unset($cc);
} catch (Throwable $e) {
    $collectionCards = [];
}
// 블로그 글 3개 (테이블 없으면 빈 배열)
try {
    $latestPosts = $pdo ? $pdo->query(
        "SELECT p.*, s.name AS series_name
         FROM blog_posts p
         LEFT JOIN blog_series s ON s.id = p.series_id
         WHERE p.is_active=1 ORDER BY p.sort_order, p.id LIMIT 3"
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
        <?php require_once __DIR__ . '/src/lib/meta.php'; meta_tags(); organization_jsonld(); ?>
        <?php define('BOOTSTRAP_LOADED', true); ?>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="/src/css/index.css?v=<?= md5_file(__DIR__ . '/src/css/index.css') ?>">
    </head>
    <body>
        <?php include __DIR__ . '/src/components/nav.php'; ?>
        <div class="home-wrapper">
            <h1 class="visually-hidden">평목 - 나만의 한옥 살창·창호를 실시간으로 디자인하는 스튜디오</h1>
            <p class="hero-top-copy">창호는 고르는 것이 아니라, 그리는 것입니다</p>
            <!-- AI 프롬프트 -->
            <div class="container">
                <div class="idx-ai-wrap">
                    <div class="idx-ai-bar">
                        <i class="bi bi-stars idx-ai-icon"></i>
                        <input type="text" id="idxAiInput" class="idx-ai-input"
                            placeholder="원하는 창호를 말해보세요  예: 정자살 여닫이 2짝 900×2000">
                        <button id="idxAiSend" class="idx-ai-btn">설계 시작</button>
                    </div>
                    <div id="idxAiResult" class="idx-ai-result" style="display:none;"></div>
                </div>
            </div>
            <!-- -->
            <div class="container">
                <div class="mt-5 mb-4">
                    <p class="ab-section-label">Studio</p>
                    <h2 class="ab-section-title">그려지면, 만들 수 있습니다.<br><span class="small">창호를 직접 설계하고 실시간 예상 견적을 확인하세요.</span></h2>
                    
                    <p class="ab-section-body">문틀 크기, 살 간격, 패턴을 조정하면 예상 견적이 즉시 반영됩니다.</p>
                    <p class="ab-section-body">설계 지식이 없어도 괜찮습니다. 제작 가능한 형태인지는 <strong>알고리즘이 판단합니다.</strong></p>
                    <p class="ab-section-body">스튜디오는 만들 수 없는 창호를 그리지 않습니다. <strong>평목 공방에서 제작해 드립니다.</strong></p>
                </div>
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
                    ['engine_key'=>'triangle', 'title'=>'Triangle Lattice', 'description'=>'세 방향의 살이 60° 각도로 교차하는 삼각형 문살 패턴.<br>역동적이고 세련된 인상을 공간에 더합니다.','image_url'=>''],
                    ['engine_key'=>'diamond',  'title'=>'Diamond Lattice',  'description'=>'4방향 살이 대각선을 포함해 방사형으로 교차하는 패턴.<br>화려하고 입체적인 구조감을 연출합니다.',   'image_url'=>''],
                    ['engine_key'=>'hexagon',  'title'=>'Hexagon Lattice',  'description'=>'세 방향의 살이 서로 맞물려 육각형 눈을 이루는 어금육모 패턴.<br>자연의 벌집 구조를 닮은 단정하고 견고한 전통미를 담아냅니다.','image_url'=>''],
                ];
                $renderCards = !empty($studioCards) ? $studioCards : $defaultCards;
                ?>
                <div class="row g-4">
                <?php foreach ($renderCards as $sc):
                    $key   = $sc['engine_key'];
                    $hasBg = !empty($sc['image_url']);
                ?>
                    <div class="col-md-4 text-center">
                        <div class="service-card service-card--<?= htmlspecialchars($key) ?><?= $hasBg ? ' has-bg-image' : '' ?> h-80 p-4"<?= $hasBg ? ' style="--card-bg-image:url(\'' . htmlspecialchars($sc['image_url']) . '\')"' : '' ?>>
                            <div class="mb-4" style="height:120px;display:flex;align-items:center;justify-content:center;">
                                <a href="/src/engine/<?= htmlspecialchars($key) ?>/<?= htmlspecialchars($key) ?>.php" class="pm-symbol-link" aria-label="<?= htmlspecialchars($sc['title']) ?> 패턴 미리보기">
                                    <?= $svgIcons[$key] ?? '' ?>
                                    <span class="visually-hidden"><?= htmlspecialchars($sc['title']) ?> 패턴 미리보기</span>
                                </a>
                            </div>
                            <h3 class="service-title text-center mb-3"><?= htmlspecialchars($sc['title']) ?></h3>
                            <p class="service-sub-text text-center mb-0"><?= $sc['description'] ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <!-- card -->
            <?php if (!empty($collectionCards)): ?>
            <section class="collection-strip-section">
                <div class="container">
                    <div class="mb-4 d-flex align-items-end justify-content-between flex-wrap gap-2">
                        <div>
                            <p class="ab-section-label">Collection</p>
                            <h2 class="ab-section-title">마음에 드는 패턴을 골라 편집해보세요.</h2>
                            <p class="ab-section-body">컬렉션에서 패턴을 둘러보고,<br>마음에 드는 걸 그대로 에디터로 불러와 나만의 크기·색상으로 편집해 사용할 수 있습니다.</p>
                        </div>
                        <a href="/collection/" class="home-blog-more">컬렉션 전체 보기 <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <div class="collection-strip-outer">
                        <button type="button" class="collection-strip-nav collection-strip-nav-prev" aria-label="이전 패턴 보기"><i class="bi bi-chevron-left"></i></button>
                        <div class="collection-strip-viewport">
                            <div class="collection-strip-track" id="collectionStripTrack">
                                <?php foreach (array_merge($collectionCards, $collectionCards) as $cc): ?>
                                <a class="collection-strip-card" href="<?= htmlspecialchars($cc['href']) ?>">
                                    <img src="<?= htmlspecialchars($cc['image_path']) ?>" alt="<?= htmlspecialchars($cc['display_name']) ?>" loading="lazy">
                                    <div class="collection-strip-label"><?= htmlspecialchars($cc['display_name']) ?></div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <button type="button" class="collection-strip-nav collection-strip-nav-next" aria-label="다음 패턴 보기"><i class="bi bi-chevron-right"></i></button>
                    </div>
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
                var cards = track.querySelectorAll('.collection-strip-card');
                var totalCount = cards.length / 2; // 무한 루프용으로 두 벌 렌더링된 원본 개수

                // 뷰포트 너비를 카드 최소 폭(180px) 기준으로 나눠 보여줄 장수를 정하고, 그만큼 등분해 카드 크기를 계산 (잘린 카드가 보이지 않게 함)
                var minCardWidth = 180;
                function measure() {
                    var vw = viewport.clientWidth;
                    var fit = Math.floor((vw + gap) / (minCardWidth + gap));
                    var count = Math.max(1, Math.min(4, fit, totalCount));
                    var cardWidth = (vw - (count - 1) * gap) / count;
                    cards.forEach(function (el) { el.style.width = cardWidth + 'px'; });
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
            <section class="values-section mt-5 ">
                <div class="values-inner container">
                    <!-- Curation Header -->
                    <hr class="curation-divider">
                    <div class="mt-5 mb-4">
                        <p class="ab-section-label">큐레이션</p>
                        <h2 class="ab-section-title">공간별 창호 디자인을 찾아보세요.</h2>
                        <p class="ab-section-body">중문부터 거실, 카페, 서재까지—<br>원하는 공간을 선택하면 추천 컬렉션을 바로 확인할 수 있습니다.</p>
                    </div>
                    <!-- Space Cards (DB) -->
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
                </div>
            </section>
            <!-- card -->
        </div>
        <!-- home-wrapper -->
        <!-- Process -->
        <section class="process-section">
            <div class="container">
                <div class="mb-4 d-flex align-items-end justify-content-between flex-wrap gap-2">
                    <div>
                        <p class="ab-section-label">사용법</p>
                        <h2 class="ab-section-title">이렇게 사용하세요.</h2>
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
                            <p class="process-desc">상단 스튜디오 메뉴에서 원하는 창호 패턴을 선택하세요.</p>
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
                            <p class="process-desc">완성된 설계를 가지고 평목 공방에 제작을 주문하세요.</p>
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

        <!-- Guide (사용법과 겹치는 4개 카드는 제거, 나머지만 사용법 바로 옆에 배치) -->
        <section class="guide-section">
            <div class="container">
                <div class="mb-4">
                    <p class="ab-section-label">더 알아보기</p>
                    <h2 class="ab-section-title">그 외 궁금한 점</h2>
                    <p class="ab-section-body">평목 소개부터 컬렉션, 계정, 배송까지 자주 찾는 도움말을 모았습니다.</p>
                </div>
                <div class="guide-cards-grid">
                    <a href="/guide/intro" class="guide-card">
                        <div class="guide-card-icon" style="background:var(--accent-tint);color:var(--accent);"><i class="bi bi-info-circle-fill"></i></div>
                        <div class="guide-card-body">
                            <div class="guide-card-title">평목 소개</div>
                            <div class="guide-card-desc">평목이 무엇인지, 어떻게 시작하는지 알아보세요.</div>
                        </div>
                        <i class="bi bi-arrow-right guide-card-arrow"></i>
                    </a>
                    <a href="/guide/collection" class="guide-card">
                        <div class="guide-card-icon" style="background:var(--accent-tint);color:var(--accent);"><i class="bi bi-collection-fill"></i></div>
                        <div class="guide-card-body">
                            <div class="guide-card-title">컬렉션</div>
                            <div class="guide-card-desc">공개 라이브러리 패턴을 열람하고 내 보드에 저장하세요.</div>
                        </div>
                        <i class="bi bi-arrow-right guide-card-arrow"></i>
                    </a>
                    <a href="/guide/account" class="guide-card">
                        <div class="guide-card-icon" style="background:var(--accent-tint);color:var(--accent);"><i class="bi bi-person-gear"></i></div>
                        <div class="guide-card-body">
                            <div class="guide-card-title">계정 설정</div>
                            <div class="guide-card-desc">프로필, 비밀번호, 회사 정보를 관리하는 방법을 안내합니다.</div>
                        </div>
                        <i class="bi bi-arrow-right guide-card-arrow"></i>
                    </a>
                    <a href="/guide/delivery" class="guide-card">
                        <div class="guide-card-icon" style="background:var(--accent-tint);color:var(--accent);"><i class="bi bi-truck"></i></div>
                        <div class="guide-card-body">
                            <div class="guide-card-title">배송</div>
                            <div class="guide-card-desc">택배·화물 배송 방법과 반품·교환 안내를 확인하세요.</div>
                        </div>
                        <i class="bi bi-arrow-right guide-card-arrow"></i>
                    </a>
                </div>
            </div>
        </section>

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

        <!-- FAQ -->
        <?php if ($faqVisible): ?>
        <section class="faq-section">
            <div class="container">
                <div class="mb-4">
                    <p class="ab-section-label">FAQ</p>
                    <h2 class="ab-section-title">자주 묻는 질문</h2>
                </div>
                <?php if (!empty($faqs)): ?>
                <div class="accordion faq-accordion" id="faqAccordion">
                    <?php foreach ($faqs as $i => $faq): ?>
                    <div class="accordion-item faq-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button faq-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $faq['id'] ?>">
                                <?= htmlspecialchars($faq['question']) ?>
                            </button>
                        </h3>
                        <div id="faq<?= $faq['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body faq-body">
                                <?= $faq['answer'] ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
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
                            <i class="bi bi-envelope-fill"></i> 이메일로 문의하기
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

        const inputEl  = document.getElementById('idxAiInput');
        const sendBtn  = document.getElementById('idxAiSend');
        const resultEl = document.getElementById('idxAiResult');

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
                    // params를 sessionStorage에 저장 후 엔진으로 이동
                    sessionStorage.setItem('pmok_ai_params', JSON.stringify(data.params || {}));
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