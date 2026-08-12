<?php
// 가이드 아티클 공통 렌더러. 각 개별 파일(예: order.php)은 $guide_current만 설정하고
// 이 파일을 include한다. 제목·본문은 guide_articles 테이블에서 가져오고,
// 이전/다음 링크는 _head.php의 $guide_nav 순서를 그대로 따라 자동 계산한다.
require_once __DIR__ . '/../lib/guide_content.php';

$guide_slug = basename($guide_current, '.php');
$article    = guide_article($guide_slug);
$guide_title = $article['title'] ?? ($guide_title ?? '');

include __DIR__ . '/_head.php';

// $guide_nav는 위 _head.php에서 정의됨 — 순서대로 펼쳐서 현재 위치의 이전/다음 아티클을 찾는다.
$guide_prev = null;
$guide_next = null;
$flatArticles = [];
foreach ($guide_nav as $sec) {
    foreach ($sec['articles'] as $art) $flatArticles[] = $art;
}
foreach ($flatArticles as $i => $art) {
    if ($art['file'] !== $guide_current) continue;
    if ($i > 0) {
        $prevFile = $flatArticles[$i - 1]['file'];
        $guide_prev = ['href' => $prevFile, 'title' => $guideTitlesMap[basename($prevFile, '.php')] ?? $flatArticles[$i - 1]['title']];
    }
    if ($i < count($flatArticles) - 1) {
        $nextFile = $flatArticles[$i + 1]['file'];
        $guide_next = ['href' => $nextFile, 'title' => $guideTitlesMap[basename($nextFile, '.php')] ?? $flatArticles[$i + 1]['title']];
    }
    break;
}

echo $article['body_html'] ?? '<p>콘텐츠가 없습니다.</p>';

include __DIR__ . '/_foot.php';
