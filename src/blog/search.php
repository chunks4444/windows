<?php
// 블로그 검색 결과 — /blog/search?q=검색어  (/en/blog/search 도 같은 파일)
// 검색 방식·이표기·기록은 src/lib/blog_search.php 참고.
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/i18n.php';
require_once __DIR__ . '/../lib/blog_search.php';

$q        = blog_search_normalize((string)($_GET['q'] ?? ''));
$tooShort = $q !== '' && mb_strlen($q) < BLOG_SEARCH_MIN_LEN;
$perPage  = 20;
$page     = max(1, (int)($_GET['page'] ?? 1));
$total    = 0;
$rows     = [];
$terms    = [];
$notes    = [];
$failed   = false;

if ($q !== '' && !$tooShort) {
    try {
        $pdo = db();
        ['terms' => $terms, 'notes' => $notes] = blog_search_expand($pdo, $q);
        $res = blog_search($pdo, $terms, $perPage, ($page - 1) * $perPage);
        $total = $res['total'];
        $rows  = $res['rows'];
        // 기록은 첫 페이지에서만 — 다음 페이지로 넘길 때마다 같은 검색이 여러 번 쌓이지 않게
        if ($page === 1) blog_search_log($pdo, $q, $total);
    } catch (Throwable $e) {
        error_log('blog search: ' . $e->getMessage());
        $failed = true;
    }
}
$totalPages = max(1, (int)ceil($total / $perPage));

// 한국어 조사(은/는, 과/와) — 마지막 글자 받침 여부로 고른다. 한글이 아니면 '은(는)'처럼 둘 다 표기
function bs_josa(string $word, string $withBatchim, string $without): string {
    $last = mb_substr(rtrim($word, "'\"’”"), -1); // 따옴표로 감싼 말이면 따옴표 앞 글자로 판단
    $code = mb_ord($last);
    if ($code < 0xAC00 || $code > 0xD7A3) return $word . $withBatchim . '(' . $without . ')';
    return $word . ((($code - 0xAC00) % 28) ? $withBatchim : $without);
}
function bs_alias_note(array $note): string {
    [$found, $others] = $note;
    if (is_en()) {
        return sprintf(t('blog_search_alias'), "'" . $found . "'", implode(', ', array_map(fn($o) => "'$o'", $others)));
    }
    $quoted = array_map(fn($o) => "'" . $o . "'", $others);
    $lastO  = array_pop($quoted);
    $list   = ($quoted ? implode(', ', $quoted) . ', ' : '') . bs_josa($lastO, '과', '와');
    return sprintf(t('blog_search_alias'), bs_josa("'" . $found . "'", '은', '는'), $list);
}
?>
<!DOCTYPE html>
<html lang="<?= is_en() ? 'en' : 'ko' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, follow">
    <?php
    require_once __DIR__ . '/../lib/meta.php';
    meta_tags(['title' => ($q !== '' ? $q . ' — ' : '') . t('blog_search_title'), 'canonical' => SITE_URL . lang_href('/blog/search'), 'hreflang' => '/blog/search']);
    ?>
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<?php css_tag('/src/css/blog.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="bg-page">
    <div class="bg-hero">
        <div class="bg-hero-inner">
            <p class="bg-hero-label"><a href="<?= lang_href('/blog/') ?>">Blog</a></p>
            <h1><?= htmlspecialchars(t('blog_search_title')) ?></h1>
        </div>
    </div>

    <div class="bg-search-page">
        <form class="bg-search" method="get" action="<?= lang_href('/blog/search') ?>" role="search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="<?= htmlspecialchars(t('blog_search_ph')) ?>" aria-label="<?= htmlspecialchars(t('blog_search_ph')) ?>" maxlength="<?= BLOG_SEARCH_MAX_LEN ?>" <?= $q === '' ? 'autofocus' : '' ?>>
            <button type="submit"><?= htmlspecialchars(t('blog_search_btn')) ?></button>
        </form>

        <?php if ($q === ''): ?>
            <p class="bg-search-info"><?= htmlspecialchars(t('blog_search_hint')) ?></p>
        <?php elseif ($tooShort): ?>
            <div class="bg-empty"><?= htmlspecialchars(t('blog_search_short')) ?></div>
        <?php elseif ($failed): ?>
            <div class="bg-empty"><?= htmlspecialchars(t('blog_search_error')) ?></div>
        <?php else: ?>
            <p class="bg-search-info">
                <?= htmlspecialchars(sprintf(t('blog_search_result'), $q, $total)) ?>
                <a href="<?= lang_href('/blog/') ?>"><?= htmlspecialchars(t('blog_search_clear')) ?></a>
            </p>
            <?php foreach ($notes as $note): ?>
            <p class="bg-search-alias"><i class="bi bi-arrow-left-right" aria-hidden="true"></i> <?= htmlspecialchars(bs_alias_note($note)) ?></p>
            <?php endforeach; ?>

            <?php if ($total === 0): ?>
            <div class="bg-empty bg-search-none">
                <p><?= htmlspecialchars(t('blog_search_none')) ?></p>
                <a class="bg-search-mail" href="mailto:pyeongmok@gmail.com?subject=<?= rawurlencode(sprintf(t('blog_search_mail_subject'), $q)) ?>"><i class="bi bi-envelope"></i> pyeongmok@gmail.com</a>
            </div>
            <?php else: ?>
            <ol class="bg-ranked-list bg-search-list">
                <?php foreach ($rows as $r):
                    $useEn = is_en() && !empty($r['body_text_en']);
                    $text  = (string)($useEn ? $r['body_text_en'] : $r['body_text']);
                ?>
                <li class="bg-ranked-item">
                    <a class="bg-ranked-link" href="<?= lang_href('/blog/' . rawurlencode($r['slug'])) ?>">
                        <div class="bg-ranked-text">
                            <p class="bg-ranked-cat">
                                <?php if ($r['series_name']): ?><?= htmlspecialchars(db_field($r, 'series_name')) ?><?= $r['series_order'] ? ' · ' . htmlspecialchars(sprintf(t('home_blog_episode'), (int)$r['series_order'])) : '' ?> · <?php endif; ?><?= date('Y.m.d', strtotime($r['created_at'])) ?>
                            </p>
                            <h3 class="bg-ranked-title"><?= blog_search_highlight(db_field($r, 'title'), $terms) ?></h3>
                            <p class="bg-ranked-summary bg-search-snippet"><?= blog_search_snippet($text, $terms, db_field($r, 'summary')) ?></p>
                        </div>
                    </a>
                </li>
                <?php endforeach; ?>
            </ol>

            <?php if ($totalPages > 1): $qs = 'q=' . rawurlencode($q) . '&amp;'; ?>
            <nav class="bg-pagination" aria-label="<?= htmlspecialchars(t('blog_page')) ?>">
                <a class="bg-page-arrow <?= $page <= 1 ? 'disabled' : '' ?>" href="?<?= $qs ?>page=<?= max(1, $page - 1) ?>" aria-label="<?= htmlspecialchars(t('blog_prev_page')) ?>"><i class="bi bi-chevron-left"></i></a>
                <?php for ($pn = 1; $pn <= $totalPages; $pn++): ?>
                <a class="bg-page-num <?= $pn === $page ? 'active' : '' ?>" href="?<?= $qs ?>page=<?= $pn ?>"><?= $pn ?></a>
                <?php endfor; ?>
                <a class="bg-page-arrow <?= $page >= $totalPages ? 'disabled' : '' ?>" href="?<?= $qs ?>page=<?= min($totalPages, $page + 1) ?>" aria-label="<?= htmlspecialchars(t('blog_next_page')) ?>"><i class="bi bi-chevron-right"></i></a>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
