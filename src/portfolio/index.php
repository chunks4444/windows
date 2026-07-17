<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/engine_icons.php';
try {
$pdo = db();

$pdo->exec("
CREATE TABLE IF NOT EXISTS works (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL DEFAULT '',
    description VARCHAR(300) NOT NULL DEFAULT '',
    image_url VARCHAR(500) NOT NULL DEFAULT '',
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    KEY idx_works_sort (sort_order, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS work_images (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    work_id INT UNSIGNED NOT NULL,
    image_url VARCHAR(500) NOT NULL DEFAULT '',
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_work_images_work (work_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

if ((int)$pdo->query('SELECT COUNT(*) FROM works')->fetchColumn() === 0) {
    $stmt = $pdo->prepare('INSERT INTO works (title, description, image_url, sort_order) VALUES (?,?,?,?)');
    foreach ([
        ['한옥 중문 정자살', '경기도 양평',  'https://picsum.photos/seed/w1/500/700', 0],
        ['거실 미서기창',    '서울 마포',    'https://picsum.photos/seed/w2/500/680', 1],
        ['카페 파티션',      '서울 성수',    'https://picsum.photos/seed/w3/500/720', 2],
        ['서재 창호',        '경기 용인',    'https://picsum.photos/seed/w4/500/660', 3],
        ['현관 중문',        '서울 강남',    'https://picsum.photos/seed/w5/500/700', 4],
        ['다실 창호',        '전남 순천',    'https://picsum.photos/seed/w6/500/690', 5],
        ['침실 여닫이창',    '부산 해운대',  'https://picsum.photos/seed/w7/500/710', 6],
        ['갤러리 파티션',    '서울 종로',    'https://picsum.photos/seed/w8/500/680', 7],
    ] as $s) $stmt->execute($s);
}

if ((int)$pdo->query('SELECT COUNT(*) FROM work_images')->fetchColumn() === 0) {
    $works_all = $pdo->query('SELECT id, sort_order FROM works WHERE is_active=1 ORDER BY sort_order, id')->fetchAll();
    $imgStmt   = $pdo->prepare('INSERT INTO work_images (work_id, image_url, sort_order) VALUES (?,?,?)');
    $sizes = [[600,800],[800,600],[600,800],[800,500],[500,700]];
    foreach ($works_all as $w) {
        $pfx = 'wi' . $w['id'];
        foreach ($sizes as $j => [$width, $height]) {
            $imgStmt->execute([$w['id'], "https://picsum.photos/seed/{$pfx}_{$j}/{$width}/{$height}", $j]);
        }
    }
}

$works = $pdo->query('SELECT * FROM works WHERE is_active=1 ORDER BY sort_order, id')->fetchAll();
$total = count($works);

// 작품별 이미지 목록 (디테일 모달용, 없으면 대표 이미지 하나로 대체)
$workImages = [];
if ($works) {
    $ids = array_column($works, 'id');
    $in  = implode(',', array_fill(0, count($ids), '?'));
    $imgStmt = $pdo->prepare("SELECT work_id, image_url FROM work_images WHERE work_id IN ($in) ORDER BY sort_order, id");
    $imgStmt->execute($ids);
    foreach ($imgStmt->fetchAll() as $row) {
        $workImages[$row['work_id']][] = $row['image_url'];
    }
}

// 태그 테이블 생성 + 시드
$pdo->exec("
CREATE TABLE IF NOT EXISTS work_tags (
    id         INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    name       VARCHAR(50)       NOT NULL DEFAULT '',
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active  TINYINT(1)        NOT NULL DEFAULT 1,
    created_at DATETIME          NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    KEY idx_wt_sort (sort_order, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
if ((int)$pdo->query('SELECT COUNT(*) FROM work_tags')->fetchColumn() === 0) {
    $ts = $pdo->prepare('INSERT INTO work_tags (name, sort_order) VALUES (?,?)');
    foreach (['중문','거실','카페','서재','현관','다실','침실','갤러리','한옥','파티션'] as $i => $t)
        $ts->execute([$t, $i]);
}
$tags = array_merge(['전체'], $pdo->query('SELECT name FROM work_tags WHERE is_active=1 ORDER BY sort_order, id')->fetchAll(PDO::FETCH_COLUMN));
} catch (Throwable $e) {
    $works = [];
    $total = 0;
    $tags  = ['전체'];
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once __DIR__ . '/../lib/meta.php'; meta_tags(); ?>
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php css_tag('/src/css/work.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>
<style>
/* 포트폴리오 페이지 전용 — 상단 공지 띠배너 숨김 (배너가 있을 때 nav.php가 강제로 밀어내는 top값도 함께 되돌림) */
#pmTopbarNotice { display: none !important; }

/* 상단 네비는 배경 없이 사진 위에 그대로 떠 있게 함 */
.pm-navbar {
    top: 0 !important;
    background: transparent !important;
    border-bottom: none !important;
}
.pm-navbar .nav-link { color: #fff !important; opacity: .82; }
.pm-navbar .nav-link:hover,
.pm-navbar .nav-link.active,
.pm-navbar .nav-link[aria-expanded="true"] { color: #fff !important; opacity: 1; }
.pm-navbar .nav-link::before { background: #fff !important; }
.pm-navbar .pm-nav-logo { filter: brightness(0) invert(1) !important; opacity: .95; }
.pm-menu-trigger,
.pm-filter-trigger { color: #fff !important; }

/* Menu/Filter 버튼을 좌우 캐러셀 화살표 바로 위로 재배치 */
.pm-menu-trigger {
    position: fixed; left: 20px; top: calc(50% - 34px);
    transform: translateY(-100%);
    z-index: 1000;
}
.pm-filter-trigger {
    position: fixed; right: 20px; top: calc(50% - 34px);
    transform: translateY(-100%);
    z-index: 1000;
    margin-left: 0;
}
</style>

<div class="wk-page">

    <div class="wk-top-scrim"></div>
    <div class="wk-intro-overlay"></div>
    <div class="wk-fade-overlay" id="wkFadeOverlay"></div>
    <div class="wk-intro-line"></div>
    <div class="wk-intro-line-head"></div>

    <!-- ── 페이지 헤더 ── -->
    <div class="wk-hero">
        <div class="wk-hero-inner">
            <p class="wk-hero-label">Portfolio</p>
            <h1>포트폴리오</h1>
            <p class="wk-hero-sub">
                평목 공방에서 완성된 작품들입니다.<br>
                <span class="wk-count-badge"><?= $total ?>개 작품</span>
            </p>
        </div>
    </div>

    <!-- ── 가로 스크롤 캐러셀 (풀스크린) ── -->
    <section class="wk-carousel-section">
        <button class="wk-nav-arrow prev" id="wkPrev" aria-label="이전"></button>
        <button class="wk-nav-arrow next" id="wkNext" aria-label="다음"></button>

        <div class="wk-carousel" id="wkCarousel">
            <?php foreach ($works as $i => $w):
                $desc = strip_tags($w['description']);
                $icon = engine_icon_svg($w['engine_key'] ?? '');
                $images = $workImages[$w['id']] ?? ($w['image_url'] ? [$w['image_url']] : []);
            ?>
            <div class="wk-slide<?= $i === 0 ? ' is-active' : '' ?>"
                 data-title="<?= htmlspecialchars($w['title']) ?>"
                 data-desc="<?= htmlspecialchars($desc) ?>"
                 data-images="<?= htmlspecialchars(json_encode($images)) ?>"
                 data-panel-bg="<?= htmlspecialchars($w['panel_bg'] ?: '#111111') ?>"
                 data-title-color="<?= htmlspecialchars($w['title_color'] ?: '#ffffff') ?>"
                 data-desc-color="<?= htmlspecialchars($w['desc_color'] ?: '#888888') ?>"
                 role="button">

                <img class="wk-slide-photo"
                     src="<?= htmlspecialchars($w['image_url']) ?>"
                     alt="<?= htmlspecialchars($w['title']) ?>"
                     loading="lazy">
                <div class="wk-slide-scrim"></div>
                <?php if ($icon): ?>
                <span class="wk-slide-icon"><?= $icon ?></span>
                <?php endif; ?>

                <div class="wk-slide-info">
                    <?php if ($desc): ?>
                    <div class="wk-slide-eyebrow"><?= htmlspecialchars($desc) ?></div>
                    <?php endif; ?>
                    <span class="wk-slide-rule"></span>
                    <h2 class="wk-slide-title"><?= htmlspecialchars($w['title']) ?></h2>
                    <span class="wk-slide-rule"></span>
                    <div class="wk-slide-foot">
                        <span class="wk-slide-num"><?= sprintf('%02d', $i + 1) ?> / <?= sprintf('%02d', $total) ?></span>
                        <span class="wk-slide-plus">+</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="wk-empty" id="wkEmpty" style="display:none;">
            해당 카테고리의 작품이 없습니다.
        </div>
    </section>

</div>

<!-- ── 디테일 뷰 모달 (네비게이션 없이 이미지·제목만) ── -->
<div class="wk-modal" id="wkModal" aria-hidden="true">
    <button class="wk-modal-close" id="wkModalClose" aria-label="닫기">&times;</button>
    <div class="wk-modal-viewer">
        <button class="wk-modal-arrow prev" id="wkModalPrev" aria-label="이전 사진"></button>
        <img class="wk-modal-img" id="wkModalImg" src="" alt="">
        <button class="wk-modal-arrow next" id="wkModalNext" aria-label="다음 사진"></button>
    </div>
    <div class="wk-modal-foot">
        <span class="wk-modal-eyebrow">Portfolio</span>
        <h2 class="wk-modal-title" id="wkModalTitle"></h2>
        <span class="wk-modal-rule"></span>
        <p class="wk-modal-desc" id="wkModalDesc"></p>
        <span class="wk-modal-counter" id="wkModalCounter"></span>
    </div>
    <div class="wk-modal-thumbs" id="wkModalThumbs"></div>
</div>

<!-- ── 키워드 필터 사이드 패널 (우측, 메뉴 드로어와 동일 디자인) ── -->
<div class="pm-nav-drawer-backdrop" id="wkFilterBackdrop"></div>
<div class="pm-nav-drawer" id="wkFilterPanel" aria-hidden="true">
    <div class="pm-dw-head">
        <span class="wk-filter-panel-title"><i class="bi bi-sliders"></i> Filter</span>
        <button class="pm-dw-close" id="wkFilterPanelClose" aria-label="닫기">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="pm-dw-body">
        <?php foreach ($tags as $i => $tag): ?>
        <button class="pm-dw-link-top wk-tag<?= $i === 0 ? ' active' : '' ?>"
                data-tag="<?= htmlspecialchars($tag) ?>">
            <i class="bi <?= $i === 0 ? 'bi-grid' : 'bi-tag' ?>"></i>
            <span><?= htmlspecialchars($tag) ?></span>
        </button>
        <?php endforeach; ?>
    </div>
</div>

<script>
(function () {
    const tags     = document.querySelectorAll('.wk-tag');
    const carousel = document.getElementById('wkCarousel');
    const slides   = Array.from(document.querySelectorAll('.wk-slide'));
    const emptyEl  = document.getElementById('wkEmpty');
    const prevBtn  = document.getElementById('wkPrev');
    const nextBtn  = document.getElementById('wkNext');
    const hero     = document.querySelector('.wk-hero');

    // 좌측 여백에 있는 헤더는 스크롤해서 실제 사진이 그 자리에 오면 자연스럽게 사라지게 함
    function fadeHero() {
        const fadeDistance = carousel.clientWidth * 0.28;
        hero.style.opacity = Math.max(0, 1 - carousel.scrollLeft / fadeDistance);
        hero.style.pointerEvents = carousel.scrollLeft > 4 ? 'none' : 'auto';
    }
    fadeHero();

    // 활성(중앙) 슬라이드 판정 — 슬라이드 3개가 뷰포트에 항상 꽉 차게 배치되므로
    // IntersectionObserver의 ratio만으로는 여러 개가 동시에 "가득 보임"으로 잡힌다.
    // 캐러셀 중심에 가장 가까운 슬라이드 하나를 활성으로 고정하는 방식으로 판정한다.
    let rafPending = false;
    function updateActive() {
        const box = carousel.getBoundingClientRect();
        const centerX = box.left + box.width / 2;
        let closest = null, closestDist = Infinity;
        slides.forEach(s => {
            if (s.classList.contains('wk-hidden')) return;
            const r = s.getBoundingClientRect();
            const dist = Math.abs((r.left + r.width / 2) - centerX);
            if (dist < closestDist) { closestDist = dist; closest = s; }
        });
        slides.forEach(s => s.classList.toggle('is-active', s === closest));
    }
    carousel.addEventListener('scroll', () => {
        if (rafPending) return;
        rafPending = true;
        requestAnimationFrame(() => { updateActive(); fadeHero(); rafPending = false; });
    });
    updateActive();

    function goToSlide(el) {
        el.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
    }

    slides.forEach(slide => {
        slide.addEventListener('click', () => {
            if (slide.classList.contains('is-active')) {
                openModal(slide);
            } else {
                goToSlide(slide);
            }
        });
    });

    function visibleSlides() { return slides.filter(s => !s.classList.contains('wk-hidden')); }

    // ── 자동 슬라이드 — 사용자가 조작(호버/터치/화살표/모달)하면 멈춘다 ──
    const AUTOPLAY_MS = 4500;
    const fadeOverlay = document.getElementById('wkFadeOverlay');
    let autoplayTimer = null;
    function wrapToFirst(vs) {
        fadeOverlay.classList.add('show');
        setTimeout(() => {
            vs[0].scrollIntoView({ behavior: 'instant', inline: 'center' });
            updateActive();
            requestAnimationFrame(() => fadeOverlay.classList.remove('show'));
        }, 950);
    }
    function autoAdvance() {
        const vs = visibleSlides();
        if (vs.length < 2) return;
        const cur = vs.findIndex(s => s.classList.contains('is-active'));
        if (cur === vs.length - 1) { wrapToFirst(vs); return; }
        goToSlide(vs[cur + 1]);
    }
    function startAutoplay() {
        stopAutoplay();
        autoplayTimer = setInterval(autoAdvance, AUTOPLAY_MS);
    }
    function stopAutoplay() {
        if (autoplayTimer) { clearInterval(autoplayTimer); autoplayTimer = null; }
    }
    startAutoplay();
    carousel.addEventListener('mouseenter', stopAutoplay);
    carousel.addEventListener('mouseleave', () => { if (!modal.classList.contains('open')) startAutoplay(); });
    carousel.addEventListener('touchstart', stopAutoplay, { passive: true });

    prevBtn.addEventListener('click', () => {
        stopAutoplay();
        const vs = visibleSlides();
        const cur = vs.findIndex(s => s.classList.contains('is-active'));
        goToSlide(vs[Math.max(0, cur - 1)] || vs[0]);
    });
    nextBtn.addEventListener('click', () => {
        stopAutoplay();
        const vs = visibleSlides();
        const cur = vs.findIndex(s => s.classList.contains('is-active'));
        goToSlide(vs[Math.min(vs.length - 1, cur + 1)] || vs[0]);
    });

    tags.forEach(btn => {
        btn.addEventListener('click', () => {
            tags.forEach(t => t.classList.remove('active'));
            btn.classList.add('active');

            const tag = btn.dataset.tag;
            let visible = 0;
            slides.forEach(slide => {
                const match = tag === '전체' ||
                    slide.dataset.title.includes(tag) ||
                    slide.dataset.desc.includes(tag);
                slide.classList.toggle('wk-hidden', !match);
                if (match) visible++;
            });
            emptyEl.style.display = visible === 0 ? 'block' : 'none';
            carousel.scrollLeft = 0;
            requestAnimationFrame(updateActive);
            closeFilterPanel();
            startAutoplay();
        });
    });

    // ── 디테일 뷰 모달 (네비게이션 없이 이미지만) ──────────────
    const modal        = document.getElementById('wkModal');
    const modalImg     = document.getElementById('wkModalImg');
    const modalTitle   = document.getElementById('wkModalTitle');
    const modalDesc    = document.getElementById('wkModalDesc');
    const modalCounter = document.getElementById('wkModalCounter');
    const modalThumbs  = document.getElementById('wkModalThumbs');
    const modalClose   = document.getElementById('wkModalClose');
    const modalPrev    = document.getElementById('wkModalPrev');
    const modalNext    = document.getElementById('wkModalNext');
    let modalImages = [], modalIdx = 0;

    function openModal(slide) {
        try { modalImages = JSON.parse(slide.dataset.images || '[]'); } catch (e) { modalImages = []; }
        modalTitle.textContent = slide.dataset.title || '';
        modalDesc.textContent = slide.dataset.desc || '';
        modal.style.backgroundColor = slide.dataset.panelBg || '#111111';
        modalTitle.style.color = slide.dataset.titleColor || '#ffffff';
        modalDesc.style.color = slide.dataset.descColor || '#888888';
        renderThumbs();
        showModalImg(0);
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        stopAutoplay();
    }
    function closeModal() {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        startAutoplay();
    }
    modalImg.addEventListener('load', () => modalImg.classList.add('loaded'));
    function showModalImg(i) {
        if (!modalImages.length) return;
        modalIdx = (i + modalImages.length) % modalImages.length;
        modalImg.classList.remove('loaded');
        modalImg.src = modalImages[modalIdx];
        modalCounter.textContent = modalImages.length > 1
            ? String(modalIdx + 1).padStart(2, '0') + ' / ' + String(modalImages.length).padStart(2, '0')
            : '';
        modalThumbs.querySelectorAll('.wk-modal-thumb').forEach((t, idx) => t.classList.toggle('active', idx === modalIdx));
    }
    function renderThumbs() {
        modalThumbs.innerHTML = '';
        const multi = modalImages.length > 1;
        modalThumbs.style.display = multi ? 'flex' : 'none';
        modalPrev.style.display = multi ? 'flex' : 'none';
        modalNext.style.display = multi ? 'flex' : 'none';
        if (!multi) return;
        modalImages.forEach((src, idx) => {
            const t = document.createElement('div');
            t.className = 'wk-modal-thumb';
            const img = document.createElement('img');
            img.src = src;
            t.appendChild(img);
            t.addEventListener('click', () => showModalImg(idx));
            modalThumbs.appendChild(t);
        });
    }
    modalPrev.addEventListener('click', () => showModalImg(modalIdx - 1));
    modalNext.addEventListener('click', () => showModalImg(modalIdx + 1));
    modalClose.addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    window.addEventListener('keydown', e => {
        if (!modal.classList.contains('open')) return;
        if (e.key === 'Escape')    closeModal();
        if (e.key === 'ArrowLeft')  showModalImg(modalIdx - 1);
        if (e.key === 'ArrowRight') showModalImg(modalIdx + 1);
    });

    // ── 키워드 필터 사이드 패널 (우측) ──────────────────
    const filterTrigger  = document.getElementById('pmFilterTrigger');
    const filterPanel    = document.getElementById('wkFilterPanel');
    const filterBackdrop = document.getElementById('wkFilterBackdrop');
    const filterClose    = document.getElementById('wkFilterPanelClose');

    function openFilterPanel() {
        filterPanel.classList.add('open');
        filterBackdrop.classList.add('open');
        filterPanel.setAttribute('aria-hidden', 'false');
    }
    function closeFilterPanel() {
        filterPanel.classList.remove('open');
        filterBackdrop.classList.remove('open');
        filterPanel.setAttribute('aria-hidden', 'true');
    }
    if (filterTrigger)  filterTrigger.addEventListener('click', openFilterPanel);
    if (filterClose)    filterClose.addEventListener('click', closeFilterPanel);
    if (filterBackdrop) filterBackdrop.addEventListener('click', closeFilterPanel);
    window.addEventListener('keydown', e => {
        if (e.key === 'Escape' && filterPanel.classList.contains('open')) closeFilterPanel();
    });
})();
</script>
</body>
</html>
