<?php
function handle_pwgen(&$result, $source, $query, $cfg, $key) {
    if ($source !== 'pwgen_generate') return;
    $len = intval($_GET['len'] ?? 16);
    if ($len < 4 || $len > 128) $len = 16;
    $upper = $_GET['upper'] ?? '1';
    $lower = $_GET['lower'] ?? '1';
    $digits = $_GET['digits'] ?? '1';
    $symbols = $_GET['symbols'] ?? '1';
    $chars = '';
    if ($upper === '1') $chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    if ($lower === '1') $chars .= 'abcdefghijklmnopqrstuvwxyz';
    if ($digits === '1') $chars .= '0123456789';
    if ($symbols === '1') $chars .= '!@#$%^&*()-_=+[]{}|;:,.<>?';
    if (empty($chars)) $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $password = '';
    $max = mb_strlen($chars) - 1;
    for ($i = 0; $i < $len; $i++) $password .= $chars[random_int(0, $max)];
    $result['success'] = true;
    $result['data'] = [
        'password' => $password,
        'length' => $len,
        'strength' => $len >= 20 ? '强' : ($len >= 12 ? '中' : '弱'),
    ];
}
