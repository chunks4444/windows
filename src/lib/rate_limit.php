<?php
function rate_limit_check(string $key, int $max, int $window_sec): bool {
    $dir  = sys_get_temp_dir() . '/pmok_rl';
    if (!is_dir($dir)) mkdir($dir, 0700, true);
    $file = $dir . '/' . md5($key) . '.json';
    $now  = time();

    $times = [];
    if (file_exists($file)) {
        $times = json_decode(@file_get_contents($file), true) ?? [];
    }
    $times = array_values(array_filter($times, fn($t) => $t > $now - $window_sec));

    if (count($times) >= $max) return false;

    $times[] = $now;
    file_put_contents($file, json_encode($times), LOCK_EX);
    return true;
}
