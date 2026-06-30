<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once __DIR__ . '/../lib/meta.php'; meta_tags(); ?>
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php css_tag('/src/css/collection.css'); ?>
</head>
<body>

<?php include __DIR__ . '/../components/nav.php'; ?>

<!-- 페이지 히어로 -->
<div class="lib-hero">
    <div class="lib-hero-inner">
        <p class="lib-hero-label">Collection</p>
        <h1>컬렉션</h1>
        <p class="lib-hero-sub">평목 스튜디오에서 제작된 창호 격자 패턴을 둘러보세요.</p>
    </div>
</div>

<!-- 검색 / 필터 바 -->
<div class="lib-toolbar">
    <div class="lib-toolbar-inner">
        <div class="lib-filters" id="libFilters">
            <button class="lib-filter-btn active" data-filter="all">전체</button>
            <button class="lib-filter-btn lib-filter-like" data-filter="liked"><i class="bi bi-heart-fill"></i> 좋아요</button>
        </div>
        <div class="lib-search">
            <i class="bi bi-search"></i>
            <input type="text" id="libSearch" placeholder="패턴 검색…" autocomplete="off">
        </div>
        <div class="lib-cat-row" id="libCatFilters">
            <button class="lib-cat-btn active" data-cat=""><i class="bi bi-grid"></i> 전체</button>
        </div>
    </div>
</div>

<div class="lib-main">
    <div class="lib-masonry" id="libMasonry"></div>
    <div id="libLoadMore" style="display:none;text-align:center;padding:24px 0;">
        <button class="lib-loadmore-btn" onclick="loadNextPage()">
            <span id="libLoadMoreText">더 보기</span>
            <span id="libLoadMoreSpinner" style="display:none;"></span>
        </button>
    </div>
</div>

<script>
window.__pmokEngineIcons = <?= json_encode(array_map(fn($svg) => $svg, $navStudioIcons), JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="/src/js/collection.js?v=<?= md5_file(__DIR__ . '/../js/collection.js') ?>"></script>

<!-- 보드 모달 -->
<div id="boardModal" class="bm-backdrop" style="display:none;">
    <div class="bm-modal">
        <div class="bm-header">
            <span class="bm-title">보드에 저장</span>
            <button class="bm-close" onclick="closeBoardModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div id="boardList" class="bm-list"></div>
        <div class="bm-divider"></div>
        <div class="bm-new">
            <input id="boardNameInput" class="bm-input" type="text" placeholder="새 보드 이름…" maxlength="40">
            <button class="bm-create-btn" onclick="createBoard()">만들기</button>
        </div>
    </div>
</div>

<!-- 토스트 -->
<div id="libToast" class="lib-toast"></div>

<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
