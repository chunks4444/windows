<?php
// 공지 글(공개 페이지 /notice/) 공용 함수 — 공개 목록·상세, 관리자 API, 사이트맵이 함께 쓴다.
// 관리자(슈퍼)가 Quill 편집기로 쓴 HTML을 저장하지만, 저장할 때 한 번 더 허용 목록으로 걸러서 스크립트·이벤트 속성이 남지 않게 한다.

require_once __DIR__ . '/db.php';

function notice_ensure_table(PDO $pdo): void {
    static $done = false;
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS notices (
        id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
        title        VARCHAR(200) NOT NULL,
        title_en     VARCHAR(200) NULL,
        label        VARCHAR(30)  NULL,
        label_en     VARCHAR(30)  NULL,
        body         MEDIUMTEXT   NOT NULL,
        body_en      MEDIUMTEXT   NULL,
        cta_text     VARCHAR(60)  NULL,
        cta_text_en  VARCHAR(60)  NULL,
        cta_url      VARCHAR(300) NULL,
        is_pinned    TINYINT(1)   NOT NULL DEFAULT 0,
        is_active    TINYINT(1)   NOT NULL DEFAULT 1,
        published_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_notice_list (is_active, is_pinned, published_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    if ($done) return;
    $done = true;
    try {
        $pdo->query('SELECT label, label_en, cta_text, cta_text_en, cta_url FROM notices LIMIT 0');
    } catch (Throwable $e) {
        $pdo->exec("ALTER TABLE notices
            ADD COLUMN label VARCHAR(30) NULL AFTER title_en,
            ADD COLUMN label_en VARCHAR(30) NULL AFTER label,
            ADD COLUMN cta_text VARCHAR(60) NULL AFTER body_en,
            ADD COLUMN cta_text_en VARCHAR(60) NULL AFTER cta_text,
            ADD COLUMN cta_url VARCHAR(300) NULL AFTER cta_text_en");
    }
}
const NOTICE_ALLOWED_TAGS  = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'a', 'ul', 'ol', 'li', 'h2', 'h3', 'blockquote'];
const NOTICE_DROPPED_TAGS  = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'svg', 'math', 'link', 'meta', 'base', 'noscript', 'template'];

const NOTICE_CONTACT_EMAIL = 'pyeongmok@gmail.com';

function notice_safe_href(string $href, bool $allowForm = false): ?string {
    $href = trim($href);
    if ($href === '') return null;
    if ($allowForm && $href === '#contact') return $href;
    if (preg_match('#^(https?://|mailto:|tel:)#i', $href)) return $href;
    if ($href[0] === '/' && (!isset($href[1]) || $href[1] !== '/')) return $href;
    return null;
}

function notice_clean_node(DOMNode $parent): void {
    $kids = [];
    foreach ($parent->childNodes as $c) $kids[] = $c;
    foreach ($kids as $node) {
        if ($node->nodeType === XML_TEXT_NODE) continue;
        if ($node->nodeType !== XML_ELEMENT_NODE) { $parent->removeChild($node); continue; }
        $tag = strtolower($node->nodeName);

        if (in_array($tag, NOTICE_DROPPED_TAGS, true)) { $parent->removeChild($node); continue; }

        notice_clean_node($node);

        if (!in_array($tag, NOTICE_ALLOWED_TAGS, true)) {
            while ($node->firstChild) $parent->insertBefore($node->firstChild, $node);
            $parent->removeChild($node);
            continue;
        }

        $href = $tag === 'a' ? $node->getAttribute('href') : '';
        $bullet = $tag === 'li' ? $node->getAttribute('data-list') : '';
        while ($node->attributes->length > 0) $node->removeAttributeNode($node->attributes->item(0));

        if ($tag === 'a') {
            $safe = notice_safe_href($href);
            if ($safe === null) {
                while ($node->firstChild) $parent->insertBefore($node->firstChild, $node);
                $parent->removeChild($node);
                continue;
            }
            $node->setAttribute('href', $safe);
            if (preg_match('#^https?://#i', $safe)) {
                $node->setAttribute('target', '_blank');
                $node->setAttribute('rel', 'noopener noreferrer');
            }
        }
        if ($tag === 'li' && $bullet === 'bullet') $node->setAttribute('data-bullet', '1');
    }
}

function notice_sanitize_html(string $html): string {
    $html = trim($html);
    if ($html === '') return '';
    $doc = new DOMDocument('1.0', 'UTF-8');
    $prev = libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8"><div id="nt-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($prev);
    $root = $doc->getElementById('nt-root');
    if (!$root) return '';

    notice_clean_node($root);

    foreach (iterator_to_array($root->getElementsByTagName('ol')) as $ol) {
        $items = [];
        foreach ($ol->childNodes as $c) if ($c->nodeType === XML_ELEMENT_NODE && strtolower($c->nodeName) === 'li') $items[] = $c;
        if (!$items) continue;
        $allBullet = true;
        foreach ($items as $li) if ($li->getAttribute('data-bullet') !== '1') $allBullet = false;
        if ($allBullet) {
            $ul = $doc->createElement('ul');
            while ($ol->firstChild) $ul->appendChild($ol->firstChild);
            $ol->parentNode->replaceChild($ul, $ol);
        }
    }
    foreach (iterator_to_array($root->getElementsByTagName('li')) as $li) $li->removeAttribute('data-bullet');

    $out = '';
    foreach ($root->childNodes as $c) $out .= $doc->saveHTML($c);
    $out = trim($out);
    $plain = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($out), ENT_QUOTES, 'UTF-8')));
    return $plain === '' ? '' : $out;
}

function notice_excerpt(string $html, int $len = 110): string {
    $t = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(str_replace(['</p>', '<br>', '</li>'], ' ', $html)), ENT_QUOTES, 'UTF-8')));
    return mb_strlen($t) > $len ? mb_substr($t, 0, $len - 1) . '…' : $t;
}

function notice_article_html(array $n, bool $modal = false): string {
    $label = db_field($n, 'label');
    $ctaText = db_field($n, 'cta_text');
    $ctaUrl = notice_safe_href((string)($n['cta_url'] ?? ''), true) ?? '';
    if ($ctaUrl !== '' && $ctaUrl[0] === '/') $ctaUrl = lang_href($ctaUrl);
    $tag = $modal ? 'h2' : 'h1';
    $idAttr = $modal ? ' id="ntModalTitle"' : '';
    $ts = strtotime((string)$n['published_at']);

    $out = '<header class="nt-head">';
    if ($label !== '') $out .= '<p class="nt-kicker">' . htmlspecialchars($label) . '</p>';
    $out .= '<' . $tag . ' class="nt-h1"' . $idAttr . '>';
    if (!empty($n['is_pinned'])) $out .= '<span class="nt-pin">' . htmlspecialchars(t('notice_pinned')) . '</span>';
    $out .= htmlspecialchars(db_field($n, 'title')) . '</' . $tag . '>';
    $out .= '<time class="nt-date" datetime="' . date('Y-m-d', $ts) . '">' . date('Y.m.d', $ts) . '</time></header>';
    $out .= '<div class="nt-body">' . db_field($n, 'body') . '</div>';
    if ($ctaText !== '' && $ctaUrl === '#contact') {
        $subject = mb_substr(($label !== '' ? '[' . $label . '] ' : '') . db_field($n, 'title'), 0, 100);
        $out .= '<p class="nt-cta-wrap"><a class="nt-cta" href="' . htmlspecialchars(lang_href('/company/#contact')) . '" data-nt-contact data-subject="' . htmlspecialchars($subject) . '">' . htmlspecialchars($ctaText) . ' <i class="bi bi-arrow-right"></i></a></p>';
        $out .= '<p class="nt-cta-mail"><span>' . htmlspecialchars(t('notice_mail_label')) . '</span>' . htmlspecialchars(NOTICE_CONTACT_EMAIL) . '</p>';
    } elseif ($ctaText !== '' && $ctaUrl !== '') {
        $ext = preg_match('#^https?://#i', $ctaUrl) ? ' target="_blank" rel="noopener noreferrer"' : '';
        $out .= '<p class="nt-cta-wrap"><a class="nt-cta" href="' . htmlspecialchars($ctaUrl) . '"' . $ext . '>' . htmlspecialchars($ctaText) . ' <i class="bi bi-arrow-right"></i></a></p>';
    }
    return $out;
}
