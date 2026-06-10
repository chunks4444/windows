<?php
define('SITE_URL',   'https://windows.pyeongmok.com');
define('SITE_NAME',  '평목');
define('MAIL_FROM',  'pyeongmok@gmail.com');

// ── SMTP 설정 ─────────────────────────────────────────────
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'pyeongmok@gmail.com');
define('SMTP_PASS', '&&&chun20662782@@@');

// ── SMTP 발송 ────────────────────────────────────────────
function send_mail(string $to, string $subject, string $template, array $vars = [], string $extra_headers = ''): bool {
    $tpl = __DIR__ . '/../components/mailform/' . $template . '.php';
    if (!file_exists($tpl)) return false;

    extract($vars, EXTR_SKIP);
    ob_start();
    include $tpl;
    $html = ob_get_clean();

    // 앱 비밀번호 미설정 시 fallback
    if (!SMTP_PASS) return _mail_fallback($to, $subject, $html, $extra_headers);

    return _smtp_send(SMTP_USER, $to, $subject, $html, $extra_headers);
}

function _smtp_send(string $from, string $to, string $subject, string $html, string $extra_headers = ''): bool {
    $sock = @stream_socket_client('tcp://' . SMTP_HOST . ':' . SMTP_PORT, $errno, $errstr, 10);
    if (!$sock) return false;
    stream_set_timeout($sock, 10);

    $read = function() use ($sock) { return fgets($sock, 512); };
    $send = function(string $cmd) use ($sock) { fwrite($sock, $cmd . "\r\n"); };

    $read(); // 220 greeting

    $send('EHLO windows.pyeongmok.com');
    while (true) { $r = $read(); if ($r === false || substr($r, 3, 1) === ' ') break; }

    $send('STARTTLS');
    $read(); // 220

    if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        fclose($sock); return false;
    }

    $send('EHLO windows.pyeongmok.com');
    while (true) { $r = $read(); if ($r === false || substr($r, 3, 1) === ' ') break; }

    $send('AUTH LOGIN');
    $read();
    $send(base64_encode(SMTP_USER));
    $read();
    $send(base64_encode(SMTP_PASS));
    $auth = $read();
    if (substr(trim($auth), 0, 3) !== '235') { fclose($sock); return false; }

    $send('MAIL FROM:<' . $from . '>');
    $read();
    $send('RCPT TO:<' . $to . '>');
    $read();
    $send('DATA');
    $read();

    $subj = '=?UTF-8?B?' . base64_encode('[' . SITE_NAME . '] ' . $subject) . '?=';
    $msg  = "From: " . SITE_NAME . " <{$from}>\r\n"
          . "To: {$to}\r\n"
          . "Subject: {$subj}\r\n"
          . "MIME-Version: 1.0\r\n"
          . "Content-Type: text/html; charset=UTF-8\r\n"
          . "Content-Transfer-Encoding: base64\r\n";
    if ($extra_headers) $msg .= $extra_headers . "\r\n";
    $msg .= "\r\n" . chunk_split(base64_encode($html));

    $send($msg . "\r\n.");
    $resp = $read();
    $send('QUIT');
    fclose($sock);

    return substr(trim($resp), 0, 3) === '250';
}

function _mail_fallback(string $to, string $subject, string $html, string $extra_headers = ''): bool {
    $subj_enc = '=?UTF-8?B?' . base64_encode('[' . SITE_NAME . '] ' . $subject) . '?=';
    $parts    = [
        'From: ' . SITE_NAME . ' <' . MAIL_FROM . '>',
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'Content-Transfer-Encoding: base64',
    ];
    if ($extra_headers !== '') $parts[] = $extra_headers;
    return mail($to, $subj_enc, base64_encode($html), implode("\r\n", $parts));
}
