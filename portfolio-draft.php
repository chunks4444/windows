<?php
// 포트폴리오 디자인 작업용 사본 — src/portfolio/index.php의 복제본.
// 실제 서비스 페이지(/portfolio/)는 건드리지 않고 여기서 디자인을 자유롭게 바꿔보기 위한 용도.
// CSS도 별도(work_draft.css)로 분리해 라이브 페이지에 영향 없음.
// 프로젝트 루트에 둔 이유: /src/portfolio/ 밑에 두면 .htaccess의 클린 URL 리다이렉트 규칙에 걸려
// /portfolio/{slug} 상세 페이지 라우팅으로 잘못 넘어가 버림.
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/src/lib/db.php';
require_once __DIR__ . '/src/lib/engine_icons.php';
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
    $imgStmt = $pdo->prepare("SELECT work_id, image_url, panel_bg, font_color FROM work_images WHERE work_id IN ($in) ORDER BY sort_order, id");
    $imgStmt->execute($ids);
    foreach ($imgStmt->fetchAll() as $row) {
        $workImages[$row['work_id']][] = ['url' => $row['image_url'], 'panel_bg' => $row['panel_bg'] ?: null, 'font_color' => $row['font_color'] ?: null];
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
    <?php require_once __DIR__ . '/src/lib/meta.php'; meta_tags(); ?>
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php css_tag('/src/css/work_draft.css'); ?>
</head>
<body>
<?php include __DIR__ . '/src/components/nav.php'; ?>
<div style="position:fixed;top:8px;left:50%;transform:translateX(-50%);z-index:3000;background:#c00;color:#fff;font-size:11px;font-weight:700;letter-spacing:.05em;padding:3px 10px;border-radius:4px;pointer-events:none;">DRAFT</div>
<style>
/* 포트폴리오 페이지 전용 — 상단 공지 띠배너 숨김 (배너가 있을 때 nav.php가 강제로 밀어내는 top값도 함께 되돌림) */
#pmTopbarNotice { display: none !important; }
</style>

<div class="wkg-page">

    <!-- ── 페이지 헤더 ── -->
    <div class="wkg-header container">
        <div class="wkg-header-row">
            <div>
                <p class="wkg-label">Portfolio</p>
                <h1>포트폴리오</h1>
                <p class="wkg-sub">
                    평목 공방에서 완성된 작품들입니다.
                    <span class="wkg-count-badge"><?= $total ?>개 작품</span>
                </p>
            </div>
            <button type="button" class="wkg-filter-btn" id="wkgFilterBtn"><i class="bi bi-sliders"></i> 필터</button>
        </div>
    </div>

    <!-- ── 화이트 정사각형 카드 그리드 (3열, 카드 사이 2px 블랙) ── -->
    <div class="wkg-grid-wrap container">
        <div class="wkg-grid" id="wkCarousel">
            <?php foreach ($works as $w):
                $desc = strip_tags($w['description']);
                $icon = engine_icon_svg($w['engine_key'] ?? '');
                $images = $workImages[$w['id']] ?? ($w['image_url'] ? [['url' => $w['image_url'], 'panel_bg' => null, 'font_color' => null]] : []);
            ?>
            <div class="wkg-card"
                 data-title="<?= htmlspecialchars($w['title']) ?>"
                 data-desc="<?= htmlspecialchars($desc) ?>"
                 data-images="<?= htmlspecialchars(json_encode($images)) ?>"
                 data-panel-bg="<?= htmlspecialchars($w['panel_bg'] ?: '#111111') ?>"
                 data-title-color="<?= htmlspecialchars($w['title_color'] ?: '#ffffff') ?>"
                 data-desc-color="<?= htmlspecialchars($w['desc_color'] ?: '#888888') ?>"
                 role="button">

                <?php if (!empty($w['icon_svg'])): ?>
                <span class="wkg-card-svg-icon"><?= $w['icon_svg'] ?></span>
                <?php else: ?>
                <img class="wkg-card-img"
                     src="<?= htmlspecialchars($w['image_url']) ?>"
                     alt="<?= htmlspecialchars($w['title']) ?>"
                     loading="lazy">
                <?php if ($icon): ?>
                <span class="wkg-card-icon"><?= $icon ?></span>
                <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="wk-empty" id="wkEmpty" style="display:none;">
            해당 카테고리의 작품이 없습니다.
        </div>

        <div class="wkg-load-more-wrap">
            <button type="button" class="wkg-load-more-btn" id="wkgLoadMoreBtn" style="display:none;">더 보기</button>
        </div>
    </div>

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
        <span class="wk-modal-eyebrow" id="wkModalEyebrow">Portfolio</span>
        <h2 class="wk-modal-title" id="wkModalTitle"></h2>
        <span class="wk-modal-rule" id="wkModalRule"></span>
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
    const tags        = document.querySelectorAll('.wk-tag');
    const cards       = Array.from(document.querySelectorAll('.wkg-card'));
    const emptyEl     = document.getElementById('wkEmpty');
    const loadMoreBtn = document.getElementById('wkgLoadMoreBtn');
    const gridEl      = document.querySelector('.wkg-grid');
    const PAGE_SIZE = 3;

    let currentTag    = '전체';
    let visibleCount  = PAGE_SIZE;

    function matchesTag(card, tag) {
        return tag === '전체' ||
            card.dataset.title.includes(tag) ||
            card.dataset.desc.includes(tag);
    }

    // 처음 화면엔 스크롤 없이 꽉 차는 만큼(화면 높이에 맞춰 몇 줄이 들어가는지) 보여주고,
    // 그 다음부터 "더 보기"를 누를 때마다 PAGE_SIZE(3개)씩 늘어난다.
    function computeInitialCount() {
        if (!cards.length || !gridEl) return PAGE_SIZE;
        const colCount = getComputedStyle(gridEl).gridTemplateColumns.split(' ').filter(Boolean).length || 1;
        const cardRect = cards[0].getBoundingClientRect();
        const cardHeight = cardRect.height || 300;
        const availableHeight = window.innerHeight - cardRect.top;
        const rows = Math.max(1, Math.ceil(availableHeight / cardHeight));
        return rows * colCount;
    }

    // 태그 필터 + "더 보기" 페이지네이션을 한 곳에서 같이 계산
    function updateVisibility() {
        const matched = cards.filter(card => matchesTag(card, currentTag));
        matched.forEach((card, idx) => card.classList.toggle('wkg-hidden', idx >= visibleCount));
        cards.filter(card => !matchesTag(card, currentTag)).forEach(card => card.classList.add('wkg-hidden'));

        emptyEl.style.display = matched.length === 0 ? 'block' : 'none';
        loadMoreBtn.style.display = matched.length > visibleCount ? '' : 'none';
    }

    cards.forEach(card => {
        card.addEventListener('click', () => openModal(card));
    });

    tags.forEach(btn => {
        btn.addEventListener('click', () => {
            tags.forEach(t => t.classList.remove('active'));
            btn.classList.add('active');
            currentTag   = btn.dataset.tag;
            visibleCount = computeInitialCount();
            updateVisibility();
            closeFilterPanel();
        });
    });

    loadMoreBtn.addEventListener('click', () => {
        visibleCount += PAGE_SIZE;
        updateVisibility();
    });

    visibleCount = computeInitialCount();
    updateVisibility();

    // ── 디테일 뷰 모달 (네비게이션 없이 이미지만) ──────────────
    const modal        = document.getElementById('wkModal');
    const modalImg     = document.getElementById('wkModalImg');
    const modalEyebrow = document.getElementById('wkModalEyebrow');
    const modalTitle   = document.getElementById('wkModalTitle');
    const modalRule    = document.getElementById('wkModalRule');
    const modalDesc    = document.getElementById('wkModalDesc');
    const modalCounter = document.getElementById('wkModalCounter');
    const modalThumbs  = document.getElementById('wkModalThumbs');
    const modalClose   = document.getElementById('wkModalClose');
    const modalPrev    = document.getElementById('wkModalPrev');
    const modalNext    = document.getElementById('wkModalNext');
    let modalImages = [], modalIdx = 0;
    let modalDefaultBg = '#111111', modalDefaultTitleColor = '#ffffff', modalDefaultDescColor = '#888888';

    function openModal(slide) {
        try { modalImages = JSON.parse(slide.dataset.images || '[]'); } catch (e) { modalImages = []; }
        modalDefaultBg         = slide.dataset.panelBg   || '#111111';
        modalDefaultTitleColor = slide.dataset.titleColor || '#ffffff';
        modalDefaultDescColor  = slide.dataset.descColor  || '#888888';
        modalTitle.textContent = slide.dataset.title || '';
        modalDesc.textContent = slide.dataset.desc || '';
        modal.style.backgroundColor = modalDefaultBg;
        renderThumbs();
        showModalImg(0);
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }
    function closeModal() {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }
    modalImg.addEventListener('load', () => modalImg.classList.add('loaded'));
    function showModalImg(i) {
        if (!modalImages.length) return;
        modalIdx = (i + modalImages.length) % modalImages.length;
        const item = modalImages[modalIdx];
        modalImg.classList.remove('loaded');
        modalImg.src = item.url || item; // item이 문자열인 예전 데이터도 호환
        modal.style.backgroundColor = (item.panel_bg || modalDefaultBg);
        const titleColor = item.font_color || modalDefaultTitleColor;
        const descColor  = item.font_color || modalDefaultDescColor;
        modalEyebrow.style.color = titleColor;
        modalTitle.style.color = titleColor;
        modalRule.style.backgroundColor = titleColor;
        modalClose.style.color = titleColor;
        modalDesc.style.color = descColor;
        modalCounter.style.color = descColor;
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
        modalImages.forEach((item, idx) => {
            const t = document.createElement('div');
            t.className = 'wk-modal-thumb';
            const img = document.createElement('img');
            img.src = item.url || item;
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
    const filterTrigger  = document.getElementById('wkgFilterBtn');
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
