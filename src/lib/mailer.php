<?php
define('SITE_URL',   'https://studio.pyeongmok.com');
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
function send_mail(string $to, string $subject, string $template, array $vars = [], string $extra_headers = '', string $fromType = 'sales', string $bcc = ''): bool {
    $tpl = __DIR__ . '/../components/mailform/' . $template . '.php';
    if (!file_exists($tpl)) return false;

    extract($vars, EXTR_SKIP);
    ob_start();
    include $tpl;
    $html = ob_get_clean();

    $cfg  = mail_config();
    $from = $fromType === 'member' ? $cfg['mail_member'] : $cfg['mail_sales'];

    // 앱 비밀번호 미설정 시 fallback
    if (!$cfg['mail_smtp_pass']) return _mail_fallback($to, $subject, $html, $from, $extra_headers, $bcc);

    return _smtp_send($cfg['mail_smtp_user'], $cfg['mail_smtp_pass'], $from, $to, $subject, $html, $extra_headers, $bcc);
}

function _smtp_send(string $authUser, string $authPass, string $from, string $to, string $subject, string $html, string $extra_headers = '', string $bcc = ''): bool {
    $sock = @stream_socket_client('tcp://' . SMTP_HOST . ':' . SMTP_PORT, $errno, $errstr, 10);
    if (!$sock) return false;
    stream_set_timeout($sock, 10);

    $read = function() use ($sock) { return fgets($sock, 512); };
    $send = function(string $cmd) use ($sock) { fwrite($sock, $cmd . "\r\n"); };

    $read(); // 220 greeting

    $send('EHLO studio.pyeongmok.com');
    while (true) { $r = $read(); if ($r === false || substr($r, 3, 1) === ' ') break; }

    $send('STARTTLS');
    $read(); // 220

    if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        fclose($sock); return false;
    }

    $send('EHLO studio.pyeongmok.com');
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

function _mail_fallback(string $to, string $subject, string $html, string $from, string $extra_headers = '', string $bcc = ''): bool {
    $subj_enc = '=?UTF-8?B?' . base64_encode('[' . SITE_NAME . '] ' . $subject) . '?=';
    $parts    = [
        'From: ' . SITE_NAME . ' <' . $from . '>',
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'Content-Transfer-Encoding: base64',
    ];
    if ($extra_headers !== '') $parts[] = $extra_headers;
    $ok = mail($to, $subj_enc, base64_encode($html), implode("\r\n", $parts));
    if ($bcc && $bcc !== $to) mail($bcc, $subj_enc, base64_encode($html), implode("\r\n", $parts));
    return $ok;
}
