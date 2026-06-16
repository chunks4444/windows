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
    <div class="work-item work-title-card" data-base-y="0" data-title="" data-desc="">
        <span>WORKS</span>
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
    <div class="work-label-desc"  id="workLabelDesc"></div>
</div>

<script>
(function () {
    const track  = document.getElementById('workTrack');
    const items  = document.querySelectorAll('.work-item');
    const label  = document.getElementById('workLabel');
    const lTitle = document.getElementById('workLabelTitle');
    const lDesc  = document.getElementById('workLabelDesc');

    /* ── 카드 Y 오프셋 (스크롤 위치 기반) ── */
    function updatePositions() {
        const mid = track.scrollLeft + window.innerWidth / 2;
        items.forEach(item => {
                item.style.transform = '';
        });
    }

    /* ── 중앙 카드 라벨 ── */
    function updateLabel() {
        const mid = track.scrollLeft + window.innerWidth / 2;
        let closest = null, minDist = Infinity;
        items.forEach(item => {
            const d = Math.abs(item.offsetLeft + item.offsetWidth / 2 - mid);
            if (d < minDist) { minDist = d; closest = item; }
        });
        if (closest) {
            lTitle.textContent = closest.dataset.title;
            lDesc.textContent  = closest.dataset.desc;
            label.classList.add('visible');
        }
    }

    track.addEventListener('scroll', () => {
        requestAnimationFrame(() => { updatePositions(); updateLabel(); });
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

    /* ── 드래그 스크롤 ── */
    let isDragging = false, startX, startScroll;
    track.addEventListener('mousedown', e => {
        isDragging = true; dragMoved = false;
        startX = e.pageX; startScroll = track.scrollLeft;
        track.style.cursor = 'grabbing';
    });
    window.addEventListener('mousemove', e => {
        if (!isDragging) return;
        if (Math.abs(e.pageX - startX) > 5) dragMoved = true;
        track.scrollLeft = startScroll - (e.pageX - startX);
    });
    window.addEventListener('mouseup', () => {
        isDragging = false;
        track.style.cursor = 'grab';
    });

    /* ── 좌우 패딩: 첫·끝 카드가 중앙에 올 수 있도록 ── */
    function setSnapPadding() {
        const first = items[0];
        const last  = items[items.length - 1];
        if (!first || !last) return;
        const vw   = window.innerWidth;
        const padL = (vw - first.offsetWidth) / 2;
        const padR = (vw - last.offsetWidth)  / 2;
        track.style.paddingLeft  = Math.max(0, padL) + 'px';
        track.style.paddingRight = Math.max(0, padR) + 'px';
        // 첫 번째 카드 중앙 정렬로 초기 스크롤
        track.scrollLeft = 0;
    }

    // 이미지 로드 후 패딩 계산
    let loaded = 0;
    const allImgs = track.querySelectorAll('img');
    function onLoad() { loaded++; if (loaded === allImgs.length) setSnapPadding(); }
    allImgs.forEach(img => {
        if (img.complete) onLoad();
        else { img.addEventListener('load', onLoad); img.addEventListener('error', onLoad); }
    });
    setTimeout(setSnapPadding, 600);
    window.addEventListener('resize', setSnapPadding);

    /* ── 초기화 ── */
    updatePositions();
    updateLabel();
})();
</script>
</body>
</html>
