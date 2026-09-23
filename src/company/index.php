<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/i18n.php';
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

// studio_cards.title/description은 자유서술형 긴 글이라 용어집(term())으로 못 다루고,
// title_en/description_en 컬럼을 따로 관리한다(어드민 미입력 시 한글로 폴백).
function sc_title(array $cardsByKey, string $key, string $fallback): string {
    $sc = $cardsByKey[$key] ?? null;
    if (!$sc) return $fallback;
    return (is_en() && !empty($sc['title_en'])) ? $sc['title_en'] : $sc['title'];
}
function sc_desc(array $cardsByKey, string $key, string $fallbackKey): string {
    $sc = $cardsByKey[$key] ?? null;
    if (!$sc) return t($fallbackKey);
    if (is_en()) return !empty($sc['description_en']) ? $sc['description_en'] : t($fallbackKey);
    return $sc['description'];
}
?>
<!DOCTYPE html>
<html lang="<?= is_en() ? 'en' : 'ko' ?>">
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
            <p class="ab-hero-label"><?= htmlspecialchars(t('company_hero_label')) ?></p>
            <h1 class="ab-hero-title"><?= t('company_hero_title') ?></h1>
            <p class="ab-hero-desc"><?= t('company_hero_desc') ?></p>
        </div>
    </div>
</section>

<!-- PHILOSOPHY — full-width box -->
<div class="ab-phil-box" id="philosophy">
    <div class="container">
        <p class="ab-section-label"><?= htmlspecialchars(t('company_phil_label')) ?></p>
        <div class="ab-phil-cols">
            <div class="ab-phil-left">
                <h2 class="ab-section-title"><?= htmlspecialchars(t('company_phil_title')) ?></h2>
                <div class="ab-phil-text">
                    <p><?= t('company_phil_p1') ?></p>
                    <p><?= t('company_phil_p2') ?></p>
                    <p><?= t('company_phil_p3') ?></p>
                </div>
            </div>
            <div class="ab-phil-right">
                <div class="ab-phil-grid">
                    <div class="ab-phil-item">
                        <p class="ab-phil-num">01</p>
                        <h3 class="ab-phil-name"><?= htmlspecialchars(t('company_phil_item1_title')) ?></h3>
                        <p class="ab-phil-desc"><?= t('company_phil_item1_desc') ?></p>
                    </div>
                    <div class="ab-phil-item">
                        <p class="ab-phil-num">02</p>
                        <h3 class="ab-phil-name"><?= htmlspecialchars(t('company_phil_item2_title')) ?></h3>
                        <p class="ab-phil-desc"><?= t('company_phil_item2_desc') ?></p>
                    </div>
                    <div class="ab-phil-item">
                        <p class="ab-phil-num">03</p>
                        <h3 class="ab-phil-name"><?= htmlspecialchars(t('company_phil_item3_title')) ?></h3>
                        <p class="ab-phil-desc"><?= t('company_phil_item3_desc') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- LIGHT & LATTICE — Philosophy 산문의 연장. 홈에서 옮겨온 섹션 -->
<div class="container">
    <section class="ab-section" id="light">
        <p class="ab-section-label"><?= htmlspecialchars(t('company_light_label')) ?></p>
        <h2 class="ab-section-title"><?= t('company_light_title') ?></h2>
        <div class="ab-prose">
            <div>
                <p><?= htmlspecialchars(t('company_light_p1a')) ?></p>
                <p><?= t('company_light_p1b') ?></p>
            </div>
            <div>
                <p><?= htmlspecialchars(t('company_light_p2a')) ?></p>
                <p><?= htmlspecialchars(t('company_light_p2b')) ?></p>
            </div>
        </div>
    </section>
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
                <img src="<?= htmlspecialchars($sl['image_url']) ?>" class="hero-slide-img" alt="<?= htmlspecialchars(db_field($sl, 'title')) ?>">
                <?php if ($sl['title'] || $sl['subtitle']): ?>
                <div class="hero-slide-caption">
                    <?php if ($sl['title']): ?>
                    <h2 class="hero-slide-title"><?= htmlspecialchars(db_field($sl, 'title')) ?></h2>
                    <?php endif; ?>
                    <?php if ($sl['subtitle']): ?>
                    <p class="hero-slide-sub"><?= htmlspecialchars(db_field($sl, 'subtitle')) ?></p>
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
        <p class="ab-section-label"><?= htmlspecialchars(t('company_studio_label')) ?></p>
        <h2 class="ab-section-title"><?= htmlspecialchars(t('company_studio_title')) ?></h2>
        <p class="ab-section-body"><?= t('company_studio_body') ?></p>
        <div class="ab-tools-grid">
            <a href="<?= lang_href('/src/engine/classic/classic.php') ?>" class="ab-tool-card">
                <svg class="ab-tool-icon" width="48" height="48" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                    <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="4"/>
                    <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="4"/>
                    <rect fill="currentColor" x="148" y="148" width="46" height="384" rx="4"/>
                    <rect fill="currentColor" x="294" y="148" width="46" height="384" rx="4"/>
                    <rect fill="currentColor" x="486" y="148" width="46" height="384" rx="4"/>
                </svg>
                <div>
                    <p class="ab-tool-name"><?= htmlspecialchars(sc_title($cardsByKey, 'classic', 'Classic Lattice')) ?></p>
                    <p class="ab-tool-desc"><?= sc_desc($cardsByKey, 'classic', 'home_engine_desc_classic') ?></p>
                </div>
            </a>
            <a href="<?= lang_href('/src/engine/square/square.php') ?>" class="ab-tool-card">
                <svg class="ab-tool-icon" width="48" height="48" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                    <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="4"/>
                    <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="4"/>
                    <rect fill="currentColor" x="204" y="148" width="46" height="384" rx="4"/>
                    <rect fill="currentColor" x="430" y="148" width="46" height="384" rx="4"/>
                </svg>
                <div>
                    <p class="ab-tool-name"><?= htmlspecialchars(sc_title($cardsByKey, 'square', 'Square Lattice')) ?></p>
                    <p class="ab-tool-desc"><?= sc_desc($cardsByKey, 'square', 'home_engine_desc_square') ?></p>
                </div>
            </a>
            <a href="<?= lang_href('/src/engine/cross/cross.php') ?>" class="ab-tool-card">
                <svg class="ab-tool-icon" width="48" height="48" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                    <g transform="rotate(45 340 340)">
                        <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="4"/>
                        <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="4"/>
                        <rect fill="currentColor" x="204" y="148" width="46" height="384" rx="4"/>
                        <rect fill="currentColor" x="430" y="148" width="46" height="384" rx="4"/>
                    </g>
                </svg>
                <div>
                    <p class="ab-tool-name"><?= htmlspecialchars(sc_title($cardsByKey, 'cross', 'Cross Lattice')) ?></p>
                    <p class="ab-tool-desc"><?= sc_desc($cardsByKey, 'cross', 'home_engine_desc_cross') ?></p>
                </div>
            </a>
            <a href="<?= lang_href('/src/engine/diamond/diamond.php') ?>" class="ab-tool-card">
                <svg class="ab-tool-icon" width="48" height="48" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                    <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/>
                    <rect fill="currentColor" x="148" y="317" width="384" height="46" rx="4"/>
                    <g transform="rotate(45 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/></g>
                    <g transform="rotate(135 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/></g>
                </svg>
                <div>
                    <p class="ab-tool-name"><?= htmlspecialchars(sc_title($cardsByKey, 'diamond', 'Diamond Lattice')) ?></p>
                    <p class="ab-tool-desc"><?= sc_desc($cardsByKey, 'diamond', 'home_engine_desc_diamond') ?></p>
                </div>
            </a>
            <a href="<?= lang_href('/src/engine/triangle/triangle.php') ?>" class="ab-tool-card">
                <svg class="ab-tool-icon" width="48" height="48" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                    <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/>
                    <g transform="rotate(60 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/></g>
                    <g transform="rotate(120 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/></g>
                </svg>
                <div>
                    <p class="ab-tool-name"><?= htmlspecialchars(sc_title($cardsByKey, 'triangle', 'Triangle Lattice')) ?></p>
                    <p class="ab-tool-desc"><?= sc_desc($cardsByKey, 'triangle', 'home_engine_desc_triangle') ?></p>
                </div>
            </a>
            <a href="<?= lang_href('/src/engine/hexagon/hexagon.php') ?>" class="ab-tool-card">
                <svg class="ab-tool-icon" width="48" height="48" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                    <polyline points="210,265 340,190 470,265" fill="none" stroke="currentColor" stroke-width="46" stroke-linejoin="round" stroke-linecap="round"/>
                    <line x1="210" y1="265" x2="210" y2="415" stroke="currentColor" stroke-width="46" stroke-linecap="round"/>
                    <line x1="470" y1="265" x2="470" y2="415" stroke="currentColor" stroke-width="46" stroke-linecap="round"/>
                    <line x1="210" y1="415" x2="340" y2="490" stroke="currentColor" stroke-width="46" stroke-linecap="round"/>
                    <line x1="470" y1="415" x2="340" y2="490" stroke="currentColor" stroke-width="46" stroke-linecap="round"/>
                </svg>
                <div>
                    <p class="ab-tool-name"><?= htmlspecialchars(sc_title($cardsByKey, 'hexagon', 'Hexagon Lattice')) ?></p>
                    <p class="ab-tool-desc"><?= sc_desc($cardsByKey, 'hexagon', 'home_engine_desc_hexagon') ?></p>
                </div>
            </a>
        </div>

        <div class="ab-common-features">
            <h3 class="ab-common-features-title"><?= htmlspecialchars(t('company_features_title')) ?></h3>
            <ul class="ab-common-features-list">
                <li><?= htmlspecialchars(t('company_feature1')) ?></li>
                <li><?= htmlspecialchars(t('company_feature2')) ?></li>
                <li><?= htmlspecialchars(t('company_feature3')) ?></li>
                <li><?= htmlspecialchars(t('company_feature4')) ?></li>
                <li><?= htmlspecialchars(t('company_feature5')) ?></li>
                <li><?= htmlspecialchars(t('company_feature6')) ?></li>
                <li><?= htmlspecialchars(t('company_feature7')) ?></li>
                <li class="ab-common-features-highlight"><?= htmlspecialchars(t('company_feature_highlight')) ?></li>
            </ul>
        </div>

        <div class="ab-studio-next">
            <p class="ab-studio-next-desc"><?= t('company_studio_next_desc') ?></p>
            <div class="ab-studio-next-btns">
                <a href="/collection/" class="ab-contact-btn"><?= htmlspecialchars(t('company_studio_btn1')) ?></a>
                <a href="/guide/order" class="ab-contact-btn ab-contact-btn--outline"><?= htmlspecialchars(t('company_studio_btn2')) ?></a>
            </div>
        </div>
    </section>
</div>

<!-- INFO — full-width box -->
<div class="ab-contact-box" id="contact">
    <div class="container">
        <p class="ab-section-label"><?= htmlspecialchars(t('home_contact_label')) ?></p>
        <h2 class="ab-section-title"><?= htmlspecialchars(t('company_contact_title')) ?></h2>
        <p class="ab-section-body"><?= t('company_contact_body') ?></p>
        <button class="ab-contact-btn" data-bs-toggle="modal" data-bs-target="#contactModal">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            <?= htmlspecialchars(t('company_contact_email_btn')) ?>
        </button>

        <div class="ab-contact-cols">
            <div class="ab-contact-info">
                <dl class="ab-contact-dl">
                    <div class="ab-contact-row">
                        <dt><?= htmlspecialchars(t('company_contact_address')) ?></dt>
                        <dd><?= htmlspecialchars(t('company_address_value')) ?></dd>
                    </div>
                    <div class="ab-contact-row">
                        <dt><?= htmlspecialchars(t('company_contact_phone')) ?></dt>
                        <dd><a href="tel:+827051244568">070-5124-4568</a></dd>
                    </div>
                    <div class="ab-contact-row">
                        <dt><?= htmlspecialchars(t('company_contact_email')) ?></dt>
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
                <img src="/src/img/web/IMG_0313.jpeg" alt="<?= htmlspecialchars(t('company_gallery_1')) ?>">
            </div>
            <div class="ab-gallery-item">
                <img src="/src/img/web/IMG_0315.jpeg" alt="<?= htmlspecialchars(t('company_gallery_2')) ?>">
            </div>
            <div class="ab-gallery-item">
                <img src="/src/img/web/IMG_0889.jpeg" alt="<?= htmlspecialchars(t('company_gallery_4')) ?>">
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>

<?php include __DIR__ . '/../components/contact_modal.php'; ?>
</body>
</html>
