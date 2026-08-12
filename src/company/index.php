<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/db.php';
try {
    $studioCards = db()->query('SELECT * FROM studio_cards WHERE is_active=1 ORDER BY sort_order, id')->fetchAll();
} catch (Exception $e) {
    $studioCards = [];
}
try {
    $heroSlides = db()->query('SELECT * FROM hero_slides WHERE is_active=1 ORDER BY sort_order, id')->fetchAll();
} catch (Exception $e) {
    $heroSlides = [];
}
$cardsByKey = [];
foreach ($studioCards as $sc) $cardsByKey[$sc['engine_key']] = $sc;
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once __DIR__ . '/../lib/meta.php'; meta_tags(); ?>
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php css_tag('/src/css/company.css'); ?>
    
</head>
<body>

<?php include __DIR__ . '/../components/nav.php'; ?>

<!-- HERO (full-width, outside container) -->
<section class="ab-hero">
    <div class="ab-hero-bg"></div>
    <div class="ab-hero-overlay"></div>
    <div class="ab-hero-content">
        <div class="container">
            <p class="ab-hero-label">About 평목</p>
            <h1 class="ab-hero-title">나무로 만드는<br>빛과 바람의 길,<em>평목</em></h1>
            <p class="ab-hero-desc">평목(平木)은 전통창호의 아름다움을 현대 공간에 담아내는 창호디자인 스튜디오입니다. <br>
            수백 년을 이어온 문살 기법을 디지털 도구로 재해석하여, 누구나 자신만의 창호를 <br>
            직접 설계하고 제작까지 연결할 수 있는 환경을 만들어갑니다.</p>
        </div>
    </div>
</section>

<!-- PHILOSOPHY — full-width box -->
<div class="ab-phil-box" id="philosophy">
    <div class="container">
        <p class="ab-section-label">Philosophy</p>
        <div class="ab-phil-cols">
            <div class="ab-phil-left">
                <h2 class="ab-section-title">평목(平木)<br><span style="font-size:0.6em;font-weight:400;color:var(--text-muted);letter-spacing:0.05em;">workgroup pyeongmok</span></h2>
                <div class="ab-phil-text">
                    <p>곡식을 '되'나 '말' 단위로 깎아낼 때 쓰는<br>도구를 '평미레'라고 하는데<br>여기에 해당하는 한자어가 '평목(平木)'입니다.</p>
                    <p>평목은 넘쳐도 아니 되고,<br>모자라도 아니 되는,<br>균형을 잡아 주는 일을 합니다.</p>
                    <p>견고한 나무를 다듬는 평목의 목수들은<br>시간에 마모되지 않고 시간 속에서<br>품격이 더해지는 창호와 가구를 만들고자 합니다.</p>
                </div>
            </div>
            <div class="ab-phil-right">
                <div class="ab-phil-grid">
                    <div class="ab-phil-item">
                        <p class="ab-phil-num">01</p>
                        <h3 class="ab-phil-name">오랜 시간 축적된 지혜와 다듬어진 원리를 공간에 담습니다.</h3>
                        <p class="ab-phil-desc">어금육모, 솟을살, 아자살 등 수백 년을 이어온 전통 문살 기법을 현대적 감각으로 해석합니다. <br><strong>형태는 단순해지고, 정신은 깊어집니다.</strong></p>
                    </div>
                    <div class="ab-phil-item">
                        <p class="ab-phil-num">02</p>
                        <h3 class="ab-phil-name">실시간 설계</h3>
                        <p class="ab-phil-desc">치수와 비율을 조정하는 순간 도면이 실시간으로 완성됩니다. <br>설계와 제작 사이의 거리를 최소화하여 아이디어가 바로 현실이 됩니다.</p>
                    </div>
                    <div class="ab-phil-item">
                        <p class="ab-phil-num">03</p>
                        <h3 class="ab-phil-name">맞춤 제작</h3>
                        <p class="ab-phil-desc">모든 창호는 공간과 사람에 맞게 설계됩니다. <br>완성된 도면은 실제 제작으로 이어지며, 세상에 하나뿐인 창호가 탄생합니다.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hero Carousel (Philosophy 아래) -->
<?php if (!empty($heroSlides)): ?>
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
<?php endif; ?>

<!-- STUDIO -->
<div class="container">
    <section class="ab-section" id="studio">
        <p class="ab-section-label">Studio</p>
        <h2 class="ab-section-title">직접 설계해 보세요.</h2>
        <p class="ab-section-body">평목 스튜디오는 브라우저에서 바로 사용할 수 있는 <strong>창호 설계 도구</strong>입니다. <br>문틀 크기, 살 간격, 패턴을 조정하며 나만의 창호를 완성해 보세요. 완성한 설계는 전통 창호 기법 그대로, 평목 공방에서 제작됩니다.</p>
        <div class="ab-tools-grid">
            <a href="/src/engine/classic/classic.php" class="ab-tool-card">
                <svg class="ab-tool-icon" width="48" height="48" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                    <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="4"/>
                    <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="4"/>
                    <rect fill="currentColor" x="148" y="148" width="46" height="384" rx="4"/>
                    <rect fill="currentColor" x="294" y="148" width="46" height="384" rx="4"/>
                    <rect fill="currentColor" x="486" y="148" width="46" height="384" rx="4"/>
                </svg>
                <div>
                    <p class="ab-tool-name"><?= htmlspecialchars($cardsByKey['classic']['title'] ?? 'Classic Lattice') ?></p>
                    <p class="ab-tool-desc"><?= $cardsByKey['classic']['description'] ?? '전통 창호의 기본이 되는 격자 문살 패턴.<br>균형 잡힌 비례와 절제된 구조가 특징입니다.' ?></p>
                </div>
            </a>
            <a href="/src/engine/square/square.php" class="ab-tool-card">
                <svg class="ab-tool-icon" width="48" height="48" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                    <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="4"/>
                    <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="4"/>
                    <rect fill="currentColor" x="204" y="148" width="46" height="384" rx="4"/>
                    <rect fill="currentColor" x="430" y="148" width="46" height="384" rx="4"/>
                </svg>
                <div>
                    <p class="ab-tool-name"><?= htmlspecialchars($cardsByKey['square']['title'] ?? 'Square Lattice') ?></p>
                    <p class="ab-tool-desc"><?= $cardsByKey['square']['description'] ?? '가로살과 세로살이 직각으로 교차하는 정방형 문살 패턴.<br>단아하고 절제된 아름다움을 표현합니다.' ?></p>
                </div>
            </a>
            <a href="/src/engine/cross/cross.php" class="ab-tool-card">
                <svg class="ab-tool-icon" width="48" height="48" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                    <g transform="rotate(45 340 340)">
                        <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="4"/>
                        <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="4"/>
                        <rect fill="currentColor" x="204" y="148" width="46" height="384" rx="4"/>
                        <rect fill="currentColor" x="430" y="148" width="46" height="384" rx="4"/>
                    </g>
                </svg>
                <div>
                    <p class="ab-tool-name"><?= htmlspecialchars($cardsByKey['cross']['title'] ?? 'Cross Lattice') ?></p>
                    <p class="ab-tool-desc"><?= $cardsByKey['cross']['description'] ?? '45° 대각선으로 교차하는 마름모 문살 패턴.<br>역동적인 사선의 흐름이 공간에 긴장감을 더합니다.' ?></p>
                </div>
            </a>
            <a href="/src/engine/diamond/diamond.php" class="ab-tool-card">
                <svg class="ab-tool-icon" width="48" height="48" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                    <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/>
                    <rect fill="currentColor" x="148" y="317" width="384" height="46" rx="4"/>
                    <g transform="rotate(45 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/></g>
                    <g transform="rotate(135 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/></g>
                </svg>
                <div>
                    <p class="ab-tool-name"><?= htmlspecialchars($cardsByKey['diamond']['title'] ?? 'Diamond Lattice') ?></p>
                    <p class="ab-tool-desc"><?= $cardsByKey['diamond']['description'] ?? '4방향 살이 대각선을 포함해 방사형으로 교차하는 패턴.<br>화려하고 입체적인 구조감을 연출합니다.' ?></p>
                </div>
            </a>
            <a href="/src/engine/triangle/triangle.php" class="ab-tool-card">
                <svg class="ab-tool-icon" width="48" height="48" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                    <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/>
                    <g transform="rotate(60 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/></g>
                    <g transform="rotate(120 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/></g>
                </svg>
                <div>
                    <p class="ab-tool-name"><?= htmlspecialchars($cardsByKey['triangle']['title'] ?? 'Triangle Lattice') ?></p>
                    <p class="ab-tool-desc"><?= $cardsByKey['triangle']['description'] ?? "수직살과 좌우 빗살, 세 방향의 살대가 한 점에서 만나도록 짠 세모 솟을살을 재현한 엔진입니다. '솟을'은 살이 교차점에서 겹치며 위로 솟아오르는 데서 온 이름으로, 교차점마다 살이 도드라져 짜임에 입체감이 살아 있습니다. 살들이 교차하며 정삼각형이 화면 가득 반복되어, 육모의 둥글고 넉넉한 인상과 달리 팽팽하고 긴장감 있는 느낌을 줍니다. 모든 셀이 정삼각형이 되도록 세로 칸수가 자동으로 계산되며, 세로 칸수를 직접 지정할 수는 없습니다." ?></p>
                </div>
            </a>
            <a href="/src/engine/hexagon/hexagon.php" class="ab-tool-card">
                <svg class="ab-tool-icon" width="48" height="48" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                    <polyline points="210,265 340,190 470,265" fill="none" stroke="currentColor" stroke-width="46" stroke-linejoin="round" stroke-linecap="round"/>
                    <line x1="210" y1="265" x2="210" y2="415" stroke="currentColor" stroke-width="46" stroke-linecap="round"/>
                    <line x1="470" y1="265" x2="470" y2="415" stroke="currentColor" stroke-width="46" stroke-linecap="round"/>
                    <line x1="210" y1="415" x2="340" y2="490" stroke="currentColor" stroke-width="46" stroke-linecap="round"/>
                    <line x1="470" y1="415" x2="340" y2="490" stroke="currentColor" stroke-width="46" stroke-linecap="round"/>
                </svg>
                <div>
                    <p class="ab-tool-name"><?= htmlspecialchars($cardsByKey['hexagon']['title'] ?? 'Hexagon Lattice') ?></p>
                    <p class="ab-tool-desc"><?= $cardsByKey['hexagon']['description'] ?? "세모솟을살과 같은 세 방향 살대를 쓰되, 교차점을 한 점에 모으지 않고 어긋나게 짜 육각형이 열리도록 한 육모 솟을살을 재현한 엔진입니다. 어금육모라고도 부릅니다. '솟을'은 살이 교차점에서 겹치며 위로 솟아오르는 데서 온 이름으로, 짜임에 입체감이 살아 있습니다. 살이 만드는 벌집 모양의 여섯 각은 사각보다 원에 가까워, 같은 짜임인데도 세모의 팽팽함 대신 둥글고 넉넉한 인상을 줍니다." ?></p>
                </div>
            </a>
        </div>
    </section>
</div>

<!-- INFO — full-width box -->
<div class="ab-contact-box" id="contact">
    <div class="container">
        <p class="ab-section-label">Contact</p>
        <h2 class="ab-section-title">함께 만들어가요.</h2>
        <p class="ab-section-body">작은 문의도 괜찮습니다.<br>설계·제작·설치 상담부터 협업 및 프로젝트 제안까지 모두 환영합니다.<br>편하게 연락해 주세요. 빠르게 답변드리겠습니다.</p>
        <button class="ab-contact-btn" data-bs-toggle="modal" data-bs-target="#contactModal">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            이메일 문의하기
        </button>

        <div class="ab-contact-cols">
            <div class="ab-contact-info">
                <dl class="ab-contact-dl">
                    <div class="ab-contact-row">
                        <dt>주소</dt>
                        <dd>경기도 양평군 양서면 도곡리 107-2</dd>
                    </div>
                    <div class="ab-contact-row">
                        <dt>전화</dt>
                        <dd><a href="tel:+827051244568">070-5124-4568</a></dd>
                    </div>
                    <div class="ab-contact-row">
                        <dt>이메일</dt>
                        <dd><button type="button" class="ab-contact-email-btn" data-bs-toggle="modal" data-bs-target="#contactModal">pyeongmok@gmail.com</button></dd>
                    </div>
                    <div class="ab-contact-row">
                        <dt>Instagram</dt>
                        <dd><a href="https://instagram.com/pyeongmok_1" target="_blank">@pyeongmok_1</a></dd>
                    </div>
                </dl>
            </div>
            <div class="ab-contact-map">
                <iframe
                    src="https://maps.google.com/maps?q=경기도+양평군+양서면+도곡리+107-2&output=embed&z=15"
                    width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>

        <div class="ab-gallery">
            <div class="ab-gallery-item">
                <img src="/src/img/web/IMG_0313.jpeg" alt="공방 사진 1">
            </div>
            <div class="ab-gallery-item">
                <img src="/src/img/web/IMG_0315.jpeg" alt="공방 사진 2">
            </div>
            <div class="ab-gallery-item">
                <img src="/src/img/web/IMG_0889.jpeg" alt="공방 사진 4">
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>

<?php include __DIR__ . '/../components/contact_modal.php'; ?>
</body>
</html>
