<?php
define('SITE_URL',   'https://windows.pyeongmok.com');
define('SITE_NAME',  '평목');
define('MAIL_FROM',  'noreply@windows.pyeongmok.com');

function send_mail(string $to, string $subject, string $template, array $vars = [], string $extra_headers = ''): bool {
    $tpl = __DIR__ . '/../components/mailform/' . $template . '.php';
    if (!file_exists($tpl)) return false;

    extract($vars, EXTR_SKIP);
    ob_start();
    include $tpl;
    $html = ob_get_clean();

    $subj_enc = '=?UTF-8?B?' . base64_encode('[' . SITE_NAME . '] ' . $subject) . '?=';
    $parts    = [
        'From: ' . SITE_NAME . ' <' . MAIL_FROM . '>',
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'Content-Transfer-Encoding: base64',
    ];
    if ($extra_headers !== '') $parts[] = $extra_headers;
    $headers = implode("\r\n", $parts);

    return mail($to, $subj_enc, base64_encode($html), $headers);
}
