<?php
function handle_dev_tools(&$result, $source, $query, $cfg, $key) {
    if ($source === 'dev_phone') {
        $num = preg_replace('/[^0-9]/', '', $query);
        if (strlen($num) < 7) { $result['error'] = '请输入至少7位手机号'; return; }
        $head = substr($num, 0, 7);
        $json = @file_get_contents("https://api.7x24cc.com/phone.php?number={$head}");
        if ($json) {
            $data = json_decode($json, true);
            if ($data && isset($data['province'])) {
                $result['success'] = true;
                $result['data'] = [
                    'phone' => $num, 'province' => $data['province'] ?? '',
                    'city' => $data['city'] ?? '', 'isp' => $data['isp'] ?? '',
                ];
                return;
            }
        }
        $result['error'] = '查询失败'; return;
    }
    if ($source === 'dev_idcard') {
        $id = strtoupper(preg_replace('/[^0-9Xx]/', '', $query));
        if (strlen($id) !== 18) { $result['error'] = '请输入18位身份证号'; return; }
        $json = @file_get_contents("https://api.7x24cc.com/idcard.php?number={$id}");
        if ($json) {
            $data = json_decode($json, true);
            if ($data && isset($data['province'])) {
                $result['success'] = true;
                $result['data'] = [
                    'id' => $id, 'province' => $data['province'] ?? '',
                    'city' => $data['city'] ?? '', 'district' => $data['district'] ?? '',
                    'birthday' => $data['birthday'] ?? '', 'gender' => $data['gender'] ?? '',
                ];
                return;
            }
        }
        $result['error'] = '查询失败'; return;
    }
}
