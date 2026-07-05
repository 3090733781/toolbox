<?php
$cfgFile = __DIR__ . '/../../config.json';
$cfg = file_exists($cfgFile) ? json_decode(file_get_contents($cfgFile), true) : [];
$oauth = $cfg['oauth'] ?? [];
$base = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
$Oauth_config['apiurl'] = $oauth['apiurl'] ?? 'https://u.23zo.cn/';
$Oauth_config['appid'] = $oauth['appid'] ?? '1000';
$Oauth_config['appkey'] = $oauth['appkey'] ?? '1111111111111111111111111111';
$Oauth_config['callback'] = $base . '/api/oauth/connect.php';
