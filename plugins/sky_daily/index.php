<?php
/**
 * 光遇每日任务 - API 处理
 * 端点: sky_daily_fetch
 */

require_once __DIR__ . '/core.php';

function handle_sky_daily(&$result, $source, $query, $cfg, $key) {
    switch ($source) {
        case 'sky_daily_fetch':
            $date = $_GET['date'] ?? date('Y-m-d');
            $data = sky_fetch_daily($date);
            if ($data) {
                $result['success'] = true;
                $result['data'] = $data;
            } else {
                $result['success'] = false;
                $result['error'] = '未找到 ' . $date . ' 的每日任务（可能尚未发布）';
            }
            return;
    }
}
