<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/slug.php';
try {
    $totalPatterns = (int) db()->query("SELECT COUNT(*) FROM library_patterns WHERE is_active = 1")->fetchColumn();
} catch (Throwable $e) {
    $totalPatterns = 0;
}

// 첫 화면 카드를 서버에서 미리 렌더링한다. 이전에는 #libMasonry가 빈 div로 나가고
// collection.js의 fetch 결과로만 채워져서, 초기 HTML(뷰 소스)에 이미지가 하나도
// 없었다 — 구글이 이 페이지의 대표 이미지를 못 찾는 원인이었다. collection.js는
// DOMContentLoaded에서 어차피 같은 데이터를 다시 불러와 이 마크업을 교체하므로
// 최종 렌더링 결과는 동일하다.
$engineEditorMap = [
    'classic'  => '/src/engine/classic/classic.php',
    'square'   => '/src/engine/square/square.php',
    'diamond'  => '/src/engine/diamond/diamond.php',
    'cross'    => '/src/engine/cross/cross.php',
    'triangle' => '/src/engine/triangle/triangle.php',
    'hexagon'  => '/src/engine/hexagon/hexagon.php',
];
$ssrQuery = trim($_GET['q'] ?? '');
try {
    $ssrWhere  = 'p.is_active = 1';
    $ssrParams = [];
    if ($ssrQuery !== '') {
        $like = '%' . $ssrQuery . '%';
        $ssrWhere .= ' AND (p.name_ko LIKE :q OR p.slug LIKE :q3 OR p.id IN (SELECT pattern_id FROM library_keywords WHERE keyword LIKE :q2))';
        $ssrParams[':q']  = $like;
        $ssrParams[':q2'] = $like;
        $ssrParams[':q3'] = $like;
    }
    $stmt = db()->prepare(
        "SELECT p.id, p.slug, p.name_ko, p.drawing_id, p.image_path, d.type AS engine,
                GROUP_CONCAT(k.keyword ORDER BY k.id SEPARATOR ',') AS keywords
         FROM library_patterns p
         LEFT JOIN drawings d ON d.id = p.drawing_id
         LEFT JOIN library_keywords k ON k.pattern_id = p.id
         WHERE $ssrWhere
         GROUP BY p.id
         ORDER BY p.sort_order, p.id
         LIMIT 20"
    );
    foreach ($ssrParams as $k => $v) $stmt->bindValue($k, $v);
    $stmt->execute();
    $ssrPatterns = $stmt->fetchAll();
} catch (Throwable $e) {
    $ssrPatterns = [];
}

function collection_card_html(array $p, array $navStudioIcons, array $engineEditorMap, bool $eager = false): string {
    $displayName = library_pattern_display_name($p['slug'], $p['name_ko'] ?? '');
    $keywords    = $p['keywords'] ? explode(',', $p['keywords']) : [];
    $engineKey   = strtolower($p['engine'] ?? '');
    $editorUrl   = $engineEditorMap[$engineKey] ?? null;
    $loadAttr    = $eager ? '' : ' loading="lazy"';

    $imgHtml = $p['image_path']
        ? '<img src="' . htmlspecialchars($p['image_path'], ENT_QUOTES) . '" alt="' . htmlspecialchars($displayName, ENT_QUOTES) . '"' . $loadAttr . ' style="width:100%;height:100%;object-fit:cover;">'
        : '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--text-3);font-size:40px;"><i class="bi bi-image"></i></div>';

    $editorBtn = ($editorUrl && $p['drawing_id'])
        ? '<a href="' . htmlspecialchars($editorUrl, ENT_QUOTES) . '?drawing_id=' . (int)$p['drawing_id'] . '" class="lib-btn lib-btn-primary" onclick="return openCollectionEditor(event,\'' . htmlspecialchars($editorUrl, ENT_QUOTES) . '?drawing_id=' . (int)$p['drawing_id'] . '\')"><i class="bi bi-pencil"></i> 열기</a>'
        : '';

    $kwHtml = implode(' &middot; ', array_map(fn($k) => '<span style="font-size:11px;color:var(--text);">' . htmlspecialchars($k, ENT_QUOTES) . '</span>', array_slice($keywords, 0, 3)));

    $engineIcon = $navStudioIcons[$engineKey] ?? '';
    $engineIconHtml = $engineIcon ? '<span class="lib-card-engine-icon">' . $engineIcon . '</span>' : '';

    return '
        <div class="lib-item" data-id="' . (int)$p['id'] . '">
            <div class="lib-card-img" style="aspect-ratio:1/1;">
                ' . $imgHtml . '
                <div class="lib-overlay">
                    <div class="lib-overlay-top">
                        <button class="lib-icon-btn lib-like-btn" onclick="toggleLike(event,' . (int)$p['id'] . ')" title="좋아요">
                            <i class="bi bi-heart"></i>
                        </button>
                        <button class="lib-icon-btn lib-board-btn" onclick="openBoardModal(event,' . (int)$p['id'] . ',\'' . htmlspecialchars($displayName, ENT_QUOTES) . '\')" title="보드에 저장">
                            <i class="bi bi-collection"></i>
                        </button>
                        <button class="lib-icon-btn lib-share-btn" onclick="shareCollectionPattern(event,\'' . htmlspecialchars($p['slug'], ENT_QUOTES) . '\',\'' . htmlspecialchars($displayName, ENT_QUOTES) . '\',\'' . htmlspecialchars($p['image_path'] ?? '', ENT_QUOTES) . '\')" title="공유">
                            <i class="bi bi-share"></i>
                        </button>
                    </div>
                    <div class="lib-overlay-bottom">
                        <div class="lib-overlay-title">' . htmlspecialchars($displayName) . '</div>
                        <div class="lib-overlay-actions">' . $editorBtn . '</div>
                    </div>
                </div>
            </div>
            <div class="lib-card-body">
                <div class="lib-card-name">' . $engineIconHtml . htmlspecialchars($displayName) . '</div>
                <div class="lib-card-sub">' . $kwHtml . '</div>
            </div>
        </div>';
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
    <?php css_tag('/src/css/collection.css'); ?>
    <?php if ($kakaoJsKey = kakao_js_key()): ?>
    <script src="https://t1.kakaocdn.net/kakao_js_sdk/2.7.4/kakao.min.js"></script>
    <script>if (window.Kakao && !Kakao.isInitialized()) Kakao.init('<?= addslashes($kakaoJsKey) ?>');</script>
    <?php endif; ?>
</head>
<body>

<?php include __DIR__ . '/../components/nav.php'; ?>

<!-- 페이지 히어로 -->
<div class="lib-hero">
    <div class="lib-hero-inner">
        <p class="lib-hero-label">Collection</p>
        <h1>컬렉션</h1>
        <p class="lib-hero-sub">
            평목 스튜디오에서 제작된 창호 격자 패턴을 둘러보세요.&ensp;
            <span class="lib-count-badge"><?= $totalPatterns ?>개 패턴</span>
        </p>
    </div>
</div>

<!-- 검색 / 필터 바 -->
<div class="lib-toolbar">
    <div class="lib-toolbar-inner">
        <div class="lib-select-row">
            <select id="libKrSelect" class="lib-select">
                <option value="" disabled selected hidden>우리살</option>
                <option value="">전체</option>
            </select>
            <select id="libNewSelect" class="lib-select">
                <option value="" disabled selected hidden>새살</option>
                <option value="">전체</option>
                <option value="new">새살</option>
            </select>
            <select id="libJpSelect" class="lib-select">
                <option value="" disabled selected hidden>일본살</option>
                <option value="">전체</option>
                <option value="jp-shoji">쇼지</option>
                <option value="jp-kumiko">쿠미꼬</option>
            </select>
            <button class="lib-filter-like" id="libLikeBtn"><i class="bi bi-heart-fill"></i> 좋아요</button>
        </div>
        <div class="lib-right-group">
            <span class="lib-result-count" id="libResultCount"><strong><?= (int)$totalPatterns ?></strong>개 패턴</span>
            <div class="lib-search">
                <i class="bi bi-search"></i>
                <input type="text" id="libSearch" placeholder="패턴 검색…" autocomplete="off" value="<?= htmlspecialchars($ssrQuery, ENT_QUOTES) ?>">
            </div>
        </div>
    </div>
</div>

<div class="lib-main">
    <div class="lib-masonry" id="libMasonry"><?php
        foreach ($ssrPatterns as $i => $p) {
            echo collection_card_html($p, $navStudioIcons, $engineEditorMap, $i < 4);
        }
    ?></div>
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
<script src="/src/js/collection-share.js?v=<?= md5_file(__DIR__ . '/../js/collection-share.js') ?>"></script>

<!-- 공유 모달 -->
<div id="libShareModal" class="bm-backdrop" style="display:none;">
    <div class="bm-modal">
        <div class="bm-header">
            <span class="bm-title">공유하기</span>
            <button class="bm-close" id="libShareModalClose"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="lib-share-linkrow">
            <input type="text" id="libShareModalLink" readonly>
            <button type="button" id="libShareModalCopy">복사</button>
        </div>
        <div class="lib-share-channels">
            <button type="button" id="libShareModalKakao" title="카카오톡 공유">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3C6.5 3 2 6.6 2 11c0 2.8 1.8 5.3 4.6 6.7-.2.7-.7 2.6-.8 3-.1.5.2.5.4.4.2-.1 2.6-1.8 3.6-2.5.7.1 1.4.2 2.2.2 5.5 0 10-3.6 10-8 0-4.4-4.5-7.8-10-7.8z"/></svg>
                <span>카카오</span>
            </button>
            <button type="button" id="libShareModalFb" title="페이스북 공유">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 21v-7.9h2.7l.4-3.1h-3.1V8.1c0-.9.3-1.5 1.6-1.5h1.7V3.8C15.9 3.7 14.8 3.6 13.6 3.6c-2.5 0-4.2 1.5-4.2 4.3v2.1H6.7v3.1h2.7V21h4.1z"/></svg>
                <span>FB</span>
            </button>
            <button type="button" id="libShareModalX" title="X(트위터) 공유">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M18.9 3H22l-7.5 8.6L23 21h-6.9l-5.4-6.6L4.4 21H1.3l8-9.2L1 3h7l4.9 6.1L18.9 3zm-1.2 16h1.9L7.4 4.9H5.4L17.7 19z"/></svg>
                <span>X</span>
            </button>
            <button type="button" id="libShareModalThreads" title="스레드에 공유">
                <i class="bi bi-threads"></i>
                <span>스레드</span>
            </button>
        </div>
    </div>
</div>

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
