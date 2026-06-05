<?php
// 진단용 - 확인 후 삭제
$keys = ['REMOTE_ADDR','HTTP_X_REAL_IP','HTTP_X_FORWARDED_FOR','HTTP_CF_CONNECTING_IP','HTTP_CF_IPCOUNTRY','HTTP_X_FORWARDED','HTTP_CLIENT_IP'];
foreach ($keys as $k) {
    echo $k . ': ' . ($_SERVER[$k] ?? '(없음)') . "\n";
}
