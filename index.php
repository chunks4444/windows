<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/src/lib/db.php';
try {
    $pdo        = db();
    $heroSlides = $pdo->query('SELECT * FROM hero_slides WHERE is_active=1 ORDER BY sort_order, id')->fetchAll();
    $spaceCards = $pdo->query('SELECT label, image_url, collection_query FROM space_cards WHERE is_active=1 ORDER BY sort_order, id')->fetchAll();
    $faqs       = $pdo->query('SELECT * FROM faqs WHERE is_active=1 ORDER BY sort_order, id')->fetchAll();
} catch (Throwable $e) {
    $pdo        = null;
    $heroSlides = [];
    $spaceCards = [];
    $faqs       = [];
}
// 스튜디오 카드 (테이블 없으면 빈 배열)
try {
    $studioCards = $pdo ? $pdo->query('SELECT * FROM studio_cards WHERE is_active=1 ORDER BY sort_order, id')->fetchAll() : [];
} catch (Throwable $e) {
    $studioCards = [];
}
// 블로그 글 3개 (테이블 없으면 빈 배열)
try {
    $latestPosts = $pdo ? $pdo->query('SELECT * FROM blog_posts WHERE is_active=1 ORDER BY sort_order, id LIMIT 3')->fetchAll() : [];
} catch (Throwable $e) {
    $latestPosts = [];
}
?>
<!DOCTYPE html>
<html lang="ko">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php require_once __DIR__ . '/src/lib/meta.php'; meta_tags(); ?>
        <?php define('BOOTSTRAP_LOADED', true); ?>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="/src/css/index.css">
    </head>
    <body>
        <?php include __DIR__ . '/src/components/nav.php'; ?>
        <div class="home-wrapper">
            <h1 class="visually-hidden">평목 - 나만의 한옥 살창·창호를 실시간으로 디자인하는 스튜디오</h1>
            <!-- Hero Carousel -->
            <div class="hero-carousel-outer">
              <div class="container">
                <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="4500" data-bs-touch="false">
                    <div class="carousel-indicators">
                        <?php foreach ($heroSlides as $i => $sl): ?>
                        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $i ?>"
                            <?= $i === 0 ? 'class="active" aria-current="true"' : '' ?>
                            aria-label="Slide <?= $i + 1 ?>"></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="carousel-inner">
                        <?php foreach ($heroSlides as $i => $sl): ?>
                        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                            <img src="<?= htmlspecialchars($sl['image_url']) ?>" class="hero-slide-img" alt="<?= htmlspecialchars($sl['title']) ?>">
                            <?php if ($sl['title'] || $sl['subtitle']): ?>
                            <div class="hero-slide-caption">
                                <?php if ($sl['title']): ?>
                                <h2 class="hero-slide-title"><?= htmlspecialchars($sl['title']) ?></h2>
                                <?php endif; ?>
                                <?php if ($sl['subtitle']): ?>
                                <p class="hero-slide-sub"><?= htmlspecialchars($sl['subtitle']) ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    </button>
                </div>
              </div>
            </div>
            <!-- -->
            <div class="container">
                <div class="mt-5 mb-4">
                    <p class="ab-section-label">Studio</p>
                    <h2 class="ab-section-title">나만의 창호를 직접 디자인하세요.</h2>
                    <p class="ab-section-body">평목 스튜디오는 브라우저에서 바로 사용할 수 있는 <strong>창호 설계 도구</strong>입니다.<br>문틀 크기, 살 간격, 패턴을 조정하며 나만의 창호를 완성하면, <strong>평목 공방에서 제작해 드립니다.</strong></p>
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
                    ['engine_key'=>'classic',  'title'=>'Classic Lattice',  'description'=>'전통 창호의 기본이 되는 격자 문살 패턴.<br>균형 잡힌 비례와 절제된 구조가 특징입니다.',          'image_url'=>''],
                    ['engine_key'=>'square',   'title'=>'Square Lattice',   'description'=>'가로살과 세로살이 직각으로 교차하는 정방형 문살 패턴.<br>단아하고 절제된 아름다움을 표현합니다.',   'image_url'=>''],
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
                                <a href="/src/engine/<?= htmlspecialchars($key) ?>/<?= htmlspecialchars($key) ?>.php" class="pm-symbol-link">
                                    <?= $svgIcons[$key] ?? '' ?>
                                </a>
                            </div>
                            <h3 class="service-title text-center mb-3"><?= htmlspecialchars($sc['title']) ?></h3>
                            <p class="service-sub-text text-center mb-0"><?= $sc['description'] ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <!-- card -->
            <section class="values-section mt-5 ">
                <div class="values-inner container">
                    <div class="values-grid d-none">
                        <div class="values-item">
                            <h2 class="values-title">당신의 공간에 빛과 바람의 길을 디자인하세요.</h2>
                            <p class="sub-title">문틀 크기와 비율을 조정하면 실제 제작 가능한<br>맞춤 창호 디자인을 실시간으로 확인할 수 있습니다</p>
                        </div>
                        <div class="values-item">
                            <h2 class="values-title">나만의 창호를 직접 디자인하세요.</h2>
                            <p class="sub-title">실시간으로 디자인하고, 공방에서 완성합니다.<br>원하는 디자인을 즉시 확인하고
                                <br>완성된 설계는 실제 제작으로 이어집니다.
                            </p>
                        </div>
                    </div>
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
                        <a class="space-card" href="/src/collection/" onclick="sessionStorage.setItem('collectionQ','<?= htmlspecialchars($sc['collection_query'], ENT_QUOTES) ?>');">
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
                <div class="mb-4">
                    <p class="ab-section-label">사용법</p>
                    <h2 class="ab-section-title">이렇게 사용하세요.</h2>
                </div>
                <div class="process-container">
                    <div class="process-step">
                        <span class="process-num">01</span>
                        <div class="process-step-body">
                            <i class="bi bi-pencil-square process-icon"></i>
                            <h3 class="process-title">패턴 설계</h3>
                            <p class="process-desc">상단 Studio 메뉴에서 원하는 창호 패턴을 선택하세요.</p>
                            <ul class="process-hints">
                                <li><i class="bi bi-check2"></i> 문틀 가로·세로 크기 입력</li>
                                <li><i class="bi bi-check2"></i> 살 간격·두께 슬라이더 조정</li>
                                <li><i class="bi bi-check2"></i> 실시간으로 결과 확인</li>
                            </ul>
                        </div>
                    </div>
                    <div class="process-step">
                        <span class="process-num">02</span>
                        <div class="process-step-body">
                            <i class="bi bi-bookmark-heart process-icon"></i>
                            <h3 class="process-title">저장 & 탐색</h3>
                            <p class="process-desc">완성된 도면을 저장하고 컬렉션에서 영감을 찾아보세요.</p>
                            <ul class="process-hints">
                                <li><i class="bi bi-check2"></i> 도면 저장 후 내 도면에서 관리</li>
                                <li><i class="bi bi-check2"></i> 컬렉션에서 다양한 패턴 탐색</li>
                                <li><i class="bi bi-check2"></i> 보드에 마음에 드는 패턴 모으기</li>
                            </ul>
                        </div>
                    </div>
                    <div class="process-step">
                        <span class="process-num">03</span>
                        <div class="process-step-body">
                            <i class="bi bi-file-earmark-image process-icon"></i>
                            <h3 class="process-title">렌더링 & 내보내기</h3>
                            <p class="process-desc">완성된 도면을 PNG·PDF로 내보내거나 AI 렌더링으로 실제 공간에 배치해 검토하세요.</p>
                            <ul class="process-hints">
                                <li><i class="bi bi-check2"></i> PNG·PDF 고해상도 내보내기</li>
                                <li><i class="bi bi-check2"></i> AI 렌더링으로 공간 시각화</li>
                                <li><i class="bi bi-check2"></i> 배경 이미지와 도면 합성 확인</li>
                            </ul>
                        </div>
                    </div>
                    <div class="process-step">
                        <span class="process-num">04</span>
                        <div class="process-step-body">
                            <i class="bi bi-chat-heart process-icon"></i>
                            <h3 class="process-title">제작 주문</h3>
                            <p class="process-desc">완성된 설계를 가지고 평목 공방에 제작을 주문하세요.</p>
                            <ul class="process-hints">
                                <li><i class="bi bi-check2"></i> 도면 오른쪽 상단 주문버튼 클릭</li>
                                <li><i class="bi bi-check2"></i> 저장한 도면 기반으로 상담</li>
                                <li><i class="bi bi-check2"></i> 설계·제작·협업 모두 환영</li>
                            </ul>
                        </div>
                    </div>
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
                    <a href="/src/blog/" class="home-blog-more">전체 보기 <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="home-blog-grid">
                    <?php foreach ($latestPosts as $p): ?>
                    <a href="/src/blog/detail.php?id=<?= $p['id'] ?>" class="home-blog-card">
                        <?php if ($p['thumbnail_url']): ?>
                        <div class="home-blog-card-thumb">
                            <img src="<?= htmlspecialchars($p['thumbnail_url']) ?>" alt="<?= htmlspecialchars($p['title']) ?>" loading="lazy">
                        </div>
                        <?php endif; ?>
                        <div class="home-blog-card-body">
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
        <!-- Guide -->
        <section class="guide-section">
            <div class="container">
                <div class="mb-4">
                    <p class="ab-section-label">가이드</p>
                    <h2 class="ab-section-title">평목 스튜디오 가이드</h2>
                    <p class="ab-section-body">처음 사용하시나요? 단계별 가이드에서 모든 기능을 확인하세요.</p>
                </div>
                <div class="guide-cards-grid">
                    <a href="/src/guide/intro.php" class="guide-card">
                        <div class="guide-card-icon" style="background:#E6F4F2;color:#3A8C82;"><i class="bi bi-info-circle-fill"></i></div>
                        <div class="guide-card-body">
                            <div class="guide-card-title">평목 소개</div>
                            <div class="guide-card-desc">평목이 무엇인지, 어떻게 시작하는지 알아보세요.</div>
                        </div>
                        <i class="bi bi-arrow-right guide-card-arrow"></i>
                    </a>
                    <a href="/src/guide/studio-classic.php" class="guide-card">
                        <div class="guide-card-icon" style="background:#FFF0EE;color:#cc2200;"><i class="bi bi-pencil-square"></i></div>
                        <div class="guide-card-body">
                            <div class="guide-card-title">스튜디오 사용법</div>
                            <div class="guide-card-desc">6가지 격자 패턴 엔진의 상세 사용 방법을 안내합니다.</div>
                        </div>
                        <i class="bi bi-arrow-right guide-card-arrow"></i>
                    </a>
                    <a href="/src/guide/drawing.php" class="guide-card">
                        <div class="guide-card-icon" style="background:#F5F4EE;color:#7A6B40;"><i class="bi bi-folder2-open"></i></div>
                        <div class="guide-card-body">
                            <div class="guide-card-title">도면 관리</div>
                            <div class="guide-card-desc">도면 저장, 버전 관리, PDF·PNG 내보내기 방법을 안내합니다.</div>
                        </div>
                        <i class="bi bi-arrow-right guide-card-arrow"></i>
                    </a>
                    <a href="/src/guide/render.php" class="guide-card">
                        <div class="guide-card-icon" style="background:#F2F0FB;color:#5A4DB8;"><i class="bi bi-stars"></i></div>
                        <div class="guide-card-body">
                            <div class="guide-card-title">AI 렌더링</div>
                            <div class="guide-card-desc">배경 이미지와 도면을 합성해 AI로 공간을 시각화합니다.</div>
                        </div>
                        <i class="bi bi-arrow-right guide-card-arrow"></i>
                    </a>
                    <a href="/src/guide/collection.php" class="guide-card">
                        <div class="guide-card-icon" style="background:#FFF8EE;color:#b8894a;"><i class="bi bi-collection-fill"></i></div>
                        <div class="guide-card-body">
                            <div class="guide-card-title">컬렉션</div>
                            <div class="guide-card-desc">공개 라이브러리 패턴을 열람하고 내 보드에 저장하세요.</div>
                        </div>
                        <i class="bi bi-arrow-right guide-card-arrow"></i>
                    </a>
                    <a href="/src/guide/account.php" class="guide-card">
                        <div class="guide-card-icon" style="background:#EEF3F8;color:#2A6B8C;"><i class="bi bi-person-gear"></i></div>
                        <div class="guide-card-body">
                            <div class="guide-card-title">계정 설정</div>
                            <div class="guide-card-desc">프로필, 비밀번호, 회사 정보를 관리하는 방법을 안내합니다.</div>
                        </div>
                        <i class="bi bi-arrow-right guide-card-arrow"></i>
                    </a>
                    <a href="/src/guide/order.php" class="guide-card">
                        <div class="guide-card-icon" style="background:#FDF0E6;color:#B8662F;"><i class="bi bi-cart-check"></i></div>
                        <div class="guide-card-body">
                            <div class="guide-card-title">주문</div>
                            <div class="guide-card-desc">콘텐츠 준비 중입니다.</div>
                        </div>
                        <i class="bi bi-arrow-right guide-card-arrow"></i>
                    </a>
                    <a href="/src/guide/delivery.php" class="guide-card">
                        <div class="guide-card-icon" style="background:#EAF3FB;color:#2E6FA8;"><i class="bi bi-truck"></i></div>
                        <div class="guide-card-body">
                            <div class="guide-card-title">배송</div>
                            <div class="guide-card-desc">콘텐츠 준비 중입니다.</div>
                        </div>
                        <i class="bi bi-arrow-right guide-card-arrow"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- FAQ -->
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
                        </h2>
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

        <!-- FOOTER -->
        <div class="site-footer border-top px-4 py-4">
            <p class="footer-copy mb-0">©
                <?= date('Y') ?>
                평목(平木). All rights reserved.</p>
            <div class="footer-cta-center">
                <p class="footer-cta-sub">설계 문의, 제작 상담, 협업 제안 모두 환영합니다.</p>
                <a href="/src/company/#contact" class="footer-cta-link">함께 만들어가요. <i class="bi bi-arrow-up-right"></i></a>
            </div>
            <a href="https://pyeongmok.com" class="footer-link">pyeongmok.com</a>
        </div>

    </body>

</html>