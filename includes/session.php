<?php

function toolbox_session_root() {
    return realpath(__DIR__ . '/..') ?: dirname(__DIR__);
}

function toolbox_session_is_writable_dir($dir) {
    if (!$dir || !is_dir($dir) || !is_writable($dir)) {
        return false;
    }
    $probe = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.session_probe_' . bin2hex(random_bytes(4));
    $ok = @file_put_contents($probe, '1', LOCK_EX) !== false;
    if ($ok) {
        @unlink($probe);
    }
    return $ok;
}

function toolbox_session_prepare_dir($dir) {
    if (!is_dir($dir) && !@mkdir($dir, 0700, true)) {
        return false;
    }
    @chmod($dir, 0700);
    @file_put_contents(rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'index.html', '');
    @file_put_contents(rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.htaccess', "Require all denied\nDeny from all\n");
    return toolbox_session_is_writable_dir($dir);
}

function toolbox_session_configure() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return true;
    }

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', $secure ? '1' : '0');
    ini_set('session.cookie_samesite', 'Strict');

    $current = session_save_path();
    if ($current && strpos($current, ';') !== false) {
        $parts = explode(';', $current);
        $current = end($parts);
    }

    if (!toolbox_session_is_writable_dir($current)) {
        $root = toolbox_session_root();
        $candidates = [
            rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'cx_toolbox_sessions',
            $root . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'sessions',
        ];
        foreach ($candidates as $dir) {
            if (toolbox_session_prepare_dir($dir)) {
                ini_set('session.save_path', $dir);
                break;
            }
        }
    }

    return true;
}

function toolbox_session_start() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return true;
    }
    toolbox_session_configure();
    $ok = @session_start();
    if (!$ok && function_exists('error_log')) {
        error_log('toolbox session_start failed; save_path=' . session_save_path());
    }
    return $ok;
}

function toolbox_session_regenerate() {
    if (session_status() !== PHP_SESSION_ACTIVE && !toolbox_session_start()) {
        return false;
    }
    return @session_regenerate_id(true);
}

function toolbox_session_clear_cookie() {
    if (!ini_get('session.use_cookies')) {
        return;
    }
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

function toolbox_session_destroy() {
    toolbox_session_start();
    $_SESSION = [];
    toolbox_session_clear_cookie();
    if (session_status() === PHP_SESSION_ACTIVE) {
        @session_destroy();
    }
}
