<?php
function handle_weather(&$result, $source, $query, $cfg, $key) {
    $city = trim($_GET['city'] ?? '');
    if (!$city) { $result['error'] = '缺少城市参数'; return; }
    if (!preg_match('/[\x{4e00}-\x{9fff}]/u', $city)) {
        $geo = @httpGet("https://restapi.amap.com/v3/geocode/geo?key={$key}&address=" . urlencode($city) . "&city=");
        if ($geo) { $g = @json_decode($geo, true); if (($g['status'] ?? '') === '1' && !empty($g['geocodes'][0]['city'])) { $city = $g['geocodes'][0]['city']; } elseif (($g['status'] ?? '') === '1' && !empty($g['geocodes'][0]['district'])) { $city = $g['geocodes'][0]['district']; } }
    }
    $resp = httpGet("https://restapi.amap.com/v3/weather/weatherInfo?key={$key}&city=" . urlencode($city) . "&extensions=base");
    if ($resp === false) { $result['error'] = '连接失败'; return; }
    $d = json_decode($resp, true);
    if (!$d || ($d['status'] ?? '') !== '1') { $result['error'] = $d['info'] ?? '查询失败'; return; }
    $live = $d['lives'][0] ?? [];
    if (!$live) { $result['error'] = '暂无天气数据'; return; }
    $result['success'] = true;
    $result['data'] = ['city' => $live['city'] ?? $city,'province' => $live['province'] ?? '','adcode' => $live['adcode'] ?? '','condition' => $live['weather'] ?? '','temp' => ($live['temperature'] ?? '') . '°C','wind_dir' => $live['winddirection'] ?? '','wind_power' => ($live['windpower'] ?? '') . '级','humidity' => ($live['humidity'] ?? '') . '%','report_time' => $live['reporttime'] ?? ''];
}
