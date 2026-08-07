<?php
define('SITE_URL',   'https://pyeongmok.com');
define('SITE_NAME',  '평목');

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

// 첨부파일이 없으면 기존과 동일한 단일 text/html 파트, 있으면 multipart/mixed로 감싼다
function _build_mime_part(string $html, array $attachments): array {
    if (!$attachments) {
        return [
            "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64",
            chunk_split(base64_encode($html)),
        ];
    }
    $boundary = 'pmok_' . bin2hex(random_bytes(12));
    $body  = "--{$boundary}\r\n"
           . "Content-Type: text/html; charset=UTF-8\r\n"
           . "Content-Transfer-Encoding: base64\r\n\r\n"
           . chunk_split(base64_encode($html)) . "\r\n";

    foreach ($attachments as $att) {
        $content = isset($att['tmp_name']) ? @file_get_contents($att['tmp_name']) : ($att['content'] ?? '');
        if (!$content) continue;
        $filename = $att['name'] ?? 'attachment';
        $mime     = $att['type'] ?: 'application/octet-stream';
        $encName  = '=?UTF-8?B?' . base64_encode($filename) . '?=';
        $body .= "--{$boundary}\r\n"
               . "Content-Type: {$mime}; name=\"{$encName}\"\r\n"
               . "Content-Transfer-Encoding: base64\r\n"
               . "Content-Disposition: attachment; filename=\"{$encName}\"\r\n\r\n"
               . chunk_split(base64_encode($content)) . "\r\n";
    }
    $body .= "--{$boundary}--\r\n";

    return ["Content-Type: multipart/mixed; boundary=\"{$boundary}\"", $body];
}

function _smtp_send(string $authUser, string $authPass, string $from, string $to, string $subject, string $mimeHeaders, string $body, string $extra_headers = '', string $bcc = ''): bool {
    $sock = @stream_socket_client('tcp://' . SMTP_HOST . ':' . SMTP_PORT, $errno, $errstr, 10);
    if (!$sock) return false;
    stream_set_timeout($sock, 10);

    $read = function() use ($sock) { return fgets($sock, 512); };
    $send = function(string $cmd) use ($sock) { fwrite($sock, $cmd . "\r\n"); };

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
    if ($bcc && $bcc !== $to) {
        $send('RCPT TO:<' . $bcc . '>');
        $read();
    }
    $send('DATA');
    $read();

    $subj = '=?UTF-8?B?' . base64_encode('[' . SITE_NAME . '] ' . $subject) . '?=';
    $msg  = "From: " . SITE_NAME . " <{$from}>\r\n"
          . "To: {$to}\r\n"
          . "Subject: {$subj}\r\n"
          . "MIME-Version: 1.0\r\n"
          . $mimeHeaders . "\r\n";
    if ($extra_headers) $msg .= $extra_headers . "\r\n";
    $msg .= "\r\n" . $body;

    $send($msg . "\r\n.");
    $resp = $read();
    $send('QUIT');
    fclose($sock);

    return substr(trim($resp), 0, 3) === '250';
}

function _mail_fallback(string $to, string $subject, string $mimeHeaders, string $body, string $from, string $extra_headers = '', string $bcc = ''): bool {
    $subj_enc = '=?UTF-8?B?' . base64_encode('[' . SITE_NAME . '] ' . $subject) . '?=';
    $parts    = [
        'From: ' . SITE_NAME . ' <' . $from . '>',
        'MIME-Version: 1.0',
        $mimeHeaders,
    ];
    if ($extra_headers !== '') $parts[] = $extra_headers;
    $ok = mail($to, $subj_enc, $body, implode("\r\n", $parts));
    if ($bcc && $bcc !== $to) mail($bcc, $subj_enc, $body, implode("\r\n", $parts));
    return $ok;
}
