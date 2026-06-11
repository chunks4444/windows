<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/src/lib/db.php';
$pdo        = db();
$heroSlides = $pdo->query('SELECT * FROM hero_slides WHERE is_active=1 ORDER BY sort_order, id')->fetchAll();
$spaceCards = $pdo->query('SELECT label, image_url, collection_query FROM space_cards WHERE is_active=1 ORDER BY sort_order, id')->fetchAll();
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

            <!-- Hero Carousel -->
            <div class="hero-carousel-outer">
              <div class="container">
                <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="4500">
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

                <div class="row g-4">
                    <div class="col-md-4 text-center">
                        <div class="service-card service-card--classic h-80 p-4">
                            <div class="mb-4" style="height:120px;display:flex;align-items:center;justify-content:center;">
                                <a href="/src/engine/classic/classic.php" class="pm-symbol-link">
                                    <svg
                                        width="56"
                                        height="84"
                                        viewBox="148 52 384 576"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <g transform="rotate(90 340 340)">
                                            <rect class="pm-symbol-bar" x="148" y="204" width="384" height="46" rx="0"/>
                                            <rect class="pm-symbol-bar" x="148" y="430" width="384" height="46" rx="0"/>
                                            <rect class="pm-symbol-bar" x="148" y="148" width="46" height="384" rx="0"/>
                                            <rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/>
                                            <rect class="pm-symbol-bar" x="486" y="148" width="46" height="384" rx="0"/>
                                            <!-- 세로선 인출 (좌우 촉 → 회전 후 상하 인출) -->
                                            <rect class="pm-symbol-bar" x="100" y="204" width="48" height="46" rx="0"/>
                                            <rect class="pm-symbol-bar" x="532" y="204" width="48" height="46" rx="0"/>
                                            <rect class="pm-symbol-bar" x="100" y="430" width="48" height="46" rx="0"/>
                                            <rect class="pm-symbol-bar" x="532" y="430" width="48" height="46" rx="0"/>
                                        </g>
                                    </svg>
                                </a>
                            </div>
                            <h4 class="service-title text-center mb-3">Classic Lattice</h4>
                            <p class="service-sub-text text-center mb-0">전통 창호의 기본이 되는 격자 문살 패턴.<br>균형 잡힌 비례와 절제된 구조가 특징입니다.</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="service-card service-card--square h-80 p-4">
                            <div class=" mb-4">
                                <a href="/src/engine/square/square.php" class="pm-symbol-link">
                                    <svg
                                        width="120"
                                        height="120"
                                        viewbox="0 0 680 680"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <rect class="pm-symbol-bar" x="148" y="204" width="384" height="46" rx="0"/>
                                        <rect class="pm-symbol-bar" x="148" y="430" width="384" height="46" rx="0"/>
                                        <rect class="pm-symbol-bar" x="204" y="148" width="46" height="384" rx="0"/>
                                        <rect class="pm-symbol-bar" x="430" y="148" width="46" height="384" rx="0"/>
                                    </svg>
                                </a>
                            </div>
                            <h4 class="service-title text-center mb-3">Square Lattice</h4>
                            <p class="service-sub-text text-center mb-0">가로살과 세로살이 직각으로 교차하는 정방형 문살 패턴.<br>단아하고 절제된 아름다움을 표현합니다.</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="service-card service-card--cross h-80 p-4">
                            <div class=" mb-4">
                                <a href="/src/engine/cross/cross.php" class="pm-symbol-link">
                                    <svg
                                        width="120"
                                        height="120"
                                        viewbox="0 0 680 680"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <g transform="rotate(45 340 340)">
                                            <rect class="pm-symbol-bar" x="148" y="204" width="384" height="46" rx="0"/>
                                            <rect class="pm-symbol-bar" x="148" y="430" width="384" height="46" rx="0"/>
                                            <rect class="pm-symbol-bar" x="204" y="148" width="46" height="384" rx="0"/>
                                            <rect class="pm-symbol-bar" x="430" y="148" width="46" height="384" rx="0"/>
                                        </g>
                                    </svg>
                                </a>
                            </div>
                            <h4 class="service-title text-center mb-3">Cross Lattice</h4>
                            <p class="service-sub-text text-center mb-0">45° 대각선으로 교차하는 마름모 문살 패턴.<br>역동적인 사선의 흐름이 공간에 긴장감을 더합니다.</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="service-card service-card--triangle h-80 p-4">
                            <div class=" mb-4">
                                <a href="/src/engine/triangle/triangle.php" class="pm-symbol-link">
                                    <svg
                                        width="120"
                                        height="120"
                                        viewbox="0 0 680 680"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/>
                                        <g transform="rotate(60 340 340)">
                                            <rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/>
                                        </g>
                                        <g transform="rotate(120 340 340)">
                                            <rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/>
                                        </g>
                                    </svg>
                                </a>
                            </div>
                            <h4 class="service-title text-center mb-3">Triangle Lattice</h4>
                            <p class="service-sub-text text-center mb-0">세 방향의 살이 60° 각도로 교차하는 삼각형 문살 패턴.<br>역동적이고 세련된 인상을 공간에 더합니다.</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="service-card service-card--diamond h-80 p-4">
                            <div class="text-center mb-4">
                                <a href="/src/engine/diamond/diamond.php" class="pm-symbol-link">
                                    <svg
                                        width="120"
                                        height="120"
                                        viewbox="0 0 680 680"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/>
                                        <rect class="pm-symbol-bar" x="148" y="317" width="384" height="46" rx="0"/>
                                        <g transform="rotate(45 340 340)">
                                            <rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/>
                                        </g>
                                        <g transform="rotate(135 340 340)">
                                            <rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/>
                                        </g>
                                    </svg>
                                </a>
                            </div>
                            <h4 class="service-title text-center mb-3">Diamond Lattice</h4>
                            <p class="service-sub-text text-center mb-0">4방향 살이 대각선을 포함해 방사형으로 교차하는 패턴.<br>화려하고 입체적인 구조감을 연출합니다.</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="service-card service-card--hexagon h-80 p-4">
                            <div class="text-center mb-4">
                                <a href="/src/engine/hexagon/hexagon.php" class="pm-symbol-link">
                                    <svg
                                        width="120"
                                        height="120"
                                        viewBox="0 0 680 680"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <polyline points="210,265 340,190 470,265" stroke-width="32" stroke-linejoin="round" stroke-linecap="round" class="pm-symbol-stroke"/>
                                        <line x1="210" y1="265" x2="210" y2="415" stroke-width="32" stroke-linecap="round" class="pm-symbol-stroke"/>
                                        <line x1="470" y1="265" x2="470" y2="415" stroke-width="32" stroke-linecap="round" class="pm-symbol-stroke"/>
                                        <line x1="210" y1="415" x2="340" y2="490" stroke-width="32" stroke-linecap="round" class="pm-symbol-stroke"/>
                                        <line x1="470" y1="415" x2="340" y2="490" stroke-width="32" stroke-linecap="round" class="pm-symbol-stroke"/>
                                    </svg>
                                </a>
                            </div>
                            <h4 class="service-title text-center mb-3">Hexagon Lattice</h4>
                            <p class="service-sub-text text-center mb-0">세 방향의 살이 서로 맞물려 균일한 육각형 눈을 이루는 어금육모 패턴.<br>자연의 벌집 구조를 닮은 단정하고 견고한 전통미를 담아냅니다.</p>
                        </div>
                    </div>
                </div>
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
                        <a class="space-card" href="/src/collection/?q=<?= urlencode($sc['collection_query']) ?>">
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
                        <i class="bi bi-pencil-square process-icon"></i>
                        <h3 class="process-title">패턴 설계</h3>
                        <p class="process-desc">상단 Studio 메뉴에서 원하는<br>창호 패턴을 선택하세요.</p>
                        <ul class="process-hints">
                            <li><i class="bi bi-check2"></i> 문틀 가로·세로 크기 입력</li>
                            <li><i class="bi bi-check2"></i> 살 간격·두께 슬라이더 조정</li>
                            <li><i class="bi bi-check2"></i> 실시간으로 결과 확인</li>
                        </ul>
                    </div>
                    <div class="process-arrow">
                        <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </div>
                    <div class="process-step">
                        <span class="process-num">02</span>
                        <i class="bi bi-bookmark-heart process-icon"></i>
                        <h3 class="process-title">저장 & 탐색</h3>
                        <p class="process-desc">완성된 도면을 저장하고<br>컬렉션에서 영감을 찾아보세요.</p>
                        <ul class="process-hints">
                            <li><i class="bi bi-check2"></i> 도면 저장 후 내 도면에서 관리</li>
                            <li><i class="bi bi-check2"></i> 컬렉션에서 다양한 패턴 탐색</li>
                            <li><i class="bi bi-check2"></i> 보드에 마음에 드는 패턴 모으기</li>
                        </ul>
                    </div>
                    <div class="process-arrow">
                        <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </div>
                    <div class="process-step">
                        <span class="process-num">03</span>
                        <i class="bi bi-chat-heart process-icon"></i>
                        <h3 class="process-title">제작 주문</h3>
                        <p class="process-desc">완성된 설계를 가지고<br>평목 공방에 제작을 주문하세요.</p>
                        <ul class="process-hints">
                            <li><i class="bi bi-check2"></i> 도면 오른쪽 상단 주문버튼 클릭</li>
                            <li><i class="bi bi-check2"></i> 저장한 도면 기반으로 상담</li>
                            <li><i class="bi bi-check2"></i> 설계·제작·협업 모두 환영</li>
                        </ul>
                    </div>
                </div>
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