<?php
function handle_whois(&$result, $source, $query, $cfg, $key) {
    $domain = $_GET['domain'] ?? '';
    if (!$domain) { $result['error'] = '缺少域名参数'; return; }
    $ext = strtolower(substr(strrchr($domain, '.'), 1)) ?: 'com';
    $whoisServers = [
        'com' => ['rdap' => 'https://rdap.verisign.com/com/v1/domain/'],
        'net' => ['rdap' => 'https://rdap.verisign.com/net/v1/domain/'],
        'org' => ['raw' => 'whois.pir.org'], 'cn' => ['raw' => 'whois.cnnic.cn'],
        'top' => ['raw' => 'whois.nic.top'], 'xyz' => ['raw' => 'whois.nic.xyz'],
        'io' => ['raw' => 'whois.nic.io'], 'cc' => ['raw' => 'whois.nic.cc'],
        'info' => ['raw' => 'whois.afilias.net'], 'moe' => ['raw' => 'whois.nic.moe'],
        'icu' => ['raw' => 'whois.nic.icu'],
    ];
    $fields = ['domain'=>'','registrar'=>'','reg_date'=>'','exp_date'=>'','updated_date'=>'','status'=>[],'nameservers'=>[],'dnssec'=>'','registrant'=>'','email'=>''];
    $raw = '';
    $cfg_ws = $whoisServers[$ext] ?? ['rdap' => 'https://rdap.verisign.com/com/v1/domain/'];
    if (isset($cfg_ws['rdap'])) {
        $resp = @httpGet($cfg_ws['rdap'] . urlencode($domain), 10);
        if ($resp !== false) {
            $d = @json_decode($resp, true);
            if ($d && isset($d['ldhName'])) {
                $events = []; foreach (($d['events'] ?? []) as $e) $events[$e['eventAction']] = $e['eventDate'];
                $ns = []; foreach (($d['nameservers'] ?? []) as $n) $ns[] = $n['ldhName'] ?? '';
                $registrar = '';
                foreach (($d['entities'] ?? []) as $e) {
                    if (in_array('registrar', $e['roles'] ?? [])) {
                        foreach (($e['vcardArray'][1] ?? []) as $v) { if ($v[0] === 'fn') { $registrar = $v[3] ?? ''; break; } }
                    }
                }
                $fields['domain'] = $d['ldhName']; $fields['registrar'] = $registrar;
                $fields['reg_date'] = $events['registration'] ?? ''; $fields['exp_date'] = $events['expiration'] ?? '';
                $fields['updated_date'] = $events['last changed'] ?? ($events['last update of RDAP database'] ?? '');
                $fields['status'] = $d['status'] ?? []; $fields['nameservers'] = $ns;
                $fields['dnssec'] = ($d['secureDNS']['delegationSigned'] ?? false) ? '是' : '否';
                $raw = "域名: {$d['ldhName']}\n注册商: {$registrar}\n状态: " . implode(', ', $fields['status']) . "\n注册日期: " . ($fields['reg_date'] ?: 'N/A') . "\n过期日期: " . ($fields['exp_date'] ?: 'N/A') . "\n最后更新: " . ($fields['updated_date'] ?: 'N/A') . "\nDNS服务器: " . implode(', ', $ns) . "\nDNSSEC: {$fields['dnssec']}\n";
            }
        }
    }
    if (!$raw && isset($cfg_ws['raw'])) {
        $fp = @fsockopen($cfg_ws['raw'], 43, $e, $es, 8);
        if ($fp) { fwrite($fp, $domain . "\r\n"); while (!feof($fp)) $raw .= fgets($fp, 4096); fclose($fp); $raw = trim(preg_replace('/^%.*\n/m', '', $raw)); }
    }
    if (!$raw || stripos($raw, 'no entries found') !== false || stripos($raw, 'out of this registry') !== false) {
        foreach (['whois.cnnic.cn', 'whois.pir.org'] as $srv) {
            $fp = @fsockopen($srv, 43, $e, $es, 4);
            if ($fp) { $raw = ''; fwrite($fp, $domain . "\r\n"); while (!feof($fp)) $raw .= fgets($fp, 4096); fclose($fp); $raw = trim(preg_replace('/^%.*\n/m', '', $raw)); if ($raw && stripos($raw, 'no entries found') === false && stripos($raw, 'out of this registry') === false) break; }
        }
    }
    if (!$raw || stripos($raw, 'no entries found') !== false || stripos($raw, 'out of this registry') !== false) { $result['error'] = '无可用信息'; return; }
    if (!$fields['domain']) {
        $fields['domain'] = $domain;
        if (preg_match('/Domain\s*Name:\s*(.+)/i', $raw, $m)) $fields['domain'] = trim($m[1]);
        if (preg_match('/Registrar:\s*(.+)/i', $raw, $m)) $fields['registrar'] = trim($m[1]);
        if (preg_match('/Creation\s*(?:Date|Time):\s*(.+)/i', $raw, $m)) $fields['reg_date'] = trim($m[1]);
        if (preg_match('/(?:Registry\s*)?Expir(?:y|ation)\s*(?:Date|Time):\s*(.+)/i', $raw, $m)) $fields['exp_date'] = trim($m[1]);
        if (preg_match('/Updated\s*(?:Date|Time):\s*(.+)/i', $raw, $m)) $fields['updated_date'] = trim($m[1]);
        if (preg_match('/Name\s*Server:\s*(.+)/i', $raw, $m)) { preg_match_all('/Name\s*Server:\s*(.+)/i', $raw, $ms); $fields['nameservers'] = array_map('trim', $ms[1]); }
        if (preg_match('/DNSSEC:\s*(.+)/i', $raw, $m)) $fields['dnssec'] = trim($m[1]);
        if (preg_match('/Domain\s*Status:\s*(.+)/i', $raw, $m)) { preg_match_all('/Domain\s*Status:\s*(.+)/i', $raw, $ms); $fields['status'] = array_map(function($s) { return trim(explode(' ', $s)[0]); }, $ms[1]); }
    }
    $result['success'] = true; $result['data'] = ['raw' => $raw, 'fields' => $fields];
}
