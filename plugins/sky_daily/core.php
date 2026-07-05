<?php
/**
 * 光遇每日任务 - 核心逻辑
 * 数据来源: 网易大神 ds.163.com
 */
define('SKY_UID', '0c565eef3c904d84b23f5624ff67f853');
define('SKY_API', 'https://inf.ds.163.com/v1/web');
define('SKY_FEED_TYPES', '1,2,3,4,6,7,10,11');
define('SKY_CACHE_TTL', 7200);

function sky_http_get(string $url): ?string {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HTTPHEADER => ['User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'],
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($code === 200 && $body !== false) ? $body : null;
}

function sky_parse_feed(array $feed): array {
    $c = is_string($feed['content'] ?? null) ? json_decode($feed['content'], true) : ($feed['content'] ?? []);
    $body = $c['body'] ?? [];
    $text = $body['text'] ?? '';
    $longText = $body['longText'] ?? '';
    $coverUrl = $body['media'][0]['url'] ?? '';

    // 日期与基本信息
    $date = ''; $calendar = ''; $map = ''; $season = '';
    if (preg_match('/^(\d{4})年(\d{1,2})月(\d{1,2})日/', $text, $m)) {
        $date = sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        $rest = trim(substr($text, strlen($m[0])));
        $lines = explode("\n", $rest);
        $calendar = trim($lines[0] ?? '');
        $map = trim($lines[1] ?? '');
    }
    if (preg_match('/季[：:]\s*(.+)/u', $text, $m)) $season = $m[1];
    if (preg_match('/剩余时间[：:]\s*(.+)/u', $text, $m)) $season .= ' (剩余' . $m[1] . ')';

    // 解析任务
    $tasks = [];
    $sectionEnd = strlen($longText);
    foreach (['<strong>季节蜡烛</strong>', '伊甸之眼坠落碎片位置', '003917-8p704dsqv9.png'] as $marker) {
        $p = strpos($longText, $marker);
        if ($p !== false && $p < $sectionEnd) $sectionEnd = $p;
    }

    if (preg_match_all('/<strong>\s*任务[一二三四五六七八九十]+\s*[：:]\s*(.+?)\s*<\/strong>/u', $longText, $tm, PREG_SET_ORDER)) {
        foreach ($tm as $idx => $m) {
            $name = strip_tags($m[1]);
            $pos = strpos($longText, $m[0]);
            $nextPos = ($idx + 1 < count($tm)) ? strpos($longText, $tm[$idx + 1][0]) : $sectionEnd;
            $block = substr($longText, $pos, min($nextPos - $pos, 3000));

            $desc = ''; $loc = '';
            if (preg_match('/<\/strong>\s*(?:<\/p>)?\s*(?:<p[^>]*>)?\s*(.+?)(?:<\/p>|<br|<img)/us', $block, $dm)) {
                $desc = trim(strip_tags($dm[1]));
                if (mb_strlen($desc) < 4 || $desc === '完成方法') $desc = '';
            }
            if (preg_match('/<strong>\s*(?:推荐)?位置[：:]\s*(.+?)\s*<\/strong>/u', $block, $lm)) $loc = strip_tags($lm[1]);
            if (preg_match('/<strong>\s*推荐先祖[：:]\s*(.+?)\s*<\/strong>/u', $block, $pm)) {
                $loc = ($loc ? $loc . ' | ' : '') . '先祖: ' . strip_tags($pm[1]);
            }
            $images = [];
            if (preg_match_all('/<img[^>]+src="([^"]+)"[^>]*>/', $block, $im)) {
                $images = array_slice($im[1], 0, 2);
            }
            $tasks[] = compact('name', 'desc', 'loc', 'images');
        }
    }

    // 碎片信息
    $shard = ['map' => '', 'type' => '', 'location' => '', 'time' => '', 'method' => '', 'reward' => '', 'images' => []];
    if (preg_match('/伊甸之眼坠落碎片位置/', $longText)) {
        $sp = strpos($longText, '伊甸之眼坠落碎片位置');
        $ep = strpos($longText, '<strong>大蜡烛</strong>', $sp) ?: strlen($longText);
        $sb = substr($longText, $sp, $ep - $sp);
        if (preg_match('/<strong>\s*地图[：:]\s*(.+?)\s*<\/strong>/u', $sb, $m)) $shard['map'] = strip_tags($m[1]);
        if (preg_match('/<strong>\s*(\S+碎片).*?<\/strong>/u', $sb, $m)) $shard['type'] = strip_tags($m[1]);
        if (preg_match('/<strong>\s*具体位置[：:]\s*(.+?)\s*<\/strong>/u', $sb, $m)) $shard['location'] = strip_tags($m[1]);
        if (preg_match('/<strong>\s*坠落时间[：:]\s*(.+?)\s*<\/strong>/u', $sb, $m)) $shard['time'] = strip_tags($m[1]);
        if (preg_match('/<strong>\s*清理方式[：:]\s*(.+?)\s*<\/strong>/u', $sb, $m)) $shard['method'] = strip_tags($m[1]);
        if (preg_match('/<strong>\s*获取货币[：:]\s*(.+?)\s*<\/strong>/u', $sb, $m)) $shard['reward'] = strip_tags($m[1]);
        if (preg_match_all('/<img[^>]+src="([^"]+)"[^>]*>/', $sb, $sim)) $shard['images'] = array_slice($sim[1], 0, 2);
    }

    // 季节蜡烛
    $sc = ['map' => '', 'group' => '', 'images' => []];
    if (preg_match('/季节蜡烛/', $longText)) {
        $scp = strpos($longText, '季节蜡烛');
        $sce = strpos($longText, '伊甸之眼', $scp) ?: strlen($longText);
        $scb = substr($longText, $scp, $sce - $scp);
        if (preg_match('/地图[：:]\s*(.+?)<br>位置[：:]\s*(.+?)</us', $scb, $m)) { $sc['map'] = strip_tags($m[1]); $sc['group'] = strip_tags($m[2]); }
        elseif (preg_match('/<strong>\s*地图[：:]\s*(.+?)(?:<br>|位置[：:]\s*(.+?))?\s*<\/strong>/us', $scb, $m)) {
            $sc['map'] = strip_tags($m[1]); $sc['group'] = $m[2] ? strip_tags($m[2]) : '';
        }
        if (preg_match_all('/<img[^>]+src="([^"]+)"[^>]*>/', $scb, $sim)) $sc['images'] = array_slice($sim[1], 0, 1);
    }

    // 大蜡烛
    $bigCandles = [];
    if (preg_match('/大蜡烛/', $longText)) {
        $bcp = strpos($longText, '大蜡烛');
        $bcb = substr($longText, $bcp);
        foreach (['云野', '雨林', '霞谷', '暮土', '禁阁'] as $mn) {
            if (preg_match('/<strong>' . $mn . '<\/strong>/u', $bcb, $mm)) {
                $ms = strpos($bcb, $mm[0]);
                $me = strlen($bcb);
                foreach (['云野', '雨林', '霞谷', '暮土', '禁阁'] as $nx) {
                    $np = strpos($bcb, "<strong>{$nx}</strong>", $ms + strlen($mm[0]));
                    if ($np !== false && $np < $me) $me = $np;
                }
                $mb = substr($bcb, $ms, $me - $ms);
                $mi = [];
                if (preg_match_all('/<img[^>]+src="([^"]+)"[^>]*>/', $mb, $mim)) $mi = array_slice($mim[1], 0, 1);
                $bigCandles[] = ['map' => $mn, 'images' => $mi];
            }
        }
    }

    // 免费魔法
    $magic = '';
    if (preg_match('/今日免费魔法.*?<strong>\s*(.+?)\s*<\/strong>/us', $longText, $fmm)) $magic = strip_tags($fmm[1]);

    return compact('date', 'calendar', 'map', 'season', 'coverUrl', 'tasks', 'shard', 'sc', 'bigCandles', 'magic');
}

function sky_fetch_daily(string $targetDate = ''): ?array {
    $targetDate = $targetDate ?: date('Y-m-d');

    // 检查缓存
    $cacheFile = __DIR__ . '/cache/' . $targetDate . '.json';
    if (file_exists($cacheFile) && time() - filemtime($cacheFile) < SKY_CACHE_TTL) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if ($cached) return $cached;
    }

    // 获取 feeds
    $pages = ($targetDate === date('Y-m-d')) ? 1 : 5;
    $allFeeds = [];
    $maxTime = '';
    for ($i = 0; $i < $pages; $i++) {
        $url = SKY_API . '/feed/basic/getSomeOneFeeds?feedTypes=' . SKY_FEED_TYPES . '&someOneUid=' . SKY_UID;
        if ($maxTime !== '') $url .= '&maxTime=' . urlencode((string)$maxTime);
        $json = sky_http_get($url);
        if (!$json) break;
        $data = json_decode($json, true);
        if (!$data || ($data['code'] ?? 0) !== 200) break;
        $feeds = $data['result']['feeds'] ?? [];
        if (empty($feeds)) break;
        $allFeeds = array_merge($allFeeds, $feeds);
        $nr = $data['result']['nextRangeParam'] ?? null;
        if ($nr && isset($nr['maxTime'])) $maxTime = $nr['maxTime'];
        else break;
    }

    if (empty($allFeeds)) return null;

    // 查找每日任务帖
    $dailyFeed = null;
    foreach ($allFeeds as $f) {
        $topics = $f['topicInfoList'] ?? [];
        $isDaily = false;
        foreach ($topics as $t) {
            if (strpos($t['topicName'] ?? '', '每日任务') !== false) { $isDaily = true; break; }
        }
        if (!$isDaily) continue;
        $c = is_string($f['content'] ?? null) ? json_decode($f['content'], true) : ($f['content'] ?? []);
        $txt = $c['body']['text'] ?? '';
        if ($targetDate === date('Y-m-d')) { $dailyFeed = $f; break; }
        if (preg_match('/(\d{4})年(\d{1,2})月(\d{1,2})日/', $txt, $m)) {
            $fd = sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
            if ($fd === $targetDate) { $dailyFeed = $f; break; }
        }
    }

    if (!$dailyFeed) return null;

    $result = sky_parse_feed($dailyFeed);
    $cacheDir = dirname($cacheFile);
    if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);
    file_put_contents($cacheFile, json_encode($result, JSON_UNESCAPED_UNICODE));
    return $result;
}
