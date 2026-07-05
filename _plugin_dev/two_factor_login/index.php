<?php
/**
 * 插件名称：二次验证登录
 * 插件说明：生成和校验 TOTP 动态验证码。
 */

function twofa_base32_encode($data) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $bits = '';
    $encoded = '';
    $len = strlen($data);
    for ($i = 0; $i < $len; $i++) {
        $bits .= str_pad(decbin(ord($data[$i])), 8, '0', STR_PAD_LEFT);
    }
    for ($i = 0; $i < strlen($bits); $i += 5) {
        $chunk = substr($bits, $i, 5);
        if (strlen($chunk) < 5) $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
        $encoded .= $alphabet[bindec($chunk)];
    }
    return $encoded;
}

function twofa_base32_decode($secret) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret = strtoupper(preg_replace('/[^A-Z2-7]/i', '', $secret));
    if ($secret === '') return false;

    $bits = '';
    $len = strlen($secret);
    for ($i = 0; $i < $len; $i++) {
        $pos = strpos($alphabet, $secret[$i]);
        if ($pos === false) return false;
        $bits .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
    }

    $decoded = '';
    for ($i = 0; $i + 8 <= strlen($bits); $i += 8) {
        $decoded .= chr(bindec(substr($bits, $i, 8)));
    }
    return $decoded;
}

function twofa_totp($secret, $time = null, $step = 30, $digits = 6) {
    $key = twofa_base32_decode($secret);
    if ($key === false) return false;
    $counter = (int) floor(($time ?? time()) / $step);
    $binCounter = pack('N*', 0, $counter);
    $hash = hash_hmac('sha1', $binCounter, $key, true);
    $offset = ord($hash[19]) & 0x0f;
    $code = (
        ((ord($hash[$offset]) & 0x7f) << 24) |
        ((ord($hash[$offset + 1]) & 0xff) << 16) |
        ((ord($hash[$offset + 2]) & 0xff) << 8) |
        (ord($hash[$offset + 3]) & 0xff)
    ) % pow(10, $digits);
    return str_pad((string) $code, $digits, '0', STR_PAD_LEFT);
}

function twofa_json_exit($data) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function handle_two_factor_login(&$result, $source, $query, $cfg, $key) {
    switch ($source) {
        case 'twofa_generate':
            $issuer = trim($_GET['issuer'] ?? $_POST['issuer'] ?? '工具箱');
            $account = trim($_GET['account'] ?? $_POST['account'] ?? 'admin');
            if ($issuer === '') $issuer = '工具箱';
            if ($account === '') $account = 'admin';

            $secret = twofa_base32_encode(random_bytes(20));
            $label = rawurlencode($issuer . ':' . $account);
            $params = http_build_query([
                'secret' => $secret,
                'issuer' => $issuer,
                'algorithm' => 'SHA1',
                'digits' => 6,
                'period' => 30,
            ], '', '&', PHP_QUERY_RFC3986);

            $result['success'] = true;
            $result['data'] = [
                'secret' => $secret,
                'otp_auth_url' => 'otpauth://totp/' . $label . '?' . $params,
                'current_code' => twofa_totp($secret),
                'period' => 30,
                'digits' => 6,
            ];
            return;

        case 'twofa_verify':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) $input = $_POST;
            $secret = trim($input['secret'] ?? $_GET['secret'] ?? '');
            $code = preg_replace('/\D/', '', (string)($input['code'] ?? $_GET['code'] ?? ''));
            $window = intval($input['window'] ?? $_GET['window'] ?? 1);
            if ($window < 0 || $window > 3) $window = 1;
            if ($secret === '' || $code === '') { $result['error'] = '请输入密钥和验证码'; return; }
            if (strlen($code) !== 6) { $result['error'] = '验证码必须是6位数字'; return; }
            if (twofa_base32_decode($secret) === false) { $result['error'] = '密钥格式不正确'; return; }

            $ok = false;
            $matchedOffset = null;
            for ($i = -$window; $i <= $window; $i++) {
                $candidate = twofa_totp($secret, time() + ($i * 30));
                $matched = function_exists('hash_equals') ? hash_equals($candidate, $code) : $candidate === $code;
                if ($matched) {
                    $ok = true;
                    $matchedOffset = $i;
                    break;
                }
            }

            $result['success'] = true;
            $result['data'] = [
                'valid' => $ok,
                'matched_offset' => $matchedOffset,
                'server_time' => date('Y-m-d H:i:s'),
            ];
            if (!$ok) $result['error'] = '验证码无效或已过期';
            return;
    }
}

if (isset($_GET['source']) && in_array($_GET['source'], ['twofa_generate', 'twofa_verify'], true)) {
    $result = ['source' => $_GET['source'], 'success' => false, 'data' => [], 'error' => null];
    handle_two_factor_login($result, $_GET['source'], $_GET['query'] ?? '', [], '');
    twofa_json_exit($result);
}
