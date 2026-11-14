<?php
// 사이트에 공개하는 '포트폴리오 PDF' — 회사 소개의 다운로드 버튼과 관리자 업로드 페이지가 함께 쓴다.
// 저장: site_config 'portfolio_pdf' (JSON {path, name, size, uploaded_at}). 아직 올린 적이 없으면
// 홍보메일 템플릿(#1)에 첨부된 PDF를 대신 쓴다 (도입 즉시 동작하도록). 파일은 uploads/promo/<32자리 16진수>.pdf.

const PORTFOLIO_PDF_KEY = 'portfolio_pdf';

/** 현재 공개 중인 포트폴리오 PDF. 없으면 null. ['source'=>'upload'|'promo', 'path', 'file', 'name', 'size', 'uploaded_at', 'url'] */
function portfolio_pdf_current(PDO $pdo): ?array {
    $root = __DIR__ . '/../..';
    $cands = [];

    $st = $pdo->prepare('SELECT value FROM site_config WHERE key_name = ?');
    $st->execute([PORTFOLIO_PDF_KEY]);
    $raw = $st->fetchColumn();
    if ($raw !== false && ($j = json_decode((string)$raw, true)) && !empty($j['path'])) {
        $cands[] = ['source' => 'upload', 'path' => (string)$j['path'], 'name' => (string)($j['name'] ?? ''), 'uploaded_at' => $j['uploaded_at'] ?? null];
    }
    try {
        $t = $pdo->query('SELECT attachment_path, attachment_name FROM promo_templates WHERE id = 1')->fetch();
        if ($t && (string)$t['attachment_path'] !== '') {
            $cands[] = ['source' => 'promo', 'path' => (string)$t['attachment_path'], 'name' => (string)$t['attachment_name'], 'uploaded_at' => null];
        }
    } catch (Throwable $e) { /* 홍보 표가 없는 환경 */ }

    foreach ($cands as $c) {
        if (!preg_match('~^/uploads/promo/([0-9a-f]{32}\.pdf)$~', $c['path'], $m)) continue;
        $file = $root . $c['path'];
        if (!is_file($file)) continue;
        return [
            'source'      => $c['source'],
            'path'        => $c['path'],
            'file'        => $m[1],
            'name'        => $c['name'] !== '' ? $c['name'] : 'portfolio.pdf',
            'size'        => (int)filesize($file),
            'uploaded_at' => $c['uploaded_at'] ?: date('Y-m-d H:i:s', (int)filemtime($file)),
            'url'         => '/src/api/promo/download.php?f=' . $m[1],
        ];
    }
    return null;
}
