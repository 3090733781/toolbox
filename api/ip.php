<?php
function handle_ip(&$result, $source, $query, $cfg, $key) {
    if ($source === 'myip') {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        if ($ip && strpos($ip, ',') !== false) $ip = trim(explode(',', $ip)[0]);
        if ($ip === '127.0.0.1' || $ip === '::1' || strpos($ip, '192.168.') === 0 || strpos($ip, '10.') === 0 || strpos($ip, '172.16.') === 0) {
            $ext = @httpGet('https://api.ip.sb/geoip', 5);
            if ($ext) { $d = @json_decode($ext, true); if ($d && !empty($d['ip'])) $ip = $d['ip']; }
            if ($ip === '127.0.0.1' || $ip === '::1') {
                $ext2 = @httpGet('http://ip-api.com/json/?fields=query', 5);
                if ($ext2) { $d2 = @json_decode($ext2, true); if ($d2 && !empty($d2['query'])) $ip = $d2['query']; }
            }
        }
        $result['success'] = true; $result['data'] = ['ip' => $ip]; return;
    }
    switch ($source) {
        case 'ip-api':
            $url = 'http://ip-api.com/json/' . ($query ? urlencode($query) : '') . '?lang=zh-CN';
            $resp = httpGet($url);
            if ($resp === false) { $result['error'] = '连接失败'; break; }
            $d = json_decode($resp, true);
            if (!$d || $d['status'] !== 'success') { $result['error'] = $d['message'] ?? '查询失败'; break; }
            $result['success'] = true;
            $result['data'] = ['ip' => $d['query'] ?? '','country' => $d['country'] ?? '','region' => trim(($d['regionName'] ?? '') . ' ' . ($d['city'] ?? '')),'city' => $d['city'] ?? '','district' => $d['district'] ?? '','lat' => $d['lat'] ?? '','lon' => $d['lon'] ?? '','isp' => $d['isp'] ?? '','org' => $d['org'] ?? '','timezone' => $d['timezone'] ?? '','zip' => $d['zip'] ?? ''];
            break;
        case 'ip-sb':
            $resp = httpGet('https://api.ip.sb/geoip' . ($query ? '/' . urlencode($query) : ''));
            if ($resp === false) { $result['error'] = '连接失败'; break; }
            $d = json_decode($resp, true);
            if (!$d || !isset($d['ip'])) { $result['error'] = '查询失败'; break; }
            $result['success'] = true;
            $result['data'] = ['ip' => $d['ip'] ?? '','country' => $d['country'] ?? '','region' => trim(($d['region'] ?? $d['region_code'] ?? '') . ' ' . ($d['city'] ?? '')),'city' => $d['city'] ?? '','lat' => $d['latitude'] ?? $d['lat'] ?? '','lon' => $d['longitude'] ?? $d['lon'] ?? '','isp' => $d['isp'] ?? $d['asn_organization'] ?? '','org' => $d['organization'] ?? $d['asn_organization'] ?? '','timezone' => $d['timezone'] ?? '','zip' => $d['postal_code'] ?? ''];
            break;
        case 'ipwhois':
            $resp = httpGet('https://ipwho.is/' . ($query ? urlencode($query) : ''));
            if ($resp === false) { $result['error'] = '连接失败'; break; }
            $d = json_decode($resp, true);
            if (!$d || !($d['success'] ?? false)) { $result['error'] = $d['message'] ?? '查询失败'; break; }
            $result['success'] = true;
            $result['data'] = ['ip' => $d['ip'] ?? '','country' => $d['country'] ?? '','region' => trim(($d['region'] ?? '') . ' ' . ($d['city'] ?? '')),'city' => $d['city'] ?? '','lat' => $d['latitude'] ?? '','lon' => $d['longitude'] ?? '','isp' => $d['connection']['isp'] ?? '','org' => $d['connection']['org'] ?? '','timezone' => $d['timezone']['id'] ?? '','zip' => $d['postal'] ?? $d['postcode'] ?? ''];
            break;
        case 'ipip':
            $resp = httpGet('https://myip.ipip.net/json');
            if ($resp === false) { $result['error'] = '连接失败'; break; }
            $d = json_decode($resp, true);
            if (!$d || ($d['ret'] ?? '') !== 'ok') { $result['error'] = '查询失败'; break; }
            $loc = $d['data']['location'] ?? [];
            $result['success'] = true;
            $result['data'] = ['ip' => $d['data']['ip'] ?? '','country' => $loc[0] ?? '','region' => trim(($loc[1] ?? '') . ' ' . ($loc[2] ?? '')),'city' => $loc[2] ?? '','lat' => '','lon' => '','isp' => $loc[4] ?? '','org' => '','timezone' => '','zip' => ''];
            break;
        case 'ip-baidu':
            if (!$query) { $result['error'] = '请输入 IP'; break; }
            $resp = httpGetRaw("https://sp0.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php?query=" . urlencode($query) . "&resource_id=6006&ie=utf8&format=json");
            if ($resp === false) { $result['error'] = '连接失败'; break; }
            $resp = @mb_convert_encoding($resp, 'UTF-8', 'GBK');
            $d = json_decode($resp, true);
            if (!$d || empty($d['data'][0]['location'])) { $result['error'] = '查询失败'; break; }
            $result['success'] = true;
            $result['data'] = ['ip' => $d['data'][0]['origip'] ?? $query, 'country' => '中国', 'region' => $d['data'][0]['location'], 'city' => '','lat' => '','lon' => '','isp' => '','org' => '','timezone' => '','zip' => ''];
            break;
        case 'ip-baota':
            if (!$query) { $result['error'] = '请输入 IP'; break; }
            $resp = httpGetRaw("https://www.bt.cn/api/panel/get_ip_info?ip=" . urlencode($query));
            if ($resp === false) { $result['error'] = '连接失败'; break; }
            $d = json_decode($resp, true); $info = $d[$query] ?? [];
            if (!$info || empty($info['country'])) { $result['error'] = '查询失败'; break; }
            $result['success'] = true;
            $result['data'] = ['ip' => $query, 'country' => $info['country'] ?? '', 'region' => trim(($info['country']??'') . ' ' . ($info['province']??'') . ' ' . ($info['city']??'')), 'city' => $info['city'] ?? '', 'lat' => $info['latitude'] ?? '', 'lon' => $info['longitude'] ?? '', 'isp' => $info['carrier'] ?? '', 'org' => '', 'timezone' => '', 'zip' => ''];
            break;
        case 'ip9':
            if (!$query) { $result['error'] = '请输入 IP'; break; }
            $resp = httpGet("https://ip9.com.cn/get?ip=" . urlencode($query));
            if ($resp === false) { $result['error'] = '连接失败'; break; }
            $d = json_decode($resp, true);
            if (!$d || ($d['ret'] ?? 0) !== 200) { $result['error'] = '查询失败'; break; }
            $info = $d['data'] ?? [];
            $result['success'] = true;
            $result['data'] = ['ip' => $info['ip'] ?? $query, 'country' => $info['country'] ?? '', 'region' => trim(($info['prov'] ?? '') . ' ' . ($info['city'] ?? '')), 'city' => $info['city'] ?? '', 'lat' => $info['lat'] ?? '', 'lon' => $info['lng'] ?? '', 'isp' => $info['isp'] ?? '', 'org' => '', 'timezone' => '', 'zip' => $info['post_code'] ?? ''];
            break;
    }
}
