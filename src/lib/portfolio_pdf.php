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

/**
 * PDF 1페이지(표지) 미리보기 JPG — 회사 소개의 포트폴리오 모달에 보여준다.
 * uploads/promo/<같은 32자리>.cover.jpg 로 한 번 만들어 두고 다시 쓴다 (없으면 이때 Ghostscript로 생성, 약 0.6초).
 * 서버에 gs가 없거나(로컬 Mac 등) 변환에 실패하면 null → 모달은 그림 없이 뜬다.
 * 반환: 웹 경로 '/uploads/promo/<hash>.cover.jpg?v=<수정시각>'
 */
function portfolio_pdf_cover(string $pdfFileName): ?string {
    if (!preg_match('/^([0-9a-f]{32})\.pdf$/', $pdfFileName, $m)) return null;
    $dir   = __DIR__ . '/../../uploads/promo';
    $pdf   = $dir . '/' . $pdfFileName;
    $cover = $dir . '/' . $m[1] . '.cover.jpg';
    if (!is_file($cover)) {
        if (!is_file($pdf) || !is_writable($dir)) return null;
        $gs = is_executable('/usr/bin/gs') ? '/usr/bin/gs' : null;
        if (!$gs) return null;
        $tmp = $cover . '.' . getmypid() . '.tmp';
        $cmd = escapeshellarg($gs) . ' -q -dNOPAUSE -dBATCH -dSAFER -sDEVICE=jpeg -dJPEGQ=85 -r110'
             . ' -dFirstPage=1 -dLastPage=1 -dTextAlphaBits=4 -dGraphicsAlphaBits=4'
             . ' -sOutputFile=' . escapeshellarg($tmp) . ' ' . escapeshellarg($pdf) . ' 2>/dev/null';
        exec($cmd, $out, $rc);
        if ($rc !== 0 || !is_file($tmp) || filesize($tmp) < 1000) { @unlink($tmp); return null; }
        @chmod($tmp, 0644);
        if (!@rename($tmp, $cover)) { @unlink($tmp); return null; }
    }
    return '/uploads/promo/' . $m[1] . '.cover.jpg?v=' . (int)filemtime($cover);
}
