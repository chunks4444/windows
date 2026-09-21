<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/slug.php';
require_once __DIR__ . '/../lib/i18n.php';
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
$ssrHasExplicitFilter = $ssrQuery !== '' || ($_GET['category'] ?? '') !== '' || ($_GET['group'] ?? '') !== '';
try {
    $ssrWhere  = 'p.is_active = 1';
    $ssrParams = [];
    if ($ssrQuery !== '') {
        $like = '%' . $ssrQuery . '%';
        $ssrWhere .= ' AND (p.name_ko LIKE :q OR p.slug LIKE :q3 OR p.id IN (SELECT pattern_id FROM library_keywords WHERE keyword LIKE :q2))';
        $ssrParams[':q']  = $like;
        $ssrParams[':q2'] = $like;
        $ssrParams[':q3'] = $like;
    } elseif (!$ssrHasExplicitFilter) {
        // 필터/검색어 없이 그냥 들어온 첫 화면은 collection.js가 기본 group=kr(우리살)로 다시 불러오므로
        // SSR도 동일하게 자체 창작(PYM) 계열을 제외해 하이드레이션 후 카드 목록이 바뀌지 않게 맞춘다.
        $ssrWhere .= " AND p.pattern_category != (SELECT id FROM pattern_categories WHERE code='PYM')";
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
                        <button class="lib-icon-btn lib-like-btn" onclick="toggleLike(event,' . (int)$p['id'] . ')" title="' . htmlspecialchars(t('col_like'), ENT_QUOTES) . '">
                            <i class="bi bi-heart"></i>
                        </button>
                        <button class="lib-icon-btn lib-board-btn" onclick="openBoardModal(event,' . (int)$p['id'] . ',\'' . htmlspecialchars($displayName, ENT_QUOTES) . '\')" title="' . htmlspecialchars(t('col_save_board'), ENT_QUOTES) . '">
                            <i class="bi bi-collection"></i>
                        </button>
                        <button class="lib-icon-btn lib-share-btn" onclick="shareCollectionPattern(event,\'' . htmlspecialchars($p['slug'], ENT_QUOTES) . '\',\'' . htmlspecialchars($displayName, ENT_QUOTES) . '\',\'' . htmlspecialchars($p['image_path'] ?? '', ENT_QUOTES) . '\')" title="' . htmlspecialchars(t('share_title'), ENT_QUOTES) . '">
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
<html lang="<?= is_en() ? 'en' : 'ko' ?>">
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
        <h1><?= htmlspecialchars(t('nav_collection')) ?></h1>
        <p class="lib-hero-sub">
            <?= htmlspecialchars(t('col_sub')) ?>&ensp;
            <span class="lib-count-badge"><?= htmlspecialchars(sprintf(t('col_count'), (int)$totalPatterns)) ?></span>
        </p>
    </div>
</div>

<!-- 검색 / 필터 바 -->
<div class="lib-toolbar">
    <div class="lib-toolbar-inner">
        <div class="lib-select-row">
            <select id="libKrSelect" class="lib-select">
                <option value="" disabled selected hidden><?= htmlspecialchars(t('col_group_kr')) ?></option>
                <option value="kr"><?= htmlspecialchars(t('col_group_kr')) ?></option>
                <!-- 11계열은 initGroupFilter()가 JS로 여기 뒤에 채움 -->
            </select>
            <select id="libNewSelect" class="lib-select">
                <option value="" disabled selected hidden><?= htmlspecialchars(t('col_group_new')) ?></option>
                <option value="new"><?= htmlspecialchars(t('col_group_new')) ?></option>
            </select>
            <select id="libJpSelect" class="lib-select">
                <option value="" disabled selected hidden><?= htmlspecialchars(t('col_group_jp')) ?></option>
                <option value="jp"><?= htmlspecialchars(t('col_group_jp')) ?></option>
                <option value="jp-shoji"><?= htmlspecialchars(t('col_group_shoji')) ?></option>
                <option value="jp-kumiko"><?= htmlspecialchars(t('col_group_kumiko')) ?></option>
            </select>
            <button class="lib-filter-like" id="libLikeBtn"><svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor"><path d="M8 14.2s-5.6-3.4-5.6-7.4c0-1.9 1.5-3.4 3.4-3.4 1.1 0 2.1.5 2.7 1.4.6-.9 1.6-1.4 2.7-1.4 1.9 0 3.4 1.5 3.4 3.4 0 4-5.6 7.4-5.6 7.4z"/></svg> 좋아요</button>
        </div>
        <div class="lib-right-group">
            <span class="lib-result-count" id="libResultCount"><?= htmlspecialchars(sprintf(t('col_count'), (int)$totalPatterns)) ?></span>
            <div class="lib-search">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4"><circle cx="7" cy="7" r="5"/><line x1="10.8" y1="10.8" x2="14" y2="14" stroke-linecap="round"/></svg>
                <input type="text" id="libSearch" placeholder="<?= htmlspecialchars(t('col_search_ph')) ?>" autocomplete="off" value="<?= htmlspecialchars($ssrQuery, ENT_QUOTES) ?>">
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
            <span id="libLoadMoreText"><?= htmlspecialchars(t('wk_load_more')) ?></span>
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
            <span class="bm-title"><?= htmlspecialchars(t('share_title')) ?></span>
            <button class="bm-close" id="libShareModalClose"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="lib-share-linkrow">
            <input type="text" id="libShareModalLink" readonly>
            <button type="button" id="libShareModalCopy"><?= htmlspecialchars(t('share_copy')) ?></button>
        </div>
        <div class="lib-share-channels">
            <button type="button" id="libShareModalKakao" title="<?= htmlspecialchars(t('share_kakao_aria')) ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3C6.5 3 2 6.6 2 11c0 2.8 1.8 5.3 4.6 6.7-.2.7-.7 2.6-.8 3-.1.5.2.5.4.4.2-.1 2.6-1.8 3.6-2.5.7.1 1.4.2 2.2.2 5.5 0 10-3.6 10-8 0-4.4-4.5-7.8-10-7.8z"/></svg>
                <span><?= htmlspecialchars(t('share_kakao')) ?></span>
            </button>
            <button type="button" id="libShareModalFb" title="<?= htmlspecialchars(t('share_fb_aria')) ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 21v-7.9h2.7l.4-3.1h-3.1V8.1c0-.9.3-1.5 1.6-1.5h1.7V3.8C15.9 3.7 14.8 3.6 13.6 3.6c-2.5 0-4.2 1.5-4.2 4.3v2.1H6.7v3.1h2.7V21h4.1z"/></svg>
                <span>FB</span>
            </button>
            <button type="button" id="libShareModalX" title="<?= htmlspecialchars(t('share_x_aria')) ?>">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M18.9 3H22l-7.5 8.6L23 21h-6.9l-5.4-6.6L4.4 21H1.3l8-9.2L1 3h7l4.9 6.1L18.9 3zm-1.2 16h1.9L7.4 4.9H5.4L17.7 19z"/></svg>
                <span>X</span>
            </button>
            <button type="button" id="libShareModalThreads" title="<?= htmlspecialchars(t('share_threads_aria')) ?>">
                <i class="bi bi-threads"></i>
                <span><?= htmlspecialchars(t('share_threads')) ?></span>
            </button>
        </div>
    </div>
</div>

<!-- 보드 모달 -->
<div id="boardModal" class="bm-backdrop" style="display:none;">
    <div class="bm-modal">
        <div class="bm-header">
            <span class="bm-title"><?= htmlspecialchars(t('col_save_board')) ?></span>
            <button class="bm-close" onclick="closeBoardModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div id="boardList" class="bm-list"></div>
        <div class="bm-divider"></div>
        <div class="bm-new">
            <input id="boardNameInput" class="bm-input" type="text" placeholder="<?= htmlspecialchars(t('col_new_board_ph')) ?>" maxlength="40">
            <button class="bm-create-btn" onclick="createBoard()"><?= htmlspecialchars(t('col_create')) ?></button>
        </div>
    </div>
</div>

<!-- 토스트 -->
<div id="libToast" class="lib-toast"></div>

<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
