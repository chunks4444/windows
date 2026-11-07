<?php
define('SITE_URL',   'https://pyeongmok.com');
define('SITE_NAME',  '워크그룹 평목');

// ── SMTP 서버 ─────────────────────────────────────────────
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);

// ── 메일 계정/주소 설정 (site_config 테이블, 관리자 페이지에서 수정) ──
// mail_smtp_user/mail_smtp_pass: SMTP 로그인 계정/앱 비밀번호
// mail_sales : 영업(문의·견적) 메일   mail_member : 회원(가입·비밀번호) 메일
function mail_config(): array {
    static $cfg = null;
    if ($cfg !== null) return $cfg;
    $cfg = [
        'mail_smtp_user' => 'pyeongmok@gmail.com',
        'mail_smtp_pass' => '',
        'mail_sales'     => 'pyeongmok@gmail.com',
        'mail_member'    => 'pyeongmok@gmail.com',
    ];
    try {
        require_once __DIR__ . '/db.php';
        $rows = db()->query("SELECT key_name, value FROM site_config WHERE key_name LIKE 'mail_%'")->fetchAll();
        foreach ($rows as $r) {
            if ($r['value'] !== '') $cfg[$r['key_name']] = $r['value'];
        }
    } catch (Throwable $e) {}
    return $cfg;
}

function mail_address(string $type = 'sales'): string {
    $cfg = mail_config();
    return $type === 'member' ? $cfg['mail_member'] : $cfg['mail_sales'];
}

// ── SMTP 발송 ────────────────────────────────────────────
// $attachments: [['name'=>string, 'tmp_name'=>string, 'type'=>string], ...] ($_FILES 형식 그대로 넘기면 됨)
function send_mail(string $to, string $subject, string $template, array $vars = [], string $extra_headers = '', string $fromType = 'sales', string $bcc = '', array $attachments = []): bool {
    $tpl = __DIR__ . '/../components/mailform/' . $template . '.php';
    if (!file_exists($tpl)) return false;

    extract($vars, EXTR_SKIP);
    ob_start();
    include $tpl;
    $html = ob_get_clean();

    $cfg  = mail_config();
    $from = $fromType === 'member' ? $cfg['mail_member'] : $cfg['mail_sales'];

    [$mimeHeaders, $body] = _build_mime_part($html, $attachments);

    // 앱 비밀번호 미설정 시 fallback
    if (!$cfg['mail_smtp_pass']) return _mail_fallback($to, $subject, $mimeHeaders, $body, $from, $extra_headers, $bcc);

    return _smtp_send($cfg['mail_smtp_user'], $cfg['mail_smtp_pass'], $from, $to, $subject, $mimeHeaders, $body, $extra_headers, $bcc);
}

// 본문 <img src="data:image/...;base64,...">를 CID 인라인 이미지로 바꾼다.
// Gmail 등 대부분의 메일 클라이언트는 data: 주소 이미지를 차단해 깨진 이미지로 보이기 때문
// (견적요청 메일의 도면 썸네일이 대표적). 이미지는 메일 안에 함께 실려 가므로 외부 공개 주소가 필요 없다.
function _extract_inline_images(string &$html): array {
    $images = [];
    $html = preg_replace_callback(
        '/src=(["\'])data:(image\/(?:png|jpeg|gif|webp));base64,([A-Za-z0-9+\/=\s]+)\1/',
        function ($m) use (&$images) {
            $binary = base64_decode(preg_replace('/\s+/', '', $m[3]), true);
            if ($binary === false) return $m[0];
            $cid = 'img' . count($images) . '_' . bin2hex(random_bytes(6)) . '@pyeongmok.com';
            $images[] = ['cid' => $cid, 'mime' => $m[2], 'content' => $binary];
            return 'src=' . $m[1] . 'cid:' . $cid . $m[1];
        },
        $html
    );
    return $images;
}

// 본문(html) 파트: 인라인 이미지가 없으면 단일 text/html, 있으면 multipart/related로 묶는다.
function _build_html_part(string $html): array {
    $images = _extract_inline_images($html);
    $htmlHeaders = "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64";
    $htmlBody    = chunk_split(base64_encode($html));
    if (!$images) return [$htmlHeaders, $htmlBody];

    $boundary = 'pmok_rel_' . bin2hex(random_bytes(12));
    $body = "--{$boundary}\r\n{$htmlHeaders}\r\n\r\n{$htmlBody}\r\n";
    foreach ($images as $i => $img) {
        $ext = substr($img['mime'], 6);
        $body .= "--{$boundary}\r\n"
               . "Content-Type: {$img['mime']}; name=\"image{$i}.{$ext}\"\r\n"
               . "Content-Transfer-Encoding: base64\r\n"
               . "Content-ID: <{$img['cid']}>\r\n"
               . "Content-Disposition: inline; filename=\"image{$i}.{$ext}\"\r\n\r\n"
               . chunk_split(base64_encode($img['content'])) . "\r\n";
    }
    $body .= "--{$boundary}--\r\n";
    return ["Content-Type: multipart/related; type=\"text/html\"; boundary=\"{$boundary}\"", $body];
}

// 첨부파일이 없으면 본문 파트 그대로, 있으면 multipart/mixed로 감싼다
function _build_mime_part(string $html, array $attachments): array {
    [$htmlHeaders, $htmlBody] = _build_html_part($html);
    if (!$attachments) return [$htmlHeaders, $htmlBody];

    $boundary = 'pmok_' . bin2hex(random_bytes(12));
    $body  = "--{$boundary}\r\n"
           . $htmlHeaders . "\r\n\r\n"
           . $htmlBody . "\r\n";

    foreach ($attachments as $att) {
        $content = isset($att['tmp_name']) ? @file_get_contents($att['tmp_name']) : ($att['content'] ?? '');
        if (!$content) continue;
        $filename = $att['name'] ?? 'attachment';
        $mime     = $att['type'] ?: 'application/octet-stream';
        // 파일 이름 — 따옴표 안에 =?UTF-8?B?…?=를 넣던 방식은 표준이 아니라 메일앱에 따라 한글 이름이 깨지고 확장자(.pdf)도 못 알아봤다(2026-11-04).
        // RFC 2231(filename*=UTF-8''퍼센트인코딩)로 한글 이름을 주고, 그걸 모르는 메일앱용으로 영문만 남긴 이름을 같이 준다.
        $ext   = pathinfo($filename, PATHINFO_EXTENSION);
        $ascii = trim(preg_replace('/_+/', '_', preg_replace('/[^A-Za-z0-9._-]/', '_', pathinfo($filename, PATHINFO_FILENAME))), '_');
        $ascii = ($ascii !== '' ? $ascii : 'attachment') . ($ext !== '' ? '.' . preg_replace('/[^A-Za-z0-9]/', '', $ext) : '');
        $star  = "UTF-8''" . rawurlencode($filename);
        $body .= "--{$boundary}\r\n"
               . "Content-Type: {$mime}; name=\"{$ascii}\"\r\n"
               . "Content-Transfer-Encoding: base64\r\n"
               . "Content-Disposition: attachment; filename=\"{$ascii}\";\r\n filename*={$star}\r\n\r\n"
               . chunk_split(base64_encode($content)) . "\r\n";
    }
    $body .= "--{$boundary}--\r\n";

    return ["Content-Type: multipart/mixed; boundary=\"{$boundary}\"", $body];
}

// 템플릿 파일 없이 완성된 HTML을 그대로 보낸다 (홍보메일용). 제목 앞에 [사이트명]을 붙이지 않는다 — "(광고)"가 맨 앞이어야 해서.
function send_html_mail(string $to, string $subject, string $html, string $extra_headers = '', array $attachments = []): bool {
    $cfg  = mail_config();
    $from = $cfg['mail_sales'];
    [$mimeHeaders, $body] = _build_mime_part($html, $attachments);
    if (!$cfg['mail_smtp_pass']) return _mail_fallback($to, $subject, $mimeHeaders, $body, $from, $extra_headers, '', true);
    return _smtp_send($cfg['mail_smtp_user'], $cfg['mail_smtp_pass'], $from, $to, $subject, $mimeHeaders, $body, $extra_headers, '', true);
}

function _smtp_send(string $authUser, string $authPass, string $from, string $to, string $subject, string $mimeHeaders, string $body, string $extra_headers = '', string $bcc = '', bool $rawSubject = false): bool {
    $sock = @stream_socket_client('tcp://' . SMTP_HOST . ':' . SMTP_PORT, $errno, $errstr, 10);
    if (!$sock) return false;
    stream_set_timeout($sock, 10);

    $read = function() use ($sock) { return fgets($sock, 512); };
    // TLS 소켓은 fwrite가 일부만 쓰고 돌아올 수 있다 — PDF 첨부로 메일이 커지면 끝까지 반복해서 쓴다
    $send = function(string $cmd) use ($sock) {
        $data = $cmd . "\r\n"; $len = strlen($data); $off = 0;
        while ($off < $len) {
            $n = @fwrite($sock, substr($data, $off, 65536));
            if ($n === false || $n === 0) return false;
            $off += $n;
        }
        return true;
    };

    $read(); // 220 greeting

    $send('EHLO pyeongmok.com');
    while (true) { $r = $read(); if ($r === false || substr($r, 3, 1) === ' ') break; }

    $send('STARTTLS');
    $read(); // 220

    if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        fclose($sock); return false;
    }

    $send('EHLO pyeongmok.com');
    while (true) { $r = $read(); if ($r === false || substr($r, 3, 1) === ' ') break; }

    $send('AUTH LOGIN');
    $read();
    $send(base64_encode($authUser));
    $read();
    $send(base64_encode($authPass));
    $auth = $read();
    if (substr(trim($auth), 0, 3) !== '235') { fclose($sock); return false; }

    $send('MAIL FROM:<' . $from . '>');
    $read();
    $send('RCPT TO:<' . $to . '>');
    $read();
    // 숨은참조 — 쉼표로 여러 주소 가능(헤더엔 안 쓰고 RCPT로만 넣으므로 받는 사람에게 안 보임)
    foreach (_bcc_list($bcc, $to) as $b) {
        $send('RCPT TO:<' . $b . '>');
        $read();
    }
    $send('DATA');
    $read();

    $subj = _mime_header($rawSubject ? $subject : '[' . SITE_NAME . '] ' . $subject);
    $msg  = "From: " . _mime_header(SITE_NAME) . " <{$from}>\r\n"
          . "To: {$to}\r\n"
          . "Subject: {$subj}\r\n"
          . "MIME-Version: 1.0\r\n"
          . $mimeHeaders . "\r\n";
    if ($extra_headers) $msg .= $extra_headers . "\r\n";
    $msg .= "\r\n" . $body;

    // 본문 전송 뒤 Gmail이 큰 메일(PDF 첨부 등)을 받아 처리하는 동안은 10초를 넘길 수 있다.
    // 응답을 못 읽었다고 실패로 처리하면 메일은 이미 나갔는데 이력만 '실패'가 되므로 대기 시간을 늘린다.
    stream_set_timeout($sock, 120);
    $wrote = $send($msg . "\r\n.");
    $resp = $wrote ? $read() : false;
    $ok = $resp !== false && substr(trim($resp), 0, 3) === '250';
    if (!$ok) error_log('smtp send failed to ' . $to . ': ' . ($wrote ? var_export(trim((string)$resp), true) : 'write failed') . ' (message ' . strlen($msg) . ' bytes)');
    $send('QUIT');
    fclose($sock);

    return $ok;
}

// 한글 등 비ASCII 헤더(제목·보내는 사람 이름) 인코딩 — RFC 2047은 인코딩 덩어리 하나를 75자 이하로 제한한다.
// 예전엔 긴 제목을 한 덩어리로 넣어 일부 메일앱에서 제목이 깨졌고, From 이름은 인코딩 없이 들어갔다(2026-11-04 수정).
// 글자 중간에서 자르지 않도록 UTF-8 글자 단위로 45바이트(base64 60자)씩 나눠 줄바꿈+공백으로 잇는다.
function _mime_header(string $text): string {
    if (!preg_match('/[^\x20-\x7E]/', $text)) return $text;
    $chunks = []; $buf = '';
    foreach (mb_str_split($text, 1, 'UTF-8') as $ch) {
        if (strlen($buf) + strlen($ch) > 45) { $chunks[] = $buf; $buf = ''; }
        $buf .= $ch;
    }
    if ($buf !== '') $chunks[] = $buf;
    return implode("\r\n ", array_map(fn($c) => '=?UTF-8?B?' . base64_encode($c) . '?=', $chunks));
}

function _mail_fallback(string $to, string $subject, string $mimeHeaders, string $body, string $from, string $extra_headers = '', string $bcc = '', bool $rawSubject = false): bool {
    $subj_enc = _mime_header($rawSubject ? $subject : '[' . SITE_NAME . '] ' . $subject);
    $parts    = [
        'From: ' . _mime_header(SITE_NAME) . ' <' . $from . '>',
        'MIME-Version: 1.0',
        $mimeHeaders,
    ];
    if ($extra_headers !== '') $parts[] = $extra_headers;
    $ok = mail($to, $subj_enc, $body, implode("\r\n", $parts));
    foreach (_bcc_list($bcc, $to) as $b) mail($b, $subj_enc, $body, implode("\r\n", $parts));
    return $ok;
}

// "a@x.com, b@y.com" → 유효한 주소 배열(중복·받는 사람 본인 제외)
function _bcc_list(string $bcc, string $to): array {
    $out = [];
    foreach (preg_split('/\s*,\s*/', trim($bcc), -1, PREG_SPLIT_NO_EMPTY) as $b) {
        $k = strtolower($b);
        if ($k === strtolower($to) || isset($out[$k]) || !filter_var($b, FILTER_VALIDATE_EMAIL)) continue;
        $out[$k] = $b;
    }
    return array_values($out);
}
