<?php
function handle_icp(&$result, $source, $query, $cfg, $key) {
    $domain = $_GET['domain'] ?? '';
    if (!$domain) { $result['error'] = '缺少域名参数'; return; }
    $base = rtrim($cfg['icp_api'] ?? '', '/');
    if (!$base) { $result['error'] = '请在后台配置 ICP 查询接口'; return; }
    $url = $base . '/query/web?search=' . urlencode($domain);
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10,
        CURLOPT_USERAGENT => 'Mozilla/5.0',
    ]);
    $resp = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($code < 200 || $code >= 300) { $result['error'] = '连接失败'; return; }
    $d = @json_decode($resp, true);
    if (!$d || !($d['success'] ?? false)) { $result['error'] = '暂无 ICP 备案信息'; return; }
    $list = $d['params']['list'] ?? [];
    if (empty($list)) { $result['error'] = '暂无 ICP 备案信息'; return; }
    $item = $list[0];
    $result['success'] = true;
    $result['data'] = ['domain' => $item['domain'] ?? $domain, 'icp' => $item['serviceLicence'] ?? $item['mainLicence'] ?? '', 'unit' => $item['unitName'] ?? '', 'nature' => $item['natureName'] ?? '', 'audit_date' => $item['updateRecordTime'] ?? ''];
}
