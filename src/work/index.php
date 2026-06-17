<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/db.php';
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

// 전체 작품 다중 이미지 시드 (최초 1회)
if ((int)$pdo->query('SELECT COUNT(*) FROM work_images')->fetchColumn() === 0) {
    $works_all = $pdo->query('SELECT id, sort_order FROM works WHERE is_active=1 ORDER BY sort_order, id')->fetchAll();
    $imgStmt   = $pdo->prepare('INSERT INTO work_images (work_id, image_url, sort_order) VALUES (?,?,?)');
    // 가로(L)/세로(P) 섞은 5장 세트 (작품별 고유 seed)
    $sizes = [
        [600, 800],  // P
        [800, 600],  // L
        [600, 800],  // P
        [800, 500],  // L
        [500, 700],  // P
    ];
    foreach ($works_all as $w) {
        $pfx = 'wi' . $w['id'];
        foreach ($sizes as $j => [$width, $height]) {
            $url = "https://picsum.photos/seed/{$pfx}_{$j}/{$width}/{$height}";
            $imgStmt->execute([$w['id'], $url, $j]);
        }
    }
}

$works = $pdo->query('SELECT * FROM works WHERE is_active=1 ORDER BY sort_order, id')->fetchAll();
?>
<!DOCTYPE html>
<html lang="ko" style="overflow:hidden;background:#f0ede8;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once __DIR__ . '/../lib/meta.php'; meta_tags(); ?>
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/src/css/work.css">
</head>
<body style="background:#f0ede8;overflow:hidden;">
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="work-track" id="workTrack">
    <div class="work-item" data-base-y="0" data-title="" data-desc="">
        <div class="work-title-card"><span>WORKS</span></div>
    </div>
    <?php
    // 카드별 세로 오프셋 (춤추는 효과)
    foreach ($works as $i => $w):
    ?>
    <div class="work-item" data-base-y="0" data-title="<?= htmlspecialchars($w['title']) ?>" data-desc="<?= htmlspecialchars($w['description']) ?>" data-href="/src/work/detail.php?id=<?= $w['id'] ?>">
        <img src="<?= htmlspecialchars($w['image_url']) ?>"
             alt="<?= htmlspecialchars($w['title']) ?>"
             loading="lazy">
    </div>
    <?php endforeach; ?>
</div>

<!-- 제목 표시 영역 -->
<div class="work-label" id="workLabel">
    <div class="work-label-title" id="workLabelTitle"></div>
</div>

<!-- 스크롤 힌트 -->
<div class="scroll-hint" id="scrollHint">
    <svg width="54" height="33" viewBox="0 0 36 22" fill="none">
        <!-- 마우스 아이콘 (중앙, 비클릭) -->
        <rect x="13" y="1" width="10" height="16" rx="5" stroke="currentColor" stroke-width="1.5" pointer-events="none"/>
        <rect x="17" y="4" width="2" height="4" rx="1" fill="currentColor" class="scroll-wheel" pointer-events="none"/>
        <!-- 왼쪽 화살표 -->
        <g id="hintLeft" style="cursor:pointer">
            <rect x="0" y="0" width="12" height="22" fill="transparent"/>
            <path d="M5 11 L1 11 M1 11 L4 8 M1 11 L4 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </g>
        <!-- 오른쪽 화살표 -->
        <g id="hintRight" style="cursor:pointer">
            <rect x="24" y="0" width="12" height="22" fill="transparent"/>
            <path d="M31 11 L35 11 M35 11 L32 8 M35 11 L32 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </g>
    </svg>
</div>

<script>
(function () {
    const track  = document.getElementById('workTrack');
    const items  = document.querySelectorAll('.work-item');
    const label  = document.getElementById('workLabel');
    const lTitle = document.getElementById('workLabelTitle');

    /* ── 중앙 카드 라벨 ── */
    function updateLabel() {
        const center = track.scrollLeft + window.innerWidth / 2;
        let closest = null, minDist = Infinity;
        items.forEach(item => {
            const dist = Math.abs(item.offsetLeft + item.offsetWidth / 2 - center);
            if (dist < minDist) { minDist = dist; closest = item; }
        });
        if (closest) {
            lTitle.textContent = closest.dataset.title;
            label.classList.toggle('visible', !!(closest.dataset.title));
        }
    }

    track.addEventListener('scroll', () => {
        requestAnimationFrame(updateLabel);
    });

    /* ── 카드 클릭 → 상세 페이지 ── */
    let dragMoved = false;
    items.forEach(item => {
        if (!item.dataset.href) return;
        item.addEventListener('click', () => {
            if (!dragMoved) location.href = item.dataset.href;
        });
        item.style.cursor = 'pointer';
    });

    /* ── 마우스 드래그 스크롤 ── */
    let isDragging = false, startX, startScroll;

    track.addEventListener('mousedown', e => {
        isDragging = true; dragMoved = false;
        startX = e.pageX; startScroll = track.scrollLeft;
        track.style.cursor = 'grabbing';
        track.style.scrollSnapType = 'none';
    });
    window.addEventListener('mousemove', e => {
        if (!isDragging) return;
        if (Math.abs(e.pageX - startX) > 5) dragMoved = true;
        track.scrollLeft = startScroll - (e.pageX - startX);
    });
    window.addEventListener('mouseup', () => {
        if (!isDragging) return;
        isDragging = false;
        track.style.cursor = 'grab';
        track.style.scrollSnapType = '';
    });

    /* ── 화살표 클릭 → 카드 이동 ── */
    function scrollByCard(dir) {
        const cardW = items[0].offsetWidth + 16;
        track.scrollBy({ left: dir * cardW, behavior: 'smooth' });
    }
    document.getElementById('hintLeft').addEventListener('click',  () => scrollByCard(-1));
    document.getElementById('hintRight').addEventListener('click', () => scrollByCard(1));

    /* ── 마우스 휠 → 가로 스크롤 ── */
    const hint = document.getElementById('scrollHint');
    let wheelTimer;
    track.addEventListener('wheel', e => {
        e.preventDefault();
        track.style.scrollSnapType = 'none';
        track.scrollLeft += e.deltaY + e.deltaX;
        clearTimeout(wheelTimer);
        wheelTimer = setTimeout(() => { track.style.scrollSnapType = ''; }, 80);
    }, { passive: false });

    /* ── 오토플레이 ── */
    let autoTimer;
    function autoNext() {
        const cardW = items[0].offsetWidth + 16;
        const maxScroll = track.scrollWidth - track.clientWidth;
        if (track.scrollLeft >= maxScroll - 1) {
            track.scrollTo({ left: 0, behavior: 'smooth' });
        } else {
            track.scrollBy({ left: cardW, behavior: 'smooth' });
        }
    }
    function startAuto() { setTimeout(autoNext, 800); autoTimer = setInterval(autoNext, 2000); }
    function stopAuto()  { clearInterval(autoTimer); }

    startAuto();
    track.addEventListener('mouseenter', stopAuto);
    track.addEventListener('mouseleave', startAuto);
    track.addEventListener('touchstart', stopAuto,  { passive: true });
    track.addEventListener('touchend',   () => setTimeout(startAuto, 1500));

    window.addEventListener('resize', updateLabel);

    /* ── 초기화 ── */
    updateLabel();
})();
</script>
</body>
</html>
